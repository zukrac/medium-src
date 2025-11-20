<?php

class PurchasePlanner
{
    private array $priceList;
    private int $need;
    private array $minCosts;
    private array $solutionPath;
    private float $INF = PHP_FLOAT_MAX;

    /**
     * @param array $priceList
     * @param int $need
     * @return array
     *
     * @todo Replace arrays and private properties with input/output DTOs
     */
    public function findOptimalPurchases(array $priceList, int $need): array
    {
        // @todo Add various validation
        if ($need <= 0 || empty($priceList)) {
            return [];
        }

        $this->initialize($priceList, $need);
        $this->initializeCostAndPathArrays();
        $this->calculateOptimalCosts();

        if ($this->minCosts[$this->need] == $this->INF) {
            return [];
        }

        return $this->reconstructPurchasePlan();
    }

    private function initialize(array $priceList, int $need): void
    {
        $this->priceList = $this->sortPriceListByPricePerUnit($priceList);
        $this->need = $need;
    }

    private function sortPriceListByPricePerUnit(array $priceList): array
    {
        usort($priceList, function ($a, $b) {
            return ($a['price'] <=> $b['price']);
        });
        return $priceList;
    }

    private function initializeCostAndPathArrays(): void
    {
        // $minCosts[i] stores the minimum cost to achieve at least i units
        $this->minCosts = array_fill(0, $this->need + 1, $this->INF);
        $this->minCosts[0] = 0.0;

        // $solutionPath[i] stores the last offer and quantity used to achieve the minimum cost for i units
        $this->solutionPath = array_fill(0, $this->need + 1, null);
    }

    private function calculateOptimalCosts(): void
    {
        foreach ($this->priceList as $offerIndex => $offer) {
            $this->processOffer($offerIndex, $offer);
        }
    }

    private function processOffer(int $offerIndex, array $offer): void
    {
        // @todo Add pack size validation (>0)
        $packSize = $offer['pack'];
        $maxPurchasablePacks = (int) floor($offer['count'] / $packSize);
        $costPerPack = $offer['price'];

        // Iterate backwards to avoid using updated values in the same iteration
        for ($currentUnitsNeeded = $this->need; $currentUnitsNeeded >= $packSize; $currentUnitsNeeded--) {
            $this->evaluatePacksForCurrentAmount($currentUnitsNeeded, $offerIndex, $packSize, $maxPurchasablePacks, $costPerPack);
        }
    }

    private function evaluatePacksForCurrentAmount(int $currentUnitsNeeded, int $offerIndex, int $packSize, int $maxPurchasablePacks, float $costPerPack): void
    {
        for ($numPacks = 1; $numPacks <= $maxPurchasablePacks; $numPacks++) {
            $unitsFromPacks = $numPacks * $packSize;

            // Cannot buy more units than currently needed
            if ($unitsFromPacks > $currentUnitsNeeded) {
                break;
            }

            $remainingUnitsNeeded = $currentUnitsNeeded - $unitsFromPacks;

            // Check if the remaining amount can be achieved with a known minimum cost
            if ($this->minCosts[$remainingUnitsNeeded] != $this->INF) {
                $newTotalCost = $this->minCosts[$remainingUnitsNeeded] + $costPerPack * $numPacks;

                // If this new combination is cheaper than the previously known way to achieve $currentUnitsNeeded
                if ($newTotalCost < $this->minCosts[$currentUnitsNeeded]) {
                    $this->minCosts[$currentUnitsNeeded] = $newTotalCost;

                    // Record this offer and quantity as part of the optimal path for $currentUnitsNeeded
                    $this->solutionPath[$currentUnitsNeeded] = [
                        'offer_idx' => $offerIndex,
                        'qty' => $unitsFromPacks
                    ];
                }
            }
        }
    }

    private function reconstructPurchasePlan(): array
    {
        $purchasePlan = [];
        $unitsToAccountFor = $this->need;

        // Backtrack from the total need using the recorded solution path
        while ($unitsToAccountFor > 0 && $this->solutionPath[$unitsToAccountFor] !== null) {
            $step = $this->solutionPath[$unitsToAccountFor];
            $offerId = $this->priceList[$step['offer_idx']]['id'];
            $quantityPurchased = $step['qty'];

            // Add the quantity to the plan, merging with existing entries for the same offer
            $this->addOrIncrementQuantity($purchasePlan, $offerId, $quantityPurchased);

            // Move to the next step in the path
            $unitsToAccountFor -= $quantityPurchased;
        }

        return $purchasePlan;
    }

    private function addOrIncrementQuantity(array &$plan, int $offerId, int $quantity): void
    {
        foreach ($plan as &$item) {
            if ($item['id'] === $offerId) {
                $item['qty'] += $quantity;
                return;
            }
        }
        $plan[] = ['id' => $offerId, 'qty' => $quantity];
    }
}
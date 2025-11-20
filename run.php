<?php

require_once 'PurchasePlanner.php';

$cases = [
    [
        'name' => "Первый случай",
        'requiredQty' => 76,
        'items' => [
            ['id' => 111, 'count' => 42, 'price' => 13, 'pack' => 1],
            ['id' => 222, 'count' => 77, 'price' => 11, 'pack' => 10],
            ['id' => 333, 'count' => 103, 'price' => 10, 'pack' => 50],
            ['id' => 444, 'count' => 65, 'price' => 12, 'pack' => 5]
        ]
    ],
    [
        'name' => "Второй",
        'requiredQty' => 76,
        'items' => [
            ['id' => 111, 'count' => 42, 'price' => 9, 'pack' => 1],
            ['id' => 222, 'count' => 77, 'price' => 11, 'pack' => 10],
            ['id' => 333, 'count' => 103, 'price' => 10, 'pack' => 50],
            ['id' => 444, 'count' => 65, 'price' => 12, 'pack' => 5]
        ]
    ],
    [
        'name' => "Третий",
        'requiredQty' => 76,
        'items' => [
            ['id' => 111, 'count' => 100, 'price' => 30, 'pack' => 1],
            ['id' => 222, 'count' => 60, 'price' => 11, 'pack' => 10],
            ['id' => 333, 'count' => 100, 'price' => 13, 'pack' => 50],
        ]
    ],
    [
        'name' => "Невозможный",
        'requiredQty' => 76,
        'items' => [
            ['id' => 111, 'count' => 100, 'price' => 30, 'pack' => 10],
            ['id' => 222, 'count' => 60, 'price' => 11, 'pack' => 10],
            ['id' => 333, 'count' => 100, 'price' => 13, 'pack' => 50],
        ]
    ],
];



$finder = new PurchasePlanner();
foreach ($cases as $case) {
    $results[$case['name']] = json_encode($finder->findOptimalPurchases($case['items'], $case['requiredQty']));
}

echo var_export($results, true);
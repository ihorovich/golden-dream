<?php
header('Content-Type: application/json');

$city = $_GET['city'];

// Симуляція відповіді для демонстраційних цілей.
$branches = [
    'Київ' => ['Відділення 1', 'Відділення 2', 'Відділення 3'],
    'Львів' => ['Відділення 1', 'Відділення 2'],
    'Одеса' => ['Відділення 1', 'Відділення 2', 'Відділення 3', 'Відділення 4'],
];

$response = isset($branches[$city]) ? $branches[$city] : [];
echo json_encode(['branches' => $response]);
?>

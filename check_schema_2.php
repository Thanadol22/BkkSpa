<?php
require 'app/config/database.php';
$pdo = Database::getInstance()->getConnection();

echo "--- PRODUCT ---\n";
$stmt = $pdo->query("DESCRIBE product");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode(", ", $cols) . "\n";

echo "--- COURSE ---\n";
$stmt = $pdo->query("DESCRIBE course");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode(", ", $cols) . "\n";
?>
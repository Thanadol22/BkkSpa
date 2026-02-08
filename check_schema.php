<?php
require 'app/config/database.php';
$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->query("DESCRIBE promotion_product");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "promotion_product columns: " . implode(", ", $cols) . "\n";

$stmt = $pdo->query("DESCRIBE promotion_course");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "promotion_course columns: " . implode(", ", $cols) . "\n";
?>
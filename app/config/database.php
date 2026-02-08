<?php
// app/config/database.php

class Database
{
    private static $instance = null;  // เก็บ instance เดียว (Singleton)
    private $pdo;

    private function __construct()
    {
        $host    = $_ENV['DB_HOST'] ?? 'localhost';
        $db      = $_ENV['DB_DATABASE'] ?? 'dbbkkspa';
        $user    = $_ENV['DB_USERNAME'] ?? 'root';
        $pass    = $_ENV['DB_PASSWORD'] ?? '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // ให้โยน exception เวลา error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // ดึงข้อมูลแบบ array key เป็นชื่อคอลัมน์
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            // ตั้ง Session timezone ของ MySQL ให้ตรงกับเวลาเซิร์ฟเวอร์ (เช่น +07:00)
            // ถ้า MySQL มี timezone tables โหลดไว้ เราสามารถใช้ 'Asia/Bangkok' แทน '+07:00'
            try {
                $this->pdo->exec("SET time_zone = '+07:00'");
            } catch (\Exception $e) {
                // ไม่บังคับ ถ้า DB user ไม่มีสิทธิ์ตั้ง time_zone ให้ข้ามไป
            }
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    // เรียกใช้งาน instance เดียวทั่วโปรเจค
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // เอาไปใช้ใน Model อื่น ๆ
    public function getConnection()
    {
        return $this->pdo;
    }
}

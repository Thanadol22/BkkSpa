<?php
// app/core/Model.php

class Model
{
    protected $db;

    public function __construct()
    {
        // ดึง PDO connection จากคลาส Database
        $this->db = Database::getInstance()->getConnection();
    }
}

<?php

$host = getenv("DB_HOST") ?: "localhost";
$dbname = getenv("DB_NAME") ?: "uber_cueillette";
$user = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD") ?: "";

$port = getenv("DB_PORT") ?: "3306";

$db_type = getenv("DB_TYPE") ?: "mysql";

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur connexion : " . $e->getMessage());
}

?>
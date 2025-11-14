<?php

header('Content-Type: application/json; charset=utf-8');

$host= "localhost";
$dbname= "taller_carlup";
$username="root"; //predeterminado de xampp pero cambia en un hosting 
$pass=""; //predeterminado de xampp pero cambia en un hosting

try{
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username,$pass);
    $conn-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}catch(PDOException $e){
http_response_code(500);

echo json_encode([
    "error" => "Error de conexion a base de datos",
    "detalle" => $e->getMessage()
]);
exit;
}







?>
<?php
$servername = "localhost";
$username = "phpmyadmin"; // Cambia esto por tu nombre de usuario de MySQL
$password = "Shadalu18"; // Cambia esto por tu contraseña de MySQL
$database = "seguros"; // Cambia esto por el nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
function writeLog($text) {  
    date_default_timezone_set('America/Tegucigalpa');
    $handle = fopen("log.txt","a+"); 		
    fwrite($handle,date("[YmdHis]")  ." ".$text ."\n");  
    fclose($handle);
}
?>
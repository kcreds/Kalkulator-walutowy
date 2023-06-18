<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "nbp_data";

//Tworzenie połączenia i przypisanie do zmiennej $conn
$conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);

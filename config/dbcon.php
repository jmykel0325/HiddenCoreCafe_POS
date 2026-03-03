<?php

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'HiddenCafeDb';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die('Connection Failed: ' . mysqli_connect_error());
}

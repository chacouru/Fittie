<?php
require_once __DIR__ . '/functions.php';

$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$address = $_POST['address'];
$phone = $_POST['phone'];

$pdo = db_connect();
$stmt = $pdo->prepare("INSERT INTO users (name, email, password, address, phone) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$name, $email, $password, $address, $phone]);

header("Location: toppage.php");
exit;

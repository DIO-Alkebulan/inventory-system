<?php
/**
 * Common Header File
 * Include this at the top of dashboard pages
 */
if (!isset($_SESSION['user_id'])) {
    header("Location: /inventory-system/public/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="/inventory-system/assets/css/style.css">
    <link rel="stylesheet" href="/inventory-system/assets/css/dashboard.css">
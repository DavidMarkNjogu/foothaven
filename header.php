<?php
// header.php
session_start();
require_once 'db.php';

// Calculate cart count for the badge
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
        $cart_count += $quantity;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Kenya Kicks | Quality Footwear</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <a href="index.php" class="logo">KENYA KICKS.</a>
    <nav class="nav-links">
        <a href="index.php">Shop</a>
        <a href="cart.php">Cart <span class="cart-count"><?php echo $cart_count; ?></span></a>
        <a href="login.php" style="font-size:0.8rem; opacity:0.5;">Staff</a>
    </nav>
</header>
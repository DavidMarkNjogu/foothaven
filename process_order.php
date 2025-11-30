<?php
// process_order.php
session_start();
require_once 'db.php';

// 1. Validation: Ensure form was submitted and cart is not empty
if (!isset($_POST['place_order']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

// 2. Sanitize Inputs (Security)
$phone = mysqli_real_escape_string($conn, $_POST['phone']);
$name = isset($_POST['name']) ? mysqli_real_escape_string($conn, $_POST['name']) : 'Guest';
$location_id = (int)$_POST['location_id'];

// 3. Server-Side Calculations (Trust No One)
// A. Calculate Product Total
$product_total = 0;
$order_items = [];
$cart_ids = implode(',', array_keys($_SESSION['cart']));

if ($cart_ids) {
    $stmt = $conn->query("SELECT * FROM products WHERE id IN ($cart_ids)");
    $products = $stmt->fetchAll();
    
    foreach ($products as $product){
        $qty = $_SESSION['cart'][$product['id']];
        // Check stock availability
        if ($product['stock_level'] < $qty) {
            die("Error: Stock changed during checkout. Only " . $product['stock_level'] . " left for " . $product['title']);
        }
        
        $line_total = $product['selling_price'] * $qty;
        $product_total += $line_total;
        
        // Store for item insertion later
        $order_items[] = [
            'id' => $product['id'],
            'price' => $product['selling_price'],
            'qty' => $qty
        ];
    }
}

// B. Calculate Shipping
$shipping_cost = 0;
$stmt_loc = $conn->prepare("SELECT shipping_cost FROM locations WHERE id = ?");
$stmt_loc->execute([$location_id]);
$loc = $stmt_loc->fetch();

if (!$loc) { die("Invalid Location"); }
$shipping_cost = $loc['shipping_cost'];
$grand_total = $product_total + $shipping_cost;
// C. Final Total
$grand_total = $product_total + $shipping_cost;

// 4. Database Transaction: Insert Order
// Use a transaction to ensure both Order and Items are saved, or neither
// --- TRANSACTION ---
try {
    $conn->beginTransaction();

    // 1. Insert Order
    $sql_order = "INSERT INTO orders (customer_name, customer_phone, location_id, total_product_cost, shipping_cost, total_amount, status) 
                  VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->execute([$name, $phone, $location_id, $product_total, $shipping_cost, $grand_total]);
    
    $order_id = $conn->lastInsertId();

    // 2. Insert Items & Update Stock
    $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)";
    $stmt_item = $conn->prepare($sql_item);

    $sql_stock = "UPDATE products SET stock_level = stock_level - ? WHERE id = ?";
    $stmt_stock = $conn->prepare($sql_stock);

    foreach ($order_items as $item) {
        // Insert Item
        $stmt_item->execute([$order_id, $item['id'], $item['qty'], $item['price']]);
        // Update Stock
        $stmt_stock->execute([$item['qty'], $item['id']]);
    }

    $conn->commit();

    unset($_SESSION['cart']);
    header("Location: success.php?order=$order_id");
    exit();

} catch (Exception $e) {
    $conn->rollBack();
    die("Transaction Failed: " . $e->getMessage());
}
?>
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
    $sql_products = "SELECT * FROM products WHERE id IN ($cart_ids)";
    $result_products = mysqli_query($conn, $sql_products);
    
    while ($product = mysqli_fetch_assoc($result_products)) {
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
$sql_loc = "SELECT shipping_cost FROM locations WHERE id = $location_id";
$result_loc = mysqli_query($conn, $sql_loc);
if ($row = mysqli_fetch_assoc($result_loc)) {
    $shipping_cost = $row['shipping_cost'];
} else {
    die("Invalid Location Selected");
}

// C. Final Total
$grand_total = $product_total + $shipping_cost;

// 4. Database Transaction: Insert Order
// Use a transaction to ensure both Order and Items are saved, or neither
mysqli_begin_transaction($conn);

try {
    // Insert into orders table
    $sql_order = "INSERT INTO orders (customer_name, customer_phone, location_id, total_product_cost, shipping_cost, total_amount, status) 
                  VALUES ('$name', '$phone', '$location_id', '$product_total', '$shipping_cost', '$grand_total', 'pending')";
    
    if (!mysqli_query($conn, $sql_order)) {
        throw new Exception("Order Error: " . mysqli_error($conn));
    }
    
    $order_id = mysqli_insert_id($conn);

    // Insert Order Items and Deduct Stock
    foreach ($order_items as $item) {
        $pid = $item['id'];
        $price = $item['price'];
        $qty = $item['qty'];
        
        // Insert Item
        $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) 
                     VALUES ('$order_id', '$pid', '$qty', '$price')";
        if (!mysqli_query($conn, $sql_item)) {
            throw new Exception("Item Error: " . mysqli_error($conn));
        }
        
        // Update Stock
        $sql_stock = "UPDATE products SET stock_level = stock_level - $qty WHERE id = $pid";
        if (!mysqli_query($conn, $sql_stock)) {
            throw new Exception("Stock Error: " . mysqli_error($conn));
        }
    }

    // Commit Transaction
    mysqli_commit($conn);

    // 5. Success! Clear Cart and Redirect
    unset($_SESSION['cart']);
    header("Location: success.php?order=$order_id");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    die("Transaction Failed: " . $e->getMessage());
}
?>
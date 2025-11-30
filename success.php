<?php
// success.php
include 'header.php';

$order_id = isset($_GET['order']) ? (int)$_GET['order'] : 0;

if ($order_id == 0) {
    header("Location: index.php");
    exit();
}

// Fetch Order Details for display
$sql = "SELECT * FROM orders WHERE id = $order_id";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("Order not found.");
}
?>

<div class="container" style="text-align:center; padding: 3rem 1rem;">
    
    <div style="font-size:4rem; color: #28a745; margin-bottom:1rem;">✔</div>
    
    <h1 style="margin-bottom:1rem;">Order Received!</h1>
    <p style="color:#666; font-size:1.1rem;">Thank you for shopping with Kenya Kicks.</p>
    
    <div class="order-summary" style="max-width:500px; margin: 2rem auto; text-align:left;">
        <h3 style="border-bottom:1px solid #eee; padding-bottom:10px;">Payment Instructions</h3>
        
        <p style="margin-top:1rem;">To complete your order, please pay via M-PESA:</p>
        
        <div style="background:#f4f4f4; padding:1.5rem; border-radius:8px; margin: 1rem 0; text-align:center;">
            <p style="font-size:0.9rem; text-transform:uppercase; letter-spacing:1px;">Send Money / Buy Goods</p>
            <h2 style="font-size:2rem; color:var(--primary); margin:0.5rem 0;">07XX XXX XXX</h2>
            <p style="font-size:0.9rem;">(Use your name as reference)</p>
        </div>

        <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:1.2rem; margin-top:1rem;">
            <span>Amount to Pay:</span>
            <span style="color:var(--accent);">KSh <?php echo number_format($order['total_amount']); ?></span>
        </div>
        
        <div style="margin-top:2rem; font-size:0.9rem; color:#666; background:#eef; padding:10px; border-radius:4px;">
            <strong>Order ID: #<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></strong><br>
            Use this ID if you contact support.
        </div>
    </div>

    <a href="index.php" class="btn-cta">Continue Shopping</a>
</div>

</body>
</html>
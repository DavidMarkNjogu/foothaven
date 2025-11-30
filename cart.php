<?php
// cart.php
include 'header.php';

// --- HANDLE REMOVE ACTION ---
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id_to_remove = $_GET['id'];
    unset($_SESSION['cart'][$id_to_remove]);
    header("Location: cart.php"); // Refresh to clear URL parameters
    exit();
}

// --- FETCH CART ITEMS ---
$cart_items = [];
$subtotal = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Create a comma-separated list of IDs for the SQL query (e.g., 1,3,5)
    $ids = implode(',', array_keys($_SESSION['cart']));
    
    // Safety check for SQL injection (ensure only numbers)
    if (!empty($ids)) {
        $sql = "SELECT * FROM products WHERE id IN ($ids)";
        $result = mysqli_query($conn, $sql);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $qty = $_SESSION['cart'][$row['id']];
            $row['qty'] = $qty;
            $row['line_total'] = $qty * $row['selling_price'];
            $subtotal += $row['line_total'];
            $cart_items[] = $row;
        }
    }
}
?>

<div class="container">
    <h2 class="section-title">Your Cart</h2>

    <?php if (empty($cart_items)): ?>
        <div style="text-align:center; padding:3rem;">
            <p>Your cart is empty.</p>
            <br>
            <a href="index.php" class="btn-cta">Start Shopping</a>
        </div>
    <?php else: ?>
        
        <div style="overflow-x:auto;"> 
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <img src="<?php echo $item['image_main']; ?>" class="cart-img-thumb">
                                    <span><?php echo htmlspecialchars($item['title']); ?></span>
                                </div>
                            </td>
                            <td>KSh <?php echo number_format($item['selling_price']); ?></td>
                            <td><?php echo $item['qty']; ?></td>
                            <td>KSh <?php echo number_format($item['line_total']); ?></td>
                            <td>
                                <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="btn-danger" onclick="return confirm('Remove this item?');">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="display:flex; justify-content:flex-end; margin-top:2rem;">
            <div class="order-summary" style="width:100%; max-width:400px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:1rem; font-size:1.2rem;">
                    <strong>Subtotal:</strong>
                    <span>KSh <?php echo number_format($subtotal); ?></span>
                </div>
                <p style="font-size:0.9rem; color:#666; margin-bottom:1.5rem;">Shipping calculated at checkout.</p>
                <a href="checkout.php" class="btn-cta" style="width:100%; text-align:center;">Proceed to Checkout</a>
            </div>
        </div>

    <?php endif; ?>
</div>
</body>
</html>
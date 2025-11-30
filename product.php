<?php
// product.php
include 'header.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// FIX: Use Named Parameters (:id) to prevent SQLite Driver Crash
$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
$stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    // If ID=1 is not in the database, this redirects to home
    header("Location: index.php");
    exit();
}

// --- ADD TO CART LOGIC ---
$msg = "";
if (isset($_POST['add_to_cart'])) {
    $qty = (int)$_POST['quantity'];
    
    if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $qty;
    } else {
        $_SESSION['cart'][$product_id] = $qty;
    }
    
    $msg = "Added to cart!";
    header("Refresh:0"); 
}
?>

<div class="container">
    <?php if ($msg): ?>
        <div style="background:#d4edda; color:#155724; padding:10px; margin-bottom:20px; border-radius:4px;">
            <?php echo $msg; ?> <a href="cart.php" style="font-weight:bold; color:#155724;">View Cart</a>
        </div>
    <?php endif; ?>

    <div class="product-detail-layout" style="display:flex; flex-wrap:wrap; gap:2rem;">
        
        <div class="images-section" style="flex:1; min-width:300px;">
            <img src="<?php echo $product['image_main']; ?>" style="width:100%; border-radius:8px; margin-bottom:10px;">
            
            <div class="thumbnails" style="display:grid; grid-template-columns:repeat(4, 1fr); gap:10px;">
                <img src="<?php echo $product['image_top'] ?? 'https://via.placeholder.com/100'; ?>" style="width:100%; border-radius:4px;">
                <img src="<?php echo $product['image_bottom'] ?? 'https://via.placeholder.com/100'; ?>" style="width:100%; border-radius:4px;">
                <img src="<?php echo $product['image_side'] ?? 'https://via.placeholder.com/100'; ?>" style="width:100%; border-radius:4px;">
                <img src="<?php echo $product['image_back'] ?? 'https://via.placeholder.com/100'; ?>" style="width:100%; border-radius:4px;">
            </div>
        </div>

        <div class="info-section" style="flex:1; min-width:300px;">
            <h1 style="font-size:2rem; margin-bottom:0.5rem;"><?php echo htmlspecialchars($product['title']); ?></h1>
            
            <p style="font-size:1.5rem; color:var(--accent); font-weight:bold; margin-bottom:1rem;">
                KSh <?php echo number_format($product['selling_price']); ?>
            </p>

            <p style="color:#666; margin-bottom:1.5rem; line-height:1.6;">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </p>

            <ul style="list-style:none; margin-bottom:2rem; color:#444;">
                <li><strong>Stock:</strong> <?php echo $product['stock_level']; ?> pairs left</li>
                <li><strong>Size Range:</strong> <?php echo $product['size_range'] ?? 'Standard'; ?></li>
            </ul>

            <form method="POST">
                <div style="margin-bottom:1rem;">
                    <label>Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_level']; ?>" style="padding:8px; width:60px; margin-left:10px;">
                </div>
                
                <button type="submit" name="add_to_cart" class="btn-cta" style="width:100%; border:none; cursor:pointer;">
                    Add to Cart
                </button>
            </form>
            
            <div style="margin-top:2rem; font-size:0.9rem; color:#666; background:#f9f9f9; padding:1rem; border-left:3px solid #ccc;">
                <strong>Note on Delivery:</strong> We ship to your nearest town or Matatu Sacco stage. You will select your pickup point at checkout.
            </div>
        </div>
    </div>
</div>

</body>
</html>
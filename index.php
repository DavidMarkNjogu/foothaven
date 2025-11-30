<?php
// index.php
include 'header.php'; // Ensure header.php includes db.php

// Fetch latest products
$sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 6";
$stmt = $conn->query($sql);
$products = $stmt->fetchAll();

?>

<section class="hero">
    <h1>Upgrade Your Step.</h1>
    <p>Premium footwear. Honest prices. Delivered to your town.</p>
    <a href="#shop" class="btn-cta">Shop Now</a>
</section>

<div class="container" id="shop">
    <h2 class="section-title">Latest Drops</h2>
    
    <?php if (count($products) > 0): ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?php echo !empty($product['image_main']) ? htmlspecialchars($product['image_main']) : 'https://via.placeholder.com/400x300?text=No+Image'; ?>" 
                         alt="<?php echo htmlspecialchars($product['title']); ?>" 
                         class="product-img">
                    
                    <div class="product-info">
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="product-title">
                            <?php echo htmlspecialchars($product['title']); ?>
                        </a>
                        <div class="product-price">
                            KSh <?php echo number_format($product['selling_price']); ?>
                        </div>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-buy">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center; padding: 2rem;">No products found.</p>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; <?php echo date('Y'); ?> Kenya Kicks. Deliveries via major Saccos countrywide.</p>
</footer>
</body>
</html>
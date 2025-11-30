<?php
// checkout.php
include 'header.php';

// Redirect if cart empty
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

// Calculate Subtotal (Server Side)
$subtotal = 0;
$ids = implode(',', array_keys($_SESSION['cart']));
$sql_cart = "SELECT id, selling_price FROM products WHERE id IN ($ids)";
$result_cart = mysqli_query($conn, $sql_cart);
while ($row = mysqli_fetch_assoc($result_cart)) {
    $subtotal += $row['selling_price'] * $_SESSION['cart'][$row['id']];
}

// Fetch Locations for Dropdown
$sql_loc = "SELECT * FROM locations ORDER BY town_name ASC";
$result_loc = mysqli_query($conn, $sql_loc);
?>

<div class="container">
    <h2 class="section-title">Checkout</h2>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem;" class="checkout-layout">
        
        <div class="checkout-form">
            <form action="process_order.php" method="POST" id="checkoutForm">
                
                <div class="form-group">
                    <label class="form-label">Phone Number (M-PESA) *</label>
                    <input type="tel" name="phone" class="form-control" placeholder="0712 345 678" required>
                    <small style="color:#666;">We use this to track your order. No account needed.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Full Name (Optional)</label>
                    <input type="text" name="name" class="form-control" placeholder="John Doe">
                </div>

                <div class="form-group">
                    <label class="form-label">Delivery Location *</label>
                    <select name="location_id" id="locationSelect" class="form-control" required>
                        <option value="" data-cost="0" data-point="">Select your Town</option>
                        <?php while($loc = mysqli_fetch_assoc($result_loc)): ?>
                            <option value="<?php echo $loc['id']; ?>" 
                                    data-cost="<?php echo $loc['shipping_cost']; ?>"
                                    data-point="<?php echo htmlspecialchars($loc['pickup_point']); ?>">
                                <?php echo htmlspecialchars($loc['town_name']); ?> 
                                (KSh <?php echo number_format($loc['shipping_cost']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div id="pickupMessage" style="background:#eef; color:#334; padding:10px; border-radius:4px; margin-bottom:1.5rem; display:none;">
                    <strong>Pickup Point:</strong> <span id="pickupText"></span>
                </div>

                <div style="background:#fff3cd; color:#856404; padding:10px; border-radius:4px; font-size:0.9rem; margin-bottom:1.5rem;">
                    <strong>Why no home delivery?</strong> To keep prices low and ensure safety, we ship to trusted Sacco stages in your town.
                </div>

                <button type="submit" name="place_order" class="btn-cta" style="width:100%; border:none; cursor:pointer;">
                    Pay & Complete Order
                </button>
            </form>
        </div>

        <div class="order-summary">
            <h3>Order Summary</h3>
            <hr style="margin:1rem 0; border:0; border-top:1px solid #eee;">
            
            <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem;">
                <span>Subtotal</span>
                <span>KSh <?php echo number_format($subtotal); ?></span>
            </div>

            <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem; color:var(--text-grey);">
                <span>Shipping</span>
                <span id="displayShipping"> - </span>
            </div>

            <hr style="margin:1rem 0; border:0; border-top:1px solid #eee;">

            <div style="display:flex; justify-content:space-between; font-weight:800; font-size:1.5rem; color:var(--accent);">
                <span>Total</span>
                <span id="displayTotal">KSh <?php echo number_format($subtotal); ?></span>
            </div>
        </div>

    </div>
</div>

<script>
    const subtotal = <?php echo $subtotal; ?>;
    const locationSelect = document.getElementById('locationSelect');
    const displayShipping = document.getElementById('displayShipping');
    const displayTotal = document.getElementById('displayTotal');
    const pickupMessage = document.getElementById('pickupMessage');
    const pickupText = document.getElementById('pickupText');

    locationSelect.addEventListener('change', function() {
        // Get selected option
        const selectedOption = this.options[this.selectedIndex];
        const cost = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
        const point = selectedOption.getAttribute('data-point');

        // 1. Update Shipping Text
        if (cost > 0) {
            displayShipping.textContent = 'KSh ' + cost.toLocaleString();
        } else {
            displayShipping.textContent = ' - ';
        }

        // 2. Update Total (Subtotal + Shipping)
        const total = subtotal + cost;
        displayTotal.textContent = 'KSh ' + total.toLocaleString();

        // 3. Show Pickup Point Logic
        if (point) {
            pickupMessage.style.display = 'block';
            pickupText.textContent = point;
        } else {
            pickupMessage.style.display = 'none';
        }
    });
</script>

<style>
    @media (max-width: 768px) {
        .checkout-layout { grid-template-columns: 1fr !important; }
        .order-summary { order: -1; margin-bottom: 2rem; } /* Show summary first on mobile */
    }
</style>

</body>
</html>
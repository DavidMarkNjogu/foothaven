<?php
// admin/dashboard.php
session_start();
require_once '../db.php';

// Security Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['admin_role']; // 'owner' or 'staff'

// --- 1. DATA FETCHING ---

// A. Fetch Orders (For Everyone)
$sql_orders = "SELECT * FROM orders ORDER BY order_date DESC LIMIT 50";
$result_orders = mysqli_query($conn, $sql_orders);

// B. Calculate Financials (OWNER ONLY)
$revenue = 0;
$total_profit = 0;

if ($role == 'owner') {
    // This query joins items with products to calculate profit
    // Profit = (Selling Price - Buying Price) * Quantity
    $sql_profit = "
        SELECT 
            SUM(oi.price_at_purchase * oi.quantity) as revenue,
            SUM((oi.price_at_purchase - p.buying_price) * oi.quantity) as profit
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status != 'cancelled'
    ";
    
    $result_fin = mysqli_query($conn, $sql_profit);
    $fin_data = mysqli_fetch_assoc($result_fin);
    
    $revenue = $fin_data['revenue'] ?? 0;
    $total_profit = $fin_data['profit'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #111; --bg: #f4f4f4; --accent: #FF4500; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; padding: 0; }
        
        /* HEADER */
        .admin-header { background: var(--primary); color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .logout { color: #ccc; text-decoration: none; font-size: 0.9rem; }
        
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        
        /* CARDS (Owner Only) */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stat-label { color: #666; font-size: 0.9rem; display: block; margin-bottom: 0.5rem; }
        .stat-value { font-size: 1.8rem; font-weight: 800; color: var(--primary); }
        .stat-value.green { color: #28a745; }
        
        /* TABLE */
        .table-wrapper { overflow-x: auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; min-width: 600px; } /* Min-width forces scroll on mobile */
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; font-weight: 600; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; text-transform: uppercase; font-weight: bold; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-completed { background: #d4edda; color: #155724; }

        /* HIDE FROM STAFF */
        .owner-only { display: <?php echo ($role === 'owner') ? 'block' : 'none'; ?>; }
    </style>
</head>
<body>

<div class="admin-header">
    <div>
        <strong>KENYA KICKS ADMIN</strong>
        <span style="font-size:0.8rem; background:#333; padding:2px 6px; border-radius:4px; margin-left:10px;">
            <?php echo strtoupper($role); ?> VIEW
        </span>
    </div>
    <a href="../index.php" class="logout">Back to Site</a>
</div>

<div class="container">
    
    <div class="owner-only">
        <h2 style="margin-bottom:1rem;">Financial Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total Revenue</span>
                <span class="stat-value">KSh <?php echo number_format($revenue); ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Net Profit</span>
                <span class="stat-value green">KSh <?php echo number_format($total_profit); ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Margin Estimate</span>
                <span class="stat-value">
                    <?php echo ($revenue > 0) ? round(($total_profit / $revenue) * 100, 1) : 0; ?>%
                </span>
            </div>
        </div>
    </div>

    <h2 style="margin-bottom:1rem;">Recent Orders</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Location</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($order = mysqli_fetch_assoc($result_orders)): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name'] ?: 'Guest'); ?></td>
                        <td>
                            <a href="tel:<?php echo $order['customer_phone']; ?>" style="color:var(--accent); text-decoration:none;">
                                <?php echo $order['customer_phone']; ?>
                            </a>
                        </td>
                        <td>ID: <?php echo $order['location_id']; ?></td>
                        <td>KSh <?php echo number_format($order['total_amount']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $order['status']; ?>">
                                <?php echo $order['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="#" style="font-size:0.85rem; color:#666;">View</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
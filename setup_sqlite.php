<?php
// setup_sqlite.php
// Run this file once locally via 'php setup_sqlite.php' to generate the database file

$db = new PDO('sqlite:foothaven.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Create Tables
$commands = [
    "CREATE TABLE IF NOT EXISTS admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'staff'
    )",
    "CREATE TABLE IF NOT EXISTS locations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        town_name TEXT NOT NULL,
        pickup_point TEXT NOT NULL,
        shipping_cost REAL NOT NULL DEFAULT 0.00
    )",
    "CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT,
        category TEXT DEFAULT 'Unbranded',
        size_range TEXT,
        color TEXT,
        buying_price REAL NOT NULL,
        selling_price REAL NOT NULL,
        stock_level INTEGER DEFAULT 0,
        image_main TEXT,
        image_top TEXT,
        image_bottom TEXT,
        image_side TEXT,
        image_back TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_name TEXT,
        customer_phone TEXT NOT NULL,
        location_id INTEGER NOT NULL,
        total_product_cost REAL NOT NULL,
        shipping_cost REAL NOT NULL,
        total_amount REAL NOT NULL,
        mpesa_code TEXT,
        status TEXT DEFAULT 'pending',
        order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(location_id) REFERENCES locations(id)
    )",
    "CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        quantity INTEGER DEFAULT 1,
        price_at_purchase REAL NOT NULL,
        FOREIGN KEY(order_id) REFERENCES orders(id),
        FOREIGN KEY(product_id) REFERENCES products(id)
    )"
];

foreach ($commands as $cmd) {
    $db->exec($cmd);
}

// 2. Insert Your Data
$db->exec("INSERT INTO admin_users (username, password, role) VALUES ('boss', '123456', 'owner')");
$db->exec("INSERT INTO admin_users (username, password, role) VALUES ('john_staff', '123456', 'staff')");

$db->exec("INSERT INTO locations (town_name, pickup_point, shipping_cost) VALUES 
('Nairobi (CBD)', 'Mololine Stage, Accral Road', 250.00),
('Nakuru (Town)', 'Pick & Drop Office, Oginga Odinga St', 100.00),
('Eldoret', 'North Rift Shuttle, Main Stage', 300.00),
('Kisumu', 'Guardian Angel Office', 350.00),
('Mombasa', 'Modern Coast Office', 500.00),
('Thika', 'Manchester Sacco Stage', 250.00)");

$db->exec("INSERT INTO products (title, description, buying_price, selling_price, stock_level, image_main) 
VALUES ('Street Runner X1', 'A durable, everyday sneaker.', 1500.00, 3500.00, 7, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=1000&auto=format&fit=crop')");

echo "Database foothaven.sqlite created successfully!";
?>
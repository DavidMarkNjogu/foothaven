<?php
// admin/login.php
session_start();
require_once '../db.php'; 

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // PDO PREPARED STATEMENT (SQLite Compatible)
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = :user");
    $stmt->bindValue(':user', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Simple password check for MVP (In production use password_verify)
    if ($user && $user['password'] === $password) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_name'] = $user['username'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f4f4; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
        .login-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        input { width: 100%; padding: 12px; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #111; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .error { color: red; margin-bottom: 1rem; display: block; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2 style="text-align:center; margin-top:0;">Staff Access</h2>
        <?php if($error): ?> <span class="error"><?php echo $error; ?></span> <?php endif; ?>
        
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>
            
            <label>Password</label>
            <input type="password" name="password" required>
            
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>
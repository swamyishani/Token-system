<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'admin_accounts';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$counter = $_POST['counter'] ?? '';
$login = false;
$auth_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = authenticate($email, $password);
    if ($login) {
        header("Location: admin_page.php");
        exit;
    } else {
        $auth_message = 'Authentication failed. Please check your credentials and try again.';
    }
}

function initialize_db($conn) {
    $queries = [
        "CREATE TABLE IF NOT EXISTS admin_accounts (NAME VARCHAR(100) NOT NULL, EMAIL VARCHAR(255) PRIMARY KEY, PASSWORD VARCHAR(255) NOT NULL)",
        "CREATE TABLE IF NOT EXISTS employees (name VARCHAR(255) NOT NULL, token_no INT AUTO_INCREMENT PRIMARY KEY)",
        "INSERT IGNORE INTO admin_accounts (NAME, EMAIL, PASSWORD) VALUES ('Admin', 'admin@example.com', 'password123')"
    ];
    foreach ($queries as $query) {
        mysqli_query($conn, $query);
    }
}

function get_db_connection() {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
    $conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($conn) {
        mysqli_set_charset($conn, 'utf8mb4');
        return $conn;
    }

    $err = mysqli_connect_error();
    if (stripos($err, 'Unknown database') !== false) {
        $adminConn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS);
        if ($adminConn) {
            mysqli_query($adminConn, "CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            mysqli_close($adminConn);
            $conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
            if ($conn) {
                initialize_db($conn);
                mysqli_set_charset($conn, 'utf8mb4');
                return $conn;
            }
        }
    }

    error_log('Database connection failed: ' . $err);
    return null;
}

function authenticate($email, $password) {
    if (!$email || !$password) {
        return false;
    }

    $conn = get_db_connection();
    if (!$conn) {
        return false;
    }

    $stmt = mysqli_prepare($conn, 'SELECT PASSWORD FROM admin_accounts WHERE EMAIL = ?');
    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $storedPassword);
    $found = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    if ($found && $storedPassword === $password) {
        return true;
    }

    return false;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Queue Management & Token Allocation System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php if ($auth_message) echo $auth_message; ?>

  <!-- ==========================================================================
     VIEW 3: ADMIN LOGIN INTERFACE (SECURE ENTERPRISE SPECIFICATION)
     ========================================================================== -->
  <section id="view-admin-login" class="view-section active-view">
    <div class="login-box card">
      <div class="login-header">
        <h3>Staff Authentication Portal</h3>
        <p>Enterprise Node Security Gateway</p>
      </div>
      <form action="admin_login.php" method="POST">
        <label for="admin-email">Staff Corporate Email</label>
        <input type="email" name="email" id="admin-email" placeholder="name@company.com" required>
        
        <label for="admin-password">Secure Security Password</label>
        <input type="password" name="password" id="admin-password" placeholder="••••••••" required>
        
        <label for="counter-select">Assign to Desk Counter Node</label>
        <select name="counter" id="counter-select" required>
          <option value="">-- Select Deployment Workspace --</option>
          <option value="inquiries">Counter 1 (Inquiries)</option>
          <option value="cashier terminal">Counter 2 (Cashier Terminal)</option>
          <option value="approvals">Counter 3 (Approvals & Audits)</option>
        </select>

        <label for="mfa">MFA Authenticator Token Key</label>
        <input type="text" name="mfa" id="mfa" placeholder="6-Digit MFA Code Token" maxlength="6">
        
        <button type="submit" class="btn">Open Admin Dashboard</button>
      </form>
      
      <div class="form-footer-links">
        <a href="new_admin.php">Create new account</a>
      </div>
    </div>
  </section>

</body>
</html>


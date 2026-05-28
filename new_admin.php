<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'admin_accounts';

function initialize_db($conn) {
    $queries = [
        "CREATE TABLE IF NOT EXISTS admin_accounts (NAME VARCHAR(100) NOT NULL, EMAIL VARCHAR(255) PRIMARY KEY, PASSWORD VARCHAR(255) NOT NULL)",
        "CREATE TABLE IF NOT EXISTS employees (name VARCHAR(255) NOT NULL, token_no INT AUTO_INCREMENT PRIMARY KEY)"
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

function create_new_admin_account($name, $email, $password) {
  if (!$name || !$email || !$password) {
    echo "<script type='text/javascript'>alert('All fields are required to create a new admin account. Please fill in all details.')</script>";
    return false;
  }

  $conn = get_db_connection();
  if (!$conn) {
    echo "<script type='text/javascript'>alert('Database connection failed.')</script>";
    return false;
  }

  $stmt = mysqli_prepare($conn, 'INSERT INTO admin_accounts (NAME, EMAIL, PASSWORD) VALUES (?, ?, ?)');
  if (!$stmt) {
    mysqli_close($conn);
    echo "<script type='text/javascript'>alert('Failed to prepare statement.')</script>";
    return false;
  }

  mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $password);
  $success = mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
  mysqli_close($conn);

  if ($success) {
    echo "<script type='text/javascript'>alert('Admin account created successfully!')</script>";
    return true;
  }

  echo "<script type='text/javascript'>alert('Error creating account.')</script>";
  return false;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'] ?? '';
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  create_new_admin_account($name, $email, $password);
}

?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Queue Management & Token Allocation System</title>
  <link rel="stylesheet" href="style.css">
</head>
 
<body>
    <section id="view-admin-login" class="view-section active-view">
    <div class="login-box card">
      <div class="login-header">
        <h3>Create new account</h3>
        <p>Enterprise Node Security Gateway</p>
      </div>
      
      <form action="new_admin.php" method="POST">
        <label for="admin-name">Enter Name</label>
        <input type="text" name="name" id="admin-name" placeholder="Admin Name" required>

        <label for="admin-email">Enter Email</label>
        <input type="email" name="email" id="admin-email" placeholder="name@company.com" required>
        
        <label for="admin-password">Enter Password</label>
        <input type="password" name="password" id="admin-password" placeholder="••••••••" required>

        <label for="mfa">MFA Authenticator Token Key</label>
        <input type="text" id="mfa" placeholder="6-Digit MFA Code Token" maxlength="6">
        
        <button type="submit" class="btn">Open Admin Dashboard</button>
      </form>
    </div>
  </section>
</body>

</html>
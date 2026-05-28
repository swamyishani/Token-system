<?php 
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
        initialize_db($conn);
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

function show_count() {
    $conn = get_db_connection();
    if (!$conn) {
        return 0;
    }
    $result = mysqli_query($conn, 'SELECT COUNT(*) AS cnt FROM employees');
    $count = 0;
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $count = $row['cnt'] ?? 0;
        mysqli_free_result($result);
    }
    mysqli_close($conn);
    return $count;
}

function show_tokens() {
    $conn = get_db_connection();
    if (!$conn) {
        echo "<tr><td colspan='2'>Database not available</td></tr>";
        return;
    }
    $result = mysqli_query($conn, 'SELECT name, token_no FROM employees LIMIT 20');
    if (!$result) {
        echo "<tr><td colspan='2' style='color:red;'>Query error: " . htmlspecialchars(mysqli_error($conn)) . "</td></tr>";
        mysqli_close($conn);
        return;
    }
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>" . htmlspecialchars($row['name'] ?? 'N/A') . "</td><td>" . htmlspecialchars($row['token_no'] ?? 'N/A') . "</td></tr>";
    }
    mysqli_free_result($result);
    mysqli_close($conn);
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

  <section id="view-realtime-admin" class="view-section active-view">
    <div class="dashboard-header">
      <h2>Live Matrix Control Dashboard Node</h2>
      <div class="live-indicator">
        <div class="pulse-dot"></div> Production Stream Synchronized
      </div>
    </div>

    <div class="dashboard-layout">
      <!-- Active Workstation Control Column -->
      <div class="card">
        <h3>Workstation Node 3</h3>
        <p class="section-desc">Active Operator Agent: John Doe</p>
        
        <div class="panel-controls">
          <button class="btn btn-success" onclick="alert('Calling Next Ticket Registry Entry.')">Call Next Token</button>
        </div>

        <div class="live-metrics-summary">
          <strong>Live Metrics Log Summary:</strong>
          <div class="metric-row"><span>Avg Service Run:</span><strong>5m 12s</strong></div>
          <div class="metric-row"><span>Remaining Tokens:</span><span ><?php echo show_count(); ?></span></div>
        </div>
      </div>

      <!-- Live Stream Data Matrix Representation Table -->
      <div class="card">
        <h3>Live Active Token Pipeline Streams</h3>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Customer Name</th>
                <th>Token Number</th>
              </tr>
            </thead>
            <tbody>
              <?php show_tokens(); ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

</body>
</html>
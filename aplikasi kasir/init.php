<?php
// Initialize the cash register system
// This script sets up the database and sample data

require_once 'includes/db_connect.php';
require_once 'database/init_db.php';

echo "<h2>Cash Register System Initialization</h2>";
echo "<p>Setting up the database and sample data...</p>";

try {
    // Run the initialization script
    ob_start();
    include 'database/init_db.php';
    $output = ob_get_contents();
    ob_end_clean();

    echo "<pre>$output</pre>";
    echo "<p>Initialization completed successfully!</p>";
    echo "<a href='login.php' style='color: #3498db; text-decoration: none; font-weight: bold;'>Go to Login Page</a>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error during initialization: " . $e->getMessage() . "</p>";
}
?>
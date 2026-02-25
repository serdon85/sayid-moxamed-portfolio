<?php
// config/config.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'electricity_billing_db');

// Root URL (Adjust if site is in a subfolder)
define('BASE_URL', 'http://localhost/Electricity%20Billing%20system/');

// Security: Session settings
session_start();

// Error reporting (Turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

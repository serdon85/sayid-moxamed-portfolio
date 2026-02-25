<?php
// seed.php
require_once 'config/config.php';
require_once 'classes/Database.php';

$db = new Database();

try {
    echo "Seeding database...<br>";

    // 1. Roles (Already in SQL but being safe)
    $db->query("INSERT IGNORE INTO roles (id, role_name) VALUES (1, 'Admin'), (2, 'Billing Officer'), (3, 'Accountant'), (4, 'Technician')");
    $db->execute();

    // 2. Admin User
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $db->query("INSERT IGNORE INTO users (username, password, full_name, role_id) VALUES ('admin', :pass, 'System Admin', 1)");
    $db->bind(':pass', $password);
    $db->execute();

    // 3. Tariffs
    // Residential slabs
    $db->query("INSERT IGNORE INTO tariffs (type, slab_start, slab_end, rate_per_unit, tax_percentage) VALUES 
                ('Residential', 0, 100, 0.50, 5.00),
                ('Residential', 101, 300, 0.75, 5.00),
                ('Residential', 301, NULL, 1.20, 5.00)");
    $db->execute();

    // Commercial slabs
    $db->query("INSERT IGNORE INTO tariffs (type, slab_start, slab_end, rate_per_unit, tax_percentage) VALUES 
                ('Commercial', 0, 500, 1.50, 10.00),
                ('Commercial', 501, NULL, 2.00, 10.00)");
    $db->execute();

    // 4. Sample Customer
    $db->query("INSERT IGNORE INTO customers (meter_number, first_name, last_name, email, phone, address, tariff_type) VALUES 
                ('MET-0001', 'John', 'Doe', 'john@example.com', '1234567890', '123 Electric Street', 'Residential')");
    $db->execute();

    echo "Seeding completed! You can now login with: <b>admin</b> / <b>admin123</b><br>";
    echo "<a href='public/index.php'>Go to Login</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

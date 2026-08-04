<?php
require_once __DIR__ . '/../api/config.php';

// Add columns note and trash to user_purchases if they don't exist
$queries = [
    "ALTER TABLE `user_purchases` ADD COLUMN `note` TEXT DEFAULT NULL AFTER `purchase_token` CASCADE",
    "ALTER TABLE `user_purchases` ADD COLUMN `trash` TINYINT(1) DEFAULT 0 AFTER `note` CASCADE"
];

// Let's check columns first to avoid duplicate column errors
$res = $conn->query("SHOW COLUMNS FROM `user_purchases` LIKE 'note'");
if ($res->num_rows == 0) {
    if ($conn->query("ALTER TABLE `user_purchases` ADD COLUMN `note` TEXT DEFAULT NULL AFTER `purchase_token`")) {
        echo "Column 'note' added successfully.\n";
    } else {
        echo "Error adding 'note': " . $conn->error . "\n";
    }
} else {
    echo "Column 'note' already exists.\n";
}

$res2 = $conn->query("SHOW COLUMNS FROM `user_purchases` LIKE 'trash'");
if ($res2->num_rows == 0) {
    if ($conn->query("ALTER TABLE `user_purchases` ADD COLUMN `trash` TINYINT(1) DEFAULT 0 AFTER `note`")) {
        echo "Column 'trash' added successfully.\n";
    } else {
        echo "Error adding 'trash': " . $conn->error . "\n";
    }
} else {
    echo "Column 'trash' already exists.\n";
}
?>

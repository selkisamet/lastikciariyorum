<?php
// Migration script to add page_description columns

require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = Database::getInstance();

    // Add page_description to cities table
    echo "Adding page_description column to cities table...\n";
    $db->query("ALTER TABLE cities ADD COLUMN page_description TEXT NULL AFTER meta_description");
    echo "✓ Cities table updated successfully\n\n";

    // Add page_description to districts table
    echo "Adding page_description column to districts table...\n";
    $db->query("ALTER TABLE districts ADD COLUMN page_description TEXT NULL AFTER meta_description");
    echo "✓ Districts table updated successfully\n\n";

    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Note: Columns already exist. No changes needed.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
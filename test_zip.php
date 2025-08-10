<?php
if (class_exists('ZipArchive')) {
    echo "✅ ZipArchive extension is enabled and working!\n";
    echo "PHP Version: " . PHP_VERSION . "\n";
    echo "Loaded extensions:\n";
    if (in_array('zip', get_loaded_extensions())) {
        echo "- zip extension is loaded\n";
    }
} else {
    echo "❌ ZipArchive class not found\n";
    echo "Please restart Apache to apply the php.ini changes\n";
}
?>

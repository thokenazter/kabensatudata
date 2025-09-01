<?php

/**
 * OPcache configuration for Laravel Octane
 * Place file in document root and include from php.ini:
 * opcache.preload=/path/to/opcache.php
 */

// Validate opcache is enabled
if (!extension_loaded('Zend OPcache')) {
    echo "OPcache extension is not loaded\n";
    exit(1);
}

// Set recommended php.ini settings
opcache_reset();

if (function_exists('opcache_get_status')) {
    // Optimal OPcache settings for production
    ini_set('opcache.memory_consumption', 256);          // Increase memory allocation (MB)
    ini_set('opcache.interned_strings_buffer', 64);      // Increases string storage size
    ini_set('opcache.max_accelerated_files', 32531);     // Maximum files opcache can cache
    ini_set('opcache.file_update_protection', 0);        // No filesystem timestamp check
    ini_set('opcache.validate_timestamps', 0);           // Disable timestamp validation in production
    ini_set('opcache.jit_buffer_size', '128M');          // JIT buffer size
    ini_set('opcache.jit', '1255');                      // JIT settings (if PHP 8.0+)
    ini_set('opcache.revalidate_freq', 0);               // How often to check script timestamps (0 = never)
    ini_set('opcache.save_comments', 1);                 // Laravel requires comments
    ini_set('opcache.enable_file_override', 1);          // File override for better performance

    echo "OPcache configured successfully\n";
} else {
    echo "OPcache status function not available\n";
}

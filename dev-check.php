#!/usr/bin/env php
<?php

/**
 * Faveo Development Environment Checker
 * 
 * This script validates that your development environment meets
 * all the requirements to run Faveo Helpdesk.
 * 
 * Usage: php dev-check.php
 */

echo "\n";
echo "=====================================\n";
echo "  Faveo Development Environment Check\n";
echo "=====================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// Check PHP Version
echo "Checking PHP version...\n";
$phpVersion = PHP_VERSION;
$minPhpVersion = '8.1.0';
if (version_compare($phpVersion, $minPhpVersion, '>=')) {
    $success[] = "✓ PHP version $phpVersion (>= $minPhpVersion required)";
} else {
    $errors[] = "✗ PHP version $phpVersion is too old. Need >= $minPhpVersion";
}

// Check Required PHP Extensions
echo "Checking PHP extensions...\n";
$requiredExtensions = [
    'pdo',
    'mbstring',
    'tokenizer',
    'xml',
    'zip',
    'openssl',
    'json',
    'curl',
];

$optionalExtensions = [
    'imap',
    'mcrypt',
    'gd',
    'redis',
];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        $success[] = "✓ Extension '$ext' is loaded";
    } else {
        $errors[] = "✗ Extension '$ext' is NOT loaded (required)";
    }
}

foreach ($optionalExtensions as $ext) {
    if (extension_loaded($ext)) {
        $success[] = "✓ Extension '$ext' is loaded";
    } else {
        $warnings[] = "⚠ Extension '$ext' is NOT loaded (optional but recommended)";
    }
}

// Check Composer
echo "Checking Composer...\n";
exec('composer --version 2>&1', $composerOutput, $composerReturn);
if ($composerReturn === 0) {
    $success[] = "✓ Composer is installed: " . trim($composerOutput[0]);
} else {
    $errors[] = "✗ Composer is NOT installed";
}

// Check Node.js and NPM
echo "Checking Node.js and NPM...\n";
exec('node --version 2>&1', $nodeOutput, $nodeReturn);
if ($nodeReturn === 0) {
    $success[] = "✓ Node.js is installed: " . trim($nodeOutput[0]);
} else {
    $warnings[] = "⚠ Node.js is NOT installed (needed for frontend assets)";
}

exec('npm --version 2>&1', $npmOutput, $npmReturn);
if ($npmReturn === 0) {
    $success[] = "✓ NPM is installed: v" . trim($npmOutput[0]);
} else {
    $warnings[] = "⚠ NPM is NOT installed (needed for frontend assets)";
}

// Check Git
echo "Checking Git...\n";
exec('git --version 2>&1', $gitOutput, $gitReturn);
if ($gitReturn === 0) {
    $success[] = "✓ Git is installed: " . trim($gitOutput[0]);
} else {
    $warnings[] = "⚠ Git is NOT installed (needed for version control)";
}

// Check if vendor directory exists
echo "Checking dependencies...\n";
if (is_dir(__DIR__ . '/vendor')) {
    $success[] = "✓ Composer dependencies are installed (vendor/ exists)";
} else {
    $warnings[] = "⚠ Composer dependencies not installed. Run: composer install";
}

// Check if node_modules directory exists
if (is_dir(__DIR__ . '/node_modules')) {
    $success[] = "✓ NPM dependencies are installed (node_modules/ exists)";
} else {
    $warnings[] = "⚠ NPM dependencies not installed. Run: npm install";
}

// Check .env file
echo "Checking environment configuration...\n";
if (file_exists(__DIR__ . '/.env')) {
    $success[] = "✓ Environment file (.env) exists";
    
    // Check for APP_KEY
    $envContent = file_get_contents(__DIR__ . '/.env');
    if (preg_match('/APP_KEY=base64:[\w+\/]+=*/', $envContent)) {
        $success[] = "✓ Application key (APP_KEY) is set";
    } else {
        $warnings[] = "⚠ Application key not set. Run: php artisan key:generate";
    }
} else {
    $warnings[] = "⚠ Environment file (.env) not found. Copy .env.example to .env";
}

// Check storage permissions
echo "Checking directory permissions...\n";
$storageDir = __DIR__ . '/storage';
$bootstrapCacheDir = __DIR__ . '/bootstrap/cache';

if (is_writable($storageDir)) {
    $success[] = "✓ Storage directory is writable";
} else {
    $errors[] = "✗ Storage directory is NOT writable. Run: chmod -R 775 storage";
}

if (is_writable($bootstrapCacheDir)) {
    $success[] = "✓ Bootstrap cache directory is writable";
} else {
    $errors[] = "✗ Bootstrap cache directory is NOT writable. Run: chmod -R 775 bootstrap/cache";
}

// Print Results
echo "\n";
echo "=====================================\n";
echo "  Results\n";
echo "=====================================\n\n";

if (!empty($success)) {
    echo "SUCCESS (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "ERRORS (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

// Summary
echo "=====================================\n";
echo "  Summary\n";
echo "=====================================\n";
if (empty($errors)) {
    echo "✓ Your environment looks good!\n";
    if (!empty($warnings)) {
        echo "⚠ There are some optional warnings to address.\n";
    }
    echo "\nNext steps:\n";
    echo "1. Configure your .env file with database settings\n";
    echo "2. Run: php artisan migrate\n";
    echo "3. Run: npm run dev\n";
    echo "4. Run: php artisan serve\n";
    echo "\nFor more information, see DEVELOPER_GUIDE.md\n";
} else {
    echo "✗ Please fix the errors above before continuing.\n";
    echo "\nFor help, see DEVELOPER_GUIDE.md\n";
}
echo "=====================================\n\n";

exit(empty($errors) ? 0 : 1);

<?php
// config/App.php

// Load .env file into $_ENV
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        [$name, $value] = explode('=', $line, 2);
        $_ENV[$name] = trim($value);
    }
}

/**
 * Get an environment variable by key.
 *
 * @param string $key     The environment variable name.
 * @param mixed  $default The default value if the key does not exist.
 *
 * @return mixed The value of the environment variable or the default.
 */
function env($key, $default = null)
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

/**
 * Generate the full base URL with an optional path.
 *
 * @param string $path Optional path to append to the base URL.
 *
 * @return string The complete URL.
 */
function base_url($path = '')
{
    $base = rtrim($_ENV['APP_URL'] ?? env('APP_URL'), '/');
    return $base . '/' . ltrim($path, '/');
}

/**
 * Dump one or more variables and terminate the script.
 * 
 * @param mixed ...$vars The variables to dump.
 *
 * @return void
 */
function dd(...$vars)
{
    foreach ($vars as $var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
    die();
}

/**
 * Escape a string for safe HTML output.
 *
 * @param string $string The string to escape.
 *
 * @return string The escaped string.
 */
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Log an error message into a log file.
 *
 * @param string $message The error message to log.
 *
 * @return void
 */
function log_error($message)
{
    // Default to "logs" directory if LOG_PATH is not set
    $logDir = __DIR__ . '/../' . (env('LOG_PATH', 'logs'));

    // Ensure logs directory exists
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $file = $logDir . '/errors.log';
    $date = date('Y-m-d H:i:s');

    // Format: [2025-09-15 12:00:00] ERROR: message
    $entry = "[$date] ERROR: $message" . PHP_EOL;

    file_put_contents($file, $entry, FILE_APPEND);
}
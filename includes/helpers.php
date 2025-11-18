<?php
// includes/helpers.php

/**
 * Unified image URL helpers
 * Place identical copy in /includes/helpers.php and /admin/includes/helpers.php
 */


/**
 * Unified image URL helpers (safe & minimal)
 */

if (!function_exists('project_base_url')) {
    function project_base_url(): string {
        $is_https = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        );
        $scheme = $is_https ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');

        $script_dir = isset($_SERVER['SCRIPT_NAME'])
            ? rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/')
            : '';
        if ($script_dir === '/') { $script_dir = ''; }

        $project_dir = preg_replace('#/admin($|/.*)#i', '', $script_dir);

        return rtrim($scheme . '://' . $host . $project_dir, '/');
    }
}

if (!function_exists('base_url')) {
    function base_url(): string {
        return project_base_url();
    }
}

/**
 * Build a safe absolute URL for an image.
 * Does not rewrite DB paths (no forced admin/ stripping).
 */
if (!function_exists('build_image_url')) {
    function build_image_url($path) {
    $path = trim((string)$path);
    $path = str_replace(["\r", "\n"], '', $path);
    if ($path === '') {
        return '/assets/img/placeholder.png';
    }
    // Normalize slashes
    $path = str_replace('\\', '/', $path);
    // If it already looks like an absolute URL, leave it alone
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    // Ensure starts at web root, not relative to /admin/
    if ($path[0] !== '/') {
        $path = '/' . ltrim($path, '/');
    }
    return $path;
}

}

if (!function_exists('resolve_admin_image_url')) {
    function resolve_admin_image_url(?string $path): string {
        return build_image_url($path);
    }
}

// Clean CKEditor or textarea input before saving to DB
function clean_editor_input($html, $allowed_tags = '') {
    $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
    $clean = strip_tags($html, $allowed_tags);
    $clean = preg_replace('/(\r\n|\n|\r)+$/', '', $clean);
    $clean = trim($clean);
    return $clean;
}

function display_editor_content($text) {
    // Remove any leftover rn or linebreaks
    $text = str_replace(array("\\r\\n", "rn", "\r", "\n"), ' ', $text);
    // Collapse spaces
    $text = preg_replace('/\s+/', ' ', $text);
    return $text;
}
?>

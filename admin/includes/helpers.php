<?php
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
    // Convert <br> to space
    $html = preg_replace('/<br\s*\/?>/i', ' ', $html);

    // Replace literal 'rn' and '\r\n' with a space
    $html = str_replace(array("\\r\\n", "rn", "\r", "\n"), ' ', $html);

    // Strip unwanted tags
    $clean = strip_tags($html, $allowed_tags);

    // Collapse multiple spaces
    $clean = preg_replace('/\s+/', ' ', $clean);

    return trim($clean);
}


?>
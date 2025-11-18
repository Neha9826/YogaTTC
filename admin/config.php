<?php
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    define('BASE_URL', 'http://localhost/ShivohamRetreat/admin/');
} else {
    define('BASE_URL', 'https://shivohamretreat.com/admin/');
}

// Absolute path for server-side includes
if (!defined('BASE_PATH')) {
    define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/ShivohamRetreat/admin/');
}

// Subfolders for assets (safe for deep nested files)
if (!defined('CSS_URL')) {
    define('CSS_URL', BASE_URL . 'css/');
}
if (!defined('JS_URL')) {
    define('JS_URL', BASE_URL . 'js/');
}
if (!defined('IMG_URL')) {
    define('IMG_URL', BASE_URL . 'img/');
}

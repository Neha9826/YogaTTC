<?php
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    define('BASE_URL', 'http://localhost/ShivohamRetreat/');
} else {
    define('BASE_URL', 'https://shivohamretreat.com/');
}

define('YOGA_URL', BASE_URL . 'yoga/');
define('UPLOADS_URL', BASE_URL . 'uploads/');

<?php
// Konfigurasi Aplikasi Inventory Management System - Bank BTN

define('APP_NAME', 'Inventory Management System');
define('APP_SHORT', 'IMS BTN');
define('APP_VERSION', '1.0.0');

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'ims_btn');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Path
define('BASE_URL', 'http://localhost/ims-btn');
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

// Session
define('SESSION_TIMEOUT', 1800); // 30 menit
define('SESSION_NAME', 'IMS_BTN_SESSION');

// Pagination
define('PER_PAGE', 10);

// Upload config
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_FILE_EXT', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

date_default_timezone_set('Asia/Jakarta');

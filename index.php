<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) {
    redirect('pages/dashboard.php');
} else {
    redirect('login.php');
}

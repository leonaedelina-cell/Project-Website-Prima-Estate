<?php
/**
 * logout.php - Estate Prima
 * Hancurkan session lalu balikin ke homepage.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: ' . BASE_URL . 'index.php');
	exit;
}
cek_csrf();

$_SESSION = [];
session_destroy();

header('Location: ' . BASE_URL . 'index.php');
exit;

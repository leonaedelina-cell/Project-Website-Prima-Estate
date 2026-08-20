<?php
/**
 * logout.php - Estate Prima
 * Hancurkan session lalu balikin ke homepage.
 */

session_start();
require_once __DIR__ . '/config/database.php';
$_SESSION = [];
session_destroy();

header('Location: ' . BASE_URL . 'index.php');
exit;

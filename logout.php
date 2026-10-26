<?php
require_once __DIR__ . '/includes/auth.php';
session_destroy();
header('Location: /otono-makeup/index.php'); exit;
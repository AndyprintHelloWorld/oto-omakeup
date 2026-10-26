<?php
if (PHP_SAPI !== 'cli') {
	http_response_code(404);
	exit;
}

require_once __DIR__ . '/config/db.php';

$email = getenv('ADMIN_EMAIL') ?: '';
$password = getenv('ADMIN_PASSWORD') ?: '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
	fwrite(STDERR, "Define ADMIN_EMAIL y ADMIN_PASSWORD (minimo 12 caracteres) antes de ejecutar este script.\n");
	exit(1);
}

$db = getDB();
$hash = password_hash($password, PASSWORD_DEFAULT);
$st = $db->prepare("INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), password=VALUES(password), rol='admin'");
$st->execute(['Administrador', $email, $hash, 'admin']);
fwrite(STDOUT, "Administrador creado o actualizado: $email\n");
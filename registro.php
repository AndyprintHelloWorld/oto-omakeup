<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
$err=''; $ok='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_verify();
  $nombre=trim($_POST['nombre']??''); $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
  if($nombre===''||$email===''||strlen($pass)<6){ $err='Datos invalidos'; }
  else {
    $db=getDB();
    $st=$db->prepare('SELECT id FROM usuarios WHERE email=?'); $st->execute([$email]);
    if($st->fetch()){ $err='Email ya registrado'; }
    else {
      $hash=password_hash($pass,PASSWORD_DEFAULT);
      $st=$db->prepare('INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,?)');
      $st->execute([$nombre,$email,$hash,'cliente']);
      $ok='Registrado. Puedes iniciar sesión.';
    }
  }
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Registro</title><link rel="stylesheet" href="/otono-makeup/assets/css/style.css"><script src="/otono-makeup/assets/js/password-visibility.js" defer></script></head><body>
<?php include __DIR__ . '/includes/store-header.php'; ?>
<form method="post"><?php echo csrf_field(); ?>
<h2>Registro</h2>
<?php if($err) echo '<div class="alert alert-err">'.e($err).'</div>'; ?>
<?php if($ok) echo '<div class="alert alert-ok">'.e($ok).'</div>'; ?>
<input name="nombre" placeholder="Nombre" required>
<input name="email" type="email" placeholder="Correo" required>
<input id="register-password" name="password" type="password" autocomplete="new-password" placeholder="Contraseña (min 6)" required>
<label class="password-visibility" for="show-register-password"><input id="show-register-password" type="checkbox" aria-controls="register-password" data-password-toggle>Mostrar contraseña</label>
<button class="btn" type="submit">Registrarse</button>
</form></body></html>
<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_verify();
  $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
  $db=getDB(); $st=$db->prepare('SELECT * FROM usuarios WHERE email=?'); $st->execute([$email]); $u=$st->fetch();
  if($u && password_verify($pass,$u['password'])){
    session_regenerate_id(true);
    $_SESSION['user_id']=$u['id']; $_SESSION['user_rol']=$u['rol'];
    $destination = $u['rol'] === 'admin' ? '/otono-makeup/admin/index.php' : '/otono-makeup/index.php';
    header('Location: ' . $destination); exit;
  } else $err='Credenciales invalidas';
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Login</title><link rel="stylesheet" href="/otono-makeup/assets/css/style.css"></head><body>
<?php include __DIR__ . '/includes/store-header.php'; ?>
<form method="post"><?php echo csrf_field(); ?>
<h2>Iniciar sesión</h2>
<?php if($err) echo '<div class="alert alert-err">'.e($err).'</div>'; ?>
<input name="email" type="email" placeholder="Correo" required>
<input name="password" type="password" placeholder="Contraseña" required>
<button class="btn" type="submit">Entrar</button>
<p><a href="/otono-makeup/registro.php">Crear cuenta</a></p>
</form></body></html>
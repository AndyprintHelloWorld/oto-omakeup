<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
$db=getDB(); $id=(int)($_GET['id']??0);
$st=$db->prepare('SELECT p.*,c.nombre cat FROM productos p LEFT JOIN categorias c ON p.categoria_id=c.id WHERE p.id=?'); $st->execute([$id]); $p=$st->fetch();
if(!$p){ die('No encontrado'); }
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add'])){
  csrf_verify(); requireLogin();
  $cant=max(1,(int)$_POST['cantidad']);
  if(!isset($_SESSION['carrito'])) $_SESSION['carrito']=[];
  $_SESSION['carrito'][$id]=($_SESSION['carrito'][$id]??0)+$cant;
  header('Location: /otono-makeup/carrito.php'); exit;
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title><?php echo e($p['nombre']);?></title><link rel="stylesheet" href="/otono-makeup/assets/css/style.css"></head><body>
<?php include __DIR__ . '/includes/store-header.php'; ?>
<main class="container">
<div class="card" style="max-width:500px;margin:auto">
<div class="img" style="height:220px"><?php echo $p['imagen']?'<img src="/otono-makeup/uploads/'.e($p['imagen']).'">':'🌸';?></div>
<h2><?php echo e($p['nombre']);?></h2><p><?php echo e($p['descripcion']);?></p>
<p class="cat"><?php echo e($p['cat']);?></p><p class="price"><?php echo shopFormatPrice($p['precio']);?></p>
<p class="stock">Stock: <?php echo (int)$p['existencias'];?></p>
<?php if(isLoggedIn()): ?>
<form method="post"><?php echo csrf_field(); ?><input type="hidden" name="add" value="1"><input name="cantidad" type="number" min="1" max="<?php echo (int)$p['existencias'];?>" value="1"><button class="btn" type="submit">Agregar al carrito</button></form>
<?php else: ?><p><a href="/otono-makeup/login.php">Inicia sesión para comprar</a></p><?php endif; ?>
</div></main></body></html>
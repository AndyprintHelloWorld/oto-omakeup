<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
requireLogin();
$db=getDB();
$carrito=$_SESSION['carrito']??[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_verify();
  if(isset($_POST['remove'])){ unset($_SESSION['carrito'][(int)$_POST['remove']]); }
  if(isset($_POST['update'])){
    $id=(int)$_POST['pid']; $q=max(0,(int)$_POST['qty']);
    if($q<=0) unset($_SESSION['carrito'][$id]); else $_SESSION['carrito'][$id]=$q;
  }
  header('Location: /otono-makeup/carrito.php'); exit;
}
$items=[]; $total=0;
foreach($carrito as $pid=>$q){
  $st=$db->prepare('SELECT * FROM productos WHERE id=?'); $st->execute([$pid]); $p=$st->fetch();
  if($p){ $sub=$p['precio']*$q; $total+=$sub; $items[]=[$p,$q,$sub]; }
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Carrito</title><link rel="stylesheet" href="/otono-makeup/assets/css/style.css"></head><body>
<?php include __DIR__ . '/includes/store-header.php'; ?>
<main class="container">
<h2>Carrito</h2>
<?php if(empty($items)): ?><p>Carrito vacío.</p><?php else: ?>
<table><tr><th>Producto</th><th>Precio</th><th>Cant</th><th>Subtotal</th><th></th></tr>
<?php foreach($items as [$p,$q,$sub]): ?>
<tr>
<td><?php echo e($p['nombre']);?></td>
<td><?php echo shopFormatPrice($p['precio']);?></td>
<td><form method="post" style="box-shadow:none;background:transparent;padding:0;max-width:none;display:inline"><?php echo csrf_field();?><input type="hidden" name="pid" value="<?php echo $p['id'];?>"><input name="qty" type="number" min="0" value="<?php echo $q;?>" style="width:60px"><button class="btn" name="update" value="1">OK</button></form></td>
<td><?php echo shopFormatPrice($sub);?></td>
<td><form method="post" style="box-shadow:none;background:transparent;padding:0;max-width:none;display:inline"><?php echo csrf_field();?><button class="btn" name="remove" value="<?php echo $p['id'];?>">X</button></form></td>
</tr>
<?php endforeach; ?>
</table>
<p><strong>Total: <?php echo shopFormatPrice($total);?></strong></p>
<a class="btn btn-gold" href="/otono-makeup/checkout.php">Confirmar pedido</a>
<?php endif; ?>
</main></body></html>
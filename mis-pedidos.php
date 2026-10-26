<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
requireLogin();
$db=getDB();
$st=$db->prepare('SELECT * FROM pedidos WHERE usuario_id=? ORDER BY id DESC'); $st->execute([$_SESSION['user_id']]); $pedidos=$st->fetchAll();
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Mis pedidos</title><link rel="stylesheet" href="/otono-makeup/assets/css/style.css"></head><body>
<?php include __DIR__ . '/includes/store-header.php'; ?>
<main class="container">
<h2>Mis pedidos</h2>
<table><tr><th>#</th><th>Fecha</th><th>Total</th><th>Estado</th><th>Detalle</th></tr>
<?php foreach($pedidos as $p): ?>
<tr><td><?php echo $p['id'];?></td><td><?php echo $p['fecha'];?></td><td><?php echo shopFormatPrice($p['total']);?></td><td><?php echo e($p['estado']);?></td>
<td><a href="/otono-makeup/pedido.php?id=<?php echo $p['id'];?>">Ver</a></td></tr>
<?php endforeach; ?>
</table></main></body></html>
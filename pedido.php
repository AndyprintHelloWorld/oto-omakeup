<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
requireLogin();
$db=getDB(); $id=(int)($_GET['id']??0);
$st=$db->prepare('SELECT * FROM pedidos WHERE id=? AND usuario_id=?'); $st->execute([$id,$_SESSION['user_id']]); $p=$st->fetch();
if(!$p){ die('No encontrado'); }
$st=$db->prepare('SELECT d.*,pr.nombre FROM detalle_pedido d JOIN productos pr ON d.producto_id=pr.id WHERE d.pedido_id=?'); $st->execute([$id]); $det=$st->fetchAll();
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Pedido</title><link rel="stylesheet" href="/otono-makeup/assets/css/style.css"></head><body>
<?php include __DIR__ . '/includes/store-header.php'; ?>
<main class="container">
<h2>Pedido #<?php echo $p['id'];?></h2><p>Estado: <?php echo e($p['estado']);?> | Total: <?php echo shopFormatPrice($p['total']);?></p>
<?php if(!empty($p['destinatario'])): ?>
<section class="delivery-summary">
<h3>Datos de entrega</h3>
<p><strong>Recibe:</strong> <?php echo e($p['destinatario']);?> · <?php echo e($p['telefono']);?></p>
<p><strong>Dirección:</strong> <?php echo e($p['direccion']);?>, <?php echo e($p['ciudad']);?>, <?php echo e($p['region']);?>, <?php echo e($p['codigo_postal']);?>, <?php echo e(shopRegions()[$p['pais']]['label'] ?? $p['pais']);?></p>
<?php if(!empty($p['referencia'])): ?><p><strong>Referencia:</strong> <?php echo e($p['referencia']);?></p><?php endif; ?>
<p><strong>Pago:</strong> <?php echo e(shopPaymentMethods()[$p['metodo_pago']] ?? 'No especificado');?></p>
</section>
<?php endif; ?>
<table><tr><th>Producto</th><th>Cant</th><th>Precio</th><th>Subtotal</th></tr>
<?php foreach($det as $d): ?><tr><td><?php echo e($d['nombre']);?></td><td><?php echo $d['cantidad'];?></td><td><?php echo shopFormatPrice($d['precio_unitario']);?></td><td><?php echo shopFormatPrice($d['subtotal']);?></td></tr><?php endforeach;?>
</table></main></body></html>
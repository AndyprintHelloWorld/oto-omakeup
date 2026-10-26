<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
$db=getDB();
$cats=$db->query("SELECT c.id,c.nombre,COUNT(p.id) AS producto_count FROM categorias c LEFT JOIN productos p ON p.categoria_id=c.id AND p.estado='activo' GROUP BY c.id,c.nombre ORDER BY c.nombre")->fetchAll();
$q=trim($_GET['q']??''); $cat=(int)($_GET['cat']??0);
$sql="SELECT p.*,c.nombre cat FROM productos p LEFT JOIN categorias c ON p.categoria_id=c.id WHERE p.estado='activo'";
$params=[];
if($q!==''){ $sql.=" AND p.nombre LIKE ?"; $params[]="%$q%"; }
if($cat>0){ $sql.=" AND p.categoria_id=?"; $params[]=$cat; }
$sql.=" ORDER BY p.id DESC";
$st=$db->prepare($sql); $st->execute($params); $prods=$st->fetchAll();
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Catálogo</title><link rel="stylesheet" href="/otono-makeup/assets/css/style.css"></head><body>
<?php include __DIR__ . '/includes/store-header.php'; ?>
<main class="container">
<details class="category-browser">
<summary><span>Explorar categorías</span><span><?php echo count($cats); ?> categorías</span></summary>
<nav class="category-links" aria-label="Categorías del catálogo">
<a class="category-chip<?php echo $cat===0?' is-current':''; ?>" href="/otono-makeup/catalogo.php">Todas<span><?php echo array_sum(array_column($cats,'producto_count')); ?></span></a>
<?php foreach($cats as $c): ?>
<a class="category-chip<?php echo $cat===(int)$c['id']?' is-current':''; ?>" href="/otono-makeup/catalogo.php?cat=<?php echo (int)$c['id']; ?>"><?php echo e($c['nombre']); ?><span><?php echo (int)$c['producto_count']; ?></span></a>
<?php endforeach; ?>
</nav>
</details>
<form class="filter-bar" method="get">
<input name="q" placeholder="Buscar..." value="<?php echo e($q); ?>">
<select name="cat"><option value="0">Todas las categorías</option><?php foreach($cats as $c):?><option value="<?php echo $c['id'];?>" <?php if($cat==$c['id'])echo'selected';?>><?php echo e($c['nombre']);?></option><?php endforeach;?></select>
<button class="btn" type="submit">Filtrar</button>
</form>
<div class="grid catalog-grid">
<?php foreach($prods as $p): ?>
<div class="card">
<div class="img"><?php echo $p['imagen']?'<img src="/otono-makeup/uploads/'.e($p['imagen']).'">':'🌸';?></div>
<h3><?php echo e($p['nombre']);?></h3><p class="cat"><?php echo e($p['cat']);?></p>
<p class="price"><?php echo shopFormatPrice($p['precio']);?></p><p class="stock">Stock: <?php echo (int)$p['existencias'];?></p>
<a class="btn" href="/otono-makeup/producto.php?id=<?php echo $p['id'];?>">Ver</a>
</div><?php endforeach;?>
</div></main></body></html>
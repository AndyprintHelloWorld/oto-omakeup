<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
$db = getDB();
$prods = $db->query("SELECT p.*,c.nombre cat FROM productos p LEFT JOIN categorias c ON p.categoria_id=c.id WHERE p.estado='activo' ORDER BY p.id DESC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Otoño Makeup</title>
<link rel="stylesheet" href="/otono-makeup/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/store-header.php'; ?>
<section class="hero">
  <div class="hero-content">
    <p class="hero-kicker">Belleza para cada versión de ti</p>
    <h1>Otoño Makeup</h1>
    <p class="slogan">Realza tu belleza. Expresa tu esencia.</p>
    <a class="btn hero-cta" href="/otono-makeup/catalogo.php">Descubre la colección <span aria-hidden="true">&rarr;</span></a>
  </div>
</section>
<main class="container">
  <div class="section-heading">
    <div>
      <p class="eyebrow">Encuentra tus favoritos</p>
      <h2>Productos destacados</h2>
    </div>
    <a class="section-link" href="/otono-makeup/catalogo.php">Ver catálogo <span aria-hidden="true">&rarr;</span></a>
  </div>
  <div class="grid">
    <?php foreach($prods as $p): ?>
    <div class="card">
      <div class="img<?php echo $p['imagen'] ? '' : ' img-placeholder'; ?>"><?php echo $p['imagen']? '<img src="/otono-makeup/uploads/'.e($p['imagen']).'">' : '<span class="placeholder-label">'.e($p['cat']).'</span>'; ?></div>
      <h3><?php echo e($p['nombre']); ?></h3>
      <p class="cat"><?php echo e($p['cat']); ?></p>
      <p class="price"><?php echo shopFormatPrice($p['precio']); ?></p>
      <p class="stock">Stock: <?php echo (int)$p['existencias']; ?></p>
      <a class="btn" href="/otono-makeup/producto.php?id=<?php echo $p['id']; ?>">Ver producto <span aria-hidden="true">&rarr;</span></a>
    </div>
    <?php endforeach; ?>
  </div>
</main>
<footer>© Otoño Makeup</footer>
</body></html>
<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
requireLogin();
$db=getDB();
$delivery = [
  'destinatario' => '',
  'telefono' => '',
  'pais' => shopCurrentRegion(),
  'region' => '',
  'ciudad' => '',
  'codigo_postal' => '',
  'direccion' => '',
  'referencia' => '',
  'metodo_pago' => 'contra_entrega',
];
$paymentMethods = shopPaymentMethods();
$msg=''; $ok=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_verify();
  foreach ($delivery as $field => $default) {
    $postedValue = $_POST[$field] ?? $default;
    $delivery[$field] = is_string($postedValue) ? trim($postedValue) : $default;
  }
  $carrito=$_SESSION['carrito']??[];
  if(empty($carrito)){ $msg='Carrito vacio'; }
  else {
    $requiredFields = [
      'destinatario' => 'nombre de quien recibe',
      'telefono' => 'teléfono',
      'region' => 'estado o región',
      'ciudad' => 'ciudad',
      'codigo_postal' => 'código postal',
      'direccion' => 'dirección',
    ];
    $missingFields = [];
    foreach ($requiredFields as $field => $label) {
      if ($delivery[$field] === '') $missingFields[] = $label;
    }
    $regions = shopRegions();
    if (!isset($regions[$delivery['pais']])) $missingFields[] = 'país válido';
    if (!isset($paymentMethods[$delivery['metodo_pago']])) $missingFields[] = 'método de pago válido';
    $maxLengths = ['destinatario' => 120, 'telefono' => 30, 'region' => 120, 'ciudad' => 120, 'codigo_postal' => 20, 'direccion' => 255, 'referencia' => 255];
    foreach ($maxLengths as $field => $maxLength) {
      if (strlen($delivery[$field]) > $maxLength) $missingFields[] = 'un dato supera el largo permitido';
    }
    if ($missingFields) {
      $msg = 'Revisa los datos requeridos: ' . implode(', ', array_unique($missingFields)) . '.';
    } else {
    try{
      $db->beginTransaction();
      $total=0; $items=[];
      foreach($carrito as $pid=>$q){
        $st=$db->prepare('SELECT * FROM productos WHERE id=? FOR UPDATE'); $st->execute([$pid]); $p=$st->fetch();
        if(!$p || $p['estado']!=='activo') throw new Exception('Producto no disponible');
        if($q>$p['existencias']) throw new Exception('Stock insuficiente: '.$p['nombre']);
        $sub=$p['precio']*$q; $total+=$sub;
        $items[]=[$p,$q,$sub];
      }
      $st=$db->prepare('INSERT INTO pedidos (usuario_id,total,estado,destinatario,telefono,pais,region,ciudad,codigo_postal,direccion,referencia,metodo_pago) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
      $st->execute([$_SESSION['user_id'],$total,'confirmado',$delivery['destinatario'],$delivery['telefono'],$delivery['pais'],$delivery['region'],$delivery['ciudad'],$delivery['codigo_postal'],$delivery['direccion'],$delivery['referencia'] ?: null,$delivery['metodo_pago']]);
      $pedidoId=$db->lastInsertId();
      foreach($items as [$p,$q,$sub]){
        $st=$db->prepare('INSERT INTO detalle_pedido (pedido_id,producto_id,cantidad,precio_unitario,subtotal) VALUES (?,?,?,?,?)');
        $st->execute([$pedidoId,$p['id'],$q,$p['precio'],$sub]);
        $st=$db->prepare('UPDATE productos SET existencias=existencias-? WHERE id=?');
        $st->execute([$q,$p['id']]);
        $st=$db->prepare('INSERT INTO movimientos_inventario (producto_id,tipo,cantidad,motivo,usuario_id) VALUES (?,?,?,?,?)');
        $st->execute([$p['id'],'salida',$q,'Venta pedido #'.$pedidoId,$_SESSION['user_id']]);
      }
      $db->commit();
      unset($_SESSION['carrito']);
      $ok=true; $msg='Pedido #'.$pedidoId.' confirmado. Total '.shopFormatPrice($total);
    }catch(Exception $e){
      $db->rollBack(); $msg=$e->getMessage();
    }
    }
  }
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Checkout</title><link rel="stylesheet" href="/otono-makeup/assets/css/style.css"></head><body>
<?php include __DIR__ . '/includes/store-header.php'; ?>
<main class="container">
<h2>Confirmar pedido</h2>
<?php if($msg) echo '<div class="alert '.($ok?'alert-ok':'alert-err').'">'.e($msg).'</div>'; ?>
<?php if($ok): ?><a class="btn" href="/otono-makeup/mis-pedidos.php">Ver mis pedidos</a><?php elseif(empty($_SESSION['carrito'])): ?>
<p>Tu carrito está vacío.</p><a class="btn" href="/otono-makeup/catalogo.php">Volver al catálogo</a>
<?php else: ?>
<form class="checkout-form" method="post">
<?php echo csrf_field(); ?>
<h2>Datos de entrega</h2>
<div class="checkout-fields">
  <label class="checkout-field" for="delivery-recipient">Nombre de quien recibe
    <input id="delivery-recipient" name="destinatario" autocomplete="name" maxlength="120" value="<?php echo e($delivery['destinatario']); ?>" required>
  </label>
  <label class="checkout-field" for="delivery-phone">Teléfono
    <input id="delivery-phone" name="telefono" type="tel" autocomplete="tel" maxlength="30" value="<?php echo e($delivery['telefono']); ?>" required>
  </label>
  <label class="checkout-field" for="delivery-country">País
    <select id="delivery-country" name="pais" autocomplete="country" required>
      <?php foreach (shopRegions() as $countryCode => $country): ?>
        <option value="<?php echo e($countryCode); ?>" <?php echo $delivery['pais'] === $countryCode ? 'selected' : ''; ?>><?php echo e($country['label']); ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label class="checkout-field" for="delivery-region">Estado / provincia / región
    <input id="delivery-region" name="region" autocomplete="address-level1" maxlength="120" value="<?php echo e($delivery['region']); ?>" required>
  </label>
  <label class="checkout-field" for="delivery-city">Ciudad
    <input id="delivery-city" name="ciudad" autocomplete="address-level2" maxlength="120" value="<?php echo e($delivery['ciudad']); ?>" required>
  </label>
  <label class="checkout-field" for="delivery-postal">Código postal
    <input id="delivery-postal" name="codigo_postal" autocomplete="postal-code" maxlength="20" value="<?php echo e($delivery['codigo_postal']); ?>" required>
  </label>
  <label class="checkout-field checkout-field-wide" for="delivery-address">Dirección y número
    <input id="delivery-address" name="direccion" autocomplete="address-line1" maxlength="255" value="<?php echo e($delivery['direccion']); ?>" required>
  </label>
  <label class="checkout-field checkout-field-wide" for="delivery-reference">Referencias de entrega (opcional)
    <input id="delivery-reference" name="referencia" maxlength="255" value="<?php echo e($delivery['referencia']); ?>">
  </label>
</div>
<fieldset class="payment-options">
  <legend>Método de pago</legend>
  <?php foreach ($paymentMethods as $methodCode => $methodLabel): ?>
    <label class="payment-option"><input type="radio" name="metodo_pago" value="<?php echo e($methodCode); ?>" <?php echo $delivery['metodo_pago'] === $methodCode ? 'checked' : ''; ?> required><span><?php echo e($methodLabel); ?></span></label>
  <?php endforeach; ?>
</fieldset>
<p class="checkout-note">Compra de prueba: no se realiza ningún cargo ni se solicitan datos de tarjeta. Los pedidos se registran en MXN.</p>
<button class="btn btn-gold" type="submit">Confirmar pedido</button>
</form>
<?php endif; ?>
</main></body></html>
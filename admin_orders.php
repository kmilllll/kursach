<?php
require_once 'config.php';$admin_id=require_admin($conn);
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['change_status'])){
 check_csrf();$id=(int)($_POST['id']??0);$next=$_POST['status']??'';
 if(!in_array($next,['pending','completed','cancelled'],true)){flash('error','Невідомий статус замовлення.');redirect_to('admin_orders.php');}
 try{
  $conn->begin_transaction();
  $q=$conn->prepare('SELECT payment_status FROM orders WHERE id=? FOR UPDATE');$q->bind_param('i',$id);$q->execute();$order=$q->get_result()->fetch_assoc();
  if(!$order)throw new RuntimeException('Замовлення не знайдено.');
  $prev=$order['payment_status'];
  if($prev!==$next&&table_exists($conn,'order_items')){
   $q=$conn->prepare('SELECT product_id AS pid,quantity,custom_data FROM order_items WHERE order_id=?');$q->bind_param('i',$id);$q->execute();$items=$q->get_result()->fetch_all(MYSQLI_ASSOC);
   if($items){$needs=bouquet_needs($items);
     if($next==='cancelled'&&$prev!=='cancelled')change_stock($conn,$needs,false);
     if($prev==='cancelled'&&$next!=='cancelled')change_stock($conn,$needs,true);
   }
  }
  $q=$conn->prepare('UPDATE orders SET payment_status=? WHERE id=?');$q->bind_param('si',$next,$id);$q->execute();$conn->commit();flash('success','Статус замовлення оновлено.');
 }catch(Throwable $e){$conn->rollback();flash('error','Не вдалося оновити: '.$e->getMessage());}
 redirect_to('admin_orders.php');
}
$status=$_GET['status']??'all';if(!in_array($status,['all','pending','completed','cancelled'],true))$status='all';
if($status==='all')$orders=$conn->query('SELECT * FROM orders ORDER BY id DESC LIMIT 150')->fetch_all(MYSQLI_ASSOC);
else{$q=$conn->prepare('SELECT * FROM orders WHERE payment_status=? ORDER BY id DESC LIMIT 150');$q->bind_param('s',$status);$q->execute();$orders=$q->get_result()->fetch_all(MYSQLI_ASSOC);}
?>
<!doctype html><html lang="uk"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Замовлення · flora.atelier admin</title><link rel="stylesheet" href="css/admin_style.css"><link rel="stylesheet" href="css/flor-admin-v8.css"></head><body><?php require 'admin_header.php';?><div class="admin-page-head"><div><span class="admin-eyebrow">ЗАМОВЛЕННЯ МАГАЗИНУ</span><h1>Замовлення <em>◇</em></h1><p>Переглядай деталі, керуй статусами та запасами.</p></div></div><div class="admin-tabs top-tabs"><a href="?status=all" class="<?=$status==='all'?'active':''?>">Усі</a><a href="?status=pending" class="<?=$status==='pending'?'active':''?>">Очікують</a><a href="?status=completed" class="<?=$status==='completed'?'active':''?>">Виконані</a><a href="?status=cancelled" class="<?=$status==='cancelled'?'active':''?>">Скасовані</a></div>
<div class="order-admin-grid"><?php if(!$orders):?><div class="admin-card admin-empty">У цій категорії замовлень поки немає.</div><?php endif;?><?php foreach($orders as $o):?><article class="admin-card order-admin-card"><div class="card-heading"><div><span class="admin-eyebrow">ЗАМОВЛЕННЯ №<?=(int)$o['id']?></span><h2><?=e($o['name'])?></h2></div><span class="status-pill <?=e($o['payment_status'])?>"><?=e(['pending'=>'Очікує','completed'=>'Завершено','cancelled'=>'Скасовано'][$o['payment_status']]??$o['payment_status'])?></span></div><div class="order-admin-details"><p><b>Створено:</b> <?=e($o['placed_on'])?></p><p><b>Email:</b> <a href="mailto:<?=e($o['email'])?>"><?=e($o['email'])?></a></p><p><b>Телефон:</b> <?=e($o['number'])?></p><p><b>Доставка:</b> <?=e($o['address'])?></p><p><b>Оплата:</b> <?=e($o['method'])?></p><div class="order-products"><b>Склад замовлення</b><p><?=nl2br(e($o['total_products']))?></p><?php if(table_exists($conn,'order_items')):$qq=$conn->prepare('SELECT * FROM order_items WHERE order_id=?');$qq->bind_param('i',$o['id']);$qq->execute();$lines=$qq->get_result()->fetch_all(MYSQLI_ASSOC);foreach($lines as $line):$c=bouquet_data($line['custom_data']);if(!$c)continue;?><div class="order-bouquet-detail"><b><?=e($line['item_name'])?> · <?=e(['round'=>'класичний','airy'=>'повітряний','cascade'=>'каскадний'][$c['shape']??'round']??'класичний')?> букет</b><p><?php foreach($c['items'] as $f)echo e($f['name']).' × '.(int)$f['quantity'].'; ';?></p><?php if($c['note']??false):?><p>Побажання флористу: <?=e($c['note'])?></p><?php endif;?></div><?php endforeach;endif;?></div></div><div class="order-admin-bottom"><strong><?=money($o['total_price'])?></strong><form method="post" class="status-form"><?=csrf_input()?><input type="hidden" name="id" value="<?=(int)$o['id']?>"><select name="status"><option value="pending" <?=$o['payment_status']==='pending'?'selected':''?>>Очікує</option><option value="completed" <?=$o['payment_status']==='completed'?'selected':''?>>Завершено</option><option value="cancelled" <?=$o['payment_status']==='cancelled'?'selected':''?>>Скасовано</option></select><button class="admin-btn" name="change_status" value="1">Зберегти</button></form></div></article><?php endforeach;?></div><p class="admin-note">Для замовлень, створених у новій версії, скасування повертає квіти на склад. Для старих замовлень без деталізації автоматичне повернення залишків недоступне.</p>
<?php require 'admin_footer.php';?></body></html>

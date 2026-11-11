<?php
require_once 'config.php';
$admin_id=require_admin($conn);
$updated=null;
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['refresh_gifts'])){
 check_csrf();
 $changes=[
  ['Подарункова листівка','Квіткова коробка «Весняна ніжність»','Рожеві тюльпани у ніжній подарунковій композиції.',2490,'gift_hatbox_blush.jpg',8],
  ['Преміум пакування','Квітковий кошик «Гармонія»','Плетений кошик з рожевими півоніями у світлих природних відтінках.',2890,'gift_basket_rose.jpg',6],
  ['Подарунковий набір','Квітковий бокс «Теплі почуття»','Квіткова композиція з трояндами у святковому боксі.',2650,'gift_box_pastel.jpg',7]
 ];
 try {
  $conn->begin_transaction(); $updated=0;
  $stmt=$conn->prepare("UPDATE products SET name=?,details=?,price=?,image=?,stock=? WHERE product_type='gift' AND name=?");
  foreach($changes as $c){$stmt->bind_param('ssdsis',$c[1],$c[2],$c[3],$c[4],$c[5],$c[0]);$stmt->execute();$updated+=$stmt->affected_rows;}
  $conn->commit();
  flash('success',$updated?('Оновлено '.$updated.' демонстраційні композиції. Інші товари не змінено.'):'Старі демонстраційні подарунки не знайдено. Можливо, колекцію вже оновлено.');
 } catch(Throwable $e){$conn->rollback();flash('error','Не вдалося оновити подарунки: '.$e->getMessage());}
 redirect_to('admin_gifts_setup.php');
}
?>
<!doctype html><html lang="uk"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Квіткові подарунки — FLÔR ADMIN</title><link rel="stylesheet" href="css/admin_style.css"><link rel="stylesheet" href="css/flor-admin-v8.css"></head><body>
<?php require 'admin_header.php'; ?>
<section class="admin-section"><div class="admin-heading"><span>ТОВАРИ / КОЛЕКЦІЯ</span><h1>Квіти в коробках та кошиках</h1><p>Оновлення трьох стартових подарунків із бази shop_db. Власні товари адміністраторів не змінюються.</p></div>
<div style="background:white;border:1px solid #eadfdc;padding:26px;max-width:760px;border-radius:18px">
<h2 style="margin:0 0 15px">Що зміниться</h2>
<p>«Подарункова листівка» → «Квіткова коробка Ніжність»</p>
<p>«Преміум пакування» → «Квітковий кошик Гармонія»</p>
<p>«Подарунковий набір» → «Квітковий бокс Теплі почуття»</p>
<p style="color:#82757a;font-size:13px">Назви, фото, ціни й залишки старих демонстраційних позицій будуть оновлені. Наявні покупці й замовлення залишаться.</p>
<form method="post"><?=csrf_input()?><button class="btn" type="submit" name="refresh_gifts" value="1">Оновити подарункову колекцію</button></form>
<p style="margin-top:20px"><a href="admin_products.php">← Повернутись до товарів</a></p>
</div></section>
<?php require 'admin_footer.php'; ?></body></html>

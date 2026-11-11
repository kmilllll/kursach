<?php
require_once 'config.php';
$flowers=$conn->query("SELECT id,name,details,price,image,stock FROM products WHERE product_type='flower' AND is_active=1 AND stock>0 ORDER BY id")->fetch_all(MYSQLI_ASSOC);
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['create_bouquet'])){
 check_csrf(); $uid=require_user(); $chosen=$_POST['flowers']??[];
 $wrap=in_array($_POST['wrap']??'', ['cream','blush','kraft'],true)?$_POST['wrap']:'cream';
 $tierOptions=['auto','high','middle','front']; $tiers=is_array($_POST['tier']??null)?$_POST['tier']:[];
 $note=mb_substr(trim((string)($_POST['note']??'')),0,300);
 $items=[];$total=0;$firstImage='';$units=0;
 try {
  if(!is_array($chosen))throw new RuntimeException('Не вдалося прочитати вибір квітів.');
  foreach($chosen as $id=>$count){
   $pid=(int)$id;$qty=(int)$count;
   if($qty<1)continue;
   if($pid<1)throw new RuntimeException('Некоректний товар.');
   $q=$conn->prepare("SELECT id,name,price,image,stock FROM products WHERE id=? AND product_type='flower' AND is_active=1");
   $q->bind_param('i',$pid);$q->execute();$p=$q->get_result()->fetch_assoc();
   if(!$p||$qty>(int)$p['stock'])throw new RuntimeException('Один із вибраних видів квітів закінчився. Перевірте кількість.');
   $tier=in_array($tiers[$pid]??'auto',$tierOptions,true)?$tiers[$pid]:'auto';
   $items[]=['id'=>$pid,'name'=>$p['name'],'quantity'=>$qty,'price'=>(float)$p['price'],'tier'=>$tier];
   $units+=$qty;$total+=(float)$p['price']*$qty;if(!$firstImage)$firstImage=$p['image'];
  }
  if($units<1||$units>80)throw new RuntimeException('Для одного букета оберіть від 1 до 80 квітів.');
  $data=json_encode(['items'=>$items,'shape'=>'round','wrap'=>$wrap,'note'=>$note],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
  $name='Авторський букет · Класичний';$pid=0;$one=1;
  $q=$conn->prepare('INSERT INTO cart(user_id,pid,name,price,quantity,image,custom_data) VALUES(?,?,?,?,?,?,?)');
  $q->bind_param('iisdiss',$uid,$pid,$name,$total,$one,$firstImage,$data);$q->execute();
  flash('success','Ваш букет додано до кошика!');redirect_to('cart.php');
 }catch(Throwable $error){flash('error',$error->getMessage());redirect_to('builder.php');}
}
$preselected=(int)($_GET['flower']??0);
$flowerJS=[];
foreach($flowers as $f)$flowerJS[]=['id'=>(int)$f['id'],'name'=>$f['name'],'price'=>(float)$f['price'],'stock'=>(int)$f['stock'],'image'=>photo($f['image'],'flower'),'cutout'=>flower_cutout($f['image'])];
?>
<!doctype html><html lang="uk"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Свій букет — FLÔR</title><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/flor-v5.css"><link rel="stylesheet" href="css/flor-v6.css"><link rel="stylesheet" href="css/flor-v7.css"><link rel="stylesheet" href="css/flor-v8.css"><link rel="stylesheet" href="css/flor-v9.css"></head><body><?php require 'header.php'; ?>
<main class="builder-page f5-builder-page">
 <div class="breadcrumbs"><a href="home.php">Головна</a> / <span>Створи свій букет</span></div>
 <div class="f5-builder-head"><div><span class="f5-kicker">FLÔR CUSTOM / 01</span><h1>Створи свій букет.</h1><p>Оберіть квіти і кількість — ми покажемо, як виглядатиме класична композиція з трьома рівнями висоти та однією точкою зборки.</p></div><span class="l9-builder-accessory">Квіти доступні лише тут — у конструкторі</span></div>
 <div class="f5-builder-path"><span class="active">01 / Квіти</span><span>02 / Пакування</span><span>03 / У кошик</span></div>
 <form method="post" id="bouquet-form" class="bouquet-builder f5-builder-layout"><?=csrf_input()?>
 <div class="builder-editor f5-builder-editor">
  <div class="f5-step-heading"><div><span class="f5-step-number">01</span><h2>Які квіти додамо?</h2></div><p>Усі доступні стебла — з каталогу. Натисніть «+», щоб додати квітку.</p></div>
  <div class="flower-picker f5-picker">
   <?php if(!$flowers):?><div class="empty-state">Наразі немає квітів у наявності.</div><?php endif;?>
   <?php foreach($flowers as $f):?><article class="picker-item f5-picker-item" data-flower-id="<?=(int)$f['id']?>">
    <img src="<?=e(photo($f['image'],'flower'))?>" alt="<?=e($f['name'])?>" loading="lazy" onerror="this.onerror=null;this.src='assets/flower-placeholder.svg'">
    <div class="picker-text"><h3><?=e($f['name'])?></h3><span class="picker-price"><?=money($f['price'])?> / шт.</span><small>Є в наявності: <?=(int)$f['stock']?></small></div>
    <label class="l9-tier-label">Розташування <select name="tier[<?=(int)$f['id']?>]" aria-label="Рівень для <?=e($f['name'])?>"><option value="auto">Авто</option><option value="high">Вище</option><option value="middle">Середина</option><option value="front">Ближче</option></select></label><div class="stepper"><button type="button" data-step="-1" aria-label="Прибрати одну квітку">−</button><input type="number" name="flowers[<?=(int)$f['id']?>]" min="0" max="<?=(int)$f['stock']?>" value="<?= $preselected===(int)$f['id']?1:0 ?>" aria-label="Кількість <?=e($f['name'])?>" inputmode="numeric"><button type="button" data-step="1" aria-label="Додати одну квітку">+</button></div>
   </article><?php endforeach;?>
  </div>
  <div class="f5-step-heading"><div><span class="f5-step-number">02</span><h2>Оберіть пакування</h2></div><p>Одна класична форма букета — без зайвих режимів. Пакування входить у вартість.</p></div>
  <div class="f5-wrap-options" role="radiogroup" aria-label="Колір пакування"><label><input type="radio" name="wrap" value="cream" checked><span><i style="background:#e8dfd4"></i>Молочне</span></label><label><input type="radio" name="wrap" value="blush"><span><i style="background:#e1c2c6"></i>Пудрове</span></label><label><input type="radio" name="wrap" value="kraft"><span><i style="background:#c4a68b"></i>Крафт</span></label></div>
  <label for="bouquet-note" class="f5-note-label">Побажання флористу <small>(необов’язково)</small></label><textarea id="bouquet-note" name="note" maxlength="300" class="field" rows="3" placeholder="Наприклад: без зайвих декоративних деталей"></textarea>
 </div>
 <aside class="builder-preview-panel f5-builder-preview"><div class="preview-heading"><div><span class="f5-kicker">ПРИБЛИЗНИЙ ПЕРЕГЛЯД</span><h2>Ваш букет</h2></div><span class="f5-preview-label">КЛАСИЧНИЙ</span></div>
  <div class="bouquet-photo-stage f5-bouquet-stage wrap-cream" id="bouquet-photo-stage" aria-label="Попередній вигляд букета з вибраних квітів">
   <div class="paper-back" id="paper-back"></div><div class="photo-stems" id="photo-stems"></div><div class="paper-front" id="paper-front"><div class="paper-fold paper-fold-left"></div><div class="paper-fold paper-fold-right"></div><div class="paper-ribbon"></div></div>
   <div id="preview-empty" class="f5-preview-empty"><span>＋</span><b>Почніть з однієї квітки</b><p>Виберіть квіти ліворуч — і ми складемо класичний букет тут.</p></div>
   <span class="preview-count" id="photo-count" hidden></span>
  </div>
  <p class="preview-disclaimer">Ескіз із реальних фото окремих квітів: три рівні висоти, стебла сходяться до спільної точки. Готова композиція може відрізнятися.</p>
  <div class="bouquet-summary f5-bouquet-summary"><div class="summary-title"><b>Склад композиції</b><span id="flower-count">0 квітів</span></div><div id="chosen-flowers" class="chosen-flowers"><p>Додайте квіти — тут з’явиться склад.</p></div><div class="total-line"><span>Вартість квітів</span><strong id="bouquet-total">0 ₴</strong></div><button class="btn btn-wide" type="submit" name="create_bouquet" value="1" id="builder-submit" disabled>Додати до кошика <span>↗</span></button><p class="summary-footnote">Доступна кількість перевіряється під час оформлення замовлення.</p></div>
 </aside></form>
 <script id="flower-data" type="application/json"><?=json_encode($flowerJS,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?></script>
</main><?php require 'footer.php'; ?><script src="js/builder.js" defer></script></body></html>

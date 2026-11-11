<?php
require_once 'config.php';$uid=require_user();
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['place_order'])){
 check_csrf();
 $name=trim($_POST['name']??'');$phone=trim($_POST['number']??'');$email=trim($_POST['email']??'');
 $city=trim($_POST['city']??'');$street=trim($_POST['street']??'');$country=trim($_POST['country']??'Україна');
 $method=in_array($_POST['method']??'', ['cash on delivery','card on delivery'],true)?$_POST['method']:'cash on delivery';
 if(mb_strlen($name)<2||mb_strlen($phone)<7||!filter_var($email,FILTER_VALIDATE_EMAIL)||$city===''||$street===''){
     flash('error','Перевірте ім’я, телефон, email і адресу доставки.');redirect_to('checkout.php');
 }
 if(!table_exists($conn,'order_items')){flash('error','Для оформлення нових замовлень спочатку імпортуйте upgrade_v3.sql.');redirect_to('checkout.php');}
 try{
   $conn->begin_transaction();
   $q=$conn->prepare('SELECT * FROM cart WHERE user_id=? ORDER BY id FOR UPDATE');$q->bind_param('i',$uid);$q->execute();$items=$q->get_result()->fetch_all(MYSQLI_ASSOC);
   if(!$items)throw new RuntimeException('Ваш кошик порожній.');
   $needs=bouquet_needs($items);
   change_stock($conn,$needs,true); // Перевірка й списання В СУКУПНОСТІ, щоб не продати зайві квіти.
   $total=0;$description=[];
   foreach($items as $r){$total+=(float)$r['price']*(int)$r['quantity'];$description[]=$r['name'].' × '.(int)$r['quantity'];}
   $address=$street.', '.$city.', '.$country;$descriptionText=implode('; ',$description);
   $q=$conn->prepare('INSERT INTO orders(user_id,name,number,email,method,address,total_products,total_price,placed_on,payment_status) VALUES(?,?,?,?,?,?,?,?,NOW(),\'pending\')');
   $q->bind_param('issssssd',$uid,$name,$phone,$email,$method,$address,$descriptionText,$total);$q->execute();$oid=$conn->insert_id;
   $insert=$conn->prepare('INSERT INTO order_items(order_id,product_id,item_name,unit_price,quantity,custom_data) VALUES(?,?,?,?,?,?)');
   foreach($items as $r){$pid=(int)$r['pid'];$unit=(float)$r['price'];$qty=(int)$r['quantity'];$custom=$r['custom_data'];$insert->bind_param('iisdis',$oid,$pid,$r['name'],$unit,$qty,$custom);$insert->execute();}
   $del=$conn->prepare('DELETE FROM cart WHERE user_id=?');$del->bind_param('i',$uid);$del->execute();$conn->commit();
   flash('success','Дякуємо! Замовлення №'.$oid.' оформлено.');redirect_to('orders.php');
 }catch(Throwable $ex){$conn->rollback();flash('error',$ex->getMessage());redirect_to('checkout.php');}
}
$q=$conn->prepare('SELECT * FROM cart WHERE user_id=? ORDER BY id');$q->bind_param('i',$uid);$q->execute();$items=$q->get_result()->fetch_all(MYSQLI_ASSOC);$total=0;foreach($items as $it)$total+=(float)$it['price']*$it['quantity'];
?>
<!doctype html><html lang="uk"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Оформлення замовлення — flora.atelier</title><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/flor-v5.css"><link rel="stylesheet" href="css/flor-v6.css"><link rel="stylesheet" href="css/flor-v7.css"><link rel="stylesheet" href="css/flor-v8.css"><link rel="stylesheet" href="css/flor-v9.css"></head><body><?php require 'header.php';?><main class="page-shell"><div class="page-intro"><span class="eyebrow">ОСТАННІЙ КРОК</span><h1>Оформлення <em>замовлення</em></h1><p>Розкажи, куди доставити твою красу.</p></div>
 <div class="checkout-layout"><form method="post" class="checkout-form"><?=csrf_input()?><div class="form-panel"><span class="eyebrow">01 · КОНТАКТИ</span><h2>Дані отримувача</h2><div class="fields-grid"><label>Ім’я та прізвище *<input class="field" name="name" required maxlength="100" value="<?=e($_SESSION['user_name']??'')?>"></label><label>Номер телефону *<input class="field" name="number" type="tel" required maxlength="30" placeholder="+380…"></label><label class="full-field">Email *<input class="field" name="email" required type="email" maxlength="100" value="<?=e($_SESSION['user_email']??'')?>"></label></div></div><div class="form-panel"><span class="eyebrow">02 · ДОСТАВКА</span><h2>Адреса</h2><div class="fields-grid"><label class="full-field">Вулиця, будинок, квартира *<input class="field" name="street" required maxlength="200"></label><label>Місто *<input class="field" name="city" required maxlength="100"></label><label>Країна<input class="field" name="country" maxlength="100" value="Україна"></label></div></div><div class="form-panel"><span class="eyebrow">03 · ОПЛАТА</span><h2>Спосіб оплати</h2><label class="payment-radio"><input type="radio" name="method" value="cash on delivery" checked> Готівкою при отриманні</label><label class="payment-radio"><input type="radio" name="method" value="card on delivery"> Карткою при отриманні</label><p class="fine-print">Оплата відбувається при отриманні: онлайн-платіж не підключено.</p></div><button type="submit" class="btn btn-wide" name="place_order" value="1" <?=$items?'':'disabled'?>>Підтвердити замовлення ↗</button></form><aside class="checkout-summary"><span class="eyebrow">ВАШ КОШИК</span><h3>Склад замовлення</h3><?php foreach($items as $r):?><div class="summary-product"><span><?=e($r['name'])?> <small>× <?=(int)$r['quantity']?></small></span><b><?=money($r['price']*$r['quantity'])?></b></div><?php endforeach;?><div class="summary-total-line total"><span>До сплати</span><strong><?=money($total)?></strong></div><a class="link-underline" href="cart.php">← Повернутися до кошика</a></aside></div></main><?php require 'footer.php';?></body></html>

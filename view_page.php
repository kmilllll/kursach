<?php
require_once 'config.php';
$pid=(int)($_GET['pid']??0);
$q=$conn->prepare('SELECT * FROM products WHERE id=? AND is_active=1');$q->bind_param('i',$pid);$q->execute();$p=$q->get_result()->fetch_assoc();
// Окремі стебла доступні покупцям тільки в конструкторі композицій.
if($p && $p['product_type']==='flower') redirect_to('builder.php?flower='.$pid);
if(!$p){http_response_code(404);}
$reviewsReady=table_exists($conn,'product_reviews');
if($_SERVER['REQUEST_METHOD']==='POST'){
    check_csrf();
    if(!$p){flash('error','Товар більше недоступний.');redirect_to('shop.php');}
    if(isset($_POST['add_to_cart'])){
        $uid=require_user();$n=(int)($_POST['quantity']??1);
        if($n<1||$n>(int)$p['stock']){flash('error','Такої кількості зараз немає в наявності.');redirect_to('view_page.php?pid='.$pid);}
        $check=$conn->prepare('SELECT id,quantity FROM cart WHERE user_id=? AND pid=? AND custom_data IS NULL');$check->bind_param('ii',$uid,$pid);$check->execute();$existing=$check->get_result()->fetch_assoc();
        if($existing){if((int)$existing['quantity']+$n>$p['stock']){flash('error','У кошику вже є частина доступного залишку.');redirect_to('view_page.php?pid='.$pid);}$u=$conn->prepare('UPDATE cart SET quantity=quantity+? WHERE id=? AND user_id=?');$u->bind_param('iii',$n,$existing['id'],$uid);$u->execute();}
        else{$ins=$conn->prepare('INSERT INTO cart(user_id,pid,name,price,quantity,image) VALUES(?,?,?,?,?,?)');$ins->bind_param('iisdis',$uid,$pid,$p['name'],$p['price'],$n,$p['image']);$ins->execute();}
        flash('success','Товар додано до кошика.');redirect_to('cart.php');
    }
    if(isset($_POST['send_review'])){
        $uid=require_user();
        if(!$reviewsReady){flash('error','Відгуки ще не підключено. Імпортуйте upgrade_v3.sql.');redirect_to('view_page.php?pid='.$pid.'#reviews');}
        $rating=(int)($_POST['rating']??0);$body=trim((string)($_POST['review_text']??''));
        if($rating<1||$rating>5||mb_strlen($body)<10||mb_strlen($body)>1200){flash('error','Оберіть оцінку та напишіть відгук від 10 до 1200 символів.');redirect_to('view_page.php?pid='.$pid.'#reviews');}
        $s=$conn->prepare("INSERT INTO product_reviews (product_id,user_id,rating,review_text,status,admin_reply) VALUES(?,?,?,?,'pending',NULL) ON DUPLICATE KEY UPDATE rating=VALUES(rating), review_text=VALUES(review_text), status='pending', admin_reply=NULL, updated_at=NOW()");
        $s->bind_param('iiis',$pid,$uid,$rating,$body);$s->execute();
        flash('success','Дякуємо! Відгук надіслано на перевірку.');redirect_to('view_page.php?pid='.$pid.'#reviews');
    }
}
$reviews=[];$avg=0;$nReviews=0;$myReview=null;
if($p && $reviewsReady){
    $q=$conn->prepare("SELECT r.*, u.name AS user_name FROM product_reviews r JOIN users u ON u.id=r.user_id WHERE r.product_id=? AND r.status='approved' ORDER BY r.created_at DESC LIMIT 30");
    $q->bind_param('i',$pid);$q->execute();$reviews=$q->get_result()->fetch_all(MYSQLI_ASSOC);
    $q=$conn->prepare("SELECT COUNT(*) AS cnt,COALESCE(AVG(rating),0) AS avg FROM product_reviews WHERE product_id=? AND status='approved'");$q->bind_param('i',$pid);$q->execute();$summary=$q->get_result()->fetch_assoc();$avg=(float)$summary['avg'];$nReviews=(int)$summary['cnt'];
    if($user_id){$q=$conn->prepare('SELECT rating,review_text,status FROM product_reviews WHERE product_id=? AND user_id=?');$q->bind_param('ii',$pid,$user_id);$q->execute();$myReview=$q->get_result()->fetch_assoc();}
}
?>
<!doctype html><html lang="uk"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($p['name']??'Товар не знайдено')?> — flora.atelier</title><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/flor-v5.css"><link rel="stylesheet" href="css/flor-v6.css"><link rel="stylesheet" href="css/flor-v7.css"><link rel="stylesheet" href="css/flor-v8.css"><link rel="stylesheet" href="css/flor-v9.css"></head><body><?php require 'header.php'; ?>
<main class="page-shell">
 <?php if(!$p):?><div class="empty-state">Такого товару немає. <a href="shop.php" class="text-link">Повернутися до каталогу</a></div><?php else:?>
 <div class="breadcrumbs"><a href="home.php">Головна</a> / <a href="shop.php?type=<?=e($p['product_type'])?>"><?=product_type_name($p['product_type'])?></a> / <?=e($p['name'])?></div>
 <section class="product-detail"><div class="detail-photo"><img src="<?=e(photo($p['image'], $p['product_type']))?>" alt="<?=e($p['name'])?>" onerror="this.onerror=null;this.src='assets/flower-placeholder.svg'"></div><div class="detail-body"><span class="eyebrow"><?=product_type_name($p['product_type'])?></span><h1><?=e($p['name'])?></h1><div class="detail-stars"><a href="#reviews"><span class="stars"><?=str_repeat('★',round($avg)).str_repeat('☆',5-round($avg))?></span> <?= $nReviews?number_format($avg,1).' · '.$nReviews.' відгуків':'Ще немає відгуків' ?></a></div><div class="detail-price"><?=money($p['price'])?> <?= $p['product_type']==='flower'?'<small>/ шт.</small>':''?></div><p class="detail-desc"><?=e($p['details'])?></p><p class="stock-indicator"><?=((int)$p['stock']>0?'● В наявності: '.(int)$p['stock'].' шт.':'○ Тимчасово немає')?></p><form method="post" class="detail-buy"><?=csrf_input()?><label for="item-qty">Кількість</label><input id="item-qty" name="quantity" type="number" min="1" max="<?=(int)$p['stock']?>" value="1" <?=(int)$p['stock']<1?'disabled':''?>><button type="submit" class="btn" name="add_to_cart" value="1" <?=(int)$p['stock']<1?'disabled':''?>>Додати до кошика ↗</button></form><?php if($p['product_type']==='flower' && (int)$p['stock']>0):?><p class="detail-builder-link"><a href="builder.php?flower=<?=(int)$p['id']?>">Або додати цю квітку до власного букета →</a></p><?php endif;?><div class="detail-promises"><span>✿ Кожен букет — з турботою</span><span>◇ Свіжі квіти щодня</span></div></div></section>
 <section id="reviews" class="reviews-section"><div class="section-heading"><div><span class="eyebrow">СЛОВА НАШИХ КЛІЄНТІВ</span><h2>Відгуки про товар</h2></div><span class="review-overall"><span class="stars">★★★★★</span> <?=number_format($avg,1)?> / 5 · <?=$nReviews?> відгуків</span></div>
 <div class="reviews-layout"><div class="review-feed">
 <?php if($reviews):foreach($reviews as $r):?><article class="review-card"><div class="review-card-top"><b><?=e($r['user_name'])?></b><time><?=date('d.m.Y',strtotime($r['created_at']))?></time></div><div class="stars"><?=str_repeat('★',(int)$r['rating']).str_repeat('☆',5-(int)$r['rating'])?></div><p><?=nl2br(e($r['review_text']))?></p><?php if(trim((string)$r['admin_reply'])!==''):?><div class="admin-reply"><b>✳ Відповідь flora.atelier</b><p><?=nl2br(e($r['admin_reply']))?></p></div><?php endif;?></article><?php endforeach;else:?><div class="empty-state">Тут поки тихо. Стань першою людиною, яка залишить враження ♡</div><?php endif;?></div>
 <aside class="review-write"><span class="eyebrow">ЗАЛИШИТИ ВРАЖЕННЯ</span><h3>Розкажи, як тобі квіти</h3><?php if(!$reviewsReady):?><p>Щоб увімкнути відгуки, імпортуйте файл <code>upgrade_v3.sql</code> у phpMyAdmin.</p><?php elseif(!$user_id):?><p>Для відгуку потрібно увійти в обліковий запис.</p><a class="btn" href="login.php">Увійти</a><?php else:?><?php if($myReview):?><p class="review-status">Ваш відгук: <?=['pending'=>'очікує модерації','approved'=>'опублікований','rejected'=>'не пройшов модерацію'][$myReview['status']]??'на перевірці'?>. Ви можете його оновити.</p><?php endif;?><form method="post"><?=csrf_input()?><label class="form-label" for="rating">Оцінка</label><select id="rating" class="field" name="rating" required><option value="">Оберіть від 1 до 5</option><?php for($i=5;$i>=1;$i--):?><option value="<?=$i?>" <?= $myReview && (int)$myReview['rating']===$i?'selected':''?>><?=str_repeat('★',$i).' '.($i===5?'Чудово':'')?></option><?php endfor;?></select><label class="form-label" for="review_text">Ваш відгук</label><textarea id="review_text" name="review_text" class="field" rows="5" required minlength="10" maxlength="1200" placeholder="Розкажіть про свої враження..."><?=e($myReview['review_text']??'')?></textarea><button type="submit" class="btn btn-wide" name="send_review" value="1">Надіслати відгук ↗</button><small>Публікуємо відгуки після перевірки адміністратором.</small></form><?php endif;?></aside>
 </div></section>
 <?php endif;?></main><?php require 'footer.php';?></body></html>

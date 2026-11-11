<?php
require_once 'config.php';
$type=$_GET['type']??'bouquet';
// Поштучні квіти є інгредієнтами конструктора, не окремим магазином.
if($type==='flower'){header('Location: builder.php',true,302);exit;}
if(!in_array($type,['all','bouquet','gift'],true))$type='bouquet';
$search=mb_substr(trim((string)($_GET['q']??'')),0,100);
$sort=$_GET['sort']??'new';if(!in_array($sort,['new','price_low','price_high'],true))$sort='new';
$order=['new'=>'id DESC','price_low'=>'price ASC, id DESC','price_high'=>'price DESC, id DESC'][$sort];
$where=['is_active=1',"product_type IN ('bouquet','gift')"];$args=[];$argtypes='';
if($type!=='all'){$where[]='product_type=?';$args[]=$type;$argtypes.='s';}
if($search!==''){$where[]='name LIKE ?';$args[]='%'.$search.'%';$argtypes.='s';}
$sql='SELECT * FROM products WHERE '.implode(' AND ',$where).' ORDER BY '.$order;
$stmt=$conn->prepare($sql);if($args)$stmt->bind_param($argtypes,...$args);$stmt->execute();$products=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$label=['bouquet'=>'Готові букети','gift'=>'Квіти в коробках і кошиках','all'=>'Увесь каталог'][$type];
$desc=['bouquet'=>'Букети, які вже зібрані — залишається лише обрати.','gift'=>'Готові подарункові композиції: ніжні квіти в шляпних коробках, боксах і плетених кошиках.','all'=>'Готові букети й квіткові подарунки в одному місці.'][$type];
?>
<!doctype html><html lang="uk"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($label)?> — FLÔR</title><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/flor-v5.css"><link rel="stylesheet" href="css/flor-v6.css"><link rel="stylesheet" href="css/flor-v7.css"><link rel="stylesheet" href="css/flor-v8.css"><link rel="stylesheet" href="css/flor-v9.css"></head><body><?php require 'header.php'; ?>
<main class="page-shell catalogue-v4 rv-catalog rv-type-<?=e($type)?>">
 <div class="breadcrumbs"><a href="home.php">Головна</a> <span>/</span> <?=e($label)?></div>
 <div class="catalog-hero"><div><span class="eyebrow">КОЛЕКЦІЯ FLÔR · FLOWER STUDIO</span><h1><?=e($label)?></h1><p><?=e($desc)?></p></div><span class="catalog-counter"><?=count($products)?> позицій</span></div>
 <?php if($type==='gift'):?>
 <section class="v6-gift-intro"><div><span class="f5-kicker">КВІТКОВІ ПОДАРУНКИ</span><h2>Трохи більше,<br>ніж просто букет.</h2><p>Квіти у витончених коробках та кошиках: готові композиції, які можна одразу дарувати.</p></div><img src="<?=e(photo('white bouquet.jpg','gift'))?>" alt="Квіткова композиція у плетеному кошику"></section>
 <?php endif;?>
 <div class="catalog-layout"><aside class="catalog-sidebar"><h2>Категорії</h2><nav aria-label="Категорії товарів"><a href="shop.php?type=bouquet" class="<?=$type==='bouquet'?'active':''?>">Готові букети <span>↗</span></a><a href="shop.php?type=gift" class="<?=$type==='gift'?'active':''?>">Квіти в коробках <span>↗</span></a><a href="shop.php?type=all" class="<?=$type==='all'?'active':''?>">Усі товари <span>↗</span></a></nav><div class="catalog-tip"><strong>Власна композиція?</strong><p>Вибирайте окремі квіти й бачте попередній вигляд авторської композиції.</p><a href="builder.php">Створити букет →</a></div></aside>
 <div class="catalog-main"><form method="get" class="catalog-filter"><input type="hidden" name="type" value="<?=e($type)?>"><label class="search-input"><span>⌕</span><input name="q" value="<?=e($search)?>" placeholder="Знайти букет або композицію"></label><label class="sort-input">Сортування <select name="sort" onchange="this.form.submit()"><option value="new" <?=$sort==='new'?'selected':''?>>Нові спочатку</option><option value="price_low" <?=$sort==='price_low'?'selected':''?>>Від дешевших</option><option value="price_high" <?=$sort==='price_high'?'selected':''?>>Від дорожчих</option></select></label><button class="btn btn-small" type="submit">Знайти</button></form>
 <div class="product-grid catalogue-products"><?php foreach($products as $p):?><article class="product-card"><a href="view_page.php?pid=<?=(int)$p['id']?>" class="product-card-photo"><img src="<?=e(photo($p['image'],$p['product_type']))?>" alt="<?=e($p['name'])?>" loading="lazy" onerror="this.onerror=null;this.src='assets/flower-placeholder.svg'"><span class="photo-badge"><?=e(product_type_name($p['product_type']))?></span></a><div class="product-card-info"><a href="view_page.php?pid=<?=(int)$p['id']?>"><h3><?=e($p['name'])?></h3></a><b><?=money($p['price'])?><?php if($p['product_type']==='flower'):?><small> / шт.</small><?php endif;?></b></div><p class="product-excerpt"><?=e(mb_strimwidth($p['details'],0,90,'…'))?></p><div class="card-bottom"><span><?= $p['stock']>0?'У наявності · '.(int)$p['stock'].' шт.':'Немає в наявності' ?></span><?php if($p['product_type']==='flower'):?><a href="builder.php?flower=<?=(int)$p['id']?>">У букет ↗</a><?php else:?><a href="view_page.php?pid=<?=(int)$p['id']?>">Обрати ↗</a><?php endif;?></div></article><?php endforeach; ?><?php if(!$products):?><div class="empty-state"><h3>Нічого не знайдено</h3><p>Спробуйте іншу назву або перейдіть до каталогу.</p><a class="btn" href="shop.php?type=all">Усі товари</a></div><?php endif;?></div>
 </div></div>
</main><?php require 'footer.php';?></body></html>

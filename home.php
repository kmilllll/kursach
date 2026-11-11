<?php
require_once 'config.php';
$featured = $conn->query("SELECT id,name,details,price,image,product_type FROM products WHERE product_type='bouquet' AND is_active=1 ORDER BY id DESC LIMIT 4")->fetch_all(MYSQLI_ASSOC);
$gifts = $conn->query("SELECT id,name,details,price,image,product_type FROM products WHERE product_type='gift' AND is_active=1 ORDER BY id DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html lang="uk"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>FLÔR — квіти, що говорять про почуття</title>
<meta name="description" content="FLÔR — романтична квіткова майстерня. Готові букети, квіткові подарунки та конструктор власного букета.">
<link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/flor-v5.css"><link rel="stylesheet" href="css/flor-v6.css"><link rel="stylesheet" href="css/flor-v7.css"><link rel="stylesheet" href="css/flor-v8.css"><link rel="stylesheet" href="css/flor-v9.css"></head>
<body class="flor-v9-home"><?php require 'header.php'; ?>
<main>
 <section class="l9-hero" aria-labelledby="l9-hero-title">
  <div class="l9-photo" aria-hidden="true"><img src="<?=e(photo('pink bouquet.jpg','bouquet'))?>" onerror="this.onerror=null;this.src='assets/flower-placeholder.svg'" alt=""></div>
  <div class="l9-hero-overlay" aria-hidden="true"></div>
  <div class="l9-hero-botanic l9-hero-botanic-one" aria-hidden="true"></div>
  <div class="l9-hero-content">
   <div class="l9-smallline"><span class="l9-mark">✦</span> Квіткова майстерня FLÔR <span class="l9-mark">✦</span></div>
   <h1 id="l9-hero-title">Нехай квіти<br><span>говорять</span><br>за вас.</h1>
   <p>Маленькі прояви любові, зібрані пелюстка за пелюсткою. Готові композиції та букети, створені саме вами.</p>
   <div class="l9-hero-actions"><a class="l9-btn l9-btn-ink" href="shop.php?type=bouquet">Обрати готовий букет <b>↗</b></a><a class="l9-link" href="builder.php">Зібрати свій <b>↗</b></a></div>
  </div>
  <div class="l9-hero-side" aria-hidden="true"><span>Квіти на кожен випадок</span><span>З турботою · FLÔR</span></div>
  <div class="l9-hero-badge" aria-hidden="true"><span>FLÔR</span><small>made with love</small></div>
  <div class="l9-hero-edge" aria-hidden="true"></div>
 </section>
 <div class="l9-marquee" aria-label="Наші особливості"><span>Свіжі квіти</span><i>✿</i><span>Ручне складання</span><i>✿</i><span>Особливі миті</span><i>✿</i><span>Квіткові історії</span></div>

 <section class="l9-collection" aria-labelledby="l9-collection-title">
  <div class="l9-intro"><div class="l9-serial">01 / Букети із почуттям</div><div><h2 id="l9-collection-title">Зібрані з ніжністю.</h2><p>Кожна композиція — маленька історія, яку хочеться подарувати.</p></div><a class="l9-textlink" href="shop.php?type=bouquet">Уся колекція <span>↗</span></a></div>
  <div class="l9-bouquet-grid">
  <?php foreach ($featured as $i=>$p):?>
   <a class="l9-bouquet-card l9-bouquet-<?=(int)$i?>" href="view_page.php?pid=<?=(int)$p['id']?>"><div class="l9-bouquet-photo"><img src="<?=e(photo($p['image'],'bouquet'))?>" alt="<?=e($p['name'])?>" loading="lazy" onerror="this.onerror=null;this.src='assets/flower-placeholder.svg'"><span class="l9-photo-num">FLÔR / <?=str_pad((string)($i+1),2,'0',STR_PAD_LEFT)?></span></div><div class="l9-bouquet-meta"><div><strong><?=e($p['name'])?></strong><small>Готовий букет</small></div><span><?=money($p['price'])?></span></div></a>
  <?php endforeach; ?>
  <?php if (!$featured):?><p class="empty-state">У колекції поки немає готових букетів.</p><?php endif;?>
  </div>
  <div class="l9-collection-after"><img src="assets/flor-ornament.svg" alt="" aria-hidden="true"><p>Справжня краса живе у простих, щирих жестах.</p><a class="l9-link" href="shop.php?type=bouquet">Дивитися всі букети ↗</a></div>
 </section>

 <section class="l9-letter" aria-labelledby="l9-letter-title">
  <div class="l9-letter-leaf" aria-hidden="true"></div>
  <div class="l9-letter-photo"><img src="<?=e(photo('yellow queen rose.jpg','bouquet'))?>" onerror="this.onerror=null;this.src='assets/flower-placeholder.svg'" loading="lazy" alt="Ніжний букет у майстерні"></div>
  <div class="l9-letter-content"><span class="l9-serial">02 / Ваш власний букет</span><img class="l9-ornament" src="assets/flor-ornament.svg" alt="" aria-hidden="true"><h2 id="l9-letter-title">А якщо квіти<br>оберете <em>ви?</em></h2><p>Від першої троянди до останньої стрічки. Обирайте квіти, дивіться композицію та збирайте букет за своїм настроєм.</p><div class="l9-letter-stages"><span><b>01</b> Оберіть квіти</span><span><b>02</b> Знайдіть відтінок пакування</span><span><b>03</b> Замовте свою композицію</span></div><a href="builder.php" class="l9-btn l9-btn-sage">Перейти до конструктора <b>↗</b></a></div>
  <div class="l9-letter-lace" aria-hidden="true"></div>
 </section>

 <section class="l9-gifts" aria-labelledby="l9-gifts-title"><div class="l9-gifts-header"><span class="l9-serial">03 / Квіткові подарунки</span><h2 id="l9-gifts-title">Трохи більше, ніж букет.</h2><p>Улюблені квіти в кошиках і коробках — красиві жести для особливих людей.</p></div><div class="l9-gift-grid">
 <?php foreach($gifts as $i=>$p):?><a href="view_page.php?pid=<?=(int)$p['id']?>" class="l9-gift"><div class="l9-gift-photo"><img src="<?=e(photo($p['image'],'gift'))?>" alt="<?=e($p['name'])?>" loading="lazy" onerror="this.onerror=null;this.src='assets/flower-placeholder.svg'"></div><div class="l9-gift-meta"><span><?=e($p['name'])?></span><strong><?=money($p['price'])?></strong></div></a><?php endforeach; ?>
 </div><a href="shop.php?type=gift" class="l9-textlink">Дивитися квіткові подарунки <span>↗</span></a></section>

 <section class="l9-last-note"><div class="l9-last-ornament" aria-hidden="true"></div><span class="l9-serial">FLÔR FLOWER ATELIER</span><h2>Квіти — це спосіб<br>сказати <em>«ти мені дорогий».</em></h2><p>Нехай кожен букет стане ніжним спогадом.</p><a href="about.php" class="l9-link">Історія нашої майстерні ↗</a></section>
</main><?php require 'footer.php'; ?></body></html>

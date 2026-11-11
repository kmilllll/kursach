<?php
require_once 'config.php';
$currentPage=basename($_SERVER['SCRIPT_NAME']??'home.php');
$catalogType=$_GET['type']??'';
?>
<div class="rv-topnote flor-topline"><span>✧ &nbsp; ЛИСТИ ПРО ЛЮБОВ, НАПИСАНІ КВІТАМИ &nbsp; ✧</span><span>FLÔR FLOWER ATELIER</span></div>
<header class="site-header rv-header flor-site-header">
 <button class="nav-toggle rv-mobile-menu" type="button" aria-label="Відкрити меню" aria-expanded="false" aria-controls="site-nav" data-nav-toggle><span aria-hidden="true">☰</span></button>
 <a class="logo rv-brand flor-logo" href="home.php" aria-label="FLÔR — головна">FLÔR<small>flower atelier</small></a>
 <nav class="site-nav rv-nav flor-nav" id="site-nav" aria-label="Основне меню">
  <a class="<?= $currentPage==='home.php'?'active':'' ?>" href="home.php">Головна</a>
  <a class="<?= $currentPage==='shop.php'&&$catalogType==='bouquet'?'active':'' ?>" href="shop.php?type=bouquet">Готові букети</a>
  <a class="<?= $currentPage==='builder.php'?'active':'' ?>" href="builder.php">Створи свій букет</a>
  <a class="<?= $currentPage==='shop.php'&&$catalogType==='gift'?'active':'' ?>" href="shop.php?type=gift">Квіткові подарунки</a>
 </nav>
 <div class="header-actions rv-head-actions flor-head-actions">
  <?php if(!empty($_SESSION['admin_id'])):?><a class="flor-admin-link" href="admin_page.php">Адмінка</a><?php endif;?>
  <a class="rv-icon-link" href="<?= $user_id?'orders.php':'login.php' ?>" aria-label="<?= $user_id?'Мої замовлення':'Увійти' ?>" title="<?= $user_id?'Мої замовлення':'Увійти' ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20c0-4.2 3.2-6.7 7.5-6.7s7.5 2.5 7.5 6.7"/></svg></a>
  <a class="rv-icon-link rv-cart" href="cart.php" title="Кошик" aria-label="Кошик"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 8h16l-1.3 12H5.3L4 8Z"/><path d="M8 9V6a4 4 0 018 0v3"/></svg><span><?=cart_count($conn,$user_id)?></span></a>
 </div>
</header>
<?php notices(); ?>

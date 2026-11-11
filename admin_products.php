<?php
require_once 'config.php';$admin_id=require_admin($conn);
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_csrf();$action=$_POST['action']??'';
 if($action==='toggle'){
     $id=(int)($_POST['id']??0);$q=$conn->prepare('UPDATE products SET is_active=1-is_active WHERE id=?');$q->bind_param('i',$id);$q->execute();flash('success','Видимість товару оновлено.');redirect_to('admin_products.php');
 }
 if($action==='save'){
     $id=(int)($_POST['id']??0);$name=trim((string)($_POST['name']??''));$details=trim((string)($_POST['details']??''));
     $price=filter_var($_POST['price']??null,FILTER_VALIDATE_FLOAT);$stock=filter_var($_POST['stock']??null,FILTER_VALIDATE_INT);
     $type=$_POST['product_type']??'';$active=isset($_POST['is_active'])?1:0;
     if(mb_strlen($name)<2||mb_strlen($name)>100||mb_strlen($details)>500||$price===false||$price<0||$stock===false||$stock<0||$stock>100000||!in_array($type,['flower','bouquet','gift'],true)){
         flash('error','Перевірте назву, ціну, опис, категорію та залишок.');redirect_to('admin_products.php'.($id?'?edit='.$id:'?new=1'));
     }
     $img='';
     if($id){$q=$conn->prepare('SELECT image FROM products WHERE id=?');$q->bind_param('i',$id);$q->execute();$existing=$q->get_result()->fetch_assoc();if(!$existing){flash('error','Товар не знайдено.');redirect_to('admin_products.php');}$img=$existing['image'];}
     if(isset($_FILES['image'])&&$_FILES['image']['error']!==UPLOAD_ERR_NO_FILE){
         if($_FILES['image']['error']!==UPLOAD_ERR_OK||$_FILES['image']['size']>5*1024*1024||$_FILES['image']['size']<100){flash('error','Не вдалося завантажити файл (до 5 МБ).');redirect_to('admin_products.php'.($id?'?edit='.$id:'?new=1'));}
         $mime=(new finfo(FILEINFO_MIME_TYPE))->file($_FILES['image']['tmp_name']);
         $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;
         if(!$ext){flash('error','Дозволені лише JPG, PNG або WEBP.');redirect_to('admin_products.php'.($id?'?edit='.$id:'?new=1'));}
         $file=bin2hex(random_bytes(12)).'.'.$ext;$dir=__DIR__.'/uploaded_img';if(!is_dir($dir))mkdir($dir,0755,true);
         if(!move_uploaded_file($_FILES['image']['tmp_name'],$dir.'/'.$file)){flash('error','Помилка збереження фотографії. Перевірте права доступу до uploaded_img.');redirect_to('admin_products.php'.($id?'?edit='.$id:'?new=1'));}
         $img=$file;
     }
     if($id){$q=$conn->prepare('UPDATE products SET name=?,details=?,price=?,image=?,product_type=?,stock=?,is_active=? WHERE id=?');$q->bind_param('ssdssiii',$name,$details,$price,$img,$type,$stock,$active,$id);$q->execute();}
     else{$q=$conn->prepare('INSERT INTO products(name,details,price,image,product_type,stock,is_active) VALUES(?,?,?,?,?,?,?)');$q->bind_param('ssdssii',$name,$details,$price,$img,$type,$stock,$active);$q->execute();}
     flash('success',$id?'Товар успішно збережено.':'Новий товар додано.');redirect_to('admin_products.php');
 }
}
$filter=$_GET['type']??'all';if(!in_array($filter,['all','flower','bouquet','gift'],true))$filter='all';$search=trim((string)($_GET['q']??''));$search=mb_substr($search,0,100);
if($filter==='all'&&$search==='')$products=$conn->query('SELECT * FROM products ORDER BY id DESC')->fetch_all(MYSQLI_ASSOC);
elseif($filter==='all'){$s='%'.$search.'%';$q=$conn->prepare('SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC');$q->bind_param('s',$s);$q->execute();$products=$q->get_result()->fetch_all(MYSQLI_ASSOC);}
elseif($search===''){$q=$conn->prepare('SELECT * FROM products WHERE product_type=? ORDER BY id DESC');$q->bind_param('s',$filter);$q->execute();$products=$q->get_result()->fetch_all(MYSQLI_ASSOC);}
else{$s='%'.$search.'%';$q=$conn->prepare('SELECT * FROM products WHERE product_type=? AND name LIKE ? ORDER BY id DESC');$q->bind_param('ss',$filter,$s);$q->execute();$products=$q->get_result()->fetch_all(MYSQLI_ASSOC);}
$editId=(int)($_GET['edit']??0);$editing=null;if($editId){$q=$conn->prepare('SELECT * FROM products WHERE id=?');$q->bind_param('i',$editId);$q->execute();$editing=$q->get_result()->fetch_assoc();}
$showForm=$editing||isset($_GET['new']);
?>
<!doctype html><html lang="uk"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Товари · flora.atelier admin</title><link rel="stylesheet" href="css/admin_style.css"><link rel="stylesheet" href="css/flor-admin-v8.css"></head><body><?php require 'admin_header.php';?>
<div class="admin-page-head"><div><span class="admin-eyebrow">АСОРТИМЕНТ ТА СКЛАД</span><h1>Товари <em>✿</em></h1><p>Додавай квіти, змінюй ціни та контролюй наявність.</p></div><a class="admin-btn" href="admin_products.php?new=1">+ Новий товар</a></div>
<?php if($showForm):?><section class="admin-card edit-panel" id="edit"><div class="card-heading"><h2><?=$editing?'Редагування: '.e($editing['name']):'Створити товар'?></h2><a href="admin_products.php">Закрити ✕</a></div><form method="post" enctype="multipart/form-data" class="product-edit-form"><?=csrf_input()?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=(int)($editing['id']??0)?>"><div class="admin-form-grid"><label>Назва товару *<input name="name" value="<?=e($editing['name']??'')?>" maxlength="100" required minlength="2"></label><label>Категорія *<select name="product_type" required><?php foreach(['bouquet'=>'Готовий букет','flower'=>'Квіти поштучно','gift'=>'Подарунок'] as $k=>$v):?><option value="<?=$k?>" <?=($editing['product_type']??'flower')===$k?'selected':''?>><?=$v?></option><?php endforeach;?></select></label><label>Ціна, ₴ *<input type="number" name="price" min="0" step="0.01" value="<?=e($editing['price']??'')?>" required></label><label>Залишок, шт. *<input type="number" name="stock" min="0" max="100000" value="<?=e($editing['stock']??'10')?>" required></label><label class="full">Опис (до 500 символів)<textarea name="details" maxlength="500" rows="3"><?=e($editing['details']??'')?></textarea></label><label class="full">Фотографія · JPG, PNG, WEBP до 5 МБ<input type="file" name="image" accept="image/jpeg,image/png,image/webp"><?php if(!empty($editing['image'])):?><span class="file-current">Зараз: <?=e($editing['image'])?></span><?php endif;?></label><label class="checkline full"><input type="checkbox" name="is_active" value="1" <?=(!$editing || $editing['is_active'])?'checked':''?>> Показувати покупцям</label></div><div class="form-actions"><button class="admin-btn" type="submit">Зберегти товар ↗</button><a class="admin-btn light" href="admin_products.php">Скасувати</a></div></form></section><?php endif;?>
<div class="admin-card"><div class="card-heading"><h2>Список товарів</h2><small><?=count($products)?> позицій</small></div><div class="admin-filters"><div class="admin-tabs"><a class="<?=$filter==='all'?'active':''?>" href="admin_products.php">Усі</a><a class="<?=$filter==='bouquet'?'active':''?>" href="?type=bouquet">Букети</a><a class="<?=$filter==='flower'?'active':''?>" href="?type=flower">Квіти</a><a class="<?=$filter==='gift'?'active':''?>" href="?type=gift">Подарунки</a></div><form class="admin-search"><input type="hidden" name="type" value="<?=e($filter)?>"><input name="q" value="<?=e($search)?>" placeholder="Пошук товарів…"><button>Знайти</button></form></div><div class="table-wrap"><table class="admin-table product-table"><thead><tr><th>Товар</th><th>Категорія</th><th>Ціна</th><th>Залишок</th><th>Статус</th><th>Дії</th></tr></thead><tbody><?php foreach($products as $p):?><tr><td><div class="admin-product"><img src="<?=e(photo($p['image'], $p['product_type']))?>" alt="" onerror="this.onerror=null;this.src='assets/flower-placeholder.svg'"><b><?=e($p['name'])?></b></div></td><td><?=e(product_type_name($p['product_type']))?></td><td><?=money($p['price'])?></td><td><span class="stock-indicator <?= (int)$p['stock']<=5?'critical':''?>"><?= (int)$p['stock']?> шт.</span></td><td><span class="status-pill <?=$p['is_active']?'approved':'rejected'?>"><?=$p['is_active']?'Опубліковано':'Приховано'?></span></td><td><div class="table-actions"><a href="?edit=<?=(int)$p['id']?>">Редагувати</a><form method="post"><?=csrf_input()?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?=(int)$p['id']?>"><button><?=$p['is_active']?'Приховати':'Показати'?></button></form></div></td></tr><?php endforeach;?><tr><?php if(!$products):?><td colspan="6">Товарів не знайдено.</td><?php endif;?></tr></tbody></table></div></div>
<?php require 'admin_footer.php';?></body></html>

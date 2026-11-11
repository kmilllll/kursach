<?php
require_once 'config.php';$admin_id=require_admin($conn);
$tbl=table_exists($conn,'messages')?'messages':(table_exists($conn,'message')?'message':'');
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['delete_message'])){
 check_csrf();$id=(int)($_POST['id']??0);
 if($tbl){$q=$conn->prepare("DELETE FROM `$tbl` WHERE id=?");$q->bind_param('i',$id);$q->execute();flash('success','Повідомлення видалено.');}redirect_to('admin_contacts.php');
}
$messages=$tbl?$conn->query("SELECT * FROM `$tbl` ORDER BY id DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC):[];
?>
<!doctype html><html lang="uk"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Повідомлення · flora.atelier admin</title><link rel="stylesheet" href="css/admin_style.css"><link rel="stylesheet" href="css/flor-admin-v8.css"></head><body><?php require 'admin_header.php';?><div class="admin-page-head"><div><span class="admin-eyebrow">НА ЗВ’ЯЗКУ З КЛІЄНТАМИ</span><h1>Повідомлення <em>✉</em></h1><p>Не пропускай жодне запитання про квіти.</p></div></div><div class="message-grid"><?php foreach($messages as $m):?><article class="admin-card message-card"><span class="admin-eyebrow">ПОВІДОМЛЕННЯ №<?=(int)$m['id']?></span><h2><?=e($m['name'])?></h2><a href="mailto:<?=e($m['email'])?>"><?=e($m['email'])?></a><small><?=e($m['number'])?></small><p><?=nl2br(e($m['message']))?></p><div class="message-actions"><a class="admin-btn outline" href="mailto:<?=e($m['email'])?>?subject=flora.atelier">Відповісти email ↗</a><form method="post"><?=csrf_input()?><input type="hidden" name="id" value="<?=(int)$m['id']?>"><button type="submit" name="delete_message" value="1" class="text-danger" data-confirm="Видалити повідомлення?">Видалити</button></form></div></article><?php endforeach;?><?php if(!$messages):?><div class="admin-card admin-empty">Повідомлень поки немає.</div><?php endif;?></div><?php require 'admin_footer.php';?></body></html>

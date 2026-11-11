<?php
require_once 'config.php';
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['login'])){
 check_csrf();$email=trim($_POST['email']??'');$pass=(string)($_POST['password']??'');
 $q=$conn->prepare('SELECT * FROM users WHERE email=? LIMIT 1');$q->bind_param('s',$email);$q->execute();$u=$q->get_result()->fetch_assoc();
 $valid=$u && password_verify($pass,$u['password']);
 if(!$valid && $u && preg_match('/^[a-f\d]{32}$/i',$u['password'])&&hash_equals(strtolower($u['password']),md5($pass))){$valid=true;$new=password_hash($pass,PASSWORD_DEFAULT);$q=$conn->prepare('UPDATE users SET password=? WHERE id=?');$q->bind_param('si',$new,$u['id']);$q->execute();}
 if($valid){session_regenerate_id(true);$_SESSION['user_id']=(int)$u['id'];$_SESSION['user_name']=$u['name'];$_SESSION['user_email']=$u['email'];if($u['user_type']==='admin'){$_SESSION['admin_id']=(int)$u['id'];$_SESSION['admin_name']=$u['name'];$_SESSION['admin_email']=$u['email'];redirect_to('admin_page.php');}unset($_SESSION['admin_id'],$_SESSION['admin_name'],$_SESSION['admin_email']);redirect_to('home.php');}
 $error='Неправильний email або пароль.';
}
?>
<!doctype html><html lang="uk"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Увійти — flora.atelier</title><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/flor-v5.css"><link rel="stylesheet" href="css/flor-v6.css"><link rel="stylesheet" href="css/flor-v7.css"><link rel="stylesheet" href="css/flor-v8.css"><link rel="stylesheet" href="css/flor-v9.css"></head><body><?php require 'header.php';?><main class="auth-section"><form method="post" class="auth-card"><?=csrf_input()?><span class="eyebrow">ЛАСКАВО ПРОСИМО</span><h1>З поверненням <em>♡</em></h1><p>Раді знову бачити тебе у flora.atelier</p><?php if($error):?><div class="inline-error"><?=e($error)?></div><?php endif;?><label>Email<input class="field" type="email" name="email" required autocomplete="email"></label><label>Пароль<input class="field" type="password" name="password" required autocomplete="current-password"></label><button name="login" value="1" class="btn btn-wide">Увійти ↗</button><p>Ще немає акаунту? <a href="register.php">Зареєструйся</a></p></form></main><?php require 'footer.php';?></body></html>

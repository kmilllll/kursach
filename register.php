<?php
require_once 'config.php';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['register'])){
 check_csrf();$name=trim($_POST['name']??'');$email=trim($_POST['email']??'');$pass=(string)($_POST['password']??'');$confirm=(string)($_POST['confirm']??'');
 if(mb_strlen($name)<2||mb_strlen($name)>100||!filter_var($email,FILTER_VALIDATE_EMAIL))$error='Перевірте ім’я та адресу email.';
 elseif(strlen($pass)<8)$error='Пароль повинен містити щонайменше 8 символів.';
 elseif($pass!==$confirm)$error='Паролі не збігаються.';
 else{
  $q=$conn->prepare('SELECT id FROM users WHERE email=?');$q->bind_param('s',$email);$q->execute();
  if($q->get_result()->num_rows)$error='Такий email вже зареєстрований.';
  else{$hash=password_hash($pass,PASSWORD_DEFAULT);$q=$conn->prepare("INSERT INTO users(name,email,password,user_type) VALUES(?,?,?,'user')");$q->bind_param('sss',$name,$email,$hash);$q->execute();flash('success','Акаунт створено! Тепер увійдіть.');redirect_to('login.php');}
 }
}
?>
<!doctype html><html lang="uk"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Реєстрація — flora.atelier</title><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/flor-v5.css"><link rel="stylesheet" href="css/flor-v6.css"><link rel="stylesheet" href="css/flor-v7.css"><link rel="stylesheet" href="css/flor-v8.css"><link rel="stylesheet" href="css/flor-v9.css"></head><body><?php require 'header.php';?><main class="auth-section"><form method="post" class="auth-card"><?=csrf_input()?><span class="eyebrow">СТАНЬ ЧАСТИНОЮ НАШОЇ ІСТОРІЇ</span><h1>Почнемо <em>знайомство</em></h1><?php if($error):?><div class="inline-error"><?=e($error)?></div><?php endif;?><label>Твоє ім’я<input class="field" name="name" required minlength="2" maxlength="100" value="<?=e($_POST['name']??'')?>"></label><label>Email<input class="field" type="email" name="email" required value="<?=e($_POST['email']??'')?>"></label><label>Пароль<input class="field" type="password" name="password" required minlength="8"></label><label>Повтори пароль<input class="field" type="password" name="confirm" required minlength="8"></label><button name="register" value="1" class="btn btn-wide">Створити акаунт ↗</button><p>Вже зареєстровані? <a href="login.php">Увійти</a></p></form></main><?php require 'footer.php';?></body></html>

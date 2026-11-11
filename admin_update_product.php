<?php
require_once 'config.php';require_admin($conn);
$id=(int)($_GET['update']??$_GET['edit']??0);
redirect_to('admin_products.php'.($id?'?edit='.$id:'?new=1'));

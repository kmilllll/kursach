<?php
// FLORA.ATELIER v3: єдина точка налаштування сесії та бази.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli('localhost', 'root', '', 'shop_db');
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit('Не вдалося підключитися до shop_db. Запустіть MySQL у XAMPP і перевірте config.php.');
}
$user_id = (int) ($_SESSION['user_id'] ?? 0);

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function money($n): string { return number_format((float)$n, 0, ',', ' ') . ' ₴'; }
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_input(): string { return '<input type="hidden" name="csrf_token" value="'.e(csrf_token()).'">'; }
function check_csrf(): void {
    if (!isset($_POST['csrf_token']) || !hash_equals(csrf_token(), (string)$_POST['csrf_token'])) {
        http_response_code(403);
        exit('Форма застаріла. Оновіть сторінку і спробуйте ще раз.');
    }
}
function redirect_to(string $url): void { header('Location: '.$url); exit; }
function flash(string $type, string $message): void { $_SESSION['flashes'][] = ['type'=>$type, 'message'=>$message]; }
function notices(): void {
    foreach (($_SESSION['flashes'] ?? []) as $f) echo '<div class="notice '.($f['type']==='error'?'notice-error':'notice-success').'">'.e($f['message']).'</div>';
    unset($_SESSION['flashes']);
}
function require_user(): int { $id=(int)($_SESSION['user_id']??0); if (!$id) redirect_to('login.php'); return $id; }
function require_admin(mysqli $conn): int {
    $id=(int)($_SESSION['admin_id']??0);
    if (!$id) redirect_to('login.php');
    $q=$conn->prepare("SELECT id FROM users WHERE id=? AND user_type='admin'");
    $q->bind_param('i',$id); $q->execute();
    if (!$q->get_result()->num_rows) { unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email']); redirect_to('login.php'); }
    return $id;
}
function table_exists(mysqli $conn, string $name): bool {
    // SHOW TABLES LIKE ? is not supported as a prepared statement by some
    // MariaDB versions bundled with XAMPP. Check metadata instead.
    static $cache = [];
    if (array_key_exists($name, $cache)) {
        return $cache[$name];
    }
    $query = $conn->prepare(
        'SELECT COUNT(*) AS found FROM information_schema.TABLES '
        . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $query->bind_param('s', $name);
    $query->execute();
    $row = $query->get_result()->fetch_assoc();
    $query->close();
    return $cache[$name] = ((int)($row['found'] ?? 0) > 0);
}
function cart_count(mysqli $conn, int $uid): int {
    if(!$uid)return 0;
    $q=$conn->prepare('SELECT COALESCE(SUM(quantity),0) AS total FROM cart WHERE user_id=?');
    $q->bind_param('i',$uid);$q->execute();return (int)$q->get_result()->fetch_assoc()['total'];
}
function photo($name, string $type = ''): string {
    // Фото демонстраційних композицій: реальні фотографії Pexels.
    // Користувацькі завантаження адміністратором лишаються у uploaded_img.
    $base=basename(str_replace('\\', '/', (string)$name));
    $singles = [
       'pink roses.jpg'=>'pink_rose','red tulipa.jpg'=>'red_tulip',
       'yellow tulipa.jpg'=>'yellow_tulip','lavendor rose.jpg'=>'lavender_rose',
       'cottage rose.jpg'=>'spray_rose','white lily.jpg'=>'white_lily'
    ];
    // Справжні предметні фотографії з відкритої фотоколекції Pexels.
    // Для навчального сайту, не AI, не з макетів користувача.
    $bouquetIds = [
      'pink bouquet.jpg'=>31265182,      // ніжно-рожеві та білі троянди в пакуванні
      'white bouquet.jpg'=>34701051,     // світлі троянди на світлому тлі
      'yellow queen rose.jpg'=>23731967, // м'який пастельний квітковий букет
      'red tulipa.jpg'=>16244984,        // насичена композиція
      'pink queen rose.jpg'=>28759039,
      'bloomnation.jpg'=>31265182,
      'fun bloomnation.jpg'=>34701051
    ];
    // 1) flower box  2) wicker basket  3) flower basket  4) floral hat box
    $giftIds = [
       'pink bouquet.jpg'=>1233440,        // рожеві троянди у квітковому боксі
       'white bouquet.jpg'=>36106446,      // пастельна композиція в кошику
       'beach florist.jpg'=>31870268,      // квіти у подарунковій коробці
       'gift_hatbox_blush.jpg'=>1233440,
       'gift_basket_rose.jpg'=>36106446,
       'gift_box_pastel.jpg'=>31870268,
       'gift_hatbox_peony.jpg'=>35285625,
    ];
    if ($type==='flower' && isset($singles[$base])) return 'media/singles/'.$singles[$base].'.jpg';
    if ($type==='bouquet' && isset($bouquetIds[$base])) {
        $id=$bouquetIds[$base];
        $local='media/pexels/bouquet-'.$id.'.jpg';
        return is_file(__DIR__.'/'.$local) ? $local : 'https://images.pexels.com/photos/'.$id.'/pexels-photo-'.$id.'.jpeg?auto=compress&cs=tinysrgb&w=1200';
    }
    if ($type==='gift' && isset($giftIds[$base])) {
        $id=$giftIds[$base];
        $local='media/pexels/gift-'.$id.'.jpg';
        return is_file(__DIR__.'/'.$local) ? $local : 'https://images.pexels.com/photos/'.$id.'/pexels-photo-'.$id.'.jpeg?auto=compress&cs=tinysrgb&w=1200';
    }
    if (isset($singles[$base])) return 'media/singles/'.$singles[$base].'.jpg';
    if (!$base) return 'assets/flower-placeholder.svg';
    return 'uploaded_img/'.rawurlencode($base);
}
function flower_cutout($name): string {
    $base = basename(str_replace('\\', '/', (string)$name));
    $mapping = [
        'pink roses.jpg'=>'pink_rose', 'red tulipa.jpg'=>'red_tulip',
        'yellow tulipa.jpg'=>'yellow_tulip', 'lavendor rose.jpg'=>'lavender_rose',
        'cottage rose.jpg'=>'spray_rose', 'white lily.jpg'=>'white_lily'
    ];
    return isset($mapping[$base]) ? 'media/cutouts/'.$mapping[$base].'.png' : photo($name,'flower');
}
function product_type_name(string $t): string {
    return ['flower'=>'Квіти поштучно','bouquet'=>'Готові букети','gift'=>'Квіти в коробках'][$t]??'Товари';
}
function bouquet_data($raw): array {
    if (!$raw) return [];
    $d=json_decode((string)$raw,true);if(!is_array($d))return [];
    // Підтримка конструктора попередньої версії, де custom_data був простим масивом.
    if (array_is_list($d))return ['items'=>$d,'shape'=>'round','wrap'=>'cream','note'=>''];
    return $d;
}
function bouquet_needs(array $cartRows): array {
    $needs=[];
    foreach($cartRows as $row){
        $mult=(int)$row['quantity'];
        if($mult<1)throw new RuntimeException('Некоректна кількість у кошику.');
        if(!empty($row['custom_data'])){
            $data=bouquet_data($row['custom_data']);
            if(empty($data['items'])||!is_array($data['items']))throw new RuntimeException('Помилка складу авторського букета.');
            foreach($data['items'] as $flower){
                $pid=(int)($flower['id']??0);$qty=(int)($flower['quantity']??0);
                if($pid<1||$qty<1)throw new RuntimeException('Некоректний склад авторського букета.');
                $needs[$pid]=($needs[$pid]??0)+$qty*$mult;
            }
        }else{
            $pid=(int)$row['pid'];if($pid<1)throw new RuntimeException('Некоректний товар у кошику.');
            $needs[$pid]=($needs[$pid]??0)+$mult;
        }
    }
    ksort($needs,SORT_NUMERIC);
    return $needs;
}
function change_stock(mysqli $conn, array $needs, bool $subtract): void {
    foreach($needs as $pid=>$qty){
        $id=(int)$pid; $n=(int)$qty;
        $q=$conn->prepare('SELECT name,stock,is_active FROM products WHERE id=? FOR UPDATE');
        $q->bind_param('i',$id);$q->execute();$p=$q->get_result()->fetch_assoc();
        if(!$p)throw new RuntimeException('Один із товарів більше не існує.');
        if($subtract && (!(int)$p['is_active'] || (int)$p['stock']<$n))throw new RuntimeException('Недостатньо в наявності: '.$p['name']);
        $new=$subtract?(int)$p['stock']-$n:(int)$p['stock']+$n;
        $u=$conn->prepare('UPDATE products SET stock=? WHERE id=?');$u->bind_param('ii',$new,$id);$u->execute();
    }
}
// Простий Unicode fallback, якщо mbstring вимкнено в конфігурації PHP.
if (!function_exists('mb_strlen')) {
    function mb_strlen(string $value): int { return preg_match_all('/./us', $value); }
}
if (!function_exists('mb_substr')) {
    function mb_substr(string $value, int $start, ?int $length=null): string {
        $chars=preg_split('//u',$value,-1,PREG_SPLIT_NO_EMPTY);
        return implode('',array_slice($chars,$start,$length));
    }
}
if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth(string $value, int $start, int $width, string $trim=''): string {
        $s=mb_substr($value,$start,$width);
        return mb_strlen($value)>$start+$width ? $s.$trim : $s;
    }
}

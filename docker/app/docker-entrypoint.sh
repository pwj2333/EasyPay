#!/bin/sh
set -eu

APP_DIR=/var/www/html
CONFIG_FILE="$APP_DIR/config.php"

if [ ! -f "$CONFIG_FILE" ]; then
    php <<'PHP'
<?php
function env_value($name, $default) {
    $value = getenv($name);
    return $value === false || $value === '' ? $default : $value;
}

$dbconfig = [
    'host' => env_value('EASYPAY_DB_HOST', 'db'),
    'port' => (int) env_value('EASYPAY_DB_PORT', '3306'),
    'user' => env_value('EASYPAY_DB_USER', 'easypay'),
    'pwd' => env_value('EASYPAY_DB_PASSWORD', 'easypay'),
    'dbname' => env_value('EASYPAY_DB_NAME', 'easypay'),
    'dbqz' => env_value('EASYPAY_DB_PREFIX', 'pay'),
];

file_put_contents('/var/www/html/config.php', "<?php\n\n\$dbconfig = ".var_export($dbconfig, true).";\n");
PHP
fi

mkdir -p "$APP_DIR/assets/files"
chown -R www-data:www-data "$APP_DIR/assets/files"
chown www-data:www-data "$CONFIG_FILE" 2>/dev/null || true

# ponytail: fixed 60s DB wait covers normal Compose startup; use a health endpoint if startup work grows.
tries=60
while [ "$tries" -gt 0 ]; do
    if php >/dev/null 2>&1 <<'PHP'
<?php
require '/var/www/html/config.php';
$dsn = 'mysql:host='.$dbconfig['host'].';port='.$dbconfig['port'].';dbname='.$dbconfig['dbname'].';charset=utf8mb4';
new PDO($dsn, $dbconfig['user'], $dbconfig['pwd'], [PDO::ATTR_TIMEOUT => 2]);
PHP
    then
        break
    fi
    tries=$((tries - 1))
    sleep 1
done

if [ "$tries" -eq 0 ]; then
    echo "EasyPay database is not reachable" >&2
    exit 1
fi

php >/dev/null <<'PHP'
<?php
require '/var/www/html/config.php';

if (!preg_match('/^[A-Za-z0-9_]+$/', $dbconfig['dbqz'])) {
    throw new RuntimeException('Invalid database table prefix');
}

$table = '`'.$dbconfig['dbqz'].'_config`';
$dsn = 'mysql:host='.$dbconfig['host'].';port='.$dbconfig['port'].';dbname='.$dbconfig['dbname'].';charset=utf8mb4';
$pdo = new PDO($dsn, $dbconfig['user'], $dbconfig['pwd'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$defaults = [
    'syskey' => 'A1B2C3D4E5F6a7b8c9d0e1f2g3h4i5j6',
    'cronkey' => '538291',
];

$stmt = $pdo->prepare("SELECT v FROM {$table} WHERE k = :k");
$save = $pdo->prepare("REPLACE INTO {$table} (k, v) VALUES (:k, :v)");

foreach ($defaults as $key => $default) {
    $stmt->execute([':k' => $key]);
    $value = $stmt->fetchColumn();
    if ($value === false || $value === '' || $value === $default) {
        $newValue = $key === 'cronkey' ? (string) random_int(100000, 999999) : bin2hex(random_bytes(24));
        $save->execute([':k' => $key, ':v' => $newValue]);
    }
}

function easypay_mojibake_default($value) {
    return is_string($value)
        && $value !== ''
        && preg_match('/\p{Han}/u', $value) !== 1
        && preg_match('/[^\x00-\x7F]/', $value) === 1;
}

$configDefaults = [
    'sitename' => '聚合支付平台',
    'title' => '聚合支付 - 行业领先的免签约支付平台',
    'keywords' => '聚合支付,支付宝免签约即时到账,财付通免签约,微信免签约支付,QQ钱包免签约,免签约支付',
    'description' => '聚合支付是XX公司旗下的免签约支付产品，完美解决支付难题，一站式接入支付宝，微信，财付通，QQ钱包,微信wap，帮助开发者快速集成到自己相应产品，效率高，见效快，费率低！',
    'orgname' => 'XX公司',
    'blockalert' => '温馨提醒该商品禁止出售，如有疑问请联系网站客服！',
    'transfer_name' => '聚合支付平台',
    'transfer_desc' => '聚合支付平台自动结算',
];

foreach ($configDefaults as $key => $default) {
    $stmt->execute([':k' => $key]);
    if (easypay_mojibake_default($stmt->fetchColumn())) {
        $save->execute([':k' => $key, ':v' => $default]);
    }
}

$typeTable = '`'.$dbconfig['dbqz'].'_type`';
$typeDefaults = [
    1 => '支付宝',
    2 => '微信支付',
    3 => 'QQ钱包',
    4 => '网银支付',
    5 => '京东支付',
];
$selectType = $pdo->prepare("SELECT name FROM {$typeTable} WHERE id = :id");
$saveType = $pdo->prepare("UPDATE {$typeTable} SET name = :name WHERE id = :id");

foreach ($typeDefaults as $id => $name) {
    $selectType->execute([':id' => $id]);
    if (easypay_mojibake_default($selectType->fetchColumn())) {
        $saveType->execute([':id' => $id, ':name' => $name]);
    }
}
PHP

exec docker-php-entrypoint "$@"

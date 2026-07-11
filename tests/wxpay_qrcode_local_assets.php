<?php

$page = file_get_contents(__DIR__.'/../includes/pages/wxpay_qrcode.php');
$assets = [
    'jquery-1.12.4.min.js',
    'layer-3.1.1.min.js',
    'jquery.qrcode-1.0.min.js',
    'clipboard-1.7.1.min.js',
];

assert(strpos($page, '$cdnpublic') === false);

foreach ($assets as $asset) {
    assert(strpos($page, '/assets/vendor/wxpay/'.$asset) !== false);
    assert(filesize(__DIR__.'/../assets/vendor/wxpay/'.$asset) > 0);
}

echo "WeChat Pay QR local assets check OK\n";

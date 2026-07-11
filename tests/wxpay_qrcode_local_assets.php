<?php

$page = file_get_contents(__DIR__.'/../includes/pages/wxpay_qrcode.php');
$assets = [
    'jquery/1.12.4/jquery.min.js',
    'layer/3.1.1/layer.min.js',
    'jquery.qrcode/1.0/jquery.qrcode.min.js',
    'clipboard.js/1.7.1/clipboard.min.js',
];

assert(strpos($page, '$cdnpublic') === false);

foreach ($assets as $asset) {
    assert(strpos($page, '/assets/cdnjs/'.$asset) !== false);
    assert(filesize(__DIR__.'/../assets/cdnjs/'.$asset) > 0);
}

echo "WeChat Pay QR local assets check OK\n";

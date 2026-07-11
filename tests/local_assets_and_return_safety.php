<?php

$root = realpath(__DIR__.'/..');
$sourceExtensions = ['php', 'html', 'css', 'js'];
$forbiddenStaticHosts = [
    'cdnjs'.'.cloudflare.com',
    'cdn'.'.bootcdn.net',
    'lib'.'.baomitu.com',
    'cdn'.'.staticfile.net',
    'mirrors'.'.sustech.edu.cn',
    's4'.'.zstatic.net',
    'fonts'.'.googleapis.com',
    'static'.'.geetest.com',
    'res'.'.wx.qq.com/open/libs',
    'open'.'.mobile.qq.com/sdk',
    'wx'.'.gtimg.com/pay_h5',
];
$skipPaths = [
    $root.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR,
    $root.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'cdnjs'.DIRECTORY_SEPARATOR,
    $root.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'external'.DIRECTORY_SEPARATOR,
];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($iterator as $file) {
    if (!$file->isFile() || !in_array(strtolower($file->getExtension()), $sourceExtensions, true)) continue;

    $path = $file->getPathname();
    foreach ($skipPaths as $skipPath) {
        if (strpos($path, $skipPath) === 0) continue 2;
    }

    $source = file_get_contents($path);
    assert(preg_match('~<(?:script|link)[^>]+(?:src|href)=[^>]*(?:https?:)?//~i', $source) !== 1, $path);
    assert(preg_match('~@import[^;]*(?:https?:)?//~i', $source) !== 1, $path);
    foreach ($forbiddenStaticHosts as $host) {
        assert(strpos($source, $host) === false, $path.': '.$host);
    }

    preg_match_all('~\$cdnpublic\s*\?>\s*([^"\'<]+)~', $source, $matches);
    foreach ($matches[1] as $asset) {
        assert(is_file($root.'/assets/cdnjs/'.trim($asset)), $asset);
    }
}

$common = file_get_contents($root.'/includes/common.php');
$adminHead = file_get_contents($root.'/admin/head.php');
assert(strpos($common, "\$cdnpublic = '/assets/cdnjs/';") !== false);
assert(strpos($adminHead, "\$cdnpublic = '/assets/cdnjs/';") !== false);

require_once $root.'/includes/functions.php';
assert(getSafeReturnUrl('about:blank') === '/payok.html');
assert(getSafeReturnUrl('javascript:window.close()') === '/payok.html');
assert(getSafeReturnUrl('') === '/payok.html');
assert(getSafeReturnUrl('/user/order.php') === '/user/order.php');

echo "Local assets and payment return safety check OK\n";

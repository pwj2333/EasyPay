<?php

foreach (['pay_channel.php', 'uset.php'] as $file) {
    $source = file_get_contents(__DIR__.'/../admin/'.$file);

    assert(strpos($source, 'window.open(data.url)') === false);
    assert(strpos($source, 'target="_blank"') !== false);
    assert(strpos($source, 'ajax_pay.php?act=testpay&redirect=1') !== false);
    assert(strpos($source, 'form[0].submit()') !== false);
}

$backend = file_get_contents(__DIR__.'/../admin/ajax_pay.php');
assert(strpos($backend, "isset(\$_GET['redirect'])") !== false);
assert(strpos($backend, "header('Location: ./testsubmit.php?trade_no='") !== false);

echo "Admin test payment navigation check OK\n";

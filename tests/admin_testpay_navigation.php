<?php

foreach (['pay_channel.php', 'uset.php'] as $file) {
    $source = file_get_contents(__DIR__.'/../admin/'.$file);

    assert(strpos($source, 'window.open(data.url)') === false);
    assert(strpos($source, 'window.location.assign(data.url)') !== false);
}

echo "Admin test payment navigation check OK\n";

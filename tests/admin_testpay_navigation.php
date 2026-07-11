<?php

foreach (['pay_channel.php', 'uset.php'] as $file) {
    $source = file_get_contents(__DIR__.'/../admin/'.$file);

    assert(strpos($source, 'window.open(data.url)') === false);
    assert(strpos($source, "window.open('about:blank', '_blank')") !== false);
    assert(strpos($source, 'payWindow.location.replace(data.url)') !== false);
    assert(substr_count($source, 'payWindow.close()') === 2);
}

echo "Admin test payment navigation check OK\n";

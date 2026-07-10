<?php

$head = file_get_contents(__DIR__.'/../template/index1/head.php');
$index = file_get_contents(__DIR__.'/../template/index1/index.php');

assert(strpos($head, 'jquery/1.12.4/jquery.min.js') === false);
assert(strpos($head, 'jquery.dropotron/1.4.3/jquery.dropotron.min.js') === false);
assert(strpos($head, 'font-awesome.min.css" media="print" onload=') !== false);
assert(strpos($index, 'jQuery') === false);
assert(strpos($index, 'document.body.classList.add') !== false);

echo "Template index1 CDN blocking check OK\n";

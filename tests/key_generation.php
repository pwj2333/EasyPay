<?php

$windowsConfig = dirname(PHP_BINARY).'/extras/ssl/openssl.cnf';
if(is_file($windowsConfig)){
	putenv('OPENSSL_CONF='.$windowsConfig);
}

require dirname(__DIR__).'/includes/functions.php';

$pair = generate_key_pair();
assert(base64_decode($pair['public_key'], true) !== false);
assert(base64_decode($pair['private_key'], true) !== false);

echo "Key generation OK\n";

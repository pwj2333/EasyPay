<?php

function easypay_mojibake_default($value) {
	return is_string($value)
		&& $value !== ''
		&& preg_match('/\p{Han}/u', $value) !== 1
		&& preg_match('/[^\x00-\x7F]/', $value) === 1;
}

assert(easypay_mojibake_default('æ”¯ä»˜å®') === true);
assert(easypay_mojibake_default('支付宝') === false);
assert(easypay_mojibake_default('PayPal') === false);

echo "Mojibake detection OK\n";

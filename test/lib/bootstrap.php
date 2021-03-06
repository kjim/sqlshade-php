<?php
require_once(dirname(__FILE__).'/../vendor/lime.php');

function is_tokens_order($t, $tokens, $expected, $message = null)
{
    $actual = array();
    foreach ($tokens as $token) {
        $actual[] = array($token->getType(), $token->getValue());
    }
    $t->is_deeply($actual, $expected, $message);
}

date_default_timezone_set('Asia/Tokyo');

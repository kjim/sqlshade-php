<?php
require_once(dirname(__FILE__).'/bootstrap.php');

$t = new lime_test();
$lexer = new Twig_Lexer(new Twig_Environment());

// @test
$stream = $lexer->tokenize("{{ username }} is fine.");

$token = $stream->next();
$t->is($token->getType(), Twig_Token::VAR_START_TYPE, '`{{` is VAR_START_TYPE');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::NAME_TYPE, '`username` is NAME_TYPE');
$t->is($token->getValue(), 'username', 'value is "username"');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::VAR_END_TYPE, '`}}` is VAR_END_TYPE');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::TEXT_TYPE, '` is fine.` is TEXT_TYPE');
$t->is($token->getValue(), ' is fine.', 'text value is " is fine."');

try {
  $token = $stream->next();
  $t->fail();
}
catch (Twig_SyntaxError $e) {
  $t->pass('no more token');
}

// @test
$stream = $lexer->tokenize("{% if True %}{% endif %}");

$token = $stream->next();
$t->is($token->getType(), Twig_Token::BLOCK_START_TYPE, '`{%` is BLOCK_START_TYPE');
$t->is($token->getValue(), '', 'BLOCK_START_TYPE has no value');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::NAME_TYPE, '`if` is NAME_TYPE');
$t->is($token->getValue(), 'if', 'value is if');
$token = $stream->next();
$t->is($token->getType(), Twig_Token::NAME_TYPE, '`True` is NAME_TYPE');
$t->is($token->getValue(), 'True', 'value is True');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::BLOCK_END_TYPE, '`%}` is BLOCK_END_TYPE');
$t->is($token->getValue(), '', 'BLOCK_START_TYPE has no value');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::BLOCK_START_TYPE, '`{%` is BLOCK_START_TYPE');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::NAME_TYPE, '`endif` is NAME_TYPE');
$t->is($token->getValue(), 'endif', 'value is endif');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::BLOCK_END_TYPE, '%} is BLOCK_END_TYPE');

// @setup
$lexer = new Twig_Lexer(new Twig_Environment(),
                        array(
                          'tag_block'    => array('/*#', '*/'),
                          'tag_variable' => array('/*:', '*/'),
                          ));

// @test
$stream = $lexer->tokenize("/*:item*/'generated uid'");

$token = $stream->next();
$t->is($token->getType(), Twig_Token::VAR_START_TYPE, 'VAR_START_TYPE');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::NAME_TYPE, 'NAME_TYPE');
$t->is($token->getValue(), 'item', 'value is "item"');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::VAR_END_TYPE, 'VAR_END_TYPE');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::TEXT_TYPE, 'TEXT_TYPE');
$t->is($token->getValue(), "'generated uid'", "value is 'generated uid'");

// @test
$stream = $lexer->tokenize("this is text");
$t->is($stream->next()->getValue(), 'this is text');

$stream = $lexer->tokenize("'here!!' label");
$t->is($stream->next()->getValue(), "'here!!' label");

$stream = $lexer->tokenize("3523.382, 2e10");
$t->is($stream->next()->getValue(), "3523.382, 2e10");

// @test
$stream = $lexer->tokenize("/*# if True *//*# endif */");

$token = $stream->next();
$t->is($token->getType(), Twig_Token::BLOCK_START_TYPE, 'BLOCK_START_TYPE');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::NAME_TYPE, 'NAME_TYPE');
$t->is($token->getValue(), 'if', 'value is if');
$token = $stream->next();
$t->is($token->getType(), Twig_Token::NAME_TYPE, 'NAME_TYPE');
$t->is($token->getValue(), 'True', 'value is True');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::BLOCK_END_TYPE, 'BLOCK_END_TYPE');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::BLOCK_START_TYPE, 'BLOCK_START_TYPE');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::NAME_TYPE, 'NAME_TYPE');
$t->is($token->getValue(), 'endif', 'value is endif');

$token = $stream->next();
$t->is($token->getType(), Twig_Token::BLOCK_END_TYPE, 'BLOCK_END_TYPE');

// @test
$stream = $lexer->tokenize("/* comment */");

$token = $stream->next();
$t->is($token->getType(), Twig_Token::TEXT_TYPE, '/* comment */ is TEXT_TYPE');

try {
  $stream->next();
  $t->fail();
}
catch (Twig_SyntaxError $e) {
  $t->pass('no more token');
}

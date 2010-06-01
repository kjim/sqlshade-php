<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/TokenParser/Substitute.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Parser.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Environment.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Substitute.php');

$t = new lime_test();

// @setup
$env = new SQLShade_Environment();

// @test
$stream1 = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::VAR_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 1),
        new SQLShade_Token(SQLShade_Token::VAR_END_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE, "'faketext'", 1),

        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    'example.sql'
    );
$token = $stream1->next(); // item

$driveparser = new SQLShade_Parser($env);
$driveparser->setStream($stream1);
$tokenparser = new SQLShade_TokenParser_Substitute();
$tokenparser->setParser($driveparser);

$node = $tokenparser->parse($token);
echo($node);
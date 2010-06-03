<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/TokenParser/Substitute.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Parser.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Environment.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Substitute.php');

$t = new lime_test();

// @setup
$env = new SQLShade_Environment();
$driveparser = new SQLShade_Parser($env);
$tokenparser = new SQLShade_TokenParser_Substitute();
$tokenparser->setParser($driveparser);

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
$driveparser->setStream($stream1);

$node = $tokenparser->parse($token);
$t->ok($node instanceof SQLShade_Node_Substitute,
       'return node is instance of SQLShade_Node_Substitute');

$t->ok($node->getIdent() instanceof SQLShade_Node_Expression_Name,
       'getIdent() return value is instance of SQLShade_Node_Expression_Name');
$t->is($node->getIdent()->getName(), 'item');
$t->is($node->getFaketext(), "'faketext'", "faketext is 'faketext'");

$token = $stream1->getCurrent();
$t->is($token->getValue(), '', "getValue() not return 'faketext' value");

// @test
$stream2 = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::VAR_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 1),
        new SQLShade_Token(SQLShade_Token::VAR_END_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE, "123456 AND ...", 1),

        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    'example.sql'
    );
$token = $stream2->next();
$driveparser->setStream($stream2);

$node = $tokenparser->parse($token);
$t->ok($node instanceof Sqlshade_Node_Substitute);
$t->is($node->getFaketext(), "123456", "faketext is 123456");

$token = $stream2->getCurrent();
$t->is($token->getValue(), ' AND ...', "getValue() return value has no literal 123456");

// @test
$shouldBeCloseParen = ")";
$parse = "_parseUntilEndOfFaketext";
$t->is(SQLShade_TokenParser_Substitute::$parse("('foo')", $shouldBeCloseParen), 7);
$t->is(SQLShade_TokenParser_Substitute::$parse("('foo') ", $shouldBeCloseParen), 7);
$t->is(SQLShade_TokenParser_Substitute::$parse("('foo')\nA", $shouldBeCloseParen), 7);
$t->is(SQLShade_TokenParser_Substitute::$parse("('foo')\rA", $shouldBeCloseParen), 7);
$t->is(SQLShade_TokenParser_Substitute::$parse("('foo)')", $shouldBeCloseParen), 8);
$t->is(SQLShade_TokenParser_Substitute::$parse("('foo)') ", $shouldBeCloseParen), 8);
$t->is(SQLShade_TokenParser_Substitute::$parse("('foo) \\'bar ')", $shouldBeCloseParen), 15);
$t->is(SQLShade_TokenParser_Substitute::$parse("('foo) \\'bar ') ", $shouldBeCloseParen), 15);

$t->is(SQLShade_TokenParser_Substitute::$parse("('foo', 'bar', 'baz')", $shouldBeCloseParen), 21);
$t->is(SQLShade_TokenParser_Substitute::$parse("('foo', 'bar', 'baz') ", $shouldBeCloseParen), 21);
$t->is(SQLShade_TokenParser_Substitute::$parse("(1, 2, 3) ", $shouldBeCloseParen), 9);
$t->is(SQLShade_TokenParser_Substitute::$parse("(CURRENT_TIMESTAMP, now(), '2010-03-06 12:00:00')", $shouldBeCloseParen), 49);
$t->is(SQLShade_TokenParser_Substitute::$parse("(CURRENT_TIMESTAMP, now(), '2010-03-06 12:00:00') ", $shouldBeCloseParen), 49);

$t->is(SQLShade_TokenParser_Substitute::$parse("CURRENT_TIMESTAMP"), 17);
$t->is(SQLShade_TokenParser_Substitute::$parse("CURRENT_TIMESTAMP "), 17);
$t->is(SQLShade_TokenParser_Substitute::$parse("CURRENT_TIMESTAMP\nA"), 17);
$t->is(SQLShade_TokenParser_Substitute::$parse("now()"), 5);
$t->is(SQLShade_TokenParser_Substitute::$parse("now() "), 5);
$t->is(SQLShade_TokenParser_Substitute::$parse("(cast('323' as Number), to_int(now()))", $shouldBeCloseParen), 38);
$t->is(SQLShade_TokenParser_Substitute::$parse("(cast('323' as Number), to_int(now())) ", $shouldBeCloseParen), 38);

$t->is(SQLShade_TokenParser_Substitute::$parse(""), -1);
$t->is(SQLShade_TokenParser_Substitute::$parse(" "), -1);
$t->is(SQLShade_TokenParser_Substitute::$parse("("), -1);
$t->is(SQLShade_TokenParser_Substitute::$parse("( "), -1);
$t->is(SQLShade_TokenParser_Substitute::$parse(")"), -1);
$t->is(SQLShade_TokenParser_Substitute::$parse(") "), -1);
$t->is(SQLShade_TokenParser_Substitute::$parse("()", $shouldBeCloseParen), 2);

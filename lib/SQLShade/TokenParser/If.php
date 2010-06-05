<?php
require_once(dirname(__FILE__).'/../TokenParser.php');
require_once(dirname(__FILE__).'/../Token.php');
require_once(dirname(__FILE__).'/../Node/If.php');
require_once(dirname(__FILE__).'/../Node/Expression/Name.php');

class SQLShade_TokenParser_If extends SQLShade_TokenParser {

    public function parse(SQLShade_Token $token) {
        $lineno = $token->getLine();

        $expr = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);
        $compound = $this->parser->subparse(array($this, 'decideIfEnd'), true);
        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);

        return new SQLShade_Node_If($expr, $compound, $lineno, null);
    }

    public function deparse($node) {
        $lineno = $node->getLine();
        $expressionParser = $this->parser->getExpressionParser();

        $tokens = array();
        $tokens[] = new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', $lineno);
        $tokens[] = new SQLShade_Token(SQLShade_Token::NAME_TYPE, $this->getTag(), 'n/a');
        $tokens = array_merge($tokens, $expressionParser->deparseExpression($node->getExpr()));
        $tokens[] = new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', $lineno);

        $tokens = array_merge($tokens, $this->parser->subdeparse($node->getBody()));

        $tokens[] = new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 'n/a');
        $tokens[] = new SQLShade_Token(SQLShade_Token::NAME_TYPE, $this->getEndTag(), 'n/a');
        $tokens[] = new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 'n/a');

        return $tokens;
    }

    public function decideIfEnd($token) {
        return $token->test($this->getEndTag());
    }

    public function getTag() {
        return 'if';
    }

    public function getEndTag() {
        return 'endif';
    }

}

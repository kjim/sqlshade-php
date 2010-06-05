<?php
require_once(dirname(__FILE__).'/../TokenParser.php');
require_once(dirname(__FILE__).'/../Token.php');
require_once(dirname(__FILE__).'/../Node/For.php');
require_once(dirname(__FILE__).'/../Node/Expression/AssignName.php');
require_once(dirname(__FILE__).'/../Node/Expression/Name.php');

class SQLShade_TokenParser_For extends SQLShade_TokenParser {

    public function parse(SQLShade_Token $token) {
        $lineno = $token->getLine();

        $alias = $this->parser->getExpressionParser()->parsePrimaryExpression(true);

        $this->parser->getStream()->expect(SQLShade_Token::NAME_TYPE, 'in');

        $sequence = $this->parser->getExpressionParser()->parsePrimaryExpression();
        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);
        $compound = $this->parser->subparse(array($this, 'decideForEnd'), true);
        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);

        return new SQLShade_Node_For($alias, $sequence, $compound, $lineno, null);
    }

    public function deparse($node) {
        $lineno = $node->getLine();

        $tokens = array();
        $tokens[] = new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', $lineno);
        $tokens[] = new SQLShade_Token(SQLShade_Token::NAME_TYPE, $this->getTag(), 'n/a');
        $expressionParser = $this->parser->getExpressionParser();
        $tokens = array_merge($tokens, $expressionParser->deparseExpression($node->getItem()));
        $tokens[] = new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'in', $lineno);
        $tokens = array_merge($tokens, $expressionParser->deparseExpression($node->getIdent()));
        $tokens[] = new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', $lineno);

        $tokens = array_merge($tokens, $this->parser->subdeparse($node->getBody()));

        $tokens[] = new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 'n/a');
        $tokens[] = new SQLShade_Token(SQLShade_Token::NAME_TYPE, $this->getEndTag(), 'n/a');
        $tokens[] = new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 'n/a');

        return $tokens;
    }

    public function decideForEnd($token) {
        return $token->test($this->getEndTag());
    }

    public function getTag() {
        return 'for';
    }

    public function getEndTag() {
        return 'endfor';
    }

}

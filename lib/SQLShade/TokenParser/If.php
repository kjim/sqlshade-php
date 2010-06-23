<?php
require_once(dirname(__FILE__).'/../TokenParser.php');
require_once(dirname(__FILE__).'/../Token.php');
require_once(dirname(__FILE__).'/../Node/If.php');
require_once(dirname(__FILE__).'/../Node/Expression/Name.php');

class SQLShade_TokenParser_If extends SQLShade_TokenParser
{
    public function parse(SQLShade_Token $token)
    {
        $lineno = $token->getLine();

        $expr = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);
        $compound = $this->parser->subparse(array($this, 'decideEnd'), true);
        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);

        return new SQLShade_Node_If($expr, $compound, $lineno, null);
    }

    public function decideEnd($token)
    {
        return $token->test($this->getEndTag());
    }

    public function getTag()
    {
        return 'if';
    }

    public function getEndTag()
    {
        return 'endif';
    }
}

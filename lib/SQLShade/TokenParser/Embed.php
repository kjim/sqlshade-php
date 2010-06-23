<?php
require_once(dirname(__FILE__).'/../TokenParser.php');
require_once(dirname(__FILE__).'/../TokenParser/If.php');
require_once(dirname(__FILE__).'/../Token.php');
require_once(dirname(__FILE__).'/../Node/Embed.php');
require_once(dirname(__FILE__).'/../Node/Expression/Name.php');

class SQLShade_TokenParser_Embed extends SQLShade_TokenParser_If
{
    public function parse(/*Token*/$token)
    {
        $lineno = $token->getLine();

        $expr = $this->parser->getExpressionParser()->parsePrimaryExpression();

        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);
        $compound = $this->parser->subskip(array($this, 'decideEnd'), true);
        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);

        return new SQLShade_Node_Embed($expr, $compound, $lineno, null);
    }

    public function getTag()
    {
        return 'embed';
    }

    public function getEndTag()
    {
        return 'endembed';
    }
}

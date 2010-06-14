<?php
require_once(dirname(__FILE__).'/../TokenParser.php');
require_once(dirname(__FILE__).'/../TokenParser/Embed.php');
require_once(dirname(__FILE__).'/../Token.php');
require_once(dirname(__FILE__).'/../Node/Eval.php');
require_once(dirname(__FILE__).'/../Node/Expression/Name.php');

class SQLShade_TokenParser_Eval extends SQLShade_TokenParser_Embed {

    public function parse(/*Token*/$token) {
        $lineno = $token->getLine();

        $expr = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);
        $compound = $this->parser->subparse(array($this, 'decideEnd'), true);
        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);

        return new SQLShade_Node_Eval($expr, $compound, $lineno, null);
    }

    public function getTag() {
        return 'eval';
    }

    public function getEndTag() {
        return 'endeval';
    }

}

<?php
require_once(dirname(__FILE__).'/../TokenParser.php');
require_once(dirname(__FILE__).'/../TokenParser/Embed.php');
require_once(dirname(__FILE__).'/../Token.php');
require_once(dirname(__FILE__).'/../Node/Eval.php');
require_once(dirname(__FILE__).'/../Node/Expression/Name.php');

class SQLShade_TokenParser_Eval extends SQLShade_TokenParser_Embed {

    public function parse(/*Token*/$token) {
        $lineno = $token->getLine();

        $token = $this->parser->getCurrentToken();
        $this->parser->getStream()->expect(SQLShade_Token::NAME_TYPE);
        $ident = new SQLShade_Node_Expression_Name($token->getValue(), $lineno);

        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);
        $compound = $this->parser->subparse(array($this, 'decideEvalEnd'), true);
        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);

        return new SQLShade_Node_Eval($ident, $compound, $lineno);
    }

    public function decideEvalEnd($token) {
        return $token->test('endeval');
    }

    public function getTag() {
        return 'eval';
    }

}

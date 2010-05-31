<?php
require_once(dirname(__FILE__).'/../TokenParser.php');
require_once(dirname(__FILE__).'/../Token.php');
require_once(dirname(__FILE__).'/../Node/Embed.php');
require_once(dirname(__FILE__).'/../Node/Expression/Name.php');

class SQLShade_TokenParser_Embed extends SQLShade_TokenParser_If {

    public function parse(/*Token*/$token) {
        $lineno = $token->getLine();

        $token = $this->parser->getCurrentToken();
        $this->parser->getStream()->expect(SQLShade_Token::NAME_TYPE);
        $ident = new SQLShade_Node_Expression_Name($token->getValue(), $lineno);

        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);
        $compound = $this->parser->subparse(array($this, 'decideEmbedEnd'), true);
        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);

        return new SQLShade_Node_Embed($ident, $compound, $lineno);
    }

    public function decideEmbedEnd($token) {
        return $token->test('endembed');
    }

    public function getTag() {
        return 'embed';
    }

}

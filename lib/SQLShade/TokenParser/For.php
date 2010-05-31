<?php
require_once(dirname(__FILE__).'/../TokenParser.php');
require_once(dirname(__FILE__).'/../Token.php');
require_once(dirname(__FILE__).'/../Node/For.php');
require_once(dirname(__FILE__).'/../Node/Expression/AssignName.php');
require_once(dirname(__FILE__).'/../Node/Expression/Name.php');

class SQLShade_TokenParser_For extends SQLShade_TokenParser {

    public function parse(SQLShade_Token $token) {
        $lineno = $token->getLine();

        $token = $this->parser->getCurrentToken();
        $this->parser->getStream()->expect(SQLShade_Token::NAME_TYPE);
        $assignname = new SQLShade_Node_Expression_AssignName($token->getValue(), $lineno);

        $this->parser->getStream()->expect(SQLShade_Token::NAME_TYPE, 'in');

        $token = $this->parser->getCurrentToken();
        $this->parser->getStream()->expect(SQLShade_Token::NAME_TYPE);
        $ident = new SQLShade_Node_Expression_Name($token->getValue(), $lineno);

        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);
        $compound = $this->parser->subparse(array($this, 'decideForEnd'), true);
        $this->parser->getStream()->expect(SQLShade_Token::BLOCK_END_TYPE);

        return new SQLShade_Node_For($assignname, $ident, $compound, $lineno);
    }

    public function decideForEnd($token) {
        return $token->test('endfor');
    }

    public function getTag() {
        return 'for';
    }

}

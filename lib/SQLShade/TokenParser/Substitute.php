<?php
require_once(dirname(__FILE__).'/../TokenParser.php');
require_once(dirname(__FILE__).'/../Token.php');
require_once(dirname(__FILE__).'/../Node/Substitute.php');
require_once(dirname(__FILE__).'/../Node/Literal.php');

class SQLShade_TokenParser_Substitute extends SQLShade_TokenParser {

    public function parse(SQLShade_Token $token) {
        $lineno = $token->getLine();

        $token = $this->parser->getCurrentToken();
        $this->parser->getStream()->expect(SQLShade_Token::NAME_TYPE);
        $ident = $token->getValue();

        $this->parser->getStream()->expect(SQLShade_Token::VAR_END_TYPE);

        $token = $this->parser->getCurrentToken();
        if ($token->getType() !== SQLShade_Token::TEXT_TYPE) {
            throw new SQLShade_SyntaxError('Substitute must need fake literal', $token->getLine());
        }

        return new SQLShade_Node_Substitute($ident, 'hoge', $lineno);
    }

}

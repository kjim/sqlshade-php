<?php
require_once(dirname(__FILE__).'/../TokenParser.php');
require_once(dirname(__FILE__).'/../Node/For.php');

class SQLShade_TokenParser_If extends Twig_TokenParser {

    public function parse(Twig_Token $token) {
        $lineno = $token->getLine();

        $token = $this->parser->getCurrentToken();
        $token->expect(Twig_Token::NAME_TYPE);
        $ident = new SQLShade_Node_Expression_Name($token->getValue(), $lineno);

        $this->parser->getStream()->next();
        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);
        $compound = $this->parser->subparse(array($this, 'decideIfEnd'));
        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

        return new SQLShade_Node_If($ident, $lineno);
    }

    public function decideIfEnd() {
        return $token->test('endif');
    }

    public function getTag() {
        return 'if';
    }

}

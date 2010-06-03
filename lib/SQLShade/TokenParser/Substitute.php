<?php
require_once(dirname(__FILE__).'/../TokenParser.php');
require_once(dirname(__FILE__).'/../Token.php');
require_once(dirname(__FILE__).'/../Node/Substitute.php');
require_once(dirname(__FILE__).'/../Node/Literal.php');

class SQLShade_TokenParser_Substitute extends SQLShade_TokenParser {

    static protected $SOULD_BE_END_CHAR_RULES = array(
        "(" => ")",
        "'" => "'",
        );

    public function parse(SQLShade_Token $token) {
        $lineno = $token->getLine();

        $token = $this->parser->getCurrentToken();
        $this->parser->getStream()->expect(SQLShade_Token::NAME_TYPE);
        $ident = new SQLShade_Node_Expression_Name($token->getValue(), $token->getLine());

        $this->parser->getStream()->expect(SQLShade_Token::VAR_END_TYPE);

        $token = $this->parser->getCurrentToken();
        if ($token->getType() !== SQLShade_Token::TEXT_TYPE) {
            throw new SQLShade_SyntaxError('Substitute must need fake literal', $token->getLine());
        }

        $tokenvalue = $token->getValue();
        $faketext = $this->_parseFaketext($tokenvalue, $token->getLine());
        $token->setValue(str_replace($faketext, '', $tokenvalue));
        return new SQLShade_Node_Substitute($ident, $faketext, $lineno);
    }

    public function _parseFaketext($text, $lineno) {
        if (is_null($text) || strlen($text) <= 0) {
            return ''; // return empty string
        }

        $charRules = SQLShade_TokenParser_Substitute::$SOULD_BE_END_CHAR_RULES;
        $firstChar = $text[0];
        $shouldBeEndChar = isset($charRules[$firstChar]) ? $charRules[$firstChar] : null;
        $end = self::_parseUntilEndOfFaketext($text, $shouldBeEndChar);
        if ($end != -1) {
            return mb_substr($text, 0, $end);
        }
        else {
            throw new SQLShade_SyntaxError('Invalid faketext literal', $lineno);
        }
    }

    static public function _parseUntilEndOfFaketext($text, $shouldBeEndChar = null) {
        if (!$text) {
            return -1;
        }

        list($stack, $string, $escape) = array(0, false, false);
        if (!is_null($shouldBeEndChar)) {
            $end = array($shouldBeEndChar);
            $offset = 1;
        }
        else {
            $end = array(" ", "\n", "\r");
            $offset = 0;
        }

        $len = strlen($text);
        $lastindex = 0;
        for ($i = 0; $i < $len; $i++) {
            $c = $text[$i];
            $lastindex = $i;
            if ($string === false) {
                if ($c === "(") {
                    $stack += 1;
                }
                elseif ($c === ")") {
                    $stack -= 1;
                }
            }
            if ($escape === false) {
                if ($c === "'") {
                    $string = !$string;
                }
                elseif ($c === "\\") {
                    $escape = true;
                }
            }
            else {
                $escape = false;
            }

            if ($stack === 0 && $string === false && in_array($c, $end) && $i > 0) {
                return $i + $offset;
            }
        }

        if ($stack === 0 && $string === false && !in_array($c, $end)) {
            return $lastindex + 1;
        }
        else {
            return -1;
        }
    }

    public function getTag() {
        return "substitute";
    }

}

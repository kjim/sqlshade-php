<?php
require_once(dirname(__FILE__).'/SyntaxError.php');
require_once(dirname(__FILE__).'/Token.php');
require_once(dirname(__FILE__).'/Node/Expression/Constant.php');
require_once(dirname(__FILE__).'/Node/Expression/Name.php');
require_once(dirname(__FILE__).'/Node/Expression/AttrName.php');
require_once(dirname(__FILE__).'/Node/Expression/AssignName.php');
require_once(dirname(__FILE__).'/Node/Expression/Unary/Not.php');

class SQLShade_ExpressionParser {

    protected $parser;

    public function __construct($parser) {
        $this->parser = $parser;
    }

    public function parseExpression() {
        return $this->parseConditionalExpression();
    }

    public function parseConditionalExpression() {
        $lineno = $this->parser->getCurrentToken()->getLine();
        $expr = $this->parseUnaryExpression();
        return $expr;
    }

    public function parseUnaryExpression() {
        if ($this->parser->getStream()->test('not')) {
            return $this->parseNotExpression();
        }

        return $this->parsePrimaryExpression();
    }

    public function parseNotExpression() {
        $token = $this->parser->getStream()->next();
        $node = $this->parseUnaryExpression();

        return new SQLShade_Node_Expression_Unary_Not($node, $token->getLine(), $token);
    }

    public function parsePrimaryExpression($assignment = false) {
        $token = $this->parser->getCurrentToken();
        switch ($token->getType()) {
            case SQLShade_Token::NAME_TYPE:
                $this->parser->getStream()->next();
                $value = $token->getValue();
                if ($value === 'true') {
                    $node = new SQLShade_Node_Expression_Constant(true, $token->getLine(), $token);
                }
                elseif ($value === 'false') {
                    $node = new SQLShade_Node_Expression_Constant(false, $token->getLine(), $token);
                }
                else {
                    $cls = $assignment ? 'SQLShade_Node_Expression_AssignName' : 'SQLShade_Node_Expression_Name';
                    $node = new $cls($token->getValue(), $token->getLine(), $token);
                }
                break;

            case SQLShade_Token::STRING_TYPE:
                $this->parser->getStream()->next();
                $node = new SQLShade_Node_Expression_Constant($token->getValue(), $token->getLine(), $token);
                break;

            default:
                throw new SQLShade_SyntaxError(sprintf('Unexpected token "%s" of value "%s"', SQLShade_Token::getTypeAsString($token->getType()), $token->getValue()), $token->getLine());
        }
        if (!$assignment) {
            $node = $this->parsePostfixExpression($node);
        }

        return $node;
    }

    public function parsePostfixExpression($node) {
        static $SUBSCRIPT_OPS = array('.', '[');

        $stop = false;
        while (!$stop && $this->parser->getCurrentToken()->getType() === SQLShade_Token::OPERATOR_TYPE) {
            switch ($this->parser->getCurrentToken()->getValue()) {
                case '.':
                case '[':
                    $node = $this->parseSubscriptExpression($node);
                    break;

                default:
                    $stop = true;
                    break;
            }
        }

        return $node;
    }

    public function parseSubscriptExpression($node) {
        $token = $this->parser->getStream()->next();
        $lineno = $token->getLine();
        if ($token->getValue() == '.') {
            $token = $this->parser->getStream()->next();
            if ($token->getType() == SQLShade_Token::NAME_TYPE) {
                $attr = new SQLShade_Node_Expression_Constant($token->getValue(), $lineno, $token);
            } else {
                throw new SQLShade_SyntaxError('Expected name or number', $lineno);
            }
        } else {
            $attr = $this->parseExpression();
            $this->parser->getStream()->expect(SQLShade_Token::OPERATOR_TYPE, ']');
        }

        return new SQLShade_Node_Expression_AttrName($node, $attr, $lineno, $token);
    }

}

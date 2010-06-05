<?php
require_once(dirname(__FILE__).'/../Printer/Index.php');
require_once(dirname(__FILE__).'/../RenderContext.php');
require_once(dirname(__FILE__).'/../RenderError.php');

class SQLShade_Renderer_Index {

    protected $env;
    protected $strict;

    public function __construct($env, $strict = true) {
        $this->env = $env;
        $this->strict = $strict;
    }

    public function render(/*Node_Module*/$node, $data = array()) {
        $printer = new SQLShade_Printer_Index();
        $context = new SQLShade_RenderContext($data);
        return $this->_render($node, $context, $printer);
    }

    protected function _render($node, $context, $printer = null) {
        if ($printer === null) {
            $printer = new SQLShade_Printer_Index();
        }

        $ctx = array('printer' => $printer, 'context' => $context);
        $this->traverse($node->getBody(), $ctx);
        return $printer->freeze();
    }

    protected function traverse($node, &$ctx) {
        foreach ($node->getChildren() as $n) {
            $n->acceptVisitor($this, $ctx);
        }
    }

    protected function serializeNode($node) {
        list($serialized, $pad) = array('', '');
        foreach ($this->env->getParser()->subdeparse($node) as $token) {
            switch ($token->getType()) {
                case SQLShade_Token::BLOCK_START_TYPE:
                    $serialized .= '/*#';
                    $pad = '';
                    break;

                case SQLShade_Token::VAR_START_TYPE:
                    $serialized .= '/*:';
                    $pad = '';
                    break;

                case SQLShade_Token::BLOCK_END_TYPE:
                case SQLShade_Token::VAR_END_TYPE:
                    $serialized .= '*/';
                    $pad = '';
                    break;

                case SQLShade_Token::EOF_TYPE:
                    return $serialized;

                default:
                    $serialized .= $pad . $token->getValue();
                    $pad = ' ';
                    break;
            }
        }

        return $serialized;
    }

    protected function toAttributeAccessKey($node) {
        $nodetype = get_class($node);
        if ($nodetype === 'SQLShade_Node_Expression_Name') {
            return $node->getName();
        }
        elseif ($nodetype === 'SQLShade_Node_Expression_Constant') {
            return $node->getValue();
        }
        elseif ($nodetype === 'SQLShade_Node_Expression_AttrName') {
            $left = $this->toAttributeAccessKey($node->getNode());
            $right = $this->toAttributeAccessKey($node->getAttr());
            return $left . '.' . $right;
        }

        throw new LogicException("Unexpected node type: " . $nodetype);
    }

    protected function getAttribute($node, $context) {
        switch (get_class($node)) {
            case 'SQLShade_Node_Expression_Constant':
                $attribute = $node->getValue();
                break;

            default:
                $attribute = $context->data[$this->toAttributeAccessKey($node)];
                break;
        }
        
        return $attribute;
    }

    public function visitLiteral($node, &$ctx) {
        $printer = $ctx['printer'];
        $printer->write($node->getLiteral());
    }

    public function visitSubstitute($node, &$ctx) {
        $context = $ctx['context'];
        $expr = $node->getExpr();
        try {
            $variable = $this->getAttribute($expr, $context);
        } catch (SQLShade_KeyError $e) {
            if ($this->strict) {
                throw new SQLShade_RenderError('Has no parameters: ' . $expr);
            }
            else {
                $ctx['printer']->write($this->serializeNode($node));
            }
            return;
        }
        $this->writeSubstitute($node, $ctx, $variable);
    }

    protected function writeSubstitute($node, &$ctx, &$variable) {
        $printer = $ctx['printer'];
        if (is_array($variable)) {
            if (count($variable) === 0) {
                throw new SQLShade_RenderError('Binding data should not be empty');
            }
            $printer->write('(' . implode(', ', array_fill(0, count($variable), '?')) . ')');
            foreach ($variable as &$v) {
                $printer->bind($v);
            }
        }
        else {
            $printer->write('?');
            $printer->bind($variable);
        }
    }

    public function visitEmbed($node, &$ctx) {
        $context = $ctx['context'];
        $expr = $node->getExpr();
        try {
            $variable = $this->getAttribute($expr, $context);
        } catch (SQLShade_KeyError $e) {
            if ($this->strict) {
                throw new SQLShade_RenderError('Has no parameters: ' . $expr);
            }
            else {
                $ctx['printer']->write($this->serializeNode($node));
            }
            return;
        }
        $this->writeEmbed($node, $ctx, $variable);
    }

    protected function writeEmbed($node, &$ctx, &$variable) {
        $ctx['printer']->write($variable);
    }

    public function visitEval($node, &$ctx) {
        $context = $ctx['context'];
        $expr = $node->getExpr();
        try {
            $source = $this->getAttribute($expr, $context);
        } catch (SQLShade_KeyError $e) {
            if ($this->strict) {
                throw new SQLShade_RenderError('Has no parameters: ' . $expr);
            }
            else {
                $ctx['printer']->write($this->serializeNode($node));
            }
            return;
        }
        $this->writeEval($node, $ctx, $source);
    }

    protected function writeEval($node, &$ctx, &$source) {
        $subNode = $this->env->compileSource($source, '<inner_source>');
        list($innerQuery, $innerBounds) = $this->_render($subNode, clone $ctx['context']);

        $printer = $ctx['printer'];
        $printer->write($innerQuery);
        foreach ($innerBounds as $v) {
            $printer->bind($v);
        }
    }

    public function visitIf($node, &$ctx) {
        $context = $ctx['context'];
        $expr = $node->getExpr();
        try {
            $variable = $this->getAttribute($expr, $context);
        } catch (SQLShade_KeyError $e) {
            if ($this->strict) {
                throw new SQLShade_RenderError('Has no parameters: ' . $expr);
            }
            else {
                $ctx['printer']->write($this->serializeNode($node));
            }
            return;
        }
        $this->writeIf($node, $ctx, $variable);
    }

    protected function writeIf($node, &$ctx, &$variable) {
        if ($variable) {
            $this->traverse($node, $ctx);
        }
    }

    public function visitFor($node, &$ctx) {
        $context = $ctx['context'];
        $ident = $node->getIdent();
        try {
            $sequence = $this->getAttribute($ident, $context);
        } catch (SQLShade_KeyError $e) {
            if ($this->strict) {
                throw new SQLShade_RenderError('Has no parameters: ' . $ident);
            }
            else {
                $ctx['printer']->write($this->serializeNode($node));
            }
            return;
        }
        $alias = $node->getItem()->getName();
        $this->writeFor($node, $ctx, $alias, $sequence);
    }

    protected function writeFor($node, &$ctx, &$alias, &$sequence) {
        $forBlockContext = clone $ctx['context'];
        $forBlockCtx = $ctx;
        $forBlockCtx['context'] = $forBlockContext;
        foreach ($sequence as $idata) {
            $forBlockContext->data->update(array((string) $alias => $idata));
            $this->traverse($node, $forBlockCtx);
        }
    }

}

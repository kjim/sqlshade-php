<?php
require_once(dirname(__FILE__).'/../Printer/Index.php');
require_once(dirname(__FILE__).'/../RenderContext.php');
require_once(dirname(__FILE__).'/../RenderError.php');

class SQLShade_Renderer_Index {

    protected $env;
    protected $strict;

    public function __construct($env) {
        $this->env = $env;
        $this->strict = true;
    }

    public function render(/*Node_Module*/$node, $data = array()) {
        $printer = new SQLShade_Printer_Index();
        $context = array(
            'printer' => $printer,
            'context' => (is_array($data) ? new SQLShade_RenderContext($data) : $data),
            );
        $this->traverse($node->getBody(), $context);
        return $printer->freeze();
    }

    protected function traverse($node, &$ctx) {
        foreach ($node->getChildren() as $n) {
            $n->acceptVisitor($this, $ctx);
        }
    }

    public function visitLiteral($node, &$ctx) {
        $printer = $ctx['printer'];
        $printer->write($node->getLiteral());
    }

    public function visitSubstitute($node, &$ctx) {
        $context = $ctx['context'];
        $ident = $node->getIdent()->getName();
        $text = $node->getFaketext();
        try {
            $variable = $context->data[$ident];
        } catch (SQLShade_KeyError $e) {
            if ($this->strict) {
                throw new SQLShade_RenderError('Has no parameters: ' . $ident);
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
        $ident = $node->getIdent()->getName();
        try {
            $variable = $context->data[$ident];
        } catch (SQLShade_KeyError $e) {
            if ($this->strict) {
                throw new SQLShade_RenderError('Has no parameters: ' . $ident);
            }
        }
        $this->writeEmbed($node, $ctx, $variable);
    }

    protected function writeEmbed($node, &$ctx, &$variable) {
        $ctx['printer']->write($variable);
    }

    public function visitEval($node, &$ctx) {
        $context = $ctx['context'];
        $ident = $node->getIdent()->getName();
        try {
            $source = $context->data[$ident];
        } catch (SQLShade_KeyError $e) {
            if ($this->strict) {
                throw new SQLShade_RenderError('Has no parameters: ' . $ident);
            }
            return;
        }
        $this->writeEval($node, $ctx, $source);
    }

    protected function writeEval($node, &$ctx, &$source) {
        $subNode = $this->env->compileSource($source, '<inner_source>');
        list($innerQuery, $innerBounds) = $this->render($subNode, clone $ctx['context']);

        $printer = $ctx['printer'];
        $printer->write($innerQuery);
        foreach ($innerBounds as $v) {
            $printer->bind($v);
        }
    }

    public function visitIf($node, &$ctx) {
        $context = $ctx['context'];
        $ident = $node->getIdent()->getName();
        try {
            $variable = $context->data[$ident];
        } catch (SQLShade_KeyError $e) {
            if ($this->strict) {
                throw new SQLShade_RenderError('Has no parameters: ' . $ident);
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
        $ident = $node->getIdent()->getName();
        try {
            $sequence = $context->data[$ident];
        } catch (SQLShade_KeyError $e) {
            if ($this->strict) {
                throw new SQLShade_RenderError('Has no parameters: ' . $ident);
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

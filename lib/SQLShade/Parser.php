<?php

class SQLShade_Parser implements Twig_ParserInterface {

    protected
        $stream,
        $handlers,
        $visitors,
        $blocks,
        $blockStack,
        $env;

    public function __construct(Twig_Environment $env = null) {
        if (null != $env) {
            $this->setEnvironment($env);
        }
    }

    public function setEnvironment(Twig_Environment $env) {
        $this->env = $env;
    }

    public function parse(Twig_TokenStream $stream) {
        $this->handlers = array();
        $this->visitors = array();

        // tag handlers
        foreach ($this->env->getTokenParsers() as $handler) {
            $handler->setParser($this);
            $this->handlers[$handler->getTag()] = $handler;
        }

        // node visitors
        $this->visitors = $this->env->getNodeVisitors();

        $this->stream = $stream;
        $this->blocks = array();
        $this->blockStack = array();

        try {
            $body = $this->subparse(null);
        } catch (Twig_SyntaxError $e) {
            if (is_null($e->getFilename())) {
                $e->setFilename($this->stream->getFilename());
            }

            throw $e;
        }

        $node = new SQLShade_Node_Module($body, $this->blocks, $this->stream->getFilename());
        $t = new Twig_NodeTraverser($this->env);
        foreach ($this->visitors as $visitor) {
            $node = $t->traverse($node, $visitor);
        }

        return $node;
    }

    public function subparse($test, $drop_needle = false) {
        $lineno = $this->getCurrentToken()->getLine();
        $rv = array();
        while (!$this->stream->isEOF()) {
            $tokentype = $this->getCurrentToken()->getType();

            // literal
            if ($tokentype === Twig_Token::TEXT_TYPE) {
                $token = $this->stream->next();
                $rv[] = new SQLShade_Node_Text($token->getValue(), $token->getLine());
            }

            // placeholder
            else if ($tokentype === Twig_Token::VAR_START_TYPE) {
                $token = $this->stream->next();
                $pname = $token->getValue();
                $this->stream->expect(Twig_Token::VAR_END_TYPE);
                $rv[] = new SQLShade_Node_PlaceHolder($pname, $token->getLine());
            }

            // block
            else if ($tokentype === Twig_Token::BLOCK_START_TYPE) {
                $this->stream->next(); // skip
                $token = $this->getCurrentToken();

                if ($token->getType() !== Twig_Token::NAME_TYPE) {
                    throw new Twig_SyntaxError('A block must start with a tag name', $token->getLine());
                }

                if (!is_null($test) && call_user_func($test, $token)) {
                    return new Twig_NodeList($rv, $lineno);
                }

                if (!isset($this->handlers[$token->getValue()])) {
                    throw new Twig_SyntaxError(sprintf('Unknown tag name "%s"', $token->getValue()), $token->getLine());
                }
            }
            else {
                throw new LogicException('Lexer or parser ended up in unsupported state.');
            }
        }

        return new Twig_NodeList($rv, $lineno);
    }

    public function addHandler($name, $class) {
        $this->handlers[$name] = $class;
    }

    public function addNodeVisitor(Twig_NodeVisitorInterface $visitor) {
        $this->visitors[] = $visitor;
    }

    public function getBlockStack() {
        return $this->blockStack;
    }

    public function peekBlockStack() {
        return $this->blockStack[count($this->blockStack) - 1];
    }

    public function popBlockStack() {
        array_pop($this->blockStack);
    }

    public function pushBlockStack($name) {
        $this->blockStack[] = $name;
    }

    public function hasBlock($name) {
        return isset($this->blocks[$name]);
    }

    public function setBlock($name, $value) {
        $this->blocks[$name] = $value;
    }

    public function getStream() {
        return $this->stream;
    }

    public function getCurrentToken() {
        return $this->stream->getCurrent();
    }

}

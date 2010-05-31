<?php

class SQLShade_Parser {

    protected $stream;
    protected $handlers;
    protected $env;

    public function __construct($env = null) {
        if (null != $env) {
            $this->setEnvironment($env);
        }
    }

    public function setEnvironment($env) {
        $this->env = $env;
    }

    public function parse($stream) {
        $this->handlers = array();

        // tag handlers
        foreach ($this->env->getTokenParsers() as $handler) {
            $handler->setParser($this);
            $this->handlers[$handler->getTag()] = $handler;
        }

        $this->stream = $stream;

        try {
            $body = $this->subparse(null);
        } catch (SQLShade_SyntaxError $e) {
            if (is_null($e->getFilename())) {
                $e->setFilename($this->stream->getFilename());
            }

            throw $e;
        }

        return new SQLShade_Node_Module($body, $this->stream->getFilename());
    }

    public function subparse($test, $drop_needle = false) {
        $lineno = $this->getCurrentToken()->getLine();
        $rv = array();
        while (!$this->stream->isEOF()) {
            $tokentype = $this->getCurrentToken()->getType();

            // literal
            if ($tokentype === Twig_Token::TEXT_TYPE) {
                $token = $this->stream->next();
                $rv[] = new SQLShade_Node_Literal($token->getValue(), $token->getLine());
            }

            // substitute
            else if ($tokentype === Twig_Token::VAR_START_TYPE) {
                $token = $this->stream->next();
                $pname = $token->getValue();
                $this->stream->expect(Twig_Token::VAR_END_TYPE);
                $rv[] = new SQLShade_Node_Substitute($pname, $token->getLine());
            }

            // block
            else if ($tokentype === Twig_Token::BLOCK_START_TYPE) {
                $this->stream->next(); // skip
                $token = $this->getCurrentToken();

                if ($token->getType() !== Twig_Token::NAME_TYPE) {
                    throw new SQLShade_SyntaxError('A block must start with a tag name', $token->getLine());
                }

                if (!is_null($test) && call_user_func($test, $token)) {
                    return new SQLShade_Node_Compound($rv, $lineno);
                }

                if (!isset($this->handlers[$token->getValue()])) {
                    throw new SQLShade_SyntaxError(sprintf('Unknown tag name "%s"', $token->getValue()), $token->getLine());
                }

                $this->stream->next();

                $subparser = $this->handlers[$token->getValue()];
                $node = $subparser->parse($token);
                if (!is_null($node)) {
                    $rv[] = $node;
                }
            }
            else {
                throw new LogicException('Lexer or parser ended up in unsupported state.');
            }
        }

        return new SQLShade_Node_Compound($rv, $lineno);
    }

    public function addHandler($name, $class) {
        $this->handlers[$name] = $class;
    }

    public function getStream() {
        return $this->stream;
    }

    public function getCurrentToken() {
        return $this->stream->getCurrent();
    }

}

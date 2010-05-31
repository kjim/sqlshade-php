<?php

class SQLShade_Environment {

    protected $charset;
    protected $loader;
    protected $lexer;
    protected $parser;
    protected $parsers;
    protected $extensions;

    public function __construct($options = array()) {
        $this->setLexer(new SQLShade_Lexer());
        $this->setParser(new SQLShade_Parser());

        $this->charset = isset($options['charset']) ? $options['charset'] : 'UTF-8';
        $this->extensions = array('core' => new SQLShade_Extension_Core());
    }

    public function getLexer() {
        return $this->lexer;
    }

    public function setLexer($lexer) {
        $this->lexer = $lexer;
        $lexer->setEnvironment($this);
    }

    public function tokenize($source, $name) {
        return $this->getLexer()->tokenize($source, $name);
    }

    public function getParser() {
        return $this->parser;
    }

    public function setParser($parser) {
        $this->parser = $parser;
        $parser->setEnvironment($this);
    }

    public function parse($tokens) {
        return $this->getParser()->parse($tokens);
    }

    public function getExtensions() {
        return $this->extensions;
    }

    public function getTokenParsers() {
        if ($this->parsers === null) {
            $this->parsers = array();
            foreach ($this->getExtensions() as $ext) {
                $this->parsers = array_merge($this->parsers, $ext->getTokenParsers());
            }
        }

        return $this->parsers;
    }

}

<?php
require_once(dirname(__FILE__).'/Lexer.php');
require_once(dirname(__FILE__).'/Parser.php');
require_once(dirname(__FILE__).'/Extension/Core.php');

class SQLShade_Environment {

    protected $charset;
    protected $loader;
    protected $lexer;
    protected $parser;
    protected $parsers;
    protected $extensions;

    public function __construct($options = array()) {
        $lexoptions = array(
            'tag_comment'  => array('/*-', '*/'),
            'tag_block'    => array('/*#', '*/'),
            'tag_variable' => array('/*:', '*/'),
            );

        $this->setLexer(new SQLShade_Lexer($this, $lexoptions));
        $this->setParser(new SQLShade_Parser($this));

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

    public function getRenderer() {
        return $this->renderer;
    }

    public function setRenderer($renderer) {
        $this->renderer = $renderer;
        $renderer->setEnvironment($this);
    }

    public function render($node) {
        return $this->getRenderer()->render($node);
    }

    public function compileSource($source, $name) {
        return $this->parse($this->tokenize($source, $name));
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

    public function getTemplateClass($filename) {
        return '__SQLShadeTemplate_' . md5($filename);
    }

}

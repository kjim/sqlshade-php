<?php
require_once(dirname(__FILE__).'/Environment.php');

class SQLShade_Template
{
    static protected $defaultEnvironment;

    protected $name;
    protected $node;
    protected $renderer;

    public function __construct($source, $options = array())
    {
        $options = array_merge(
            array('strict' => true,
                  'parameter_format' => 'list',
                  'env' => self::getDefaultEnvironment(),
                  'name' => 'n/a',
                ), $options);

        $env = $options['env'];
        $cls = $env->getRendererClass($options['parameter_format']);
        $this->renderer = new $cls($env, $options['strict']);

        $this->name = $options['name'];
        $this->node = $env->compileSource($source, $this->name);
    }

    public function render($context = array())
    {
        return $this->renderer->render($this->node, $context);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNode()
    {
        return $this->node;
    }

    static protected function getDefaultEnvironment()
    {
        if (self::$defaultEnvironment === null) {
            self::$defaultEnvironment = new SQLShade_Environment();
        }

        return self::$defaultEnvironment;
    }
}

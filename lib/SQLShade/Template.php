<?php

class SQLShade_Template {

    protected $name;

    public function __construct($name) {
        $this->name = $name;
    }

    public function render($context) {
        // FIXME: render implementation
    }

    protected function getName() {
        return $name;
    }

}

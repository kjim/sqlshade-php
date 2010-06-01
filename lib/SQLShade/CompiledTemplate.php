<?php

abstract class SQLShade_CompiledTemplate {

    abstract public function render($context);

    abstract protected function getName();

}

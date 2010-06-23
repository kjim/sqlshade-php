<?php

abstract class SQLShade_TokenParser
{
    protected $parser;

    public function setParser($parser)
    {
        $this->parser = $parser;
    }
}

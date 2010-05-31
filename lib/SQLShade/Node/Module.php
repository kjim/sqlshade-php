<?php

class SQLShade_Node_Module extends SQLShade_Node {

    protected $body;
    protected $filename;

    public function __construct(/*Node_Compound*/$body, $filename) {
        parent::__construct(1);

        $this->body = $body;
        $this->filename = $filename;
    }

    public function getBody() {
        return $this->body;
    }

}

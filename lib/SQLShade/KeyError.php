<?php
require_once(dirname(__FILE__).'/Error.php');

class SQLShade_KeyError extends SQLShade_Error
{
    public function __construct($key)
    {
        parent::__construct($key);
    }
}

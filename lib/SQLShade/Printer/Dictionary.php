<?php
require_once(dirname(__FILE__).'/Array.php');

class SQLShade_Printer_Dictionary extends SQLShade_Printer_Array
{
    public function bind($name, $variable)
    {
        $this->bound[$name] = $variable;
    }
}

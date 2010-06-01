<?php

class SQLShade_Printer_PHP {

    protected $source;

    public function __construct() {
        $this->source = '';
    }

    public function getSource() {
        return $this->source;
    }

    public function raw($string) {
        $this->source .= $string;

        return $this;
    }

    public function write() {
        $strings = func_get_args();
        foreach ($strings as $string) {
            $this->source .= $string;
        }

        return $this;
    }

    public function string($value) {
        $this->source .= sprintf('"%s"', addcslashes($value, "\t\"\$\\"));

        return $this;
    }

    public function repr($value) {
        if (is_int($value) || is_float($value)) {
            $this->raw($value);
        }
        elseif (is_null($value)) {
            $this->raw('null');
        }
        elseif (is_bool($value)) {
            $this->raw($value ? 'true' : 'false');
        }
        elseif (is_array($value)) {
            $this->raw('array(');
            foreach ($value as $key => $value) {
                $this->repr($key)->raw(' => ')->repr($value)->raw(', ');
            }
            $this->raw(')');
        }
        else {
            $this->string($value);
        }

        return $this;
    }

}

<?php
require_once(dirname(__FILE__).'/KeyError.php');

class SQLShade_RenderContext
{
    public $data;
    public $env;

    public function __construct($data, $env = array())
    {
        $this->data = new SQLShade_RenderContext_InnerArray($data);
        $this->env = $env;
    }

    public function __clone()
    {
        $this->data = clone($this->data);
        $this->env = $this->env;
    }

}

class SQLShade_RenderContext_InnerArray implements ArrayAccess
{
    protected $source;

    public function __construct($source)
    {
        $this->source = $source;
    }

    public function update($data)
    {
        foreach ($data as $k => $v) {
            $this->source[$k] = $v;
        }
    }

    protected function & get($ident)
    {
        $identStruct = explode('.', $ident);
        $tmp =& $this->source;
        foreach ($identStruct as $e) {
            if (!isset($tmp[$e])) {
                throw new SQLShade_KeyError($ident);
            }
            $tmp =& $tmp[$e];
        }
        return $tmp;
    }

    public function offsetExists($offset)
    {
        try {
            $this->get($offset);
            return true;
        } catch (SQLShade_KeyError $e) {
            return false;
        }
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new Exception("not supported");
    }

    public function offsetUnset($offset)
    {
        throw new Exception("not supported");
    }

    public function __clone()
    {
        $this->source = $this->source;
    }
}

<?php

class SQLShade_SyntaxError extends SQLShade_Error {

    protected
        $message,
        $lineno,
        $filename
        ;

    public function __construct($message, $lineno, $filename = null) {
        $this->message = $message;
        $this->lineno = $lineno;
        $this->filename = $filename;

        parent::__construct($message.self::formatLineno($lineno, $filename), $lineno);
    }

    static protected function formatLineno($lineno, $filename) {
        if (is_null($filename)) {
            return " at line: $lineno";
        }
        else {
            return " in file '$filename' at line: $lineno";
        }
    }

}

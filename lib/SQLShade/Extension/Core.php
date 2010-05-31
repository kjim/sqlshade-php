<?php
require_once(dirname(__FILE__).'/../Extension.php');

class SQLShade_Extension_Core extends SQLShade_Extension {

    public function getTokenParsers() {
        return array(
            new SQLShade_TokenParser_For(),
            new SQLShade_TokenParser_If(),
            );
    }

}

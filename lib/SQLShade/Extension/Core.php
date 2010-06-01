<?php
require_once(dirname(__FILE__).'/../Extension.php');
require_once(dirname(__FILE__).'/../TokenParser/Substitute.php');
require_once(dirname(__FILE__).'/../TokenParser/For.php');
require_once(dirname(__FILE__).'/../TokenParser/If.php');
require_once(dirname(__FILE__).'/../TokenParser/Embed.php');
require_once(dirname(__FILE__).'/../TokenParser/Eval.php');

class SQLShade_Extension_Core extends SQLShade_Extension {

    public function getTokenParsers() {
        return array(
            new SQLShade_TokenParser_Substitute(),
            new SQLShade_TokenParser_For(),
            new SQLShade_TokenParser_If(),
            new SQLShade_TokenParser_Embed(),
            new SQLShade_TokenParser_Eval(),
            );
    }

}

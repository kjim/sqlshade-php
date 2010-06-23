<?php
require_once(dirname(__FILE__).'/../TokenParser/Substitute.php');
require_once(dirname(__FILE__).'/../TokenParser/For.php');
require_once(dirname(__FILE__).'/../TokenParser/If.php');
require_once(dirname(__FILE__).'/../TokenParser/Embed.php');

require_once(dirname(__FILE__).'/../Renderer/Index.php');

class SQLShade_Extension_Core
{
    public function getTokenParsers()
    {
        return array(
            new SQLShade_TokenParser_Substitute(),
            new SQLShade_TokenParser_For(),
            new SQLShade_TokenParser_If(),
            new SQLShade_TokenParser_Embed(),
            );
    }

    public function getRendererClasses()
    {
        return array(
            'list' => 'SQLShade_Renderer_Index',
            );
    }
}

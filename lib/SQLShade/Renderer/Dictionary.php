<?php
require_once(dirname(__FILE__).'/../Printer/Dictionary.php');
require_once(dirname(__FILE__).'/../RenderContext.php');
require_once(dirname(__FILE__).'/../RenderError.php');
require_once(dirname(__FILE__).'/Array.php');

class SQLShade_Renderer_Dictionary extends SQLShade_Renderer_Array
{
    public function render(/*Node_Module*/$node, $data = array())
    {
        $printer = new SQLShade_Printer_Dictionary();
        $context = new SQLShade_RenderContext($data);
        return $this->_render($node, $context, $printer);
    }

    protected function _render($node, $context, $printer = null)
    {
        if ($printer === null) {
            $printer = new SQLShade_Printer_Dictionary();
        }

        $ctx = array('printer' => $printer, 'context' => $context);
        $this->traverse($node->getBody(), $ctx);
        return $printer->freeze();
    }

    protected function writeSubstitute($node, &$ctx, &$variable)
    {
        list($printer, $context) = array($ctx['printer'], $ctx['context']);
        if (is_array($variable) && count($variable) === 0) {
            throw new SQLShade_RenderError('Binding data should not be empty');
        }
        $attrName = $this->toAttributeAccessKey($node->getExpr());
        if (isset($context->env['for'])) {
            $forEnv = $context->env['for'];
            $alias = $forEnv['alias'];
            if ($attrName === $alias || self::_startsWith($attrName . '.', $alias)) {
                $pname = $attrName . '_' . (string) $forEnv['count'];
            }
            else {
                $pname = $attrName;
            }
        }
        else {
            $pname = $attrName;
        }
        if (is_array($variable)) {
            $idents = array();
            $i = 0;
            foreach ($variable as $v) {
                $identCurr = $pname . '_' . (string) ($i+1);
                $idents[] = ':' . $identCurr;
                $printer->bind($identCurr, $v);
                $i += 1;
            }
            $printer->write('(' . implode(', ', $idents) . ')');
        }
        else {
            $printer->write(':' . $pname);
            $printer->bind($pname, $variable);
        }
    }

    protected function writeFor($node, &$ctx, &$alias, &$sequence)
    {
        $forEnv = array('alias' => $alias);
        $forBlockContext = clone($ctx['context']);
        $forBlockCtx = $ctx;
        $forBlockContext->env['for'] =& $forEnv;
        $forBlockCtx['context'] = $forBlockContext;

        $i = 0;
        foreach ($sequence as $idata) {
            $forEnv['count'] = $i + 1;
            $forBlockContext->data->update(array((string) $alias => $idata));
            $this->traverse($node, $forBlockCtx);
            $i += 1;
        }
    }

    static protected function _startsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }
}

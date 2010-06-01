<?php
require_once(dirname(__FILE__).'/../Printer/PHP.php');

class SQLShade_Compiler_Index {

    protected $env;
    protected $strict;

    public function __construct($env) {
        $this->env = $env;
        $this->strict = true;
    }

    public function compile(/*Node_Module*/$node) {
        $printer = new SQLShade_Printer_PHP();
        $this->printTemplate($node, $printer);

        return $printer->getSource();
    }

    protected function printTemplate($moduleNode, $printer) {
        $printer
            ->raw("<?php\n")
            ->raw("class " . $this->env->getTemplateClass($moduleNode->getFilename()))
            ->raw(" extends SQLShade_CompiledTemplate {\n")
            ->raw("public function render(\$context) {\n")
            ->raw("\$buf = '';\n")
            ;

        $this->traverse($moduleNode->getBody(), $printer);

        $printer
            ->raw("return ")->raw("\$buf;\n")
            ->raw("}\n")
            ->raw("public function getName() {\n")
            ->raw("return ")->string($moduleNode->getFilename())->raw(";\n")
            ->raw("}\n")
            ->raw("}\n")
            ;
    }

    protected function traverse($node, $printer) {
        foreach ($node->getChildren() as $n) {
            $n->acceptVisitor($this, $printer);
        }
    }

    public function visitLiteral($node, $printer) {
        $printer
            ->raw("\$buf .= ")
            ->string($node->getLiteral())
            ->raw(";\n")
            ;
    }

    public function visitSubstitute($node, $printer) {
    }

    public function visitEmbed($node, $printer) {
    }

    public function visitEval($node, $printer) {
    }

    public function visitIf($node, $printer) {
    }

    public function visitFor($node, $printer) {
    }

}

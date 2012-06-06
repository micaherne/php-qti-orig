<?php

// An attempt to generate PHP controller / view for a QTI item without XSLT
// This assumes that the XML is a valid QTI 2.1 item

// Test stuff - to be refactored out
$example = "choice_multiple";
$dom = new DOMDocument();
$dom->load("data/$example/$example.xml");
$gen = new qti_item_generator($dom);

$out = fopen("data/$example/{$example}_controller.php", 'w');
fputs($out, $gen->generate_controller($example));
fclose($out);

class qti_item_generator {
        
    public function __construct($dom) {
        $this->dom = $dom;
        $this->parse_declarations();
    }
    
    public function parse_declarations() {
        // Get response variable types (needed to work out how to render interactions)
        $this->responseDeclarations = array();
        $responseDeclarationNodes = $this->dom->getElementsByTagNameNS ('http://www.imsglobal.org/xsd/imsqti_v2p1', 'responseDeclaration');
        foreach($responseDeclarationNodes as $node) {
            $responseDeclaration = new stdClass();
            $identifier = $node->getAttribute('identifier');
            $responseDeclaration->identifier = $identifier;
            $responseDeclaration->cardinality = $node->getAttribute('cardinality');
            $responseDeclaration->baseType = $node->getAttribute('baseType');
            
            $this->responseDeclarations[$identifier] = $responseDeclaration;
        }
        
        $this->outcomeDeclarations = array();
        $outcomeDeclarationNodes = $this->dom->getElementsByTagNameNS ('http://www.imsglobal.org/xsd/imsqti_v2p1', 'outcomeDeclaration');
        foreach($outcomeDeclarationNodes as $node) {
            $outcomeDeclaration = new stdClass();
            $identifier = $node->getAttribute('identifier');
            $outcomeDeclaration->identifier = $identifier;
            $outcomeDeclaration->cardinality = $node->getAttribute('cardinality');
            $outcomeDeclaration->baseType = $node->getAttribute('baseType');
            $this->outcomeDeclarations[$identifier] = $outcomeDeclaration;
        }
    }
    
    public function generate_controller($id) {
        $result = "<?php \nclass {$id}_controller extends qti_item_controller {\n
    		public function __construct() {\n";
        
        // Create the itemBody generator function
        $result .= '$p = new qti_item_body($this);' . "\n";
        
        $itemBodyTags = $this->dom->getElementsByTagNameNS ('http://www.imsglobal.org/xsd/imsqti_v2p1', 'itemBody');
        foreach($itemBodyTags as $node) {
            $result .= $this->generating_function($node, '$p');
        }
        
        $result .= ";\n" . '$this->item_body = $p;' . "\n\n";
        
        // Create responseProcessing function
        // TODO: Deal with templates
        $result .= '$r = new qti_response_processing($this);' . "\n";
        $responseProcessingTags = $this->dom->getElementsByTagNameNS ('http://www.imsglobal.org/xsd/imsqti_v2p1', 'responseProcessing');
        foreach($responseProcessingTags as $node) {
            // Check for template
            // TODO: Remove hard coded location
            // TODO: Deal with other templates by downloading
            if (!is_null($node->attributes->getNamedItem('template'))) {
                $template = $node->attributes->getNamedItem('template');
                if (strpos($template->value, "http://www.imsglobal.org/question/qti_v2p1/rptemplates/") === 0) {
                    $template = str_replace("http://www.imsglobal.org/question/qti_v2p1/rptemplates/", '', $template->value);
                    $dom = new DOMDocument();
                    $template_location = dirname(__FILE__) . "/../ref/qtiv2p1pd2/rptemplates/$template.xml";
                    $dom->load(dirname(__FILE__) . "/../ref/qtiv2p1pd2/rptemplates/$template.xml");
                    $result .= $this->generating_function($dom->documentElement, '$r');
                }
                
            } else {
                $result .= $this->generating_function($node, '$r');
            }
        }
        
        $result .= ";\n" . '$this->response_processing = $r;' . "\n";
        
        // Close __construct
        $result .= "}";
        // TODO: Add: "public function beginAttempt() {" etc.
        $result .= "    public function beginAttempt() {
        parent::beginAttempt();

        \$this->response['RESPONSE'] = new qti_variable('single', 'identifier', array(
            'correct' => 'ChoiceA'
        ));
        \$this->outcome['SCORE'] = new qti_variable('single', 'integer', array(
            'default' => 0
        ));

    }";
        // Close class
        $result .= "}";
        return $result;
    }
    
    // Return a view / responseProcessing generating function for a given XML node
    public function generating_function($node, $varname = '$p') {
        if (($node->nodeType == XML_TEXT_NODE)){
            if (trim($node->nodeValue) == '') {
                return;
            } else {
                return $varname . '->__text(\'' . addslashes($node->nodeValue) . '\')';
            }
        }
        $result = $varname . '->' . $node->nodeName . '(';
            $children = array();
            if (count($node->attributes) > 0) {
                $attrs = array();
                foreach($node->attributes as $attr) {
                    $attrs[] = "'{$attr->name}' => '{$attr->value}'";
                }
                $children[] = 'array(' . implode(', ', $attrs) . ')';
            }
            if (!empty($node->childNodes)) {
                foreach($node->childNodes as $node) {
                    $childFunction = $this->generating_function($node, $varname);
                    if (!is_null($childFunction)) {
                        $children[] = $childFunction;
                    }
                }
            }
            $result .= implode(",\n", $children);
        $result .= ')';
        return $result;
    }
}
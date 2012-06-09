<?php

// An attempt to generate PHP controller / view for a QTI item without XSLT
// This assumes that the XML is a valid QTI 2.1 item

// Test stuff - to be refactored out
$example = "order";
$dom = new DOMDocument();
$dom->load("data/$example/$example.xml");
$gen = new qti_item_generator($dom);

$out = fopen("data/$example/{$example}_controller.php", 'w');
fputs($out, $gen->generate_controller($example));
fclose($out);

class qti_item_generator {
        
    public function __construct($dom) {
        $this->dom = $dom;
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
        parent::beginAttempt();\n";

        foreach($this->dom->getElementsByTagNameNS ('http://www.imsglobal.org/xsd/imsqti_v2p1', 'responseDeclaration') as $responseDeclarationNode) {
            $result .= $this->variable_declaration($responseDeclarationNode);
        }
        
        foreach($this->dom->getElementsByTagNameNS ('http://www.imsglobal.org/xsd/imsqti_v2p1', 'outcomeDeclaration') as $outcomeDeclarationNode) {
            $result .= $this->variable_declaration($outcomeDeclarationNode);
        }
        
        // TODO: Implement templateDeclaration

        // Close beginAttempt
        $result .= "}";
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
    
    // Return a qti_variable constructor given a responseDeclaration or outcomeDeclaration node
    public function variable_declaration($node) {
        /* \$this->response['RESPONSE'] = new qti_variable('single', 'identifier', array(
                    'correct' => 'ChoiceA'
        )); */
        $identifier = $node->getAttribute('identifier');
        $cardinality = $node->getAttribute('cardinality');
        $type = str_replace('Declaration', '', $node->nodeName);
        $result = '$this->' . $type . "['$identifier'] = new qti_variable('";
        $result .= $cardinality . "', '";
        $result .= $node->getAttribute('baseType') . "', array(";
        
        // Create params
        // TODO: Support things like "interpretation" attribute, record types etc.
        $params = array();
        foreach($node->childNodes as $child) {
            switch($child->nodeName) {
                case 'defaultValue':
                    $defaultValue = array();
                    foreach($child->childNodes as $valueNode) {
                        if ($valueNode->nodeType == XML_TEXT_NODE) {
                            continue;
                        }
                        $defaultValue[] = $valueNode->nodeValue;
                    }
                    if ($cardinality == 'single') {
                        $params[] = "'defaultValue' => '{$defaultValue[0]}'";
                    } else {
                        $params[] = "'defaultValue' => array('" . implode("','", $defaultValue) . "')";
                    }
                    break;
                case 'correctResponse':
                    $correctResponse = array();
                    foreach($child->childNodes as $valueNode) {
                        if ($valueNode->nodeType == XML_TEXT_NODE) {
                            continue;
                        }
                        $correctResponse[] = $valueNode->nodeValue;
                    }
                    if ($cardinality == 'single') {
                        $params[] = "'correctResponse' => '{$correctResponse[0]}'";
                    } else {
                        $params[] = "'correctResponse' => array('" . implode("','", $correctResponse) . "')";
                    }
                    break;
                case 'mapping':
                    $mapping = array();
                    foreach($child->attributes as $attr) {
                        $mapping[] = "'{$attr->name}' => '{$attr->value}'";
                    }
                    
                    $mapEntry = array();
                    foreach($child->childNodes as $valueNode) {
                        if ($valueNode->nodeType == XML_TEXT_NODE) {
                            continue;
                        }
                        $mapEntry[] = "'{$valueNode->getAttribute('mapKey')}' => '{$valueNode->getAttribute('mappedValue')}'";
                    }
                    
                    $mapping['mapEntry'] = "'mapEntry' => array(" . implode(",", $mapEntry) . ')';
                    
                    $params[] = "'mapping' => array(" . implode(",", $mapping) . ')';
                    break;
                    
                // TODO: Implement areaMapping
            }
        }
        
        $result .= implode(',', $params);
        
        $result .= '));';
        return $result;
    }
}
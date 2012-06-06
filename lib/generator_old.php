<?php

// An attempt to generate PHP controller / view for a QTI item without XSLT
// This assumes that the XML is a valid QTI 2.1 item

// Test stuff - to be refactored out
$dom = new DOMDocument();
$dom->load('data/choice_fixed/choice_fixed.xml');
$gen = new qti_item_generator($dom);

$out = fopen('data/choice_fixed/gen_choice_fixed_view.php', 'w');
fputs($out, $gen->generate_view());
fclose($out);

class qti_item_generator {
    
    protected $responseDeclarations = null;
    protected $outcomeDeclarations = null;
    
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
    
    public function generate_view() {
        $dom = $this->dom; // De-ref so it's less of a pain
        
        $itemBodyNodes = $dom->getElementsByTagNameNS ('http://www.imsglobal.org/xsd/imsqti_v2p1', 'itemBody');
        if($itemBodyNodes->length != 1) {
            throw new Exception('itemBody not found');
        }
        
        $itemBody = $itemBodyNodes->item(0);
        
        $result = '<div>';
        foreach($itemBody->childNodes as $node) {
            
            $result .= $this->rendernode($node);
        }
        
        $result .= '</div>';
        
        return $result;
    }
    
    public function generate_controller() {
        $dom = $this->dom;
        
        
    }
    
    public function rendernode($node) {
        if ($node->nodeType == XML_TEXT_NODE) {
            return $node->nodeValue;
        } else if($node->nodeType != XML_ELEMENT_NODE) {
            return ''; // ignore anything but elements and text
        }
        $renderMethod = 'renderview_' . $node->nodeName;
        if (method_exists($this, $renderMethod)) {
            return $this->$renderMethod($node);
        } else {
            $result = "<{$node->nodeName}";
            if ($attrs = $node->attributes) {
                foreach($attrs as $attr) {
                    $result .= " {$attr->name}=\"{$attr->value}\"";
                }
            }
            $result .= ">";
            if ($childNodes = $node->childNodes) {
                foreach($childNodes as $child) {
                    if ($child->nodeType == XML_ELEMENT_NODE) {
                        $result .= $this->rendernode($child);
                    } else if($child->nodeType == XML_TEXT_NODE) {
                        $result .= $child->nodeValue;
                    }
                }
            }
            $result .= "</{$node->nodeName}>";
            
            return $result;
        }
    }
    
    // TODO: Support shuffle & fixed
    public function renderview_choiceInteraction($node) {
        $responseIdentifier = $node->getAttribute('responseIdentifier');
        $result = "<form method=\"post\" id=\"choiceInteraction_{$responseIdentifier}\" class=\"qti_blockInteraction\">";
        
        // Work out what kind of HTML tag will be used for simpleChoices
        if (!isset($this->responseDeclarations[$responseIdentifier])) {
            throw new Exception("Declaration for $responseIdentifier not found");
        }
        $simpleChoiceType = 'radio';
        $brackets = ''; // we need brackets for multiple responses
        if ($this->responseDeclarations[$responseIdentifier]->cardinality == 'multiple') {
            $simpleChoiceType = 'checkbox';
            $brackets = '[]';
        }
        
        // Process child nodes
        foreach($node->childNodes as $child) {
            if ($child->nodeName == 'prompt') {
                $result .= '<span class="qti_prompt">';
                foreach($child->childNodes as $promptChild) {
                    if ($promptChild->nodeType == XML_TEXT_NODE) {
                        $result .= $promptChild->nodeValue;
                    } else {
                        $result .= $this->rendernode($promptChild);
                    }
                }
                $result .= "</span>";
            } else if ($child->nodeName == 'simpleChoice') {
                $result .= "<input type=\"{$simpleChoiceType}\" name=\"{$responseIdentifier}{$brackets}\" class=\"qti_simpleChoice\" value=\"{$child->getAttribute('identifier')}\"/>{$child->nodeValue}";
            } 
        }
        $result .= "<input type=\"submit\" />";
        $result .= "</form>";
        return $result;
    }
    
}
<?php

require dirname(__FILE__).'/../lib/generator.php';

class QTIVariableTest extends PHPUnit_Framework_TestCase {
    
    public function testVariableDeclaration() {
        
        $xml = '<responseDeclaration identifier="RESPONSE" cardinality="multiple" baseType="identifier">
		<correctResponse>
			<value>H</value>
			<value>O</value>
		</correctResponse>
		<mapping lowerBound="0" upperBound="2" defaultValue="-2">
			<mapEntry mapKey="H" mappedValue="1"/>
			<mapEntry mapKey="O" mappedValue="1"/>
			<mapEntry mapKey="Cl" mappedValue="-1"/>
		</mapping>
	</responseDeclaration>';
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $generator = new qti_item_generator(null);
        $this->response = array();
        $code = $generator->variable_declaration($dom->documentElement);
        echo $code;
    }
}
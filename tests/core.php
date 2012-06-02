<?php

require '../lib/core.php';

class QTIVariableTest extends PHPUnit_Framework_TestCase {
    public function testToString() {
        
        $variable1 = new qti_variable('single', 'integer', array('value' => 3));
        $this->assertEquals('single integer [3]', "" . $variable1);
        
        $variable2 = new qti_variable('multiple', 'identifier', array('value' => array('A', 'B')));
        $this->assertEquals('multiple identifier [A,B]', $variable2);

    }
}
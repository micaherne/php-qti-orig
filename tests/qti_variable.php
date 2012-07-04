<?php

require_once '../lib/core.php';

class QTIVariableTest extends PHPUnit_Framework_TestCase {
    
    public function testToString() {
        
        $variable1 = new qti_variable('single', 'integer', array('value' => 3));
        $this->assertEquals('single integer [3]', "" . $variable1);
        
        $variable2 = new qti_variable('multiple', 'identifier', array('value' => array('A', 'B')));
        $this->assertEquals('multiple identifier [A,B]', $variable2);

    }
    
    public function testMultiple() {
        $variable1 = new qti_variable('single', 'identifier', array('value' => 'thing1'));
        $variable2 = new qti_variable('single', 'identifier', array('value' => 'thing2'));
        $variable3 = new qti_variable('single', 'identifier', array('value' => 'thing3'));
        
        $result1 = qti_variable::multiple($variable1, $variable2, $variable3);
        $this->assertEquals('multiple', $result1->cardinality);
        $this->assertEquals('identifier', $result1->type);
        $this->assertEquals(3, count($result1->value));
        
        $result2 = qti_variable::multiple();
        $this->assertNull($result2->value);
        
        $null1 = new qti_variable('single', 'identifier');
        $null2 = new qti_variable('single', 'identifier');
        $result3 = qti_variable::multiple($null1, $null2);
        $this->assertNull($result3->value);
    }
    
    public function testOrdered() {
        $variable1 = new qti_variable('single', 'identifier', array('value' => 'thing1'));
        $variable2 = new qti_variable('single', 'identifier', array('value' => 'thing2'));
        $variable3 = new qti_variable('single', 'identifier', array('value' => 'thing3'));
    
        $result1 = qti_variable::ordered($variable1, $variable2, $variable3);
        $this->assertEquals('ordered', $result1->cardinality);
        $this->assertEquals('identifier', $result1->type);
        $this->assertEquals(3, count($result1->value));
    }
    
    public function testContainerSize() {
        $variable1 = new qti_variable('single', 'identifier', array('value' => 12));
        $result1 = $variable1->containerSize();
        $this->assertEquals(1, $result1->value);
        
        $variable2 = new qti_variable('multiple', 'identifier', array('value' => array('thing1', 'thing2')));
        $result2 = $variable2->containerSize();
        $this->assertEquals(2, $result2->value);
    }
    
    public function testIsNull() {
        $variable1 = new qti_variable('single', 'identifier');
        $result1 = $variable1->isNull();
        $this->assertEquals('single', $result1->cardinality);
        $this->assertEquals('boolean', $result1->type);
        $this->assertTrue($result1->value);
        
        // only empty strings and containers should be treated as null, not (e.g.) booleans
        $variable2 = new qti_variable('single', 'boolean', array('value' => false));
        $this->assertFalse($variable2->isNull()->value);
    }
    
    public function testIndex() {
        $variable1 = new qti_variable('multiple', 'identifier', array('value' => array('thing1', 'thing2')));
        $this->assertEquals("thing2", $variable1->index(2)->value);
    }
    
    public function testRandom() {
        $variable1 = new qti_variable('multiple', 'identifier', array('value' => array(2, 4, 6, 8, 10)));
        $result1 = $variable1->random();
        $this->assertEquals('single', $result1->cardinality);
        $this->assertEquals('identifier', $result1->type);
        $this->assertTrue($result1->value <= 10 && $result->value % 2 == 0);
    }
    
    public function testMember() {
        $variable1 = new qti_variable('single', 'identifier', array('value' => 6));
        $variable2 = new qti_variable('multiple', 'identifier', array('value' => array(2, 4, 6, 8, 10)));
        $variable3 = new qti_variable('single', 'identifier', array('value' => 5));
        
        $result1 = $variable1->member($variable2);
        $this->assertEquals('single', $result1->cardinality);
        $this->assertEquals('boolean', $result1->type);
        $this->assertTrue($result1->value);
        
        $result2 = $variable3->member($variable2);
        $this->assertFalse($result2->value);
    }
    
    public function testDelete() {
        $variable1 = new qti_variable('single', 'identifier', array('value' => 6));
        $variable2 = new qti_variable('multiple', 'identifier', array('value' => array(2, 4, 6, 8, 10)));
        
        $result1 = $variable1->delete($variable2);
        $this->assertEquals('multiple', $result1->cardinality);
        $this->assertEquals('identifier', $result1->type);
        $this->assertEquals(4, count($result1->value));
    
    }
    
    public function testArrays() {
        $arr1 = array(1, 5, 10, 15, 15, 20, 25);
        $arr2 = array(15, 20);
    }
    
    public function testContains() {
        $variable1 = new qti_variable('single', 'identifier', array('value' => 6));
        $variable2 = new qti_variable('multiple', 'identifier', array('value' => array(2, 4, 6, 8, 10)));
        $this->assertTrue($variable2->contains($variable1)->value);
        
        $variable3 = new qti_variable('multiple', 'identifier', array('value' => array(6, 8)));
        $this->assertTrue($variable2->contains($variable3)->value);
        
        $variable4 = new qti_variable('multiple', 'identifier', array('value' => array(6, 8, 8)));
        $this->assertFalse($variable2->contains($variable4)->value);
        
        // Test ordered
        $variable2->cardinality = 'ordered';
        $variable5 = new qti_variable('ordered', 'identifier', array('value' => array(6, 8)));
        $this->assertTrue($variable2->contains($variable5)->value);
        
        $variable6 = new qti_variable('ordered', 'identifier', array('value' => array(8, 6)));
        $this->assertFalse($variable2->contains($variable6)->value);
        
        $variable7 = new qti_variable('ordered', 'identifier', array('value' => array(8, 10)));
        $this->assertTrue($variable2->contains($variable7)->value);
        
    }
    
    public function testSubstring() {
        $variable1 = new qti_variable('single', 'string', array('value' => 'Scunthorpe'));
        $variable2 = new qti_variable('single', 'string', array('value' => 'thor'));
        $this->assertTrue($variable2->substring($variable1)->value);
        
        $variable3 = new qti_variable('single', 'string', array('value' => 'Thor'));
        $this->assertFalse($variable3->substring($variable1)->value);
        $this->assertTrue($variable3->substring($variable1, false)->value);
    }
    
    public function testNot() {
        $variable1 = new qti_variable('single', 'boolean', array('value' => true));
        $this->assertFalse($variable1->not()->value);
        
        $variable1->value = false;
        $this->assertTrue($variable1->not()->value);
        
        $variable1->value = null;
        $this->assertTrue($variable1->not()->isNull()->value);
    }
    
    public function testAnd() {
        $variable1 = new qti_variable('single', 'boolean', array('value' => true));
        $variable2 = new qti_variable('single', 'boolean', array('value' => false));
        $this->assertTrue(qti_variable::and_($variable1, $variable1)->value);
        $this->assertFalse(qti_variable::and_($variable1, $variable2, $variable1)->value);
    }
    
    public function testOr() {
        $variable1 = new qti_variable('single', 'boolean', array('value' => true));
        $variable2 = new qti_variable('single', 'boolean', array('value' => false));
        $this->assertTrue(qti_variable::or_($variable1, $variable1)->value);
        $this->assertTrue(qti_variable::or_($variable2, $variable1, $variable2)->value);
        $this->assertFalse(qti_variable::or_($variable2, $variable2)->value);
    }
    
    public function testAnyN() {
        $variable1 = new qti_variable('single', 'boolean', array('value' => true));
        $variable2 = new qti_variable('single', 'boolean', array('value' => false));
        $this->assertTrue(qti_variable::anyN(1, 3, $variable1, $variable1)->value);
        $this->assertFalse(qti_variable::anyN(1, 3, $variable1, $variable1, $variable1, $variable1)->value);
        
        $variable3 = new qti_variable('single', 'boolean');
        $this->assertNull(qti_variable::anyN(2, 4, $variable1, $variable3, $variable3, $variable3)->value);
    }

    public function testMatch() {
        $variable1 = new qti_variable('single', 'identifier', array('value' => 6));
        $this->assertTrue($variable1->match($variable1)->value);
        
        $variable2 = new qti_variable('single', 'identifier', array('value' => 4));
        $this->assertFalse($variable1->match($variable2)->value);
        
        $variable3 = new qti_variable('multiple', 'identifier', array('value' => array('A', 'B', 'C')));
        $this->assertTrue($variable3->match($variable3)->value);
    }
    
    public function testStringMatch() {
        $variable1 = new qti_variable('single', 'string', array('value' => 'Some String'));
        $this->assertTrue($variable1->stringMatch($variable1, true)->value);
        $this->assertTrue($variable1->stringMatch($variable1, false)->value);
        
        $variable2 = new qti_variable('single', 'string', array('value' => 'some string'));
        $this->assertTrue($variable1->stringMatch($variable1, false)->value);
        $this->assertFalse($variable1->stringMatch($variable2, true)->value);
        
        $variable3 = new qti_variable('single', 'string');
        $this->assertNull($variable3->stringMatch($variable1, true)->value);
    }
    
    public function testPatternMatch() {
        $variable1 = new qti_variable('single', 'string', array('value' => 'Some String'));
        $this->assertTrue($variable1->patternMatch('^Some')->value);
        $this->assertFalse($variable1->patternMatch('\d{3}')->value);
    }

    public function testLT() {
        $variable1 = new qti_variable('single', 'integer', array('value' => 5));
        $variable2 = new qti_variable('single', 'integer', array('value' => 300));
        $this->assertTrue($variable1->lt($variable2)->value);
        $this->assertFalse($variable2->lt($variable1)->value);
    }
    
    /* No tests for gt, lte, gte. Assume that any problems with these functions will 
     * also exist for lt */
    
    public function testSum() {
        $variable1 = new qti_variable('single', 'integer', array('value' => 5));
        $this->assertEquals(15, qti_variable::sum($variable1, $variable1, $variable1)->value);
    }
    
    public function testSubtract() {
        $variable1 = new qti_variable('single', 'integer', array('value' => 5));
        $this->assertEquals(0, $variable1->subtract($variable1)->value);
        
        $variable2 = new qti_variable('single', 'integer', array('value' => 2));
        $this->assertEquals(3, $variable1->subtract($variable2)->value);
        
    }
    
    public function testPower() {
        $variable1 = new qti_variable('single', 'integer', array('value' => 5));
        $this->assertEquals(3125, $variable1->power($variable1)->value);
    
        $variable2 = new qti_variable('single', 'integer', array('value' => 2));
        $this->assertEquals(25, $variable1->power($variable2)->value);
    
        $variable3 = new qti_variable('single', 'float', array('value' => 25));
        $variable4 = new qti_variable('single', 'float', array('value' => 0.5));
        $this->assertEquals(5, $variable3->power($variable4)->value);
        
    }
    
    public function testIntegerModulus() {
        $variable1 = new qti_variable('single', 'integer', array('value' => 5));
        $this->assertEquals(0, $variable1->integerModulus($variable1)->value);
        
        $variable2 = new qti_variable('single', 'integer', array('value' => 2));
        $this->assertEquals(1, $variable1->integerModulus($variable2)->value);
        
    }
    
    public function testTruncate() {
        $variable1 = new qti_variable('single', 'float', array('value' => 6.8));
        $this->assertEquals(6, $variable1->truncate()->value);
        $this->assertEquals('integer', $variable1->truncate()->type);
        
        $variable2 = new qti_variable('single', 'float', array('value' => -6.8));
        $this->assertEquals(-6, $variable2->truncate()->value);
        
    }
    
    public function testRound() {
        $variable1 = new qti_variable('single', 'float', array('value' => 6.8));
        $this->assertEquals(7, $variable1->round()->value);
        $this->assertEquals('integer', $variable1->round()->type);
    
        $variable2 = new qti_variable('single', 'float', array('value' => -6.5));
        $this->assertEquals(-6, $variable2->round()->value);
    
        $variable3 = new qti_variable('single', 'float', array('value' => 6.49));
        $this->assertEquals(6, $variable3->round()->value);
        
    }
}
<?php

require '../lib/generator.php';

// Not unit testing but we might as well use PHPUnit
class QTIPerformanceTest extends PHPUnit_Framework_TestCase {
    
    // See how long it takes to generate 1000 Monty Halls.
    public function testGenerate() {
        $start = microtime(true);
        for($i = 0; $i < 1000; $i++) {
            $dom = new DOMDocument();
            $dom->load('../ref/qtiv2p1pd2/examples/items/adaptive.xml');
            $generator = new qti_item_generator($dom);
        }
        echo "Time taken to generate 1000 Monty Halls: " . (microtime(true) - $start) . " seconds\n";
    }
}
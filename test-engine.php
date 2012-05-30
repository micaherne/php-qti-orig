<?php

/*
 * This is a test engine which just passes off all work to examples/choice_controller.php
 * 
 * It should really:
 * 
 * 1. examine the request to discover what item is being requested
 * 2. create the correct controller class for that item
 * 3. pass control to the controller
 * 
 * In theory it should also be able to display multiple items on the same page and
 * their controllers should be able to understand whether that particular item instance
 * has been submitted (multiple instances of same item should be allowed)
 */

require_once 'lib/core.php';

$controller_file = 'data/choice/choice_controller.php';
$controller_class = 'choice_controller';

require_once $controller_file;
$controller = new $controller_class();
$controller->persistence = new qti_persistence();
$controller->response_source = new qti_http_response_source();

$controller->run();
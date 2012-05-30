<?php

$choice = new DOMDocument();
$choice->load('ref/qtiv2p1pd2/examples/items/choice.xml');

$xsl = new XSLTProcessor();
$sheet = new DOMDocument();
$sheet->load('test.xsl');
$xsl->importStylesheet($sheet);

header("Content-Type: text/xml");
echo $xsl->transformToXml($choice); 

/* require_once 'lib/core.php';
require_once 'examples/choice_controller.php';

$controller = new choice_controller();
$controller->persistence = new qti_persistence();
$controller->response_source = new qti_http_response_source();
$controller->init();
$controller->start_session();
if(empty($_GET)) {
    echo $controller->render_body($type='text/xml');
} else {
    $controller->process_response();
} */
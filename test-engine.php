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

$item = $_GET['item'];
list($package, $itemid) = explode('/', $item);

// If it's a request for a resource, serve it
if (isset($_GET['resource'])) {
    if (!isset($_GET['path'])) {
        header("HTTP/1.0 400 Bad request");
        die('Path required'); // TODO: Should be bad request header
    }
    $path = "data/{$package}/" . $_GET['path'];
    if (!file_exists(dirname(__FILE__). '/' . $path)) {
        header("HTTP/1.0 404 Not found");
        die("$path Not found");
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimetype = $finfo->file($path);
    header("Content-Type: $mimetype");
    readfile($path);
    exit;
}

$controller_file = "data/{$package}/{$itemid}_controller.php";
$controller_class = "{$itemid}_controller";

require_once $controller_file;
$controller = new $controller_class();
$controller->rootDir = dirname(__FILE__). '/data/' . $package;
$controller->persistence = new qti_persistence();
$controller->response_source = new qti_http_response_source();
$controller->resource_provider = new qti_resource_provider($_SERVER['SCRIPT_NAME'], $item);

$controller->run();


/**
 * Given a relative URL such as 'images/sign.png' will provide an absolute URL
 * that will serve the given resource.
 * @author Michael
 *
 */
class qti_resource_provider {
    // TODO: Where on earth should this class go?? Is it part of core or the engine?

    public $script;
    public $item;
    public $package;
    public $itemid;

    public function __construct($script, $item) {
        $this->script = $script;
        $this->item = $item;
        list($package, $itemid) = explode('/', $item);
        $this->package = $package;
        $this->itemid = $itemid;
    }

    public function urlFor($relativePath) {
        return $this->script . '?resource=true&path=' . urlencode($relativePath) . '&item=' . urlencode($this->item); 
    }

}
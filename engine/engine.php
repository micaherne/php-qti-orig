<?php

/*
 * Engine is a front controller which:
 * 
 * 1. Accepts uploaded files and creates controllers for them
 * 2. Accepts an identifier for an item and instantiates a controller for it
 * 3. Proxies resource files
 * 
 * In theory this should be able to display multiple items on the same page and
 * their controllers should be able to understand whether that particular item instance
 * has been submitted (multiple instances of same item should be allowed)
 */

require_once 'config.php';
require_once '../lib/core.php';
require_once '../lib/generator.php';

/**
 * Given a relative URL such as 'images/sign.png' will provide an absolute URL
 * that will serve the given resource.
 * @author Michael
 *
 */
class qti_simple_resource_provider implements qti_resource_provider {
    // This class isn't part of core.php as there isn't really a sensible
    // generic implementation - it really needs to be implemented by the application

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
        return $this->script . '?resource=true&item=' . urlencode($this->item) . '&path=' . urlencode($relativePath);
    }

}

$item = $_GET['item'];
list($package, $itemid) = explode('/', $item);

// If it's a request for a resource, serve it
if (isset($_GET['resource'])) {
    if (!isset($_GET['path'])) {
        header("HTTP/1.0 400 Bad request");
        die('Path required'); // TODO: Should be bad request header
    }
    $path = "$datadir/{$package}/" . $_GET['path'];
    if (!file_exists($path)) {
        header("HTTP/1.0 404 Not found");
        die("$path Not found");
    }
    
    /*
     * Try to determine certain file types from extension rather
     * than relying on mime magic, as e.g. css files don't work 
     * in some browsers if they aren't served with proper type.
     */
    $mimetype = null;
    if ($ext = pathinfo($path, PATHINFO_EXTENSION)) {
        switch ($ext) {
            case 'css':
                $mimetype = 'text/css';
                break;
        }
    }
    
    if (is_null($mimetype)) {
        $finfo = new finfo(FILEINFO_MIME);
        $mimetype = $finfo->file($path);
    }
    
    header("Content-Type: $mimetype");
    readfile($path);
    exit;
}

$controller_file = "$datadir/{$package}/{$itemid}_controller.php";
$controller_class = "{$itemid}_controller";

require_once $controller_file;
$controller = new $controller_class("{$package}/{$itemid}");
$controller->setPersistence(new qti_session_persistence());
$controller->setResponseSource(new qti_http_response_source());
$controller->setResourceProvider(new qti_simple_resource_provider($_SERVER['SCRIPT_NAME'], $item));

$controller->show_debugging = true;

// $controller->run is called in view.php in the correct place


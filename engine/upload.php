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

if($_FILES) {
    // Deal with uploaded files
    
    // TODO: Implement this sensibly
    // Create a folder
    $folderno = 0;
    while (file_exists($datadir . '/' . $folderno)) {
        $folderno++;
    }
    
    $basedir = $datadir . '/' . $folderno;
    echo $basedir;
    mkdir($basedir);
    // foreach not really necessary but easier
    foreach($_FILES as $file) {
        $filepath = $basedir . '/' . $file['name'];
        move_uploaded_file($file['tmp_name'], $filepath);
        $uploadedfileinfo = pathinfo($filepath);
        switch ($uploadedfileinfo['extension']) {
            case 'xml':
                // Generate a controller file
                $filename = $uploadedfileinfo['filename'];
                $dom = new DOMDocument();
                $dom->load($uploadedfileinfo['dirname'] . '/' . $uploadedfileinfo['basename']);
                $gen = new qti_item_generator($dom);
                $out = fopen($uploadedfileinfo['dirname'] . '/' . "{$filename}_controller.php", 'w');
                fputs($out, $gen->generate_controller($filename));
                fclose($out);
                                
                // TODO: Then what? Redirect to view?
                break;
            case 'zip':
                echo "Content packaged items not implemented";
                break;
            default:
                echo "ERROR $uploadedfiletype";
                break;
        }
    }
    exit;
}

$item = $_GET['item'];
list($package, $itemid) = explode('/', $item);

// If it's a request for a resource, serve it
if (isset($_GET['resource'])) {
    if (!isset($_GET['path'])) {
        header("HTTP/1.0 400 Bad request");
        die('Path required'); // TODO: Should be bad request header
    }
    $path = "../data/{$package}/" . $_GET['path'];
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
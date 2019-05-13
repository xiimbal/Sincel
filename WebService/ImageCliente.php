<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";

$server = new soap_server();
// configure  WSDL
$server->configureWSDL('Upload File', 'urn:uploadwsdl');

// Register the method to expose
$server->register('upload_file', // method
        array('file' => 'xsd:string', 'location' => 'xsd:string'), // input parameters
        array('return' => 'xsd:string'), // output parameters
        'urn:uploadwsdl', // namespace
        'urn:uploadwsdl#upload_file', // soapaction
        'rpc', // style
        'encoded', // use
        'Uploads files to the server'                                // documentation
);

// Define the method as a PHP function

function upload_file($encoded, $name) {    
    $this_dir = dirname(__FILE__);// path to admin/

    $parent_dir = realpath($this_dir . '/..');// admin's parent dir path can be represented by admin/..
    $location = $parent_dir . "/WebService/uploads/$name"; // Mention where to upload the file            

    $contador = 1;
    while (file_exists($location)) {
        $name_aux = "($contador)" . $name;
        $location = $parent_dir . "/WebService/uploads/$name_aux"; // Mention where to upload the file            
        $contador++;
    }
    $fp = fopen($location, "x");
    fclose($fp);
    //$file_get = file_get_contents($location);
    $current = base64_decode($encoded); // Now decode the content which was sent by the client         
    if (file_put_contents($location, $current) == FALSE) {// Write the decoded content in the file mentioned at particular location      
        return "Please upload a file...";
    } else {
        return "File $location Uploaded successfully...";// Output success message                              
    }
}

// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>
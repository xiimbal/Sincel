<?php
header('Content-Type: image/png');

if(!isset($_GET['texto']) || $_GET['texto']==""){
    header("Location: ../index.php");
}

$texto = $_GET['texto'];

require_once('../WEB-INF/Classes/barcode/BCGFontFile.php');
require_once('../WEB-INF/Classes/barcode/BCGColor.php');
require_once('../WEB-INF/Classes/barcode/BCGDrawing.php');
require_once('../WEB-INF/Classes/barcode/BCGcode128.barcode.php');

$colorFont = new BCGColor(0, 0, 0);
$colorBack = new BCGColor(255, 255, 255);
$font = new BCGFontFile('../WEB-INF/Classes/barcode/font/Arial.ttf', 18);

$code = new BCGcode128(); // Or another class name from the manual
$code->setScale(2); // Resolution
$code->setThickness(30); // Thickness
$code->setForegroundColor($colorFont); // Color of bars
$code->setBackgroundColor($colorBack); // Color of spaces
$code->setFont($font); // Font (or 0)
$code->parse($texto); // Text

$drawing = new BCGDrawing('', $colorBack);
$drawing->setBarcode($code);
$drawing->draw();

$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
?>,

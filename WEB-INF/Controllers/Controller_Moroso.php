<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Moroso.class.php");
include_once("../../Classes/Cliente.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/CentroCosto.class.php");
include_once("../../Classes/CentroCostoReal.class.php");
include_once("../../Classes/Mail.class.php");
include_once("../../Classes/ParametroGlobal.class.php");

$parametroGlobal = new ParametroGlobal();
if($parametroGlobal->getRegistroById("8")){
    $correo_emisor = ($parametroGlobal->getValor());
}else{
    $correo_emisor = ("scg-salida@scgenesis.mx");
}

$parametros = "";
if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}
$array = Array();
if (isset($parametros['morosos'])) {
    $array = $parametros['morosos'];
}
$moroso = new Moroso();
$cliente = new Cliente();
$cliente->getRegistroById($parametros['clave']);
$tipo = $cliente->getIdTipoMorosidad();
$moroso->setCliente($parametros['clave']);
if ($tipo == 1) {
    if ($moroso->MorosoCliente()) {
        echo "El cliente ah pasado ha estatus de moroso";
    } else {
        echo "Error: No se pudo pasar el cliente a moroso";
    }
} elseif ($tipo == 2) {
    $moroso->setLocalidad($array);
    $cc = new CentroCosto();
    $message = "Las localidades";
    foreach ($array as $value) {
        $cc->getRegistroById($value);
        $message.=" " . $cc->getNombre() . ",";
    }
    $message = substr($message, 0, strlen($message) - 1);
    $message.=" del cliente " . $cliente->getNombreRazonSocial() . " se han marcado como morosas.";
    if ($moroso->MorosoLocalidad()) {
        echo "Las localidades se han actualizado";
    } else {
        echo "Error: No se pudo actualizar las localidades intentelo nuevamente";
    }
} else {
    $moroso->setLocalidad($array);
    $cc = new CentroCostoReal();
    $message = "Los centros de costo";
    foreach ($array as $value) {
        $cc->setId_cc($value);
        $cc->getRegistrobyID();
        $message.=" " . $cc->getNombre() . ",";
    }
    $message = substr($message, 0, strlen($message) - 1);
    $message.=" del cliente " . $cliente->getNombreRazonSocial() . " se han marcado como morosos.";
    if ($moroso->MorosoCC()) {
        echo "Los centros de costo se han actualizado";
    } else {
        echo "Error: No se pudo actualizar los centros de costo intentelo nuevamente";
    }
}
$mail = new Mail();
$mail->setFrom($correo_emisor);
$mail->setSubject("Moroso: " . $cliente->getNombreRazonSocial());
$catalogo = new Catalogo();
$query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud=3;");
$z = 0;
while ($rs = mysql_fetch_array($query4)) {
    $correos[$z] = $rs['correo'];
    $z++;
}
$mail->setBody($message);
foreach ($correos as $value) {
    if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
        $mail->setTo($value);
        if ($mail->enviarMail() == "1") {
            //                     echo "Se envio un correo de aviso.";
        } else {
            echo "Error: El correo no se pudo enviar.";
        }
    }
}
?>
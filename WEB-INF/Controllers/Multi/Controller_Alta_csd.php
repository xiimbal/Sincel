<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../../Classes/CFDI.class.php");
include_once("../../Classes/Catalogo.class.php");
$obj = new CFDI();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setId_Cfdi($_GET['id']);
    if ($obj->deletebyID()) {
        echo "El CSD se eliminó correctamente";
    } else {
        echo "El CSD no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    $ruta = "../../../CSD/";
    foreach ($_FILES as $key) {
        if ($key['error'] == UPLOAD_ERR_OK) {//Verificamos si se subio correctamente
            $nombre = $key['name']; //Obtenemos el nombre del archivo
            if (strpos($nombre, '.cer') !== false) {
                $obj->setCsd($nombre);
            } else {
                if (strpos($nombre, '.pem') !== false) {
                    $obj->setPem($nombre);
                } else {
                    $obj->setArchivo_key($nombre);
                }
            }
            $temporal = $key['tmp_name']; //Obtenemos el nombre del archivo temporal
            move_uploaded_file($temporal, $ruta . $nombre); //Movemos el archivo temporal a la ruta especificada
        } else {
            echo $key['error']; //Si no se cargo mostramos el error
        }
    }
    $obj->setCsd_password($_POST['pass']);
    $obj->setNombre($_POST['nombre']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla('PHP Alta CSD');
    $obj->setNoCertificado($_POST['certificado']);
    $obj->setNoSAT($_POST['nosat']);
    if (isset($_POST['id']) && $_POST['id'] != "") {
        $obj->setId_Cfdi($_POST['id']);
        if ($obj->updateRegistro()) {
            echo "El CFDI se actualizó correctamente";
        } else {
            echo "Error: No se actualizo correctamente, intente más tarde o contacte con el administrador";
        }
    } else {
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista("SELECT * FROM c_cfdi WHERE nombre='" . $_POST['nombre'] . "'");
        if ($rs = mysql_fetch_array($result)) {
            echo "Error: el nombre del CFDI ya existe intente con otro";
        } else {
            if ($obj->nuevoRegistro()) {
                echo "El CFDI se registró correctamente";
            } else {
                echo "Error: No se registró correctamente, intente más tarde o contacte con el administrador";
            }
        }
    }
}
?>

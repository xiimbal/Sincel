<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../../Classes/Mail.class.php");
include_once("../../Classes/CatalogoFacturacion.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/ParametroGlobal.class.php");
include_once("../../Classes/Parametros.class.php");

$parametroGlobal = new ParametroGlobal();
$parameter = new Parametros();

if($parameter->getRegistroById("18") && $parameter->getActivo() == "1"){
    $carpeta_virtual = $parameter->getDescripcion();
    $index_ultimo = strrpos($carpeta_virtual, "/");
    if($index_ultimo !== false){
        $carpeta_virtual = substr($carpeta_virtual, $index_ultimo+1);
    }
}else{
    $carpeta_virtual = "";
}

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Factura enviada</title>
    </head>
    <body>
        <?php
        
        if (substr($_POST['correos'], -1) == ";") {
            $correos_str = substr($_POST['correos'], 0, -1);
        } else {
            $correos_str = $_POST['correos'];
        }
        $correos_aux = explode(";", $correos_str);
        foreach ($_POST['contactos'] as $value) {
            if(!in_array($value, $correos_aux)){
                array_push($correos_aux, $value);
            }
        }
        
        $correos = array_unique($correos_aux);
        $mail = new Mail();        
        if($parametroGlobal->getRegistroById("9")){
            $mail->setFrom($parametroGlobal->getValor());
        }else{
            $mail->setFrom("facturas.scg@scgenesis.mx");
        }
        $catalogo_fact = new CatalogoFacturacion();
        $result = $catalogo_fact->obtenerLista("SELECT PathPDF,PathXML FROM c_factura WHERE IdFactura=" . $_POST['id']);
        if ($rs = mysql_fetch_array($result)) {
            if ($rs['PathPDF'] != "" && $rs['PathXML'] != "") {
                $mail->setSubject($_POST['titulo']);
                $mail->setBody($_POST['comentario']);
                if (strpos($rs['PathPDF'], 'PDF/') !== false) {
                    if(!file_exists("../../../" . $rs['PathPDF']) || !file_exists("../../../" . $rs['PathXML'])){
                        echo "<br/>Error: No se encontraron los archivos en el servidor";
                        return;
                    }else{
                        $mail->setAttachPDF("../../../" . $rs['PathPDF']);
                        $mail->setAttachXML("../../../" . $rs['PathXML']);     
                    }
                }else{
                    $index = strrpos($rs['PathPDF'], "Facturas");
                    if($index === FALSE){                            
                        if(!file_exists("../../../" .$carpeta_virtual."/".$rs['PathPDF']) || !file_exists("../../../" .$carpeta_virtual."/".$rs['PathXML'])){
                            echo "<br/>Error: No se encontraron los archivos en el servidor";
                            return;
                        }
                        $mail->setAttachPDF("../../../" .$carpeta_virtual."/".$rs['PathPDF']);
                        $mail->setAttachXML("../../../" .$carpeta_virtual."/".$rs['PathXML']);
                    }else{
                        if(!file_exists("../../../" .$carpeta_virtual."/".substr($rs['PathPDF'], $index+9)) 
                                || !file_exists("../../../" .$carpeta_virtual."/".substr($rs['PathXML'], $index+9))){
                            echo "<br/>Error: No se encontraron los archivos en el servidor";
                            return;
                        }
                        $mail->setAttachPDF("../../../" .$carpeta_virtual."/".substr($rs['PathPDF'], $index+9));
                        $mail->setAttachXML("../../../" .$carpeta_virtual."/".substr($rs['PathXML'], $index+9));
                    }
                }
                
                 /* Obtenemos los correos a quien mandaremos el mail por default de revisión*/
                $catalogo = new Catalogo();
                $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 8;");                
                while ($rs = mysql_fetch_array($query4)) {                    
                    array_push($correos, $rs['correo']);
                }
                
                $correos_correctos = array();
                foreach ($correos as $value) {//Se envia el correo a las direcciones capturadas por el usuario.                    
                    if(isset($value) && $value!=""){
                        if (isset($value) && $value != "" && $value != NULL && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                            array_push($correos_correctos, $value);                            
                        } else {
                            echo "<br/>Error: correo electrónico inválido <b>$value</b>";
                        }
                    }
                }
                                
                if(!empty($correos_correctos)){                    
                    $mail->setTo($correos_correctos);
                    if ($mail->enviarMailPDF()) {
                        $catalogo_fact->obtenerLista("UPDATE c_factura SET FacturaEnviada=1 WHERE IdFactura=" . $_POST['id']);
                        echo "<br/>Se envío un correo a los destinatarios: ";
                        foreach ($correos_correctos as $value) {
                            echo "<br/> $value";
                        }
                    } else {
                        echo "Error: no se envío el correo " . $value . ".";
                    }
                }
                
            } else {
                echo "Error: Los archivos no se encontraron";
            }
        } else {
            echo "Error:no se pudo encontrar la factura";
        }
        ?>        
    </body>
</html>
<?php

if(isset($_POST['sesion']) || isset($_GET['sesion'])){
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../../index.php");
    }
    include_once("../Classes/ClientesExportacion.class.php");
    include_once("../Classes/Mail.class.php");
    include_once("../Classes/Catalogo.class.php");
    include_once("../Classes/Usuario.class.php");
    include_once("../Classes/Cliente.class.php");        
    include_once("../Classes/Parametros.class.php");
    include_once("../Classes/ParametroGlobal.class.php");
    $usuario = $_SESSION['user'];
}else{
    include_once("WEB-INF/Classes/ClientesExportacion.class.php");
    include_once("WEB-INF/Classes/Mail.class.php");
    include_once("WEB-INF/Classes/Catalogo.class.php");
    include_once("WEB-INF/Classes/Usuario.class.php");
    include_once("WEB-INF/Classes/Cliente.class.php");
    include_once("WEB-INF/Classes/Parametros.class.php");
    include_once("WEB-INF/Classes/ParametroGlobal.class.php");
    $usuario = "CRON PHP";
}

$catalogo = new Catalogo();
$mail = new Mail();
$parametroGlobal = new ParametroGlobal();
date_default_timezone_get();

$correos = array();
/*Correos para el cron por default*/
$result = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5;");
while($rs = mysql_fetch_array($result)){
    if(isset($rs['correo']) && $rs['correo']!="" && filter_var($rs['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
        array_push($correos, $rs['correo']);
    }
}
/*Correos de usuarios con el puesto de cuentas por cobrar*/
$consulta = "SELECT CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS cxc, correo 
FROM c_usuario AS u WHERE u.IdPuesto = 15;";
$result = $catalogo->obtenerLista($consulta);
while($rs = mysql_fetch_array($result)){
    if(isset($rs['correo']) && $rs['correo']!="" && filter_var($rs['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
        array_push($correos, $rs['correo']);
    }
}

$parametro = new Parametros();
$parametro->getRegistroById("12");
if(($parametro->getValor())!=null){
    $meses = $parametro->getValor();
}else{
    $meses = 3;
}

$cliente_exportacion = new ClientesExportacion();

$result = $cliente_exportacion->ObtenerClientesMorososSinAdeudo();
if(mysql_num_rows ($result)>0){
    $mail = new Mail();
    if($parametroGlobal->getRegistroById("8")){
        $mail->setFrom($parametroGlobal->getValor());
    }else{
        $mail->setFrom("scg-salida@scgenesis.mx");
    }
    $usuario_anterior = "";
    $correo_anterior = "";
    $ejecutivo = "";
    $mensaje = "Los siguientes clientes morosos ya no tienen facturas pendientes mayor a $meses meses y han sido marcados como no morosos: <br/><br/>";
    $mensaje.="<table><thead><th>Cliente</th><th>RFC</th><th>Ejecutivo de Cuenta</th></thead>";
    $mensaje.="<tbody>";
    $contador = 0;
    while($rs = mysql_fetch_array($result)){
        if($usuario_anterior != "" && $usuario_anterior != $rs['IdUsuario']){
            $mensaje.="</tbody>";
            $mensaje.="</table>";
            $mail->setSubject("Clientes morosos sin facturas pendientes del ejecutivo $ejecutivo");
            $mail->setBody($mensaje);
            if($usuario_anterior!="NA"){
                if(isset($correo_anterior) && $correo_anterior!="" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                    $mail->setTo($correo_anterior);
                    if ($mail->enviarMail() == "1") {
                        //echo "<br/>Un correo fue enviado de clientes no morosos a ".$correo_anterior;
                    } else {
                        echo "<br/>Error: No se pudo enviar el correo a ".$correo_anterior.".";
                    }
                }else{
                    $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                    $mail->setSubject("Facturas pendientes de cancelar del ejecutivo $ejecutivo");
                    $mail->setBody($message_ejecutivo_extra. " ".$mensaje);
                }
            }else{
                $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                $mail->setSubject("Clientes morosos sin facturas pendientes");
                $mail->setBody($message_ejecutivo_extra. " ".$mensaje);
            }
            
            foreach ($correos as $value) {/*Lo mandamos a los correos de los usuarios de cuentas por cobrar*/        
                $mail->setTo($value);
                if ($mail->enviarMail() == "1") {
                    //echo "<br/>Un correo fue enviado de clientes no morosos a ".$correo_anterior;
                } else {
                    echo "<br/>Error: No se pudo enviar el correo a $value.";
                }
            }
            
            $mensaje = "Los siguientes clientes morosos ya no tienen facturas pendientes mayor a $meses meses y han sido marcados como no morosos: <br/><br/>";
            $mensaje.="<table><thead><th>Cliente</th><th>RFC</th><th>Ejecutivo de Cuenta</th></thead>";
            $mensaje.="<tbody>";
        }
        $cliente_exportacion->setIdEstatusCobranza("1");
        $cliente_exportacion->setPantalla("PHP Controler_MarcarNoMoroso.php");
        $cliente_exportacion->setUsuarioModificacion($_SESSION['user']);
        if($cliente_exportacion->editarEstatusCobranzaCliente($rs['ClaveCliente'])){
            $mensaje.= ("<tr><td>".$rs['ClaveCliente']." - ".$rs['NombreRazonSocial']."</td><td>".$rs['RFC']."</td><td>".$rs['EjecutivoCuenta']."</td></tr>");
        }else{
            echo "<br/> El cliente ".$rs['NombreRazonSocial']." no pudo ser actualizado de estatus de cobranza.";
        }        
        
        if(isset($rs['IdUsuario']) && $rs['IdUsuario']!="" && $rs['IdUsuario']!="NA"){
            $correo_anterior = $rs['correo'];
            $usuario_anterior = $rs['IdUsuario'];
            $ejecutivo = $rs['EjecutivoCuenta'];
        }else{
            $correo_anterior = "NA";
            $usuario_anterior = "NA";
            $ejecutivo = "NA";
        }
        $contador++;
    }
    if($usuario_anterior != ""){
        $mensaje.="</tbody>";
        $mensaje.="</table>";
        $mail->setSubject("Clientes morosos sin facturas pendientes del ejecutivo $ejecutivo");
        $mail->setBody($mensaje);
        if($usuario_anterior!="NA"){
            if(isset($correo_anterior) && $correo_anterior!="" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                $mail->setTo($correo_anterior);
                if ($mail->enviarMail() == "1") {
                    //echo "<br/>Un correo fue enviado de clientes no morosos a ".$correo_anterior;
                } else {
                    echo "<br/>Error: No se pudo enviar el correo a ".$correo_anterior.".";
                }
            }else{
                $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                $mail->setSubject("Facturas pendientes de cancelar del ejecutivo $ejecutivo");
                $mail->setBody($message_ejecutivo_extra. " ".$mensaje);
            }
        }else{
            $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
            $mail->setSubject("Clientes morosos sin facturas pendientes");
            $mail->setBody($message_ejecutivo_extra. " ".$mensaje);
        }

        foreach ($correos as $value) {/*Lo mandamos a los correos de los usuarios de cuentas por cobrar*/        
            $mail->setTo($value);
            if ($mail->enviarMail() == "1") {
                //echo "<br/>Un correo fue enviado de clientes no morosos a ".$correo_anterior;
            } else {
                echo "<br/>Error: No se pudo enviar el correo a $value.";
            }
        }       
    }
    echo "<br/><br/>El proceso ha terminado, se cambiaron $contador cliente(s) a NO morosos y se han enviado los correos correspondientes";
}else{
    echo "<br/><br/>No hay clientes morosos que marcar como no morosos";
}

?>

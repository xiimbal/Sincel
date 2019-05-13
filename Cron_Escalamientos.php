<?php

header('Content-Type: text/html; charset=utf-8');
include_once("WEB-INF/Classes/Catalogo.class.php");
include_once("WEB-INF/Classes/ParametroGlobal.class.php");
include_once("WEB-INF/Classes/Mail.class.php");
include_once("WEB-INF/Classes/ConexionMultiBD.class.php");

$con = new ConexionMultiBD();         
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();
while($rs_multi = mysql_fetch_array($result_bases)){
    echo "<br/><br/>Procesando empresa ".$rs_multi['nombre_empresa'];
    $empresa = $rs_multi['id_empresa'];
    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);
    $parametroGlobal = new ParametroGlobal();
    $parametroGlobal->setEmpresa($empresa);
    
    $queryEscalamientos = "SELECT cee.*,e.Nombre,e.IdEstadoTicket from c_escalamientoEstado cee LEFT JOIN c_estado AS e ON cee.idEstado = e.IdEstado";
    $resultEscalamiento = $catalogo->obtenerLista($queryEscalamientos);
    while($rsEscalamiento = mysql_fetch_array($resultEscalamiento)){ 
        if(isset($rsEscalamiento['IdEstadoTicket'])){
            $queryNotas = "SELECT t.NombreCliente, t.IdTicket, t.ClaveCliente, t.Prioridad FROM c_ticket t 
                WHERE DATEDIFF(NOW(),t.FechaModificacionEstadoTicket) = ".$rsEscalamiento['tiempoEnvio']." AND (t.EstadoDeTicket != 2 AND t.EstadoDeTicket!=4)
                AND t.EstadoDeTicket = ".$rsEscalamiento['IdEstadoTicket'];
            $resultNota = $catalogo->obtenerLista($queryNotas);
            while($rsNota = mysql_fetch_array($resultNota)){
                if($rsEscalamiento['prioridad'] < $rsNota['Prioridad']){
                    $updatePrioridad = "UPDATE c_ticket t SET t.Prioridad = (SELECT pt.IdPrioridad from c_prioridadticket pt WHERE pt.Prioridad = ".$rsEscalamiento['prioridad'].")
                            WHERE t.IdTicket = ".$rsNota['IdTicket'];
                    echo $updatePrioridad;
                    $rsUpdate = $catalogo->obtenerLista($updatePrioridad);
                    if($rsUpdate){
                        echo "<br/>Se ha modificado la prioridad del ticket ".$rsNota['IdTicket']." debido a que el escalamiento tenía una prioridad mayor";
                    }
                }
                $correos = array();
                $mail = new Mail();
                $mail->setSubject("Atención al ticket ".$rsNota['IdTicket']);
                $mail->setBody("<br/>Ya han pasado ".$rsEscalamiento['tiempoEnvio']." días desde que
                        el ticket <b>".$rsNota['IdTicket']."</b> del cliente ".$rsNota['NombreCliente']." se
                        encuentra en el estado ".$rsEscalamiento['Nombre']."<br/>"."Mensaje: ".$rsEscalamiento['mensaje']);
                if($parametroGlobal->getRegistroById("8")){
                    $mail->setFrom($parametroGlobal->getValor());
                }else{
                    $mail->setFrom("scg-salida@scgenesis.mx");
                }
                /* Obtenemos los correos a los que le enviaremos la informacion */
                $queryCorreos = "SELECT correo from c_escalamientoCorreo ec WHERE idEscalamiento = ".$rsEscalamiento['idEscalamiento'];
                $resultCorreos = $catalogo->obtenerLista($queryCorreos);
                while($rsCorreo = mysql_fetch_array($resultCorreos)){
                    $tipo = substr($rsCorreo['correo'], 0, 2);
                    if(strcmp($tipo, "cl") == 0){
                        $queryFinal = "SELECT CorreoElectronicoEnvioFact1, CorreoElectronicoEnvioFact2, CorreoElectronicoEnvioFact3,CorreoElectronicoEnvioFact4
                                from c_cliente WHERE ClaveCliente = ".$rsNotas['ClaveCliente'];
                    }else if(strcmp($tipo, "co") == 0){
                        $queryFinal = "SELECT CorreoElectronico from c_contacto WHERE IdTipoContacto = ".substr($rsCorreo['correo'], 2);
                    }else if(strcmp($tipo,"us") == 0){
                        $queryFinal = "SELECT correo from c_usuario WHERE idUsuario = ".substr($rsCorreo['correo'], 2);
                    }else if(strcmp($tipo, "tf") == 0){
                        $queryFinal = "SELECT u.correo from k_tfscliente ktc 
                            LEFT JOIN c_usuario u ON ktc.IdUsuario = u.IdUsuario 
                            LEFT JOIN c_ticket t ON t.ClaveCliente = ktc.ClaveCliente
                            WHERE t.IdTicket = ".$rsNota['IdTicket'];
                    }
                    $resultFinal = $catalogo->obtenerLista($queryFinal);
                    while($rsFinal = mysql_fetch_array($resultFinal)){
                        if(isset($rsFinal['CorreoElectronicoEnvioFact1']) && $rsFinal['CorreoElectronicoEnvioFact1'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact1'], FILTER_VALIDATE_EMAIL)){
                            array_push($correos, $rsFinal['CorreoElectronicoEnvioFact1']);
                        }
                        if(isset($rsFinal['CorreoElectronicoEnvioFact2']) && $rsFinal['CorreoElectronicoEnvioFact2'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact2'], FILTER_VALIDATE_EMAIL)){
                            array_push($correos, $rsFinal['CorreoElectronicoEnvioFact2']);
                        }
                        if(isset($rsFinal['CorreoElectronicoEnvioFact3']) && $rsFinal['CorreoElectronicoEnvioFact3'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact3'], FILTER_VALIDATE_EMAIL)){
                            array_push($correos, $rsFinal['CorreoElectronicoEnvioFact3']);
                        }
                        if(isset($rsFinal['CorreoElectronicoEnvioFact4']) && $rsFinal['CorreoElectronicoEnvioFact4'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact4'], FILTER_VALIDATE_EMAIL)){
                            array_push($correos, $rsFinal['CorreoElectronicoEnvioFact4']);
                        }
                        if(isset($rsFinal['CorreoElectronico']) && $rsFinal['CorreoElectronico'] != "" && filter_var($rsFinal['CorreoElectronico'], FILTER_VALIDATE_EMAIL)){
                            array_push($correos, $rsFinal['CorreoElectronico']);
                        }
                        if(isset($rsFinal['correo']) && $rsFinal['correo'] != "" && filter_var($rsFinal['correo'], FILTER_VALIDATE_EMAIL)){
                            array_push($correos, $rsFinal['correo']);
                        }
                    }
                }
                foreach ($correos as $value) {/*Lo mandamos a los correos de los usuarios de cuentas por cobrar*/        
                    $mail->setTo($value);
                    if ($mail->enviarMail() == "1") {
                        echo "<br/>Un correo fue enviado por escalamientos a $value.";
                    } else {
                        echo "<br/>Error: No se pudo enviar el correo a $value.";
                    }
                }
            }
        }
        $queryNotas = "SELECT t.NombreCliente, nt.IdTicket, t.ClaveCliente, t.Prioridad FROM c_ticket t 
            LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) from c_notaticket WHERE IdTicket = t.IdTicket)
            WHERE DATEDIFF(NOW(),nt.FechaHora) = ".$rsEscalamiento['tiempoEnvio']." AND (t.EstadoDeTicket != 2 AND t.EstadoDeTicket!=4)
            AND nt.IdEstatusAtencion = ".$rsEscalamiento['idEstado'];
        $resultNota = $catalogo->obtenerLista($queryNotas);
        while($rsNota = mysql_fetch_array($resultNota)){
            if($rsEscalamiento['prioridad'] < $rsNota['Prioridad']){
                $updatePrioridad = "UPDATE c_ticket t SET t.Prioridad = (SELECT pt.IdPrioridad from c_prioridadticket pt WHERE pt.Prioridad = ".$rsEscalamiento['prioridad'].")
                        WHERE t.IdTicket = ".$rsNota['IdTicket'];
                echo $updatePrioridad;
                $rsUpdate = $catalogo->obtenerLista($updatePrioridad);
                if($rsUpdate){
                    echo "<br/>Se ha modificado la prioridad del ticket ".$rsNota['IdTicket']." debido a que el escalamiento tenía una prioridad mayor";
                }
            }
            $correos = array();
            $mail = new Mail();
            $mail->setSubject("Atención al ticket ".$rsNota['IdTicket']);
            $mail->setBody("<br/>Ya han pasado ".$rsEscalamiento['tiempoEnvio']." días desde que
                    el ticket <b>".$rsNota['IdTicket']."</b> del cliente ".$rsNota['NombreCliente']." se
                    encuentra en el estado ".$rsEscalamiento['Nombre']."<br/>"."Mensaje: ".$rsEscalamiento['mensaje']);
            if($parametroGlobal->getRegistroById("8")){
                $mail->setFrom($parametroGlobal->getValor());
            }else{
                $mail->setFrom("scg-salida@scgenesis.mx");
            }
            /* Obtenemos los correos a los que le enviaremos la informacion */
            $queryCorreos = "SELECT correo from c_escalamientoCorreo ec WHERE idEscalamiento = ".$rsEscalamiento['idEscalamiento'];
            $resultCorreos = $catalogo->obtenerLista($queryCorreos);
            while($rsCorreo = mysql_fetch_array($resultCorreos)){
                $tipo = substr($rsCorreo['correo'], 0, 2);
                if(strcmp($tipo, "cl") == 0){
                    $queryFinal = "SELECT CorreoElectronicoEnvioFact1, CorreoElectronicoEnvioFact2, CorreoElectronicoEnvioFact3,CorreoElectronicoEnvioFact4
                            from c_cliente WHERE ClaveCliente = ".$rsNotas['ClaveCliente'];
                }else if(strcmp($tipo, "co") == 0){
                    $queryFinal = "SELECT CorreoElectronico from c_contacto WHERE IdTipoContacto = ".substr($rsCorreo['correo'], 2);
                }else if(strcmp($tipo,"us") == 0){
                    $queryFinal = "SELECT correo from c_usuario WHERE idUsuario = ".substr($rsCorreo['correo'], 2);
                }else if(strcmp($tipo, "tf") == 0){
                    $queryFinal = "SELECT u.correo from k_tfscliente ktc 
                        LEFT JOIN c_usuario u ON ktc.IdUsuario = u.IdUsuario 
                        LEFT JOIN c_ticket t ON t.ClaveCliente = ktc.ClaveCliente
                        WHERE t.IdTicket = ".$rsNota['IdTicket'];
                }
                $resultFinal = $catalogo->obtenerLista($queryFinal);
                while($rsFinal = mysql_fetch_array($resultFinal)){
                    if(isset($rsFinal['CorreoElectronicoEnvioFact1']) && $rsFinal['CorreoElectronicoEnvioFact1'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact1'], FILTER_VALIDATE_EMAIL)){
                        array_push($correos, $rsFinal['CorreoElectronicoEnvioFact1']);
                    }
                    if(isset($rsFinal['CorreoElectronicoEnvioFact2']) && $rsFinal['CorreoElectronicoEnvioFact2'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact2'], FILTER_VALIDATE_EMAIL)){
                        array_push($correos, $rsFinal['CorreoElectronicoEnvioFact2']);
                    }
                    if(isset($rsFinal['CorreoElectronicoEnvioFact3']) && $rsFinal['CorreoElectronicoEnvioFact3'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact3'], FILTER_VALIDATE_EMAIL)){
                        array_push($correos, $rsFinal['CorreoElectronicoEnvioFact3']);
                    }
                    if(isset($rsFinal['CorreoElectronicoEnvioFact4']) && $rsFinal['CorreoElectronicoEnvioFact4'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact4'], FILTER_VALIDATE_EMAIL)){
                        array_push($correos, $rsFinal['CorreoElectronicoEnvioFact4']);
                    }
                    if(isset($rsFinal['CorreoElectronico']) && $rsFinal['CorreoElectronico'] != "" && filter_var($rsFinal['CorreoElectronico'], FILTER_VALIDATE_EMAIL)){
                        array_push($correos, $rsFinal['CorreoElectronico']);
                    }
                    if(isset($rsFinal['correo']) && $rsFinal['correo'] != "" && filter_var($rsFinal['correo'], FILTER_VALIDATE_EMAIL)){
                        array_push($correos, $rsFinal['correo']);
                    }
                }
            }
            foreach ($correos as $value) {/*Lo mandamos a los correos de los usuarios de cuentas por cobrar*/        
                $mail->setTo($value);
                if ($mail->enviarMail() == "1") {
                    echo "<br/>Un correo fue enviado por escalamientos a $value.";
                } else {
                    echo "<br/>Error: No se pudo enviar el correo a $value.";
                }
            }
        }
    }
}
?>


<?php
header('Content-Type: text/html; charset=utf-8');
include_once("WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("WEB-INF/Classes/Catalogo.class.php");
include_once("WEB-INF/Classes/Mail.class.php");
include_once("WEB-INF/Classes/ParametroGlobal.class.php");
include_once("WEB-INF/Classes/ConexionMultiBD.class.php");

set_time_limit (0);
ini_set("memory_limit","256M");

$con = new ConexionMultiBD();         
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();
while($rs_multi = mysql_fetch_array($result_bases)){
    echo "<br/><br/>Procesando empresa ".$rs_multi['nombre_empresa'];
    $empresa = $rs_multi['id_empresa'];
    $catalogo = new CatalogoFacturacion();
    $catalogo->setEmpresa($empresa);
    $catalogo_aux = new Catalogo();
    $catalogo_aux->setEmpresa($empresa);
    $parametroGlobal = new ParametroGlobal();
    $parametroGlobal->setEmpresa($empresa);
    $mail = new Mail();
    $mail->setEmpresa($empresa);

    $correos = array();
    /*Correos para el cron por default*/
    $result = $catalogo_aux->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5;");
    while($rs = mysql_fetch_array($result)){
        if(isset($rs['correo']) && $rs['correo']!="" && filter_var($rs['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
            array_push($correos, $rs['correo']);
        }
    }
    /*Correos de usuarios con el puesto de cuentas por cobrar*/
    $consulta = "SELECT CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS cxc, correo 
    FROM c_usuario AS u WHERE u.IdPuesto = 15;";
    $result = $catalogo_aux->obtenerLista($consulta);
    while($rs = mysql_fetch_array($result)){
        if(isset($rs['correo']) && $rs['correo']!="" && filter_var($rs['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
            array_push($correos, $rs['correo']);
        }
    }

    /* Obtenemos las nuevas facuras, ndc y cancelaciones de facturas y ndc*/
    $consulta = "SELECT (CASE WHEN !ISNULL(u.IdUsuario) THEN u.IdUsuario ELSE 'NA' END) AS IdUsuario, c.Activo,
    (CASE WHEN !ISNULL(u.IdUsuario) THEN CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) ELSE 'Sin Ejecutivo' END) AS EjecutivoCuenta,
    fn.IdFactura, fn.IdPagoParcial, fn.Estado, f.RFCReceptor, DATE(f.FechaFacturacion) AS FechaFacturacion, f.Folio, f.Total, (SELECT CASE WHEN f.TipoComprobante = 'ingreso' THEN 'Factura' ELSE 'Nota de crédito' END) AS TipoComprobante, f.NombreReceptor,
    DATE(pp.FechaPago) AS FechaPago, pp.ImportePagado, pp.ImportePorPagar,
    (CASE WHEN fn.Estado = 3 THEN 'Pago Parcial' WHEN fn.Estado = 2 THEN 'Cancelación' WHEN fn.Estado = 1 THEN 'Nueva' ELSE null END) Tipo, u.correo
    FROM c_factura_notificacion AS fn
    INNER JOIN c_factura AS f ON f.IdFactura = fn.IdFactura
    LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MAX(ClaveCliente) FROM c_cliente WHERE TRIM(f.RFCReceptor) = TRIM(RFC))
    LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta
    LEFT JOIN c_pagosparciales AS pp ON pp.IdPagoParcial = fn.IdPagoParcial
    WHERE f.Serie <> 'PREF' AND c.IdTipoCliente <> 7 
    ORDER BY u.IdUsuario, TipoComprobante, fn.Estado;";
    $result = $catalogo->obtenerLista($consulta);

    if(mysql_num_rows($result) > 0){    
        if($parametroGlobal->getRegistroById("8")){
            $mail->setFrom($parametroGlobal->getValor());
        }else{
            $mail->setFrom("scg-salida@scgenesis.mx");
        }
        /*Se van guardando la informacion por cada tabla para hacer algo general y mandarlo a cxc y a los predeterminados*/
        $tabla_nueva_factura = "<table><thead><tr><th>Folio</th><th>Fecha Facturación</th><th>Cliente</th><th>Total</th><th>Ejecutivo</th></tr></thead><tbody>";
        $tabla_nueva_nota = "<table><thead><tr><th>Folio</th><th>Fecha Facturación</th><th>Cliente</th><th>Total</th><th>Ejecutivo</th></tr></thead><tbody>";
        $tabla_factura_cancelada = "<table><thead><tr><th>Folio</th><th>Fecha Facturación</th><th>Cliente</th><th>Total</th><th>Ejecutivo</th></tr></thead><tbody>";
        $tabla_nota_cancelada = "<table><thead><tr><th>Folio</th><th>Fecha Facturación</th><th>Cliente</th><th>Total</th><th>Ejecutivo</th></tr></thead><tbody>";
        $tabla_pagos = "<table><thead><tr><th>Folio</th><th>Fecha Pago</th><th>Cliente</th><th>Pago Realizado</th><th>Ejecutivo</th></tr></thead><tbody>";

        /*Variables para saber cuando hay un cambio en ejecutivo de cuenta o tipo de dato (nuevo, cancelado o pago)*/
        $message_ejecutivo = "";
        $id_ejecutivo_anterior = "";
        $ejecutivo_anterior = "";
        $correo_anterior = "";
        $tipo_anterior = "";

        while ($rs = mysql_fetch_array($result)) {//Recorremos todos los resultados
            if($id_ejecutivo_anterior!= "" && $id_ejecutivo_anterior!=$rs['IdUsuario']){/*Se termino de procesar un ejecutivo de cuenta y se iniciara con uno nuevo*/
                if($id_ejecutivo_anterior!="NA"){
                    $mail->setSubject("Resumen de facturación del ejecutivo $ejecutivo_anterior");
                    $mail->setBody($message_ejecutivo);        
                    if(isset($correo_anterior) && $correo_anterior!="" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                        /*$mail->setTo($correo_anterior);
                        if ($mail->enviarMail() == "1") {
                            echo "<br/>Un correo fue enviado de resumen de facturas a $correo_anterior.";
                        } else {
                            echo "<br/>Error: No se pudo enviar el correo a $correo_anterior.";
                        }*/
                    }
                }else{
                    $message_ejecutivo_extra = "<br/><b>Los datos anteriores no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                    $mail->setSubject("Resumen de facturación sin ejecutivo");
                    $mail->setBody($message_ejecutivo_extra. " ".$message_ejecutivo);
                }
                
                /*Correos para el cron por default*/
                $result2 = $catalogo_aux->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5;");
                while($rs2 = mysql_fetch_array($result2)){
                    if(isset($rs2['correo']) && $rs2['correo']!="" && filter_var($rs2['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                        /*$mail->setTo($rs2['correo']);
                        $mail->enviarMail();*/
                    }
                }
                $message_ejecutivo = "";
                $tipo_anterior = "";
            }                

            if($tipo_anterior != $rs['Estado']."_".$rs['TipoComprobante']){//El procesamiento cambia dependiendo si se empieza con nuevas facturas, cancelaciones o pagos
                if($message_ejecutivo!=""){//Cerramos una tabla si viene abierta
                    $message_ejecutivo.="</tbody></table>";
                }

                switch ($rs['Estado']){
                    case "1":
                        if($rs['TipoComprobante'] == "Factura"){
                            $message_ejecutivo .= "<br/><br/><b>Nuevas facturas: </b><br/>";                        
                        }else{
                            $message_ejecutivo .= "<br/><br/><b>Nuevas notas de crédito: </b>";                        
                        }
                        $message_ejecutivo .= "<table><thead><tr><th>Folio</th><th>Fecha Facturación</th><th>Cliente</th><th>Total</th></tr></thead><tbody>";
                        break;
                    case "2":
                        if($rs['TipoComprobante'] == "Factura"){
                            $message_ejecutivo .= "<br/><br/><b>Facturas canceladas: </b>";
                        }else{
                            $message_ejecutivo .= "<br/><br/><b>Notas de crédito canceladas: </b>";
                        }
                        $message_ejecutivo .= "<table><thead><tr><th>Folio</th><th>Fecha Facturación</th><th>Cliente</th><th>Total</th></tr></thead><tbody>";
                        break;                    
                    case "3":
                        $message_ejecutivo .= "<br/><br/><b>Nuevos pagos: </b>";
                        $message_ejecutivo .= "<table><thead><tr><th>Folio</th><th>Fecha Pago</th><th>Cliente</th><th>Monto</th></tr></thead><tbody>";
                        break;
                    default :
                        break;
                }
            }

            switch ($rs['Estado']){
                case "1":               
                    $nombre_receptor = $rs['NombreReceptor'];
                    if($rs['Activo'] == "1"){
                        $message_ejecutivo .= "<tr><td>".$rs['Folio']."</td><td>".$catalogo_aux->formatoFechaReportes($rs['FechaFacturacion']) ."</td>
                            <td>".$nombre_receptor."</td><td>$ ".$rs['Total']."</td></tr>";
                    }else{
                        $nombre_receptor .= " (no se envío al ejecutivo porque el cliente esta inactivo)";
                    }
                    //Mensaje de resumen global
                    if($rs['TipoComprobante'] == "Factura"){
                            $tabla_nueva_factura.= "<tr><td>".$rs['Folio']."</td><td>".$catalogo_aux->formatoFechaReportes($rs['FechaFacturacion']) ."</td>
                        <td>".$nombre_receptor."</td><td>$ ".$rs['Total']."</td><td>".$rs['EjecutivoCuenta']."</td></tr>"; 
                    }else{
                        $tabla_nueva_nota.= "<tr><td>".$rs['Folio']."</td><td>".$catalogo_aux->formatoFechaReportes($rs['FechaFacturacion']) ."</td>
                        <td>".$nombre_receptor."</td><td>$ ".$rs['Total']."</td><td>".$rs['EjecutivoCuenta']."</td></tr>"; 
                    }                
                    break;
                case "2":      
                    $nombre_receptor = $rs['NombreReceptor'];
                    if($rs['Activo'] == "1"){
                        $message_ejecutivo .= "<tr><td>".$rs['Folio']."</td><td>".$catalogo_aux->formatoFechaReportes($rs['FechaFacturacion']) ."</td>
                            <td>".$nombre_receptor."</td><td>$ ".$rs['Total']."</td></tr>";                    
                    }else{
                        $nombre_receptor .= " (no se envío al ejecutivo porque el cliente esta inactivo)";
                    }
                    //Mensaje de resumen global
                    if($rs['TipoComprobante'] == "Factura"){
                        $tabla_factura_cancelada.= "<tr><td>".$rs['Folio']."</td><td>".$catalogo_aux->formatoFechaReportes($rs['FechaFacturacion']) ."</td>
                        <td>".$nombre_receptor."</td><td>$ ".$rs['Total']."</td><td>".$rs['EjecutivoCuenta']."</td></tr>"; 
                    }else{
                        $tabla_nota_cancelada.= "<tr><td>".$rs['Folio']."</td><td>".$catalogo_aux->formatoFechaReportes($rs['FechaFacturacion']) ."</td>
                        <td>".$nombre_receptor."</td><td>$ ".$rs['Total']."</td><td>".$rs['EjecutivoCuenta']."</td></tr>"; 
                    }
                    break;                    
                case "3":       
                    $nombre_receptor = $rs['NombreReceptor'];
                    if($rs['Activo'] == "1"){
                        $message_ejecutivo .= "<tr><td>".$rs['Folio']."</td><td>".$catalogo_aux->formatoFechaReportes($rs['FechaPago']) ."</td>
                            <td>".$nombre_receptor."</td><td>$ ".$rs['ImportePagado']."</td></tr>";
                    }else{
                        $nombre_receptor .= " (no se envío al ejecutivo porque el cliente esta inactivo)";
                    }
                    //Mensaje de resumen global
                    $tabla_pagos.= "<tr><td>".$rs['Folio']."</td><td>".$catalogo_aux->formatoFechaReportes($rs['FechaPago']) ."</td>
                        <td>".$nombre_receptor."</td><td>$ ".$rs['ImportePagado']."</td><td>".$rs['EjecutivoCuenta']."</td></tr>";
                    break;
                default :
                    break;
            }
            if(isset($rs['IdUsuario']) && $rs['IdUsuario']!=""){
                $correo_anterior = $rs['correo'];
                $id_ejecutivo_anterior = $rs['IdUsuario'];
                $ejecutivo_anterior = $rs['EjecutivoCuenta'];
            }else{
                $correo_anterior = "NA";
                $id_ejecutivo_anterior = "NA";
                $ejecutivo_anterior = "NA";
            }
            $tipo_anterior = $rs['Estado']."_".$rs['TipoComprobante'];
        }

        if($id_ejecutivo_anterior!= ""){/*Se termino de procesar el ultimo ejecutivo de cuenta y se iniciara con el resumen general*/
            if($id_ejecutivo_anterior!="NA"){
                $mail->setSubject("Resumen de facturación del ejecutivo $ejecutivo_anterior");
                $mail->setBody($message_ejecutivo);        
                if(isset($correo_anterior) && $correo_anterior!="" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                    /*$mail->setTo($correo_anterior);
                    if ($mail->enviarMail() == "1") {
                        echo "<br/>Un correo fue enviado de resumen de facturas a $correo_anterior.";
                    } else {
                        echo "<br/>Error: No se pudo enviar el correo a $correo_anterior.";
                    }*/
                }
            }else{
                $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                $mail->setSubject("Resumen de facturación sin ejecutivo");
                $mail->setBody($message_ejecutivo_extra. " ".$message_ejecutivo);
            }

            /*Correos para el cron por default*/
            $result2 = $catalogo_aux->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5;");
            while($rs2 = mysql_fetch_array($result2)){
                if(isset($rs2['correo']) && $rs2['correo']!="" && filter_var($rs2['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                    /*$mail->setTo($rs2['correo']);
                    $mail->enviarMail();*/
                }
            }
            $message_ejecutivo = "";
            $tipo_anterior = "";
        }
        /*********************  RESUMEN GLOBAL  ****************************/
        $tabla_nueva_factura.="</thead></table>";
        $tabla_nueva_nota.="</thead></table>";
        $tabla_factura_cancelada.="</thead></table>";
        $tabla_nota_cancelada.="</thead></table>";
        $tabla_pagos.="</thead></table>";
        $mensaje_golbal = "Los movimientos realizados el día de hoy en facturación son los siguientes: ";
        $mensaje_golbal.= "<br/><b>Facturas nuevas: </b><br/>$tabla_nueva_factura";
        $mensaje_golbal.= "<br/><br/><b>Notas de crédito nuevas: </b><br/>$tabla_nueva_nota";
        $mensaje_golbal.= "<br/><b>Facturas canceladas: </b><br/>$tabla_factura_cancelada";
        $mensaje_golbal.= "<br/><br/><b>Notas de crédito canceladas: </b><br/>$tabla_nota_cancelada";
        $mensaje_golbal.= "<br/><br/><b>Pagos realizados: </b><br/>$tabla_pagos";
        $mail->setSubject("Resumen de facturación diario ".$catalogo_aux->formatoFechaReportes(date("Y")."-".date("m")."-".date("d")));
        $mail->setBody($mensaje_golbal);
        foreach ($correos as $value) {/*Lo mandamos a los correos de los usuarios de cuentas por cobrar*/        
            $mail->setTo($value);
            if ($mail->enviarMail() == "1") {
                echo "<br/>Un correo fue enviado por resumen global de facturación a $value.";
            } else {
                echo "<br/>Error: No se pudo enviar el correo a $value.";
            }
        }
    }
    $catalogo->obtenerLista("DELETE FROM c_factura_notificacion");
}
?>

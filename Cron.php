<?php
set_time_limit (0);
header('Content-Type: text/html; charset=utf-8');
include_once("WEB-INF/Classes/Catalogo.class.php");
include_once("WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("WEB-INF/Classes/Mail.class.php");
include_once("WEB-INF/Classes/ParametroGlobal.class.php");
include_once("WEB-INF/Classes/Parametros.class.php");
include_once("WEB-INF/Classes/ConexionMultiBD.class.php");

$con = new ConexionMultiBD();         
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();
while($rs_multi = mysql_fetch_array($result_bases)){
    echo "<br/><br/>Procesando empresa ".$rs_multi['nombre_empresa']."<br/>";
    $empresa = $rs_multi['id_empresa'];
    
    //$_GET['uguid']=$empresa;
    /**************************************************** Ejecutar morosos ****************************************************/    
    //include "WEB-INF/Controllers/Controler_ClienteExportacion.php";
    /**************************************************** Ejecutar copiado de la tabla de clientes y usuarios ****************************************************/
    $_GET['uguid']=$empresa;
    echo "<br/>";
    include "WEB-INF/Controllers/Controler_ActualizarClientesUsuarios.php";
    /**************************************************** Fecha de regreso de equipos ****************************************************/
    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);
    $catalogoFacturacion = new CatalogoFacturacion();
    $catalogoFacturacion->setEmpresa($empresa);
    $mail = new Mail();
    $mail->setEmpresa($empresa);
    $parametroGlobal = new ParametroGlobal();
    $parametroGlobal->setEmpresa($empresa);
    $parametros = new Parametros();
    $parametros->setEmpresa($empresa);

    set_time_limit(0);
    if($parametroGlobal->getRegistroById("8")){
        $mail->setFrom($parametroGlobal->getValor());
    }else{
        $mail->setFrom("scg-salida@scgenesis.mx");
    }
    $consulta = "SELECT s.id_solicitud, s.fecha_regreso, GROUP_CONCAT(b.NoSerie) AS Series,
            (CASE WHEN !ISNULL(cc.ClaveCentroCosto) THEN cc.ClaveCentroCosto ELSE cc2.ClaveCentroCosto END) AS ClaveCentroCosto,
            (CASE WHEN !ISNULL(cc.ClaveCentroCosto) THEN cc.Nombre ELSE cc2.Nombre END) AS Localidad,
            (CASE WHEN !ISNULL(c.ClaveCliente) THEN c.ClaveCliente ELSE c2.ClaveCliente END) AS ClaveCliente,
            (CASE WHEN !ISNULL(c.ClaveCliente) THEN c.NombreRazonSocial ELSE c2.NombreRazonSocial END) AS Cliente,
            (CASE WHEN !ISNULL(u.IdUsuario) THEN CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) ELSE CONCAT(u2.Nombre,' ',u2.ApellidoPaterno,' ',u2.ApellidoMaterno) END) AS EjecutivoCuenta,
            (CASE WHEN !ISNULL(u.IdUsuario) THEN u.correo ELSE u2.correo END) AS correo
            FROM c_solicitud AS s
            INNER JOIN c_bitacora AS b ON s.id_solicitud = b.id_solicitud
            INNER JOIN c_inventarioequipo AS cie ON cie.NoSerie = b.NoSerie
            LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cie.IdKserviciogimgfa
            LEFT JOIN c_centrocosto AS cc ON ks.ClaveCentroCosto = cc.ClaveCentroCosto
            LEFT JOIN c_cliente AS c ON cc.ClaveCliente = c.ClaveCliente
            LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta
            LEFT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = cie.IdAnexoClienteCC
            LEFT JOIN c_centrocosto AS cc2 ON kacc.CveEspClienteCC = cc2.ClaveCentroCosto
            LEFT JOIN c_cliente AS c2 ON cc2.ClaveCliente = c2.ClaveCliente
            LEFT JOIN c_usuario AS u2 ON u2.IdUsuario = c2.EjecutivoCuenta
            WHERE s.id_tiposolicitud IN(4,5) AND s.fecha_regreso = DATE(NOW())
            GROUP BY id_solicitud, EjecutivoCuenta, Cliente, Localidad
            ORDER BY EjecutivoCuenta, Cliente, Localidad;";
    $result = $catalogo->obtenerLista($consulta);
    if(mysql_num_rows($result) > 0){
        $hoy = $catalogo->formatoFechaReportes(date("Y")."-".date("m")."-".date("d"));
        $message = "Los siguientes equipos han terminado hoy $hoy su periodo de préstamo: <br/><br/>";
        $message .= "<table border=\"1\"><thead><tr><th>Equipos</th><th>Solicitud</th><th>Cliente</th><th>Localidad</th></tr></thead><tbody>";
        $correo_anterior = "";
        $fecha_regreso = "";
        while($rs = mysql_fetch_array($result)){
            if($correo_anterior!="" && $rs['correo']!=$correo_anterior){/*Mandar mail porque ya va a iniciar a procesarse un nuevo vendedor*/
                $message.="</tbody></table>";//Cerramos la tabla que se fue generando para el correo
                $mail->setSubject("Fin de equipos en demostración: $hoy");
                $mail->setBody($message);
                if($correo_anterior != "NA"){
                    if(isset($correo_anterior) && $correo_anterior!="" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                        $mail->setTo($correo_anterior);
                        if ($mail->enviarMail() == "1") {
                            echo "Un correo fue enviado de notificacion de fin de préstamo a $correo_anterior.";
                        } else {
                            echo "<br/>Error: No se pudo enviar el correo a $correo_anterior.";
                        }
                    }else{
                        $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                        $mail->setBody($message_ejecutivo_extra." ".$message);
                    }
                }else{
                    $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                    $mail->setBody($message_ejecutivo_extra." ".$message);
                }

                /*Correos para el cron por default*/
                $result2 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5;");
                while($rs2 = mysql_fetch_array($result2)){
                    if(isset($rs2['correo']) && $rs2['correo']!="" && filter_var($rs2['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                        $mail->setTo($rs2['correo']);
                        $mail->enviarMail();
                    }
                }

                $message = "Los siguientes equipos han terminado hoy ".  date("d")."/".date("m")."/".date("Y")." su periodo de préstamo: <br/><br/>";
                $message .= "<table border=\"1\"><thead><tr><th>Equipos</th><th>Solicitud</th><th>Cliente</th><th>Localidad</th></tr></thead><tbody>";//Iniciamos una nueva tabla
            }
            $message.= "<tr><td>".$rs['Series']."</td><td>".$rs['id_solicitud']."</td><td>".$rs['Cliente']."</td><td>".$rs['Localidad']."</td><tr>";
            $fecha_regreso = $rs['fecha_regreso'];
            if(isset($rs['correo']) && $rs['correo']!=""){
                $correo_anterior = $rs['correo'];
            }else{
                $correo_anterior = "NA";
            }
        }
        if($correo_anterior!=""){
            /*Cerramos el ultimo correo a enviar*/
            $message.="</tbody></table>";//Cerramos la tabla que se fue generando para el correo
            $mail->setSubject("Fin de equipos en demostración: $hoy");
            $mail->setBody($message);
            if($correo_anterior != "NA"){
                if(isset($correo_anterior) && $correo_anterior!="" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                    $mail->setTo($correo_anterior);
                    if ($mail->enviarMail() == "1") {
                        echo "Un correo fue enviado de notificacion de fin de préstamo a $correo_anterior.";
                    } else {
                        echo "<br/>Error: No se pudo enviar el correo a $correo_anterior.";
                    }
                }else{
                    $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                    $mail->setBody($message_ejecutivo_extra." ".$message);
                }
            }else{
                $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                $mail->setBody($message_ejecutivo_extra." ".$message);
            }
        }    
        /*Correos para el cron por default*/
        $result2 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5;");
        while($rs2 = mysql_fetch_array($result2)){
            if(isset($rs2['correo']) && $rs2['correo']!="" && filter_var($rs2['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                $mail->setTo($rs2['correo']);
                $mail->enviarMail();
            }
        }
    }
    /**************************************************** Avisar cuando el contrato vence el proximo mes ****************************************************/
    $meses_vencimiento = 1;
    $consulta = "SELECT ctt.NoContrato, ctt.FechaInicio, DATE(ctt.FechaTermino) AS FechaTermino, c.NombreRazonSocial,  u.correo
    FROM `c_contrato` AS ctt 
    INNER JOIN c_cliente AS c ON c.ClaveCliente = ctt.ClaveCliente
    INNER JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta
    WHERE c.Activo = 1 AND DATE(ctt.FechaTermino) = DATE(DATE_ADD(NOW(),INTERVAL $meses_vencimiento MONTH));";
    $result = $catalogo->obtenerLista($consulta);
    if(mysql_num_rows($result) > 0){
        while($rs = mysql_fetch_array($result)){        
            $message = "El contrato <b>".$rs['NoContrato']."</b> del cliente <b>".$rs['NombreRazonSocial']."</b> terminará próximamente, el día ".$catalogo->formatoFechaReportes($rs['FechaTermino']);
            $mail->setSubject("Fin de contrato: ".$rs['NoContrato']." del cliente ".$rs['NombreRazonSocial']);
            $mail->setBody($message);        
            if(isset($rs['correo']) && $rs['correo']!="" && filter_var($rs['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                $mail->setTo($rs['correo']);
                if ($mail->enviarMail() == "1") {
                    echo "<br/>Un correo fue enviado de notificacion de fin de contrato a $correo_anterior.";
                } else {
                    echo "<br/>Error: No se pudo enviar el correo a $correo_anterior.";
                }
            }else{
                $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                $mail->setBody($message_ejecutivo_extra." ".$message);
            }
            /*Correos para el cron por default*/
            $result2 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5;");
            while($rs2 = mysql_fetch_array($result2)){
                if(isset($rs2['correo']) && $rs2['correo']!="" && filter_var($rs2['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                    $mail->setTo($rs2['correo']);
                    $mail->enviarMail();
                }
            }
        }
    }
    /**************************************************** Avisar cuando una factura cumple dos meses de adeudo ****************************************************/
    $meses_adeudo = 2;
    $consulta = "SELECT DISTINCT(f.Folio) AS folio,TRIM(f.RFCReceptor) AS RFCReceptor,f.NombreReceptor, f.Total, DATE(f.FechaFacturacion) AS FechaFacturacion,  
    (CASE WHEN !ISNULL(u.IdUsuario) THEN u.IdUsuario ELSE 'NA' END) AS IdUsuario, 
    (CASE WHEN !ISNULL(u.IdUsuario) THEN CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) ELSE 'Sin Ejecutivo' END) AS ejecutivo, 
    u.correo 
    FROM c_factura AS f
    LEFT JOIN c_cliente AS c ON c.RFC = TRIM(f.RFCReceptor)
    LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta
    WHERE f.Serie<>'PREF' AND f.PendienteCancelar = 0 AND f.TipoComprobante = 'ingreso' AND f.EstadoFactura<>0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura<>3) AND f.FacturaPagada = 0 
    AND DATE(f.FechaFacturacion) = DATE(DATE_SUB(NOW(),INTERVAL $meses_adeudo MONTH)) 
    ORDER BY u.IdUsuario, f.FechaFacturacion DESC;";
    $result = $catalogoFacturacion->obtenerLista($consulta);
    if(mysql_num_rows($result) > 0){
        $usuario_anterior = "";
        $correo_anterior = "";
        $ejecutivo = "";
        $message = "";
        while($rs = mysql_fetch_array($result)){
            if($usuario_anterior != "" && $usuario_anterior != $rs['IdUsuario']){
                $mail->setSubject("Adeudo de facturas del ejecutivo ".$ejecutivo);
                $mail->setBody($message);
                if($usuario_anterior!="NA"){
                    if(isset($correo_anterior) && $correo_anterior!="" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                        $mail->setTo($correo_anterior);
                        if ($mail->enviarMail() == "1") {
                            echo "<br/>Un correo fue enviado de notificacion de adeudo de factura a ".$correo_anterior;
                        } else {
                            echo "<br/>Error: No se pudo enviar el correo a ".$correo_anterior.".";
                        }
                    }else{
                        $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                        $mail->setSubject("Adeudo de facturas del ejecutivo ".$ejecutivo);
                        $mail->setBody($message_ejecutivo_extra. " ".$message);
                    }
                }else{
                    $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                    $mail->setSubject("Adeudo de facturas sin ejecutivo");
                    $mail->setBody($message_ejecutivo_extra. " ".$message);
                }
                /*Correos para el cron por default*/
                $result2 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5;");
                while($rs2 = mysql_fetch_array($result2)){
                    if(isset($rs2['correo']) && $rs2['correo']!="" && filter_var($rs2['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                        $mail->setTo($rs2['correo']);
                        $mail->enviarMail();
                    }
                }
                $message = "";
            }
            $message .= "<br/>La factura <b>".$rs['folio']."</b> del cliente <b>".$rs['NombreReceptor']."</b> facturada el día ".$catalogo->formatoFechaReportes($rs['FechaFacturacion'])." con un monto de <b>$".$rs['Total']."</b> cumple hoy 2 meses de adeudo";
            if(isset($rs['IdUsuario']) && $rs['IdUsuario']!="" && $rs['IdUsuario']!="NA"){
                $usuario_anterior = $rs['IdUsuario'];
                $correo_anterior = $rs['correo'];
                $ejecutivo = $rs['ejecutivo'];
            }else{
                $usuario_anterior = "NA";
                $correo_anterior = "NA";
                $ejecutivo = "NA";
            }
        }
        if($usuario_anterior != ""){
            $mail->setSubject("Adeudo de facturas del ejecutivo $ejecutivo");
            $mail->setBody($message);
            if($usuario_anterior!="NA"){
                if(isset($correo_anterior) && $correo_anterior!="" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                    $mail->setTo($correo_anterior);
                    if ($mail->enviarMail() == "1") {
                        echo "<br/>Un correo fue enviado de notificacion de adeudo de factura a ".$correo_anterior;
                    } else {
                        echo "<br/>Error: No se pudo enviar el correo a ".$correo_anterior.".";
                    }
                }else{
                    $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                    $mail->setSubject("Adeudo de facturas del ejecutivo ".$ejecutivo);
                    $mail->setBody($message_ejecutivo_extra. " ".$message);
                }
            }else{
                $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                $mail->setSubject("Adeudo de facturas sin ejecutivo");
                $mail->setBody($message_ejecutivo_extra. " ".$message);
            }
            /*Correos para el cron por default*/
            $result2 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5;");
            while($rs2 = mysql_fetch_array($result2)){
                if(isset($rs2['correo']) && $rs2['correo']!="" && filter_var($rs2['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                    $mail->setTo($rs2['correo']);
                    $mail->enviarMail();
                }
            }
            $message = "";
        }
    }
    /**************************************************** Avisar cuando una factura pendiente por cancelar cumple más de 72 horas ****************************************************/
    $dias_pendiente = 3;
    $consulta = "SELECT f.IdFactura, f.Folio, DATE(f.FechaFacturacion) AS FechaFacturacion, f.NombreReceptor, f.RFCReceptor, 
    (CASE WHEN !ISNULL(u.IdUsuario) THEN CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) ELSE 'Sin Ejecutivo' END) AS EjecutivoCuenta, u.correo,
    (CASE WHEN !ISNULL(u.IdUsuario) THEN u.IdUsuario ELSE 'NA' END) AS IdUsuario
    FROM `c_factura` AS f
    LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MAX(ClaveCliente) FROM c_cliente WHERE TRIM(f.RFCReceptor) = TRIM(RFC))
    LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta
    WHERE f.PendienteCancelar = 1 AND DATEDIFF(NOW(),f.FechaFacturacion) >= $dias_pendiente
    ORDER BY u.IdUsuario, f.NombreReceptor;";
    $result = $catalogoFacturacion->obtenerLista($consulta);
    if(mysql_num_rows($result) > 0){
        $usuario_anterior = "";
        $correo_anterior = "";
        $ejecutivo = "";
        $message = "";
        while($rs = mysql_fetch_array($result)){
            if($usuario_anterior != "" && $usuario_anterior != $rs['IdUsuario']){
                $mail->setSubject("Facturas pendientes de cancelar del ejecutivo ".$ejecutivo);
                $mail->setBody($message);
                if($usuario_anterior!="NA"){
                    if(isset($correo_anterior) && $correo_anterior!="" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                        $mail->setTo($correo_anterior);
                        /*if ($mail->enviarMail() == "1") {
                            echo "<br/>Un correo fue enviado de facturas pendientes a ".$correo_anterior;
                        } else {
                            echo "<br/>Error: No se pudo enviar el correo a ".$correo_anterior.".";
                        }*/
                    }else{
                        $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                        $mail->setSubject("Facturas pendientes de cancelar del ejecutivo $ejecutivo");
                        $mail->setBody($message_ejecutivo_extra. " ".$message);
                    }
                }else{
                    $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                    $mail->setSubject("Facturas pendientes de cancelar sin ejecutivo");
                    $mail->setBody($message_ejecutivo_extra. " ".$message);
                }
                /*Correos para el cron por default*/
                $result2 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5
                UNION
                SELECT correo FROM c_usuario WHERE IdPuesto = 13 AND Activo = 1;");
                while($rs2 = mysql_fetch_array($result2)){
                    if(isset($rs2['correo']) && $rs2['correo']!="" && filter_var($rs2['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                        $mail->setTo($rs2['correo']);
                        $mail->enviarMail();
                    }
                }
                $message = "";
            }
            $message .= "<br/>La factura PENDIENTE POR CANCELAR con folio <b>".$rs['Folio']."</b> del cliente <b>".$rs['NombreReceptor']."</b> facturada el día ".$catalogo->formatoFechaReportes($rs['FechaFacturacion'])." cumple $dias_pendiente o más días desde su fecha de facturación, ya puede ser cancelada ante el SAT";
            if(isset($rs['IdUsuario']) && $rs['IdUsuario']!="" && $rs['IdUsuario']!="NA"){
                $correo_anterior = $rs['correo'];
                $usuario_anterior = $rs['IdUsuario'];
                $ejecutivo = $rs['EjecutivoCuenta'];
            }else{
                $correo_anterior = "NA";
                $usuario_anterior = "NA";
                $ejecutivo = "NA";
            }

        }
        if($usuario_anterior != ""){
            $mail->setSubject("Facturas pendientes de cancelar del ejecutivo ".$ejecutivo);
            $mail->setBody($message);
            if($usuario_anterior!="NA"){
                if(isset($correo_anterior) && $correo_anterior!="" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                    /*$mail->setTo($correo_anterior);
                    if ($mail->enviarMail() == "1") {
                        echo "<br/>Un correo fue enviado de facturas pendientes a ".$correo_anterior;
                    } else {
                        echo "<br/>Error: No se pudo enviar el correo a ".$correo_anterior.".";
                    }*/
                }else{
                    $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                    $mail->setSubject("Facturas pendientes de cancelar del ejecutivo $ejecutivo");
                    $mail->setBody($message_ejecutivo_extra. " ".$message);
                }
            }else{
                $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                $mail->setSubject("Facturas pendientes de cancelar sin ejecutivo");
                $mail->setBody($message_ejecutivo_extra. " ".$message);
            }
            /*Correos para el cron por default y puesto de facturacion*/
            $result2 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5
            UNION
            SELECT correo FROM c_usuario WHERE IdPuesto = 13 AND Activo = 1;");
            while($rs2 = mysql_fetch_array($result2)){
                if(isset($rs2['correo']) && $rs2['correo']!="" && filter_var($rs2['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                    $mail->setTo($rs2['correo']);
                    $mail->enviarMail();
                }
            }
            $message = "";
        }
    }
    /*******************************    Avisar cuando los timbres disponibles están por terminarse **************************************/
    if($parametros->getRegistroById(30)){
        $folios_restantes = $parametros->getValor();
    }else{
        $folios_restantes = 100;
    }
    
    $consulta = "SELECT IdFolio,dfe.RazonSocial,RFCEmisor,UltimoFolio, FolioFinal,(FolioFinal - UltimoFolio) AS Restantes FROM `c_folio` AS f
        LEFT JOIN c_datosfacturacionempresa AS dfe ON dfe.RFC = f.RFCEmisor
        HAVING Restantes <= $folios_restantes;";
    
    $result = $catalogoFacturacion->obtenerLista($consulta);
    if(mysql_num_rows($result) > 0){
        $message = "Los folios restantes de las siguientes empresas están por agotarse: "
                . "<table><thead><tr><td>Empresa</td><td>Último folio</td><td>Folio final</td><td>Folios restantes</td></tr></thead>";
        while($rs = mysql_fetch_array($result)){
            $message .= "<tr><td>".$rs['RFCEmisor']." / ".$rs['RazonSocial']."</td><td>".$rs['UltimoFolio']."</td><td>".$rs['FolioFinal']."</td><td>".$rs['Restantes']."</td></tr>";
        }
        $message .= "</table>";
         /*Correos para el cron por default*/
        $result2 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 18;");
        $correos = array();
        while($rs2 = mysql_fetch_array($result2)){
            array_push($correos, $rs2['correo']);
        }
        $catalogo->enviarCorreo("Alarma de timbres disponibles", $correos, $message, true);
    }
}
?>
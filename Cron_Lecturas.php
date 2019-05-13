<?php
header('Content-Type: text/html; charset=utf-8');
include_once("WEB-INF/Classes/Catalogo.class.php");
include_once("WEB-INF/Classes/Mail.class.php");
include_once("WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("WEB-INF/Classes/ParametroGlobal.class.php");
include_once("WEB-INF/Classes/ConexionMultiBD.class.php");

$con = new ConexionMultiBD();         
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();
while($rs_multi = mysql_fetch_array($result_bases)){
    echo "<br/><br/>Procesando empresa ".$rs_multi['nombre_empresa'];
    $empresa = $rs_multi['id_empresa'];
    $catalogo_aux = new Catalogo();
    $catalogo_aux->setEmpresa($empresa);
    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);
    $equipo = new EquipoCaracteristicasFormatoServicio();
    $equipo->setEmpresa($empresa);
    $parametroGlobal = new ParametroGlobal();
    $parametroGlobal->setEmpresa($empresa);

    $correos = array();
    /*Correos para el cron por default*/
    $result = $catalogo_aux->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5;");
    while($rs = mysql_fetch_array($result)){
        if(isset($rs['correo']) && $rs['correo']!="" && filter_var($rs['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
            array_push($correos, $rs['correo']);
        }
    }
    /*Correos de usuarios con el puesto de cxc*/
    $consulta = "SELECT CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS cxc, correo 
    FROM c_usuario AS u WHERE u.IdPuesto = 15;";
    $result = $catalogo_aux->obtenerLista($consulta);
    while($rs = mysql_fetch_array($result)){
        if(isset($rs['correo']) && $rs['correo']!="" && filter_var($rs['correo'], FILTER_VALIDATE_EMAIL)){//Si el correo es valido
            array_push($correos, $rs['correo']);
        }
    }

    /* Obtenemos las nuevas lecturas de corte y modificaciones*/
    $consulta = "SELECT 
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) ELSE CONCAT(u2.Nombre,' ',u2.ApellidoPaterno,' ',u2.ApellidoMaterno) END) AS Usuario,
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN u.correo ELSE u2.correo END) AS correo, 
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN u.IdUsuario ELSE u2.IdUsuario END) AS IdUsuario, 
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN c.ClaveCliente ELSE c2.ClaveCliente END) AS ClaveCliente, 	
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN c.NombreRazonSocial ELSE c2.NombreRazonSocial END) AS NombreRazonSocial, 	
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cg.Nombre ELSE cg2.Nombre END) AS ClienteGrupo, 	
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.Nombre ELSE cc2.Nombre END) AS CentroCostoNombre,             
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.ClaveCentroCosto ELSE ks.ClaveCentroCosto END) AS ClaveCentroCosto,                       
    cinv.NoSerie AS NoSerie, cinv.NoParteEquipo, cinv.Ubicacion,
    c_equipo.Modelo AS Modelo,
    l.IdLectura, DATE(l.Fecha) AS Fecha, l.ContadorBNML, l.ContadorBNPaginas, l.ContadorColorML, l.ContadorColorPaginas,l.UsuarioUltimaModificacion,
    (CASE WHEN leclog.Tipo = 1 THEN 'NUEVA' ELSE 'MODIFICADA' END) AS TipoLectura
    FROM c_lecturalog AS leclog
    INNER JOIN c_lectura AS l ON l.IdLectura = leclog.IdLectura
    LEFT JOIN`c_inventarioequipo` AS cinv ON cinv.NoSerie = l.NoSerie
    LEFT JOIN c_bitacora AS b ON b.NoSerie = cinv.NoSerie
    LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
    RIGHT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = cinv.IdAnexoClienteCC
    RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC
    LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
    LEFT JOIN c_cen_costo AS ccc1 ON cc.id_cr = ccc1.id_cc
    LEFT JOIN c_cen_costo AS ccc2 ON ks.ClaveCentroCosto = ccc2.id_cc
    LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente   
    LEFT JOIN c_clientegrupo AS cg ON cg.ClaveGrupo = c.ClaveGrupo
    LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente         
    LEFT JOIN c_clientegrupo AS cg2 ON cg2.ClaveGrupo = c2.ClaveGrupo
    LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
    LEFT JOIN c_usuario AS u2 ON u2.IdUsuario = c2.EjecutivoCuenta
    LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
    WHERE l.LecturaCorte = 1 AND leclog.Procesado = 0 ORDER BY usuario, TipoLectura DESC, NombreRazonSocial, NoSerie;";

    $result = $catalogo->obtenerLista($consulta);

    if(mysql_num_rows($result) > 0){
        $mail = new Mail();
        if($parametroGlobal->getRegistroById("8")){
            $mail->setFrom($parametroGlobal->getValor());
        }else{
            $mail->setFrom("scg-salida@scgenesis.mx");
        }
        /*Se van guardando la informacion por cada tabla para hacer algo general y mandarlo a cxc y a los predeterminados*/
        $tabla_nueva_lectura = "<table><thead><tr><th>Ejecutivo</th><th>NoSerie</th><th>Modelo</th><th>Cliente</th><th>Localidad</th><th>Contador B/N</th>
            <th>Contador Color</th><th>Periodo</th><th>Capturista</th></tr></thead><tbody>";
        $tabla_editada_lectura = "<table><thead><tr><th>Ejecutivo</th><th>NoSerie</th><th>Modelo</th><th>Cliente</th><th>Localidad</th><th>Contador B/N</th>
            <th>Contador Color</th><th>Periodo</th><th>Capturista</th></tr></thead><tbody>";

        /*Variables para saber cuando hay un cambio en ejecutivo de cuenta o tipo de dato (nuevo, editado)*/
        $message_ejecutivo = "";
        $id_ejecutivo_anterior = "";
        $ejecutivo_anterior = "";
        $correo_anterior = "";
        $tipo_anterior = "";
        while ($rs = mysql_fetch_array($result)) {//Recorremos todos los resultados
            if($id_ejecutivo_anterior!= "" && $id_ejecutivo_anterior!=$rs['IdUsuario']){/*Se termino de procesar un ejecutivo de cuenta y se iniciara con uno nuevo*/
                if($id_ejecutivo_anterior!="NA"){
                    $mail->setSubject("Lecturas de corte capturadas del ejecutivo $ejecutivo_anterior");
                    $mail->setBody($message_ejecutivo);        
                    if(isset($correo_anterior) && $correo_anterior!="" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/                    
                        $mail->setTo($correo_anterior);
                        /*if ($mail->enviarMail() == "1") {
                            echo "<br/>Un correo fue enviado de resumen de facturas a $correo_anterior.";
                        } else {
                            echo "<br/>Error: No se pudo enviar el correo a $correo_anterior.";
                        }*/
                    }
                }else{
                    $message_ejecutivo_extra = "<br/><b>Los datos anteriores no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                    $mail->setSubject("Lecturas de corte capturadas sin ejecutivo");
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

            if($tipo_anterior != $rs['TipoLectura']){//El procesamiento cambia dependiendo si se empieza con nuevas facturas, cancelaciones o pagos
                if($message_ejecutivo!=""){//Cerramos una tabla si viene abierta
                    $message_ejecutivo.="</tbody></table>";
                }

                switch ($rs['TipoLectura']){
                    case "NUEVA":                    
                        $message_ejecutivo .= "<br/><br/><b>Nuevas Lecturas: </b><br/>";   
                        $message_ejecutivo .= "<table><thead><tr><th>NoSerie</th><th>Modelo</th><th>Cliente</th><th>Localidad</th><th>Contador B/N</th>
                            <th>Contador Color</th><th>Periodo</th><th>Capturista</th></tr></thead><tbody>";                    
                        break;
                    case "MODIFICADA":
                        $message_ejecutivo .= "<br/><br/><b>Lecturas Editadas: </b><br/>";   
                        $message_ejecutivo .= "<table><thead><tr><th>NoSerie</th><th>Modelo</th><th>Cliente</th><th>Localidad</th><th>Contador B/N</th>
                            <th>Contador Color</th><th>Periodo</th><th>Capturista</th></tr></thead><tbody>";                    
                        break;                                    
                    default :
                        break;
                }
            }

            switch ($rs['TipoLectura']){
                case "NUEVA":                
                    $message_ejecutivo .= "<tr><td>".$rs['NoSerie']."</td><td>".$rs['Modelo']."</td>"; 
                    $message_ejecutivo.="<td>".$rs['NombreRazonSocial'];
                    if(isset($rs['ClienteGrupo']) && $rs['ClienteGrupo']!=""){
                        $message_ejecutivo .= " (".$rs['ClienteGrupo'].")";
                    }                
                    $message_ejecutivo .="</td><td>".$rs['CentroCostoNombre']."</td>";
                    if($equipo->isFormatoAmplio($rs['NoParteEquipo'])){
                        $message_ejecutivo .= "<td>".$rs['ContadorBNML']."</td><td>".$rs['ContadorColorML']."</td>";
                    }else{
                        $message_ejecutivo .= "<td>".$rs['ContadorBNPaginas']."</td><td>".$rs['ContadorColorPaginas']."</td>";
                    }                    
                    $message_ejecutivo .= "<td>".substr($catalogo_aux->formatoFechaReportes($rs['Fecha']),5)."</td><td>".$rs['UsuarioUltimaModificacion']."</td></tr>";

                    //Mensaje de resumen global
                    $tabla_nueva_lectura .= "<tr><td>".$rs['Usuario']."</td><td>".$rs['NoSerie']."</td><td>".$rs['Modelo']."</td><td>".$rs['NombreRazonSocial']."";
                    if(isset($rs['ClienteGrupo']) && $rs['ClienteGrupo']!=""){
                        $tabla_nueva_lectura .= " (".$rs['ClienteGrupo'].")";
                    }
                    $tabla_nueva_lectura .= "</td><td>".$rs['CentroCostoNombre']."</td>";
                    if($equipo->isFormatoAmplio($rs['NoParteEquipo'])){
                        $tabla_nueva_lectura .= "<td>".$rs['ContadorBNML']."</td><td>".$rs['ContadorColorML']."</td>";
                    }else{
                        $tabla_nueva_lectura .= "<td>".$rs['ContadorBNPaginas']."</td><td>".$rs['ContadorColorPaginas']."</td>";
                    }                    
                    $tabla_nueva_lectura .= "<td>".substr($catalogo_aux->formatoFechaReportes($rs['Fecha']),5)."</td><td>".$rs['UsuarioUltimaModificacion']."</td></tr>";                
                    break;
                case "MODIFICADA":                
                    $message_ejecutivo .= "<tr><td>".$rs['NoSerie']."</td><td>".$rs['Modelo']."</td>"; 
                    $message_ejecutivo.="<td>".$rs['NombreRazonSocial'];
                    if(isset($rs['ClienteGrupo']) && $rs['ClienteGrupo']!=""){
                        $message_ejecutivo .= " (".$rs['ClienteGrupo'].")";
                    }                
                    $message_ejecutivo .= "</td><td>".$rs['CentroCostoNombre']."</td>";
                    if($equipo->isFormatoAmplio($rs['NoParteEquipo'])){
                        $message_ejecutivo .= "<td>".$rs['ContadorBNML']."</td><td>".$rs['ContadorColorML']."</td>";
                    }else{
                        $message_ejecutivo .= "<td>".$rs['ContadorBNPaginas']."</td><td>".$rs['ContadorColorPaginas']."</td>";
                    }                    
                    $message_ejecutivo .= "<td>".substr($catalogo_aux->formatoFechaReportes($rs['Fecha']),5)."</td><td>".$rs['UsuarioUltimaModificacion']."</td></tr>";

                    //Mensaje de resumen global
                    $tabla_editada_lectura .= "<tr><td>".$rs['Usuario']."</td><td>".$rs['NoSerie']."</td><td>".$rs['Modelo']."</td><td>".$rs['NombreRazonSocial']."";
                    if(isset($rs['ClienteGrupo']) && $rs['ClienteGrupo']!=""){
                        $tabla_editada_lectura .= " (".$rs['ClienteGrupo'].")";
                    }
                    $tabla_editada_lectura .= "</td><td>".$rs['CentroCostoNombre']."</td>";
                    if($equipo->isFormatoAmplio($rs['NoParteEquipo'])){
                        $tabla_editada_lectura .= "<td>".$rs['ContadorBNML']."</td><td>".$rs['ContadorColorML']."</td>";
                    }else{
                        $tabla_editada_lectura .= "<td>".$rs['ContadorBNPaginas']."</td><td>".$rs['ContadorColorPaginas']."</td>";
                    }                    
                    $tabla_editada_lectura .= "<td>".substr($catalogo_aux->formatoFechaReportes($rs['Fecha']),5)."</td><td>".$rs['UsuarioUltimaModificacion']."</td></tr>";            
                    break;                                
                default :
                    break;
            }

            if(isset($rs['IdUsuario']) && $rs['IdUsuario']!=""){
                $correo_anterior = $rs['correo'];
                $id_ejecutivo_anterior = $rs['IdUsuario'];
                $ejecutivo_anterior = $rs['Usuario'];
            }else{
                $correo_anterior = "NA";
                $id_ejecutivo_anterior = "NA";
                $ejecutivo_anterior = "NA";
            }
            $tipo_anterior = $rs['TipoLectura'];
        }

        if($id_ejecutivo_anterior!= "" && $id_ejecutivo_anterior!=$rs['IdUsuario']){/*Se termino de procesar un ejecutivo de cuenta y se iniciara con uno nuevo*/
            if($id_ejecutivo_anterior!="NA"){
                $mail->setSubject("Lecturas de corte capturadas del ejecutivo $ejecutivo_anterior");
                $mail->setBody($message_ejecutivo);        
                if(isset($correo_anterior) && $correo_anterior!="" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/                
                    $mail->setTo($correo_anterior);
                    /*if ($mail->enviarMail() == "1") {
                        echo "<br/>Un correo fue enviado de resumen de facturas a $correo_anterior.";
                    } else {
                        echo "<br/>Error: No se pudo enviar el correo a $correo_anterior.";
                    }*/
                }
            }else{
                $message_ejecutivo_extra = "<br/><b>Los datos anteriores no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                $mail->setSubject("Lecturas de corte capturadas sin ejecutivo");
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
        $tabla_nueva_lectura.="</thead></table>";
        $tabla_editada_lectura.="</thead></table>";

        $mensaje_golbal = "Las lecturas de corte capturadas el día de hoy son las siguientes: ";
        $mensaje_golbal.= "<br/><b>Lecturas nuevas: </b><br/>$tabla_nueva_lectura";
        $mensaje_golbal.= "<br/><br/><b>Lecturas editadas: </b><br/>$tabla_editada_lectura";

        $mail->setSubject("Resumen de lecturas de corte diario ".$catalogo_aux->formatoFechaReportes(date("Y")."-".date("m")."-".date("d")));
        $mail->setBody($mensaje_golbal);
        foreach ($correos as $value) {/*Lo mandamos a los correos de los usuarios de cuentas por cobrar*/        
            $mail->setTo($value);
            if ($mail->enviarMail() == "1") {
                echo "<br/>Un correo fue enviado por resumen global de facturación a $value.";
            } else {
                echo "<br/>Error: No se pudo enviar el correo a $value.";
            }
        }
        $catalogo->obtenerLista("UPDATE c_lecturalog SET Procesado = 1;");//Dejamos todas las lecturas como procesadas
    }else{
        echo "No hay lecturas para procesar";
    }
}
?>

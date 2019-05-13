<?php

header('Content-Type: text/html; charset=utf-8');
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Mail.class.php");
include_once("../WEB-INF/Classes/ConexionMultiBD.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

$con = new ConexionMultiBD();
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();

while ($rs_multi = mysql_fetch_array($result_bases)) {
    $empresa = $rs_multi['id_empresa'];
    $catalogo = new Catalogo();
    $mail = new Mail();
    $parametroGlobal = new ParametroGlobal();
    $parametros = new Parametros();
    
    $catalogo->setEmpresa($empresa);
    $mail->setEmpresa($empresa);
    $parametroGlobal->setEmpresa($empresa);
    $parametros->setEmpresa($empresa);
    
    $enviar_correo = "0";
    if($parametros->getRegistroById("26")){
        $enviar_correo = $parametros->getValor();
    }
    
    $conuslta_almacen = "SELECT al.id_almacen AS id,al.nombre_almacen AS almacen,
        GROUP_CONCAT(DISTINCT(usu.correo) SEPARATOR ' */* ') AS correo,
        IF(ISNULL(koc.CantidadEntregada ),0,koc.CantidadEntregada) AS recibidas,
        SUM((SELECT IF(ISNULL(SUM(kent.Cantidad)),0,SUM(kent.Cantidad)) FROM k_det_entr_oc_almacen kent WHERE kent.Id_detalle_entrada=deoc.Id_detalle_entrada)) AS recib_almacen
        FROM k_orden_compra koc 
        LEFT JOIN c_componente cm ON koc.NoParteComponente=cm.NoParte 
        LEFT JOIN c_equipo eq ON koc.NoParteEquipo=eq.NoParte
        LEFT JOIN k_detalle_entrada_orden_compra deoc ON koc.IdDetalleOC=deoc.idKOrdenTrabajo 
        LEFT JOIN c_almacen al ON deoc.Almacen=al.id_almacen 
        LEFT JOIN k_responsablealmacen ra ON al.id_almacen=ra.IdAlmacen LEFT JOIN c_usuario usu ON ra.IdUsuario=usu.IdUsuario
        GROUP BY al.id_almacen HAVING recibidas<>recib_almacen ORDER BY deoc.Almacen ASC";
    $query_almacen = $catalogo->obtenerLista($conuslta_almacen);
    while ($alm = mysql_fetch_array($query_almacen)) {
        $id_almacen = $alm['id'];
        $correo_encargado = $alm['correo'];
        if ($id_almacen != "") {//if si existe el almacén
            $consulta = "SELECT koc.IdOrdenCompra AS oc,IF(ISNULL(koc.NoParteEquipo),'Componente','Equipo') AS tipo,
                IF(ISNULL(koc.NoParteEquipo),cm.NoParte,eq.NoParte) AS no_parte,IF(ISNULL(koc.NoParteEquipo),cm.Modelo,eq.Modelo) AS modelo,
                IF(ISNULL(koc.NoParteEquipo),SUBSTRING(cm.Descripcion,1,40),SUBSTRING(eq.Descripcion,1,40)) AS descripcion,
                koc.Cantidad AS solicitdas,IF(ISNULL(koc.CantidadEntregada ),0,koc.CantidadEntregada) AS recibidas,
                SUM((SELECT IF(ISNULL(SUM(kent.Cantidad)),0,SUM(kent.Cantidad)) FROM k_det_entr_oc_almacen kent WHERE kent.Id_detalle_entrada=deoc.Id_detalle_entrada)) AS recib_almacen,
                (IF(ISNULL(koc.CantidadEntregada ),0,koc.CantidadEntregada)-SUM((SELECT IF(ISNULL(SUM(kent.Cantidad)),0,SUM(kent.Cantidad)) FROM k_det_entr_oc_almacen kent WHERE kent.Id_detalle_entrada=deoc.Id_detalle_entrada))) AS faltantes_entrada
                FROM k_orden_compra koc 
                LEFT JOIN c_componente cm ON koc.NoParteComponente=cm.NoParte 
                LEFT JOIN c_equipo eq ON koc.NoParteEquipo=eq.NoParte 
                LEFT JOIN k_detalle_entrada_orden_compra deoc ON koc.IdDetalleOC=deoc.idKOrdenTrabajo
                WHERE deoc.Almacen='$id_almacen' GROUP BY koc.IdDetalleOC HAVING recibidas<>recib_almacen ORDER BY deoc.Almacen ASC";
            $query_pendientes = $catalogo->obtenerLista($consulta);
            if (mysql_num_rows($query_pendientes) > 0) {//verifica si tiene datos para enviar
                $tabla = "";
                $tabla .= "<table style='border: 1px solid black;border-collapse: collapse;width:100%'>"
                        . "<tr>"
                        . "<th rowspan='2' style='border: 1px black solid;text-align:center;font-size: 10px;'>ORDEN DE COMPRA</th><th rowspan='2' style='border: 1px black solid;text-align:center;font-size: 10px;'>TIPO</th>"
                        . "<th rowspan='2' style='border: 1px black solid;text-align:center;font-size: 10px;'>NO PARTE</th><th rowspan='2' style='border: 1px black solid;text-align:center;font-size: 10px;'>MODELO</th>"
                        . "<th rowspan='2' style='border: 1px black solid;text-align:center;font-size: 10px;'>DESCRIPCIÓN</th><th colspan='4' style='border: 1px black solid;text-align:center;font-size: 10px;'>CANTIDAD</th>"
                        . "</tr><tr>"
                        . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>SOLICITADA</th><th style='border: 1px black solid;text-align:center;font-size: 10px;'>RECIBIDAS</th>"
                        . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>EN ALMACÉN</th><th style='border: 1px black solid;text-align:center;font-size: 10px;'>POR RECIBIR</th>"
                        . "</tr>";
                while ($rs = mysql_fetch_array($query_pendientes)) {
                    $tabla .= "<tr>"
                            . "<td style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $rs['oc'] . "</td>"
                            . "<td style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $rs['tipo'] . "</td>"
                            . "<td style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $rs['no_parte'] . "</td>"
                            . "<td style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $rs['modelo'] . "</td>"
                            . "<td style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $rs['descripcion'] . "</td>"
                            . "<td style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $rs['solicitdas'] . "</td>"
                            . "<td style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $rs['recibidas'] . "</td>"
                            . "<td style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $rs['recib_almacen'] . "</td>"
                            . "<td style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $rs['faltantes_entrada'] . "</td>"
                            . "</tr>";
                }                
                $tabla .= "</table>";
                
                if ($enviar_correo == "1") {// si esta activado el enviar correo del cron
                    $correo = array();
                    $correo_aux = explode(" */* ", $correo_encargado);
                    foreach ($correo_aux as $value) {//correos de los encargados
                        array_push($correo, $value);
                    }

                    $query_correos = $catalogo->obtenerLista("SELECT cs.correo FROM c_correossolicitud cs WHERE cs.TipoSolicitud=14 AND cs.Activo=1");
                    while ($list_correo = mysql_fetch_array($query_correos)) {//correos de la tabla de correos
                        array_push($correo, $list_correo['correo']);
                    }

                    if ($parametroGlobal->getRegistroById("8")) {//obtiene el from del correo
                        $mail->setFrom($parametroGlobal->getValor());
                    } else {
                        $mail->setFrom("scg-salida@scgenesis.mx");
                    }

                    $mail->setSubject("Faltantes de entrada al almacén '" . $alm['almacen'] . "'");
                    $mail->setBody($tabla);
                    foreach ($correo as $valor) {//enviar correos
                        if (isset($valor) && $valor != "" && filter_var($valor, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                            $mail->setTo($valor);
                            $mail->enviarMail();
                        }
                    }
                }
            }
        }
    }
}
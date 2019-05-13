<?php

include_once("WEB-INF/Classes/Catalogo.class.php");
include_once("WEB-INF/Classes/MovimientoEquipo.class.php");
include_once("WEB-INF/Classes/ConexionMultiBD.class.php");

$con = new ConexionMultiBD();
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();
$contador = 1;
while ($rs_multi = mysql_fetch_array($result_bases)) {
    echo "<br/><br/>Procesando empresa " . $rs_multi['nombre_empresa'];
    $empresa = $rs_multi['id_empresa'];

    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);
    $time = time();
    $fechaActual = date("Y-m-d", $time);

    $query = $catalogo->obtenerLista("SELECT * FROM c_presolicitud AS prs 
                WHERE prs.Fecha ='" . $fechaActual . "'");
    $movimiento = new MovimientoEquipo();
    $movimiento->setEmpresa($empresa);
    if (mysql_num_rows($query) > 0) {
        
        while ($rs = mysql_fetch_array($query)) {
            if($contador == 1){
                $contador = $rs['id_reporteHistorico'];
            }
            if($contador != $rs['id_reporteHistorico']){
                $movimiento->EnviarMailCli(); //Se envia el correo
                $nseries = $movimiento->getSeriesAlm();

                if (count($nseries) > 0) {
                    /*             * ********************************************************CORREO************************************************************************ */
                    $query = $catalogo->obtenerLista("SELECT reportes_historicos.NumReporte AS NumReporte,
                    reportes_historicos.FechaCreacion AS Fecha
                    FROM reportes_historicos WHERE reportes_historicos.NumReporte=" . $rs['id_reporteHistorico'] . ";");

                    $text1 = "";
                    if ($rsp = mysql_fetch_array($query)) {
                        $count_mysql = 0;
                        $queryprin = $catalogo->obtenerLista("SELECT
                                movimientos_equipo.NoSerie AS NoSerie,
                                e.Modelo,
                                e.Descripcion AS Descripcion,
                                movimientos_equipo.tipo_movimiento AS Tipo_Movimiento,
                                reportes_historicos.FechaCreacion AS Fecha,
                                DATE(reportes_historicos.FechaCreacion) AS FechaSimple,
                                movimientos_equipo.clave_cliente_anterior AS clave_cliente_anterior,
                                movimientos_equipo.clave_centro_costo_anterior AS clave_centro_costo_anterior,
                                movimientos_equipo.clave_cliente_nuevo AS clave_cliente_nuevo,
                                movimientos_equipo.clave_centro_costo_nuevo AS clave_centro_costo_nuevo,
                                movimientos_equipo.almacen_anterior AS almacen_anterior,
                                movimientos_equipo.almacen_nuevo AS almacen_nuevo,
                                movimientos_equipo.pendiente AS pendiente,
                                c_tipomovimiento.Nombre AS TipoMovimiento,
                                c_inventarioequipo.Ubicacion AS Ubicacion,
                                c_inventarioequipo.NoParteEquipo AS NoParte,
                                k_almacenequipo.Ubicacion AS UbicacionAlm
                                FROM reportes_historicos
                                INNER JOIN reportes_movimientos ON reportes_movimientos.id_reportes = reportes_historicos.NumReporte
                                INNER JOIN movimientos_equipo ON movimientos_equipo.id_movimientos = reportes_movimientos.id_movimientos
                                LEFT JOIN c_tipomovimiento ON c_tipomovimiento.IdTipoMovimiento=movimientos_equipo.IdTipoMovimiento
                                LEFT JOIN c_bitacora as b ON b.NoSerie = movimientos_equipo.NoSerie
                                LEFT JOIN c_equipo as e ON e.NoParte = b.NoParte
                                LEFT JOIN c_inventarioequipo ON c_inventarioequipo.NoSerie=movimientos_equipo.NoSerie
                                LEFT JOIN k_almacenequipo ON k_almacenequipo.NoSerie=movimientos_equipo.NoSerie
                                WHERE reportes_historicos.NumReporte=" . $rs['id_reporteHistorico'] . " ORDER BY clave_centro_costo_anterior,almacen_anterior");
                        $numero_filas = mysql_num_rows($queryprin);
                        $usuario = new Usuario();
                        $usuario->setEmpresa($empresa);
                        $usuario->getRegistroById($_SESSION['idUsuario']);
                        //titulo
                        $text1.="<h4>Movimiento Realizado por " . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . "</h4><br/>";
                        while ($rss = mysql_fetch_array($queryprin)) {
                            //titulo
                            $text1.="<h4>Reporte de movimiento de equipo</h4><br/>";
                            //tabla 1
                            $text1.="<table border='1'><tr><td>TIPO DE MOVIMIENTO</td><td>" . $rss['TipoMovimiento'] . "</td></tr><tr><td>FECHA</td><td>" . $rsp['Fecha'] . "</td></tr></table><br/>";
                            //tabla 2
                            $text1.="<table border='1'><tr><td colspan='4'><b>DESTINO</b></td></tr>";
                            if ($rss['Tipo_Movimiento'] == 1 || $rss['Tipo_Movimiento'] == 2 || $rss['Tipo_Movimiento'] == 5) {
                                $query = $catalogo->obtenerLista("SELECT
                                        c_cliente.NombreRazonSocial AS Nombre,
                                        c_cliente.RFC AS RFC,
                                        CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoMaterno,\" \",c_usuario.ApellidoPaterno) AS Contacto,
                                        c_centrocosto.Nombre AS CentroCosto,
                                        CONCAT(c_domicilio.Calle,\" #\",c_domicilio.NoExterior) AS Calle,
                                        c_domicilio.Colonia AS Colonia,
                                        c_domicilio.Delegacion AS Delegacion,
                                        c_domicilio.Estado AS Estado,
                                        c_domicilio.CodigoPostal AS CP
                                        FROM
                                                c_cliente
                                        INNER JOIN c_centrocosto ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                                        INNER JOIN c_usuario ON c_usuario.IdUsuario=c_cliente.EjecutivoCuenta
                                        INNER JOIN c_domicilio ON c_centrocosto.ClaveCentroCosto=c_domicilio.ClaveEspecialDomicilio
                                        WHERE c_centrocosto.ClaveCentroCosto='" . $rss['clave_centro_costo_nuevo'] . "'");
                                if ($resultSet = mysql_fetch_array($query)) {
                                    $text1.="<tr><td>NOMBRE ó RAZON SOCIAL</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                                    $text1.="<tr><td>CONTACTO COMERCIAL</td><td>" . $resultSet['Contacto'] . "</td><td colspan='2'><b>RFC:</b>" . $resultSet['RFC'] . "</td></tr>";
                                    $text1.="<tr><td colspan='4'>Localidad: " . $resultSet['CentroCosto'] . "</td></tr>";
                                    $text1.="<tr><td>CALLE Y NÚMERO</td><td colspan='3'>" . $resultSet['Calle'] . "</td></tr>";
                                    $text1.="<tr><td>COLONIA</td><td colspan='3'>" . $resultSet['Colonia'] . "</td></tr>";
                                    $text1.="<tr><td>DELEGACION ó MUNICIPIO</td><td colspan='3'>" . $resultSet['Delegacion'] . "</td></tr>";
                                    $text1.="<tr><td>CIUDAD / ESTADO</td><td colspan='3'>" . $resultSet['Estado'] . "</td></tr>";
                                    $text1.="<tr><td>TELEFONO Y EXTENSION</td><td></td><td colspan='2'><b>C. POSTAL</b>:" . $resultSet['CP'] . "</td></tr>";
                                }
                            } else {
                                $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre,CONCAT(c_domicilio_almacen.Calle,\" #\",c_domicilio_almacen.NoExterior) AS Calle,
                                        c_domicilio_almacen.Colonia AS Colonia,
                                        c_domicilio_almacen.Delegacion AS Delegacion,
                                        c_domicilio_almacen.Estado AS Estado,
                                        c_domicilio_almacen.CodigoPostal AS CP FROM c_almacen LEFT JOIN c_domicilio_almacen ON c_domicilio_almacen.IdAlmacen = c_almacen.id_almacen WHERE c_almacen.id_almacen='" . $rss['almacen_nuevo'] . "'");
                                if ($resultSet = mysql_fetch_array($query)) {
                                    $text1.="<tr><td>ALMACÉN</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                                    $text1.="<tr><td>CALLE Y NÚMERO</td><td colspan='3'>" . $resultSet['Calle'] . "</td></tr>";
                                    $text1.="<tr><td>COLONIA</td><td colspan='3'>" . $resultSet['Colonia'] . "</td></tr>";
                                    $text1.="<tr><td>DELEGACION ó MUNICIPIO</td><td colspan='3'>" . $resultSet['Delegacion'] . "</td></tr>";
                                    $text1.="<tr><td>CIUDAD / ESTADO</td><td colspan='3'>" . $resultSet['Estado'] . "</td></tr>";
                                    $text1.="<tr><td>TELEFONO Y EXTENSION</td><td></td><td colspan='2'><b>C. POSTAL</b>: " . $resultSet['CP'] . "</td></tr>";
                                } else {
                                    $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre FROM c_almacen WHERE c_almacen.id_almacen='" . $rss['almacen_nuevo'] . "'");
                                    if ($resultSet = mysql_fetch_array($query)) {
                                        $text1.="<tr><td>ALMACÉN</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                                    }
                                }
                            }
                            $text1.="<tr><td colspan='4'><b>ORIGEN</b></td></tr>";
                            if ($rss['Tipo_Movimiento'] == 1 || $rss['Tipo_Movimiento'] == 3 || $rss['Tipo_Movimiento'] == 5) {
                                $query = $catalogo->obtenerLista("SELECT
                                        c_cliente.NombreRazonSocial AS Nombre,
                                        c_cliente.RFC AS RFC,
                                        CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoMaterno,\" \",c_usuario.ApellidoPaterno) AS Contacto,
                                        c_centrocosto.Nombre AS CentroCosto,
                                        CONCAT(c_domicilio.Calle,\" #\",c_domicilio.NoExterior) AS Calle,
                                        c_domicilio.Colonia AS Colonia,
                                        c_domicilio.Delegacion AS Delegacion,
                                        c_domicilio.Estado AS Estado,
                                        c_domicilio.CodigoPostal AS CP
                                        FROM
                                                c_cliente
                                        INNER JOIN c_centrocosto ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                                        INNER JOIN c_usuario ON c_usuario.IdUsuario=c_cliente.EjecutivoCuenta
                                        INNER JOIN c_domicilio ON c_centrocosto.ClaveCentroCosto=c_domicilio.ClaveEspecialDomicilio
                                        WHERE c_centrocosto.ClaveCentroCosto='" . $rss['clave_centro_costo_anterior'] . "'");
                                if ($resultSet = mysql_fetch_array($query)) {
                                    $text1.="<tr><td>NOMBRE ó RAZON SOCIAL</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                                    $text1.="<tr><td>CONTACTO COMERCIAL</td><td>" . $resultSet['Contacto'] . "</td><td colspan='2'><b>RFC:</b>" . $resultSet['RFC'] . "</tr>";
                                    $text1.="<tr><td colspan='4'>Localidad: " . $resultSet['CentroCosto'] . "</td></tr>";
                                    $text1.="<tr><td>CALLE Y NÚMERO</td><td colspan='3'>" . $resultSet['Calle'] . "</td></tr>";
                                    $text1.="<tr><td>COLONIA</td><td colspan='3'>" . $resultSet['Colonia'] . "</td></tr>";
                                    $text1.="<tr><td >DELEGACION ó MUNICIPIO</td><td colspan='3'>" . $resultSet['Delegacion'] . "</td></tr>";
                                    $text1.="<tr><td>CIUDAD / ESTADO</td><td colspan='3'>" . $resultSet['Estado'] . "</td></tr>";
                                    $text1.="<tr><td>TELEFONO Y EXTENSION</td><td></td><td colspan='2'><b>C. POSTAL</b>:" . $resultSet['CP'] . "</td></tr>";
                                }
                            } else {
                                $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre,CONCAT(c_domicilio_almacen.Calle,\" #\",c_domicilio_almacen.NoExterior) AS Calle,
                                        c_domicilio_almacen.Colonia AS Colonia,
                                        c_domicilio_almacen.Delegacion AS Delegacion,
                                        c_domicilio_almacen.Estado AS Estado,
                                        c_domicilio_almacen.CodigoPostal AS CP FROM c_almacen LEFT JOIN c_domicilio_almacen ON c_domicilio_almacen.IdAlmacen = c_almacen.id_almacen WHERE c_almacen.id_almacen='" . $rss['almacen_anterior'] . "'");
                                if ($resultSet = mysql_fetch_array($query)) {
                                    $text1.="<tr><td>ALMACÉN</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                                    $text1.="<tr><td>CALLE Y NÚMERO</td><td colspan='3'>" . $resultSet['Calle'] . "</td></tr>";
                                    $text1.="<tr><td>COLONIA</td><td colspan='3'>" . $resultSet['Colonia'] . "</td></tr>";
                                    $text1.="<tr><td>DELEGACION ó MUNICIPIO</td><td colspan='3'>" . $resultSet['Delegacion'] . "</td></tr>";
                                    $text1.="<tr><td>CIUDAD / ESTADO</td><td colspan='3'>" . $resultSet['Estado'] . "</td></tr>";
                                    $text1.="<tr><td>TELEFONO Y EXTENSION</td><td></td><td colspan='2'><b>C. POSTAL</b>: " . $resultSet['CP'] . "</td></tr>";
                                } else {
                                    $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre FROM c_almacen WHERE c_almacen.id_almacen='" . $rss['almacen_anterior'] . "'");
                                    if ($resultSet = mysql_fetch_array($query)) {
                                        $text1.="<tr><td>ALMACÉN</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                                    }
                                }
                            }
                            $text1.="</table><br/><table border='1'><tr><td colspan='4'><b>DESCRIPCION DE EQUIPOS</b></td></tr>";
                            $text1.="<tr><td>No Serie</td><td>Modelo</td><td>Ubicación</td><td>Contadores</td></tr>";


                            mysql_data_seek($queryprin, $count_mysql);
                            $aux_ant = "";
                            while ($rss = mysql_fetch_array($queryprin)) {
                                $text1.="<tr>";
                                if ($aux_ant == "" || $aux_ant == $rss['clave_centro_costo_anterior']) {
                                    $val = false;
                                    $query4 = $catalogo->obtenerLista("SELECT ts.Nombre AS servicio, ts.IdTipoServicio AS ID FROM `k_equipocaracteristicaformatoservicio` AS ke
                                                INNER JOIN c_tiposervicio AS ts ON ke.NoParte = '" . $rss['NoParte'] . "' AND ts.IdTipoServicio = ke.IdTipoServicio;");
                                    while ($resultSet = mysql_fetch_array($query4)) {
                                        if ($resultSet['ID'] == 1) {
                                            $val = true;
                                        }
                                    }
                                    $query = $catalogo->obtenerLista("SELECT
                                                        DATE(c_lectura.Fecha) AS Fecha,
                                                        c_lectura.ContadorBNPaginas AS ContadorBN,
                                                        c_lectura.ContadorColorPaginas AS ContadorCL,
                                                        c_lectura.ContadorBNML AS ContadorBNML,
                                                        c_lectura.ContadorColorML AS ContadorCLML,
                                                        c_lectura.NivelTonNegro AS NivelTonNegro,
                                                        c_lectura.NivelTonCian AS NivelTonCian,
                                                        c_lectura.NivelTonMagenta AS NivelTonMagenta,
                                                        c_lectura.NivelTonAmarillo AS NivelTonAmarillo
                                                        FROM movimientos_equipo
                                                        INNER JOIN c_lectura ON c_lectura.NoSerie = movimientos_equipo.NoSerie
                                                        WHERE movimientos_equipo.NoSerie ='" . $rss['NoSerie'] . "' AND c_lectura.Fecha <='" . $rss['FechaSimple'] . "' ORDER BY Fecha DESC");
                                    if ($rs = mysql_fetch_array($query)) {
                                        if ($val)
                                            $text1.="<td>" . $rss['NoSerie'] . "</td><td>" . $rss['Modelo'] . "</td><td>" . $rss['Ubicacion'] . "</td><td>BN: " . $rs['ContadorBN'] . "<br/>Color: " . $rs['ContadorCL'] . "</td>";
                                        else
                                            $text1.="<td>" . $rss['NoSerie'] . "</td><td>" . $rss['Modelo'] . "</td><td>" . $rss['Ubicacion'] . "</td><td>BN: " . $rs['ContadorBN'] . "</td>";
                                    } else {
                                        $text1.="<td>" . $rss['NoSerie'] . "</td><td>" . $rss['Modelo'] . "</td><td>" . $rss['Ubicacion'] . "</td> <td></td>";
                                    }
                                    $aux_ant = $rss['clave_centro_costo_anterior'];
                                } else {
                                    break;
                                }
                                $text1.="</tr>";
                                $count_mysql++;
                            }//Cierre
                            if ($numero_filas > $count_mysql) {
                                mysql_data_seek($queryprin, $count_mysql);
                            }
                            $text1.="</table><br/><br/><br/>";
                        }
                    }
                    $text1.="<br/>Para ver el cambio de equipo, ingrese al sistema por favor: " . $_SESSION['ip_server'];
                    $mail = new Mail();
                    $mail->setEmpresa($empresa);
                    if ($parametroGlobal->getRegistroById("8")) {
                        $mail->setFrom($parametroGlobal->getValor());
                    } else {
                        $mail->setFrom("scg-salida@scgenesis.mx");
                    }
                    $mail->setSubject("No Movimiento: " . $rs['id_reporteHistorico']);
                    $mail->setBody($text1);
                    $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud=2;");
                    $correos = array();
                    $z = 0;
                    while ($rs = mysql_fetch_array($query4)) {
                        $correos[$z] = $rs['correo'];
                        $z++;
                    }
                    foreach ($correos as $value) {
                        if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                            $mail->setTo($value);
                            if ($mail->enviarMail() != "1") {
                                echo "Error: No se pudo enviar el correo.";
                            } else {
                                echo "";
                            }
                        }
                    }
                    echo "<a href='WEB-INF/Controllers/Ventas/Controller_Reporte_Historico.php?noSolicitud=" . $rs['id_reporteHistorico'] . "' target='_blank' style='float: right;'>Generar Reporte</a>";
                }//end if($nseries)
                $movimiento = new MovimientoEquipo();
                $movimiento->setEmpresa($empresa);
                $contador = $rs['id_reporteHistorico'];
            }
            $movimiento->setCausa_Movimiento($rs['Causa_Movimiento']);
            $movimiento->setIdalmacen($rs['id_almacen']);
            $movimiento->setUser($rs['user']);
            $movimiento->setComentario($rs['comentario']);
            $movimiento->setTipomovimiento($rs['tipoMovimiento']);
            $movimiento->setLectura($rs['id_lectura']);
            $movimiento->setReporte_historico($rs['id_reporteHistorico']);
            $movimiento->setIdUsuario($rs['id_usuario']);
            $movimiento->setSerie($rs['NoSerie']);
            $movimiento->setFecha($rs['Fecha']);
            $movimiento->setId_lectura($rs['id_lectura']);
            $movimiento->cambiarAlmacen(); //Se crean las solicitudes correspondientes    
        }
        $movimiento->EnviarMailCli(); //Se envia el correo
        $nseries = $movimiento->getSeriesAlm();

        if (count($nseries) > 0) {
            /*             * ********************************************************CORREO************************************************************************ */
            $query = $catalogo->obtenerLista("SELECT reportes_historicos.NumReporte AS NumReporte,
            reportes_historicos.FechaCreacion AS Fecha
            FROM reportes_historicos WHERE reportes_historicos.NumReporte=" . $rs['id_reporteHistorico'] . ";");

            $text1 = "";
            if ($rsp = mysql_fetch_array($query)) {
                $count_mysql = 0;
                $queryprin = $catalogo->obtenerLista("SELECT
                        movimientos_equipo.NoSerie AS NoSerie,
                        e.Modelo,
                        e.Descripcion AS Descripcion,
                        movimientos_equipo.tipo_movimiento AS Tipo_Movimiento,
                        reportes_historicos.FechaCreacion AS Fecha,
                        DATE(reportes_historicos.FechaCreacion) AS FechaSimple,
                        movimientos_equipo.clave_cliente_anterior AS clave_cliente_anterior,
                        movimientos_equipo.clave_centro_costo_anterior AS clave_centro_costo_anterior,
                        movimientos_equipo.clave_cliente_nuevo AS clave_cliente_nuevo,
                        movimientos_equipo.clave_centro_costo_nuevo AS clave_centro_costo_nuevo,
                        movimientos_equipo.almacen_anterior AS almacen_anterior,
                        movimientos_equipo.almacen_nuevo AS almacen_nuevo,
                        movimientos_equipo.pendiente AS pendiente,
                        c_tipomovimiento.Nombre AS TipoMovimiento,
                        c_inventarioequipo.Ubicacion AS Ubicacion,
                        c_inventarioequipo.NoParteEquipo AS NoParte,
                        k_almacenequipo.Ubicacion AS UbicacionAlm
                        FROM reportes_historicos
                        INNER JOIN reportes_movimientos ON reportes_movimientos.id_reportes = reportes_historicos.NumReporte
                        INNER JOIN movimientos_equipo ON movimientos_equipo.id_movimientos = reportes_movimientos.id_movimientos
                        LEFT JOIN c_tipomovimiento ON c_tipomovimiento.IdTipoMovimiento=movimientos_equipo.IdTipoMovimiento
                        LEFT JOIN c_bitacora as b ON b.NoSerie = movimientos_equipo.NoSerie
                        LEFT JOIN c_equipo as e ON e.NoParte = b.NoParte
                        LEFT JOIN c_inventarioequipo ON c_inventarioequipo.NoSerie=movimientos_equipo.NoSerie
                        LEFT JOIN k_almacenequipo ON k_almacenequipo.NoSerie=movimientos_equipo.NoSerie
                        WHERE reportes_historicos.NumReporte=" . $rs['id_reporteHistorico'] . " ORDER BY clave_centro_costo_anterior,almacen_anterior");
                $numero_filas = mysql_num_rows($queryprin);
                $usuario = new Usuario();
                $usuario->setEmpresa($empresa);
                $usuario->getRegistroById($_SESSION['idUsuario']);
                //titulo
                $text1.="<h4>Movimiento Realizado por " . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . "</h4><br/>";
                while ($rss = mysql_fetch_array($queryprin)) {
                    //titulo
                    $text1.="<h4>Reporte de movimiento de equipo</h4><br/>";
                    //tabla 1
                    $text1.="<table border='1'><tr><td>TIPO DE MOVIMIENTO</td><td>" . $rss['TipoMovimiento'] . "</td></tr><tr><td>FECHA</td><td>" . $rsp['Fecha'] . "</td></tr></table><br/>";
                    //tabla 2
                    $text1.="<table border='1'><tr><td colspan='4'><b>DESTINO</b></td></tr>";
                    if ($rss['Tipo_Movimiento'] == 1 || $rss['Tipo_Movimiento'] == 2 || $rss['Tipo_Movimiento'] == 5) {
                        $query = $catalogo->obtenerLista("SELECT
                                c_cliente.NombreRazonSocial AS Nombre,
                                c_cliente.RFC AS RFC,
                                CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoMaterno,\" \",c_usuario.ApellidoPaterno) AS Contacto,
                                c_centrocosto.Nombre AS CentroCosto,
                                CONCAT(c_domicilio.Calle,\" #\",c_domicilio.NoExterior) AS Calle,
                                c_domicilio.Colonia AS Colonia,
                                c_domicilio.Delegacion AS Delegacion,
                                c_domicilio.Estado AS Estado,
                                c_domicilio.CodigoPostal AS CP
                                FROM
                                        c_cliente
                                INNER JOIN c_centrocosto ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                                INNER JOIN c_usuario ON c_usuario.IdUsuario=c_cliente.EjecutivoCuenta
                                INNER JOIN c_domicilio ON c_centrocosto.ClaveCentroCosto=c_domicilio.ClaveEspecialDomicilio
                                WHERE c_centrocosto.ClaveCentroCosto='" . $rss['clave_centro_costo_nuevo'] . "'");
                        if ($resultSet = mysql_fetch_array($query)) {
                            $text1.="<tr><td>NOMBRE ó RAZON SOCIAL</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                            $text1.="<tr><td>CONTACTO COMERCIAL</td><td>" . $resultSet['Contacto'] . "</td><td colspan='2'><b>RFC:</b>" . $resultSet['RFC'] . "</td></tr>";
                            $text1.="<tr><td colspan='4'>Localidad: " . $resultSet['CentroCosto'] . "</td></tr>";
                            $text1.="<tr><td>CALLE Y NÚMERO</td><td colspan='3'>" . $resultSet['Calle'] . "</td></tr>";
                            $text1.="<tr><td>COLONIA</td><td colspan='3'>" . $resultSet['Colonia'] . "</td></tr>";
                            $text1.="<tr><td>DELEGACION ó MUNICIPIO</td><td colspan='3'>" . $resultSet['Delegacion'] . "</td></tr>";
                            $text1.="<tr><td>CIUDAD / ESTADO</td><td colspan='3'>" . $resultSet['Estado'] . "</td></tr>";
                            $text1.="<tr><td>TELEFONO Y EXTENSION</td><td></td><td colspan='2'><b>C. POSTAL</b>:" . $resultSet['CP'] . "</td></tr>";
                        }
                    } else {
                        $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre,CONCAT(c_domicilio_almacen.Calle,\" #\",c_domicilio_almacen.NoExterior) AS Calle,
                                c_domicilio_almacen.Colonia AS Colonia,
                                c_domicilio_almacen.Delegacion AS Delegacion,
                                c_domicilio_almacen.Estado AS Estado,
                                c_domicilio_almacen.CodigoPostal AS CP FROM c_almacen LEFT JOIN c_domicilio_almacen ON c_domicilio_almacen.IdAlmacen = c_almacen.id_almacen WHERE c_almacen.id_almacen='" . $rss['almacen_nuevo'] . "'");
                        if ($resultSet = mysql_fetch_array($query)) {
                            $text1.="<tr><td>ALMACÉN</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                            $text1.="<tr><td>CALLE Y NÚMERO</td><td colspan='3'>" . $resultSet['Calle'] . "</td></tr>";
                            $text1.="<tr><td>COLONIA</td><td colspan='3'>" . $resultSet['Colonia'] . "</td></tr>";
                            $text1.="<tr><td>DELEGACION ó MUNICIPIO</td><td colspan='3'>" . $resultSet['Delegacion'] . "</td></tr>";
                            $text1.="<tr><td>CIUDAD / ESTADO</td><td colspan='3'>" . $resultSet['Estado'] . "</td></tr>";
                            $text1.="<tr><td>TELEFONO Y EXTENSION</td><td></td><td colspan='2'><b>C. POSTAL</b>: " . $resultSet['CP'] . "</td></tr>";
                        } else {
                            $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre FROM c_almacen WHERE c_almacen.id_almacen='" . $rss['almacen_nuevo'] . "'");
                            if ($resultSet = mysql_fetch_array($query)) {
                                $text1.="<tr><td>ALMACÉN</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                            }
                        }
                    }
                    $text1.="<tr><td colspan='4'><b>ORIGEN</b></td></tr>";
                    if ($rss['Tipo_Movimiento'] == 1 || $rss['Tipo_Movimiento'] == 3 || $rss['Tipo_Movimiento'] == 5) {
                        $query = $catalogo->obtenerLista("SELECT
                                c_cliente.NombreRazonSocial AS Nombre,
                                c_cliente.RFC AS RFC,
                                CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoMaterno,\" \",c_usuario.ApellidoPaterno) AS Contacto,
                                c_centrocosto.Nombre AS CentroCosto,
                                CONCAT(c_domicilio.Calle,\" #\",c_domicilio.NoExterior) AS Calle,
                                c_domicilio.Colonia AS Colonia,
                                c_domicilio.Delegacion AS Delegacion,
                                c_domicilio.Estado AS Estado,
                                c_domicilio.CodigoPostal AS CP
                                FROM
                                        c_cliente
                                INNER JOIN c_centrocosto ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                                INNER JOIN c_usuario ON c_usuario.IdUsuario=c_cliente.EjecutivoCuenta
                                INNER JOIN c_domicilio ON c_centrocosto.ClaveCentroCosto=c_domicilio.ClaveEspecialDomicilio
                                WHERE c_centrocosto.ClaveCentroCosto='" . $rss['clave_centro_costo_anterior'] . "'");
                        if ($resultSet = mysql_fetch_array($query)) {
                            $text1.="<tr><td>NOMBRE ó RAZON SOCIAL</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                            $text1.="<tr><td>CONTACTO COMERCIAL</td><td>" . $resultSet['Contacto'] . "</td><td colspan='2'><b>RFC:</b>" . $resultSet['RFC'] . "</tr>";
                            $text1.="<tr><td colspan='4'>Localidad: " . $resultSet['CentroCosto'] . "</td></tr>";
                            $text1.="<tr><td>CALLE Y NÚMERO</td><td colspan='3'>" . $resultSet['Calle'] . "</td></tr>";
                            $text1.="<tr><td>COLONIA</td><td colspan='3'>" . $resultSet['Colonia'] . "</td></tr>";
                            $text1.="<tr><td >DELEGACION ó MUNICIPIO</td><td colspan='3'>" . $resultSet['Delegacion'] . "</td></tr>";
                            $text1.="<tr><td>CIUDAD / ESTADO</td><td colspan='3'>" . $resultSet['Estado'] . "</td></tr>";
                            $text1.="<tr><td>TELEFONO Y EXTENSION</td><td></td><td colspan='2'><b>C. POSTAL</b>:" . $resultSet['CP'] . "</td></tr>";
                        }
                    } else {
                        $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre,CONCAT(c_domicilio_almacen.Calle,\" #\",c_domicilio_almacen.NoExterior) AS Calle,
                                c_domicilio_almacen.Colonia AS Colonia,
                                c_domicilio_almacen.Delegacion AS Delegacion,
                                c_domicilio_almacen.Estado AS Estado,
                                c_domicilio_almacen.CodigoPostal AS CP FROM c_almacen LEFT JOIN c_domicilio_almacen ON c_domicilio_almacen.IdAlmacen = c_almacen.id_almacen WHERE c_almacen.id_almacen='" . $rss['almacen_anterior'] . "'");
                        if ($resultSet = mysql_fetch_array($query)) {
                            $text1.="<tr><td>ALMACÉN</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                            $text1.="<tr><td>CALLE Y NÚMERO</td><td colspan='3'>" . $resultSet['Calle'] . "</td></tr>";
                            $text1.="<tr><td>COLONIA</td><td colspan='3'>" . $resultSet['Colonia'] . "</td></tr>";
                            $text1.="<tr><td>DELEGACION ó MUNICIPIO</td><td colspan='3'>" . $resultSet['Delegacion'] . "</td></tr>";
                            $text1.="<tr><td>CIUDAD / ESTADO</td><td colspan='3'>" . $resultSet['Estado'] . "</td></tr>";
                            $text1.="<tr><td>TELEFONO Y EXTENSION</td><td></td><td colspan='2'><b>C. POSTAL</b>: " . $resultSet['CP'] . "</td></tr>";
                        } else {
                            $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre FROM c_almacen WHERE c_almacen.id_almacen='" . $rss['almacen_anterior'] . "'");
                            if ($resultSet = mysql_fetch_array($query)) {
                                $text1.="<tr><td>ALMACÉN</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                            }
                        }
                    }
                    $text1.="</table><br/><table border='1'><tr><td colspan='4'><b>DESCRIPCION DE EQUIPOS</b></td></tr>";
                    $text1.="<tr><td>No Serie</td><td>Modelo</td><td>Ubicación</td><td>Contadores</td></tr>";


                    mysql_data_seek($queryprin, $count_mysql);
                    $aux_ant = "";
                    while ($rss = mysql_fetch_array($queryprin)) {
                        $text1.="<tr>";
                        if ($aux_ant == "" || $aux_ant == $rss['clave_centro_costo_anterior']) {
                            $val = false;
                            $query4 = $catalogo->obtenerLista("SELECT ts.Nombre AS servicio, ts.IdTipoServicio AS ID FROM `k_equipocaracteristicaformatoservicio` AS ke
                                        INNER JOIN c_tiposervicio AS ts ON ke.NoParte = '" . $rss['NoParte'] . "' AND ts.IdTipoServicio = ke.IdTipoServicio;");
                            while ($resultSet = mysql_fetch_array($query4)) {
                                if ($resultSet['ID'] == 1) {
                                    $val = true;
                                }
                            }
                            $query = $catalogo->obtenerLista("SELECT
                                                DATE(c_lectura.Fecha) AS Fecha,
                                                c_lectura.ContadorBNPaginas AS ContadorBN,
                                                c_lectura.ContadorColorPaginas AS ContadorCL,
                                                c_lectura.ContadorBNML AS ContadorBNML,
                                                c_lectura.ContadorColorML AS ContadorCLML,
                                                c_lectura.NivelTonNegro AS NivelTonNegro,
                                                c_lectura.NivelTonCian AS NivelTonCian,
                                                c_lectura.NivelTonMagenta AS NivelTonMagenta,
                                                c_lectura.NivelTonAmarillo AS NivelTonAmarillo
                                                FROM movimientos_equipo
                                                INNER JOIN c_lectura ON c_lectura.NoSerie = movimientos_equipo.NoSerie
                                                WHERE movimientos_equipo.NoSerie ='" . $rss['NoSerie'] . "' AND c_lectura.Fecha <='" . $rss['FechaSimple'] . "' ORDER BY Fecha DESC");
                            if ($rs = mysql_fetch_array($query)) {
                                if ($val)
                                    $text1.="<td>" . $rss['NoSerie'] . "</td><td>" . $rss['Modelo'] . "</td><td>" . $rss['Ubicacion'] . "</td><td>BN: " . $rs['ContadorBN'] . "<br/>Color: " . $rs['ContadorCL'] . "</td>";
                                else
                                    $text1.="<td>" . $rss['NoSerie'] . "</td><td>" . $rss['Modelo'] . "</td><td>" . $rss['Ubicacion'] . "</td><td>BN: " . $rs['ContadorBN'] . "</td>";
                            } else {
                                $text1.="<td>" . $rss['NoSerie'] . "</td><td>" . $rss['Modelo'] . "</td><td>" . $rss['Ubicacion'] . "</td> <td></td>";
                            }
                            $aux_ant = $rss['clave_centro_costo_anterior'];
                        } else {
                            break;
                        }
                        $text1.="</tr>";
                        $count_mysql++;
                    }//Cierre
                    if ($numero_filas > $count_mysql) {
                        mysql_data_seek($queryprin, $count_mysql);
                    }
                    $text1.="</table><br/><br/><br/>";
                }
            }
            $text1.="<br/>Para ver el cambio de equipo, ingrese al sistema por favor: " . $_SESSION['ip_server'];
            $mail = new Mail();
            $mail->setEmpresa($empresa);
            if ($parametroGlobal->getRegistroById("8")) {
                $mail->setFrom($parametroGlobal->getValor());
            } else {
                $mail->setFrom("scg-salida@scgenesis.mx");
            }
            $mail->setSubject("No Movimiento: " . $rs['id_reporteHistorico']);
            $mail->setBody($text1);
            $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud=2;");
            $correos = array();
            $z = 0;
            while ($rs = mysql_fetch_array($query4)) {
                $correos[$z] = $rs['correo'];
                $z++;
            }
            foreach ($correos as $value) {
                if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                    $mail->setTo($value);
                    if ($mail->enviarMail() != "1") {
                        echo "Error: No se pudo enviar el correo.";
                    } else {
                        echo "";
                    }
                }
            }
            echo "<a href='WEB-INF/Controllers/Ventas/Controller_Reporte_Historico.php?noSolicitud=" . $rs['id_reporteHistorico'] . "' target='_blank' style='float: right;'>Generar Reporte</a>";
        }//end if($nseries)
    } else {
        echo "<br/>No hay retiros programados para hoy ($fechaActual)";
    }
}
?>


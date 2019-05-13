<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/CentroCosto.class.php");
include_once("../../Classes/Mail.class.php");
include_once("../../Classes/Usuario.class.php");
include_once("../../Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../../Classes/Componente.class.php");
include_once("../../Classes/Parametros.class.php");
include_once("../../Classes/Cliente.class.php");
include_once("../../Classes/ParametroGlobal.class.php");
$parametroGlobal = new ParametroGlobal();

if (isset($_POST['num'])) {
    $numero = $_POST['num'];
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
        $id_solicitud = $parametros['solicitud'];
        $cliente = $parametros['cliente'];
        $comentario = str_replace("'", "´", $parametros['comentario_creo']);
        //$comentario = $parametros['comentario_creo'];        
        $catalogo = new Catalogo();
        $hay_equipos = false;
        $maximos_toner = 0; /* Maximo de toner por equipo */
        $toners_solicitados = 0;
        $caracteristica = new EquipoCaracteristicasFormatoServicio();
        $componente = new Componente();

        $cliente_objeto = new Cliente();
        $cliente_objeto->getRegistroById($cliente);
        $parametros_obj = new Parametros();
        $valor = "0";
        if ($parametros_obj->getRegistroById("14")) {
            $valor = $parametros_obj->getValor();
        }

        if ($cliente_objeto->getSuspendido() == "1" || ($cliente_objeto->getIdEstatusCobranza() == "2" && $valor == "0")) {
            echo "Error: Este cliente esta marcado como suspendido o moroso, no se puede continuar con el proceso.";
            return null;
        }

        $parametro = new Parametros();
        $parametro->getRegistroById("10");
        if (($parametro->getValor()) != null) {
            $maximo_negro = intval($parametro->getValor());
        } else {
            $maximo_negro = 2;
        }

        $parametro->getRegistroById("11");
        if (($parametro->getValor()) != null) {
            $maximo_color = intval($parametro->getValor());
            ;
        } else {
            $maximo_color = 8;
        }

        for ($i = 1; $i < $numero; $i++) {
            if ($parametros['tipo' . $i] == "0") {
                $hay_equipos = true;
                /* Verificamos si este equipo es de color o solo b-n */
                $es_color = false;
                $result = $caracteristica->getTiposDeServicios($parametros['modelo' . $i]);
                while ($rs = mysql_fetch_array($result)) {
                    if ($rs['IdTipoServicio'] == "1") {
                        $es_color = true;
                    }
                }
                if ($es_color) {/* Si es de color, puede pedir n toners */
                    $maximos_toner+=($maximo_color * intval($parametros['numero' . $i]));
                } else {/* Si es b-n, solo puede pedir n toner */
                    $maximos_toner+=($maximo_negro * intval($parametros['numero' . $i]));
                }
            } else {/* Contamos el numero de toners */
                $componente->getRegistroById($parametros['modelo' . $i]);
                if ($componente->getTipo() == "2") {//Si es toner
                    $toners_solicitados+= (intval($parametros['numero' . $i]));
                }
            }
        }

        if ($cliente == "22878") {//En caso que sea el cliente SERVICIOS CORPORATIVOS GENESIS, se hace un trato especial y se deja pedir los toner que sea
            $hay_equipos = true;
            $maximos_toner = 1000000;
        }

        if ($toners_solicitados <= $maximos_toner) {//El numero de toner solicitados es menor o igual al permitido
            if ($hay_equipos) {/* Si hay equipos en la solicitud */
                /* En caso de que haya fecha de devolucion, la guardamos en la variable correspondiente */
                if ($parametros['tipo_solicitud'] == "4" || $parametros['tipo_solicitud'] == "5") {
                    if (isset($parametros['fecha_regreso']) && $parametros['fecha_regreso'] != "") {
                        $fecha_devolucion = "'" . $parametros['fecha_regreso'] . "'";
                    } else {
                        $fecha_devolucion = "null";
                    }
                } else {
                    $fecha_devolucion = "null";
                }

                /* Verificamos la forma de pago */
                if (isset($parametros['formas_pago']) && $parametros['formas_pago'] != "") {
                    $formasPago = $parametros['formas_pago'];
                } else {
                    $formasPago = "null";
                }
                /* Verificamos los dias de credito */
                if (isset($parametros['dias_credito']) && $parametros['dias_credito'] != "") {
                    $dias_credito = $parametros['dias_credito'];
                } else {
                    $dias_credito = "null";
                }
                /* Verificamos los dias de credito */
                if (isset($parametros['dias_revision']) && $parametros['dias_revision'] != "") {
                    $dias_revision = $parametros['dias_revision'];
                } else {
                    $dias_revision = "null";
                }
                /* Si la solicitud es para un cliente propio, se guarda el almacén hacia donde va la solicitud */
                if (isset($parametros['cliente_propio']) && $parametros['cliente_propio'] == "1") {
                    $idAlmacen = $parametros['almacen'];
                } else {
                    $idAlmacen = "null";
                }

                $query1 = $catalogo->obtenerLista("SELECT c_solicitud.id_solicitud AS ID,c_solicitud.estatus FROM c_solicitud
                    INNER JOIN k_solicitud ON k_solicitud.id_solicitud=c_solicitud.id_solicitud
                    WHERE c_solicitud.id_solicitud=" . $id_solicitud);
                $num_rows = mysql_num_rows($query1);
                $estatus_anterior = "0";
                while ($rs_aux = mysql_fetch_array($query1)) {
                    $estatus_anterior = $rs_aux['estatus'];
                }

                $i = 1;
                $enviar_correo = false;
                $cambio_equipo = false;

                for (; $i < $numero; $i++) {
                    if (isset($parametros['numero' . $i]) && isset($parametros['modelo' . $i])) {
                        if (isset($parametros[$parametros['localidad' . $i]])) {
                            $fila_cc = $parametros[$parametros['localidad' . $i]];
                            $localidad = "'" . $parametros['localidad' . $i] . "'";
                        } else {
                            $fila_cc = "";
                            $localidad = "null";
                        }

                        if (isset($parametros['servicio' . $fila_cc])) {
                            $datos_servicio = explode("-", $parametros['servicio' . $fila_cc]);
                        } else {
                            $datos_servicio = array();
                        }

                        if (!isset($datos_servicio[0]) || $datos_servicio[0] == "") {
                            $datos_servicio[0] = "null";
                        }
                        if (!isset($datos_servicio[1]) || $datos_servicio[0] == "") {
                            $datos_servicio[1] = "null";
                        }
                        if (isset($parametros['anexo' . $fila_cc]) && $parametros['anexo' . $fila_cc] != "") {
                            $anexo = $parametros['anexo' . $fila_cc];
                        } else {
                            $anexo = "null";
                        }
                        if ($i <= $num_rows) {
                            if ($parametros['tipo' . $i] == "0") {
                                $cambio_equipo = true;
                                $query2 = $catalogo->obtenerLista("SELECT k_solicitud.cantidad AS Cantidad,
                                    k_solicitud.ClaveCentroCosto AS Localidad,
                                    k_solicitud.Modelo AS Modelo,
                                    k_solicitud.TipoInventario
                                    FROM k_solicitud WHERE k_solicitud.id_solicitud='" . $id_solicitud . "' AND k_solicitud.id_partida='" . $i . "'");
                                $rs = mysql_fetch_array($query2);
                                if ($parametros['numero' . $i] != $rs['Cantidad'] || $parametros['modelo' . $i] != $rs['Modelo'] || $parametros['localidad' . $i] != $rs['Localidad'] || $parametros['tipo_inventario' . $i] != $rs['TipoInventario']) {
                                    $enviar_correo = true;
                                    $consulta = "UPDATE k_solicitud SET cantidad='" . $parametros['numero' . $i] . "',"
                                            . "Modelo='" . $parametros['modelo' . $i] . "',ClaveCentroCosto=$localidad,"
                                            . "tipo='" . $parametros['tipo' . $i] . "', IdAnexoClienteCC = " . $anexo . ", IdServicio = " . $datos_servicio[0] . ","
                                            . "NoSerie = null, TipoInventario = " . $parametros['tipo_inventario' . $i] . " ,IdKServicio = " . $datos_servicio[1] . ","
                                            . "UsuarioUltimaModificacion='" . $_SESSION['user'] . "',FechaUltimaModificacion=NOW() "
                                            . "WHERE k_solicitud.id_solicitud='" . $id_solicitud . "' AND k_solicitud.id_partida='" . $i . "'";
                                    $query2 = $catalogo->obtenerLista($consulta);
                                }
                            } else {
                                if (isset($parametros['serie_con_cliente' . $i]) && $parametros['serie_con_cliente' . $i] != "") {
                                    $serie = "'" . $parametros['serie_con_cliente' . $i] . "'";
                                } else {
                                    $serie = "null";
                                }
                                $consulta = "UPDATE k_solicitud SET cantidad='" . $parametros['numero' . $i] . "', "
                                        . "cantidad_autorizada = '" . $parametros['numero' . $i] . "', Modelo='" . $parametros['modelo' . $i] . "',"
                                        . "ClaveCentroCosto=$localidad,tipo='1',IdAnexoClienteCC = " . $anexo . ", IdServicio = " . $datos_servicio[0] . ","
                                        . "NoSerie = $serie ,TipoInventario = " . $parametros['tipo_inventario' . $i] . ", IdKServicio = " . $datos_servicio[1] . ","
                                        . "UsuarioUltimaModificacion='" . $_SESSION['user'] . "',"
                                        . "FechaUltimaModificacion=NOW() WHERE k_solicitud.id_solicitud='" . $id_solicitud . "' AND k_solicitud.id_partida='" . $i . "'";
                                //echo "Error: $consulta";
                                $query2 = $catalogo->obtenerLista($consulta);
                            }
                        } else {
                            if ($parametros['tipo' . $i] == "0") {
                                $enviar_correo = true;
                                $query2 = $catalogo->obtenerLista("INSERT INTO k_solicitud(id_solicitud,id_partida,cantidad,Modelo,ClaveCentroCosto,tipo,IdAnexoClienteCC,
                                IdServicio,IdKServicio,TipoInventario,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                VALUES('" . $id_solicitud . "','" . $i . "','" . $parametros['numero' . $i] . "','" . $parametros['modelo' . $i] . "',
                                $localidad,'" . $parametros['tipo' . $i] . "'," . $anexo . "," . $datos_servicio[0] . "," . $datos_servicio[1] . ",
                                 " . $parametros['tipo_inventario' . $i] . ",'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP edicion_solicitud.php');");
                            } else {
                                if (isset($parametros['serie_con_cliente' . $i]) && $parametros['serie_con_cliente' . $i] != "") {
                                    $serie = "'" . $parametros['serie_con_cliente' . $i] . "'";
                                } else {
                                    $serie = "null";
                                }
                                $query2 = $catalogo->obtenerLista("INSERT INTO k_solicitud(id_solicitud,id_partida,cantidad,cantidad_autorizada,Modelo,ClaveCentroCosto,
                                tipo,IdAnexoClienteCC,IdServicio,IdKServicio,TipoInventario,NoSerie,
                                UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                VALUES('" . $id_solicitud . "','" . $i . "','" . $parametros['numero' . $i] . "','" . $parametros['numero' . $i] . "',
                                '" . $parametros['modelo' . $i] . "',$localidad,'1'," . $anexo . "," . $datos_servicio[0] . "," . $datos_servicio[1] . ",
                                " . $parametros['tipo_inventario' . $i] . ",$serie,'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP edicion_solicitud.php');");
                            }
                        }
                    }
                }

                if ($num_rows >= $i) {
                    for (; $i <= $num_rows; $i++) {
                        $catalogo->obtenerLista("DELETE FROM k_solicitud WHERE k_solicitud.id_solicitud=" . $id_solicitud . " AND k_solicitud.id_partida=" . $i);
                    }
                }

                $query2 = $catalogo->obtenerLista("UPDATE c_solicitud SET estatus = 0, id_tiposolicitud = " . $parametros['tipo_solicitud'] . ", 
                          fecha_regreso = $fecha_devolucion, id_almacen = $idAlmacen, "
                        . "comentario='" . $parametro['comentario_normal'] . "',comentario_creo = '$comentario', ClaveCliente='" . $cliente . "',IdFormaPago=$formasPago,dias_credito=$dias_credito,dias_revision=$dias_revision "
                        . " WHERE c_solicitud.id_solicitud='" . $id_solicitud . "'");

                if (!$enviar_correo) {
                    $query2 = $catalogo->obtenerLista("UPDATE c_solicitud SET estatus = $estatus_anterior WHERE c_solicitud.id_solicitud='" . $id_solicitud . "'");
                } else {
                    /* Mandamos correo */
                    $query3 = $catalogo->obtenerLista("SELECT
                        c_solicitud.fecha_solicitud AS Fecha,
                        c_cliente.ClaveCliente AS ClaveCliente,
                        c_cliente.NombreRazonSocial AS Cliente,
                        c_cliente.IdEstatusCobranza,
                        c_cliente.RFC,
                        c_solicitud.id_solicitud AS ID,
                        k_solicitud.cantidad AS Cantidad,
                        k_solicitud.tipo AS Tipo,
                        k_solicitud.Modelo AS Modelo,
                        k_solicitud.NoSerie AS NoSerie,
                        c_tiposolicitud.Nombre AS TipoSolicitud,
                        c_formapago.Nombre AS formaPago,
                        c_tipoinventario.Nombre AS tipoInventario,
                        (SELECT CASE WHEN k_solicitud.tipo = 0 THEN (SELECT MAX(Modelo) FROM c_equipo WHERE NoParte = k_solicitud.Modelo) ELSE (SELECT MAX(Modelo) FROM c_componente WHERE NoParte = k_solicitud.Modelo) END) AS Modelo,
                        k_solicitud.ClaveCentroCosto AS Localidad
                        FROM c_solicitud
                        INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
                        INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
                        LEFT JOIN c_tiposolicitud ON c_tiposolicitud.IdTipoMovimiento = c_solicitud.id_tiposolicitud
                        LEFT JOIN c_formapago ON c_formapago.IdFormaPago = c_solicitud.IdFormaPago
                        LEFT JOIN c_tipoinventario ON c_tipoinventario.idTipo = k_solicitud.TipoInventario
                        WHERE c_solicitud.id_solicitud =" . $id_solicitud . "
                        ORDER BY k_solicitud.id_partida");
                    //$cliente = "";
                    $texto = "<table border=\"1\">";
                    $texto .="<thead><tr><th>Cantidad</th><th>Modelo</th><th>Localidad</th><th>Estado del equipo/Equipo con cliente</th></tr></thead><tbody>";
                    $formasPago = "";
                    while ($rs = mysql_fetch_array($query3)) {
                        $query4 = $catalogo->obtenerLista("SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto='" . $rs['Localidad'] . "'");
                        $rsp = mysql_fetch_array($query4);
                        $estado = "";
                        if ($rs['Tipo'] == "0") {
                            $estado = $rs['tipoInventario'];
                        } else {//Si es componente
                            if (isset($rs['NoSerie']) && $rs['NoSerie'] != "") {//Si tiene número de serie asociado de un equipo en cliente
                                $estado = $rs['NoSerie'] . " (Con cliente)";
                            }
                        }
                        $texto.="<tr><td>" . $rs['Cantidad'] . "</td><td>" . $rs['Modelo'] . "</td><td>" . $rsp['Nombre'] . "</td><td>$estado</td></tr>";
                        $formasPago = $rs['formaPago'];
                        //echo "<tr><td>" . $rs['Cantidad'] . "</td><td>" . $rs['Modelo'] . "</td><td>" . $rsp['Nombre'] . "</td></tr>";
                    }
                    $texto .= "</tbody></table><br/>";
                    //apartados
                    mysql_data_seek($query3, 0);
                    $texto3 = "<h4>Apartados:</h4>";
                    $texto3.= "<table border=\"1\"><thead><tr><th>Cantidad</th><th>Modelo</th><th>Tipo</th><th>Cliente</th><th>Localidad</th></tr></thead><tbody>";
                    $val = true;
                    while ($rs = mysql_fetch_array($query3)) {
                        if ($rs['Tipo'] == 0) {
                            $query4 = $catalogo->obtenerLista("SELECT
                                k_almacenequipo.Apartado AS Apartado,
                                c_centrocosto.Nombre AS CentroCosto,
                                c_cliente.NombreRazonSocial AS Cliente,
                                COUNT(c_centrocosto.Nombre) AS Suma
                                FROM k_almacenequipo
                                INNER JOIN c_equipo ON c_equipo.NoParte = k_almacenequipo.NoParte
                                INNER JOIN c_centrocosto ON c_centrocosto.ClaveCentroCosto = k_almacenequipo.ClaveCentroCosto
                                INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                                WHERE
                                        c_equipo.Modelo = '" . $rs['Modelo'] . "'
                                AND k_almacenequipo.Apartado = 1 GROUP BY Cliente,CentroCosto");
                            while ($rsp = mysql_fetch_array($query4)) {
                                $val = false;
                                $texto3 .= "<tr><td>" . $rsp['Suma'] . "</td><td>" . $rs['Modelo'] . "</td><td>Equipo</td><td>" . $rsp['Cliente'] . "</td><td>" . $rsp['CentroCosto'] . "</td></tr>";
                            }
                        } else {
                            $query4 = $catalogo->obtenerLista("SELECT
                            k_almacencomponente.cantidad_apartados  AS Apartado,
                            k_almacencomponente.cantidad_existencia AS Existencias
                            FROM
                                    k_almacencomponente
                            INNER JOIN c_componente ON c_componente.NoParte = k_almacencomponente.NoParte
                            WHERE c_componente.Modelo='" . $rs['Modelo'] . "' AND k_almacencomponente.cantidad_apartados!=0");
                            while ($rsp = mysql_fetch_array($query4)) {
                                $val = false;
                                $texto3 .= "<tr><td>" . $rsp['Apartado'] . "</td><td>" . $rs['Modelo'] . "</td><td>Componente</td><td></td><td></td></tr>";
                            }
                        }
                    }
                    if ($val) {
                        $texto3 = "";
                    }
                    //existencias
                    mysql_data_seek($query3, 0);
                    $texto1 = "<h4>Existencias antes de autorizar:</h4>";
                    $texto1 .= "<table border=\"1\"><thead><tr><th>Cantidad</th><th>Modelo</th></tr></thead><tbody>";
                    $texto2 = "<h4>Existencias después de autorizar:</h4><br/>";
                    $texto2 .= "<table border=\"1\"><thead><tr><th>Cantidad</th><th>Modelo</th></tr></thead><tbody>";
                    $modelo_equipo = Array();
                    $modelo_compo = Array();
                    $cantidad_equipo = Array();
                    $cantidad_compo = Array();
                    $contador_equipo = 0;
                    $contador_compo = 0;
                    $val = true;
                    while ($rs = mysql_fetch_array($query3)) {
                        if ($rs['Tipo'] == 0) {
                            $query4 = $catalogo->obtenerLista("SELECT COUNT(*) AS Cuenta FROM k_almacenequipo 
                        INNER JOIN c_equipo ON c_equipo.NoParte=k_almacenequipo.NoParte
                        WHERE c_equipo.Modelo='" . $rs['Modelo'] . "' AND (k_almacenequipo.Apartado!=1 OR ISNULL(k_almacenequipo.Apartado))");
                            $rsp = mysql_fetch_array($query4);
                            $texto1 .= "<tr><td>" . $rsp['Cuenta'] . "</td><td>" . $rs['Modelo'] . "</td></tr>";
                            $cantidad = $rsp['Cuenta'] - $rs['Cantidad'];
                            if ($contador_equipo == 0) {
                                $modelo_equipo[$contador_equipo] = $rs['Modelo'];
                                $cantidad_equipo[$contador_equipo] = $cantidad;
                            } else {
                                $i = 0;
                                foreach ($modelo_equipo as $value) {
                                    if ($value == $rs['Modelo']) {
                                        $cantidad = $cantidad_equipo[$i] - $rs['Cantidad'];
                                    }
                                    $i++;
                                }
                                $modelo_equipo[$contador_equipo] = $rs['Modelo'];
                                $cantidad_equipo[$contador_equipo] = $cantidad;
                            }
                            $contador_equipo++;
                            $texto2 .="<tr><td>" . $cantidad . "</td><td>" . $rs['Modelo'] . "</td></tr>";
                            $val = false;
                        } else {
                            $query4 = $catalogo->obtenerLista("SELECT
                        (k_almacencomponente.cantidad_existencia-k_almacencomponente.cantidad_apartados)  AS Cuenta
                        FROM
                                k_almacencomponente
                        INNER JOIN c_componente ON c_componente.NoParte = k_almacencomponente.NoParte
                        WHERE c_componente.Modelo='" . $rs['Modelo'] . "'");
                            $rsp = mysql_fetch_array($query4);
                            if ($rsp['Cuenta'] != null) {
                                $cuenta = $rsp['Cuenta'];
                            } else {
                                $cuenta = "0";
                            }
                            $texto1 .= "<tr><td>" . $cuenta . "</td><td>" . $rs['Modelo'] . "</td></tr>";
                            $cantidad = 0;
                            if ($rsp['Cuenta'] == null) {
                                $cantidad = (-1) * $rs['Cantidad'];
                            } else {
                                $cantidad = $rsp['Cuenta'] - $rs['Cantidad'];
                            }
                            if ($contador_compo == 0) {
                                $modelo_compo[$contador_compo] = $rs['Modelo'];
                                $cantidad_compo[$contador_compo] = $cantidad;
                            } else {
                                $i = 0;
                                foreach ($modelo_compo as $value) {
                                    if ($value == $rs['Modelo']) {
                                        $cantidad = $cantidad_compo[$i] - $rs['Cantidad'];
                                    }
                                    $i++;
                                }
                                $modelo_compo[$contador_compo] = $rs['Modelo'];
                                $cantidad_compo[$contador_compo] = $cantidad;
                            }
                            $contador_compo++;
                            $texto2 .="<tr><td>" . $cantidad . "</td><td>" . $rs['Modelo'] . "</td></tr>";
                            $val = false;
                        }
                    }
                    $texto1 .= "</tbody></table><br/>";
                    $texto2 .= "</tbody></table><br/>";
                    if ($val) {
                        $texto1 = "";
                        $texto2 = "";
                    }
                    if ($texto3 != "") {
                        $texto3 .= "</tbody></table><br/>";
                    }
                    mysql_data_seek($query3, 0);
                    $texto .= $texto1 . $texto3 . "<br/>" . "<br/>" . $texto2;
                    $rs = mysql_fetch_array($query3);
                    $mail = new Mail();
                    if ($parametroGlobal->getRegistroById("8")) {
                        $mail->setFrom($parametroGlobal->getValor());
                    } else {
                        $mail->setFrom("scg-salida@scgenesis.mx");
                    }
                    $mail->setSubject("No Solicitud: " . $id_solicitud);
                    $message = "<html><body>";
                    $usuario = new Usuario();
                    $usuario->getRegistroById($_SESSION['idUsuario']);
                    $message .= "<h3>Hay una solicitud de equipo (modificada) de tipo <font color=red>" . $rs['TipoSolicitud'] . "</font> del usuario:</h3><h4>" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . "</h4>";
                    if ($fecha_devolucion != "null") {
                        $message .= "<h4>Con fecha de devolución " . $catalogo->formatoFechaReportes($parametros['fecha_regreso']) . "</h4><br/>";
                    }
                    $message = $message . "<br/><b>Comentario de quien generó la solicitud</b>:<br/> " . $comentario . "<br/><br/>";
                    if ($rs['IdEstatusCobranza'] != "2") {
                        $message .= "<h3>Para el cliente:</h3><h4>" . $rs['Cliente'] . "</h4>";
                    } else {
                        include_once("../../Classes/ReporteFacturacion.class.php");
                        $facturas = new ReporteFacturacion();
                        $facturas->setRfccliente($rs['RFC']);
                        $facturas->setStatus(1); /* Para que muestre solo las facturas no pagadas */
                        $result3 = $facturas->getTabla(false);
                        if (mysql_num_rows($result3) > 0) {
                            $facturas_pendientes = "<table border=\"1\"><thead><tr><th>Folio</th><th>Fecha Facturación</th><th>Total</th></tr></thead><tbody>";
                            while ($rs3 = mysql_fetch_array($result3)) {
                                $facturas_pendientes.= "<tr>";
                                $facturas_pendientes.= "<td align='center' scope='row'>" . $rs3['Folio'] . "</td>";
                                $facturas_pendientes.= "<td align='center' scope='row'>" . $rs3['FechaFacturacion'] . "</td>";
                                $facturas_pendientes.= "<td align='center' scope='row'>$" . $rs3['Total'] . "</td>";
                                $facturas_pendientes.= "</tr>";
                            }
                            $facturas_pendientes.="</tbody></table>";
                            $message .= "<h3>Para el <font color=red>cliente moroso:</font></h3><h4>" . $rs['Cliente'] . "</h4><b>Con las facturas pendientes:</b><br/>$facturas_pendientes<br/><br/>";
                        } else {
                            $message .= "<h3>Para el <font color=red>cliente moroso:</font></h3><h4>" . $rs['Cliente'] . "</h4>";
                        }
                    }


                    /* Obtenemos los correos a quien mandaremos el mail */
                    $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 0;");
                    $correos = array();
                    $z = 0;
                    while ($rs = mysql_fetch_array($query4)) {
                        $correos[$z] = $rs['correo'];
                        $z++;
                    }

                    $prefijos = array(); /* prefijos de las tablas de servicios */
                    $prefijos[0] = "fa";
                    $prefijos[1] = "gfa";
                    $prefijos[2] = "im";
                    $prefijos[3] = "gim";
                    $message_contrato = "";

                    $datos_servicio_unico = array();
                    $nombre_servicios_localidades = array();
                    /* Quitamos los datos de contratos-servicio repetidos */
                    for ($fila = 0; $fila < $parametros['numero_contratos']; $fila++) {
                        $cc = new CentroCosto();
                        $cc->getRegistroById($parametros['fila' . $fila]);
                        if (!in_array($parametros['servicio' . $fila], $datos_servicio_unico)) {//Si aun no esta guardado en el array, lo guardamos
                            $datos_servicio_unico[$fila] = $parametros['servicio' . $fila];
                            $nombre_servicios_localidades[$fila] = $cc->getNombre();
                        } else {//Si ya existe, concatenamos el nombre de la localidad.
                            $index = array_search($parametros['servicio' . $fila], $datos_servicio_unico);
                            $nombre_servicios_localidades[$index].= (", " . $cc->getNombre());
                        }
                    }

                    foreach ($datos_servicio_unico as $key => $value) {
                        $message .= "<br/> ".$datos_servicio[0].", ".$datos_servicio[1];
                        $datos_servicio = explode("-", $value);
                        $fila = $key;
                        if (isset($datos_servicio[0]) && isset($datos_servicio[1])) {
                            if (intval($datos_servicio[0]) > 0 && intval($datos_servicio[0]) < 100) {
                                $prefijo_actual = "fa";
                                $select = "fa.RentaMensual AS faRenta, fa.MLIncluidosBN AS faincluidosBN, fa.MLIncluidosColor AS faincluidosColor, fa.CostoMLExcedentesBN AS faExcedentesBN,
                            fa.CostoMLExcedentesColor AS faExcedentesColor, fa.CostoMLProcesadosBN AS faProcesadasBN, fa.CostoMLProcesadosColor AS faProcesadosColor";
                                $join = "LEFT JOIN k_serviciofa AS fa ON fa.IdAnexoClienteCC = kacc.IdAnexoClienteCC";
                                $condicion = " fa.IdServicioFA = " . $datos_servicio[0] . " AND fa.IdKServicioFA = " . $datos_servicio[1] . " AND ";
                            } else if (intval($datos_servicio[0]) >= 100 && intval($datos_servicio[0]) < 1000) {
                                $prefijo_actual = "im";
                                $select = "im.RentaMensual AS imRenta, im.PaginasIncluidasBN AS imincluidosBN, im.PaginasIncluidasColor AS imincluidosColor, im.CostoPaginasExcedentesBN AS imExcedentesBN,
                            im.CostoPaginasExcedentesColor AS imExcedentesColor, im.CostoPaginaProcesadaBN AS imProcesadasBN, im.CostoPaginaProcesadaColor AS imProcesadosColor";
                                $join = "LEFT JOIN k_servicioim AS im ON im.IdAnexoClienteCC = kacc.IdAnexoClienteCC";
                                $condicion = " im.IdServicioIM = " . $datos_servicio[0] . " AND im.IdKServicioIM = " . $datos_servicio[1] . " AND ";
                            } else if (intval($datos_servicio[0]) >= 1001 && intval($datos_servicio[0]) < 1050) {
                                $prefijo_actual = "gfa";
                                $select = "gfa.RentaMensual AS gfaRenta, gfa.MLIncluidosBN AS gfaincluidosBN, gfa.MLIncluidosColor AS gfaincluidosColor, gfa.CostoMLExcedentesBN AS gfaExcedentesBN,
                            gfa.CostoMLExcedentesColor AS gfaExcedentesColor, gfa.CostoMLProcesadosBN AS gfaProcesadasBN, gfa.CostoMLProcesadosColor AS gfaProcesadosColor";
                                $join = "LEFT JOIN k_serviciogfa AS gfa ON gfa.IdAnexoClienteCC = kacc.IdAnexoClienteCC";
                                $condicion = " gfa.IdServicioGFA = " . $datos_servicio[0] . " AND gfa.IdKServicioGFA = " . $datos_servicio[1] . " AND ";
                            } else {
                                $prefijo_actual = "gim";
                                $select = "gim.RentaMensual AS gimRenta, gim.PaginasIncluidasBN AS gimincluidosBN, gim.PaginasIncluidasColor AS gimincluidosColor, gim.CostoPaginasExcedentesBN AS gimExcedentesBN,
                            gim.CostoPaginasExcedentesColor AS gimExcedentesColor, gim.CostoPaginaProcesadaBN AS gimProcesadasBN, gim.CostoPaginaProcesadaColor AS gimProcesadosColor";
                                $join = "LEFT JOIN k_serviciogim AS gim ON gim.IdAnexoClienteCC = kacc.IdAnexoClienteCC";
                                $condicion = " gim.IdServicioGIM = " . $datos_servicio[0] . " AND gim.IdKServicioGIM = " . $datos_servicio[1] . " AND ";
                            }

                            /* Obtenemos los contratos vigentes del cliente */
                            /* $consulta = "SELECT 
                              DATE(c.FechaInicio) AS FechaInicio, DATE(c.FechaTermino) AS FechaTermino,
                              period_diff(date_format(c.FechaTermino, '%Y%m'), date_format(c.FechaInicio, '%Y%m')) as meses_forzosos,
                              fa.RentaMensual AS faRenta, fa.MLIncluidosBN AS faincluidosBN, fa.MLIncluidosColor AS faincluidosColor, fa.CostoMLExcedentesBN AS faExcedentesBN,
                              fa.CostoMLExcedentesColor AS faExcedentesColor, fa.CostoMLProcesadosBN AS faProcesadasBN, fa.CostoMLProcesadosColor AS faProcesadosColor,
                              gfa.RentaMensual AS gfaRenta, gfa.MLIncluidosBN AS gfaincluidosBN, gfa.MLIncluidosColor AS gfaincluidosColor, gfa.CostoMLExcedentesBN AS gfaExcedentesBN,
                              gfa.CostoMLExcedentesColor AS gfaExcedentesColor, gfa.CostoMLProcesadosBN AS gfaProcesadasBN, gfa.CostoMLProcesadosColor AS gfaProcesadosColor,
                              im.RentaMensual AS imRenta, im.PaginasIncluidasBN AS imincluidosBN, im.PaginasIncluidasColor AS imincluidosColor, im.CostoPaginasExcedentesBN AS imExcedentesBN,
                              im.CostoPaginasExcedentesColor AS imExcedentesColor, im.CostoPaginaProcesadaBN AS imProcesadasBN, im.CostoPaginaProcesadaColor AS imProcesadosColor,
                              gim.RentaMensual AS gimRenta, gim.PaginasIncluidasBN AS gimincluidosBN, gim.PaginasIncluidasColor AS gimincluidosColor, gim.CostoPaginasExcedentesBN AS gimExcedentesBN,
                              gim.CostoPaginasExcedentesColor AS gimExcedentesColor, gim.CostoPaginaProcesadaBN AS gimProcesadasBN, gim.CostoPaginaProcesadaColor AS gimProcesadosColor
                              FROM `c_contrato` AS c
                              LEFT JOIN c_anexotecnico AS ant ON ant.NoContrato = c.NoContrato
                              LEFT JOIN k_anexoclientecc AS kacc ON kacc.ClaveAnexoTecnico = ant.ClaveAnexoTecnico
                              LEFT JOIN k_serviciofa AS fa ON fa.IdAnexoClienteCC = kacc.IdAnexoClienteCC
                              LEFT JOIN k_serviciogfa AS gfa ON gfa.IdAnexoClienteCC = kacc.IdAnexoClienteCC
                              LEFT JOIN k_servicioim AS im ON im.IdAnexoClienteCC = kacc.IdAnexoClienteCC
                              LEFT JOIN k_serviciogim AS gim ON gim.IdAnexoClienteCC = kacc.IdAnexoClienteCC
                              WHERE $condicion c.ClaveCliente = '$cliente';"; */
                            $consulta = "SELECT 
                            DATE(c.FechaInicio) AS FechaInicio, DATE(c.FechaTermino) AS FechaTermino, 
                            period_diff(date_format(c.FechaTermino, '%Y%m'), date_format(c.FechaInicio, '%Y%m')) as meses_forzosos,
                            $select 
                            FROM `c_contrato` AS c
                            LEFT JOIN c_anexotecnico AS ant ON ant.NoContrato = c.NoContrato
                            LEFT JOIN k_anexoclientecc AS kacc ON kacc.ClaveAnexoTecnico = ant.ClaveAnexoTecnico
                            $join 
                            WHERE $condicion c.ClaveCliente = '$cliente';";
                            $query3 = $catalogo->obtenerLista($consulta);
                            $num_contratos = mysql_num_rows($query3);
                            if ($num_contratos > 0) {
                                if ($rResult = mysql_fetch_array($query3)) {
                                    $rentaMensual = 0;
                                    $incluidasBN = 0;
                                    $incluidasColor = 0;
                                    $excedenteBN = 0;
                                    $excedenteColor = 0;
                                    $procesadasBN = 0;
                                    $procesadasColor = 0;

                                    //foreach ($prefijo_actual as $value) {/* Buscamos la renta mensual */
                                    if ($rResult[$prefijo_actual . "Renta"] != null) {
                                        $rentaMensual = $rResult[$prefijo_actual . "Renta"];
                                        //break;
                                    }
                                    //}
                                    //foreach ($prefijo_actual as $value) {/* Buscamos incluidos B/N */
                                    if ($rResult[$prefijo_actual . "incluidosBN"] != null) {
                                        $incluidasBN = $rResult[$prefijo_actual . "incluidosBN"];
                                        //break;
                                    }
                                    //}
                                    //foreach ($prefijo_actual as $value) {/* Buscamos incluidos Color */
                                    if ($rResult[$prefijo_actual . "incluidosColor"] != null) {
                                        $incluidasColor = $rResult[$prefijo_actual . "incluidosColor"];
                                        //break;
                                    }
                                    //}
                                    //foreach ($prefijo_actual as $value) {/* Buscamos excedentes b/n */
                                    if ($rResult[$prefijo_actual . "ExcedentesBN"] != null) {
                                        $excedenteBN = $rResult[$prefijo_actual . "ExcedentesBN"];
                                        //break;
                                    }
                                    //}
                                    //foreach ($prefijo_actual as $value) {/* Buscamos excedentes color */
                                    if ($rResult[$prefijo_actual . "ExcedentesColor"] != null) {
                                        $excedenteColor = $rResult[$prefijo_actual . "ExcedentesColor"];
                                        //break;
                                    }
                                    //}
                                    //foreach ($prefijo_actual as $value) {/* Buscamos procesadas b/n */
                                    if ($rResult[$prefijo_actual . "ProcesadasBN"] != null) {
                                        $procesadasBN = $rResult[$prefijo_actual . "ProcesadasBN"];
                                        //break;
                                    }
                                    //}
                                    //foreach ($prefijo_actual as $value) {/* Buscamos procesadas color */
                                    if ($rResult[$prefijo_actual . "ProcesadosColor"] != null) {
                                        $procesadasColor = $rResult[$prefijo_actual . "ProcesadosColor"];
                                        //break;
                                    }
                                    //}
                                    /* $cc = new CentroCosto();
                                      $cc->getRegistroById($rResult['CveEspClienteCC']); */
                                    $message_contrato .= "<table border=\"1\">";
                                    $message_contrato .= "<tr><td colspan='4' style='text-align: center;'>CONDICIONES DE OPERACIÓN DEL ARRENDAMIENTO (" . $nombre_servicios_localidades[$key] . ")</td></tr>";
                                    $message_contrato .= "<tr><td><b>MESES FORZOSOS</b></td><td>" . $rResult['meses_forzosos'] . "</td><td><b>IMPORTE RENTA BASE MENSUAL</b></td><td> $" . $rentaMensual . "</td></tr>";
                                    $message_contrato .= "<tr><td><b>FECHA DE INICIO DEL CONTRATO</b></td><td>" . $rResult['FechaInicio'] . "</td><td>PAGINAS INCLUIDAS B/N</td><td>" . $incluidasBN . "</td></tr>";
                                    $message_contrato .= "<tr><td><b>FECHA DE TERMINO DEL CONTRATO</b></td><td>" . $rResult['FechaTermino'] . "</td><td>PAGINAS INCLUIDAS COLOR</td><td>" . $incluidasColor . "</td></tr>";
                                    if ($dias_credito == "null") {
                                        $dias_credito = "";
                                    }
                                    if ($dias_revision == "null") {
                                        $dias_revision = "";
                                    }
                                    $message_contrato .= "<tr><td><b>DIAS DE CREDITO</b></td><td>$dias_credito</td><td>PRECIO PAGINAS EXCEDENTES EN B/N</td><td> $" . $excedenteBN . "</td></tr>";
                                    $message_contrato .= "<tr><td><b>FORMA DE PAGO</b></td><td>$formasPago</td><td>PRECIO PAGINAS EXCEDENTES EN COLOR</td><td> $" . $excedenteColor . "</td></tr>";
                                    $message_contrato .= "<tr><td><b>DIAS DE REVISION DE FACTURAS</b></td><td>$dias_revision</td><td>PRECIO PAGINAS PROCESADAS EN B/N (CLICK)</td><td> $" . $procesadasBN . "</td></tr>";
                                    $message_contrato .= "<tr><td></td><td></td><td>PRECIO PAGINAS PROCESADAS EN COLOR</td><td> $" . $procesadasColor . "</td></tr>";
                                    $message_contrato .= "</table><br/><br/>";
                                }
                            } else {
                                $message_contrato .= "No se pudieron leer los datos del contrato.<br/><br/>";
                            }
                        } else {
                            /* $cc = new CentroCosto();
                              $cc->getRegistroById($parametros['fila'.$fila]); */
                            $message_contrato .= "No se específico ningún contrato o servicio para la(s) localidad(es) " . $nombre_servicios_localidades[$key] . ".<br/><br/>";
                        }
                    }

                    $message .= $message_contrato;
                    $message .= $texto;

                    /* Guardamos y creamos la liga para aceptar/rechazar la solicitud directamente */
                    $clave = $mail->generaPass();
                    $liga = $_SESSION['ip_server'] . "/acepta_solicitud.php?clv=$clave&soli=$id_solicitud&tipo";

                    $catalogo->insertarRegistro("INSERT INTO c_mailsolicitud(id_solicitud, contestada, clave, IdUsuario, Activo, UsuarioCreacion,FechaCreacion,
                UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) VALUES($id_solicitud,0,MD5('$clave'),2,1,'" . $_SESSION['user'] . "',now(),'" . $_SESSION['user'] . "',now(),'nueva_solicitud.php');");

                    $message = $message . "<br/>Autorizar solicitud: " . $liga . "=1&uguid=" . $_SESSION['idEmpresa'] . " <br/><br/>";
                    $message = $message . "<br/>Rechazar solicitud: " . $liga . "=3&uguid=" . $_SESSION['idEmpresa'] . " <br/><br/>";
                    $message = $message . "<br/>Para editar la solicitud, ingrese al sistema por favor: " . $_SESSION['ip_server'];
                    $message .= "</body></html>";
                    $mail->setBody($message);
                    foreach ($correos as $value) {
                        if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                            $mail->setTo($value);
                            if ($mail->enviarMail() == "1") {
                                echo "Un correo fue enviado para la autorización.";
                            } else {
                                echo "<br/>Error: No se pudo enviar el correo para autorizar.";
                            }
                        }
                    }
                }
            } else {
                echo "<br/>Error: Necesitas elegir al menos un equipo para modificar la solicitud.<br/><br/>";
            }
        } else {/* Los toner solicitados son mayores a los permitidos */
            echo "<br/>Error: Para esta solicitud solo puedes pedir $maximos_toner toner(s). ($maximo_negro para equipos B/N y $maximo_color para equipos de color)<br/><br/>";
        }
    }
}
?>

<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Mail.class.php");
include_once("../../Classes/Usuario.class.php");
include_once("../../Classes/Presolicitud.class.php");
include_once("../../Classes/Configuracion.class.php");
include_once("../../Classes/Contrato.class.php");
include_once("../../Classes/CentroCosto.class.php");
include_once("../../Classes/Anexo.class.php");
include_once("../../Classes/Inventario.class.php");
include_once("../../Classes/MovimientoEquipo.class.php");
include_once("../../Classes/ParametroGlobal.class.php");
$parametroGlobal = new ParametroGlobal();
$catalogo = new Catalogo();
$movimiento = new MovimientoEquipo();
$parametros;
if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}
$comentario = "";
if (isset($parametros['comentario']) && $parametros['comentario'] != null) {
    $comentario = $parametros['comentario'];
}
$id_reporte_historicos = $catalogo->insertarRegistro("INSERT INTO reportes_historicos(UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
VALUES('" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP cambie_equipo2.php');");
$tipo = $parametros['movloc'];
$nseries = $_SESSION['nseries'];
$contador = 1;
$contadorDia = 1; //Variable que auxiliara para ver si ya se ha hecho otra presolicitud para ese mismo día y poder diferenciarla
$id_lectura = "";
$demo = 2;
$despues = false;
$presolicitud = new Presolicitud();
foreach ($nseries as $value) {
    $NivelTN = "null";
    $NivelTC = "null";
    $NivelTM = "null";
    $NivelTA = "null";
    if (isset($parametros['NivelTA' . $contador]) && $parametros['NivelTA' . $contador] != "") {
        $NivelTA = "'" . $parametros['NivelTA' . $contador] . "'";
    }
    if (isset($parametros['NivelTM' . $contador]) && $parametros['NivelTM' . $contador] != "") {
        $NivelTM = "'" . $parametros['NivelTM' . $contador] . "'";
    }
    if (isset($parametros['NivelTC' . $contador]) && $parametros['NivelTC' . $contador] != "") {
        $NivelTC = "'" . $parametros['NivelTC' . $contador] . "'";
    }
    if (isset($parametros['NivelTN' . $contador]) && $parametros['NivelTN' . $contador] != "") {
        $NivelTN = "'" . $parametros['NivelTN' . $contador] . "'";
    }
    if (isset($parametros['contadorcl' . $contador])) {
        if (isset($parametros['contadorclml' . $contador]) && isset($parametros['contadorbnml' . $contador])) {
            $id_lectura = $catalogo->insertarRegistro("INSERT INTO c_lectura(NoSerie,Fecha,ContadorBNPaginas,ContadorColorPaginas,ContadorBNML,ContadorColorML,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,Pantalla,NivelTonNegro,NivelTonCian,NivelTonMagenta,NivelTonAmarillo,Activo,FechaUltimaModificacion)
                VALUES('" . $value . "','" . $parametros['fecha' . $contador] . "','" . $parametros['contadorbn' . $contador] . "','" . $parametros['contadorcl' . $contador] . "','" . $parametros['contadorbnml' . $contador] . "','" . $parametros['contadorclml' . $contador] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "','ASP.operacion_altalectura_aspx'," . $NivelTN . "," . $NivelTC . "," . $NivelTM . "," . $NivelTA . ",1,NOW());");
        } else {
            $id_lectura = $catalogo->insertarRegistro("INSERT INTO c_lectura(NoSerie,Fecha,ContadorBNPaginas,ContadorColorPaginas,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,Pantalla,NivelTonNegro,NivelTonCian,NivelTonMagenta,NivelTonAmarillo,Activo,FechaUltimaModificacion)
                VALUES('" . $value . "','" . $parametros['fecha' . $contador] . "','" . $parametros['contadorbn' . $contador] . "','" . $parametros['contadorcl' . $contador] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "','ASP.operacion_altalectura_aspx'," . $NivelTN . "," . $NivelTC . "," . $NivelTM . "," . $NivelTA . ",1,NOW());");
        }
    } else {
        if (isset($parametros['contadorbnml' . $contador])) {
            $id_lectura = $catalogo->insertarRegistro("INSERT INTO c_lectura(NoSerie,Fecha,ContadorBNPaginas,ContadorBNML,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,Pantalla,NivelTonNegro,Activo,FechaUltimaModificacion)
                VALUES('" . $value . "','" . $parametros['fecha' . $contador] . "','" . $parametros['contadorbn' . $contador] . "','" . $parametros['contadorbnml' . $contador] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "','ASP.operacion_altalectura_aspx'," . $NivelTN . ",1,NOW());");
        } else {
            $id_lectura = $catalogo->insertarRegistro("INSERT INTO c_lectura(NoSerie,Fecha,ContadorBNPaginas,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,Pantalla,NivelTonNegro,Activo,FechaUltimaModificacion)
                VALUES('" . $value . "','" . $parametros['fecha' . $contador] . "','" . $parametros['contadorbn' . $contador] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "','ASP.operacion_altalectura_aspx'," . $NivelTN . ",1,NOW());");
        }
    }
    $contador++;

    if ($tipo == 2) {
        if ($parametros['tipomovimiento'] == $demo) {
            $inventario = new Inventario();
            $query = $catalogo->obtenerLista("SELECT cinv.NoSerie,cinv.NoParteEquipo AS NoParte,e.Modelo AS Modelo FROM c_inventarioequipo AS cinv 
                INNER JOIN c_equipo AS e ON e.NoParte=cinv.NoParteEquipo WHERE cinv.NoSerie='" . $value . "'");
            if ($rs = mysql_fetch_array($query)) {
                $modelo = $rs['Modelo'];
                $parte = $rs['NoParte'];
            } else {
                $query = $catalogo->obtenerLista("SELECT k.NoSerie AS noSerie,e.Modelo AS Modelo,e.NoParte AS NoParte FROM k_almacenequipo AS k
                    INNER JOIN c_equipo AS e ON e.NoParte=k.NoParte WHERE k.NoSerie='" . $value . "'");
                if ($rs = mysql_fetch_array($query)) {
                    $modelo = $rs['Modelo'];
                    $parte = $rs['NoParte'];
                }
            }
            $centrocosto = new CentroCosto();
            $centrocosto->getRegistroById($parametros['selectcliloc2']);
            $inventario->insertarInventarioValidando($value, $parte, "", $parametros['selectcliloc2'], $centrocosto->getClaveCliente(), $modelo, true);
            $query = $catalogo->obtenerLista("SELECT m.id_movimientos AS ID FROM movimientos_equipo AS m
                WHERE m.NoSerie='" . $value . "' ORDER BY ID DESC");
            if ($rs = mysql_fetch_array($query)) {
                $id_movimiento = $rs['ID'];
            }
            $query = $catalogo->insertarRegistro("INSERT INTO reportes_movimientos(id_reportes,id_movimientos) VALUES(" . $id_reporte_historicos . "," . $id_movimiento . ");");
        } else {
            $id_kanexo = $parametros['selectanexocli2'];
            $id_ccosto = $parametros['selectcliloc2'];

            $query = $catalogo->obtenerLista("SELECT
                (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCliente, 
                (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.ClaveCentroCosto FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto,
                (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.IdAnexoClienteCC ELSE ka.IdAnexoClienteCC END) AS IDkAnexo,
                c_equipo.NoParte AS NoParte,
                (CASE WHEN !ISNULL(cinv.ClaveEspKServicioFAIM) THEN cinv.ClaveEspKServicioFAIM ELSE 'NULL' END) AS IdServicio,
                (CASE WHEN !ISNULL(cinv.IdKServicio) THEN cinv.IdKServicio ELSE 'NULL' END) AS IdKServicio,
                (CASE WHEN !ISNULL(cinv.IdKserviciogimgfa) THEN cinv.IdKserviciogimgfa ELSE 'NULL' END) AS IdKserviciogimgfa,
                (CASE WHEN !ISNULL(cinv.IdAnexoClienteCC) THEN cinv.IdAnexoClienteCC ELSE 'NULL' END) AS IdAnexoClienteCC
                FROM `c_inventarioequipo` AS cinv
                LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
                LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
                LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
                LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
                LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
                LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
                WHERE cinv.NoSerie='" . $value . "';");
            if ($rs = mysql_fetch_array($query)) {
                $IdKServicio = "NULL";
                $IdServicio = "NULL";
                $IdAnexoClienteCC = "NULL";
                $idKServiciogimgfa = "NULL";
                if (isset($rs['IdKServicio']) && !empty($rs['IdKServicio'])) {
                    $IdKServicio = $rs['IdKServicio'];
                }

                if (isset($rs['IdServicio']) && !empty($rs['IdServicio'])) {
                    $IdServicio = $rs['IdServicio'];
                }

                if (isset($rs['IdAnexoClienteCC']) && !empty($rs['IdAnexoClienteCC'])) {
                    $IdAnexoClienteCC = $rs['IdAnexoClienteCC'];
                }

                if (isset($rs['IdKserviciogimgfa']) && !empty($rs['IdKserviciogimgfa'])) {
                    $idKServiciogimgfa = $rs['IdKserviciogimgfa'];
                }
                $claveclienteA = $rs['ClaveCliente'];
                $clavecentrocostoA = $rs['ClaveCentroCosto'];
                $clavekanexoA = $rs['IDkAnexo'];
                $NoParte = $rs['NoParte'];
                $query = $catalogo->obtenerLista("SELECT
                    k_anexoclientecc.IdAnexoClienteCC AS IDkAnexo,
                    c_centrocosto.ClaveCentroCosto AS ClaveCentroCosto,
                    c_cliente.ClaveCliente AS ClaveCliente
                    FROM c_usuario
                    INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                    INNER JOIN c_centrocosto ON c_centrocosto.ClaveCliente = c_cliente.ClaveCliente
                    INNER JOIN k_anexoclientecc ON c_centrocosto.ClaveCentroCosto = k_anexoclientecc.CveEspClienteCC
                    WHERE c_centrocosto.ClaveCentroCosto='" . $id_ccosto . "';");
                if ($rs = mysql_fetch_array($query)) {
                    $claveclienteN = $rs['ClaveCliente'];
                    $clavecentrocostoN = $id_ccosto;
                    $clavekanexoN = $id_kanexo;
                    $id_movimiento = $catalogo->insertarRegistro("INSERT INTO movimientos_equipo(NoSerie,clave_cliente_anterior,clave_centro_costo_anterior,
                        k_anexo_anterior,clave_cliente_nuevo,clave_centro_costo_nuevo,k_anexo_nuevo,tipo_movimiento,Fecha,UsuarioCreacion,
                        FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente,causa_movimiento,IdTipoMovimiento,id_lectura,
                        IdAnexoClienteCCAnterior,IdKserviciogimgfaAnterior,IdKServicioAnterior,IdServicioAnterior)
                        VALUES('" . $value . "','" . $claveclienteA . "','" . $clavecentrocostoA . "','" . $clavekanexoA . "','" .
                            $claveclienteN . "','" . $clavecentrocostoN . "','" . $clavekanexoN . "',5,NOW(),'" . $_SESSION['user'] . "',NOW(),'"
                            . $_SESSION['user'] . "',NOW(),'PHP arbol clientes',0,'" . $comentario . "','" . $parametros['tipomovimiento'] . "',"
                            . $id_lectura . ", " . $IdAnexoClienteCC . "," . $idKServiciogimgfa . "," . $IdKServicio . "," . $IdServicio . ");");
                    $query = $catalogo->insertarRegistro("INSERT INTO reportes_movimientos(id_reportes,id_movimientos)
                        VALUES(" . $id_reporte_historicos . "," . $id_movimiento . ");");
                    $conf = new Configuracion();
                    $servicio = explode("-", $parametros['selectcliserv2']);
                    $conf->setIdServicio($servicio[0]);
                    if (intval($servicio[0]) > 1000) {
                        $conf->setTipoServicio("0");
                    }
                    if (isset($servicio[1])) {
                        $conf->setIdKServicio($servicio[1]);
                    }
                    $conf->setNoSerie($value);
                    $conf->setClaveCentroCosto($clavecentrocostoN);
                    $conf->setTipoServicio("0");
                    $conf->setIdAnexoClienteCC($clavekanexoN);
                    $conf->setUsuarioCreacion($_SESSION['user']);
                    $conf->setUsuarioUltimaModificacion($_SESSION['user']);
                    $conf->setUbicacion("");
                    $conf->setNoParte($NoParte);
                    $conf->setPantalla("PHP Controller_Cambio_Equipo");
                    $conf->registrarInventario();
                    $query = $catalogo->obtenerLista("UPDATE c_inventarioequipo SET c_inventarioequipo.Pantalla='PHP movimientos_equipo.php',c_inventarioequipo.ClaveEspKServicioFAIM='" . $parametros['selectcliserv2'] . "',c_inventarioequipo.Activo=1,c_inventarioequipo.IdAnexoClienteCC='" . $clavekanexoN . "' WHERE c_inventarioequipo.NoSerie='" . $value . "'");
                } else {
                    echo "Ocurrio un error";
                }
            } else {
                //echo "Viene de un almacen";
                $claveclienteN = $parametros['selectcli2'];
                $clavecentrocostoN = $parametros['selectcliloc2'];
                $clavekanexoN = $id_kanexo;


                $query = $catalogo->obtenerLista("SELECT k_almacenequipo.id_almacen AS ID,k_almacenequipo.NoParte AS Parte FROM k_almacenequipo WHERE k_almacenequipo.NoSerie='" . $value . "'");
                $rs = mysql_fetch_array($query);
                $NoParte = $rs['Parte'];
                $id_movimiento = $catalogo->insertarRegistro("INSERT INTO movimientos_equipo(NoSerie,clave_cliente_nuevo,clave_centro_costo_nuevo,k_anexo_nuevo,almacen_anterior,tipo_movimiento,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente,causa_movimiento,IdTipoMovimiento,id_lectura)                
                    VALUES('" . $value . "','" . $claveclienteN . "','" . $clavecentrocostoN . "','" . $clavekanexoN . "','" . $rs['ID'] . "',2,'" . $parametros['fecha_mov'] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP arbol clientes',1,'" . $comentario . "','" . $parametros['tipomovimiento'] . "'," . $id_lectura . ");");
                $query = $catalogo->insertarRegistro("INSERT INTO reportes_movimientos(id_reportes,id_movimientos)
                    VALUES(" . $id_reporte_historicos . "," . $id_movimiento . ");");
                $query = $catalogo->obtenerLista("DELETE FROM k_almacenequipo WHERE k_almacenequipo.NoSerie='" . $value . "'");

                $query2 = $catalogo->obtenerLista("SELECT * FROM c_inventarioequipo WHERE c_inventarioequipo.NoSerie='" . $value . "';");
                $servicio = explode("-", $parametros['selectcliserv2']);
                if ($sr = mysql_fetch_array($query2)) {
                    $query = $catalogo->insertarRegistro("UPDATE c_inventarioequipo SET ClaveEspKServicioFAIM='" . $servicio[0] . "',IdKServicio='" . $servicio[1] . "',IdAnexoClienteCC='" . $id_kanexo . "',Activo=1,
             UsuarioUltimaModificacion='" . $_SESSION['idUsuario'] . "',FechaUltimaModificacion=NOW(),Pantalla='PHP movimientos_equipo.php' WHERE NoSerie = '" . $value . "';");
                } else {
                    $query3 = $catalogo->obtenerLista("SELECT k_almacenequipo.NoParte AS Parte FROM k_almacenequipo WHERE k_almacenequipo.NoSerie='" . $value . "'");
                    if ($srp = mysql_fetch_array($query3)) {
                        $NoParte = $srp['Parte'];
                        $query = $catalogo->insertarRegistro("INSERT INTO c_inventarioequipo(NoParteEquipo,NoSerie,ClaveEspKServicioFAIM,IdKServicio,IdAnexoClienteCC,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES('" . $srp['Parte'] . "','" . $value . "','" . $servicio[0] . "','" . $servicio[1] . "','" . $id_kanexo . "',1,'" . $_SESSION['idUsuario'] . "',NOW(),'" . $_SESSION['idUsuario'] . "',NOW(),'PHP movimientos_equipos.php');");
                    } else {
                        $query = $catalogo->insertarRegistro("INSERT INTO c_inventarioequipo(NoParteEquipo,NoSerie,ClaveEspKServicioFAIM,IdKServicio,IdAnexoClienteCC,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES(''," . $value . "','" . $servicio[0] . "','" . $servicio[1] . "','" . $id_kanexo . "',1,'" . $_SESSION['idUsuario'] . "',NOW(),'" . $_SESSION['idUsuario'] . "',NOW(),'PHP movimientos_equipos.php');");
                    }
                }
                $conf = new Configuracion();
                $servicio = explode("-", $parametros['selectlocserv2']);
                $conf->setIdServicio($servicio[0]);
                if (intval($servicio[0]) > 1000) {
                    $conf->setTipoServicio("0");
                }
                if (isset($servicio[1])) {
                    $conf->setIdKServicio($servicio[1]);
                }
                $conf->setNoSerie($value);
                $conf->setClaveCentroCosto($clavecentrocostoN);
                $conf->setTipoServicio("0");
                $conf->setIdAnexoClienteCC($clavekanexoN);
                $conf->setUsuarioCreacion($_SESSION['user']);
                $conf->setUsuarioUltimaModificacion($_SESSION['user']);
                $conf->setUbicacion("");
                $conf->setNoParte($NoParte);
                $conf->setPantalla("PHP Controller_Cambio_Equipo");
                $conf->registrarInventario();
            }
        }
    } else if ($tipo == 3) {
        if ($parametros['fecha_mov'] > date("Y-m-d")) {
            $despues = true;
            $presolicitud->presolici($parametros['comentario'], $parametros['selectalm'], $_SESSION['user'], 
                    $comentario, $parametros['tipomovimiento'], $id_lectura, $id_reporte_historicos, $_SESSION['idUsuario']
                    , $value, $parametros['fecha_mov']);
            $presolicitud->nuevoRegistro();
            echo "Se programó la solicitud de retiro para el día " . $catalogo->formatoFechaReportes($parametros['fecha_mov']) . " correctamente<br/>";
        } else {
            $movimiento->setCausa_Movimiento($parametros['comentario']);
            $movimiento->setIdalmacen($parametros['selectalm']);
            $movimiento->setUser($_SESSION['user']);
            $movimiento->setComentario($comentario);
            $movimiento->setTipomovimiento($parametros['tipomovimiento']);
            $movimiento->setLectura($id_lectura);
            $movimiento->setReporte_historico($id_reporte_historicos);
            $movimiento->setIdUsuario($_SESSION['idUsuario']);
            $movimiento->setSerie($value);
            $movimiento->setFecha($parametros['fecha_mov']);
            $movimiento->setId_lectura($id_lectura);
            $movimiento->cambiarAlmacen();
        }
    }
}

if ($tipo == 3 && $despues == false) {
    $movimiento->EnviarMailCli();
    $nseries = $movimiento->getSeriesAlm();
}

if (count($nseries) > 0 && $despues == false) {
    /**********************************************************CORREO************************************************************************ */
    $query = $catalogo->obtenerLista("SELECT reportes_historicos.NumReporte AS NumReporte,
	reportes_historicos.FechaCreacion AS Fecha
        FROM reportes_historicos WHERE reportes_historicos.NumReporte=" . $id_reporte_historicos . ";");
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
                    WHERE reportes_historicos.NumReporte=" . $id_reporte_historicos . " ORDER BY clave_centro_costo_anterior,almacen_anterior");
        $numero_filas = mysql_num_rows($queryprin);
        $usuario = new Usuario();
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
    if ($parametroGlobal->getRegistroById("8")) {
        $mail->setFrom($parametroGlobal->getValor());
    } else {
        $mail->setFrom("scg-salida@scgenesis.mx");
    }
    $mail->setSubject("No Movimiento: " . $id_reporte_historicos);
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
    echo "<a href='WEB-INF/Controllers/Ventas/Controller_Reporte_Historico.php?noSolicitud=" . $id_reporte_historicos . "' target='_blank' style='float: right;'>Generar Reporte</a>";
}
?>

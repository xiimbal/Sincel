<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['form'])) {
    header("Location: ../../index.php");
}

include_once("../../Classes/ParametroLectura.class.php");
include_once("../../Classes/Parametro_Concepto.class.php");
include_once("../../Classes/Parametro_Servicio.class.php");
include_once("../../Classes/Catalogo.class.php");
$obj = new ParametroLectura();

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

if (isset($parametros['anexo']) && $parametros['anexo'] != "") {

    $obj->setClaveAnexoTecnico($parametros['anexo']);

    if (isset($parametros['ver_equipos']) && $parametros['ver_equipos'] == 1) {
        $obj->setVer_equipo(1);
    } else {
        $obj->setVer_equipo(0);
    }
    if (isset($parametros['mostrar_area']) && $parametros['mostrar_area'] == 1) {
        $obj->setMostrar_area(1);
    } else {
        $obj->setMostrar_area(0);
    }

    if (isset($parametros['resal_perio']) && $parametros['resal_perio'] == 1) {
        $obj->setResaltar_periodo(1);
    } else {
        $obj->setResaltar_periodo(0);
    }

    if (isset($parametros['rentas_lecturas']) && $parametros['rentas_lecturas'] == 1) {
        $obj->setRentas_lecturas(1);
    } else {
        $obj->setRentas_lecturas(0);
    }

    if (isset($parametros['fact_adel']) && $parametros['fact_adel'] == 1) {
        $obj->setFactura_renta_adelantada(1);
    } else {
        $obj->setFactura_renta_adelantada(0);
    }

    if (isset($parametros['dir_rep']) && $parametros['dir_rep'] == 1) {
        $obj->setDir_reporte(1);
    } else {
        $obj->setDir_reporte(0);
    }

    if (isset($parametros['Dividir_Color']) && $parametros['Dividir_Color'] == 1) {
        $obj->setDividir_Color(1);
    } else {
        $obj->setDividir_Color(0);
    }

    if (isset($parametros['dividir_factura']) && $parametros['dividir_factura'] != "") {
        $obj->setDividir_factura($parametros['dividir_factura']);
    } else {
        $obj->setDividir_factura(0);
    }

    if (isset($parametros['agrupar_factura']) && $parametros['agrupar_factura'] != "") {
        $obj->setAgrupar_factura($parametros['agrupar_factura']);
    } else {
        $obj->setAgrupar_factura(0);
    }
    
    if (isset($parametros['Mostrar_Serie']) && $parametros['Mostrar_Serie'] == 1) {
        $obj->setMostrar_Serie(1);
    } else {
        $obj->setMostrar_Serie(0);
    }
    
    if (isset($parametros['Mostrar_Modelo']) && $parametros['Mostrar_Modelo'] == 1) {
        $obj->setMostrarModelo(1);
    } else {
        $obj->setMostrarModelo(0);
    }
    
    if (isset($parametros['MostrarLecturas']) && $parametros['MostrarLecturas'] == 1) {
        $obj->setMostrar_Lecturas(1);
    } else {
        $obj->setMostrar_Lecturas(0);
    }
    
    if (isset($parametros['MostrarLocalidad']) && $parametros['MostrarLocalidad'] == 1) {
        $obj->setMostrarLocalidad(1);
    } else {
        $obj->setMostrarLocalidad(0);
    }
    
    if (isset($parametros['HistoricoFacturacion']) && $parametros['HistoricoFacturacion'] == 1) {
        $obj->setHistoricoFacturacion(1);
    } else {
        $obj->setHistoricoFacturacion(0);
    }
    
    if (isset($parametros['FechaInstalacion']) && $parametros['FechaInstalacion'] == 1) {
        $obj->setFechaInstalacion(1);
    } else {
        $obj->setFechaInstalacion(0);
    }
    
    if (isset($parametros['Agrupar_Renta']) && $parametros['Agrupar_Renta'] == 1) {
        $obj->setAgrupar_Renta(1);
    } else {
        $obj->setAgrupar_Renta(0);
    }
    
    if (isset($parametros['periodo_factura']) && $parametros['periodo_factura'] == 1) {
        $obj->setMostrarPeriodo(1);
    } else {
        $obj->setMostrarPeriodo(0);
    }

    if (isset($parametros['MostrarImporteCero']) && $parametros['MostrarImporteCero'] == 1) {
        $obj->setMostrarImporteCero(1);
    } else {
        $obj->setMostrarImporteCero(0);
    }

    if (isset($parametros['MostrarEncabezadoServicio']) && $parametros['MostrarEncabezadoServicio'] == 1) {
        $obj->setMostrarEncabezadoServicio(1);
    } else {
        $obj->setMostrarEncabezadoServicio(0);
    }

    if (isset($parametros['Agrupar_Color']) && $parametros['Agrupar_Color'] == 1) {
        $obj->setAgrupar_Color(1);
    } else {
        $obj->setAgrupar_Color(0);
    }

    if (isset($parametros['obs_in_xml'])) {
        $obj->setObservaciones_dentro_xml($parametros['obs_in_xml']);
    } else {
        $obj->setObservaciones_dentro_xml("");
    }
    if (isset($parametros['obs_out_xml'])) {
        $obj->setObservaciones_fuera_xml($parametros['obs_out_xml']);
    } else {
        $obj->setObservaciones_fuera_xml("");
    }

    if (isset($parametros['num_orden'])) {
        $obj->setNumero_orden($parametros['num_orden']);
    } else {
        $obj->setNumero_orden("");
    }

    if (isset($parametros['num_prov'])) {
        $obj->setNumero_proveedor($parametros['num_prov']);
    } else {
        $obj->setNumero_proveedor("");
    }
    
    $obj->setIdProductoSATRenta($parametros['rentaSAT']);
    $obj->setIdProductoSATImpresion($parametros['impresionesSAT']);


    $concepto = new Parametro_Concepto();
    $concepto->setUsuarioCreacion($_SESSION['user']);
    $concepto->setUsuarioUltimaModificacion($_SESSION['user']);
    $concepto->setPantalla('PHP Parametros lectura');

    $servicio = new Parametro_Servicio();
    $servicio->setUsuarioCreacion($_SESSION['user']);
    $servicio->setUsuarioUltimaModificacion($_SESSION['user']);
    $servicio->setPantalla('PHP Parametros lectura');

    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setPantalla('PHP Parametros lectura');

    if (!$obj->getIDByClave($parametros['anexo'])) {
        if ($obj->newRegistro()) {
            //echo $obj->getId_parametro();
            $concepto->setId_parametro($obj->getId_parametro());
            $servicio->setId_parametro($obj->getId_parametro());
            $concepto->deletebyParametro($obj->getId_parametro());
            $servicio->deletebyParametro($obj->getId_parametro());
            $catalogo = new Catalogo();
            $query = $catalogo->obtenerLista("SELECT c.IdServicioFA AS Id_servicio FROM c_serviciofa AS c WHERE c.Activo=1");
            while ($rs = mysql_fetch_array($query)) {
                if (isset($parametros['check_serv_nom_' . $rs['Id_servicio']]) && $parametros['check_serv_nom_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(0);
                    $servicio->setDescripcion($parametros['text_serv_nom_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
                if (isset($parametros['check_serv_uni_' . $rs['Id_servicio']]) && $parametros['check_serv_uni_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(1);
                    $servicio->setRenta($parametros['text_serv_renta_' . $rs['Id_servicio']]);
                    $servicio->setExcedente($parametros['text_serv_excedente_' . $rs['Id_servicio']]);
                    $servicio->setImpresiones($parametros['text_serv_impresiones_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
            }
            $query = $catalogo->obtenerLista("SELECT c.IdServicioGFA AS Id_servicio FROM c_serviciogfa AS c WHERE c.Activo=1");
            while ($rs = mysql_fetch_array($query)) {
                if (isset($parametros['check_serv_nom_' . $rs['Id_servicio']]) && $parametros['check_serv_nom_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(0);
                    $servicio->setDescripcion($parametros['text_serv_nom_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
                if (isset($parametros['check_serv_uni_' . $rs['Id_servicio']]) && $parametros['check_serv_uni_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(1);
                    $servicio->setRenta($parametros['text_serv_renta_' . $rs['Id_servicio']]);
                    $servicio->setExcedente($parametros['text_serv_excedente_' . $rs['Id_servicio']]);
                    $servicio->setImpresiones($parametros['text_serv_impresiones_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
            }
            $query = $catalogo->obtenerLista("SELECT c.IdServicioGIM AS Id_servicio FROM c_serviciogim AS c WHERE c.Activo=1");
            while ($rs = mysql_fetch_array($query)) {
                if (isset($parametros['check_serv_nom_' . $rs['Id_servicio']]) && $parametros['check_serv_nom_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(0);
                    $servicio->setDescripcion($parametros['text_serv_nom_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
                if (isset($parametros['check_serv_uni_' . $rs['Id_servicio']]) && $parametros['check_serv_uni_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(1);
                    $servicio->setRenta($parametros['text_serv_renta_' . $rs['Id_servicio']]);
                    $servicio->setExcedente($parametros['text_serv_excedente_' . $rs['Id_servicio']]);
                    $servicio->setImpresiones($parametros['text_serv_impresiones_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
            }
            $query = $catalogo->obtenerLista("SELECT c.IdServicioIM AS Id_servicio FROM c_servicioim AS c WHERE c.Activo=1");
            while ($rs = mysql_fetch_array($query)) {
                if (isset($parametros['check_serv_nom_' . $rs['Id_servicio']]) && $parametros['check_serv_nom_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(0);
                    $servicio->setDescripcion($parametros['text_serv_nom_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
                if (isset($parametros['check_serv_uni_' . $rs['Id_servicio']]) && $parametros['check_serv_uni_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(1);
                    $servicio->setRenta($parametros['text_serv_renta_' . $rs['Id_servicio']]);
                    $servicio->setExcedente($parametros['text_serv_excedente_' . $rs['Id_servicio']]);
                    $servicio->setImpresiones($parametros['text_serv_impresiones_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
            }
            $filas_conceptos = $parametros['filas_conceptos'];
            if ($filas_conceptos > 0) {
                for ($i = 0; $i < $filas_conceptos; $i++) {
                    $concepto->setNivel_facturacion($parametros['select_con_adic_' . $i]);
                    $concepto->setDescripcion($parametros['text_con_adic_' . $i]);
                    $concepto->setCantidad($parametros['cantidad_con_adic_' . $i]);
                    $concepto->setPrecioUnitario($parametros['preciounitario_con_adic_' . $i]);
                    $concepto->setIdProductoSAT($parametros['producto_adic_' . $i]);
                    $concepto->newRegistro();
                }
            }
        } else {
            echo "Error:El parametro no se pudo modificar, intenta más tarde por favor";
        }
    } else {/* Modificar */
        if ($obj->editRegistro()) {
            //echo $obj->getId_parametro();
            $concepto->setId_parametro($obj->getId_parametro());
            $servicio->setId_parametro($obj->getId_parametro());
            $concepto->deletebyParametro($obj->getId_parametro());
            $servicio->deletebyParametro($obj->getId_parametro());
            $catalogo = new Catalogo();
            $query = $catalogo->obtenerLista("SELECT c.IdServicioFA AS Id_servicio FROM c_serviciofa AS c WHERE c.Activo=1");
            while ($rs = mysql_fetch_array($query)) {
                if (isset($parametros['check_serv_nom_' . $rs['Id_servicio']]) && $parametros['check_serv_nom_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(0);
                    $servicio->setDescripcion($parametros['text_serv_nom_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
                if (isset($parametros['check_serv_uni_' . $rs['Id_servicio']]) && $parametros['check_serv_uni_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(1);
                    $servicio->setRenta($parametros['text_serv_renta_' . $rs['Id_servicio']]);
                    $servicio->setExcedente($parametros['text_serv_excedente_' . $rs['Id_servicio']]);
                    $servicio->setImpresiones($parametros['text_serv_impresiones_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
            }
            $query = $catalogo->obtenerLista("SELECT c.IdServicioGFA AS Id_servicio FROM c_serviciogfa AS c WHERE c.Activo=1");
            while ($rs = mysql_fetch_array($query)) {
                if (isset($parametros['check_serv_nom_' . $rs['Id_servicio']]) && $parametros['check_serv_nom_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(0);
                    $servicio->setDescripcion($parametros['text_serv_nom_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
                if (isset($parametros['check_serv_uni_' . $rs['Id_servicio']]) && $parametros['check_serv_uni_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(1);
                    $servicio->setRenta($parametros['text_serv_renta_' . $rs['Id_servicio']]);
                    $servicio->setExcedente($parametros['text_serv_excedente_' . $rs['Id_servicio']]);
                    $servicio->setImpresiones($parametros['text_serv_impresiones_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
            }
            $query = $catalogo->obtenerLista("SELECT c.IdServicioGIM AS Id_servicio FROM c_serviciogim AS c WHERE c.Activo=1");
            while ($rs = mysql_fetch_array($query)) {
                if (isset($parametros['check_serv_nom_' . $rs['Id_servicio']]) && $parametros['check_serv_nom_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(0);
                    $servicio->setDescripcion($parametros['text_serv_nom_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
                if (isset($parametros['check_serv_uni_' . $rs['Id_servicio']]) && $parametros['check_serv_uni_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(1);
                    $servicio->setRenta($parametros['text_serv_renta_' . $rs['Id_servicio']]);
                    $servicio->setExcedente($parametros['text_serv_excedente_' . $rs['Id_servicio']]);
                    $servicio->setImpresiones($parametros['text_serv_impresiones_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
            }
            $query = $catalogo->obtenerLista("SELECT c.IdServicioIM AS Id_servicio FROM c_servicioim AS c WHERE c.Activo=1");
            while ($rs = mysql_fetch_array($query)) {
                if (isset($parametros['check_serv_nom_' . $rs['Id_servicio']]) && $parametros['check_serv_nom_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(0);
                    $servicio->setDescripcion($parametros['text_serv_nom_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
                if (isset($parametros['check_serv_uni_' . $rs['Id_servicio']]) && $parametros['check_serv_uni_' . $rs['Id_servicio']] == 1) {
                    $servicio->setId_servicio($rs['Id_servicio']);
                    $servicio->setTipo(1);
                    $servicio->setRenta($parametros['text_serv_renta_' . $rs['Id_servicio']]);
                    $servicio->setExcedente($parametros['text_serv_excedente_' . $rs['Id_servicio']]);
                    $servicio->setImpresiones($parametros['text_serv_impresiones_' . $rs['Id_servicio']]);
                    $servicio->newRegistro();
                }
            }
            $filas_conceptos = $parametros['filas_conceptos'];
            if ($filas_conceptos > 0) {
                for ($i = 0; $i < $filas_conceptos; $i++) {
                    $concepto->setNivel_facturacion($parametros['select_con_adic_' . $i]);
                    $concepto->setDescripcion($parametros['text_con_adic_' . $i]);
                    $concepto->setCantidad($parametros['cantidad_con_adic_' . $i]);
                    $concepto->setPrecioUnitario($parametros['preciounitario_con_adic_' . $i]);
                    $concepto->setIdProductoSAT($parametros['producto_adic_' . $i]);
                    $concepto->newRegistro();
                }
            }
        } else {
            echo "Error:El parámetro no se pudo registrar, intenta más tarde por favor";
        }
    }
}
?>
<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['form']) || !isset($_POST['tipo'])) {
    header("Location: ../../index.php");
}


include_once("../Classes/Movimiento.class.php");
include_once("../Classes/MovimientoComponente.class.php");
include_once("../Classes/Almacen.class.php");
include_once("../Classes/AlmacenEquipo.class.php");
include_once("../Classes/AlmacenConmponente.class.php");
include_once("../Classes/CentroCosto.class.php");
include_once("../Classes/Configuracion.class.php");
include_once("../Classes/Catalogo.class.php");
include_once("../Classes/Componente.class.php");
include_once("../Classes/Solicitud.class.php");
include_once("../Classes/SolicitudToner.class.php");
include_once("../Classes/Envios.class.php");
include_once("../Classes/Lectura.class.php");
include_once("../Classes/LecturaTicket.class.php");
include_once("../Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../Classes/Ticket.class.php");
include_once("../Classes/Cliente.class.php");
include_once("../Classes/Mensajeria.class.php");
include_once("../Classes/Vehiculo.class.php");
include_once("../Classes/Conductor.class.php");
include_once("../Classes/AgregarNota.class.php");
include_once("../Classes/Contrato.class.php");
include_once("../Classes/Anexo.class.php");
include_once("../Classes/Zona.class.php");
include_once("../Classes/Log.class.php");
include_once("../Classes/Incidencia.class.php");

$obj = new Configuracion();

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$pantalla = "Envio solicitud de equipo";
if ($_POST['tipo'] == "2") { /* Se hace una llamada para mover los equipos */
    $soli = new Solicitud();
    $soli->setId_solicitud($parametros['id_solicitud']);
    $envio2 = new Envios(); 
    
    /*if ($soli->todosEquiposAsignados($parametros['id_solicitud']) && 
            ($envio2->todosEquiposEnviados($parametros['id_solicitud']) && $envio2->todosComponentesEnviados($parametros['id_solicitud']))){*/
    if ( ($soli->todosEquiposAsignados($parametros['id_solicitud'])) && 
        (
           $envio2->todosEquiposEnviados($parametros['id_solicitud']) && 
           $envio2->todosComponentesEnviados($parametros['id_solicitud'])
        )
       ){
        $soli->cambiarEstatusSolicitud(5);
        echo "<br/>La solictud ha sido marcada como surtida ya que todos los equipos y/o componentes fueron atendidos<br/>";       
    } else {/* Todavia hay equipos en almacen */
        $hay_error = false;
        $tienen_anexo = true; //Verifica si todos los cc tienen un anexo asociado
        $sin_anexo = array(); //cc que no tiene anexo
        $anexos_cc = array(); //guardamos los anexos de cada cc
        $hay_seleccionados = false; //Para saber si hay series seleccionadas.
        $no_en_almacen = false;
        $consulta = "SELECT DISTINCT(ks.ClaveCentroCosto) AS ClaveCentroCosto, cc.Nombre FROM k_solicitud AS ks INNER JOIN c_centrocosto AS cc ON ks.ClaveCentroCosto = cc.ClaveCentroCosto WHERE id_solicitud = " . $parametros['id_solicitud'] . " ORDER BY Nombre;";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);        
        
        /* Recorremos todas las localidades de esta solicitud */
        while ($rs = mysql_fetch_array($result)) {
            $cc_objeto = new CentroCosto();
            $cc_objeto->getRegistroById($rs['ClaveCentroCosto']);
            $consulta = "SELECT c.NoContrato, cat.ClaveAnexoTecnico, kacc.IdAnexoClienteCC, cc.Nombre 
                FROM c_contrato AS c
                LEFT JOIN c_anexotecnico AS cat ON cat.NoContrato = c.NoContrato
                LEFT JOIN k_anexoclientecc AS kacc ON cat.ClaveAnexoTecnico = kacc.ClaveAnexoTecnico
                LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC
                WHERE kacc.CveEspClienteCC = '" . $rs['ClaveCentroCosto'] . "';";
            $query = $catalogo->obtenerLista($consulta);
            if (mysql_num_rows($query) <= 0) {//Si las localidades no tienen contrato 0 anexo
                /* Creamos la estructura completa */                
                $contrato = new Contrato();
                if ($contrato->newRegistroDefault(date("Y") . "-" . date("m") . "-" . date("d"), date("Y") . "-12-31", $cc_objeto->getClaveCliente(), "PHP Movimiento_equipos_solicitud")) {
                    $anexo = new Anexo();
                    if ($anexo->newRegistroDefault(date("Y") . "-" . date("m") . "-" . date("d"), $contrato->getNoContrato(), $cc_objeto->getClaveCentroCosto(), "PHP Movimiento_equipos_solicitud")) {
                        $anexos_cc[$rs['ClaveCentroCosto']] = $anexo->getIdAnexoClienteCC();
                    } else {/* Ocurrio un error al crear los anexos */
                        $sin_anexo[$rs['ClaveCentroCosto']] = $rs['Nombre']; //Aqui marcamos las localidades que no tenían anexo
                        $tienen_anexo = false;
                    }
                } else { /* Ocurrio un error al generar el contrato por default */
                    $sin_anexo[$rs['ClaveCentroCosto']] = $rs['Nombre']; //Aqui marcamos las localidades que no tenían anexo, ahora lo tenemos que crear.
                    $tienen_anexo = false;
                }                
            } else {
                while ($resultSet = mysql_fetch_array($query)) {//Completamos la estructura si es que no está completa.
                    if (!isset($resultSet['IdAnexoClienteCC'])) {/* Si no hay IdAnexoClienteCC */
                        /* Creamos la estructura completa */
                        $contrato = new Contrato();
                        $contrato->getRegistroById($resultSet['NoContrato']);
                        $anexo = new Anexo();
                        if (!$anexo->newRegistroDefault(date("Y") . "/" . date("m") . "/" . date("d"), $contrato->getNoContrato(), $cc_objeto->getClaveCentroCosto(), "PHP Movimiento_equipos_solicitud")) {
                            $anexos_cc[$rs['ClaveCentroCosto']] = $anexo->getIdAnexoClienteCC();
                        } else {/* Ocurrio un error al crear los anexos */
                            $sin_anexo[$rs['ClaveCentroCosto']] = $rs['Nombre']; //Aqui marcabamos las localidades que no tenían anexo
                            $tienen_anexo = false;
                        }
                    } else {
                        $anexos_cc[$rs['ClaveCentroCosto']] = $resultSet['IdAnexoClienteCC'];
                    }
                    break;
                }
            }
        }

        $datos_completos = true; //Verificamos que los datos de mensajeria esten capturados en caso de que se necesiten.
        if (($parametros['mensajeria'] == "" || $parametros['no_guia'] == "") 
                && ($parametros['conductor'] == "" || $parametros['vehiculo'] == "")
                && ($parametros['envio_otro'] == "")) {
            $datos_completos = false;
            $hay_error = true;
        }

        if ($datos_completos) {
            $tickets_generados = "";
            if ($tienen_anexo) {
                $consulta = "SELECT DISTINCT
                (b.NoSerie) AS NoSerie,
                cs.ClaveCliente,
                cs.id_tiposolicitud,
                cs.id_almacen,
                cs.comentario,
                ks.IdAnexoClienteCC,
                ks.IdServicio, ks.IdKServicio,
                ks.Modelo, e.Modelo as ModeloEquipo,
                ks.ClaveCentroCosto, u.Loggin AS crea,
                ks.Ubicacion,
                (CASE WHEN !ISNULL(ct2.IdContacto) THEN ct2.Nombre ELSE ct.Nombre END) AS contacto,
                (CASE WHEN !ISNULL(ct2.IdContacto) THEN ct2.Telefono ELSE ct.Telefono END) AS TelefonoContacto,
                (SELECT group_concat( DISTINCT(CONVERT(IdCaracteristicaEquipo, CHAR(8))) separator ', ') FROM k_equipocaracteristicaformatoservicio AS ke
                 WHERE ke.NoParte = ks.Modelo GROUP BY ke.NoParte) AS caracteristicas
                FROM `k_solicitud` AS ks
                LEFT JOIN c_bitacora AS b ON b.id_solicitud = ks.id_solicitud AND ks.Modelo = b.NoParte
                INNER JOIN c_solicitud AS cs ON cs.id_solicitud = ks.id_solicitud
                INNER JOIN c_usuario AS u ON u.IdUsuario = cs.id_crea
                LEFT JOIN c_equipo AS e ON e.NoParte = ks.Modelo    
                LEFT JOIN c_contacto AS ct ON ct.IdContacto = (SELECT MAX(IdContacto) FROM c_contacto AS ct2 WHERE ct2.ClaveEspecialContacto = ks.ClaveCentroCosto)  
                LEFT JOIN c_contacto AS ct2 ON ct2.IdContacto = cs.IdContacto
                WHERE ks.id_solicitud = " . $parametros['id_solicitud'] . " AND ks.tipo = 0 AND (ks.ClaveCentroCosto = b.ClaveCentroCosto OR ISNULL(ks.ClaveCentroCosto)) 
                GROUP BY NoSerie;";
                $catalogo = new Catalogo();
                $result = $catalogo->obtenerLista($consulta);
                
                /* Recorremos todas las series */
                while ($rs = mysql_fetch_array($result)) {
                    //echo "<br/>".'check_solicitud_' . $rs['NoSerie'];
                    $serie_sin_espacio = str_replace(" ", "", $rs['NoSerie']);
                    /* Si el checbox de la serie no esta seleccionado */                    
                    if (!isset($parametros['check_solicitud_' . $serie_sin_espacio]) || $parametros['check_solicitud_' . $serie_sin_espacio] != "on") {
                        continue;
                    }
                    //echo " ... pasó";
                    $hay_seleccionados = true; //Hay equipo seleccionado para mover                                        
                    
                    /* Buscamos si aun sigue registrada en el almacen */
                    $almacen = new AlmacenEquipo();
                    if ($almacen->getRegistroById($rs['NoSerie'])) {
                        $idAlmacen = $almacen->getIdAlmacen();
                        /* afectamos las existencias en almacen */
                        $almacen = new AlmacenEquipo();
                        $almacen->setNoSerie($rs['NoSerie']);
                        if (!$almacen->deleteRegistro()) {
                            echo "<br/>Error: El equipo " . $rs['NoSerie'] . " no se puedo eliminar del almacén";
                            $hay_error = true;
                        }
                    } else {
                        echo "<br/>El equipo " . $rs['NoSerie'] . " ya no se encontraba en el almacén";
                        $no_en_almacen = true;
                        continue;
                    }
                    
                    if(isset($parametros['almacen_destino']) && $parametros['almacen_destino']!=""){//Si se hizo movimiento de almacen a almacen
                        $almacen = new AlmacenEquipo();
                        $almacen->setNoSerie($rs['NoSerie']);
                        $almacen->setIdAlmacen($parametros['almacen_destino']);
                        $almacen->setNoParteEquipo($rs['Modelo']);
                        $almacen->setFechaIngreso(date('Y')."-".date('m')."-".date('d'));
                        $almacen->setUbicacion("");
                        $almacen->setUsuarioCreacion($_SESSION['user']);
                        $almacen->setUsuarioModificacion($_SESSION['user']);
                        $almacen->setPantalla($pantalla);
                        if($almacen->newRegistro()){
                            $movimiento = new Movimiento();
                            if($movimiento->nuevoMovimientoAlmacenAlmacen($almacen->getNoSerie(), $idAlmacen, $parametros['almacen_destino'], $pantalla)){
                                /* Registramos el envio por mensajeria */
                                $envio = new Envios();
                                $envio->setNoSerie($rs['NoSerie']);
                                $envio->setIdSolicitud($parametros['id_solicitud']);                                
                                $envio->setActivo(1);
                                $envio->setUsuarioCreacion($_SESSION['user']);
                                $envio->setUsuarioUltimaModificacion($_SESSION['user']);
                                $envio->setPantalla($pantalla);
                                $envio->setEstatus(0);                                
                                if ($parametros['tipo_envio'] == "mensajeria" && $parametros['mensajeria'] != "") {/* Si se envia por mensajeria */
                                    $envio->setIdMensajeria($parametros['mensajeria']);
                                    $envio->setNoGuia($parametros['no_guia']);
                                    $envio->setIdVehiculo("null");
                                    $envio->setIdConductor("null");                                    
                                } else if($parametros['tipo_envio'] == "propio"){/* Si se envia por transporte propio */
                                    $envio->setIdMensajeria("null");
                                    $envio->setNoGuia("");    
                                    
                                    if ($parametros['vehiculo'] != "") {
                                        $envio->setIdVehiculo($parametros['vehiculo']);                                        
                                    } else {
                                        $envio->setIdVehiculo("null");                                        
                                    }
                                    
                                    if ($parametros['conductor'] != "") {
                                        $envio->setIdConductor($parametros['conductor']);                                        
                                    } else {
                                        $envio->setIdConductor("null");                                        
                                    }
                                } else{
                                    $envio->setIdVehiculo("null");
                                    $envio->setIdConductor("null");
                                    $envio->setIdMensajeria("null");
                                    $envio->setNoGuia("");
                                    $envio->setOtros($parametros['envio_otro']);
                                }

                                /* Agregamos el registro de mensajeria */
                                if (!$envio->newRegistro()) {
                                    echo "<br/>Error: no se pudo registrar el envío";
                                    $hay_error = true;
                                }
                            }else{
                                $hay_error = true;
                            }
                        }else{
                            echo "<br/>El equipo no se pudo insertar en el almacén destino";
                            $hay_error = true;
                        }
                    }else{//Si la solicitud va hacia un cliente
                        /* Si este equipo tiene registrada la localidad */
                        if (isset($rs['IdAnexoClienteCC'])) {
                            $idAnexoClienteCC = $rs['IdAnexoClienteCC'];
                        } else {
                            $idAnexoClienteCC = "null";
                        }
                        /* Si este equipo tiene registrado el anexo */
                        if (isset($rs['IdServicio'])) {
                            $idServico = $rs['IdServicio'];
                        } else {
                            $idServico = "null";
                        }

                        if(isset($rs['IdKServicio'])){
                            $idKServicio = $rs['IdKServicio'];
                        }else{
                            $idKServicio = "";
                        }
                        
                        /* Si no tiene anexo y servicio, hay que crear esos datos (Para cualquier tipo de solicitud) */
                        if ($idAnexoClienteCC == "null" || $idServico == "null" || $idKServicio == "") {
                            /* Procesamos dependiendo de las caracteristicas del equipo */
                            if ($rs['caracteristicas'] != null && $rs['caracteristicas'] != "") {/* Si tiene caracteristicas */
                                $caracteristicas = explode(",", $rs['caracteristicas']);
                            } else {
                                $caracteristicas = array();
                            }
                            
                            if (in_array("2", $caracteristicas)) {//Es formato amplio                
                                /************* Verificamos si tiene servicios particulares  ***************** */
                                $consulta = "SELECT IdServicioFA,IdKServicioFA FROM `k_serviciofa` WHERE IdAnexoClienteCC = '" . $anexos_cc[$rs['ClaveCentroCosto']] . "';";
                                $query = $catalogo->obtenerLista($consulta);
                                $idServico = "";
                                $idKServicio = "null";
                                while ($rs1 = mysql_fetch_array($query)) {
                                    $idServico = $rs1['IdServicioFA'];
                                    $idKServicio = $rs1['IdKServicioFA'];
                                }                                                                                  

                                if ($idServico != "" && $idServico != "0" && $idServico != "null" 
                                        && $idKServicio != "" && $idKServicio !="0" && $idKServicio != "null") {/* Si tiene servicios particulares FA*/                                    
                                    $consulta = "SELECT * FROM c_inventarioequipo WHERE NoSerie = '" . $rs['NoSerie'] . "' AND Activo = 1;";
                                    $query = $catalogo->obtenerLista($consulta);
                                    if (mysql_num_rows($query) > 0) {/* Ya esta en inventario */
                                        $consulta = "UPDATE c_inventarioequipo SET NoParteEquipo = '" . $rs['Modelo'] . "', ClaveEspKServicioFAIM = $idServico, 
                                            IdAnexoClienteCC = " . $anexos_cc[$rs['ClaveCentroCosto']] . ", Ubicacion = '".$rs['Ubicacion']."', IdKServicio = $idKServicio,
                                            UsuarioUltimaModificacion = '" . $_SESSION['user'] . "', FechaUltimaModificacion = NOW(), Pantalla = 'PHP Movimiento_equipos_solicitud' WHERE NoSerie = '" . $rs['NoSerie'] . "';";                                    
                                        $query = $catalogo->obtenerLista($consulta);
                                    } else {/* No esta en inventario */
                                        $consulta = "INSERT INTO c_inventarioequipo(NoSerie, NoParteEquipo, ClaveEspKServicioFAIM, IdAnexoClienteCC, Ubicacion,Activo, 
                                            UsuarioCreacion, FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion, Pantalla, IdKServicio) 
                                            VALUES('" . $rs['NoSerie'] . "','" . $rs['Modelo'] . "',$idServico," . $anexos_cc[$rs['ClaveCentroCosto']] . ",'".$rs['Ubicacion']."',1,
                                                '" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Movimiento_equipos_solicitud',$idKServicio);";                                        
                                        $query = $catalogo->obtenerLista($consulta);
                                    }
                                } else {/*********  No tiene servicios particulares, asi que creamos un servicio global  ********** */                                    
                                    /* Creamos el registro en: k_serviciogim */
                                    $consulta = "INSERT INTO k_serviciogfa(IdServicioGFA, IdAnexoClienteCC, RentaMensual, MLIncluidosBN, MLIncluidosColor, CostoMLExcedentesBN, 
                                    CostoMLExcedentesColor, CostoMLProcesadosBN, CostoMLProcesadosColor, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, 
                                    Pantalla, FechaTomaLectura) VALUES(1001,'" . $anexos_cc[$rs['ClaveCentroCosto']] . "',0,0,0,0,0,0,0,'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Movimiento_equipos_solicitud',NOW());";                                
                                    $idKServicio = $catalogo->insertarRegistro($consulta);
                                    if ($idKServicio!= null && $idKServicio != "0") {                                         
                                        $consulta = "INSERT INTO k_serviciogimgfa(CveEspKservicioimfa,ClaveCentroCosto, IdAnexoClienteCC, UsuarioCreacion, FechaCreacion, 
                                        UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
                                        VALUES(1050, '" . $rs['ClaveCentroCosto'] . "', " . $anexos_cc[$rs['ClaveCentroCosto']] . ",'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Movimiento_equipos_solicitud');";
                                        $query = $catalogo->insertarRegistro($consulta);
                                        if ($query != "0") {
                                            $idKServiciogimgfa = $query;
                                            $consulta = "SELECT * FROM c_inventarioequipo WHERE NoSerie = '" . $rs['NoSerie'] . "' AND Activo = 1;";
                                            $query = $catalogo->obtenerLista($consulta);
                                            if (mysql_num_rows($query) > 0) {/* Ya esta en inventario */
                                                $consulta = "UPDATE c_inventarioequipo SET NoParteEquipo = '" . $rs['Modelo'] . "', ClaveEspKServicioFAIM = 1050, 
                                                    IdAnexoClienteCC = " . $anexos_cc[$rs['ClaveCentroCosto']] . ", Ubicacion = '".$rs['Ubicacion']."', IdKServicio = $idKServicio, IdKserviciogimgfa = $idKServiciogimgfa,
                                                    UsuarioUltimaModificacion = '" . $_SESSION['user'] . "', FechaUltimaModificacion = NOW(), Pantalla = 'PHP Movimiento_equipos_solicitud' WHERE NoSerie = '" . $rs['NoSerie'] . "';";
                                                $query = $catalogo->obtenerLista($consulta);
                                            } else {/* No esta en inventario */
                                                $consulta = "INSERT INTO c_inventarioequipo(NoSerie, NoParteEquipo, ClaveEspKServicioFAIM, IdAnexoClienteCC, Ubicacion,Activo, 
                                                    UsuarioCreacion, FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion, Pantalla, IdKserviciogimgfa, IdKServicio) 
                                                    VALUES('" . $rs['NoSerie'] . "','" . $rs['Modelo'] . "',1050," . $anexos_cc[$rs['ClaveCentroCosto']] . ",'".$rs['Ubicacion']."',1,
                                                        '" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Movimiento_equipos_solicitud', $idKServiciogimgfa, $idKServicio);";                                                
                                                $query = $catalogo->obtenerLista($consulta);
                                            }
                                        } else {
                                            echo "<br/>Error: no se pudo registrar el servicio global (k_serviciogimgfa) de " . $rs['NoSerie'];
                                            $hay_error = true;
                                        }
                                    } else {
                                        echo "<br/>Error: no se pudo registrar el servicio global (k_serviciogfa) de " . $rs['NoSerie'];
                                        $hay_error = true;
                                    }
                                }
                            } else {//No es de formato amplio                      
                                /* verificamos si esta en la tabla k_servicioim */
                                $consulta = "SELECT IdServicioIM,IdKServicioIM FROM `k_servicioim` WHERE IdAnexoClienteCC = '" . $anexos_cc[$rs['ClaveCentroCosto']] . "';";
                                
                                $query = $catalogo->obtenerLista($consulta);
                                $idServico = "";
                                $idKServicio = "null";
                                while ($rs1 = mysql_fetch_array($query)) {
                                    $idServico = $rs1['IdServicioIM'];
                                    $idKServicio = $rs1['IdKServicioIM'];
                                }
                                
                                if ($idServico != "" && $idServico != "0" && $idServico != "null" 
                                        && $idKServicio != "" && $idKServicio !="0" && $idKServicio != "null") {/* Si tiene servicios particulares IM*/                                    
                                    $consulta = "SELECT * FROM c_inventarioequipo WHERE NoSerie = '" . $rs['NoSerie'] . "' AND Activo = 1;";
                                    $query = $catalogo->obtenerLista($consulta);
                                    if (mysql_num_rows($query) > 0) {/* Ya esta en inventario */
                                        $consulta = "UPDATE c_inventarioequipo SET NoParteEquipo = '" . $rs['Modelo'] . "', ClaveEspKServicioFAIM = $idServico, 
                                            IdAnexoClienteCC = " . $anexos_cc[$rs['ClaveCentroCosto']] . ", Ubicacion = '".$rs['Ubicacion']."', IdKServicio = $idKServicio,
                                            UsuarioUltimaModificacion = '" . $_SESSION['user'] . "', FechaUltimaModificacion = NOW(), Pantalla = 'PHP Movimiento_equipos_solicitud' WHERE NoSerie = '" . $rs['NoSerie'] . "';";                                        
                                        $query = $catalogo->obtenerLista($consulta);
                                    } else {/* No esta en inventario */
                                        $consulta = "INSERT INTO c_inventarioequipo(NoSerie, NoParteEquipo, ClaveEspKServicioFAIM, IdAnexoClienteCC, Ubicacion,Activo, 
                                            UsuarioCreacion, FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion, Pantalla, IdKServicio) 
                                            VALUES('" . $rs['NoSerie'] . "','" . $rs['Modelo'] . "',$idServico," . $anexos_cc[$rs['ClaveCentroCosto']] . ",'".$rs['Ubicacion']."',1,
                                                '" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Movimiento_equipos_solicitud', $idKServicio);";                                        
                                        $query = $catalogo->obtenerLista($consulta);
                                    }
                                } else {/*                             * *******  No tiene servicios particulares, asi que creamos un servicio global  ********** */
                                    /* Creamos el registro en: k_serviciogim */
                                    $consulta = "INSERT INTO k_serviciogim(IdServicioGIM, IdAnexoClienteCC, RentaMensual, PaginasIncluidasBN, PaginasIncluidasColor, CostoPaginasExcedentesBN, 
                                    CostoPaginasExcedentesColor, CostoPaginaProcesadaBN, CostoPaginaProcesadaColor, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla)
                                    VALUES (1050," . $anexos_cc[$rs['ClaveCentroCosto']] . ",0,0,0,0,0,0,0,'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Movimiento_equipos_solicitud');";
                                    $idKServicio = $catalogo->insertarRegistro($consulta);
                                    
                                    if ($idKServicio != null && $idKServicio!="0") {                                    
                                        
                                        $consulta = "INSERT INTO k_serviciogimgfa(CveEspKservicioimfa,ClaveCentroCosto, IdAnexoClienteCC, UsuarioCreacion, FechaCreacion, 
                                        UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
                                        VALUES(1050, '" . $rs['ClaveCentroCosto'] . "', " . $anexos_cc[$rs['ClaveCentroCosto']] . ",'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Movimiento_equipos_solicitud');";
                                        $query = $catalogo->insertarRegistro($consulta);
                                        if ($query != "0") {
                                            $idKServiciogimgfa = $query;
                                            $consulta = "SELECT * FROM c_inventarioequipo WHERE NoSerie = '" . $rs['NoSerie'] . "' AND Activo = 1;";
                                            $query = $catalogo->obtenerLista($consulta);
                                            if (mysql_num_rows($query) > 0) {/* Ya esta en inventario */
                                                $consulta = "UPDATE c_inventarioequipo SET NoParteEquipo = '" . $rs['Modelo'] . "', ClaveEspKServicioFAIM = 1050, 
                                                    IdAnexoClienteCC = " . $anexos_cc[$rs['ClaveCentroCosto']] . ", Ubicacion = '".$rs['Ubicacion']."', IdKserviciogimgfa = $idKServiciogimgfa,
                                                    IdKServicio = $idKServicio, UsuarioUltimaModificacion = '" . $_SESSION['user'] . "', FechaUltimaModificacion = NOW(), Pantalla = 'PHP Movimiento_equipos_solicitud' WHERE NoSerie = '" . $rs['NoSerie'] . "';";
                                                $query = $catalogo->obtenerLista($consulta);
                                            } else {/* No esta en inventario */
                                                $consulta = "INSERT INTO c_inventarioequipo(NoSerie, NoParteEquipo, ClaveEspKServicioFAIM, IdAnexoClienteCC, Ubicacion,Activo, 
                                                    UsuarioCreacion, FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion, Pantalla, IdKserviciogimgfa,IdKServicio) 
                                                    VALUES('" . $rs['NoSerie'] . "','" . $rs['Modelo'] . "',1050," . $anexos_cc[$rs['ClaveCentroCosto']] . ",'".$rs['Ubicacion']."',1,
                                                        '" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Movimiento_equipos_solicitud', $idKServiciogimgfa, $idKServicio);";
                                                $query = $catalogo->obtenerLista($consulta);
                                            }
                                        } else {
                                            echo "<br/>Error: no se pudo registrar el servicio global (k_serviciogimgfa) de " . $rs['NoSerie'];
                                            $hay_error = true;
                                        }
                                    } else {
                                        echo "<br/>Error: no se pudo registrar el servicio global (k_serviciogim) de " . $rs['NoSerie'];
                                        $hay_error = true;
                                    }
                                }
                            }
                        } else {/* Obtenemos los datos de servicio de lo registrado en c_solicitud */
                            /* Actualizamos el inventario */
                            $obj->setNoSerie($rs['NoSerie']);
                            $obj->setNoParte($rs['Modelo']);
                            $obj->setUbicacion($rs['Ubicacion']);
                            $obj->setIdServicio($idServico);
                            $obj->setIdKServicio($idKServicio);
                            $obj->setIdAnexoClienteCC($idAnexoClienteCC);
                            if (intval($idServico) > 1000) {
                                $obj->setTipoServicio("0");
                            }
                            $obj->setClaveCentroCosto($rs['ClaveCentroCosto']);
                            $obj->setUsuarioCreacion($_SESSION['user']);
                            $obj->setUsuarioUltimaModificacion($obj->getUsuarioCreacion());
                            $obj->setPantalla("PHP Movimiento_equipos_solicitud");
                            $obj->registrarInventario();
                            $query = "1";
                        }
                        
                        if ($query == "1") {
                            /* Si esta marcada como back-up, cambiamos su estatus en bitacora */
                            if ($rs['id_tiposolicitud'] == "2") {
                                $obj->setNoSerie($rs['NoSerie']);
                                if (!$obj->marcarComoBackUp()) {
                                    echo "<br/>Error: No se pudo marcar como back-up el equipo " . $rs['NoSerie'] . " de modelo: " . $rs['ModeloEquipo'];
                                    $hay_error = true;
                                }
                                $obj->marcarComoVentaDirecta(false);
                            } else if ($rs['id_tiposolicitud'] == "6") {/* Si la solicitud es de venta directa */
                                $obj->setNoSerie($rs['NoSerie']);
                                if (!$obj->marcarComoVentaDirecta(true)) {
                                    echo "<br/>Error: No se pudo marcar como venta directa el equipo " . $rs['NoSerie'] . " de modelo: " . $rs['ModeloEquipo'];
                                    $hay_error = true;
                                }
                            } else if($rs['id_tiposolicitud'] == "4"){//Si la solicitud es demo
                                $obj->setNoSerie($rs['NoSerie']);
                                $obj->marcarComoVentaDirecta(false);
                                if (!$obj->marcarComoDemo()) {
                                    echo "<br/>Error: No se pudo marcar como demo el equipo " . $rs['NoSerie'] . " de modelo: " . $rs['ModeloEquipo'];
                                    $hay_error = true;
                                }
                            }else {
                                $obj->setNoSerie($rs['NoSerie']);
                                $obj->marcarComoVentaDirecta(false);
                            }                        
                        } else {
                            echo "<br/>Error: el equipo no se pudo mover al cliente";
                            $hay_error = true;
                        }
                        
                        if (!$hay_error) {/* Si no ha habido algun error, registamos el movimiento de equipo */                            
                            if (isset($rs['ClaveCentroCosto'])) {
                                $cc = $rs['ClaveCentroCosto'];
                            } else {
                                $cc = "";
                            }
                            $movimiento = new Movimiento();
                            if (!$movimiento->nuevoMovimientoAlmacenCliente($rs['NoSerie'], $idAlmacen, $rs['ClaveCliente'], $cc, 'PHP Movimiento_equipos_solicitud', $parametros['id_solicitud'])) {
                                echo "<br/>Error: no se pudo registrar en la tabla de movimientos";
                            }

                            /* Registramos el envio por mensajeria */
                            $envio = new Envios();
                            $envio->setNoSerie($rs['NoSerie']);
                            $envio->setIdSolicitud($parametros['id_solicitud']);
                            $envio->setClaveCentroCosto($cc);
                            $envio->setActivo(1);
                            $envio->setUsuarioCreacion($_SESSION['user']);
                            $envio->setUsuarioUltimaModificacion($_SESSION['user']);
                            $envio->setPantalla("PHP Movimiento_equipos_solicitud");
                            $envio->setEstatus(0);
                            $mensaje_ticket = "";
                            if ($parametros['tipo_envio'] == "mensajeria" && $parametros['mensajeria'] != "") {/* Si se envia por mensajeria */
                                $envio->setIdMensajeria($parametros['mensajeria']);
                                $envio->setNoGuia($parametros['no_guia']);
                                $envio->setIdVehiculo("null");
                                $envio->setIdConductor("null");
                                $mensajeria = new Mensajeria();
                                $mensajeria->getRegistroById($parametros['mensajeria']);
                                $mensaje_ticket = "Envío por " . $mensajeria->getNombre() . " con numero de guia: " . $parametros['no_guia'];
                            } else if($parametros['tipo_envio'] == "propio"){/* Si se envia por transporte propio */
                                $envio->setIdMensajeria("null");
                                $envio->setNoGuia("");
                                $vehiculo = new Vehiculo();
                                $conductor = new Conductor();
                                if ($parametros['vehiculo'] != "") {
                                    $envio->setIdVehiculo($parametros['vehiculo']);
                                    $vehiculo->getRegistroById($parametros['vehiculo']);
                                } else {
                                    $envio->setIdVehiculo("null");
                                    $vehiculo->setPlacas("");
                                    $vehiculo->setModelo("");
                                }
                                if ($parametros['conductor'] != "") {
                                    $envio->setIdConductor($parametros['conductor']);
                                    $conductor->getRegistroById($parametros['conductor']);
                                } else {
                                    $envio->setIdConductor("null");
                                    $conductor->setNombre("");
                                    $conductor->setApellidoPaterno("");
                                    $conductor->setApellidoMaterno("");
                                }
                                $mensaje_ticket = "Envío con vehículo " . $vehiculo->getPlacas() . "/" . $vehiculo->getModelo() . " y conductor " . $conductor->getNombre() . " " . $conductor->getApellidoPaterno() . " " . $conductor->getApellidoMaterno();
                            } else{
                                $envio->setIdVehiculo("null");
                                $envio->setIdConductor("null");
                                $envio->setIdMensajeria("null");
                                $envio->setNoGuia("");
                                $envio->setOtros($parametros['envio_otro']);
                                $mensaje_ticket = "Envío otros: ".$envio->getOtros();
                            }

                            /* Agregamos el registro de mensajeria */
                            if (!$envio->newRegistro()) {
                                echo "<br/>Error: no se pudo registrar el envío";
                                $hay_error = true;
                            }

                            if (isset($parametros['requiere_ticket']) && $parametros['requiere_ticket'] == "si") {
                                /* Insertamos el ticket de instalacion */
                                $cliente = new Cliente();
                                $cliente->getRegistroById($rs['ClaveCliente']);
                                $ticket = new Ticket();
                                $ticket->setUsuario($_SESSION['user']);
                                $ticket->setTipoReporte(62);
                                $ticket->setEstadoDeTicket(3);
                                $ticket->setNombreCliente($cliente->getNombreRazonSocial());
                                $ticket->setClaveCentroCosto($cc);
                                $ticket->setClaveCliente($cliente->getClaveCliente());
                                $areaAtencion = 5;
                                if ($cc != "") {
                                    $cc_objeto = new CentroCosto();
                                    if($cc_objeto->getRegistroById($cc)){//Si se obtiene el objeto de centro de costo
                                        $ticket->setNombreCentroCosto($cc_objeto->getNombre());
                                        $zona = new Zona();
                                        if($zona->getRegistroById($cc_objeto->getClaveZona())){
                                            if($zona->getIdGZona() == "2"){//Si la clave de zona es igual a dos, entonces el area de atencion es foraneo
                                                $areaAtencion = 4;
                                            }
                                        }
                                    }else{//Sino se obtiene el objeto de clave de centro de costo
                                        $ticket->setNombreCentroCosto("");
                                        $zona = new Zona();
                                        if($zona->getRegistroById($cliente->getClaveZona())){//Obtenemos la zona del cliente, porque no hay centro de costo
                                            if($zona->getIdGZona() == "2"){//Si la clave de zona es igual a dos, entonces el area de atencion es foraneo
                                                $areaAtencion = 4;
                                            }
                                        }
                                    }
                                } else {
                                    $ticket->setNombreCentroCosto("");
                                    $zona = new Zona();
                                    if($zona->getRegistroById($cliente->getClaveZona())){//Obtenemos la zona del cliente, porque no hay centro de costo
                                        if($zona->getIdGZona() == "2"){//Si la clave de zona es igual a dos, entonces el area de atencion es foraneo
                                            $areaAtencion = 4;
                                        }
                                    }
                                }

                                if($envio->getNoGuia()!=""){//Si estuvo asignado un numero de guia, siempre es foraneo.
                                    $areaAtencion = 4;
                                }

                                $ticket->setNoSerieEquipo($rs['NoSerie']);
                                $ticket->setModeloEquipo($rs['ModeloEquipo']);
                                $ticket->setAreaAtencion($areaAtencion);
                                $ticket->setActivo(1);
                                $ticket->setUsuarioCreacion($rs['crea']);
                                $ticket->setUsuarioUltimaModificacion($rs['crea']);
                                $ticket->setPantalla("PHP Movimiento_equipos_solicitud");
                                $ticket->setUbicacion("0");
                                $ticket->setDescripcionReporte("Instalación de equipo " . $ticket->getNoSerieEquipo() . " Modelo: " . $ticket->getModeloEquipo());
                                $ticket->setObservacionAdicional($rs['comentario']);
                                $ticket->setNombreResp($rs['contacto']);
                                $ticket->setTelefono1Resp($rs['TelefonoContacto']);
                                if (!$ticket->newRegistro()) {
                                    echo "<br/>Error: no se pudo insertar el ticket de instalación";
                                    $hay_error = true;
                                } else {
                                    $obj2 = new AgregarNota();
                                    $obj2->setIdTicket($ticket->getIdTicket());                                    
                                    $obj2->setDiagnosticoSolucion($mensaje_ticket);
                                    $obj2->setIdestatusAtencion(62);
                                    $obj2->setActivo(1);
                                    $obj2->setShow(1);                                    
                                    $obj2->setUsuarioCreacion($rs['crea']);
                                    $obj2->setUsuarioModificacion($rs['crea']);
                                    $obj2->setPantalla("PHP Lista ticket");
                                    $obj2->newRegistro();
                                    
                                    //Agregamos los contadores de la solicitud en el ticket
                                    $consulta = "INSERT INTO c_lecturasticket(ClvEsp_Equipo, ContadorBN, ContadorCL, NivelTonNegro, NivelTonCian,NivelTonMagenta,
                                        NivelTonAmarillo,ModeloEquipo,fk_idticket,Activo,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                        SELECT l.NoSerie, l.ContadorBNPaginas, l.ContadorColorPaginas, l.NivelTonNegro, l.NivelTonCian, l.NivelTonMagenta, l.NivelTonAmarillo, 
                                        e.Modelo, ".$ticket->getIdTicket().", 1, NOW(), '".$obj2->getUsuarioCreacion()."', NOW(), '".$obj2->getUsuarioModificacion()."', NOW(), '$pantalla' 
                                        FROM c_lectura AS l
                                        LEFT JOIN c_bitacora AS b ON l.NoSerie = b.NoSerie
                                        LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
                                        WHERE l.IdSolicitud = ".$parametros['id_solicitud']." AND l.NoSerie = '".$ticket->getNoSerieEquipo()."';";
                                    $idLecturaTicket = $catalogo->insertarRegistro($consulta);
                                    
                                    //Agregamos el id del ticket para el mensaje a los usuarios
                                    $tickets_generados.="<br/>* Ticket " . $ticket->getIdTicket() . " para el equipo " . $ticket->getNoSerieEquipo() . " modelo " . $ticket->getModeloEquipo();
                                }
                            }//Fin si se requiere ticket
                        }//Fin si no hay error al mover equipo
                    }//Fin sino va a almacén                                        
                }//Fin while recorre todos los equipos

                /* Aqui enviamos los componentes */
                $envioToner = new SolicitudToner();
                $movimiento_componente = new MovimientoComponente();                
                for ($i = 0; $i < intval($parametros['total_componentes']); $i++) {
                    if (isset($parametros['almacen_sol2_' . $i]) && $parametros['almacen_sol2_' . $i] != "") { 
                        
                        //Si el checkbox no está seleccionado, nos saltamos este registro
                        if (!isset($parametros['check_solicitud2_'.$i]) || $parametros['check_solicitud2_'.$i] != "on") {
                            continue;
                        }                        
                        $hay_seleccionados = true;//Hay componentes seleccionados a enviar
                        
                        /* Primero, obtenemos el numero de serie al que el componente está asociado, si es que existe un NoSerie */
                        $consulta = "SELECT NoSerie FROM `k_solicitud` WHERE id_solicitud = " . $parametros['solicitud2_' . $i] . " 
                            AND id_partida = " . $parametros['partida_' . $i] . ";";
                        $NoSerie = "";
                        $result = $catalogo->obtenerLista($consulta);
                        while ($rs = mysql_fetch_array($result)) {
                            $NoSerie = $rs['NoSerie'];
                        }                                                                        
                        
                        if ($NoSerie != "") {//Si si existe un NoSerie, asociamos el componente a la bitacora del equipo
                            $configuracion = new Configuracion();
                            $configuracion->getRegistroByNoSerie($NoSerie);
                            $componente = new Componente();
                            $componente->getRegistroById($parametros['modelo2_' . $i]);
                            if($componente->getTipo()!="2"){
                                $tipo = "0";
                            }else{
                                $tipo = "1";
                            }
                            $configuracion->setUsuarioCreacion($_SESSION['user']);
                            $configuracion->setUsuarioUltimaModificacion($_SESSION['user']);
                            $configuracion->setActivo("1"); $configuracion->setPantalla($pantalla);
                            $configuracion->newKRegistro($parametros['modelo2_' . $i], date("Y")."-".date("m")."-".date("d"), $tipo);
                        }
                        /*Obtenemos lo que se ha enviado anteriormente de este componente en esta solicitud para el centro de costo actual*/
                        $cantidadEnviadaAnterior = 0;
                        $consulta = "SELECT SUM(Cantidad) AS cantidad FROM `k_enviotoner` WHERE IdSolicitudEquipo = ".$parametros['solicitud2_' . $i]." 
                            AND NoParte = '".$parametros['modelo2_' . $i]."' AND ClaveCentroCosto = '".$parametros['cc2_' . $i]."';";
                        $result = $catalogo->obtenerLista($consulta);
                        while($rs = mysql_fetch_array($result)){
                            $cantidadEnviadaAnterior = (int)$rs['cantidad'];
                        }
                        //Restamos lo que ya se ha enviado a lo que ya esta surtido, y eso será lo que se enviara en este proceso
                        $cantidad = intval($parametros['cantidad_surti_' . $i]) - $cantidadEnviadaAnterior;
                        
                        $envioToner->setNoParteComponente($parametros['modelo2_' . $i]);
                        $envioToner->setCantidadSolicitada($cantidad);
                        $envioToner->setClaveCentroCosto($parametros['cc2_' . $i]);
                        $envioToner->setIdSolicitudEquipo($parametros['solicitud2_' . $i]);
                        $envioToner->setUsuarioCreacion($_SESSION['user']);
                        $envioToner->setUsuarioModificacion($_SESSION['user']);
                        $envioToner->setPantalla("PHP Movimiento_equipos_solicitud");
                        if ($parametros['tipo_envio'] == "mensajeria" && $parametros['mensajeria'] != "") {/* Si se envia por mensajeria */
                            $envioToner->setIdMensajeria($parametros['mensajeria']);
                            $envioToner->setNoGuia($parametros['no_guia']);
                            $envioToner->setIdVehiculo("null");
                            $envioToner->setIdConductor("null");
                            $envioToner->newEnvioToner("2");
                        } else if($parametros['tipo_envio'] == "propio"){/* Si se envia por transporte propio */
                            $envioToner->setIdVehiculo($parametros['vehiculo']);
                            $envioToner->setIdConductor($parametros['conductor']);
                            $envioToner->setIdMensajeria("null");
                            $envioToner->setNoGuia("null");
                            $envioToner->newEnvioToner("1");
                        }else{
                            $envioToner->setIdVehiculo("null");
                            $envioToner->setIdConductor("null");
                            $envioToner->setIdMensajeria("null");
                            $envioToner->setNoGuia("");
                            $envioToner->setOtros($parametros['envio_otro']);
                            $envioToner->newEnvioToner("3");
                        }                            
                        //Descontamos los componentes apartados
                        /*Verificamos que no entren existencias negativas*/
                        $cantidad = $parametros['cantidad_surti_' . $i];
                        $almacenComponente = new AlmacenComponente();
                        if($almacenComponente->getRegistroById($parametros['modelo2_' . $i], $parametros['almacen_sol2_' . $i])){
                            if($cantidad > $almacenComponente->getApartados()){
                                $log = new Log();
                                $log->setConsulta("Intento de registrar existencias negativas ($cantidad)");
                                $log->setSeccion($pantalla);
                                $log->setIdUsuario($_SESSION['idUsuario']);
                                $log->setTipo("Incidencia sistema");
                                $log->newRegistro();
                                $cantidad = $almacenComponente->getApartados();
                            }
                        }
                        $consulta = "UPDATE `k_almacencomponente` SET cantidad_apartados = cantidad_apartados - " . $cantidad . ", 
                            FechaUltimaModificacion = NOW(), UsuarioUltimaModificacion = '".$_SESSION['user']."',Pantalla = '$pantalla' 
                            WHERE NoParte = '".$parametros['modelo2_' . $i]."' AND id_almacen = ".$parametros['almacen_sol2_' . $i].";";
                        $catalogo->obtenerLista($consulta);
                        
                        //Registramos el movimiento del almacen
                        $movimiento_componente->setNoParteComponente($parametros['modelo2_' . $i]);
                        $movimiento_componente->setCantidadMovimiento($parametros['cantidad_surti_' . $i]);
                        $movimiento_componente->setIdAlmacenAnterior($parametros['almacen_sol2_' . $i]);
                        $switch = false; //Variable para ver si se cambia el almacen anterior y el nuevo, esto para arreglar un bug del reporte de movimientos
                        
                        if(isset($parametros['almacen_destino']) && $parametros['almacen_destino']!=""){//Si se hizo movimiento de almacen a almacen
                            $movimiento_componente->setIdAlmacenNuevo($parametros['almacen_destino']);
                            $switch = true;
                            //Cargamos los nuevos componentes en el almacen destino
                            $consulta = "SELECT cantidad_existencia FROM k_almacencomponente 
                                WHERE id_almacen = ".$parametros['almacen_destino']." AND NoParte = '".$parametros['modelo2_' . $i]."';";
                            $result2 = $catalogo->obtenerLista($consulta);
                            if(mysql_num_rows($result2) > 0){
                                $consulta = "UPDATE `k_almacencomponente` SET cantidad_existencia = cantidad_existencia + ".$parametros['cantidad_surti_' . $i].",
                                FechaUltimaModificacion = NOW(), UsuarioUltimaModificacion = '".$_SESSION['user']."'
                                WHERE id_almacen = ".$parametros['almacen_destino']." AND NoParte = '".$parametros['modelo2_' . $i]."';";
                            }else{                                
                               /*Verificamos que no entren existencias negativas*/
                                $cantidad = $parametros['cantidad_surti_' . $i];
                                if($cantidad < 0){
                                    $log = new Log();
                                    $log->setConsulta("Intento de registrar existencias negativas ($cantidad)");
                                    $log->setSeccion($pantalla);
                                    $log->setIdUsuario($_SESSION['idUsuario']);
                                    $log->setTipo("Incidencia sistema");
                                    $log->newRegistro();
                                    $cantidad = 0;
                                }
                                
                                $componentesEnviados = (int)$parametros['cantidad_surti_' . $i];
                                if($componentesEnviados < 1){
                                    $componentesEnviados = 1;
                                }
                                $consulta = "INSERT INTO k_almacencomponente(NoParte, id_almacen, cantidad_existencia, cantidad_apartados, CantidadMaxima, CantidadMinima, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
                                    VALUES('".$parametros['modelo2_' . $i]."',".$parametros['almacen_destino'].",".$cantidad.",0,
                                    ".$componentesEnviados.",1,'".$_SESSION['user']."',NOW(),'".$_SESSION['user']."',NOW(),
                                    '$pantalla');";
                            }                 
                            //echo "Error: $consulta";
                            $catalogo->obtenerLista($consulta);
                        }else{
                            $movimiento_componente->setClaveClienteNuevo($parametros['clave_cliente']);
                            $movimiento_componente->setClaveCentroCostoNuevo($parametros['cc2_' . $i]);
                        }
                        $movimiento_componente->setEntradaSalida("1");
                        if ($NoSerie != "") {
                            $movimiento_componente->setNoSerieEquipoNuevo($NoSerie);
                        }
                        $movimiento_componente->setUsuarioCreacion($_SESSION['user']); $movimiento_componente->setUsuarioModificacion($_SESSION['user']);
                        $movimiento_componente->setPantalla("PHP Movimiento_equipos_solicitud");
                        if($switch){//Esto es para arreglar un bug del reporte cuando el destino y el origen son almacenes en el movimiento de compnentes.
                            /*$aux = $movimiento_componente->getIdAlmacenNuevo();
                            $movimiento_componente->setIdAlmacenNuevo($movimiento_componente->getIdAlmacenAnterior());
                            $movimiento_componente->setIdAlmacenAnterior($aux);*/
                            $movimiento_componente->newRegistro();//Se registra el movimiento de salida
                            /*$aux = $movimiento_componente->getIdAlmacenNuevo();
                            $movimiento_componente->setIdAlmacenNuevo($movimiento_componente->getIdAlmacenAnterior());
                            $movimiento_componente->setIdAlmacenAnterior($aux);*/
                        }else{
                            $movimiento_componente->newRegistro();//Se registra el movimiento de salida
                        }
                        
                        if(isset($parametros['almacen_destino']) && $parametros['almacen_destino']!=""){//Si se hizo movimiento de almacen a almacen
                            $movimiento_componente->setEntradaSalida("0");
                            $movimiento_componente->newRegistro();//Se registra el movimiento de entrada
                        }
                    }
                }
            } else {
                echo "<br/>Error: Las siguientes localidades no tienen anexo: ";
                foreach ($sin_anexo as $value) {
                    echo "$value,";
                }
                $hay_error = true;
            }
        } else {/* Si no tiene los datos completos para el envio por mensajeria */
            echo "<br/>Error: Selecciona los datos necesarios (Mensajería y número de guía, conductor y vehículo u otros) para mandar por mensajería por favor.";
        }

        if (!$hay_error) {
            $envio = new Envios();
            if ( ($soli->todosEquiposAsignados($parametros['id_solicitud'])) && 
                 (
                    $envio->todosEquiposEnviados($parametros['id_solicitud']) && 
                    $envio->todosComponentesEnviados($parametros['id_solicitud'])
                 )
                ){
                $solicitud = new Solicitud();
                $solicitud->setId_solicitud($parametros['id_solicitud']);
                $solicitud->cambiarEstatusSolicitud(5);
                echo "<br/>La solictud ha sido marcada como surtida ya que todos los equipos y/o componentes fueron atendidos<br/>";       
            }

            if ($hay_seleccionados) {
                if (!$no_en_almacen) {
                    echo "<br/>Los equipos y/o componentes se encuentran ahora registrados con el cliente";
                } else {
                    echo "<br/>Los equipos y/o componentes restantes se registraron con el cliente";
                }
            } else {
                echo "<br/>Error: No ha seleccionado ningún equipo o componentes para enviar";
            }

            if ($tickets_generados != "") {
                echo "<br/><br/>Se generaron los siguientes tickets: $tickets_generados";
            }
        }
    }
} else if ($_POST['tipo'] == "1") {/* se hace una llamada para guardar las series */
    $contador = 0;
    $correcto = true;
    $series = "";
    $repetidas = false;
    $existencias_suficientes = true;
    $almacen = new Almacen();
    $almacen_componente = new AlmacenComponente();
    $insuficientes = array();
    $catalogo = new Catalogo();
    $componentes_insertados = 0;
    $soli = new Solicitud();
    $soli->setId_solicitud($parametros['id_solicitud']);

    if (isset($parametros['comentario_solicitud']) && $parametros['comentario_solicitud'] != "") {        
        $soli->agregarComentario($parametros['comentario_solicitud']);
    }

    $series_elegidas = array();
    /* Verificamos que no haya series repetidas */
    for ($contador = 0; $contador <= intval($parametros['total']); $contador++) {
        if (isset($parametros['serie_' . $contador]) && $parametros['serie_' . $contador] != "") {
            if (!isset($series_elegidas[$parametros['serie_' . $contador]])) {
                $series_elegidas[$parametros['serie_' . $contador]] = false;
            } else {
                $series_elegidas[$parametros['serie_' . $contador]] = true;
                $repetidas = true;
            }
        }
    }

    /* Verificamos que haya existencias suficientes de los componentes en los almacenes especificados */
    for ($contador = 0; $contador <= intval($parametros['total_componentes']); $contador++) {
        if (isset($parametros['almacen_sol2_' . $contador]) && $parametros['almacen_sol2_' . $contador] != "") {
            $almacen_componente->getRegistroById($parametros['modelo2_' . $contador], $parametros['almacen_sol2_' . $contador]);
            if (intval($almacen_componente->getExistencia()) < intval($parametros['cantidad2_sur_' . $contador])) {
                $existencias_suficientes = false;
                $almacen->getRegistroById($parametros['almacen_sol2_' . $contador]);
                array_push($insuficientes, $parametros['modelo2_' . $contador . "_1"] . " en el almacén " . $almacen->getNombre());
            }
        }
    }


    if ($repetidas) {
        echo "Error: Las siguientes series están repetidas: ";
        foreach ($series_elegidas as $key => $value) {
            if ($value) {
                echo $key . ",";
            }
        }
    } else if (!$existencias_suficientes) {
        echo "Error: No hay existencias suficientes de los siguientes modelos: ";
        foreach ($insuficientes as $value) {
            echo "<br/> $value ,";
        }
    } else {
        for ($contador = 0; $contador <= intval($parametros['total']); $contador++) {//Guardamos todos los equipos
            if (isset($parametros['serie_' . $contador]) && $parametros['serie_' . $contador] != "") {
                $obj->setId_solicitud($parametros['solicitud_' . $contador]);
                $obj->setClaveCentroCosto($parametros['cc_' . $contador]);
                $obj->setUsuarioCreacion($_SESSION['user']);
                $obj->setUsuarioUltimaModificacion($_SESSION['user']);
                $obj->setNoParte($parametros['modelo_' . $contador]);
                $obj->setNoSerie($parametros['serie_' . $contador]);
                
                if(isset($parametros['modelo_original_'.$contador]) && $parametros['modelo_original_'.$contador] != $parametros['modelo_'.$contador]){//Se cambio el noparte al atender
                    $pantalla_cambio = "Solicitudes cambio modelo";
                    $partida = $parametros['partida_equipo_'.$contador];
                    $resultCantidades = $catalogo->obtenerLista("SELECT cantidad, cantidad_autorizada 
                        FROM `k_solicitud` WHERE id_solicitud = ".$obj->getId_solicitud()." AND id_partida = $partida;");
                    while($rsCantidades = mysql_fetch_array($resultCantidades)){                        
                        /////////////////////////Nuevo modelo
                        //Procesamos las cantidades en el nuevo modelo
                        $consulta = "SELECT cantidad, cantidad_autorizada FROM `k_solicitud` 
                            WHERE id_solicitud = ".$obj->getId_solicitud()." AND Modelo = '".$parametros['modelo_'.$contador]."' AND tipo = 0;";
                        $resultCantidadesNuevas = $catalogo->obtenerLista($consulta);                        
                        if(mysql_num_rows($resultCantidadesNuevas) == 0){//No hay registros de partidas para el nuevo modelo
                            /*Insertamos el nuevo modelo*/
                            $consulta = "INSERT INTO k_solicitud SELECT id_solicitud,(SELECT MAX(id_partida)+1 FROM k_solicitud WHERE id_solicitud = ".$obj->getId_solicitud()."),
                                1,'".$parametros['modelo_'.$contador]."',ClaveCentroCosto,tipo,1,0,IdServicio,IdAnexoClienteCC,IdKServicio,TipoInventario,NoSerie,0,
                                NOW(),'".$_SESSION['user']."',NOW(),'$pantalla_cambio','".$_SESSION['user']."',IdDetalleVD,ReporteRetiro,Ubicacion 
                                FROM k_solicitud WHERE id_solicitud = ".$obj->getId_solicitud()." AND id_partida = $partida;";
                            $catalogo->obtenerLista($consulta);                                
                        }else{//Si hay registros de partidas para el nuevo modelo
                            /*Actualizamos cantidades del nuevo modelo*/
                            $consulta = ("UPDATE k_solicitud SET cantidad = cantidad + 1, cantidad_autorizada = cantidad_autorizada + 1,
                                UsuarioUltimaModificacion = '".$_SESSION['user']."', FechaUltimaModificacion = NOW(),Pantalla = '$pantalla_cambio'
                                WHERE id_solicitud = ".$obj->getId_solicitud()." AND Modelo = '".$parametros['modelo_'.$contador]."' AND tipo = 0;");                                
                            $catalogo->obtenerLista($consulta);                                
                        }
                        
                        /////////////////////////Original
                        /*Procesamos las cantidades en el modelo original*/
                        $cantidad = (int)$rsCantidades['cantidad']; $cantidad --;
                        $cantidad_autorizada = (int)$rsCantidades['cantidad_autorizada']; $cantidad_autorizada--;
                        if($cantidad > 0){//Si no se cambiaron todos los equipos                                                        
                            //Descontamos en uno las cantidades de la partida actual
                            $catalogo->obtenerLista("UPDATE k_solicitud SET cantidad = cantidad - 1, cantidad_autorizada = cantidad_autorizada - 1,
                                UsuarioUltimaModificacion = '".$_SESSION['user']."', FechaUltimaModificacion = NOW(),Pantalla = '$pantalla_cambio'
                                WHERE id_solicitud = ".$obj->getId_solicitud()." AND id_partida = $partida;");
                        }else{                            
                            //Ponemos en cero las cantidades de la partida actual
                            $catalogo->obtenerLista("UPDATE k_solicitud SET cantidad = 0, cantidad_autorizada = 0,
                                UsuarioUltimaModificacion = '".$_SESSION['user']."', FechaUltimaModificacion = NOW(),Pantalla = '$pantalla_cambio'
                                WHERE id_solicitud = ".$obj->getId_solicitud()." AND id_partida = $partida;");
                        }
                    }
                    //Insertamos una incidencia notificando del cambio
                    $incidencia = new Incidencia();
                    $incidencia->setNoSerie($obj->getNoSerie());
                    $incidencia->setFecha(date('Y')."-".date('m')."-".date('d'));
                    $incidencia->setFechaFin($incidencia->getFecha());
                    $incidencia->setDescripcion("Se surtió el equipo ".$parametros['modelo_'.$contador]." en vez de ".$parametros['modelo_original_'.$contador]." en la solicitud ".$obj->getId_solicitud()." por equipos similares (Solicitud de equipos)");
                    $incidencia->setStatus(1);
                    $incidencia->setClaveCentroCosto($obj->getClaveCentroCosto());
                    $incidencia->setId_Ticket("NULL");
                    $incidencia->setActivo(1);
                    $incidencia->setUsuarioCreacion($obj->getUsuarioCreacion());
                    $incidencia->setUsuarioUltimaModificacion($obj->getUsuarioUltimaModificacion());
                    $incidencia->setPantalla($pantalla_cambio);
                    $incidencia->setIdTipoIncidencia(7);
                    if(!$incidencia->newRegistro()){
                        echo "<br/>No se pudo guardar la incidencia de cambio de modelo<br/>";
                    }
                }                                                
                
                $obj->setActivo("1");                
                $obj->setPantalla('Configuracion de equipo');
                $obj->setIdAlmacen($parametros['almacen_sol_' . $contador]);
                
                if (isset($parametros['editar']) && $parametros['editar'] == "false") {
                    if ($obj->newRegistroRapido()) {
                        /* Guardamos la lectura registrada del equipo */
                        $fa = false;
                        $equipo = new EquipoCaracteristicasFormatoServicio();
                        $result2 = $equipo->getCaracteristicasByParte($parametros['modelo_' . $contador]);
                        while ($rs2 = mysql_fetch_array($result2)) {
                            if ($rs2['IdFormatoEquipo'] == "3") {
                                $fa = true;
                            }
                        }
                        $guardar_lectura = false;
                        $lectura = new Lectura();
                        $lectura->setNoSerie($parametros['serie_' . $contador]);
                        $lectura->setLecturaCorte("0");
                        $lectura->setIdSolicitud($parametros['solicitud_' . $contador]);
                        $lectura->setActivo(1);
                        $lectura->setUsuarioCreacion($_SESSION['user']);
                        $lectura->setUsuarioUltimaModificacion($_SESSION['user']);
                        $lectura->setPantalla("PHP Movimiento_equipos_solicitud");
                        /* Contador blanco y negro */
                        if (isset($parametros['contador_bn_' . $parametros['serie_' . $contador]])) {
                            $guardar_lectura = true;
                            if (!$fa) {
                                $lectura->setContadorBNPaginas($parametros['contador_bn_' . $parametros['serie_' . $contador]]);
                                $lectura->setContadorBNML("null");
                            } else {
                                $lectura->setContadorBNML($parametros['contador_bn_' . $parametros['serie_' . $contador]]);
                                $lectura->setContadorBNPaginas("null");
                            }
                        } else {
                            $lectura->setContadorBNML("null");
                            $lectura->setContadorBNPaginas("null");
                        }
                        /* Contador color */
                        if (isset($parametros['contador_color_' . $parametros['serie_' . $contador]])) {
                            $guardar_lectura = true;
                            if (!$fa) {
                                $lectura->setContadorColorPaginas($parametros['contador_color_' . $parametros['serie_' . $contador]]);
                                $lectura->setContadorColorML("null");
                            } else {
                                $lectura->setContadorBNML($parametros['contador_color_' . $parametros['serie_' . $contador]]);
                                $lectura->setContadorColorPaginas("null");
                            }
                        } else {
                            $lectura->setContadorColorML("null");
                            $lectura->setContadorColorPaginas("null");
                        }

                        /* Toner cian */
                        if (isset($parametros['toner_cian_' . $parametros['serie_' . $contador]]) && $parametros['toner_cian_' . $parametros['serie_' . $contador]]) {
                            $guardar_lectura = true;
                            $lectura->setNivelTonCian($parametros['toner_cian_' . $parametros['serie_' . $contador]]);
                        } else {
                            $lectura->setNivelTonCian("null");
                        }
                        /* Toner negro */
                        if (isset($parametros['toner_bn_' . $parametros['serie_' . $contador]]) && $parametros['toner_bn_' . $parametros['serie_' . $contador]]!="") {
                            $guardar_lectura = true;
                            $lectura->setNivelTonNegro($parametros['toner_bn_' . $parametros['serie_' . $contador]]);
                        } else {
                            $lectura->setNivelTonNegro("null");
                        }
                        /* Toner amarillo */
                        if (isset($parametros['toner_amarillo_' . $parametros['serie_' . $contador]])  && $parametros['toner_amarillo_' . $parametros['serie_' . $contador]]!="") {
                            $guardar_lectura = true;
                            $lectura->setNivelTonAmarillo($parametros['toner_amarillo_' . $parametros['serie_' . $contador]]);
                        } else {
                            $lectura->setNivelTonAmarillo("null");
                        }
                        /* Toner magenta */
                        if (isset($parametros['toner_magenta_' . $parametros['serie_' . $contador]])  && $parametros['toner_magenta_' . $parametros['serie_' . $contador]]!="") {
                            $guardar_lectura = true;
                            $lectura->setNivelTonMagenta($parametros['toner_magenta_' . $parametros['serie_' . $contador]]);
                        } else {
                            $lectura->setNivelTonMagenta("null");
                        }

                        if ($guardar_lectura) {//Si se tiene que guardar lectura segun los parametros
                            if ($lectura->newRegistro()) {
                                //echo "<br/>Lectura guardada exitosamente<br/>";
                            } else {
                                echo "<br/>Error: no se pudo registrar la lectura<br/>";
                                $series = $series . "" . $obj->getNoSerie() . ",";
                                $hay_error = true;
                            }
                        }
                    } else {
                        $series = $series . "" . $obj->getNoSerie() . ",";
                        $correcto = false;
                    }
                } else {
                    if (!isset($parametros['bitacora_' . $contador])) {/* Si no hay id de bitacora, se tiene que registrar */
                        if ($obj->newRegistroRapido()) {
                            /* Guardamos la lectura registrada del equipo */
                            $fa = false;
                            $equipo = new EquipoCaracteristicasFormatoServicio();
                            $result2 = $equipo->getCaracteristicasByParte($parametros['modelo_' . $contador]);
                            while ($rs2 = mysql_fetch_array($result2)) {
                                if ($rs2['IdFormatoEquipo'] == "3") {
                                    $fa = true;
                                }
                            }
                            $guardar_lectura = false;
                            $lectura = new Lectura();
                            $lectura->setNoSerie($parametros['serie_' . $contador]);
                            $lectura->setLecturaCorte("0");
                            $lectura->setIdSolicitud($parametros['solicitud_' . $contador]);
                            $lectura->setActivo(1);
                            $lectura->setUsuarioCreacion($_SESSION['user']);
                            $lectura->setUsuarioUltimaModificacion($_SESSION['user']);
                            $lectura->setPantalla("PHP Movimiento_equipos_solicitud");
                            /* Contador blanco y negro */
                            if (isset($parametros['contador_bn_' . $parametros['serie_' . $contador]])) {
                                $guardar_lectura = true;
                                if (!$fa) {
                                    $lectura->setContadorBNPaginas($parametros['contador_bn_' . $parametros['serie_' . $contador]]);
                                    $lectura->setContadorBNML("null");
                                } else {
                                    $lectura->setContadorBNML($parametros['contador_bn_' . $parametros['serie_' . $contador]]);
                                    $lectura->setContadorBNPaginas("null");
                                }
                            } else {
                                $lectura->setContadorBNML("null");
                                $lectura->setContadorBNPaginas("null");
                            }
                            /* Contador color */
                            if (isset($parametros['contador_color_' . $parametros['serie_' . $contador]])) {
                                $guardar_lectura = true;
                                if (!$fa) {
                                    $lectura->setContadorColorPaginas($parametros['contador_color_' . $parametros['serie_' . $contador]]);
                                    $lectura->setContadorColorML("null");
                                } else {
                                    $lectura->setContadorBNML($parametros['contador_color_' . $parametros['serie_' . $contador]]);
                                    $lectura->setContadorColorPaginas("null");
                                }
                            } else {
                                $lectura->setContadorColorML("null");
                                $lectura->setContadorColorPaginas("null");
                            }

                            /* Toner cian */
                            if (isset($parametros['toner_cian_' . $parametros['serie_' . $contador]])) {
                                $guardar_lectura = true;
                                $lectura->setNivelTonCian($parametros['toner_cian_' . $parametros['serie_' . $contador]]);
                            } else {
                                $lectura->setNivelTonCian("null");
                            }
                            /* Toner negro */
                            if (isset($parametros['toner_bn_' . $parametros['serie_' . $contador]])) {
                                $guardar_lectura = true;
                                $lectura->setNivelTonNegro($parametros['toner_bn_' . $parametros['serie_' . $contador]]);
                            } else {
                                $lectura->setNivelTonNegro("null");
                            }
                            /* Toner amarillo */
                            if (isset($parametros['toner_amarillo_' . $parametros['serie_' . $contador]])) {
                                $guardar_lectura = true;
                                $lectura->setNivelTonAmarillo($parametros['toner_amarillo_' . $parametros['serie_' . $contador]]);
                            } else {
                                $lectura->setNivelTonAmarillo("null");
                            }
                            /* Toner magenta */
                            if (isset($parametros['toner_magenta_' . $parametros['serie_' . $contador]])) {
                                $guardar_lectura = true;
                                $lectura->setNivelTonMagenta($parametros['toner_magenta_' . $parametros['serie_' . $contador]]);
                            } else {
                                $lectura->setNivelTonMagenta("null");
                            }

                            if ($guardar_lectura) {//Si se tiene que guardar lectura segun los parametros
                                if ($lectura->newRegistro()) {
                                    //echo "<br/>Lectura guardada exitosamente<br/>";
                                } else {
                                    echo "<br/>Error: no se pudo registrar la lectura";
                                    $series = $series . "" . $obj->getNoSerie() . ",";
                                    $hay_error = true;
                                }
                            }
                        } else {
                            $series = $series . "" . $obj->getNoSerie() . ",";
                            $correcto = false;
                        }
                    }
                }
            }
        }
        
        for ($contador = 0; $contador <= intval($parametros['total_componentes']); $contador++) {//Guardamos todos los componentes
            if (isset($parametros['almacen_sol2_' . $contador]) && $parametros['almacen_sol2_' . $contador] != "" 
                    && isset($parametros['cantidad2_sur_'.$contador]) && $parametros['cantidad2_sur_'.$contador]!="") {
                $consulta = "SELECT * FROM `k_solicitud_asignado` WHERE id_solicitud = " . $parametros['solicitud2_' . $contador] . " 
                    AND id_partida = " . $parametros['partida_' . $contador] . ";";
                $result = $catalogo->obtenerLista($consulta);                
                
                
                if($parametros['modelo2_' . $contador] != $parametros['modelo2_' . $contador . '_original']){//Hubo un cabio de toner, de lo solicitad a lo entregado
                    $consultaCompatibles = "UPDATE k_solicitud SET Modelo = '".$parametros['modelo2_' . $contador]."', UsuarioUltimaModificacion = '".$_SESSION['user']."', "
                        . "FechaUltimaModificacion = NOW(), Pantalla = '$pantalla cambio toner' WHERE id_partida = " . $parametros['partida_' . $contador] . " AND id_solicitud = " . $parametros['solicitud2_' . $contador] . ";";                            
                    
                    $catalogo->obtenerLista($consultaCompatibles);                            
                }
                
                if (mysql_num_rows($result) > 0) {//Si ya está insertado esta registro                                                            
                    if ($parametros["almacen_" . $contador . "_seleccionado"] != $parametros['almacen_sol2_' . $contador]) {//Si se cambio de almacen en el momento de editar
                        /* Desapartamos lo que había apartado en el almacen anterior */
                        /*Verificamos que no entren existencias negativas*/
                        $cantidad = $parametros['cantidad2_sur_' . $contador];
                        $almacenComponente = new AlmacenComponente();                                                
                        
                        if($almacenComponente->getRegistroById($parametros['modelo2_' . $contador], $parametros["almacen_" . $contador . "_seleccionado"])){
                            if($cantidad > $almacenComponente->getApartados()){
                                $log = new Log();
                                $log->setConsulta("Intento de registrar existencias negativas ($cantidad)");
                                $log->setSeccion($pantalla);
                                $log->setIdUsuario($_SESSION['idUsuario']);
                                $log->setTipo("Incidencia sistema");
                                $log->newRegistro();
                                $cantidad = $almacenComponente->getApartados();
                            }
                        }
                        $consulta = "UPDATE `k_almacencomponente` SET cantidad_existencia = cantidad_existencia + " . $parametros['cantidad2_sur_' . $contador] . ", 
                            cantidad_apartados = cantidad_apartados - " . $cantidad . ", FechaUltimaModificacion = NOW(), 
                            UsuarioUltimaModificacion = '".$_SESSION['user']."',Pantalla = '$pantalla' 
                            WHERE id_almacen = " . $parametros["almacen_" . $contador . "_seleccionado"] . " AND NoParte = '" . $parametros['modelo2_' . $contador] . "';";                        
                        $catalogo->obtenerLista($consulta);
                        
                        
                        /* Apartamos en el nuevo almacen lo solicitado */                        
                        $cantidad = $parametros['cantidad2_sur_' . $contador];
                        /*Verificamos que no entren existencias negativas*/
                        $almacenComponente = new AlmacenComponente();
                        if($almacenComponente->getRegistroById($parametros['modelo2_' . $contador], $parametros['almacen_sol2_' . $contador])){
                            if($cantidad > $almacenComponente->getExistencia()){
                                $log = new Log();
                                $log->setConsulta("Intento de registrar existencias negativas ($cantidad)");
                                $log->setSeccion($pantalla);
                                $log->setIdUsuario($_SESSION['idUsuario']);
                                $log->setTipo("Incidencia sistema");
                                $log->newRegistro();
                                $cantidad = $almacenComponente->getExistencia();
                            }
                        }
                        
                        $consulta = "UPDATE `k_almacencomponente` SET cantidad_existencia = cantidad_existencia - " . $cantidad . ", 
                            cantidad_apartados = cantidad_apartados + " . $parametros['cantidad2_sur_' . $contador] . ", FechaUltimaModificacion = NOW(), UsuarioUltimaModificacion = '".$_SESSION['user']."', 
                            Pantalla = '$pantalla'  
                            WHERE id_almacen = " . $parametros['almacen_sol2_' . $contador] . " AND NoParte = '" . $parametros['modelo2_' . $contador] . "';";
                        
                        $catalogo->obtenerLista($consulta);
                    }
                    //Actualizamos la cantidad de componente a surtir
                    $consulta = "UPDATE k_solicitud SET cantidad_surtida = cantidad_surtida + ".$parametros['cantidad2_sur_'.$contador]." 
                        WHERE id_solicitud = ".$parametros['solicitud2_' . $contador]." AND id_partida = ".$parametros['partida_' . $contador];
                    $catalogo->obtenerLista($consulta);
                    
                    if($parametros['cantidad2_sur_'.$contador]!="" && $parametros['cantidad2_sur_'.$contador]!="0"){
                        /* Apartamos en el nuevo almacen lo solicitado */                        
                        $cantidad = $parametros['cantidad2_sur_' . $contador];
                        /*Verificamos que no entren existencias negativas*/
                        $almacenComponente = new AlmacenComponente();
                        if($almacenComponente->getRegistroById($parametros['modelo2_' . $contador], $parametros['almacen_sol2_' . $contador])){
                            if($cantidad > $almacenComponente->getExistencia()){
                                $log = new Log();
                                $log->setConsulta("Intento de registrar existencias negativas ($cantidad)");
                                $log->setSeccion($pantalla);
                                $log->setIdUsuario($_SESSION['idUsuario']);
                                $log->setTipo("Incidencia sistema");
                                $log->newRegistro();
                                $cantidad = $almacenComponente->getExistencia();
                            }
                        }
                        //Apartamos los componentes y descontamos las existencias
                        $consulta = "UPDATE `k_almacencomponente` SET cantidad_existencia = cantidad_existencia - " . $cantidad . ", 
                            cantidad_apartados = cantidad_apartados + " . $parametros['cantidad2_sur_' . $contador] . ", FechaUltimaModificacion = NOW(), UsuarioUltimaModificacion = '".$_SESSION['user']."',
                            Pantalla = '$pantalla' 
                            WHERE id_almacen = " . $parametros['almacen_sol2_' . $contador] . " AND NoParte = '" . $parametros['modelo2_' . $contador] . "';";
                        $catalogo->obtenerLista($consulta);
                        $componentes_insertados++;
                    }
                    
                    $consulta = "UPDATE k_solicitud_asignado SET IdAlmacen = " . $parametros['almacen_sol2_' . $contador] . ", 
                        UsuarioUltimaModificacion = '" . $_SESSION['user'] . "', FechaUltimaModificacion = NOW(), Pantalla = 'PHP Movimiento_equipos_solicitud' 
                        WHERE id_solicitud = " . $parametros['solicitud2_' . $contador] . " AND id_partida = " . $parametros['partida_' . $contador];
                    $catalogo->obtenerLista($consulta);
                } else {//Nuevo registro
                    //Actualizamos la cantidad de componente a surtir
                    $consulta = "UPDATE k_solicitud SET cantidad_surtida = ".$parametros['cantidad2_sur_'.$contador]." 
                        WHERE id_solicitud = ".$parametros['solicitud2_' . $contador]." AND id_partida = ".$parametros['partida_' . $contador];
                    $catalogo->obtenerLista($consulta);
                    //Insertamos en k_solicitud_asignado, para saber el almacen y la localidad destino
                    $consulta = "INSERT INTO k_solicitud_asignado(id_solicitud, id_partida, NoParte, ClaveCentroCosto, IdAlmacen, UsuarioCreacion, 
                        FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla)
                        VALUES(" . $parametros['solicitud2_' . $contador] . "," . $parametros['partida_' . $contador] . ",
                            '" . $parametros['modelo2_' . $contador] . "','" . $parametros['cc2_' . $contador] . "'," . $parametros['almacen_sol2_' . $contador] . ",
                            '" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Movimiento_equipos_solicitud');";
                    $catalogo->insertarRegistro($consulta);
                    /* Apartamos en el nuevo almacen lo solicitado */
                    
                    $cantidad = $parametros['cantidad2_sur_' . $contador];
                    /*Verificamos que no entren existencias negativas*/
                    $almacenComponente = new AlmacenComponente();
                    if($almacenComponente->getRegistroById($parametros['modelo2_' . $contador], $parametros['almacen_sol2_' . $contador])){
                        if($cantidad > $almacenComponente->getExistencia()){
                            $log = new Log();
                            $log->setConsulta("Intento de registrar existencias negativas ($cantidad)");
                            $log->setSeccion($pantalla);
                            $log->setIdUsuario($_SESSION['idUsuario']);
                            $log->setTipo("Incidencia sistema");
                            $log->newRegistro();
                            $cantidad = $almacenComponente->getExistencia();
                        }
                    }
                    //Apartamos los componentes y descontamos las existencias
                    $consulta = "UPDATE `k_almacencomponente` SET cantidad_existencia = cantidad_existencia - " . $cantidad . ", 
                        cantidad_apartados = cantidad_apartados + " . $parametros['cantidad2_sur_' . $contador] . ", Pantalla = '$pantalla', FechaUltimaModificacion = NOW(), UsuarioUltimaModificacion = '".$_SESSION['user']."'   
                        WHERE id_almacen = " . $parametros['almacen_sol2_' . $contador] . " AND NoParte = '" . $parametros['modelo2_' . $contador] . "';";
                    $catalogo->obtenerLista($consulta);
                    $componentes_insertados++;
                }
            }
        }
    }

    if ($correcto && !$repetidas) {
        if (!empty($series_elegidas) || $componentes_insertados > 0) {
            echo "Todas las series y/o componentes fueron registradas exitosamente";
        } else {
            echo "<br/>No ha elegido nuevas series o componentes a registrar";
            $envio2 = new Envios();
            if ( ($soli->todosEquiposAsignados($parametros['id_solicitud'])) && 
                (
                   $envio2->todosEquiposEnviados($parametros['id_solicitud']) && 
                   $envio2->todosComponentesEnviados($parametros['id_solicitud'])
                )
               ){
                $soli->cambiarEstatusSolicitud(5);
                echo "<br/>La solictud ha sido marcada como surtida ya que todos los equipos y/o componentes fueron atendidos<br/>";       
            }
        }
    } else if (!$correcto && !$repetidas) {
        echo "Hubo un error en las siguientes series: " . $series . " intenta de nuevo por favor";
    }
}
?>
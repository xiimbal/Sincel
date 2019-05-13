<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/AutorizarEspecial.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Bitacora.class.php");
include_once("../../Classes/Inventario.class.php");
include_once("../../Classes/DomicilioTicket.class.php");
include_once("../../Classes/Usuario.class.php"); //Usado en VerAsigna y Asignacion de nuevo Ticket con Operador
include_once("../../Classes/Ticket.class.php");
include_once("../../Classes/Puesto.class.php");
$especial = new AutorizarEspecial();

if (isset($_POST['VerAsigna'])) {
    $catalogo = new Catalogo();
    $usuario = new Usuario();
    $usuario->getRegistroById($_POST['IdOperador']);
    $consulta = "SELECT IdTicket FROM `k_tecnicoticket` WHERE IdUsuario = " . $_POST['IdOperador'] . " AND FechaHoraInicio = '" . $_POST['Fecha'] . " " . $_POST['Hora'] . ":00';";
    $result = $catalogo->obtenerLista($consulta);
    if (mysql_num_rows($result) > 0) {
        while ($rs = mysql_fetch_array($result)) {
            echo "<br/>Error: no se puede asignar servicio porque el Operador <b>" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . "</b> ya tiene asignado el servicio <b>" . $rs['IdTicket'] . "</b> para la fecha <b>" . $_POST['Fecha'] . " " . $_POST['Hora'] . "</b>";
            continue;
        }
    } else {
        echo "";
    }
} else {
    if (isset($_POST['Origen']) || isset($_POST['Destino'])) {
        $origenR = "";
        $detinoR = "";
        $catalogoc = new Catalogo();
        if ($_POST['Origen'] != "0") {
            $tabla = explode("-", $_POST['Origen']); //1==c_ubicacioens 2==c_especial
            if ($tabla[0] == 1) {
                $query = $catalogoc->obtenerLista("SELECT * FROM c_ubicaciones WHERE IdUbicacion=" . $tabla[1] . "");
                if (mysql_num_rows($query) > 0) {
                    $rs = mysql_fetch_array($query);
                    $origenR = $rs['Calle'] . "///:///" . $rs['NoExterior'] . "///:///SIN///:///" . $rs['Colonia'] . "///:///SinCiudad///:///" . $rs['Delegacion'] . "///:///" . $rs['CodigoPostal'] . "///:///SinLocalidad///:///" . $rs['Estado'] . "///:///" . $rs['Latitud'] . "///:///" . $rs['Longitud'] . "///:///280///:///" . $rs['Descripcion'];
                }
            } else if ($tabla[0] == 2) {
                $query = $catalogoc->obtenerLista("SELECT * FROM c_especial WHERE idEspecial=" . $tabla[1] . "");
                if (mysql_num_rows($query) > 0) {
                    $rs = mysql_fetch_array($query);
                    $origenR = $rs['Calle_or'] . "///:///" . $rs['NoExterior_or'] . "///:///" . $rs['NoInterior_or'] . "///:///" . $rs['Colonia_or'] . "///:///" . $rs['Ciudad_or'] . "///:///" . $rs['Delegacion_or'] . "///:///" . $rs['CodigoPostal_or'] . "///:///" . $rs['Localidad_or'] . "///:///" . $rs['Estado_or'] . "///:///" . $rs['Latitud_or'] . "///:///" . $rs['Longitud_or'] . "///:///" . $rs['Cuadrante'] . "///:///" . $rs['Origen'];
                }
            }
           
        }
        if ($_POST['Destino'] != "0") {
            $tabla = explode("-", $_POST['Destino']); //1==c_ubicacioens 2==c_especial
            if ($tabla[0] == 1) {
                $query = $catalogoc->obtenerLista("SELECT * FROM c_ubicaciones WHERE IdUbicacion=" . $tabla[1] . "");
                if (mysql_num_rows($query) > 0) {
                    $rs = mysql_fetch_array($query);
                    $detinoR = $rs['Calle'] . "///:///" . $rs['NoExterior'] . "///:///SIN///:///" . $rs['Colonia'] . "///:///SinCiudad///:///" . $rs['Delegacion'] . "///:///" . $rs['CodigoPostal'] . "///:///SinLocalidad///:///" . $rs['Estado'] . "///:///" . $rs['Latitud'] . "///:///" . $rs['Longitud'] . "///:///" . $rs['Descripcion'];
                }
            } else if ($tabla[0] == 2) {
                $query = $catalogoc->obtenerLista("SELECT * FROM c_especial WHERE idEspecial=" . $tabla[1] . "");
                if (mysql_num_rows($query) > 0) {
                    $rs = mysql_fetch_array($query);
                    $detinoR = $rs['Calle_des'] . "///:///" . $rs['NoExterior_des'] . "///:///" . $rs['NoInterior_des'] . "///:///" . $rs['Colonia_des'] . "///:///" . $rs['Ciudad_des'] . "///:///" . $rs['Delegacion_des'] . "///:///" . $rs['CodigoPostal_des'] . "///:///" . $rs['Localidad_des'] . "///:///" . $rs['Estado_des'] . "///:///" . $rs['Latitud_des'] . "///:///" . $rs['Longitud_des'] . "///:///" . $rs['Destino'];
                }
            }
            
        }

        echo $origenR . "+_+" . $detinoR;
    } else {
        if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
            $especial->setIdEspecial($_GET['id']);
            if ($especial->deleteRegistro()) {
                echo "El viaje se eliminó correctamente";
            } else {
                echo "El viaje no se pudo eliminar, ya que contiene datos asociados.";
            }
        } else {
            if (isset($_POST['form'])) {
                $parametros = "";
                parse_str($_POST['form'], $parametros);
            }            

            $especial->setIdEmpleado($parametros['slcEmpleado']);
            if ($parametros['contacto'] > 0) {
                $especial->setContacto($parametros['contacto']);
            } else {
                $catalogoc = new Catalogo();
                $query_cont = $catalogoc->obtenerLista("SELECT IdFormaContacto FROM c_domicilio_usturno WHERE IdUsuario=" . $parametros['slcEmpleado'] . "");
                $rs = mysql_fetch_array($query_cont);
                if ($rs['IdFormaContacto'] != NULL) {
                    $especial->setContacto($rs['IdFormaContacto']);
                } else {
                    $especial->setContacto("NULL");
                }
            }
            $especial->setDatoContacto($parametros['txtDatoContacto']);
            $especial->setTipoServicio($parametros['tiposer']);
            $especial->setIdCampania($parametros['slcCampania']);
            $especial->setIdTurno($parametros['slcTurno']);
            $especial->setHora($parametros['hora'] . ":".$parametros['minuto'].":00");
            $especial->setFecha($parametros['fecha']);

            $especial->setOrigen($parametros['txtOrigen']);
            $especial->setDestino($parametros['txtDestino']);

            $especial->setActivo(1);
            $especial->setUsuarioCreacion($_SESSION['user']);
            $especial->setUsuarioUltimaModificacion($_SESSION['user']);
            $especial->setPantalla('PHP catalogos Autorizar Especial');

            $especial->setCalle_or($parametros['txtCalle_or']);
            $especial->setExterior_or($parametros['txtExterior_or']);
            $especial->setInterior_or($parametros['txtInterior_or']);
            $especial->setColonia_or($parametros['txtColonia_or']);
            $especial->setCiudad_or($parametros['txtCiudad_or']);
            $especial->setDelegacion_or($parametros['txtDelegacion_or']);
            $especial->setCp_or($parametros['txtcp_or']);
            $especial->setLocalidad_or($parametros['txtLocalidad_or']);
            $especial->setEstado_or($parametros['slcEstado_or']);
            $especial->setLatitud_or($parametros['Latitud_or']);
            $especial->setLongitud_or($parametros['Longitud_or']);
            $especial->setComentario_or($parametros['Comentario_or']);
            $especial->setCuadrante($parametros['area']);

            $ultimo_index = (int)$parametros["TotalEscalas"] - 1;
            $especial->setCalle_des($parametros['txtCalle_des'.$ultimo_index]);
            $especial->setExterior_des($parametros['txtExterior_des'.$ultimo_index]);
            $especial->setInterior_des($parametros['txtInterior_des'.$ultimo_index]);
            $especial->setColonia_des($parametros['txtColonia_des'.$ultimo_index]);
            $especial->setCiudad_des($parametros['txtCiudad_des'.$ultimo_index]);
            $especial->setDelegacion_des($parametros['txtDelegacion_des'.$ultimo_index]);
            $especial->setCp_des($parametros['txtcp_des'.$ultimo_index]);
            $especial->setLocalidad_des($parametros['txtLocalidad_des'.$ultimo_index]);
            $especial->setEstado_des($parametros['slcEstado_des'.$ultimo_index]);
            $especial->setLatitud_des($parametros['Latitud_des'.$ultimo_index]);
            $especial->setLongitud_des($parametros['Longitud_des'.$ultimo_index]);
            $especial->setComentario_des($parametros['Comentario_des'.$ultimo_index]);
            
            $especial->setPrecioParticular($parametros['costo_servicio']);
            $especial->setNombre_ruta($parametros['nombre_ruta']);
            $especial->setInformacion($parametros['txtInformacion']);
            $idRuta = "";

            if ( (!isset($_POST['ruta_boton']) && isset($parametros['id']) && $parametros['id'] == "") ||
                    (isset($_POST['ruta_boton']) && $parametros['ruta'] == "")) {/* Si el id esta vacio, hay que insertar un NUEVO registro */
                if (
                        (isset($_POST['ruta_boton']) && $_POST['ruta']=="" && $especial->newRegistroRuta()) || 
                        $especial->newRegistro()
                    ) {
                    if(!isset($_POST['ruta_boton'])){                        
                        echo "El viaje de <b>" . $especial->getOrigen() . "</b> a <b>" . $especial->getDestino() . "</b> se registró correctamente";
                    }else{
                        $idRuta = $especial->getIdEspecial();
                        echo "La ruta <b>" . $especial->getNombre_ruta() . "</b> se registró correctamente";
                    }
                    
                    if (!isset($_POST['ruta_boton']) && isset($_POST['auto']) && $_POST['auto'] == 1) {
                        $catalogo = new Catalogo();
                        $query = $catalogo->obtenerLista("SELECT Loggin, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS Nombre FROM c_usuario AS cu WHERE cu.IdUsuario='" . $especial->getIdEmpleado() . "'");
                        $rs = mysql_fetch_array($query);
                        $Loggin = $rs['Loggin'];
                        $Nombre_Usuario = $rs['Nombre'];
                        $areaAtencion = $especial->getCuadrante();

                        $query_uautoriza = $catalogo->obtenerLista("SELECT IdUsuario FROM c_usuario WHERE Loggin='" . $especial->getUsuarioUltimaModificacion() . "'");
                        $rsua = mysql_fetch_array($query_uautoriza);
                        $idUsuario_autoriza = $rsua['IdUsuario'];

                        $query = $catalogo->obtenerLista("SELECT ca.Descripcion, NombreRazonSocial, ca.ClaveCentroCosto, ccc.ClaveCliente, ccc.Nombre AS NombreCentroCosto FROM c_cliente AS cc INNER JOIN c_centrocosto AS ccc ON cc.ClaveCliente=ccc.ClaveCliente
                                                          JOIN c_area AS ca ON ccc.ClaveCentroCosto=ca.ClaveCentroCosto WHERE IdArea='" . $especial->getIdCampania() . "'");
                        $rs = mysql_fetch_array($query);
                        $NombreCliente = ($rs['NombreRazonSocial']);
                        $ClaveCentroCosto = ($rs['ClaveCentroCosto']);
                        $ClaveCliente = ($rs['ClaveCliente']);
                        $NombreCentroCosto = ($rs['NombreCentroCosto']);
                        $CampaniaU = $rs['Descripcion'];

                        $query = $catalogo->obtenerLista("SELECT * FROM c_contacto WHERE ClaveEspecialContacto='" . $ClaveCliente . "';");
                        $rs = mysql_fetch_array($query);
                        $NombreResp = $rs['Nombre'];
                        $TelefonoResp = $rs['Telefono'];
                        $CelularResp = $rs['Celular'];
                        $CorreoResp = $rs['CorreoElectronico'];

                        $query = $catalogo->obtenerLista("SELECT * FROM c_equipo ORDER BY NoParte ASC LIMIT 1");
                        $rs = mysql_fetch_array($query);
                        $Modelo = $rs['Modelo'];
                        $NoParte = $rs['NoParte'];

                        $bitacora = new Bitacora();
                        $bitacora->setNoSerie($Loggin);
                        if (!$bitacora->verficarExistencia()) {//Si se desea crear la serie
                            $Inventario = new Inventario();

                            if (!$Inventario->insertarInventarioValidando($Loggin, $NoParte, "", $ClaveCentroCosto, $ClaveCliente, "", FALSE)) {
                                //echo "<br/>Error: NO se registró el Loggin";
                            } else {
                                //echo "<br/>Se registró el Loggin correctamente";
                            }
                        }

                        $consulta = "INSERT INTO c_ticket (
                                FechaHora,Usuario,EstadoDeTicket,TipoReporte,
                                ActualizarInfoEstatCobra, ActualizarInfoCliente,
                                NombreCliente,ClaveCentroCosto,ClaveCliente,NombreCentroCosto,
                                NoSerieEquipo,ModeloEquipo,ActualizarInfoEquipo,
                                 NombreResp,Telefono1Resp,Extension1Resp,Telefono2Resp,Extension2Resp,CelularResp,CorreoEResp,HorarioAtenInicResp,HorarioAtenFinResp,
                                 NombreAtenc,Telefono1Atenc,Extension1Atenc,Telefono2Atenc,Extension2Atenc,CorreoEAtenc,CelularAtenc,HorarioAtenInicAtenc,HorarioAtenFinAtenc,
                                NoTicketCliente,NoTicketDistribuidor,FechHoraInicRep,
                                DescripcionReporte,ObservacionAdicional,AreaAtencion,
                                Activo,UsuarioCreacion,FechaCreacion, FechaUltimaModificacion,UsuarioUltimaModificacion,Pantalla,
                                Ubicacion,UbicacionEmp,FechaCheckIn,FechaCheckOut, Prioridad) 
                                VALUES('" . $especial->getFecha() . " " . $especial->getHora() . "', '" . $especial->getUsuarioUltimaModificacion() . "', 3, 1,
                                     0,0,
                                     '" . $NombreCliente . "','" . $ClaveCentroCosto . "','" . $ClaveCliente . "','" . $NombreCentroCosto . "',
                                     '" . $Loggin . "','" . $Modelo . "',0,
                                     '" . $NombreResp . "','" . $TelefonoResp . "',NULL,0,0,'" . $CelularResp . "','" . $CorreoResp . "',NULL,NULL,
                                     NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,
                                     NULL,NULL,now(),  
                                     '".$especial->getInformacion()."',NULL," . $areaAtencion . ",
                                     1,'" . $especial->getUsuarioCreacion() . "',NOW(),NOW(),'" . $especial->getUsuarioUltimaModificacion() . "','" . $especial->getPantalla() . "',
                                         1,NULL,0, 0, NULL);";

                        $catalogo = new Catalogo();
                        
                        $idTicket = $catalogo->insertarRegistro($consulta);
                        if ($idTicket != NULL && $idTicket != 0) {
                            echo "<br/>El Servicio (<b>" . $idTicket . "</b>) del usuario <b>" . $Nombre_Usuario . "</b> se registro correctamente";
                            $catalogo = new Catalogo();

                            $query = $catalogo->obtenerLista("UPDATE c_especial SET idTicket= '" . $idTicket . "',
                                              UsuarioUltimaModificacion = '" . $especial->getUsuarioUltimaModificacion() . "',FechaUltimaModificacion = now(),
                                              Pantalla = '" . $especial->getPantalla() . "' WHERE idEspecial='" . $especial->getIdEspecial() . "';");
                            if ($query == 1) {
                                echo "<br/>El Servicio del usuario '" . $Nombre_Usuario . "' se registro correctamente en los datos de Viajes Reservados y al momento";

                                $domicilioT = new DomicilioTicket();
                                $domicilioT->setIdTicket($idTicket);
                                $domicilioT->setCalle($especial->getCalle_or());
                                $domicilioT->setActivo(1);
                                $domicilioT->setCiudad($especial->getCiudad_or());
                                $domicilioT->setClaveZona("NULL");
                                $domicilioT->setCodigoPostal($especial->getCp_or());
                                $domicilioT->setColonia($especial->getColonia_or());
                                $domicilioT->setDelegacion($especial->getDelegacion_or());
                                $query = $catalogo->obtenerLista("SELECT Ciudad FROM c_ciudades WHERE IdCiudad='" . $especial->getEstado_or() . "'");
                                $rse = mysql_fetch_array($query);
                                $domicilioT->setEstado($rse['Ciudad']);
                                $domicilioT->setLatitud($especial->getLatitud_or());
                                $domicilioT->setLongitud($especial->getLongitud_or());
                                $domicilioT->setNoExterior($especial->getExterior_or());
                                $domicilioT->setNoInterior($especial->getInterior_or());
                                $domicilioT->setPais("NULL");
                                $domicilioT->setUsuarioCreacion($especial->getUsuarioCreacion());
                                $domicilioT->setUsuarioUltimaModificacion($especial->getUsuarioUltimaModificacion());
                                $domicilioT->setPantalla($especial->getPantalla());
                                if (!$domicilioT->newRegistro()) {
                                    echo "Error: El domicilio del servicio no se pudo registrar";
                                } else {
                                    if (isset($_GET['Asignar'])) {
                                        $prioridad = "";
                                        $duracion = "";
                                        $unidad = "";
                                        $operador = new Usuario();
                                        $operador->getRegistroById($parametros['operador']);
                                        $puesto = new Puesto();
                                        $puesto->getRegistroById($operador->getPuesto());
                                        $tipoPuesto = $puesto->getNombre();
                                        $ticket = new Ticket();
                                        $ticket->getTicketByID($idTicket);
                                        $ticket->setUsuarioUltimaModificacion($especial->getUsuarioUltimaModificacion());
                                        $ticket->setUsuarioCreacion($especial->getUsuarioCreacion());
                                        $ticket->setPantalla($especial->getPantalla());
                                        if ($ticket->asociarTicketTecnicoGeneral($operador->getId(), $prioridad, $duracion, $unidad, $ticket->getFechaHora())) {
                                            if ($ticket->crearNota($operador->getNombre() . " " . $operador->getPaterno() . " " . $operador->getMaterno() . " para atender en fecha " . $ticket->getFechaHora() . "", $tipoPuesto)) {
                                                echo "<br/>El Servicio <b>$idTicket</b> fue asignado al operador " . $operador->getNombre() . " " . $operador->getPaterno() . " " . $operador->getMaterno() . " para atender en fecha " . $ticket->getFechaHora() . " correctamente";
                                            }
                                        } else {
                                            echo "<br/>No se pudo asignar el Servicio <b>$idTicket</b>";
                                        }
                                    }
                                    
                                    $especial->insertaEscalas($parametros, $idTicket, $NombreCliente, $ClaveCentroCosto, $ClaveCliente, $NombreCentroCosto, $Loggin, $Modelo, 
                                            $NombreResp, $TelefonoResp, $CelularResp, $CorreoResp, $areaAtencion, $pantalla, true, $idRuta);
                                }
                            } else {
                                echo "<br/>El Servicio del usuario '" . $Nombre_Usuario . "' NO se registro correctamente en los datos de Viajes";
                            }
                        } else {
                            echo "<br/>El Servicio del usuario " . $Nombre_Usuario . " NO se registro correctamente";
                        }
                    }else{
                        
                        $especial->insertaEscalas($parametros, "", "", "", "", "", "", "", 
                                            "", "", "", "", "", "", false, $idRuta);
                    }
                } else {
                    echo "Error: El viaje no se registró";
                }
            } else {/* Modificar */
                $especial->setIdEspecial($parametros['id']);
                if(isset($_POST['ruta_boton']) && $_POST['ruta_boton'] != ""){
                    $especial->setIdEspecial($parametros['ruta']);
                }
                if ((isset($_POST['ruta_boton']) && $especial->editRegistroRuta()) || (!isset($_POST['ruta_boton']) && $especial->editRegistro())) {
                    if (isset($parametros['autorizar']) && $parametros['autorizar'] == 2) {
                        echo "";
                    } else {
                        if(!isset($_POST['ruta_boton'])){
                            echo "El Viaje de <b>" . $especial->getOrigen() . "</b> a <b>" . $especial->getDestino() . "</b> se almacenó correctamente";
                        }else{
                            $idRuta = $especial->getIdEspecial();
                            echo "La ruta <b>" . $especial->getNombre_ruta() . "</b> se almacenó correctamente";
                        }
                    }
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT Loggin, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS Nombre FROM c_usuario AS cu WHERE cu.IdUsuario='" . $especial->getIdEmpleado() . "'");
                    $rs = mysql_fetch_array($query);
                    $Loggin = $rs['Loggin'];
                    $Nombre_Usuario = $rs['Nombre'];
                    $areaAtencion = $especial->getCuadrante();
                    if (!isset($_POST['ruta_boton']) && isset($parametros['edit']) && $parametros['edit']) {
                        if (isset($parametros['ticket']) && $parametros['ticket'] != "NULL") {
                            $idTicket = $parametros['ticket'];
                            $catalogo = new Catalogo();
                            $query = $catalogo->obtenerLista("UPDATE c_ticket SET FechaHora='" . $especial->getFecha() . " " . $especial->getHora() . "', AreaAtencion = '" . $areaAtencion . "',
                                              UsuarioUltimaModificacion = '" . $especial->getUsuarioUltimaModificacion() . "',FechaUltimaModificacion = now(),
                                              Pantalla = '" . $especial->getPantalla() . "' WHERE IdTicket = '" . $idTicket . "';");
                            if ($query == 1) {
                                echo "<br/>El viaje con Servicio <b>" . $idTicket . "</b> del usuario <b>'" . $Nombre_Usuario . "'</b> se ha Actualizado";
                                $domicilioT = new DomicilioTicket();
                                $domicilioT->setIdTicket($idTicket);
                                $domicilioT->setCalle($especial->getCalle_or());
                                $domicilioT->setActivo(1);
                                $domicilioT->setCiudad($especial->getCiudad_or());
                                $domicilioT->setClaveZona("NULL");
                                $domicilioT->setCodigoPostal($especial->getCp_or());
                                $domicilioT->setColonia($especial->getColonia_or());
                                $domicilioT->setDelegacion($especial->getDelegacion_or());
                                $query = $catalogo->obtenerLista("SELECT Ciudad FROM c_ciudades WHERE IdCiudad='" . $especial->getEstado_or() . "'");
                                $rse = mysql_fetch_array($query);
                                $domicilioT->setEstado($rse['Ciudad']);
                                $domicilioT->setLatitud($especial->getLatitud_or());
                                $domicilioT->setLongitud($especial->getLongitud_or());
                                $domicilioT->setNoExterior($especial->getExterior_or());
                                $domicilioT->setNoInterior($especial->getInterior_or());
                                $domicilioT->setPais("NULL");
                                $domicilioT->setUsuarioCreacion($especial->getUsuarioCreacion());
                                $domicilioT->setUsuarioUltimaModificacion($especial->getUsuarioUltimaModificacion());
                                $domicilioT->setPantalla($especial->getPantalla());
                                if (!$domicilioT->updateDomicilioTicket()) {
                                    echo "Error: El domicilio del servicio no se pudo modificar";
                                } else {
                                    $ticket = new Ticket();
                                    $ticket->getTicketByID($idTicket);
                                    
                                    $consultaS = "SELECT kt.IdUsuario, cu.Nombre, cu.ApellidoPaterno, cu.ApellidoMaterno, cp.Nombre AS NomPuesto FROM k_tecnicoticket AS kt LEFT JOIN c_usuario AS cu ON cu.IdUsuario=kt.IdUsuario LEFT JOIN c_puesto AS cp ON cu.IdPuesto=cp.IdPuesto WHERE IdTicket=" . $idTicket . ";";
//                                    echo $consultaS;
                                    $query = $catalogo->obtenerLista($consultaS);
                                    if (mysql_num_rows($query) > 0) {
                                        $rse = mysql_fetch_array($query);
                                        $prioridad = "";
                                        $duracion = "";
                                        $unidad = "";
                                        
                                        $ticket->setUsuarioUltimaModificacion($especial->getUsuarioUltimaModificacion());
                                        $ticket->setUsuarioCreacion($especial->getUsuarioCreacion());
                                        $ticket->setPantalla($especial->getPantalla());
                                        $ticket->eliminarAsignaciones(); //Eliminamos asignaciones anteriores    
                                        if ($ticket->asociarTicketTecnicoGeneral($rse['IdUsuario'], $prioridad, $duracion, $unidad, $ticket->getFechaHora())) {
                                            $consultaNota = "SELECT MAX(cnt.IdNotaTicket) AS IdNota FROM c_notaticket AS cnt WHERE cnt.IdTicket = $idTicket AND cnt.IdEstatusAtencion = 22";
                                            $resul = $catalogo->obtenerLista($consultaNota);
                                            $rsN = mysql_fetch_array($resul);
                                            $consulta = ("UPDATE c_notaticket AS cn SET cn.DiagnosticoSol='Asignado a " . $rse['NomPuesto'] . ": " . $rse['Nombre'] . " " . $rse['ApellidoPaterno'] . " " . $rse['ApellidoMaterno'] . " para atender en fecha " . $ticket->getFechaHora() . "', 
                                                          cn.UsuarioUltimaModificacion = '" . $especial->getUsuarioUltimaModificacion() . "',cn.FechaUltimaModificacion = NOW()
                                                          WHERE cn.IdNotaTicket = " . $rsN['IdNota'] . ";");
//                                            echo $consulta;
                                            $update = $catalogo->obtenerLista($consulta);
                                            if ($update == "1") {
                                                echo "<br/>El Servicio <b>$idTicket</b> está asignado al operador " . $rse['Nombre'] . " " . $rse['ApellidoPaterno'] . " " . $rse['ApellidoMaterno'] . " para atender en fecha " . $ticket->getFechaHora() . " correctamente";
                                            }
                                        } else {
                                            echo "<br/>No se pudo reasignar el Servicio <b>$idTicket</b>";
                                        }                                        
                                    }
                                    
                                        $especial->insertaEscalas($parametros, $idTicket, $ticket->getNombreCliente(), $ticket->getClaveCentroCosto(), $ticket->getClaveCliente(), 
                                                $ticket->getNombreCentroCosto(), $ticket->getNoSerieEquipo(), $ticket->getModeloEquipo(), 
                                                $ticket->getNombreResp(), $ticket->getTelefono1Resp(), $ticket->getCelularResp(), $ticket->getCorreoEResp(), $ticket->getAreaAtencion(), $pantalla, true, $idRuta);
                                }
                                                                
                            } else {
                                echo "<br/>El Servicio NO se Modifico";
                            }
                        }
                    }
                    
                    if (!isset($_POST['ruta_boton']) && isset($parametros['autorizar']) && $parametros['autorizar'] == 1) {
                        $catalogo = new Catalogo();
                        $query_uautoriza = $catalogo->obtenerLista("SELECT IdUsuario FROM c_usuario WHERE Loggin='" . $especial->getUsuarioUltimaModificacion() . "'");
                        $rsua = mysql_fetch_array($query_uautoriza);
                        $idUsuario_autoriza = $rsua['IdUsuario'];

                        $query = $catalogo->obtenerLista("SELECT ca.Descripcion, NombreRazonSocial, ca.ClaveCentroCosto, ccc.ClaveCliente, ccc.Nombre AS NombreCentroCosto FROM c_cliente AS cc INNER JOIN c_centrocosto AS ccc ON cc.ClaveCliente=ccc.ClaveCliente
                                                          JOIN c_area AS ca ON ccc.ClaveCentroCosto=ca.ClaveCentroCosto WHERE IdArea='" . $especial->getIdCampania() . "'");
                        $rs = mysql_fetch_array($query);
                        $NombreCliente = ($rs['NombreRazonSocial']);
                        $ClaveCentroCosto = ($rs['ClaveCentroCosto']);
                        $ClaveCliente = ($rs['ClaveCliente']);
                        $NombreCentroCosto = ($rs['NombreCentroCosto']);
                        $CampaniaU = $rs['Descripcion'];

                        $query = $catalogo->obtenerLista("SELECT * FROM c_contacto WHERE ClaveEspecialContacto='" . $ClaveCliente . "';");
                        $rs = mysql_fetch_array($query);
                        $NombreResp = $rs['Nombre'];
                        $TelefonoResp = $rs['Telefono'];
                        $CelularResp = $rs['Celular'];
                        $CorreoResp = $rs['CorreoElectronico'];

                        $query = $catalogo->obtenerLista("SELECT * FROM c_equipo ORDER BY NoParte ASC LIMIT 1");
                        $rs = mysql_fetch_array($query);
                        $Modelo = $rs ['Modelo'];
                        $NoParte = $rs['NoParte'];

//                    $query = $catalogo->obtenerLista("SELECT kpa.IdTicket, kpa.Asistencia, kpa.idK_Plantilla_asistencia FROM k_plantilla_asistencia AS kpa LEFT JOIN
//                                                k_plantilla AS kp ON kpa.idK_Plantilla=kp.idK_Plantilla JOIN c_plantilla AS cp ON cp.idPlantilla=kp.idPlantilla
//                                                WHERE kp.idUsuario='" . $idUsuario[$i] . "' AND cp.idPlantilla='" . $idPlantilla . "'");
//                        $rs = mysql_fetch_array($query);
//                        $idkpa = $rs['idK_Plantilla_asistencia'];
//                        $asis = $rs['Asistencia'];
//                        $ticket_exis = $rs['IdTicket'];

                        $descripcion_Campania = "";

                        $bitacora = new Bitacora();
                        $bitacora->setNoSerie($Loggin);
                        if (!$bitacora->verficarExistencia()) {//Si se desea crear la serie
                            $Inventario = new Inventario();

                            if (!$Inventario->insertarInventarioValidando($Loggin, $NoParte, "", $ClaveCentroCosto, $ClaveCliente, "", FALSE)) {
                                //echo "<br/>Error: NO se registró el Loggin";
                            } else {
                                //echo "<br/>Se registró el Loggin correctamente";
                            }
                        }

                        $consulta = "INSERT INTO c_ticket (
                                FechaHora,Usuario,EstadoDeTicket,TipoReporte,
                                ActualizarInfoEstatCobra, ActualizarInfoCliente,
                                NombreCliente,ClaveCentroCosto,ClaveCliente,NombreCentroCosto,
                                NoSerieEquipo,ModeloEquipo,ActualizarInfoEquipo,
                                 NombreResp,Telefono1Resp,Extension1Resp,Telefono2Resp,Extension2Resp,CelularResp,CorreoEResp,HorarioAtenInicResp,HorarioAtenFinResp,
                                 NombreAtenc,Telefono1Atenc,Extension1Atenc,Telefono2Atenc,Extension2Atenc,CorreoEAtenc,CelularAtenc,HorarioAtenInicAtenc,HorarioAtenFinAtenc,
                                NoTicketCliente,NoTicketDistribuidor,FechHoraInicRep,
                                DescripcionReporte,ObservacionAdicional,AreaAtencion,
                                Activo,UsuarioCreacion,FechaCreacion, FechaUltimaModificacion,UsuarioUltimaModificacion,Pantalla,
                                Ubicacion,UbicacionEmp,FechaCheckIn,FechaCheckOut, Prioridad) 
                                VALUES('" . $especial->getFecha() . " " . $especial->getHora() . "', '" . $especial->getUsuarioUltimaModificacion() . "', 3, 1,
                                     0,0,
                                     '" . $NombreCliente . "','" . $ClaveCentroCosto . "','" . $ClaveCliente . "','" . $NombreCentroCosto . "',
                                     '" . $Loggin . "','" . $Modelo . "',0,
                                     '" . $NombreResp . "','" . $TelefonoResp . "',NULL,0,0,'" . $CelularResp . "','" . $CorreoResp . "',NULL,NULL,
                                     NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,
                                     NULL,NULL,now(),  
                                     '".$especial->getInformacion()."',NULL," . $areaAtencion . ",
                                     1,'" . $especial->getUsuarioCreacion() . "',NOW(),NOW(),'" . $especial->getUsuarioUltimaModificacion() . "','" . $especial->getPantalla() . "',
                                         1,NULL,0, 0, NULL);";

                        $catalogo = new Catalogo();
                        
                        $idTicket = $catalogo->insertarRegistro($consulta);
                        if ($idTicket != NULL && $idTicket != 0) {
                            echo "<br/>El Servicio (<b>" . $idTicket . "</b>) del usuario " . $Nombre_Usuario . " se registro correctamente";
                            $catalogo = new Catalogo();

                            $query = $catalogo->obtenerLista("UPDATE c_especial SET idTicket= '" . $idTicket . "',
                                              UsuarioUltimaModificacion = '" . $especial->getUsuarioUltimaModificacion() . "',FechaUltimaModificacion = now(),
                                              Pantalla = '" . $especial->getPantalla() . "' WHERE idEspecial='" . $especial->getIdEspecial() . "';");
                            if ($query == 1) {
                                echo "<br/>El Servicio del usuario '" . $Nombre_Usuario . "' se registro correctamente en los datos de Viajes Reservados y Al momento";

                                $domicilioT = new DomicilioTicket();
                                $domicilioT->setIdTicket($idTicket);
                                $domicilioT->setCalle($especial->getCalle_or());
                                $domicilioT->setActivo(1);
                                $domicilioT->setCiudad($especial->getCiudad_or());
                                $domicilioT->setClaveZona("NULL");
                                $domicilioT->setCodigoPostal($especial->getCp_or());
                                $domicilioT->setColonia($especial->getColonia_or());
                                $domicilioT->setDelegacion($especial->getDelegacion_or());
                                $query = $catalogo->obtenerLista("SELECT Ciudad FROM c_ciudades WHERE IdCiudad='" . $especial->getEstado_or() . "'");
                                $rse = mysql_fetch_array($query);
                                $domicilioT->setEstado($rse ['Ciudad']);
                                $domicilioT->setLatitud($especial->getLatitud_or());
                                $domicilioT->setLongitud($especial->getLongitud_or());
                                $domicilioT->setNoExterior($especial->getExterior_or());
                                $domicilioT->setNoInterior($especial->getInterior_or());
                                $domicilioT->setPais("NULL");
                                $domicilioT->setUsuarioCreacion($especial->getUsuarioCreacion());
                                $domicilioT->setUsuarioUltimaModificacion($especial->getUsuarioUltimaModificacion());
                                $domicilioT->setPantalla($especial->getPantalla());
                                if (!$domicilioT->newRegistro()) {
                                    echo "Error: El domicilio del ticket no se pudo registrar";
                                } else {
                                    $ticket = new Ticket();
                                    $ticket->getTicketByID($idTicket);
                                    if (isset($_POST['asigna']) && $_POST['asigna'] == 1) {
                                        $prioridad = "";
                                        $duracion = "";
                                        $unidad = "";
                                        $operador = new Usuario();
                                        $operador->getRegistroById($parametros['operador']);
                                        $puesto = new Puesto();
                                        $puesto->getRegistroById($operador->getPuesto());
                                        $tipoPuesto = $puesto->getNombre();
                                        
                                        $ticket->setUsuarioUltimaModificacion($especial->getUsuarioUltimaModificacion());
                                        $ticket->setUsuarioCreacion($especial->getUsuarioCreacion());
                                        $ticket->setPantalla($especial->getPantalla());
                                        $ticket->eliminarAsignaciones(); //Eliminamos asignaciones anteriores    
                                        if ($ticket->asociarTicketTecnicoGeneral($operador->getId(), $prioridad, $duracion, $unidad, $ticket->getFechaHora())) {
                                            if ($ticket->crearNota($operador->getNombre() . " " . $operador->getPaterno() . " " . $operador->getMaterno() . " para atender en fecha " . $ticket->getFechaHora() . "", $tipoPuesto)) {
                                                echo "<br/>El Servicio <b>$idTicket</b> fue asignado al operador " . $operador->getNombre() . " " . $operador->getPaterno() . " " . $operador->getMaterno() . " para atender en fecha " . $ticket->getFechaHora() . " correctamente";
                                            }
                                        } else {
                                            echo "<br/>No se pudo asignar el Servicio <b>$idTicket</b>";
                                        }                                        
                                    }
                                    
                                    $especial->insertaEscalas($parametros, $idTicket, $ticket->getNombreCliente(), $ticket->getClaveCentroCosto(), $ticket->getClaveCliente(), 
                                            $ticket->getNombreCentroCosto(), $ticket->getNoSerieEquipo(), $ticket->getModeloEquipo(), 
                                            $ticket->getNombreResp(), $ticket->getTelefono1Resp(), $ticket->getCelularResp(), $ticket->getCorreoEResp(), $ticket->getAreaAtencion(), $pantalla, true, $idRuta);
                                }
                            } else {
                                echo "<br/>El Servicio del usuario '" . $Nombre_Usuario . "' NO se registro correctamente en los datos de Viajes Reservados y al momento";
                            }
                        } else {
                            echo "<br/>El Servicio del usuario " . $Nombre_Usuario . " NO se registro correctamente";
                        }
                    }else if (!isset($_POST['ruta_boton']) && isset($parametros['autorizar']) && $parametros['autorizar'] == 2) {
                        $idTicket = $parametros['ticket'];
                        $catalogo = new Catalogo();
                        $query = $catalogo->obtenerLista("UPDATE c_ticket SET EstadoDeTicket=4,
                                              UsuarioUltimaModificacion = '" . $especial->getUsuarioUltimaModificacion() . "',FechaUltimaModificacion = now(),
                                              Pantalla = '" . $especial->getPantalla() . "' WHERE IdTicket = '" . $idTicket . "';");
                        if ($query == 1) {
                            echo "<br/>El viaje Reservado o Al momento con Servicio <b>'" . $idTicket . "'</b> del usuario <b>'" . $Nombre_Usuario . "'</b> se ha Desautorizado";
                        } else {
                            echo "<br/>El Servicio NO se Desautorizó";
                        }
                    }else if (!isset($_POST['ruta_boton']) && isset($parametros['autorizar']) && $parametros['autorizar'] == 3) {
                        $idTicket = $parametros['ticket'];
                        $catalogo = new Catalogo();
                        $query = $catalogo->obtenerLista("UPDATE c_ticket SET FechaHora='" . $especial->getFecha() . " " . $especial->getHora() . "', EstadoDeTicket=3, AreaAtencion = '" . $areaAtencion . "',
                                              UsuarioUltimaModificacion = '" . $especial->getUsuarioUltimaModificacion() . "',FechaUltimaModificacion = now(),
                                              Pantalla = '" . $especial->getPantalla() . "' WHERE IdTicket = '" . $idTicket . "';");
                        if ($query == 1) {
                            echo "<br/>El viaje con Servicio <b>" . $idTicket . "</b> del usuario <b>'" . $Nombre_Usuario . "'</b> se ha Autorizado";
                            $domicilioT = new DomicilioTicket();
                            $domicilioT->setIdTicket($idTicket);
                            $domicilioT->setCalle($especial->getCalle_or());
                            $domicilioT->setActivo(1);
                            $domicilioT->setCiudad($especial->getCiudad_or());
                            $domicilioT->setClaveZona("NULL");
                            $domicilioT->setCodigoPostal($especial->getCp_or());
                            $domicilioT->setColonia($especial->getColonia_or());
                            $domicilioT->setDelegacion($especial->getDelegacion_or());
                            $query = $catalogo->obtenerLista("SELECT Ciudad FROM c_ciudades WHERE IdCiudad='" . $especial->getEstado_or() . "'");
                            $rse = mysql_fetch_array($query);
                            $domicilioT->setEstado($rse['Ciudad']);
                            $domicilioT->setLatitud($especial->getLatitud_or());
                            $domicilioT->setLongitud($especial->getLongitud_or());
                            $domicilioT->setNoExterior($especial->getExterior_or());
                            $domicilioT->setNoInterior($especial->getInterior_or());
                            $domicilioT->setPais("NULL");
                            $domicilioT->setUsuarioCreacion($especial->getUsuarioCreacion());
                            $domicilioT->setUsuarioUltimaModificacion($especial->getUsuarioUltimaModificacion());
                            $domicilioT->setPantalla($especial->getPantalla());
                            if (!$domicilioT->updateDomicilioTicket()) {
                                echo "Error: El domicilio del servicio no se pudo modificar";
                            } else {
                                if (isset($_POST['asigna']) && $_POST['asigna'] == 1) {
                                    $prioridad = "";
                                    $duracion = "";
                                    $unidad = "";
                                    $operador = new Usuario();
                                    $operador->getRegistroById($parametros['operador']);
                                    $puesto = new Puesto();
                                    $puesto->getRegistroById($operador->getPuesto());
                                    $tipoPuesto = $puesto->getNombre();
                                    $ticket = new Ticket();
                                    $ticket->getTicketByID($idTicket);
                                    $ticket->setUsuarioUltimaModificacion($especial->getUsuarioUltimaModificacion());
                                    $ticket->setUsuarioCreacion($especial->getUsuarioCreacion());
                                    $ticket->setPantalla($especial->getPantalla());
                                    $ticket->eliminarAsignaciones(); //Eliminamos asignaciones anteriores    
                                    if ($ticket->asociarTicketTecnicoGeneral($operador->getId(), $prioridad, $duracion, $unidad, $ticket->getFechaHora())) {
                                        if ($ticket->crearNota($operador->getNombre() . " " . $operador->getPaterno() . " " . $operador->getMaterno() . " para atender en fecha " . $ticket->getFechaHora() . "", $tipoPuesto)) {
                                            echo "<br/>El Servicio <b>$idTicket</b> fue asignado al operador " . $operador->getNombre() . " " . $operador->getPaterno() . " " . $operador->getMaterno() . " para atender en fecha " . $ticket->getFechaHora() . " correctamente";
                                        }
                                    } else {
                                        echo "<br/>No se pudo asignar el Servicio <b>$idTicket</b>";
                                    }
                                } else {
                                    $ticket = new Ticket();
                                    $ticket->getTicketByID($idTicket);
                                    $consultaS = "SELECT kt.IdUsuario, cu.Nombre, cu.ApellidoPaterno, cu.ApellidoMaterno, cp.Nombre AS NomPuesto FROM k_tecnicoticket AS kt LEFT JOIN c_usuario AS cu ON cu.IdUsuario=kt.IdUsuario LEFT JOIN c_puesto AS cp ON cu.IdPuesto=cp.IdPuesto WHERE IdTicket=" . $idTicket . ";";
//                                    echo $consultaS;
                                    $query = $catalogo->obtenerLista($consultaS);
                                    if (mysql_num_rows($query) > 0) {
                                        $rse = mysql_fetch_array($query);
                                        $prioridad = "";
                                        $duracion = "";
                                        $unidad = "";
                                        
                                        $ticket->setUsuarioUltimaModificacion($especial->getUsuarioUltimaModificacion());
                                        $ticket->setUsuarioCreacion($especial->getUsuarioCreacion());
                                        $ticket->setPantalla($especial->getPantalla());
                                        $ticket->eliminarAsignaciones(); //Eliminamos asignaciones anteriores    
                                        if ($ticket->asociarTicketTecnicoGeneral($rse['IdUsuario'], $prioridad, $duracion, $unidad, $ticket->getFechaHora())) {
                                            $consultaNota = "SELECT MAX(cnt.IdNotaTicket) AS IdNota FROM c_notaticket AS cnt WHERE cnt.IdTicket = $idTicket AND cnt.IdEstatusAtencion = 22";
                                            $resul = $catalogo->obtenerLista($consultaNota);
                                            $rsN = mysql_fetch_array($resul);
                                            $consulta = ("UPDATE c_notaticket AS cn SET cn.DiagnosticoSol='Asignado a " . $rse['NomPuesto'] . ": " . $rse['Nombre'] . " " . $rse['ApellidoPaterno'] . " " . $rse['ApellidoMaterno'] . " para atender en fecha " . $ticket->getFechaHora() . "', 
                                                          cn.UsuarioUltimaModificacion = '" . $especial->getUsuarioUltimaModificacion() . "',cn.FechaUltimaModificacion = NOW()
                                                          WHERE cn.IdNotaTicket = " . $rsN['IdNota'] . ";");
//                                            echo $consulta;
                                            $update = $catalogo->obtenerLista($consulta);
                                            if ($update == "1") {
                                                echo "<br/>El Servicio <b>$idTicket</b> está asignado al operador " . $rse['Nombre'] . " " . $rse['ApellidoPaterno'] . " " . $rse['ApellidoMaterno'] . " para atender en fecha " . $ticket->getFechaHora() . " correctamente";
                                            }
                                        } else {
                                            echo "<br/>No se pudo reasignar el Servicio <b>$idTicket</b>";
                                        }
                                                                              
                                    }
                                    $especial->insertaEscalas($parametros, $idTicket, $ticket->getNombreCliente(), $ticket->getClaveCentroCosto(), $ticket->getClaveCliente(), 
                                                $ticket->getNombreCentroCosto(), $ticket->getNoSerieEquipo(), $ticket->getModeloEquipo(), 
                                                $ticket->getNombreResp(), $ticket->getTelefono1Resp(), $ticket->getCelularResp(), $ticket->getCorreoEResp(), $ticket->getAreaAtencion(), $pantalla, true, $idRuta);
                                }
                            }
                        } else {
                            echo "<br/>El Servicio NO se Autorizó";
                        }
                    }
                } else {
                    echo "Error: El viaje no se modificó";
                }
            }
        }
    }
}
?>
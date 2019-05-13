<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../../Classes/Conexion.class.php");
include_once("../../Classes/AutorizarPlantilla.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Bitacora.class.php");
include_once("../../Classes/Inventario.class.php");
include_once("../../Classes/DomicilioTicket.class.php");

$obj = new AutorizarPlantilla();
$catalogo = new Catalogo;
$Nombre_UsuarioM = array();

if (isset($_GET['idelim'])) {/* Para eliminar el registro con el id recibido por get */
    //print_r($_GET);
    $obj->setIdPlantilla($_GET['idelim']);
    if ($obj->deleteRegistro()) {
        echo "La plantilla se eliminó correctamente";
    } else {
        echo "La plantilla no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }

    //print_r($_POST);
    if (isset($parametros['newPlantilla']) && $parametros['newPlantilla'] == 1) {
        $obj->setIdCampania($parametros['CampaniaFiltro']);
        $obj->setIdTurno($parametros['TurnoFiltro']);
        $obj->setFecha($parametros['txtfecha']);
        $obj->setHora($parametros['hora'] . ":" . $parametros['minutos'] . ":00");
        $obj->setTipoEvento($parametros['tipo_evento']);
        if (isset($parametros['activo']) && $parametros['activo'] == "on") {
            $obj->setActivo(1);
        } else {
            $obj->setActivo(0);
        }
        $obj->setUsuarioCreacion($_SESSION['user']);
        $obj->setUsuarioModificacion($_SESSION['user']);
        $obj->setPantalla('Generar Plantilla');
        $query = $catalogo->obtenerLista("SELECT cd.IdUsuario AS idUsuario, cu.Nombre
                                                            FROM c_domicilio_usturno AS cd LEFT JOIN c_usuario AS cu ON cd.IdUsuario=cu.IdUsuario 
                                                            WHERE cd.IdCampania='" . $obj->getIdCampania() . "' AND cd.IdTurno='" . $obj->getIdTurno() . "' AND cu.IdPuesto=100
                                                            ORDER BY cu.Nombre ASC");
        $t = 0;
        while ($rs1 = mysql_fetch_array($query)) {
            $idUsuario[$t] = $rs1['idUsuario'];
            $t++;
            $asistencia[$t] = 0;
            $comentario[$t] = "";
        }
    } else {
        $obj->setIdCampania($parametros['CampaniaFiltro']);
        $obj->setIdTurno($parametros['TurnoFiltro']);
        $obj->setFecha($parametros['txtfecha']);
        $obj->setHora($parametros['hora'] . ":" . $parametros['minutos'] . ":00");
        $obj->setEstatus($parametros['estatus']);
        $obj->setTipoEvento($parametros['tipo_evento']);
        if (isset($parametros['activo']) && $parametros['activo'] == "on") {
            $obj->setActivo(1);
        } else {
            $obj->setActivo(0);
        }
        $obj->setUsuarioCreacion($_SESSION['user']);
        $obj->setUsuarioModificacion($_SESSION['user']);
        $obj->setPantalla('Autorizar Plantilla');
        $idUsuario = $parametros['idUsuario'];
        $asistencia1 = $parametros['asistencia'];
        //echo $asistencia1;

        for ($j = 0; $j < (count($idUsuario)); $j++) {
            $m = 0;
            for ($l = 0; $l < (count($idUsuario)); $l++) {
                if ($asistencia1[$l] == $idUsuario[$j]) {
                    $asistencia[$j] = 1;
                    $m = 1;
                }
            }if ($m == 0) {
                $asistencia[$j] = 0;
            }
        }
        $comentario = $parametros['comentario'];
    }
    if(!empty($idUsuario)){
    if (isset($parametros['idPlantillaA']) && $parametros['idPlantillaA'] != "") {
        $obj->setIdPlantilla($parametros['idPlantillaA']);
        $idPlantilla = $obj->getIdPlantilla();
        $total = count($idUsuario);
        $j = 0;
        $catalogo = new Catalogo();

        if ($obj->getEstatus() == 0) {

            for ($i = 0; $i < $total; $i++) {
                $query = $catalogo->obtenerLista("SELECT Loggin, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS Nombre FROM c_usuario AS cu WHERE cu.IdUsuario='" . $idUsuario[$i] . "'");
                $rs = mysql_fetch_array($query);
                $Nombre_Usuario = $rs['Nombre'];

                $query = $catalogo->obtenerLista("SELECT kpa.idK_Plantilla_asistencia FROM k_plantilla_asistencia AS kpa INNER JOIN k_plantilla AS kp ON
                                              kp.idK_Plantilla=kpa.idK_Plantilla JOIN c_plantilla AS cp ON
                                              cp.idPlantilla=kp.idPlantilla WHERE cp.idPlantilla='" . $idPlantilla . "' AND kp.idUsuario='" . $idUsuario[$i] . "'");
                $rs = mysql_fetch_array($query);

                $query = $catalogo->obtenerLista("UPDATE k_plantilla_asistencia SET Asistencia =" . $asistencia[$i] . ",Comentario = '" . $comentario[$i] . "', 
                                                Activo = " . $obj->getActivo() . ",UsuarioUltimaModificacion = '" . $obj->getUsuarioModificacion() . "',
                                                FechaUltimaModificacion = now(),Pantalla = '" . $obj->getPantalla() . "' WHERE idK_Plantilla_asistencia='" . $rs['idK_Plantilla_asistencia'] . "';");

                if ($query == 1) {
                    array_push($Nombre_UsuarioM, $Nombre_Usuario);
                    //echo "<br/>La asistencia y comentario del usuario " . $Nombre_Usuario . " se modificaron correctamente";
                } else {
                    echo "<br/>La asistencia y comentario del usuario " . $Nombre_Usuario . " NO se modificaron correctamente";
                }
            }
            if(!empty($Nombre_UsuarioM)){
                echo "<br/> La asistencia y comentarios de los Usuarios <b>".implode(", ", $Nombre_UsuarioM)."</b> se modificaron correctamente";
            }

            $catalogo = new Catalogo();
            $query3 = $catalogo->obtenerLista("UPDATE c_plantilla SET Estatus = '1',UsuarioUltimaModificacion = '" . $obj->getUsuarioModificacion() . "',FechaUltimaModificacion = now(),Pantalla = '" . $obj->getPantalla() . "' WHERE idPlantilla='" . $idPlantilla . "';");

            if ($query3 == 1) {
                echo "<br/>El Estatus de la Plantilla se modifico a actualizado con éxito";
            } else {
                echo "<br/>El Estatus de la Plantilla NO se modifico a actualizado con éxito";
            }
        } else {
            if ($obj->getEstatus() == 1) {
                $listos=0;
                $asisten=0;
                $catalogo = new Catalogo();
                $query_uautoriza = $catalogo->obtenerLista("SELECT IdUsuario FROM c_usuario WHERE Loggin='" . $obj->getUsuarioModificacion() . "'");
                $rsua = mysql_fetch_array($query_uautoriza);
                $idUsuario_autoriza = $rsua['IdUsuario'];

                    $query = $catalogo->obtenerLista("SELECT ca.Descripcion, NombreRazonSocial, ca.ClaveCentroCosto, ccc.ClaveCliente, ccc.Nombre AS NombreCentroCosto FROM c_cliente AS cc INNER JOIN c_centrocosto AS ccc ON cc.ClaveCliente=ccc.ClaveCliente
                                                          JOIN c_area AS ca ON ccc.ClaveCentroCosto=ca.ClaveCentroCosto WHERE IdArea='" . $obj->getIdCampania() . "'");
                    $rs = mysql_fetch_array($query);
                    $obj->setNombreCliente($rs['NombreRazonSocial']);
                    $obj->setClaveCentroCosto($rs['ClaveCentroCosto']);
                    $obj->setClaveCliente($rs['ClaveCliente']);
                    $obj->setNombreCentroCosto($rs['NombreCentroCosto']);
                    $CampaniaU = $rs['Descripcion'];

                    $query = $catalogo->obtenerLista("SELECT * FROM c_contacto WHERE ClaveEspecialContacto='" . $obj->getClaveCliente() . "';");
                    $rs = mysql_fetch_array($query);
                    $NombreResp = $rs['Nombre'];
                    $TelefonoResp = $rs['Telefono'];
                    $CelularResp = $rs['Celular'];
                    $CorreoResp = $rs['CorreoElectronico'];

                    $query = $catalogo->obtenerLista("SELECT * FROM c_equipo ORDER BY NoParte ASC LIMIT 1");
                    $rs = mysql_fetch_array($query);
                    $Modelo = $rs['Modelo'];
                    $NoParte = $rs['NoParte'];

                    $total = count($idUsuario);
                    $ti=0;
                    
                    for ($i = 0; $i < $total; $i++) {

                        $query = $catalogo->obtenerLista("SELECT kpa.IdTicket, kpa.Asistencia, kpa.idK_Plantilla_asistencia FROM k_plantilla_asistencia AS kpa LEFT JOIN
                                                k_plantilla AS kp ON kpa.idK_Plantilla=kp.idK_Plantilla JOIN c_plantilla AS cp ON cp.idPlantilla=kp.idPlantilla
                                                WHERE kp.idUsuario='" . $idUsuario[$i] . "' AND cp.idPlantilla='" . $idPlantilla . "'");
                        $rs = mysql_fetch_array($query);
                        $idkpa = $rs['idK_Plantilla_asistencia'];
                        $asis = $rs['Asistencia'];
                        $ticket_exis = $rs['IdTicket'];

                        $query = $catalogo->obtenerLista("SELECT Loggin, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS Nombre FROM c_usuario AS cu WHERE cu.IdUsuario='" . $idUsuario[$i] . "'");
                        $rs = mysql_fetch_array($query);
                        $Loggin = $rs['Loggin'];
                        $Nombre_Usuario = $rs['Nombre'];
                        $areaAtencion = "";
                        $descripcion_Campania="";
                        if($asis == 1) {
                            $asisten++;
                            if ($obj->getTipoEvento() == 1) {
                                $query = $catalogo->obtenerLista("SELECT IdArea FROM c_domicilio_usturno WHERE IdUsuario='" . $idUsuario[$i] . "'");
                                $rsua = mysql_fetch_array($query);
                                $areaAtencion = $rsua['IdArea'];
                            } else {
                                if ($obj->getTipoEvento() == 2) {
                                    $query = $catalogo->obtenerLista("SELECT IdEstado, Descripcion FROM c_area WHERE IdArea='" . $obj->getIdCampania() . "'");
                                    $rsua = mysql_fetch_array($query);
                                    $areaAtencion = $rsua['IdEstado'];
                                    $descripcion_Campania = $rsua['Descripcion'];
                                }
                            }
                            if ($areaAtencion == "") {
                                //$areaAtencion = "NULL";
                                if($obj->getTipoEvento() == 1){
                                echo "Al usuario " . $idUsuario[$i] . " ".$Nombre_Usuario." no se le ha asignado Area de Domicilio, asignesela para autorizar.";
                                break;
                                }else{
                                    if ($obj->getTipoEvento() == 2) {
                                        echo "A la Campaña " . $descripcion_Campania . " no se le ha asignado Area de Localidad";// Area de Localidad obligatorias en formulario
                                        break;
                                    }
                                }
                            }
                        }

                        if (($ticket_exis == 0 || $ticket_exis == "") && $asis == 1) { //Si asiste y no tiene Ticket: se genera un Ticket
                            $bitacora = new Bitacora();
                            $bitacora->setNoSerie($Loggin);
                            if (!$bitacora->verficarExistencia()) {//Si se desea crear la serie
                                $Inventario = new Inventario();

                                if (!$Inventario->insertarInventarioValidando($Loggin, $NoParte, "", $obj->getClaveCentroCosto(), $obj->getClaveCliente(), "", FALSE)) {
                                    echo " ";//"<br/>Error: NO se registró el Loggin";
                                } else {
                                    echo " ";//"<br/>Se registró un Loggin correctamente";
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
                                VALUES(NOW(), '" . $obj->getUsuarioModificacion() . "', 3, 1,
                                     0,0,
                                     '" . $obj->getNombreCliente() . "','" . $obj->getClaveCentroCosto() . "','" . $obj->getClaveCliente() . "','" . $obj->getNombreCentroCosto() . "',
                                     '" . $Loggin . "','" . $Modelo . "',0,
                                     '" . $NombreResp . "','" . $TelefonoResp . "',NULL,0,0,'" . $CelularResp . "','" . $CorreoResp . "',NULL,NULL,
                                     NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,
                                     NULL,NULL,now(),  
                                     'Viaje de Usuario',NULL," . $areaAtencion . ",
                                     1,'" . $obj->getUsuarioCreacion() . "',NOW(),NOW(),'" . $obj->getUsuarioModificacion() . "','" . $obj->getPantalla() . "',
                                         1,NULL,0, 0, NULL);";

                            $catalogo = new Catalogo();
                            //print_r($consulta);
                            $idTicket = $catalogo->insertarRegistro($consulta);
                            if ($idTicket != NULL && $idTicket != 0) {
                                //echo "<br/>El Ticket del usuario " . $Nombre_Usuario . " se registro correctamente con número ".$idTicket."";
                                $NombreT[$ti]=$Nombre_Usuario;
                                $ticketT[$ti]=$idTicket;
                                $ti++;
                                $catalogo = new Catalogo();

                                $query = $catalogo->obtenerLista("UPDATE k_plantilla_asistencia SET idTicket= '" . $idTicket . "',
                                              UsuarioUltimaModificacion = '" . $obj->getUsuarioModificacion() . "',FechaUltimaModificacion = now(),
                                              Pantalla = '" . $obj->getPantalla() . "' WHERE idK_Plantilla_asistencia='" . $idkpa . "';");
                                if ($query == 1) {
                                    array_push($Nombre_UsuarioM, $Nombre_Usuario); //echo "<br/>El Ticket del usuario '" . $Nombre_Usuario . "' se registro correctamente en los datos de Plantilla";
                                   if($obj->getTipoEvento() == 1){
                                        $catalogo = new Catalogo();
                                        $query = $catalogo->obtenerLista("SELECT * FROM c_domicilio_usturno WHERE IdUsuario='" . $idUsuario[$i] . "'");
                                        $rsua = mysql_fetch_array($query);
                                        $domicilioT = new DomicilioTicket();
                                        $domicilioT->setIdTicket($idTicket);
                                        $domicilioT->setCalle($rsua['Calle']);
                                        $domicilioT->setActivo(1);
                                        $domicilioT->setCiudad($rsua['Ciudad']);
                                        $domicilioT->setClaveZona("NULL");
                                        $domicilioT->setCodigoPostal($rsua['CodigoPostal']);
                                        $domicilioT->setColonia($rsua['Colonia']);
                                        $domicilioT->setDelegacion($rsua['Delegacion']);
                                        $query = $catalogo->obtenerLista("SELECT Ciudad FROM c_ciudades WHERE IdCiudad='" . $rsua['Estado'] . "'");
                                        $rse = mysql_fetch_array($query);
                                        $domicilioT->setEstado($rse['Ciudad']);
                                        $domicilioT->setLatitud($rsua['Latitud']);
                                        $domicilioT->setLongitud($rsua['Longitud']);
                                        $domicilioT->setNoExterior($rsua['NoExterior']);
                                        $domicilioT->setNoInterior($rsua['NoInterior']);
                                        $domicilioT->setPais("NULL");
                                        $domicilioT->setUsuarioCreacion($obj->getUsuarioCreacion());
                                        $domicilioT->setUsuarioUltimaModificacion($obj->getUsuarioModificacion());
                                        $domicilioT->setPantalla($obj->getPantalla());
                                        if(!$domicilioT ->newRegistro()){
                                            echo "Error: El domicilio del ticket no se pudo registrar";
                                        }  
                                   }
                                    $listos++;
                                } else {
                                    echo "<br/>El Ticket del usuario '" . $Nombre_Usuario . "' NO se registro correctamente en los datos de Plantilla";
                                }
                            } else {
                                echo "<br/>El Ticket del usuario " . $Nombre_Usuario . " NO se registro correctamente";
                            }
                        } else {
                            if (($ticket_exis != 0 || $ticket_exis != "") && $asis == 1) {//Si tiene ticket se cabia estatus de ticket 
                                $catalogo = new Catalogo();

                                $query = $catalogo->obtenerLista("UPDATE c_ticket SET EstadoDeTicket=3, AreaAtencion = '" . $areaAtencion . "',
                                              UsuarioUltimaModificacion = '" . $obj->getUsuarioModificacion() . "',FechaUltimaModificacion = now(),
                                              Pantalla = '" . $obj->getPantalla() . "' WHERE IdTicket = '" . $ticket_exis . "';");
                                if ($query == 1) {
                                    $NombreT[$ti]=$Nombre_Usuario;
                                    $ticketT[$ti]=$ticket_exis;
                                    $ti++;
                                    $domticket = 0;
                                    $query1 = $catalogo->obtenerLista("SELECT * FROM c_domicilioticket WHERE IdTicket='" . $ticket_exis . "'");
                                    if (mysql_num_rows($query1) > 0) {
                                        $rsdt = mysql_fetch_array($query1);
                                        $domticket = 1;
                                        $idDomTicket = $rsdt['IdDomicilioTicket'];
                                    }
                                    if($obj->getTipoEvento() == 1){
                                        $catalogo = new Catalogo();
                                        $query = $catalogo->obtenerLista("SELECT * FROM c_domicilio_usturno WHERE IdUsuario='" . $idUsuario[$i] . "'");
                                        $rsua = mysql_fetch_array($query);
                                        $domicilioT = new DomicilioTicket();
                                        $domicilioT->setIdTicket($ticket_exis);
                                        $domicilioT->setCalle($rsua['Calle']);
                                        $domicilioT->setActivo(1);
                                        $domicilioT->setCiudad($rsua['Ciudad']);
                                        $domicilioT->setClaveZona("NULL");
                                        $domicilioT->setCodigoPostal($rsua['CodigoPostal']);
                                        $domicilioT->setColonia($rsua['Colonia']);
                                        $domicilioT->setDelegacion($rsua['Delegacion']);
                                        $query = $catalogo->obtenerLista("SELECT Ciudad FROM c_ciudades WHERE IdCiudad='" . $rsua['Estado'] . "'");
                                        $rse = mysql_fetch_array($query);
                                        $domicilioT->setEstado($rse['Ciudad']);
                                        $domicilioT->setLatitud($rsua['Latitud']);
                                        $domicilioT->setLongitud($rsua['Longitud']);
                                        $domicilioT->setNoExterior($rsua['NoExterior']);
                                        $domicilioT->setNoInterior($rsua['NoInterior']);
                                        $domicilioT->setPais("NULL");
                                        $domicilioT->setUsuarioCreacion($obj->getUsuarioCreacion());
                                        $domicilioT->setUsuarioUltimaModificacion($obj->getUsuarioModificacion());
                                        $domicilioT->setPantalla($obj->getPantalla());
                                        if($domticket==1){
                                            if(!$domicilioT->updateDomicilioTicket()){
                                                echo "Error: El domicilio del ticket no se pudo modificar";
                                            }
                                        }else{
                                            if(!$domicilioT ->newRegistro()){
                                                echo "Error: El domicilio del ticket no se pudo registrar";
                                            }  
                                        }
                                    }else{
                                        if($obj->getTipoEvento() == 2){
                                            if($domticket == 1){
                                                 $catalogo = new Catalogo();
                                                 $query = $catalogo->obtenerLista("DELETE FROM c_domicilioticket WHERE IdDomicilioTicket = '" . $idDomTicket . "';");        
                                                 if ($query != 1) {
                                                    echo "<br/>No se puedo eliminar domicilio del Ticket ".$ticket_exis." "; 
                                                  }
                                            }
                                        }
                                    }
                                    //echo "<br/>El estado del Ticket del usuario '" . $Nombre_Usuario . "' está Aprobado con número de Ticket ".$ticket_exis."";
                                    $listos++;
                                } else {
                                    echo "<br/>El estado del Ticket del usuario '" . $Nombre_Usuario . "' NO se Aprobó";
                                }
                            }
                        }
                    }
                    if(!empty($NombreT) && !empty($ticketT)){
                        echo "Se asignaron los Usuarios a Tickets correctamente";
                        for($i=0;$i<$ti;$i++){
                                echo " (".$NombreT[$i]." -> ".$ticketT[$i]."),";
                        }
                    }
                     if(!empty($Nombre_UsuarioM)){
                        echo "<br/> Los Ticket de los Usuarios <b>".implode(", ", $Nombre_UsuarioM)."</b> se asociaron correctamente con su Plantilla";
                    }
                    if($listos==$asisten){
                        $catalogo = new Catalogo();
                        $query3 = $catalogo->obtenerLista("UPDATE c_plantilla SET Fecha= '" . $obj->getFecha() . "',Hora='" . $obj->getHora() . "' , TipoEvento='" . $obj->getTipoEvento() . "', Estatus = '2',UsuarioUltimaModificacion = '" . $obj->getUsuarioModificacion() . "',FechaUltimaModificacion = now(),Pantalla = '" . $obj->getPantalla() . "',IdUsuarioAutorizacion = '" . $idUsuario_autoriza . "' WHERE idPlantilla='" . $idPlantilla . "';");
                                    if ($query3 == 1) {
                                        echo "<br/>El Estatus de la Plantilla se modificó a Autorizado con éxito";
                                        } else {
                                        echo "<br/>El Estatus de la Plantilla NO se modifico a Autorizado con éxito";
                                    }
                    }
                
            } else {
                if ($obj->getEstatus() == 2) {
                    $catalogo = new Catalogo();

                    $query3 = $catalogo->obtenerLista("UPDATE c_plantilla SET Estatus =1,UsuarioUltimaModificacion = '" . $obj->getUsuarioModificacion() . "',FechaUltimaModificacion = now(),Pantalla = '" . $obj->getPantalla() . "' WHERE idPlantilla='" . $idPlantilla . "';");
                    if ($query3 == 1) {
                        echo "Se desautorizo con éxito la plantilla '" . $idPlantilla . "' del <b>" . $obj->getFecha() . "</b>";

                        $total = count($idUsuario);
                        for ($i = 0; $i < $total; $i++) {
                            $query = $catalogo->obtenerLista("SELECT Loggin, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS Nombre FROM c_usuario AS cu WHERE cu.IdUsuario='" . $idUsuario[$i] . "'");
                            $rs = mysql_fetch_array($query);
                            $Loggin = $rs['Loggin'];
                            $Nombre_Usuario = $rs['Nombre'];

                            $query = $catalogo->obtenerLista("SELECT kpa.IdTicket, kpa.Asistencia FROM k_plantilla_asistencia AS kpa INNER JOIN k_plantilla AS kp ON 
                                            kpa.idK_Plantilla=kp.idK_Plantilla JOIN c_plantilla AS cp ON cp.idPlantilla=kp.idPlantilla 
                                            WHERE kp.idUsuario='" . $idUsuario[$i] . "' AND cp.idPlantilla='" . $idPlantilla . "';");
                            $rs = mysql_fetch_array($query);
                            $Ticket = $rs['IdTicket'];
                            $Asistencias = $rs['Asistencia'];
                            if ($Asistencias == 1) {
                                $catalogo = new Catalogo();

                                $query = $catalogo->obtenerLista("UPDATE c_ticket SET EstadoDeTicket=4, UsuarioUltimaModificacion = '" . $obj->getUsuarioModificacion() . "',FechaUltimaModificacion = now(),Pantalla = '" . $obj->getPantalla() . "'WHERE IdTicket = '" . $Ticket . "';");
                                if ($query == 1) {
                                      array_push($Nombre_UsuarioM, $Nombre_Usuario);//echo "<br/>Eel Ticket del usuario '" . $Nombre_Usuario . "' se desaprobó";
                                } else {
                                    echo "<br/>El Ticket del Usuario '" . $Nombre_Usuario . "' NO se desautorizó";
                                }
                            }
                        }
                        if(!empty($Nombre_UsuarioM)){
                            echo "<br/> Los Tickets de los Usuarios <b>".implode(", ", $Nombre_UsuarioM)."</b> se desautorizaron";
                        }
                    } else {
                        echo "<br/>NO se desautorizo la plantilla '" . $idPlantilla . "' del <b>" . $obj->getFecha() . "</b>";
                    }
                }
            }
        }
    } else {



        if (isset($parametros['idPlantilla']) && $parametros['idPlantilla'] == "") {
            if ($obj->newRegistro()) {
                $error = 0;
                $idPlantilla = $obj->getIdPlantilla();
                $total = count($idUsuario);
                $catalogo = new Catalogo();
                //('". $this->descripcion . "',NULL,'". $this->activo . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla ."','".$this->localidad."');";
                for ($i = 0; $i < $total; $i++) {
                    $query = $catalogo->obtenerLista("SELECT Loggin, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS Nombre FROM c_usuario AS cu WHERE cu.IdUsuario='" . $idUsuario[$i] . "'");
                    $rs = mysql_fetch_array($query);
                    $Nombre_Usuario = $rs['Nombre']; //Nombre para mensajes echo

                    $consulta = "INSERT INTO k_plantilla(idPlantilla,idUsuario,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                    VALUES ";
                    $consulta .= "('" . $idPlantilla . "','" . $idUsuario[$i] . "','" . $obj->getActivo() . "','" . $obj->getUsuarioCreacion() . "',now(),'" . $obj->getUsuarioModificacion() . "',now(),'" . $obj->getPantalla() . "')";

                    $idK_Plantilla = $catalogo->insertarRegistro($consulta);
                    if ($idK_Plantilla != NULL && $idK_Plantilla != 0) {
                        //echo "<br/>El Usuario '" . $Nombre_Usuario . "' se registro correctamente";

                        $consulta = "INSERT INTO k_plantilla_asistencia(idK_Plantilla,Asistencia,Prioridad,Comentario,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                VALUES ";
                        $consulta .= "('" . $idK_Plantilla . "','" . $asistencia[$i] . "',0,'" . $comentario[$i] . "','" . $obj->getActivo() . "','" . $obj->getUsuarioCreacion() . "',now(),'" . $obj->getUsuarioModificacion() . "',now(),'" . $obj->getPantalla() . "')";

                        $idK_Plantilla_asistencia = $catalogo->insertarRegistro($consulta);
                        if ($idK_Plantilla_asistencia != NULL && $idK_Plantilla_asistencia != 0) {
                            echo "";
                            //echo "<br/>La asistencia, comentario '" . $i . "' del usuario '" . $Nombre_Usuario . "' se registraron correctamente";
                        } else {
                            $error++;
                            //echo "<br/>La asistencia, comentario '" . $i . "' del usuario '" . $Nombre_Usuario . "' NO se registraron correctamente";
                        }
                    } else {
                        $error++;
                        //echo "br/>El Usuario '" . $Nombre_Usuario . "' NO se registró correctamente";
                    }
                    //$consulta .= ($i < $total - 1) ? "," : "";
                }
                if ($error > 0) {
                    echo "<br/>Ocurrio un problema al indentificar Usuarios";
                }else{
                    echo "<br/>La Plantilla (".$idPlantilla.") se registró correctamente con " . $total . " Usuarios";
                }
//$query .= "INSERT INTO prueba_array (columna1, columna2, columna3) VALUES ";
            } else {
                echo "<br/>La plantilla del <b>" . $obj->getFecha() . "</b> NO se registró correctamente";
            }
        } else {
            $obj->setIdPlantilla($parametros['idPlantilla']);
            if ($obj->editRegistro()) {
                echo "La plantilla del <b>" . $obj->getFecha() . "</b> se modificó correctamente";
                $idPlantilla = $obj->getIdPlantilla();
                $total = count($idUsuario);
                $catalogo = new Catalogo();
                //('". $this->descripcion . "',NULL,'". $this->activo . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla ."','".$this->localidad."');";
                for ($i = 0; $i < $total; $i++) {
                    $query4 = $catalogo->obtenerLista("SELECT Loggin, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS Nombre FROM c_usuario AS cu WHERE cu.IdUsuario='" . $idUsuario[$i] . "'");
                    $rs4 = mysql_fetch_array($query4);
                    $Nombre_Usuario = $rs4['Nombre'];

                    $query = $catalogo->obtenerLista("SELECT kpa.idK_Plantilla_asistencia FROM k_plantilla_asistencia AS kpa INNER JOIN k_plantilla AS kp ON
                                              kp.idK_Plantilla=kpa.idK_Plantilla JOIN c_plantilla AS cp ON
                                              cp.idPlantilla=kp.idPlantilla WHERE cp.idPlantilla='" . $idPlantilla . "' AND kp.idUsuario='" . $idUsuario[$i] . "'");
                    $rs = mysql_fetch_array($query);
                    $idK_Plantilla_asistencia_edit = $rs['idK_Plantilla_asistencia'];


                    $query8 = $catalogo->obtenerLista("UPDATE k_plantilla_asistencia SET Asistencia ='" . $asistencia[$i] . "', Comentario = '" . $comentario[$i] . "', 
                                                Activo = " . $obj->getActivo() . ",UsuarioUltimaModificacion = '" . $obj->getUsuarioModificacion() . "',
                                                FechaUltimaModificacion = now(),Pantalla = '" . $obj->getPantalla() . "' WHERE idK_Plantilla_asistencia='" . $idK_Plantilla_asistencia_edit . "';");

                    if ($query8 == 1) {
                        array_push($Nombre_UsuarioM, $Nombre_Usuario); //echo "<br/>La asistencia y comentario del usuario " . $Nombre_Usuario . " se modificaron correctamente";
                    } else {
                        echo "<br/>La asistencia y comentario del usuario " . $Nombre_Usuario . " NO se modificaron correctamente";
                    }
                }
                if(!empty($Nombre_UsuarioM)){
                            echo "<br/> Las asistencias y comentarios de los Usuarios <b>".implode(", ", $Nombre_UsuarioM)."</b> se modificaron correctamente";
                        }
            } else {
                echo "La plantilla del <b>" . $obj->getFecha() . "</b> NO se modificó correctamente";
            }
        }
    }
    }else{
        $query = $catalogo->obtenerLista("SELECT ca.Descripcion AS DesCam, ct.descripcion AS DesTur FROM c_area ca, c_turno ct WHERE IdArea= ".$obj->getIdCampania()." AND idTurno=".$obj->getIdTurno().";");
                    $rs = mysql_fetch_array($query);
        echo "La Plantilla NO se generó. La Campaña: <b>". $rs['DesCam'] ."</b> no contiene usuarios con Turno: <b>". $rs['DesTur'];
    }
}
?>

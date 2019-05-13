<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/AgregarNota.class.php");
include_once("../Classes/Area.class.php");
include_once("../Classes/Catalogo.class.php");
include_once("../Classes/CambiarEstatusNota.class.php");
include_once("../Classes/AlmacenConmponente.class.php");
include_once("../Classes/LecturaTicket.class.php");
include_once("../Classes/ParametroGlobal.class.php");
include_once("../Classes/ViaticoTicket.class.php");
include_once("../Classes/ServiciosVE.class.php");
include_once("../Classes/TipoViatico.class.php");

$lectura = new LecturaTicket();
$obj = new AgregarNota();
$obj1 = new CambiarEstatusNota();
$parametroGlobal = new ParametroGlobal();
$catalogo = new Catalogo();
$tipoViatico = new TipoViatico();

$NoParteEquipo = "";
$realizarOperacion = true;
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$fechaHora = $fecha . " " . $hora;
$obj->setIdTicket($_POST['idTicket']);
$idTicket = $_POST['idTicket'];

$obj->setFechaHora($fechaHora);
$diagnostico = addslashes(str_replace("\r\n", " ", $_POST['diagnostico']));
$obj->setDiagnosticoSolucion($diagnostico);
$obj->setIdestatusAtencion($_POST['estatus']);

if(isset($_POST['correoBebe'])){
    $correos2 = array();
    $mail2 = new Mail();
    $mail2->setSubject("Rendimiento de refacciones del ticket " . $_POST['idTicket']);
    
    $mail2->setBody("<br/>Se solicito una nueva refacción con número de parte: " . $_GET['data1'] . " en el ticket <b>" . $_POST['idTicket'] . "</b> 
            el cuál tiene asociado el equipo con número de serie: ". $_POST['txt_serie'] ." pero el rendimiento está debajo del parámetro establecido 
            que es del ". $_GET['data3'] ."%, el total de impresiones fue de ". $_POST['totalPaginas12']. " páginas 
            siendo el rendimiento de ". $_POST['rendimientoTotal12']. " páginas para esta refacción");
    if ($parametroGlobal->getRegistroById("8")) {
        $mail2->setFrom($parametroGlobal->getValor());
    } else {
        $mail2->setFrom("scg-salida@scgenesis.mx");
    }
    /* Obtenemos los correos a los que le enviaremos la informacion */
    $queryCorreos = "SELECT cs.correo from c_correossolicitud cs WHERE cs.TipoSolicitud = 20 and cs.Activo = 1";
    $resultCorreos = $catalogo->obtenerLista($queryCorreos);
    while ($rsCorreo = mysql_fetch_array($resultCorreos)) {
        if (isset($rsCorreo['correo']) && $rsCorreo['correo'] != "" && filter_var($rsCorreo['correo'], FILTER_VALIDATE_EMAIL)) {
            array_push($correos2, $rsCorreo['correo']);
        }
    }
    foreach ($correos2 as $value) {/* Lo mandamos a los correos de los usuarios de cuentas por cobrar */
        $mail2->setTo($value);
        if ($mail2->enviarMail() == "1") {
            echo "<br/>Un correo fue enviado a $value. por el rendimiento de la refacción ".$_GET['data1']."<br/>";
        } else {
            //echo "<br/>Error: No se pudo enviar el correo a $value. <br/>";
        }
    }
}

if ($_POST['estatus'] == '9') {
    $numeroRefaccion = 1;
    $existeRefaccion = false;
    $contador = 1;
    
    $almacenComponente = new AlmacenComponente();
    $almacenComponente->serchNoSerie("SELECT * FROM c_componente c WHERE c.IdTipoComponente='1'");

    $lis = $almacenComponente->getArreglo_php2();

    while ($contador <= (int) $_GET['totalrefacciones']) {
        if (isset($_POST['refaccion' . $numeroRefaccion])) {
            list($modelo, $noParte) = explode(" / ", $_POST['refaccion' . $numeroRefaccion]);
            if(empty($noParte)){
                echo "Error: No se puede encontrar el número de parte de la refacción porque no usa el formato adecuado ";
                $realizarOperacion = false;
                break;
            }
        }
        
        for ($x = 0; $x < count($lis); $x++) {
            if(strcmp($lis[$x], $noParte) == 0){
                $existeRefaccion = true;
            }
        }
        $contador++;
    }
    
    if($realizarOperacion && !$existeRefaccion){
        echo "Error: El número de parte de la refacción no es válido, favor de verificarlo";
        $realizarOperacion = false;
    }
}

if($realizarOperacion){

    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") {
        echo "<br/><b>Se subió el archivo seleccionado</b><br/>";
        $rutaArchivo = "../../nota/uploads/" . $obj->getIdTicket() . $_FILES['file']['name'];
        move_uploaded_file($_FILES['file']['tmp_name'], $rutaArchivo);

        $rutaArchivo = "../nota/uploads/" . $obj->getIdTicket() . $_FILES['file']['name'];
        $obj->setPathImagen($rutaArchivo);
    }

    $resultPrioridad = $catalogo->obtenerLista("SELECT Prioridad from c_ticket WHERE IdTicket = $idTicket");
    if ($rsPrioridad = mysql_fetch_array($resultPrioridad)) {
        $prioridad = $rsPrioridad['Prioridad'];
    }

    //Buscamos si hay arrendamientos con tiempo de espera 0 para enviar correo
    $consulta = "SELECT cee.*, e.Nombre from c_escalamientoEstado cee LEFT JOIN c_estado AS e ON e.IdEstado = cee.idEstado"
            . " WHERE cee.idEstado = " . $_POST['estatus'];
    $result = $catalogo->obtenerLista($consulta);
    while ($row = mysql_fetch_array($result)) {
        if ($row['tiempoEnvio'] == 0) {
            if ($row['prioridad'] < $prioridad) {
                $updatePrioridad = "UPDATE c_ticket t SET t.Prioridad = (SELECT pt.IdPrioridad from c_prioridadticket pt WHERE pt.Prioridad = " . $row['prioridad'] . ")
                        WHERE t.IdTicket = " . $idTicket;
                $rsUpdate = $catalogo->obtenerLista($updatePrioridad);
                if ($rsUpdate) {
                    echo "Se ha modificado la prioridad del ticket " . $rsNota['IdTicket'] . " debido a que el escalamiento tenía una prioridad mayor";
                }
            }
            $correos = array();
            $mail = new Mail();
            $NombreCliente = "";
            $mail->setSubject("Atención al ticket " . $_POST['idTicket']);
            $consultaNombreCliente = "SELECT NombreCliente from c_ticket WHERE IdTicket =" . $_POST['idTicket'];
            $resultNombre = $catalogo->obtenerLista($consultaNombreCliente);
            if ($rsNombre = mysql_fetch_array($resultNombre)) {
                $NombreCliente = $rsNombre['NombreCliente'];
            }
            $mail->setBody("<br/>Es importante que se atienda el ticket <b>" . $_POST['idTicket'] . "</b> del cliente " . $NombreCliente . " se
                encuentra en el estado " . $row['Nombre'] . "<br/>" . "Mensaje: " . $row['mensaje']);
            if ($parametroGlobal->getRegistroById("8")) {
                $mail->setFrom($parametroGlobal->getValor());
            } else {
                $mail->setFrom("scg-salida@scgenesis.mx");
            }
            /* Obtenemos los correos a los que le enviaremos la informacion */
            $queryCorreos = "SELECT correo from c_escalamientoCorreo ec WHERE idEscalamiento = " . $row['idEscalamiento'];
            $resultCorreos = $catalogo->obtenerLista($queryCorreos);
            while ($rsCorreo = mysql_fetch_array($resultCorreos)) {
                $tipo = substr($rsCorreo['correo'], 0, 2);
                $queryFinal = "";
                if (strcmp($tipo, "cl") == 0) {
                    $queryFinal = "SELECT CorreoElectronicoEnvioFact1, CorreoElectronicoEnvioFact2, CorreoElectronicoEnvioFact3,CorreoElectronicoEnvioFact4
                            from c_cliente WHERE ClaveCliente = " . $rsNotas['ClaveCliente'];
                } else if (strcmp($tipo, "co") == 0) {
                    $queryFinal = "SELECT CorreoElectronico from c_contacto WHERE IdTipoContacto = " . substr($rsCorreo['correo'], 2);
                } else if (strcmp($tipo, "us") == 0) {
                    $queryFinal = "SELECT correo from c_usuario WHERE idUsuario = " . substr($rsCorreo['correo'], 2);
                } else if (strcmp($tipo, "tf") == 0) {
                    $queryFinal = "SELECT u.correo from k_tfscliente ktc 
                        LEFT JOIN c_usuario u ON ktc.IdUsuario = u.IdUsuario 
                        LEFT JOIN c_ticket t ON t.ClaveCliente = ktc.ClaveCliente
                        WHERE t.IdTicket = " . $_POST['idTicket'];
                }
                $resultFinal = $catalogo->obtenerLista($queryFinal);
                while ($rsFinal = mysql_fetch_array($resultFinal)) {
                    if (isset($rsFinal['CorreoElectronicoEnvioFact1']) && $rsFinal['CorreoElectronicoEnvioFact1'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact1'], FILTER_VALIDATE_EMAIL)) {
                        array_push($correos, $rsFinal['CorreoElectronicoEnvioFact1']);
                    }
                    if (isset($rsFinal['CorreoElectronicoEnvioFact2']) && $rsFinal['CorreoElectronicoEnvioFact2'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact2'], FILTER_VALIDATE_EMAIL)) {
                        array_push($correos, $rsFinal['CorreoElectronicoEnvioFact2']);
                    }
                    if (isset($rsFinal['CorreoElectronicoEnvioFact3']) && $rsFinal['CorreoElectronicoEnvioFact3'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact3'], FILTER_VALIDATE_EMAIL)) {
                        array_push($correos, $rsFinal['CorreoElectronicoEnvioFact3']);
                    }
                    if (isset($rsFinal['CorreoElectronicoEnvioFact4']) && $rsFinal['CorreoElectronicoEnvioFact4'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact4'], FILTER_VALIDATE_EMAIL)) {
                        array_push($correos, $rsFinal['CorreoElectronicoEnvioFact4']);
                    }
                    if (isset($rsFinal['CorreoElectronico']) && $rsFinal['CorreoElectronico'] != "" && filter_var($rsFinal['CorreoElectronico'], FILTER_VALIDATE_EMAIL)) {
                        array_push($correos, $rsFinal['CorreoElectronico']);
                    }
                    if (isset($rsFinal['correo']) && $rsFinal['correo'] != "" && filter_var($rsFinal['correo'], FILTER_VALIDATE_EMAIL)) {
                        array_push($correos, $rsFinal['correo']);
                    }
                }
            }
            foreach ($correos as $value) {/* Lo mandamos a los correos de los usuarios de cuentas por cobrar */
                $mail->setTo($value);
                if ($mail->enviarMail() == "1") {
                    echo "<br/>Un correo fue enviado por escalamientos a $value. <br/>";
                } else {
                    echo "<br/>Error: No se pudo enviar el correo a $value. <br/>";
                }
            }
        }
    }
    if (isset($_POST['usuario']) && $_POST['usuario'] != "") {
        $obj->setUsuarioSolicitud($_POST['usuario']);
    } else {
        $obj->setUsuarioSolicitud($_SESSION['user']);
    }

    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Alta Nota ');

    if (isset($_POST['activo']) && $_POST['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }

    if (isset($_POST['show']) && $_POST['show'] == "on") {
        $obj->setShow(1);
    } else {
        $obj->setShow(0);
    }

    $idnota = "";
    if (isset($_POST['nota']) && $_POST['nota'] == "") {

        if ($_POST['estatus'] == "67") {
            $obj->setIdestatusAtencion(64);
        }

        if ((isset($_POST['validada']) && $_POST['validada'] == "1") || $obj->newRegistro()) {
            $idnota = $obj->getIdNotaTicket();
            if ($_POST['estatus'] == "67") {
                $cont = 1;
                $cont1 = 0;
                while ($cont1 < (int) $_GET['totalrefacciones']) {
                    if ($_POST['suministro' . $cont]) {
                        $obj->setRefaccion($_POST['suministro' . $cont]);
                        $obj->setCantidad($_POST['cantidadsuministro' . $cont]);
                        if ($obj->comprobarExistenciaRefaccion($obj->getIdNotaTicket(), $_POST['suministro' . $cont])) {
                            if ($obj->newRefaccion($obj->getIdNotaTicket())) {
                                if ($obj->ObtenerModelo($_POST['suministro' . $cont])) {
                                    echo "EL toner  <b>" . $obj->getModelo() . "</b> se agregó correctamente <br/>";
                                } else
                                    echo "sin modelo";
                            }else {
                                if ($obj->ObtenerModelo($_POST['suministro' . $cont])) {
                                    echo "EL toner <b>" . $obj->getModelo() . "</b> no se agregó, existen error en los datos del componente <br/>";
                                }
                            }
                        } else {
                            if ($obj->ObtenerModelo($_POST['suministro' . $cont])) {
                                echo "EL toner <b>" . $obj->getModelo() . "</b> ya se encuentra registrada en esta nota<br/>";
                            }
                        }
                        $cont1++;
                    }
                    $cont++;
                }
            } else if ($_POST['estatus'] == "12") {//reasignacion de area
                $area = new Area();
                if ($area->getRegistroByEstado($_POST['reasignar'])) {
                    $obj->setIdArea($area->getIdArea());
                    if ($obj->newArea($obj->getIdNotaTicket())) {
                        if ($obj->editTicket($_POST['idTicket'], $_POST['reasignar'])) {
                            echo "La reasignación de área creó exitosamente<br/>";
                        } else {
                            echo "La reasignación de área no se creó exitosamente<br/>";
                        }
                    } else {
                        echo "";
                    }
                } else {
                    if ($obj->editTicket($_POST['idTicket'], $_POST['reasignar'])) {
                        echo "La reasignación de área creó exitosamente<br/>";
                    } else {
                        echo "La reasignación de área no se creó exitosamente<br/>";
                    }
                }
            } else if ($_POST['estatus'] == '16') {
                if ($obj->EditEstadoTicket($_POST['idTicket'], "2"))
                    echo "El ticket se cerró correctamente<br/>";
                else
                    echo "El ticket no se cerró exitosamente<br/>";
            }else if ($_POST['estatus'] == '59') {
                //Al cancelar, se eliminan las lecturas
                $result = $catalogo->obtenerLista("DELETE FROM `c_lecturasticket` WHERE fk_idticket = ".$_POST['idTicket'].";");                
                if($result == "0"){
                    echo "<br/>Atención, no se pudo borrar la lectura del ticket, favor de reportarlo al administrador.<br/>";
                }
                
                $result = $catalogo->obtenerLista("DELETE FROM `k_nota_refaccion` WHERE IdNotaTicket IN(SELECT IdNotaTicket FROM c_notaticket WHERE IdTicket = ".$_POST['idTicket'].");");                
                
                if ($obj->EditEstadoTicket($_POST['idTicket'], "4")){
                    echo "El ticket se canceló correctamente<br/>";
                }else{
                    echo "El ticket no se canceló exitosamente<br/>";
                }
            }else if ($_POST['estatus'] == '50') {//asignar proveedor
                $obj->setClaveProveedor($_POST['proveedor']);
                if ($obj->newProveedor($obj->getIdNotaTicket()))
                    echo "El proveedor fue asignado exitosamente<br/> ";
                else
                    echo " El proveedor no se asignó exitosamente <br/>";
            }else if ($_POST['estatus'] == '9') {//agregar refaccciones, para solicitud o proximo servicio
                $numeroRefaccion = 1;
                $contador = 1;

                if ($_POST['accion'] == "validar") {
                    if (isset($_POST['validada']) && $_POST['validada'] == "1") {//Si ya está validada, encontramos la nota validada
                        $idnota = $_POST['nota_validada'];
                        $obj->setIdNotaTicket($idnota);
                        $obj->deleteRegistro();
                    }
                    $obj->setEstatusRefaccion(24);
                    $obj->setIdestatusAtencion(24);
                    $obj->editRegistro();
                    if ($_POST['idNotaAnterior']) {
                        $obj->setIdNotaTicket($_POST['idNotaAnterior']);
                        $obj->deleteRegistro();
                        $obj->setIdNotaTicket($idnota);
                    }
                    $obj->setValidada(1);
                } else {
                    $obj->setEstatusRefaccion($_POST['estatus']);
                }

                while ($contador <= (int) $_GET['totalrefacciones']) {
                    if (isset($_POST['refaccion' . $numeroRefaccion])) {
                        list($modelo, $noParte) = explode(" / ", $_POST['refaccion' . $numeroRefaccion]);
                        $obj->setRefaccion($noParte);
                        $obj->setCantidad($_POST['cantidad' . $numeroRefaccion]);
                        $estatus_verificar = 9;

                        if ($obj->comprobarExistenciaRefaccionEnTicket($obj->getIdTicket(), $noParte, $estatus_verificar)) {
                            if ($_POST['idNotaAnterior'] != "" && $obj->newRefaccion($_POST['idNotaAnterior'])) {//Nota de solicitud
                            } else {

                            }
                            if (isset($_POST['poner_cero' . $numeroRefaccion]) && $_POST['poner_cero' . $numeroRefaccion] == "1") {//Si existe la marca de poner en cantidad de validad cero
                                $obj->setCantidad(0);
                            }
                            if ($obj->newRefaccion($obj->getIdNotaTicket())) {//Nota validada
                            } else {

                            }
                        } else {
                            if ($obj->ObtenerModelo($_POST['refaccion' . $numeroRefaccion])) {
                                echo "<br/>La refacción <b>" . $obj->getModelo() . "</b> ya se encuentra registrada en este ticket, no se puede volver a solicitar.<br/>";
                            } else {
                                echo "<br/>La refacción <b>" . $_POST['refaccion' . $numeroRefaccion] . "</b> ya se encuentra registrada en este ticket, no se puede volver a solicitar.<br/>";
                            }
                        }
                        $contador++;
                    }
                    $numeroRefaccion++;
                }

                if (isset($_POST['txt_negro_nuevo']) && $_POST['txt_negro_nuevo'] != "") {//agregar lecturas 
                    $lectura->setContadorBN($_POST['txt_negro_nuevo']);
                    $lectura->setContadorColor($_POST['txt_color_nuevo']);
                    $lectura->setContadorBNA($_POST['txt_negro_anterior']);
                    $lectura->setContadorColorA($_POST['txt_color_anterior']);
                    $lectura->setFechaA($_POST['txt_fechaA']);

                    $lectura->setClaveEspEquipo($_POST['txt_serie']);
                    $lectura->setModeloEquipo($_POST['txt_modelo']);
                    $lectura->setIdTicket($_POST['txt_id_ticket']);

                    if (isset($_POST['activo']) && $_POST['activo'] == "on") {
                        $lectura->setActivo(1);
                    } else {
                        $lectura->setActivo(0);
                    }
                    $lectura->setComentario($_POST['txt_comentario']);
                    $lectura->setUsuarioCreacion($_SESSION['user']);
                    $lectura->setUsuarioUltimaModificacion($_SESSION['user']);
                    $lectura->setPantalla("Alta nota refaccion");
                    if ($lectura->NewRegistro()) {

                    }
                }

                /*             * ******   Agregar compatibles ********** */
                if ($_POST['accion'] == "validar") {
                    if ($obj->VerificarNoSerie($idTicket)) {
                        $NoParteEquipo = $obj->getNoParte();
                        $obj1->obtenerComponentesNotaRefaccion($idnota);
                        $arrayRefacciones = $obj1->getArrayNoComponente();
                        $contadorRef = 0;
                        while ($contadorRef < count($arrayRefacciones)) {
                            //comprobar existencia en componentes compatibles
                            if ($obj->ComprobarExistenciaCompatiblesEquipo($NoParteEquipo, $arrayRefacciones[$contadorRef])) {
                                $obj->setNoParte($NoParteEquipo);
                                $obj->setRefaccion($arrayRefacciones[$contadorRef]);
                                $obj->setUsuarioCreacion($_SESSION['user']);
                                $obj->setUsuarioModificacion($_SESSION['user']);
                                $obj->setPantalla("Validar refacción");
                                if ($obj->newEquipoComponenteCompatible()) {

                                }
                            }
                            $contadorRef++;
                        }
                    }
                }
            } else if ($tipoViatico->tieneEstadoViatico ($_POST['estatus']) || $_POST['estatusN'] == "275" || $_POST['estatusN'] == "276") {
                $viatico = new ViaticoTicket();                    
                $viatico->setIdTicket($idTicket);
                /*Obtenemos el tipo de viatico*/
                if (isset($_POST['tipo_viatico']) && $_POST['tipo_viatico'] != "") {                    
                    $viatico->setIdTipoViatico($_POST['tipo_viatico']);                    
                }else{
                    if($tipoViatico->getIdTipoViatico() != ""){
                        $viatico->setIdTipoViatico($tipoViatico->getIdTipoViatico());
                    }else{
                        $viatico->setIdTipoViatico(4);
                    }
                }
                
                $query1 = $catalogo->obtenerLista("SELECT IdUsuario FROM k_tecnicoticket WHERE IdTicket= " . $idTicket . " ;");
                if (mysql_num_rows($query1) > 0) {
                    $rs1 = mysql_fetch_array($query1);
                    $viatico->setIdUsuario($rs1['IdUsuario']);
                } else {
                    $viatico->setIdUsuario($_SESSION['idUsuario']);
                }

                $viatico->setFecha($fecha);
                if($_POST['estatus'] == '275'){//Kilometros por servicio
                    $viatico->setCosto($_POST['km']);                    
                    $update = "UPDATE c_notaticket SET Km =  ".$_POST['km']." WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                    $rsUpdate = $catalogo->obtenerLista($update);
                    if ($rsUpdate) {
                        echo "Se han almacenado los Km recorridos para el Ticket <b>" . $idTicket ."</b><br/>";
                    }
                }else if($_POST['estatus'] == "276"){//Tiempo de espera
                    $viatico->setCosto($_POST['tiempo_esperaR']);
                    $update = "UPDATE c_notaticket SET TiempoEsperaReal =  ".$_POST['tiempo_esperaR'].", TiempoEsperaMenor =  ".$_POST['tiempo_esperaM']." WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                    $rsUpdate = $catalogo->obtenerLista($update);
                    if ($rsUpdate) {
                        echo "Se ha almacenado tiempo de espera para el Ticket <b>" . $idTicket ."</b><br/>";
                    }
                }else{//Otros viaticos
                    if (isset($_POST['monto'])) {
                        $viatico->setCosto($_POST['monto']);
                    }else{
                        $viatico->setCosto(0);
                    }
                }
                $viatico->setComentario($diagnostico);
                if(isset($_POST['cobrar']) && $_POST['cobrar'] == "1"){
                    $viatico->setCobrarSiNo(1); 
                }else{
                    $viatico->setCobrarSiNo(0);
                }

                if(isset($_POST['pagar']) && $_POST['pagar'] == "1"){
                    $viatico->setPagarSiNo(1); 
                }else{
                    $viatico->setPagarSiNo(0); 
                }
                $viatico->setUsuarioCreacion($_SESSION['user']);
                $viatico->setUsuarioUltimaModificacion($_SESSION['user']);
                $viatico->setPantalla("Controler_AgregarNota"); 
                if($viatico->insertarNuevoViatico($obj->getIdNotaTicket())){
                    echo "Se registro viático del Ticket  <b>" . $obj->getIdTicket() . "</b><br/>";
                }else{
                    echo "<br/>No se pudo registrar el costo del viatico<br/>";
                }
            }/*else if ($_POST['estatus'] == '275') {
                $serviciosVE = new ServiciosVE();
                $serviciosVE->setIdTicket($idTicket);
                $serviciosVE->setCantidad($_POST['km']);
                $serviciosVE->setFecha($fecha);
                $serviciosVE->buscarServicioByEstatusNota(275);
                $serviciosVE->setUsuarioCreacion($_SESSION['user']);
                $serviciosVE->setUsuarioUltimaModificacion($_SESSION['user']);
                $serviciosVE->setPantalla("Controler_AgregarNota");
                $serviciosVE->newRegistroKServicio();
                
                $update = "UPDATE c_notaticket SET Km =  ".$_POST['km']." WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                $rsUpdate = $catalogo->obtenerLista($update);
                if ($rsUpdate) {
                    echo "Se han almacenado los Km recorridos para el Ticket <b>" . $idTicket ."</b><br/>";
                }
            }else if ($_POST['estatus'] == '276') {
                $serviciosVE = new ServiciosVE();
                $serviciosVE->setIdTicket($idTicket);
                $serviciosVE->setCantidad($_POST['tiempo_esperaR']);
                $serviciosVE->setFecha($fecha);
                $serviciosVE->buscarServicioByEstatusNota(276);
                $serviciosVE->setUsuarioCreacion($_SESSION['user']);
                $serviciosVE->setUsuarioUltimaModificacion($_SESSION['user']);
                $serviciosVE->setPantalla("Controler_AgregarNota");
                $serviciosVE->newRegistroKServicio();
                
                $update = "UPDATE c_notaticket SET TiempoEsperaReal =  ".$_POST['tiempo_esperaR'].", TiempoEsperaMenor =  ".$_POST['tiempo_esperaM']." WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                $rsUpdate = $catalogo->obtenerLista($update);
                if ($rsUpdate) {
                    echo "Se ha almacenado tiempo de espera para el Ticket <b>" . $idTicket ."</b><br/>";
                }
            }*/else if ($_POST['estatus'] == '277') {
                $update = "UPDATE c_notaticket SET NoBoleto =  ".$_POST['no_boleto']." WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                $rsUpdate = $catalogo->obtenerLista($update);
                if ($rsUpdate) {
                    echo "Se ha almacenado No. de Boleto para el Ticket <b>" . $idTicket ."</b><br/>";
                }
            }
            echo "La nota <b>" . $obj->getIdNotaTicket() . "</b> se registro correctamente";
        } else
            echo "Error: La nota <b>" . $obj->getIdNotaTicket() . "</b> no se agrego <br/>";
    }
}
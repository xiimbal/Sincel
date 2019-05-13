<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/ServiciosVE.class.php");
include_once("../../Classes/AgregarNota.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$nombre_objeto = $permisos_grid->getNombreTicketSistema();

if(isset($_POST['ValidacionGral']) && $_POST['ValidacionGral'] == "1"){
    $tickets = $_POST['tickets'];
    $sinError = true;
    for($x = 0; $x < count($tickets); $x++){
        $query = "SELECT ve.IdPartida,s.NombreServicio, nt.DiagnosticoSol, ve.cantidad, nt.PathImagen, ve.Validado, ve.CantidadOriginal, nt.IdNotaTicket FROM c_notaticket nt INNER JOIN
        c_estado e ON e.IdEstado = nt.IdEstatusAtencion INNER JOIN k_serviciove ve ON ve.IdNotaTicket = nt.IdNotaTicket INNER JOIN 
        c_serviciosve s ON ve.IdServicioVE = s.IdServicioVE WHERE e.FlagValidacion = 1 AND nt.IdTicket = " . $tickets[$x];
        //echo $query;
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($query);
        $servicio = new ServiciosVE();
        $nota = new AgregarNota();        
        $nota->setUsuarioModificacion($_SESSION['user']);
        $nota->setUsuarioCreacion($_SESSION['user']);
        while($rs = mysql_fetch_array($result)){
            $nota->getRegistroById($rs['IdNotaTicket']);
            $servicio->getRegistroById($rs['IdPartida']);     
            if(empty($servicio->getIdNotaTicketFacturar())){//Sólo si no ha sido validado hacemos el resto
                $nota->setIdestatusAtencion(55);//para facturar
                $nota->setPathImagen("");
                $nota->setDiagnosticoSolucion("Viático validado: (\"" . $nota->getDiagnosticoSolucion() . "\")");
                if($nota->newRegistro()){//Si hay éxito al registrar la nueva nota, entonces vinculamos el serviciove con la nueva nota    
                    $servicio->setValidado(1);
                    $servicio->setIdNotaTicketFacturar($nota->getIdNotaTicket());
                    if(!$servicio->guardarIdNotaFactura()){
                        $sinError = false;
                    }
                }else{
                    $sinError = false;
                }
            }
        }
    }
    if($sinError){
        echo "Éxito al validar viáticos de los " . $nombre_objeto . "s seleccionados.";
    }else{
        echo "Error: Los viáticos de algunos " . $nombre_objeto . "s no pudieron ser validados con éxito.";
    }
}else{
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $ticket = $parametros['ticket'];
    $sinError = true;
    for($x = 0; $x < $parametros['contador']; $x++){
        $servicio = new ServiciosVE();
        $nota = new AgregarNota();
        $nota->setUsuarioModificacion($_SESSION['user']);
        $nota->setUsuarioCreacion($_SESSION['user']);
        $nota->getRegistroById($parametros['nota' . $x]);
        if(isset($_FILES[$x])){//Si subieron un archivo, entonces lo guardamos en la nueva nota.
            $archivo = $_FILES[$x];
            $rutaArchivo = "../../../nota/uploads/$ticket-" . $parametros['nota' . $x] . $archivo['name'];
            move_uploaded_file($archivo['tmp_name'], $rutaArchivo);
            //buscamos la vieja nota, para modificar el viejo archivo
            $nota->setPathImagen($rutaArchivo);
            $nota->modificarPathImagen();
        }
        if(isset($parametros['validacion' . $x]) && $parametros['validacion' . $x] == "on"){
            $servicio->getRegistroById($parametros['viatico' . $x]);
            //Vamos a crear la nota para facturar.
            if(empty($servicio->getIdNotaTicketFacturar())){//Sólo si no ha sido validado hacemos el resto
                $nota->setIdestatusAtencion(55);//para facturar
                $nota->setPathImagen("");//No vamos a guardar la imágen en esta nueva nota del ticket
                $nota->setDiagnosticoSolucion("Viático validado: (\"" . $nota->getDiagnosticoSolucion() . "\")");
                if($nota->newRegistro()){//Si hay éxito al registrar la nueva nota, entonces vinculamos el serviciove con la nueva nota    
                    $servicio->setValidado(1);
                    $servicio->setIdNotaTicketFacturar($nota->getIdNotaTicket());
                    $servicio->setCantidad($parametros['cantidad' . $x]);
                    if(!$servicio->guardarIdNotaFactura()){
                        $sinError = false;
                    }
                }else{
                    echo "Error: Algunos viáticos no pudieron ser validados.";
                }
            }

        }
    }

    if($sinError){
        echo "Los viáticos fueron validados con éxito";
    }else{
        echo "Error: Los viáticos no pudieron ser validados";
    }
}
    
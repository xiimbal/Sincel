<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../Classes/Catalogo.class.php");
include_once("../Classes/NotaTicket.class.php");
include_once("../Classes/PermisosSubMenu.class.php");

$catalogo = new Catalogo();
$obj = new NotaTicket();
$permisos_grid2 = new PermisosSubMenu();
$nombre_nota = $permisos_grid2->getTitulo(7); //Nombre de las notas en la empresa actual

if(isset($_GET['id'])){
    if($_GET['id'] != ""){
        $id = $_GET['id'];
        if($obj->borrarRegistro($id)){
            echo "Éxito al eliminar $nombre_nota";
        }else{
            echo "Error: No se pudo eliminar $nombre_nota";
        }        
    }else{
        echo "No se pudo completa la acción. Reporte esto al administrador.";
    }
}else{
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setDiagnostico($parametros['nombre']);
    $obj->setIdTicket($parametros['relacionado']);
    /*if(isset($parametros['idTicket']) && $parametros['idTicket'] != ""){
        $obj->setIdTicket($parametros['idTicket']);
    }else{
        $obj->setIdTicket();
    }*/
    $obj->setPrioridad($parametros['prioridad']);
    $obj->setIdEstatus($parametros['tipo']);
    $obj->setCodigo($parametros['codigo']);
    $obj->setIdTecnicoAsignado($parametros['usuario']);
    //Vamos a hacer que si el Sistema detecta que el estado de la nota se marcó como cerrada (id: 2) se ponga automáticamente el progreso en 100%
    //Si no es así, vemos si el progreso fue marcado como 100%, de ser así pondremos la nota como cerrada.
    if((!empty($parametros['estado']) && $parametros['estado'] == "2") || (!empty($parametros['amount']) && $parametros['amount'] == "100")){
        $obj->setIdEstadoNota(2);
        $obj->setProgreso(100);
    }else{//Como no se cumplió, guardamos lo que ellos registraron.
        $obj->setIdEstadoNota($parametros['estado']);
        $obj->setProgreso($parametros['amount']);        
    }
    $obj->setHorasTrabajadas($parametros['horasT']);
    $obj->setFechaInicio($parametros['fechaI']);
    $obj->setFechaFin($parametros['fechaF']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setActivo(1);
    $obj->setDescripcion($parametros['descripcion']);
    $obj->setMostrarCliente(1);
    if(isset($parametros['id'])){
        if($parametros['id'] == ""){//Crear
            if($obj->newRegistro()){
                echo "$nombre_nota se ha creado con éxito.";
            }else{
                echo "Error: $nombre_nota no se creó.";
            }
        }else{
            $obj->setIdNota($parametros['id']);
            if($obj->editRegistro()){
                echo "$nombre_nota se ha modificado con éxito.";
            }else{
                echo "Error: $nombre_nota no se pudo modificar.";
            }
        }
    }else{
        echo "Error: No se pudo completar la acción. Notifique este problema con el administrador.";
    }
}
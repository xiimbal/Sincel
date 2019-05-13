<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/LecturaTicket.class.php");
include_once("../Classes/Incidencia.class.php");
$sinProblemas = true;

if(isset($_POST['idB'])){
    $lectura = new LecturaTicket();
    $incidencias = new Incidencia();
    
    if(isset($_POST['numRows']) && $_POST['numRows'] != 0){
        $numero = $_POST['numRows'];
        //Vamos a modificar lectura por lectura
        for($i = 1; $i <= $numero; $i++){
            $rutaArchivo = "";
            if(isset($_POST['lectura_'.$i])){
                //Obtenemos el ticket y los contadores
                $lectura->setIdLectura($_POST['lectura_'.$i]);
                $lectura->setContadorBN($_POST['contadorbn_'.$i]);
                if(isset($_POST['contadorcl_'.$i]))
                {$lectura->setContadorColor($_POST['contadorcl_'.$i]);}
                if($lectura->editContadores()){
                    $sinProblemas = true;
                }else{
                    $sinProblemas = false;
                    break;
                }

                if(isset($_FILES['hoja_'.$i]['name']) && $_FILES['hoja_'.$i]['name'] != ""){
                    echo "<br/><b>Se subió el archivo seleccionado</b><br/>";
                    $rutaArchivo = "../../archivosLecturas/".$lectura->getIdLectura().$_FILES['hoja_'.$i]['name']; 
                    move_uploaded_file($_FILES['hoja_'.$i]['tmp_name'],$rutaArchivo);

                    $rutaArchivo = "../archivosLecturas/".$lectura->getIdLectura().$_FILES['hoja_'.$i]['name'];   

                    $incidencias->setId_Ticket($_POST['ticket_'.$i]);
                    $incidencias->setNoSerie($_POST['NoSerie']);
                    $incidencias->setFecha(date("Y-m-d"));
                    $incidencias->setFechaFin(date("Y-m-d"));
                    $incidencias->setClaveCentroCosto($_POST['ccc_'.$i]);
                    $incidencias->setStatus(1);
                    $incidencias->setDescripcion($rutaArchivo);
                    $incidencias->setActivo(1);
                    $incidencias->setUsuarioCreacion($_SESSION['user']);
                    $incidencias->setUsuarioUltimaModificacion($_SESSION['user']); 
                    $incidencias->setPantalla("Bitacoras");
                    $incidencias->setIdTipoIncidencia(8);

                    if($incidencias->newRegistro()){
                        echo "Se creo la incidencia";
                    }else{
                        echo "Error: Error en la incidencia";
                    }
                }
            }
        }
    }
    
    if($sinProblemas){
        echo "Se han modificado los contadores";
    }else{
        echo "Error: Hubo un problema al modificar los contadores";
    }
}else{
    echo "Error: No se han recibido parámetros";
}

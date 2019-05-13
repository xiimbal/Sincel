<?php
session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../../index.php");
    }
    
    include_once("../Classes/GZona.class.php");
    $obj = new GZona(); 
    if(isset($_GET['id']) ){/*Para eliminar el registro con el id recibido por get*/
        $obj->setIdGZona($_GET['id']);
        if($obj->deleteRegistro()){
            echo "La geo zona se eliminó correctamente";
        }else{
            echo "La geo zona no se pudo eliminar, ya que contiene datos asociados.";
        }
    }
    else{
          if(isset($_POST['form'])){
            $parametros = "";
            parse_str($_POST['form'], $parametros);
        }
        $obj->setNombre($parametros['nombre']);
        $obj->setDescripcion($parametros['descripcion']);
        if(isset($parametros['activo']) && $parametros['activo']=="on"){
            $obj->setActivo(1);
        }else {
            $obj->setActivo(0);
        }
        $obj->setUsuarioCreacion($_SESSION['user']);
        $obj->setUsuarioModificacion($_SESSION['user']);
        $obj->setPantalla('c_gzona');
         if(isset($parametros['id']) && $parametros['id']==""){/*Si el id esta vacio, hay que insertar un NUEVO registro*/
            if($obj->newRegistro()){
                echo "La geo zona <b>".$obj->getNombre()."</b> se registró correctamente";
            }else{
                echo "Error: La geo zona <b>".$obj->getNombre()."</b> ya se encuentra registrado";
            }
        }else{/*Modificar*/
            $obj->setIdGZona($parametros['id']);
            if($obj->editRegistro()){
                echo "La geo zona <b>".$obj->getNombre()."</b> se modificó correctamente";
            }else{
                echo "Error: La geo zona <b>".$obj->getNombre()."</b> ya se encuentra registrado";
            }
        }
    }
?>

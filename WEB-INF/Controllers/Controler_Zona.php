<?php
session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../../index.php");
    }
    
    include_once("../Classes/Zona.class.php");
    $obj = new Zona();     
    if(isset($_GET['id']) ){/*Para eliminar el registro con el id recibido por get*/        
        $obj->setIdZona($_GET['id']);
        if($obj->deleteRegistro()){
            echo "La zona se eliminó correctamente";
        }else{
            echo "La zona no se pudo eliminar, ya que contiene datos asociados.";
        }
    }
    else{
          if(isset($_POST['form'])){
            $parametros = "";
            parse_str($_POST['form'], $parametros);
        }
        $obj->setNombre($parametros['nombre']);
        $obj->setDescripcion($parametros['descripcion']);
        $obj->setIdGZona($parametros['gZona']);
        $obj->setOrden($parametros['orden']);
        if(isset($parametros['activo']) && $parametros['activo']=="on"){
            $obj->setActivo(1);
        }else {
            $obj->setActivo(0);
        }
        $obj->setUsuarioCreacion($_SESSION['user']);
        $obj->setUsuarioModificacion($_SESSION['user']);
        $obj->setPantalla('c_zona');
         if(isset($parametros['id']) && $parametros['id']==""){/*Si el id esta vacio, hay que insertar un NUEVO registro*/
            if($obj->newRegistro()){
                echo "La zona <b>".$obj->getNombre()."</b> se registró correctamente";
            }else{
                echo "Error: La zona <b>".$obj->getNombre()."</b> ya se encuentra registrado";
            }
        }else{/*Modificar*/
            $obj->setIdZona($parametros['id']);
            if($obj->editRegistro()){
                echo "La zona <b>".$obj->getNombre()."</b> se modificó correctamente";
            }else{
                echo "Error: La zona <b>".$obj->getNombre()."</b> ya se encuentra registrado";
            }
        }
    }
?>

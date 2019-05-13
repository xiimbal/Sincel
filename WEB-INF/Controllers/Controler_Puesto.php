<?php
session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../../index.php");
    }
    
    include_once("../Classes/Puesto.class.php");
    $obj = new Puesto(); 
    if(isset($_GET['id']) ){/*Para eliminar el registro con el id recibido por get*/
        $obj->setIdPuesto($_GET['id']);
        if($obj->deleteRegistro()){
            echo "El puesto se eliminó correctamente";
        }else{
            echo "El puesto no se pudo eliminar, ya que contiene datos asociados.";
        }
    }
    else{
          if(isset($_POST['form'])){
            $parametros = "";
            parse_str($_POST['form'], $parametros);
        }
        $obj->setNombre($parametros['nombre']);
        $obj->setDescripcion($parametros['descripcion']);
        if(isset($parametros['reabrir']) && $parametros['reabrir']=="on"){
            $obj->setReAbrirTicket(1);
        }else {
            $obj->setReAbrirTicket(0);
        }
        if(isset($parametros['activo']) && $parametros['activo']=="on"){
            $obj->setActivo(1);
        }else {
            $obj->setActivo(0);
        }
        $obj->setUsuarioCreacion($_SESSION['user']);
        $obj->setUsuarioModificacion($_SESSION['user']);
        $obj->setPantalla('c_puesto');
        if(isset($parametros['id']) && $parametros['id']==""){/*Si el id esta vacio, hay que insertar un NUEVO registro*/
            if($obj->newRegistro()){
                echo "El puesto <b>".$obj->getNombre()."</b> se registró correctamente";                
                /*Registramos los permisos*/
                $obj->eliminarPermisosEspeciales();
                for($contador = 0;$contador<=$parametros['numero_permisos'];$contador++){
                    if(isset($parametros['especial_'.$contador])){
                        $obj->registrarPermisoEspecial($parametros['especial_'.$contador]);                        
                    }
                }
                $obj->registrarAreasPuesto($parametros['areas']);
            }else{
                echo "Error: El puesto <b> ".$obj->getNombre()."</b> ya se encuentra registrado";
            }
        }else{/*Modificar*/
            $obj->setIdPuesto($parametros['id']);
            if($obj->editRegistro()){
                echo "El puesto <b> ".$obj->getNombre()."</b> se modificó correctamente";                
                /*Registramos los permisos*/
                $obj->eliminarPermisosEspeciales();                
                for($contador = 0;$contador<=$parametros['numero_permisos'];$contador++){
                    if(isset($parametros['especial_'.$contador])){                    
                        $obj->registrarPermisoEspecial($parametros['especial_'.$contador]);                   
                    }
                }
                $obj->registrarAreasPuesto($parametros['areas']);
            }else{
                echo "Error: El puesto <b> ".$obj->getNombre()."</b> ya se encuentra registrado";
            }
        }
    }
?>

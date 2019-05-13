<?php
    header('Content-Type: text/html; charset=utf-8');
    if(!isset($_GET['clvUs']) || !isset($_GET['clvUs']) || !isset($_GET['clvUs'])){
        header("Location: index.php");
    }
    
    if(!isset($_GET['uguid'])){        
        $empresa = 1;//se toma por default la empresa 1, que es genesis.
    }else{
        $empresa = $_GET['uguid'];
    }
    
    $id = $_GET['clvUs'];

    include_once("WEB-INF/Classes/Usuario.class.php");
    include_once("WEB-INF/Classes/UsuarioPendiente.class.php");
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Valida usuarios</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        <?php
            $obj_pendiente = new UsuarioPendiente();
            $obj_pendiente->setEmpresa($empresa);            
            
            if($obj_pendiente->getUsuarioById($id)){
                if($obj_pendiente->getActivo() == "0"){
                    echo "Error: este enlace ya había sido procesado anteriormente";
                    return;
                }
                
                if((int)$obj_pendiente->getTiempoDiferencia() > 30){
                    echo "Error: este enlace han caducado, ya han pasado más de 30 minutos.";
                    return;
                }
                $obj = new Usuario();
                $obj_aux = new Usuario();
                $obj->setEmpresa($empresa);
                $obj_aux->setEmpresa($empresa);
                
                $obj->setPuesto($obj_pendiente->getPuesto());
                $obj->setNombre($obj_pendiente->getNombre());
                $obj->setPaterno($obj_pendiente->getPaterno());
                $obj->setMaterno($obj_pendiente->getMaterno());
                $obj->setUsuario($obj_pendiente->getUsuario());
                $obj->setPassword($obj_pendiente->getPassword());
                $obj->setActivo(1);
                $obj->setUsuarioCreacion($obj_pendiente->getUsuarioCreacion());
                $obj->setUsuarioModificacion($obj_pendiente->getUsuarioModificacion());
                $obj->setPantalla($obj_pendiente->getPantalla());
                $obj->setEmail($obj_pendiente->getEmail());
                $obj->setTelefono($obj_pendiente->getTelefono());
                $obj->setSexo($obj_pendiente->getSexo());
                $obj->setFechaNacimiento($obj_pendiente->getFechaNacimiento());
                if (!$obj->getUsuarioByUser($obj->getUsuario())) {//si no existe el nombre de usuario
                    if ($obj_aux->getRegistroByEmail($obj->getEmail())) { //Si el correo electronico ya está registrado.
                        if ($obj_aux->getPuesto() == "41") {//Si es usuario fb
                            echo "Error: El correo electrónico <b>".$obj->getEmail()."</b> ya está registrado en el sistema";
                        } else {//Si no es usuario fb
                            echo "Error: El correo electrónico <b>".$obj->getEmail()."</b> ya está registrado en el sistema";
                        }
                    }else{
                        if($obj->newRegistroSinEcriptar()){
                            $obj_pendiente->marcarProcesado($id, $obj->getId());
                            echo "El usuario <b>".$obj->getUsuario()."</b> ha sido dado de alta exitosamente en el sistema</b>";
                        }else{
                            echo "Error: no se pudo registrar el usuario, favor de reportar este problema";
                        }
                    }
                }else{
                    echo "Error: el usuario <b>".$obj->getUsuario()." ya está registrado en el sistema</b>";
                }
                                
            }else{
                echo "<br/>Error: no se encuentra ningún registro habilitado con los datos de este enlace";
            }
        ?>
    </body>
</html>
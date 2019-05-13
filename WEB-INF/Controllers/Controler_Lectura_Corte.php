<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['form'])) {
    header("Location: ../../../index.php");
}
include_once("../Classes/Lectura.class.php");

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$error = false;
$numero_equipos = $parametros['numero_equipos'];
$permisos = $_POST['permisos'];

for($contador=1;$contador<=$numero_equipos;$contador++){
    
    if($permisos[$contador-1] == "1"){/*Si el usuario eligio si insertar este registro*/
        $insertar = false;
        $lectura = new Lectura();
        $lectura->setNoSerie($parametros['serie_'.$contador]);    
        
        
        /* Contadores BN */
        if(isset($parametros['contador_bn_'.$contador]) && $parametros['contador_bn_'.$contador]!=""){
            if($parametros['fa_'.$contador] != "1"){
                $lectura->setContadorBNPaginas($parametros['contador_bn_'.$contador]);
                $lectura->setContadorBNML('null');
                $insertar = true;
            }else{
                $lectura->setContadorBNML($parametros['contador_bn_'.$contador]);
                $lectura->setContadorBNPaginas('null');
                $insertar = true;
            }
        }else{
            $lectura->setContadorBNPaginas('null');
            $lectura->setContadorBNML('null');
        }
        /*  Contador Color */
        if(isset($parametros['contador_color_'.$contador]) && $parametros['contador_color_'.$contador]!=""){
            if($parametros['fa_'.$contador] != "1"){
                $lectura->setContadorColorPaginas($parametros['contador_color_'.$contador]);
                $lectura->setContadorColorML('null');
                $insertar = true;
            }else{
                $lectura->setContadorColorML($parametros['contador_color_'.$contador]);
                $lectura->setContadorColorPaginas('null');
                $insertar = true;
            }
        }else{
            $lectura->setContadorColorPaginas('null');
            $lectura->setContadorColorML('null');
        }
        
        /*Nivel Toner Negro*/
        if(isset($parametros['toner_bn_'.$contador]) && $parametros['toner_bn_'.$contador]!=""){
            $lectura->setNivelTonNegro($parametros['toner_bn_'.$contador]);        
        }else{
            $lectura->setNivelTonNegro('null');        
        }
        /*Nivel Toner Magenta*/
        if(isset($parametros['toner_mag_'.$contador]) && $parametros['toner_mag_'.$contador]!=""){
            $lectura->setNivelTonMagenta($parametros['toner_mag_'.$contador]);
        }else{
            $lectura->setNivelTonMagenta('null');
        }
        /*Nivel Toner Cian*/
        if(isset($parametros['toner_cian_'.$contador]) && $parametros['toner_cian_'.$contador]!=""){
            $lectura->setNivelTonCian($parametros['toner_cian_'.$contador]);
        }else{
            $lectura->setNivelTonCian('null');
        }
        /*Nivel Toner Amarillo*/
        if(isset($parametros['toner_amarillo_'.$contador]) && $parametros['toner_amarillo_'.$contador]!=""){
            $lectura->setNivelTonAmarillo($parametros['toner_amarillo_'.$contador]);
        }else{
            $lectura->setNivelTonAmarillo('null');
        }

        $lectura->setFecha($parametros['fecha_captura']);
        $lectura->setActivo(1);
        $lectura->setUsuarioCreacion($_SESSION['user']);
        $lectura->setUsuarioUltimaModificacion($_SESSION['user']);
        $lectura->setLecturaCorte(1);
        $lectura->setComentario($parametros['comentario_'.$contador]);
        $lectura->setPantalla("PHP Lectura corte");

        if($insertar){//Si hay algun contador
            if($parametros['existe_'.$contador] == "0"){/*Nueva lectura*/
                if(!$lectura->newRegistro()){
                    echo "<br/>Error: no se pudo insertar la lectura del equipo ".$parametros['serie_'.$contador];
                    $error = true;
                }
            }else{
                $lectura->setIdLectura($parametros['existe_'.$contador]);
                if(!$lectura->editarRegistro()){
                    echo "<br/>Error: no se pudo actualizar la lectura del equipo ".$parametros['serie_'.$contador];
                    $error = true;
                }
            }
        }
    }
}

if(!$error){
    echo "<br/>Las lecturas fueron registradas correctamente";
}
?>

<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
        <meta http-equiv="X-UA-Compatible" content="IE=8" />
        <meta http-equiv="X-UA-Compatible" content="IE=7" />
        <meta name="description" content="SMUC Sistema de Monitoreo Ubicacion y Control"/>
        <meta name="keywords" content="CAOMI,Rackspace,Romas Informatica, MAGG, Mexico, ESCOM"/>
        <meta name="author" content="CAOMI"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>        
        <title></title>        
    </head>
    <?php    
    include_once("../WEB-INF/Classes/Catalogo.class.php");
    include_once("../WEB-INF/Classes/ReporteUso.class.php");    
    
    $catalogo = new Catalogo();
    $allowedExts = array("csv", "log", "LOG");
    $temp = explode(".", $_FILES["file"]["name"]);
    $extension = end($temp);
    if(in_array($extension, $allowedExts) /*&& ($_FILES["file"]["size"] < 190000)*/){
        if ($_FILES["file"]["error"] > 0){
          echo "Error en el archivo: " . $_FILES["file"]["error"] . "<br>";
        }else{/*Si no ha error al subir el archivo*/
            $folder = "upload";
            $nombre_archivo = $_FILES["file"]["name"];
            $existe = false;            
            do{/*renombramos el archivo si ya existe ese nombre*/
                if (file_exists($folder."/" . $nombre_archivo)) {
                    $existe = true;
                    $nombre_archivo = "(1)".$nombre_archivo;                    
                }else{
                    $existe=false;
                }  
            }while($existe);
            
            move_uploaded_file($_FILES["file"]["tmp_name"], $folder."/" . $nombre_archivo);
            $idReporte = $catalogo->insertarRegistro("INSERT INTO c_reporteuso(fecha,archivo,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,"
                    . "FechaUltimaModificacion,Pantalla) VALUES(NOW(),'$folder/$nombre_archivo',1,'".$_SESSION['user']."',NOW(),'".$_SESSION['user']."',NOW(),'upload_file.php');");            
            echo "IdReporte:$idReporte.Fin.";
            /*Leemos el excel guardado y lo vamos insertando en la base de datos*/
            $fila = 1;
            if (($gestor = fopen($folder."/".$nombre_archivo, "r")) !== FALSE) {
                while (($datos = fgetcsv($gestor, 0, ",")) !== FALSE) {
                    $numero = count($datos);
                    $obj = new ReporteUso();
                    $obj->setIdReporte($idReporte);
                    //echo "<p> $numero de campos en la línea $fila: <br /></p>\n";
                    $fila++;
                    for ($c=0; $c < $numero; $c++) {
                        //echo $datos[$c] . "<br />\n";                        
                        switch($c){
                            case 9:
                                $obj->setValores($datos[$c]);
                                $pos = strpos ($datos[$c], "/Jt=");//Tipo (101: copia, 102: scan, 103: impresion)
                                if ($pos !== false) { // nota: tres signos de igual
                                    $pos_final = strpos($datos[$c], "/", $pos +1);
                                    $tipo = substr($datos[$c], $pos+4, $pos_final - ($pos+4));
                                    if($tipo != "101" && $tipo!="102" && $tipo != "103"){
                                        echo "Pos final: ".$pos_final;
                                        echo "<br/>$tipo: No sirve";
                                        continue 3;
                                    }
                                    $obj->setTipo($tipo);
                                    
                                    $pos = strpos ($datos[$c], "/Cp=");//Hojas B/N
                                    if ($pos !== false) { // nota: tres signos de igual
                                        $pos_final = strpos ($datos[$c], "/", $pos + 1);
                                        $bn = substr($datos[$c], $pos+4, $pos_final - ($pos+4));
                                        $obj->setBN($bn);
                                    }else{
                                        $obj->setBN(0);
                                    }
                                    
                                    $pos = strpos($datos[$c], "/Cg=");//Hojas B/N
                                    if ($pos !== false) { // nota: tres signos de igual
                                        $pos_final = strpos ($datos[$c], "/", $pos + 1);
                                        $color = substr($datos[$c], $pos+4, $pos_final - ($pos+4));
                                        $obj->setColor($color);
                                    }else{
                                        $obj->setColor(0);
                                    }
                                    
                                }else{/*Si no hay tipo (101: copia, 102: scan, 103: impresion)*/
                                    continue 3;
                                }
                                break;
                            case 0:
                                $obj->setNombreDeDominio($datos[$c]);
                                break;
                            case 1:
                                $obj->setNombreDocumento($datos[$c]);
                                break;
                            case 2:
                                $obj->setImpresora($datos[$c]);
                                break;
                            case 3:
                                $fecha = explode("/", $datos[$c]);
                                $obj->setFecha($fecha[2]."-".$fecha[1]."-".$fecha[0]);
                                break;
                            case 4:
                                $obj->setHora("00:00");
                                break;
                            case 5:                                
                                $obj->setComputadora($datos[$c]);
                                break;
                            case 6:
                                break;
                            case 7:
                                $obj->setClaveCentroCosto($datos[$c]);
                                break;
                            case 8:
                                $obj->setTamano($datos[$c]);
                                break;                            
                            case 10:
                                $obj->setBytes($datos[$c]);
                                break;
                            case 11:
                                $obj->setPageCount($datos[$c]);
                                break;
                            case 12:
                                $obj->setCosto($datos[$c]);
                                break;
                            case 13:
                                $obj->setBalance($datos[$c]);
                                break;
                            default:
                                break;
                        }
                    }
                    $obj->setActivo(1);
                    $obj->setUsuarioCreacion($_SESSION['user']);
                    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
                    $obj->setPantalla("upload_file.php");
                    $obj->newRegistro();
                }
                fclose($gestor);
            }
        }        
    }else{
        echo "Necesitas elegir una archivo con formato .csv o .log";
    }
?>    
</html>
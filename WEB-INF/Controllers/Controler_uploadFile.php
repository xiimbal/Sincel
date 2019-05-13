<?php

include_once("../Classes/Equipo.class.php");
$obj = new Equipo();
$id = "";
$tipo = "";
$nombre_archivo = "";


$tabla = "";
$campo = "";
$folder = "";

if (isset($_POST['folio'])) {
    $id = $_POST['folio'];
    if (isset($_FILES['archivo']['name'])) {
        $nombre_archivo = $_FILES['archivo']['name'];
        if (isset($_POST['tipo'])) {
            $tipo = $_POST['tipo'];
            if ($id != "" && $tipo != "" && $nombre_archivo != "") {
                $tmp_archivo = $_FILES['archivo']['tmp_name'];
                if ($tipo == "equipo") {//valida de sonde viene el archivo * este ejemplo viene de equipo
                    $tabla = "c_equipo"; //nombre del la tabla a modificar
                    $campo = "PathImagen"; //campo a modificar
                    $idMod = "NoParte"; //campo parala condicion del la modificacion
                    $folder = 'documentos/equipos'; //folder donde se guardaran los archivos
                    
                }
                
                
                else if ($tipo == "equipoLista") {//otro tipo de archivo
                    $tabla = "c_equipo"; //nombre del la tabla a modificar
                    $campo = "PathListaPartes"; //campo a modificar
                    $idMod = "NoParte"; //campo parala condicion del la modificacion
                    $folder = 'documentos/equipos'; //folder donde se guardaran los archivos
                }
                else if ($tipo == "equipoGuia") {//otro tipo de archivo
                    $tabla = "c_equipo"; //nombre del la tabla a modificar
                    $campo = "PathOperacion"; //campo a modificar
                    $idMod = "NoParte"; //campo parala condicion del la modificacion
                    $folder = 'documentos/equipos'; //folder donde se guardaran los archivos
                }
                else if ($tipo == "equipoOpAvan") {//otro tipo de archivo
                    $tabla = "c_equipo"; //nombre del la tabla a modificar
                    $campo = "PathGuiaOperacionAvanza"; //campo a modificar
                    $idMod = "NoParte"; //campo parala condicion del la modificacion
                    $folder = 'documentos/equipos'; //folder donde se guardaran los archivos
                }
                else if ($tipo == "equipoManual") {//otro tipo de archivo
                    $tabla = "c_equipo"; //nombre del la tabla a modificar
                    $campo = "PathManualServicio"; //campo a modificar
                    $idMod = "NoParte"; //campo parala condicion del la modificacion
                    $folder = 'documentos/equipos'; //folder donde se guardaran los archivos
                }
                else if ($tipo == "equipoEspec") {//otro tipo de archivo
                    $tabla = "c_equipo"; //nombre del la tabla a modificar
                    $campo = "PathEspecificacionesTecnicas"; //campo a modificar
                    $idMod = "NoParte"; //campo parala condicion del la modificacion
                    $folder = 'documentos/equipos'; //folder donde se guardaran los archivos
                }
                
                else if ($tipo == "componentes") {//otro tipo de archivo
                    $tabla = "c_componente"; //nombre del la tabla a modificar
                    $campo = "PathImagen"; //campo a modificar
                    $idMod = "NoParte"; //campo parala condicion del la modificacion
                    $folder = 'documentos/componentes'; //folder donde se guardaran los archivos
                }
                if (!file_exists($folder)) {/* Creamos la carpeta sino existe */
                    mkdir($folder);
                }
                $existe = false;
                do {/* renombramos el archivo si ya existe ese nombre */
                    if (file_exists($folder . "/" . $nombre_archivo)) {
                        $existe = true;
                        $nombre_archivo = "(1)" . $nombre_archivo;
                    } else {
                        $existe = false;
                    }
                } while ($existe);
                $archivador = $folder . "/" . $nombre_archivo;
                move_uploaded_file($tmp_archivo, $folder . "/" . $nombre_archivo);
                if ($obj->editUrlImg($tabla, $campo, $archivador, $idMod, $id)) {
                    echo "El equipo <b>" . $obj->getModelo() . "</b> se modificó correctamente";
                } else {
                    echo "Error: El equipo <b>" . $obj->getModelo() . "</b> ya se encuentra registrado";
                }
            } else {
                echo "no hacer nada";
            }
        }
    } else {
        echo "no hay nombre";
    }
} else {
    echo "no existe folio";
}

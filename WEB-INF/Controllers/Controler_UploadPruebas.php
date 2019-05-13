<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

//comprobamos que sea una petición ajax
if (isset($_POST['folio']) && $_POST['folio'] != "") {
    include_once("../Classes/Equipo.class.php");
    $obj = new Equipo();
    $tabla = "c_equipo";
    if ($_POST['tipo'] == 0) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

            //obtenemos el archivo a subir
            $file = $_FILES['imagen']['name'];

            //comprobamos si existe un directorio para subir el archivo
            //si no es así, lo creamos
            if (!is_dir("documentos/equipos/"))
                mkdir("documentos/equipos/", 0777);

            $existe = false;
            do {/* renombramos el archivo si ya existe ese nombre */
                if (file_exists("documentos/equipos/images" . $file)) {
                    $existe = true;
                    $file = "(1)" . $file;
                } else {
                    $existe = false;
                }
            } while ($existe);
            
            $file = "images/".$file;
            //comprobamos si el archivo ha subido
            if ($file && move_uploaded_file($_FILES['imagen']['tmp_name'], "documentos/equipos/" . $file)) {
                sleep(3); //retrasamos la petición 3 segundos
                if ($obj->editUrlImg($tabla, "PathImagen", $file, "NoParte", $_POST['folio'])) {
                    echo $file;
                }
                //devolvemos el nombre del archivo para pintar la imagen
            }
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }else if ($_POST['tipo'] == 1) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

            //obtenemos el archivo a subir
            $file = $_FILES['listaPartes']['name'];

            //comprobamos si existe un directorio para subir el archivo
            //si no es así, lo creamos
            if (!is_dir("documentos/equipos/"))
                mkdir("documentos/equipos/", 0777);

            $existe = false;
            do {/* renombramos el archivo si ya existe ese nombre */
                if (file_exists("documentos/equipos/" . $file)) {
                    $existe = true;
                    $file = "(1)" . $file;
                } else {
                    $existe = false;
                }
            } while ($existe);
            
            $file = "ListadePartes/".$file;
            //comprobamos si el archivo ha subido
            if ($file && move_uploaded_file($_FILES['listaPartes']['tmp_name'], "documentos/equipos/" . $file)) {
                sleep(3); //retrasamos la petición 3 segundos
                if ($obj->editUrlImg($tabla, "PathListaPartes", $file, "NoParte", $_POST['folio'])) {
                    echo $file;
                }


                //devolvemos el nombre del archivo para pintar la imagen
            }
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }else if ($_POST['tipo'] == 2) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

            //obtenemos el archivo a subir
            $file = $_FILES['giaOperacion']['name'];

            //comprobamos si existe un directorio para subir el archivo
            //si no es así, lo creamos
            if (!is_dir("documentos/equipos/"))
                mkdir("documentos/equipos/", 0777);

            $existe = false;
            do {/* renombramos el archivo si ya existe ese nombre */
                if (file_exists("documentos/equipos/" . $file)) {
                    $existe = true;
                    $file = "(1)" . $file;
                } else {
                    $existe = false;
                }
            } while ($existe);
            
            $file = "GuiadeOperacion/".$file;
            //comprobamos si el archivo ha subido
            if ($file && move_uploaded_file($_FILES['giaOperacion']['tmp_name'], "documentos/equipos/" . $file)) {
                sleep(3); //retrasamos la petición 3 segundos
                if ($obj->editUrlImg($tabla, "PathOperacion", $file, "NoParte", $_POST['folio'])) {
                    echo $file;
                }
            }
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }else if ($_POST['tipo'] == 3) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

            //obtenemos el archivo a subir
            $file = $_FILES['guiaOpAvanzada']['name'];

            //comprobamos si existe un directorio para subir el archivo
            //si no es así, lo creamos
            if (!is_dir("documentos/equipos/"))
                mkdir("documentos/equipos/", 0777);

            $existe = false;
            do {/* renombramos el archivo si ya existe ese nombre */
                if (file_exists("documentos/equipos/" . $file)) {
                    $existe = true;
                    $file = "(1)" . $file;
                } else {
                    $existe = false;
                }
            } while ($existe);
            
            $file = "GuiadeOperacionAvanzada/".$file;
            //comprobamos si el archivo ha subido
            if ($file && move_uploaded_file($_FILES['guiaOpAvanzada']['tmp_name'], "documentos/equipos/" . $file)) {
                sleep(3); //retrasamos la petición 3 segundos
                // echo $file; //devolvemos el nombre del archivo para pintar la imagen
                if ($obj->editUrlImg($tabla, "PathGuiaOperacionAvanza", $file, "NoParte", $_POST['folio'])) {
                    echo $file;
                }
            }
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }else if ($_POST['tipo'] == 4) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

            //obtenemos el archivo a subir
            $file = $_FILES['manualServicio']['name'];

            //comprobamos si existe un directorio para subir el archivo
            //si no es así, lo creamos
            if (!is_dir("documentos/equipos/"))
                mkdir("documentos/equipos/", 0777);

            $existe = false;
            do {/* renombramos el archivo si ya existe ese nombre */
                if (file_exists("documentos/equipos/" . $file)) {
                    $existe = true;
                    $file = "(1)" . $file;
                } else {
                    $existe = false;
                }
            } while ($existe);
            
            $file = "ManualServicio/".$file;
            //comprobamos si el archivo ha subido
            if ($file && move_uploaded_file($_FILES['manualServicio']['tmp_name'], "documentos/equipos/" . $file)) {
                sleep(3); //retrasamos la petición 3 segundos
                //echo $file; //devolvemos el nombre del archivo para pintar la imagen
                if ($obj->editUrlImg($tabla, "PathManualServicio", $file, "NoParte", $_POST['folio'])) {
                    echo $file;
                }
            }
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }else if ($_POST['tipo'] == 5) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

            //obtenemos el archivo a subir
            $file = $_FILES['EspecificacionTec']['name'];

            //comprobamos si existe un directorio para subir el archivo
            //si no es así, lo creamos
            if (!is_dir("documentos/equipos/"))
                mkdir("documentos/equipos/", 0777);

            $existe = false;
            do {/* renombramos el archivo si ya existe ese nombre */
                if (file_exists("documentos/equipos/" . $file)) {
                    $existe = true;
                    $file = "(1)" . $file;
                } else {
                    $existe = false;
                }
            } while ($existe);
            
            $file = "EspecificacionesTecnicas/".$file;
            //comprobamos si el archivo ha subido
            if ($file && move_uploaded_file($_FILES['EspecificacionTec']['tmp_name'], "documentos/equipos/" . $file)) {
                sleep(3); //retrasamos la petición 3 segundos
                // echo $file; //devolvemos el nombre del archivo para pintar la imagen
                if ($obj->editUrlImg($tabla, "PathEspecificacionesTecnicas", $file, "NoParte", $_POST['folio'])) {
                    echo $file;
                }
            }
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }else if($_POST['tipo'] == 6){//Sube imagenes de calificaciones (Para Guau)                
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {            
            include_once("../Classes/Cliente.class.php");
            //obtenemos el archivo a subir
            $file = $_FILES['foto']['name'];

            //comprobamos si existe un directorio para subir el archivo
            //si no es así, lo creamos
            if (!is_dir("../../WebService/uploads/")){
                mkdir("../../WebService/uploads/", 0777);
                echo "No existe dir";
            }

            $existe = false;
            do {/* renombramos el archivo si ya existe ese nombre */
                if (file_exists("../../WebService/uploads/" . $file)) {
                    $existe = true;
                    $file = "(1)" . $file;
                } else {
                    $existe = false;
                }
            } while ($existe);
            echo $file;
            //comprobamos si el archivo ha subido
            if ($file && move_uploaded_file($_FILES['foto']['tmp_name'], "../../WebService/uploads/" . $file)) {                
                sleep(3); //retrasamos la petición 3 segundos
                $cliente = new Cliente();
                $cliente->setEmpresa($_POST['empresa']);
                $cliente->setId_calificacion($_POST['folio']);
                $cliente->setFoto("WebService/uploads/" . $file);
                if ($cliente->editJustCalificacion()) {                    
                    echo $file;
                }
            }
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }
}
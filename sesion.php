<?php

include_once("WEB-INF/Classes/Session.class.php");
include_once("WEB-INF/Classes/Parametros.class.php");

// CERRAR SESION
if (isset($_GET['cerrar']) && $_GET['cerrar'] == '1') { 
    session_start();
    session_destroy();
    header("Location: index.php");
}else { /* Abrir sesion */
    
    /* Si se recibe por post el username y password y son diferente a una cadena vacia */
    if (isset($_POST['username']) && $_POST['username'] != "" && isset($_POST['password']) && $_POST['password'] != "") {
        if (!stripos($_POST['username'], "DELETE") &&
                !stripos($_POST['username'], "INSERT") &&
                !stripos($_POST['username'], "UPDATE") &&
                !stripos($_POST['password'], "DELETE") &&
                !stripos($_POST['password'], "INSERT") &&
                !stripos($_POST['password'], "UPDATE")
        ) {
            $session = new Session();
            // ** PCM 30/01/2019
            $session_true = $session->getLogginMultiBD($_POST['username'], $_POST['password']);
            if ($session_true == true){
                $u_activo = $session->getUser_activo();
                $e_activa = $session->getEmpresa_activa();
                if ($u_activo == 0){
                    $session_true = false;
                    header("Location: index.php?session=false2");
                }else if($e_activa == 0){
                    $session_true = false;
                    header("Location: index.php?session=false3");
                }
            }

            if ($session_true == true) { //Buscamos el id de la empresa en la multiBD    
                // ** PCM   
                ini_set("session.gc_maxlifetime", 10800);
                session_start();
                $_SESSION['idEmpresa'] = $session->getId_empresa();
                $_SESSION['idUsuarioMBD'] = $session->getId_usu();
                $_SESSION['nombreEmpresa'] = $session->getNombre_empresa();
                $session->marcarIntentoCorrecto($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_X_FORWARDED_FOR'], $_POST['username']);
                if ($session->getLogin($_POST['username'], $_POST['password'])) {//Buscamos el id del usuario dentro de la base de la empresa                                    
                    $_SESSION['user'] = $_POST['username'];
                    $_SESSION['idUsuario'] = $session->getId_usu();
                    $_SESSION['ruta_controler'] = "WEB-INF/Controllers/";
                    /* $_SESSION['liga'] = "http://genesis1.techra.com.mx";
                      $_SESSION['ip_server'] = "http://genesis.techra.com.mx/";
                      $_SESSION['liga'] = "http://pruebasgenesis1.techra.com.mx";
                      $_SESSION['ip_server'] = "http://pruebas.techra.com.mx"; */
                    /* Obtenemos los parametros desde la bd */
                    $parametros = new Parametros();
                    $parametros->getRegistroById(7);
                    $_SESSION['liga'] = $parametros->getDescripcion();
                    $parametros->getRegistroById(8);
                    $_SESSION['ip_server'] = $parametros->getDescripcion();
                    header("Location: principal.php");
                } else {
                    header("Location: index.php?session=false");
                }
            } else {
                if($session->marcarIntentoFallido($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_X_FORWARDED_FOR'], $_POST['username'])){//Mas de 3 intentos fallidos
                    header("Location: index.php?session=false1&usr=".$_POST['username']);
                }else{
                    header("Location: index.php?session=false");
                }
            }
        } else {/* Usuario y password malicioso, tal vez intenta sabotear el sistema */
            header("Location: index.php?session=danger");
        }
    } else {/* No pueden acceder directo a esta pagina */
        header("Location: index.php");
    }
}
?>
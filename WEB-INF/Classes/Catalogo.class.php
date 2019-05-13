<?php

include_once("Conexion.class.php");
include_once("ParametroGlobal.class.php");
include_once("Mail.class.php");

/**
 * Description of Catalogo
 *
 * @author MAGG
 */
class Catalogo {
    private $empresa;

    public function multiQuery($consultas) {
        $array_consultas = split(";", $consultas);
        $conexion = new Conexion();
        if (isset($this->empresa)) {
            $conexion->setEmpresa($this->empresa);
        }
        $conexion->Conectar();
        $resultado = "1";
        foreach ($array_consultas as $consulta) {
            if ($consulta == "") {
                continue;
            }
            $resultado = $conexion->Ejecutar($consulta);
            if ($resultado != "1") {
                break;
            }
            /* Guardamos los queries que se ejecutan (INSERT, DELETE y UPDATE) */
            $log = false;
            if (strpos(strtoupper($consulta), 'INSERT') !== false) {
                $tipo = "INSERT";
                $log = true;
            } else if (strpos(strtoupper($consulta), 'DELETE') !== false) {
                $tipo = "DELETE";
                $log = true;
            } else if (strpos(strtoupper($consulta), 'UPDATE') !== false) {
                $tipo = "UPDATE";
                $log = true;
            }

            if ($log) {//Si se va a registrar un log
                if (isset($_SESSION['idUsuario'])) {
                    $usuario = $_SESSION['idUsuario'];
                } else {
                    /* Obtenemos el usuario que se pone por default segun los parametros globales */
                    $parametro = new ParametroGlobal();
                    if (isset($this->empresa)) {
                        $parametro->setEmpresa($this->empresa);
                    }
                    if ($parametro->getRegistroById("7")) {
                        $usuario = $parametro->getValor();
                    } else {
                        $usuario = "1";
                    }
                    $conexion->Conectar(); //Como parametros cierra la conexion, se tiene que volver a abrir
                }
                $consulta = "INSERT INTO c_log(IdQuery, Consulta, Fecha, IdUsuario, Tipo) VALUES(0,'" . str_replace("'", "´", $consulta) . "',NOW(),$usuario,'$tipo');";
                $conexion->Ejecutar($consulta);
            }
        }
        $conexion->Desconectar();
        return $resultado;
    }

    public function obtenerLista($consulta) {
        $conexion = new Conexion();
        if (isset($this->empresa)) {
            $conexion->setEmpresa($this->empresa);
        }
        $conexion->Conectar();
        $query = $conexion->Ejecutar($consulta);
        /* Guardamos los queries que se ejecutan (INSERT, DELETE y UPDATE) */
        $log = false;
        if (strpos(strtoupper($consulta), 'INSERT') !== false) {
            $tipo = "INSERT";
            $log = true;
        } else if (strpos(strtoupper($consulta), 'DELETE') !== false) {
            $tipo = "DELETE";
            $log = true;
        } else if (strpos(strtoupper($consulta), 'UPDATE') !== false) {
            $tipo = "UPDATE";
            $log = true;
        }

        if ($log) {//Si se va a registrar un log
            if (isset($_SESSION['idUsuario'])) {
                $usuario = $_SESSION['idUsuario'];
            } else {
                /* Obtenemos el usuario que se pone por default segun los parametros globales */
                $parametro = new ParametroGlobal();
                if (isset($this->empresa)) {
                    $parametro->setEmpresa($this->empresa);
                }
                if ($parametro->getRegistroById("7")) {
                    $usuario = $parametro->getValor();
                } else {
                    $usuario = "1";
                }
                $conexion->Conectar(); //Como parametros cierra la conexion, se tiene que volver a abrir
            }
            $consulta = "INSERT INTO c_log(IdQuery, Consulta, Fecha, IdUsuario, Tipo) VALUES(0,'" . str_replace("'", "´", $consulta) . "',NOW(),$usuario,'$tipo');";
            $conexion->Ejecutar($consulta);
        }
        $conexion->Desconectar();
        return $query;
    }

    public function insertarRegistro($consulta) {
        $conexion = new Conexion();
        if (isset($this->empresa)) {
            $conexion->setEmpresa($this->empresa);
        }
        $conexion->Conectar();
        $query = $conexion->Ejecutar($consulta);
        $id = mysql_insert_id();
        /* Guardamos los queries que se ejecutan (INSERT, DELETE y UPDATE) */
        $log = false;
        if (strpos(strtoupper($consulta), 'INSERT') !== false) {
            $tipo = "INSERT";
            $log = true;
        } else if (strpos(strtoupper($consulta), 'DELETE') !== false) {
            $tipo = "DELETE";
            $log = true;
        } else if (strpos(strtoupper($consulta), 'UPDATE') !== false) {
            $tipo = "UPDATE";
            $log = true;
        }
        if ($log) {//Si se va a registrar un log
            if (isset($_SESSION['idUsuario'])) {
                $usuario = $_SESSION['idUsuario'];
            } else {
                /* Obtenemos el usuario que se pone por default segun los parametros globales */
                $parametro = new ParametroGlobal();
                if (isset($this->empresa)) {
                    $parametro->setEmpresa($this->empresa);
                }
                if ($parametro->getRegistroById("7")) {
                    $usuario = $parametro->getValor();
                } else {
                    $usuario = "1";
                }
                $conexion->Conectar(); //Como parametros cierra la conexion, se tiene que volver a abrir
            }
            $consulta = "INSERT INTO c_log(IdQuery, Consulta, Fecha, IdUsuario, Tipo) VALUES(0,'" . str_replace("'", "´", $consulta) . "',NOW(),$usuario,'$tipo');";
            $conexion->Ejecutar($consulta);
        }
        $conexion->Desconectar();
        return $id;
    }

    public function getListaAlta($tabla, $order_by) {
        $conexion = new Conexion();
        if (isset($this->empresa)) {
            $conexion->setEmpresa($this->empresa);
        }
        $conexion->Conectar();
        $order = "";
        if ($order_by != "") {
            $order = "ORDER BY `" . $order_by . "`";
        }

        $consulta = "SELECT * FROM `" . $tabla . "` Where Activo = 1 " . $order . ";";
        $query = $conexion->Ejecutar($consulta);

        $conexion->Desconectar();
        return $query;
    }

    public function getListaAltaTodo($tabla, $order_by) {
        $conexion = new Conexion();
        if (isset($this->empresa)) {
            $conexion->setEmpresa($this->empresa);
        }
        $conexion->Conectar();
        $order = "";
        if ($order_by != "") {
            $order = "ORDER BY `" . $order_by . "`";
        }
        $query = $conexion->Ejecutar("SELECT * FROM `" . $tabla . "` " . $order . ";");
        $conexion->Desconectar();
        return $query;
    }

    function enviarCorreo($subject, $correos, $message, $pintar_mensaje) {
        $mail = new Mail();
        $parametroGlobal = new ParametroGlobal();
        if(isset($this->empresa)){
            $parametroGlobal->setEmpresa($this->empresa);
        }
        if ($parametroGlobal->getRegistroById("8")) {
            $mail->setFrom($parametroGlobal->getValor());
        } else {
            $mail->setFrom("scg-salida@scgenesis.mx");
        }
        $mail->setSubject($subject);
        $mail->setBody($message);
        foreach ($correos as $value) {
            if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                $mail->setTo($value);
                if ($mail->enviarMail() == "1") {
                    if ($pintar_mensaje) {
                        echo "<br/>Un correo fue enviado a $value.";
                    }
                } else {
                    if ($pintar_mensaje) {
                        echo "<br/>Error: No se pudo enviar el correo a $value";
                    }
                }
            }
        }
    }

    function formatoFechaReportes($fecha) {
        if(empty($fecha)){
            return "";
        }
        $mes = "";
        $aux = explode("-", $fecha);
        switch ($aux[1]) {
            case '01':
                $mes = "Enero";
                break;
            case '02':
                $mes = "Febrero";
                break;
            case '03':
                $mes = "Marzo";
                break;
            case '04':
                $mes = "Abril";
                break;
            case '05':
                $mes = "Mayo";
                break;
            case '06':
                $mes = "Junio";
                break;
            case '07':
                $mes = "Julio";
                break;
            case '08':
                $mes = "Agosto";
                break;
            case '09':
                $mes = "Septiembre";
                break;
            case '10':
                $mes = "Octubre";
                break;
            case '11':
                $mes = "Noviembre";
                break;
            case '12':
                $mes = "Diciembre";
                break;
        }
        $formatFecha = $aux[2] . " de " . $mes . " de " . $aux[0];
        return $formatFecha;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>

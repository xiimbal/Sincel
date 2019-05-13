<?php

include_once("ConexionFacturacion.class.php");
include_once("ParametroGlobal.class.php");

/**
 * Description of Catalogo
 *
 * @author MAGG
 */
class CatalogoFacturacion {
    private $empresa;
    
    public function obtenerLista($consulta) {
        $conexion = new ConexionFacturacion();
        if(isset($this->empresa)){
            $conexion->setEmpresa($this->empresa);
        }
        $conexion->Conectar();
        $query = $conexion->Ejecutar($consulta);
        /*Guardamos los queries que se ejecutan (INSERT, DELETE y UPDATE)*/    
        $log = false;
        if (strpos(strtoupper($consulta),'INSERT') !== false) {
            $tipo = "INSERT";
            $log = true;
        }else if (strpos(strtoupper($consulta),'DELETE') !== false) {
            $tipo = "DELETE";
            $log = true;
        }else if (strpos(strtoupper($consulta),'UPDATE') !== false) {
            $tipo = "UPDATE";
            $log = true;
        }
        if($log){//Si se va a registrar un log
            if(isset($_SESSION['idUsuario'])){
                $usuario = $_SESSION['idUsuario'];
            }else{
                /*Obtenemos el usuario que se pone por default segun los parametros globales*/
                $parametro = new ParametroGlobal();
                if(isset($this->empresa)){
                    $parametro->setEmpresa($this->empresa);
                }
                if($parametro->getRegistroById("7")){
                    $usuario = $parametro->getValor();
                }else{
                    $usuario = "1";
                }
                $conexion->Conectar();
            }
            $consulta = "INSERT INTO c_log(IdQuery, Consulta, Fecha, IdUsuario, Tipo) VALUES(0,'".  str_replace("'", "´", $consulta)."',NOW(),$usuario,'$tipo');";
            $conexion->Ejecutar($consulta);
        }        
        $conexion->Desconectar();
        return $query;
    }

    public function insertarRegistro($consulta) {
        $conexion = new ConexionFacturacion();
        if(isset($this->empresa)){
            $conexion->setEmpresa($this->empresa);
        }
        $conexion->Conectar();
        $query = $conexion->Ejecutar($consulta);
        $id = mysql_insert_id();
        /*Guardamos los queries que se ejecutan (INSERT, DELETE y UPDATE)*/    
        $log = false;
        if (strpos(strtoupper($consulta),'INSERT') !== false) {
            $tipo = "INSERT";
            $log = true;
        }else if (strpos(strtoupper($consulta),'DELETE') !== false) {
            $tipo = "DELETE";
            $log = true;
        }else if (strpos(strtoupper($consulta),'UPDATE') !== false) {
            $tipo = "UPDATE";
            $log = true;
        }
        if($log){//Si se va a registrar un log
            if(isset($_SESSION['idUsuario'])){
                $usuario = $_SESSION['idUsuario'];
            }else{
                /*Obtenemos el usuario que se pone por default segun los parametros globales*/
                $parametro = new ParametroGlobal();
                if(isset($this->empresa)){
                    $parametro->setEmpresa($this->empresa);
                }
                if($parametro->getRegistroById("7")){
                    $usuario = $parametro->getValor();
                }else{
                    $usuario = "1";
                }
                $conexion->Conectar();//Como parametros cierra la conexion, se tiene que volver a abrir
            }
            $consulta = "INSERT INTO c_log(IdQuery, Consulta, Fecha, IdUsuario, Tipo) VALUES(0,'".  str_replace("'", "´", $consulta)."',NOW(),$usuario,'$tipo');";
            $conexion->Ejecutar($consulta);
        }        
        $conexion->Desconectar();
        return $id;
    }

    public function getListaAlta($tabla, $order_by) {
        $conexion = new ConexionFacturacion();
        if(isset($this->empresa)){
            $conexion->setEmpresa($this->empresa);
        }        
        $order = "";
        if ($order_by != "") {
            $order = "ORDER BY `" . $order_by . "`";
        }
        $conexion->Conectar();
        $query = $conexion->Ejecutar("SELECT * FROM `" . $tabla . "` Where Activo = 1 " . $order . ";");        
        $conexion->Desconectar();
        return $query;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
}

?>

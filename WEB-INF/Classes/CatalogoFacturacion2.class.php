<?php

include_once("ConexionFacturacion2.class.php");

/**
 * Description of Catalogo
 *
 * @author MAGG
 */
class CatalogoFacturacion2 {

    public function obtenerLista($consulta) {
        $this->conn = new ConexionFacturacion2();
        $query = mysql_query($consulta);
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
                if($parametro->getRegistroById("7")){
                    $usuario = $parametro->getValor();
                }else{
                    $usuario = "1";
                }
            }
            $consulta = "INSERT INTO c_log(IdQuery, Consulta, Fecha, IdUsuario, Tipo) VALUES(0,'".  str_replace("'", "´", $consulta)."',NOW(),$usuario,'$tipo');";
            mysql_query($consulta);
        }        
        $this->conn->DesconectarF();
        return $query;
    }

    public function insertarRegistro($consulta) {
        $this->conn = new ConexionFacturacion2();
        $query = mysql_query($consulta);
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
                if($parametro->getRegistroById("7")){
                    $usuario = $parametro->getValor();
                }else{
                    $usuario = "1";
                }
            }
            $consulta = "INSERT INTO c_log(IdQuery, Consulta, Fecha, IdUsuario, Tipo) VALUES(0,'".  str_replace("'", "´", $consulta)."',NOW(),$usuario,'$tipo');";
            mysql_query($consulta);
        }        
        $this->conn->DesconectarF();
        return $id;
    }

    public function getListaAlta($tabla, $order_by) {
        $this->conn = new ConexionFacturacion2();
        $order = "";
        if ($order_by != "") {
            $order = "ORDER BY `" . $order_by . "`";
        }
        $query = mysql_query("SELECT * FROM `" . $tabla . "` Where Activo = 1 " . $order . ";");
        $this->conn->DesconectarF();
        return $query;
    }

}

?>

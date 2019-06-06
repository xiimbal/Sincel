<?php

include_once("ConexionMultiBD.class.php");

class Conexion {
    var $db;   
    var $empresa;
    
    public function Conectar() {
        $conexionMulti = new ConexionMultiBD();        
        if(isset($_SESSION['idEmpresa'])){
            $this->empresa = $_SESSION['idEmpresa'];
        }else if(isset ($this->empresa)){
            
        }else{
            echo "Error: No se pudo obtener la información para la conexión a la BD.";
            return false;
        }
        
        $consulta = "SELECT b.ip_base, b.login_base, b.pass_base, b.nombre_base 
                FROM c_empresa AS e
                INNER JOIN c_base AS b ON e.id_empresa = b.id_empresa
                WHERE e.id_empresa = $this->empresa AND e.Activo = 1 AND b.Activo = 1 AND b.tipo_base = 1;";
        $result = $conexionMulti->Ejecutar($consulta);
        while($rs = mysql_fetch_array($result)){
            $MYSQL_HOST = $rs['ip_base'];
            $MYSQL_DB = $rs['nombre_base'];
            $MYSQL_LOGIN = $rs['login_base'];
            $MYSQL_PASS = $rs['pass_base'];
        }
        $conexionMulti->Desconectar();        

        $this->db = mysql_connect($MYSQL_HOST, $MYSQL_LOGIN, $MYSQL_PASS);        
        @mysql_query("SET NAMES 'UTF8'", $this->db);
        if (!$this->db) {
            echo('Error: Imposible conectar a la base de datos, verifique que el servidor funcione correctamente: ' . mysql_error());
            exit;
        }
        mysql_select_db($MYSQL_DB);
    }

    function Desconectar() {
        if (gettype($this->db) == "resource") {
            mysql_close($this->db);
        }
    }

    function Ejecutar($query) {
        $resultado = mysql_query($query);        
        if (!$resultado) {
            $resultado = mysql_error();
        }
        return $resultado;
    }
    
    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
}

?>
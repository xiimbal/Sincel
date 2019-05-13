<?php
class ConexionMultiBD {

    var $db;
    function ConexionMultiBD() {                
        /* pruebas */
        $MYSQL_HOST = "50.22.25.101";
        $MYSQL_DB = "genesis_multibd";
        $MYSQL_LOGIN = "genesis_operac";
        $MYSQL_PASS = "3st03sg3n3s1s2014";
        
        /*Produccion*/
        /*$MYSQL_HOST = "50.22.25.101"; //Nuevo servidor de base de datos
        $MYSQL_DB = "genesis_multibase";
        $MYSQL_LOGIN = "genesis";
        $MYSQL_PASS = "R0m3r1t0";*/

        $this->db = mysql_connect($MYSQL_HOST, $MYSQL_LOGIN, $MYSQL_PASS);
        @mysql_query("SET NAMES 'utf8'", $this->db);
        if (!$this->db) {
            echo('Error: Imposible conectar a DB ' . mysql_error());
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
        $resultado = mysql_query($query, $this->db);
        if (!$resultado) {
            $resultado = mysql_error();
        }
        return $resultado;
    }
}

?>

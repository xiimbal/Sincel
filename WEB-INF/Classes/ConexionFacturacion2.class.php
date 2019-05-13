<?php

class ConexionFacturacion2 {

    var $db;

    function ConexionFacturacion2() {
        
        /*$MYSQL_HOST = "localhost";
        $MYSQL_DB = "sucursalfacturacion";
        $MYSQL_LOGIN = "root";
        $MYSQL_PASS = "";*/

        /* techra */
        /*$MYSQL_HOST = "techra.com.mx";
        $MYSQL_DB = "romeos_gfacturacion";
        $MYSQL_LOGIN = "romeos_gfac33";
        $MYSQL_PASS = "T12ur78y5";*/
        
        /*Produccion final*/
        /*$MYSQL_HOST = "198.38.93.133:44999";
        $MYSQL_DB = "sucursalfacturacion";
        $MYSQL_LOGIN = "Ky0cera";
        $MYSQL_PASS = "Ky0cErA20131!";*/
		
        /*Produccion VPS*/
        $MYSQL_HOST = "50.31.138.92";
        $MYSQL_DB = "genesis_facturacion";
        $MYSQL_LOGIN = "genesis_prod";
        $MYSQL_PASS = "3st03sg3n3$1$2014";

        $this->db = mysql_connect($MYSQL_HOST, $MYSQL_LOGIN, $MYSQL_PASS);
        @mysql_query("SET NAMES 'utf8'", $this->db);
        if (!$this->db) {
            echo('Unable to connect to db' . mysql_error());
            exit;
        }
        mysql_select_db($MYSQL_DB);
    }

    function DesconectarF() {
        if (gettype($this->db) == "resource") {
            mysql_close($this->db);
        }
    }

    function EjecutarF($query) {
        $resultado = mysql_query($query, $this->db);
        if (!$resultado) {
            $resultado = mysql_error();
        }
        return $resultado;
    }

}

?>
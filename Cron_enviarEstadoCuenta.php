<?php

ini_set("memory_limit","1024M");
set_time_limit (0);

include_once("WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

include_once('WEB-INF/Classes/EstadoCuenta.class.php');

$con = new ConexionMultiBD();         
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();
while($rs_multi = mysql_fetch_array($result_bases)){
    echo "<br/><br/>Procesando empresa ".$rs_multi['nombre_empresa']."<br/>";
    $empresa = $rs_multi['id_empresa'];
    $estado = new EstadoCuenta();
    $estado->setEmpresa($empresa);
    $estado->generarEstadoCuenta(array(), "", "", false, true);            
}
<?php

include_once("WEB-INF/Classes/ConexionMultiBD.class.php");
include_once("WEB-INF/Classes/Catalogo.class.php");

$con = new ConexionMultiBD();
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();

$consultas = "ALTER TABLE `c_prioridadticket` ADD Activo INT(1) DEFAULT 1;";

while ($rs_multi = mysql_fetch_array($result_bases)) {
    echo "<br/><br/>Procesando empresa " . $rs_multi['nombre_empresa'] . "<br/>";
    $empresa = $rs_multi['id_empresa'];
    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);
    $catalogo->multiQuery($consultas);
}
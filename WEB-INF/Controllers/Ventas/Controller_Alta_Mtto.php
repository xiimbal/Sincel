<?php
session_start();

include_once("../../Classes/FechasMantenimiento.class.php");
include_once("../../Classes/Catalogo.class.php");
if (isset($_POST['form'])) {
    $conf=0;
    if($_POST['conf']){
        $conf=$_POST['conf'];
    }
    $parametros = "";
    parse_str($_POST['form'], $parametros);
    $mantenimiento = new FechasMantenimiento();
    $mantenimiento->setConf($conf);
    $mantenimiento->setPeriocidad($parametros['periocidad']);
    $mantenimiento->setDias($parametros['numero']);
    $mantenimiento->setIdEstado($parametros['area']);
    $mantenimiento->setUserCreacion($_SESSION['user']);
    $catalogo = new Catalogo();
    $result = $catalogo->obtenerLista("SELECT
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS Cliente, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS ClaveCentroCosto, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
        cinv.NoSerie AS NoSerie,
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ID, 
	c_equipo.Modelo AS Modelo,
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.EjecutivoCuenta FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.EjecutivoCuenta END) AS Usuario,
	cinv.NoParteEquipo AS NoParte,
        DATE(c_contrato.FechaTermino) AS FechaFin
FROM `c_inventarioequipo` AS cinv
RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
INNER JOIN c_anexotecnico ON c_anexotecnico.ClaveAnexoTecnico=ka.ClaveAnexoTecnico
INNER JOIN c_contrato ON c_contrato.NoContrato=c_anexotecnico.NoContrato
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
WHERE (cc.ClaveCentroCosto='" . $parametros['no_serie']  . "' OR ks.ClaveCentroCosto='" . $parametros['no_serie']  . "') AND !ISNULL(NoSerie)");
    echo "<script type=\"text/javascript\" language=\"javascript\" src=\"resources/js/paginas/tabla_mantenimiento.js\"></script>";
    echo "<table id=\"tablainfo\">";
    echo "<thead><tr><th width=\"2%\" align=\"center\" scope=\"col\">No Serie</th>
        <th width=\"2%\" align=\"center\" scope=\"col\">Fecha de inicio</th>
        <th width=\"2%\" align=\"center\" scope=\"col\">Fin de contrato</th>
        <th width=\"2%\" align=\"center\" scope=\"col\">Mantenimientos planeados</th>
        </tr></thead><tbody>";
    while ($rs = mysql_fetch_array($result)) {
        $mantenimiento->setNserie($rs['NoSerie']);
        $mantenimiento->setFechaFin($rs['FechaFin']);
        $mantenimiento->setCentroCosto($rs['ClaveCentroCosto']);
        $mantenimiento->setFechaInicio($parametros['fechaMtto']);
        $mantenimiento->crearFechas();
        echo "<tr>";
        foreach ($mantenimiento->getTabla() as $value) {
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">".$value."</td>";
        }
        echo "</tr>";
    }
    echo "</tbody></table><br/>";
    
    if($conf==0){
        echo "<input type=\"submit\" class=\"boton\" onclick=\"enviarmtto();\" value=\"Confirmar\"/>";
        echo "<input type=\"button\" class=\"boton\" onclick=\"cancelarmtto();\" value=\"Cancelar\"/>";
    }
}
?>

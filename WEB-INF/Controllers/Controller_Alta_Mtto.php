<?php
session_start();

include_once("../../Classes/FechasMantenimiento.class.php");
include_once("../../Classes/Catalogo.class.php");
if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
    $mantenimiento = new FechasMantenimiento();
    $mantenimiento->setFechaInicio($parametros['fechaMtto']);
    $mantenimiento->setPeriocidad($parametros['periocidad']);
    $mantenimiento->setDias($parametros['numero']);
    $mantenimiento->setUserCreacion($_SESSION['user']);
    $catalogo = new Catalogo();
    echo"SELECT 
 c_centrocosto.ClaveCentroCosto AS ClaveCentroCosto,
	DATE(c_contrato.FechaTermino) AS FechaFin,
	c_inventarioequipo.NoSerie AS NoSerie
FROM
	 c_centrocosto 
INNER JOIN k_anexoclientecc ON c_centrocosto.ClaveCentroCosto= k_anexoclientecc.CveEspClienteCC
INNER JOIN c_anexotecnico ON c_anexotecnico.ClaveAnexoTecnico=k_anexoclientecc.ClaveAnexoTecnico
INNER JOIN c_contrato ON c_contrato.NoContrato=c_anexotecnico.NoContrato
LEFT JOIN c_inventarioequipo ON k_anexoclientecc.IdAnexoClienteCC=c_inventarioequipo.IdAnexoClienteCC
WHERE c_centrocosto.ClaveCentroCosto='" . $parametros['no_serie'] . "'";
    $result = $catalogo->obtenerLista("SELECT 
 c_centrocosto.ClaveCentroCosto AS ClaveCentroCosto,
	DATE(c_contrato.FechaTermino) AS FechaFin,
	c_inventarioequipo.NoSerie AS NoSerie
FROM
	 c_centrocosto 
INNER JOIN k_anexoclientecc ON c_centrocosto.ClaveCentroCosto= k_anexoclientecc.CveEspClienteCC
INNER JOIN c_anexotecnico ON c_anexotecnico.ClaveAnexoTecnico=k_anexoclientecc.ClaveAnexoTecnico
INNER JOIN c_contrato ON c_contrato.NoContrato=c_anexotecnico.NoContrato
LEFT JOIN c_inventarioequipo ON k_anexoclientecc.IdAnexoClienteCC=c_inventarioequipo.IdAnexoClienteCC
WHERE c_centrocosto.ClaveCentroCosto='" . $parametros['no_serie'] . "'");
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
    echo "</tbody></table>";
}
?>
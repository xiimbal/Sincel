<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$id = "";

if (isset($_POST['id'])) {
    $id = $_POST['id'];
}
switch ($_POST['tipo']) {
    case 1:
        //echo "<option value=\"\">Selecciona el contrato</option>";
        $query = $catalogo->obtenerLista("SELECT * FROM c_contrato WHERE ClaveCliente='$id' AND Activo = 1;");
        $i = 1;
        while ($rs = mysql_fetch_array($query)) {
            $s = "";
            if ($i == 1) {
                $i++;
                $s = "selected";
            }
            echo "<option value='" . $rs['NoContrato'] . "' $s>" . $rs['NoContrato'] . " </option>";
        }
        break;
    case 2:
        echo "<option value=\"\">Todas las zonas</option>";
        $query = $catalogo->obtenerLista("SELECT DISTINCT(zona.ClaveZona) AS ClaveZona,zona.Descripcion FROM c_contrato AS con
            INNER JOIN c_cliente AS cli ON cli.ClaveCliente=con.ClaveCliente
            INNER JOIN c_centrocosto AS cen ON cen.ClaveCliente=cli.ClaveCliente
            INNER JOIN c_zona AS zona ON zona.ClaveZona=cen.ClaveZona
            WHERE con.NoContrato='$id';");
        while ($rs = mysql_fetch_array($query)) {
            echo "<option value='" . $rs['ClaveZona'] . "' >" . $rs['ClaveZona'] . " </option>";
        }
        break;
    case 3:
        echo "<option value=\"\">Selecciona el nivel de facturación</option>";
        $query = $catalogo->obtenerLista("SELECT *  FROM c_nivel_facturacion");
        while ($rs = mysql_fetch_array($query)) {
            echo "<option value='" . $rs['Id_nivel_facturacion'] . "' >" . $rs['Nombre'] . " </option>";
        }
        break;
    case 4:
        echo "<option value=\"\">Todos los centros de costo</option>";
        $where = "WHERE ";
        if (isset($_POST['zona']) && $_POST['zona'] != "") {
            $where .= " zona.ClaveZona='" . $_POST['zona'] . "';";
        } else {
            $where .= " cli.ClaveCliente='" . $_POST['cliente'] . "'";
        }
        $query = $catalogo->obtenerLista("SELECT DISTINCT(cc.nombre) AS Nombre,cc.id_cc AS ClaveCentroCosto FROM c_contrato AS con
            INNER JOIN c_cliente AS cli ON cli.ClaveCliente=con.ClaveCliente
            INNER JOIN c_cen_costo AS cc ON cc.ClaveCliente=cli.ClaveCliente
            INNER JOIN c_zona AS zona ON zona.ClaveZona=cen.ClaveZona $where");
        while ($rs = mysql_fetch_array($query)) {
            echo "<option value='" . $rs['ClaveCentroCosto'] . "' >" . $rs['Nombre'] . " </option>";
        }
        break;
    case 5:
        echo "<option value=\"\">Todas las localidades</option>";
        $where = "WHERE ";
        if (isset($_POST['centro']) && $_POST['centro'] != "") {
            $where .= " cc.id_cc='" . $_POST['centro'] . "';";
        } else {
            $where .= " cli.ClaveCliente='" . $_POST['cliente'] . "'";
        }
        $query = $catalogo->obtenerLista("SELECT DISTINCT
	(cen.Nombre) AS Nombre,
	cen.ClaveCentroCosto
FROM
	c_contrato AS con
INNER JOIN c_cliente AS cli ON cli.ClaveCliente = con.ClaveCliente
LEFT JOIN c_cen_costo AS cc ON cc.ClaveCliente=cli.ClaveCliente
LEFT JOIN c_centrocosto AS cen ON cen.ClaveCliente = cli.ClaveCliente
LEFT JOIN c_zona AS zona ON zona.ClaveZona = cen.ClaveZona $where");
        while ($rs = mysql_fetch_array($query)) {
            echo "<option value='" . $rs['ClaveCentroCosto'] . "' >" . $rs['Nombre'] . " </option>";
        }
        break;
    case 6:
        echo "<option value=\"\">Todos los anexos</option>";
        $query = $catalogo->obtenerLista("SELECT DISTINCT
	(ane.ClaveAnexoTecnico) AS Nombre,
	ane.ClaveAnexoTecnico AS ClaveCentroCosto
FROM
	c_anexotecnico AS ane
WHERE ane.NoContrato='".$_POST['contrato'] ."'");
        while ($rs = mysql_fetch_array($query)) {
            echo "<option value='" . $rs['ClaveCentroCosto'] . "' >" . $rs['Nombre'] . " </option>";
        }
        break;
}
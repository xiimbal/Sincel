<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes_arbol.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$catalogo = new Catalogo();
$id_noserie = "";
if (isset($_POST['id'])) {
    $id_noserie = $_POST['id'];
}
$query = $catalogo->obtenerLista("SELECT 
        c_inventarioequipo.NoSerie AS NoSerie,
	c_cliente.NombreRazonSocial AS NombreCliente,
	c_centrocosto.Nombre AS CentroCostoNombre,
        c_inventarioequipo.NoParteEquipo AS NoParte
FROM
	c_usuario
INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
INNER JOIN c_centrocosto ON c_centrocosto.ClaveCliente = c_cliente.ClaveCliente
INNER JOIN k_anexoclientecc ON c_centrocosto.ClaveCentroCosto= k_anexoclientecc.CveEspClienteCC
INNER JOIN c_inventarioequipo ON k_anexoclientecc.IdAnexoClienteCC=c_inventarioequipo.IdAnexoClienteCC
WHERE c_inventarioequipo.NoSerie='" . $id_noserie . "'
ORDER BY Fecha DESC");
$rs = mysql_fetch_array($query);
$query4 = $catalogo->obtenerLista("SELECT ts.Nombre AS servicio, ts.IdTipoServicio AS ID FROM `k_equipocaracteristicaformatoservicio` AS ke
INNER JOIN c_tiposervicio AS ts ON ke.NoParte = '" . $rs['NoParte'] . "' AND ts.IdTipoServicio = ke.IdTipoServicio;");
$impresora = true;
$query2 = $catalogo->obtenerLista("SELECT c_caracteristicaequipo.IdCaracteristicaEquipo AS ID FROM c_inventarioequipo
INNER JOIN k_equipocaracteristicaformatoservicio ON k_equipocaracteristicaformatoservicio.NoParte=c_inventarioequipo.NoParteEquipo
INNER JOIN c_caracteristicaequipo ON c_caracteristicaequipo.IdCaracteristicaEquipo=k_equipocaracteristicaformatoservicio.IdCaracteristicaEquipo
WHERE c_inventarioequipo.NoSerie='" . $id_noserie . "';");
while ($rs = mysql_fetch_array($query2)) {
    if ($rs['ID'] != 1) {
        $impresora = false;
    }
}
if ($impresora) {
    $cabeceras = array("Cliente-Localidad", "No Serie", "Fecha", "Contador B/N", "Nivel toner negro", "Tipo de Lectura");
} else {
    $cabeceras = array("Cliente-Localidad", "No Serie", "Fecha", "Contador B/N", "Contador B/N ML", "Nivel toner negro", "Tipo de Lectura");
}
$val = false;
while ($rs = mysql_fetch_array($query4)) {
    if ($rs['ID'] == 1) {
        $val = true;
        if ($impresora) {
            $cabeceras = array("Cliente-Localidad", "No Serie", "Fecha", "Contador B/N", "Contador Color", "Nivel toner negro", "Nivel toner cyan", "Nivel toner magenta", "Nivel toner amarillo", "Tipo de Lectura");
        } else {
            $cabeceras = array("Cliente-Localidad", "No Serie", "Fecha", "Contador B/N", "Contador Color", "Contador B/N ML", "Contador Color ML",
                "Nivel toner negro", "Nivel toner cyan", "Nivel toner magenta", "Nivel toner amarillo", "Tipo de Lectura");
        }
    }
}
$alta = "ventas/NuevaLectura.php";
?>
<div id="lecturas"></div>
<table id="tinfo">
    <thead>
        <tr>
            <?php
            foreach ($cabeceras as $a) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $a . "</th>";
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $query = $catalogo->obtenerLista("SELECT 
        c_inventarioequipo.NoSerie AS NoSerie,
	c_cliente.NombreRazonSocial AS NombreCliente,
	c_centrocosto.Nombre AS CentroCostoNombre,
        DATE(c_lectura.Fecha) AS Fecha,
        c_lectura.ContadorBNPaginas AS ContadorBN,
        c_lectura.ContadorColorPaginas AS ContadorCL,
        c_lectura.ContadorBNML AS ContadorBNML,
        c_lectura.ContadorColorML AS ContadorCLML,
        c_lectura.NivelTonNegro AS NivelTonNegro,
        c_lectura.NivelTonCian AS NivelTonCian,
        c_lectura.NivelTonMagenta AS NivelTonMagenta,
        c_lectura.NivelTonAmarillo AS NivelTonAmarillo,
        c_inventarioequipo.NoParteEquipo AS NoParte
FROM
	c_usuario
INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
INNER JOIN c_centrocosto ON c_centrocosto.ClaveCliente = c_cliente.ClaveCliente
INNER JOIN k_anexoclientecc ON c_centrocosto.ClaveCentroCosto= k_anexoclientecc.CveEspClienteCC
INNER JOIN c_inventarioequipo ON k_anexoclientecc.IdAnexoClienteCC=c_inventarioequipo.IdAnexoClienteCC
INNER JOIN c_lectura ON c_lectura.NoSerie=c_inventarioequipo.NoSerie
WHERE c_inventarioequipo.NoSerie='" . $id_noserie . "'
ORDER BY Fecha DESC");
        $contador = 0;
        if ($val) {
            while ($rs = mysql_fetch_array($query)) {
                echo "<tr>";
                echo "<td align='center' scope='row'>" . $rs['NombreCliente'] . "-" . $rs['CentroCostoNombre'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['NoSerie'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['Fecha'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['ContadorBN'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['ContadorCL'] . "</td>";
                if (!$impresora) {
                    echo "<td align='center' scope='row'>" . $rs['ContadorBNML'] . "</td>";
                    echo "<td align='center' scope='row'>" . $rs['ContadorCLML'] . "</td>";
                }
                echo "<td align='center' scope='row'>" . $rs['NivelTonNegro'];
                if ($rs['NivelTonNegro'] != "") {
                    echo "%";
                }
                echo "</td>";
                echo "<td align='center' scope='row'>" . $rs['NivelTonCian'];
                if ($rs['NivelTonCian'] != "") {
                    echo "%";
                }
                echo "</td>";
                echo "<td align='center' scope='row'>" . $rs['NivelTonMagenta'];
                if ($rs['NivelTonMagenta'] != "") {
                    echo "%";
                }
                echo "</td>";
                echo "<td align='center' scope='row'>" . $rs['NivelTonAmarillo'];
                if ($rs['NivelTonAmarillo'] != "") {
                    echo "%";
                }
                echo "</td>";
                echo "<td align='center' scope='row'>Normal</td>";
                echo "</tr>";
                $contador++;
            }
        } else {
            while ($rs = mysql_fetch_array($query)) {
                echo "<tr>";
                echo "<td align='center' scope='row'>" . $rs['NombreCliente'] . "-" . $rs['CentroCostoNombre'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['NoSerie'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['Fecha'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['ContadorBN'] . "</td>";
                if (!$impresora) {
                    echo "<td align='center' scope='row'>" . $rs['ContadorBNML'] . "</td>";
                }
                echo "<td align='center' scope='row'>" . $rs['NivelTonNegro'];
                if ($rs['NivelTonNegro'] != "") {
                    echo "%";
                }
                echo "</td>";
                echo "<td align='center' scope='row'>Normal</td>";
                echo "</tr>";
                $contador++;
            }
        }
        $query = $catalogo->obtenerLista("SELECT 
        c_inventarioequipo.NoSerie AS NoSerie,
	c_cliente.NombreRazonSocial AS NombreCliente,
	c_centrocosto.Nombre AS CentroCostoNombre,
        DATE(c_lecturasticket.Fecha) AS Fecha,
        c_lecturasticket.ContadorBN AS ContadorBN,
        c_lecturasticket.ContadorCL AS ContadorCL,
        c_lecturasticket.NivelTonNegro AS NivelTonNegro,
        c_lecturasticket.NivelTonCian AS NivelTonCian,
        c_lecturasticket.NivelTonMagenta AS NivelTonMagenta,
        c_lecturasticket.NivelTonAmarillo AS NivelTonAmarillo
FROM
	c_usuario
INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
INNER JOIN c_centrocosto ON c_centrocosto.ClaveCliente = c_cliente.ClaveCliente
INNER JOIN k_anexoclientecc ON c_centrocosto.ClaveCentroCosto= k_anexoclientecc.CveEspClienteCC
INNER JOIN c_inventarioequipo ON k_anexoclientecc.IdAnexoClienteCC=c_inventarioequipo.IdAnexoClienteCC
INNER JOIN c_lecturasticket ON c_lecturasticket.ClvEsp_Equipo=c_inventarioequipo.NoSerie
WHERE c_inventarioequipo.NoSerie='" . $id_noserie . "'
ORDER BY Fecha DESC");
        if ($val) {
            while ($rs = mysql_fetch_array($query)) {
                echo "<tr>";
                echo "<td align='center' scope='row'>" . $rs['NombreCliente'] . "-" . $rs['CentroCostoNombre'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['NoSerie'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['Fecha'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['ContadorBN'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['ContadorCL'] . "</td>";
                if (!$impresora) {
                    echo "<td align='center' scope='row'></td>";
                    echo "<td align='center' scope='row'></td>";
                }
                echo "<td align='center' scope='row'>" . $rs['NivelTonNegro'];
                if ($rs['NivelTonNegro'] != "") {
                    echo "%";
                }
                echo "</td>";
                echo "<td align='center' scope='row'>" . $rs['NivelTonCian'];
                if ($rs['NivelTonCian'] != "") {
                    echo "%";
                }
                echo "</td>";
                echo "<td align='center' scope='row'>" . $rs['NivelTonMagenta'];
                if ($rs['NivelTonMagenta'] != "") {
                    echo "%";
                }
                echo "</td>";
                echo "<td align='center' scope='row'>" . $rs['NivelTonAmarillo'];
                if ($rs['NivelTonAmarillo'] != "") {
                    echo "%";
                }
                echo "</td>";
                echo "<td align='center' scope='row'>Ticket</td>";
                echo "</tr>";
                $contador++;
            }
        } else {
            while ($rs = mysql_fetch_array($query)) {
                echo "<tr>";
                echo "<td align='center' scope='row'>" . $rs['NombreCliente'] . "-" . $rs['CentroCostoNombre'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['NoSerie'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['Fecha'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['ContadorBN'] . "</td>";
                if (!$impresora) {
                    echo "<td align='center' scope='row'></td>";
                }
                echo "<td align='center' scope='row'>" . $rs['NivelTonNegro'];
                if ($rs['NivelTonNegro'] != "") {
                    echo "%";
                }
                echo "</td>";
                echo "<td align='center' scope='row'>Ticket</td>";
                echo "</tr>";
                $contador++;
            }
        }


        if ($contador == 0) {
            if ($val) {
                ?><tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <?php if (!$impresora) echo "<td></td>"; ?>
                    <td>No hay lecturas anteriores</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php
            } else {
                ?><tr>
                    <td></td>
                    <?php if (!$impresora) echo "<td></td>"; ?>
                    <td></td>
                    <td>No hay lecturas anteriores</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr><?php
            }
        }
        ?>
    </tbody>
</table>
<?php if ($permisos_grid->getAlta()) { ?>
    <script>
        $("#lecturas").load("ventas/NuevaLectura.php", {id: '<?php echo $id_noserie ?>', tipo: '<?php
    if ($val) {
        echo '1';
    } else {
        echo '0';
    }
    ?>'});
    </script>
<?php } ?>
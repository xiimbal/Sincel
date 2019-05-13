<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$localidad = "";
$llamadas = "";
if (isset($_POST['id'])) {
    $localidad = $_POST['id'];
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/NuevasLocalidades.js"></script>
<style type="text/css">
    .tamanoinput {width: 95px;}
</style>
<script>
    $(document).ready(function() {
        $('.boton').button().css('margin-top', '20px');
    });
</script>
<form id="lecturas">
    <?php
    $queryprin = $catalogo->obtenerLista("SELECT
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS Cliente, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS Localidad, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
        cinv.NoSerie AS NoSerie,
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ID, 
	c_equipo.Modelo AS Modelo,
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.EjecutivoCuenta FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.EjecutivoCuenta END) AS Usuario,
	cinv.NoParteEquipo AS NoParte
FROM `c_inventarioequipo` AS cinv
RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
WHERE ((ka.CveEspClienteCC = '" . $localidad . "' AND ISNULL(cinv.IdKServiciogimgfa)) OR (!ISNULL(cinv.IdKServiciogimgfa) && ks.ClaveCentroCosto = '" . $localidad . "'))
AND !ISNULL(NoSerie)
ORDER BY NoSerie DESC");
    $contador = 1;
    if (mysql_num_rows($queryprin) != 0) {
        $rsp = mysql_fetch_array($queryprin);
        mysql_data_seek($queryprin, 0);
        echo "<h3>Cliente:" . $rsp['Cliente'] . "<br/>Localidad:" . $rsp['Localidad'] . "<br/>";
        while ($rsp = mysql_fetch_array($queryprin)) {
            echo "No Serie:" . $rsp['NoSerie'] . "&nbsp;&nbsp;Modelo:" . $rsp['Modelo'] . " </h3><br/>";
            $query2 = $catalogo->obtenerLista("SELECT ts.Nombre AS servicio, ts.IdTipoServicio AS ID FROM `k_equipocaracteristicaformatoservicio` AS ke
INNER JOIN c_tiposervicio AS ts ON ke.NoParte = '" . $rsp['NoParte'] . "' AND ts.IdTipoServicio = ke.IdTipoServicio;");
            $val = false;
            while ($rs = mysql_fetch_array($query2)) {
                if ($rs['ID'] == 1) {
                    $val = true;
                }
            }
            $query = $catalogo->obtenerLista("SELECT 
c_inventarioequipo.NoSerie AS NoSerie,
c_cliente.NombreRazonSocial AS NombreCliente,
c_centrocosto.Nombre AS CentroCostoNombre,
(SELECT CASE WHEN l.Fecha > lt.Fecha THEN DATE(l.Fecha) ELSE DATE(lt.Fecha) END) AS Fecha,
(SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorBNPaginas ELSE lt.ContadorBN END) AS ContadorBN,
(SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorColorPaginas ELSE lt.ContadorCL END)AS ContadorCL,
(SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorBNML ELSE lt.ContadorBNA END) AS ContadorBNML,
(SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorColorML ELSE lt.ContadorCLA END) AS ContadorCLML,
(SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.UsuarioCreacion ELSE lt.UsuarioCreacion END) AS Usuario,
(SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.FechaCreacion ELSE lt.FechaCreacion END) AS FechaCreacion,
(SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.UsuarioUltimaModificacion ELSE lt.UsuarioUltimaModificacion END) AS UsuarioUltimaModificacion,
(SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonNegro ELSE lt.NivelTonNegro END) AS NivelTonNegro,
(SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonCian ELSE lt.NivelTonCian END) AS NivelTonCian,
(SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonMagenta ELSE lt.NivelTonMagenta END) AS NivelTonMagenta,
(SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonAmarillo ELSE lt.NivelTonAmarillo END) AS NivelTonAmarillo
FROM
c_usuario
INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
INNER JOIN c_centrocosto ON c_centrocosto.ClaveCliente = c_cliente.ClaveCliente
INNER JOIN k_anexoclientecc ON c_centrocosto.ClaveCentroCosto= k_anexoclientecc.CveEspClienteCC
LEFT JOIN c_inventarioequipo ON k_anexoclientecc.IdAnexoClienteCC=c_inventarioequipo.IdAnexoClienteCC
LEFT JOIN c_lectura AS l ON l.NoSerie = c_inventarioequipo.NoSerie AND l.Fecha = (SELECT MAX(Fecha) FROM c_lectura WHERE c_lectura.NoSerie = c_inventarioequipo.NoSerie)
LEFT JOIN c_lecturasticket AS lt ON lt.ClvEsp_Equipo = c_inventarioequipo.NoSerie AND lt.Fecha = (SELECT MAX(Fecha) FROM c_lecturasticket WHERE c_lecturasticket.ClvEsp_Equipo = c_inventarioequipo.NoSerie)
    WHERE c_inventarioequipo.NoSerie='" . $rsp['NoSerie'] . "'
    ORDER BY Fecha DESC");
            $impresora = true;
            $query2 = $catalogo->obtenerLista("SELECT c_caracteristicaequipo.IdCaracteristicaEquipo AS ID FROM c_inventarioequipo
INNER JOIN k_equipocaracteristicaformatoservicio ON k_equipocaracteristicaformatoservicio.NoParte=c_inventarioequipo.NoParteEquipo
INNER JOIN c_caracteristicaequipo ON c_caracteristicaequipo.IdCaracteristicaEquipo=k_equipocaracteristicaformatoservicio.IdCaracteristicaEquipo
WHERE c_inventarioequipo.NoSerie='" . $rsp['NoSerie'] . "';");
            while ($rs = mysql_fetch_array($query2)) {
                if ($rs['ID'] == 2) {
                    $impresora = false;
                }
            }
            if ($rs = mysql_fetch_array($query)) {
                ?>
                <table>
                    <tr>
                        <?php if ($val == 1) { ?>
                            <td>Fecha</td>
                            <td>Contador B/N</td>
                            <td>Contador Color</td>
                            <?php if (!$impresora) { ?>
                                <td>Contador B/N ML</td>
                                <td>Contador Color ML</td>
                            <?php } ?>
                            <td>Nivel Toner Negro</td>
                            <td>Nivel Toner Cyan</td>
                            <td>Nivel Toner Magenta</td>
                            <td>Nivel Toner Amarillo</td>
                        <?php } else { ?>
                            <td>Fecha</td>
                            <td>Contador B/N</td>
                            <?php if (!$impresora) { ?>
                                <td>Contador B/N ML</td>
                            <?php } ?>
                            <td>Nivel Toner Negro</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if ($val == 1) { ?>
                            <td>
                                <?php echo $rs['Fecha'] ?>
                            </td>
                            <td>
                                <?php echo $rs['ContadorBN'] ?>
                            </td>
                            <td>
                                <?php echo $rs['ContadorCL'] ?>
                            </td>
                            <?php if (!$impresora) { ?>
                                <td>
                                    <?php echo $rs['ContadorBNML'] ?>
                                </td>
                                <td>
                                    <?php echo $rs['ContadorCLML'] ?>
                                </td>
                            <?php } ?>
                            <td>       
                                <?php echo $rs['NivelTonNegro'] ?>
                            </td>
                            <td>
                                <?php echo $rs['NivelTonCian'] ?>
                            </td>
                            <td>
                                <?php echo $rs['NivelTonMagenta'] ?>
                            </td>
                            <td>
                                <?php echo $rs['NivelTonAmarillo'] ?>
                            </td>
                        <?php } else { ?>
                            <td>
                                <?php echo $rs['Fecha'] ?>
                            </td>
                            <td>
                                <?php echo $rs['ContadorBN'] ?>
                            </td>
                            <?php if (!$impresora) { ?>
                                <td>
                                    <?php echo $rs['ContadorBNML'] ?>
                                </td>
                            <?php } ?>
                            <td>       
                                <?php echo $rs['NivelTonNegro'] ?>
                            </td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if ($val == 1) { ?>
                            <td>
                                <input type="text" id="fecha<?php echo $contador ?>" name="fecha<?php echo $contador ?>" class="tamanoinput fecha"/>
                            </td>
                            <td>
                                <input type="text" id="contadorbn<?php echo $contador ?>" name="contadorbn<?php
                                echo $contador;
                                $llamadas.="contadorbn('contadorbn" . $contador . "','" . $rs['ContadorBN'] . "');";
                                ?>"  class="tamanoinput"/>
                            </td>
                            <td>
                                <input type="text" id="contadorcl<?php echo $contador ?>" name="contadorcl<?php
                                echo $contador;
                                $llamadas.="contadorcl('contadorcl" . $contador . "','" . $rs['ContadorCL'] . "');";
                                ?>" class="tamanoinput"/>
                            </td>
                            <?php if (!$impresora) { ?>
                                <td>
                                    <input type="text" id="contadorbnml<?php echo $contador ?>" name="contadorbnml<?php echo $contador ?>" class="tamanoinput"/>
                                </td>
                                <td>
                                    <input type="text" id="contadorclml<?php echo $contador ?>" name="contadorclml<?php echo $contador ?>" class="tamanoinput"/>
                                </td>
                            <?php } ?>
                            <td>
                                <input type="text" id="NivelTN<?php echo $contador ?>" name="NivelTN<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                            <td>
                                <input type="text" id="NivelTC<?php echo $contador ?>" name="NivelTC<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                            <td>
                                <input type="text" id="NivelTM<?php echo $contador ?>" name="NivelTM<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                            <td>
                                <input type="text" id="NivelTA<?php echo $contador ?>" name="NivelTA<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                        <?php } else { ?>
                            <td>
                                <input type="text" id="fecha<?php echo $contador ?>" name="fecha<?php echo $contador ?>" class="tamanoinput fecha"/>
                            </td>
                            <td>
                                <input type="text" id="contadorbn<?php echo $contador ?>" name="contadorbn<?php
                                echo $contador;
                                $llamadas.="contadorbn('contadorbn" . $contador . "','" . $rs['ContadorBN'] . "');";
                                ?>"  class="tamanoinput"/>
                            </td>
                            <?php if (!$impresora) { ?>
                                <td>
                                    <input type="text" id="contadorbnml<?php echo $contador ?>" name="contadorbnml<?php echo $contador ?>" class="tamanoinput"/>
                                </td>
                            <?php } ?>
                            <td>
                                <input type="text" id="NivelTN<?php echo $contador ?>" name="NivelTN<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                        <?php } ?>

                    </tr>
                </table>
                <?php
            } else {
                ?>
                <table>
                    <tr>
                        <?php if ($val == 1) { ?>
                            <td>Fecha</td>
                            <td>Contador B/N</td>
                            <td>Contador Color</td>
                            <?php if (!$impresora) { ?>
                                <td>Contador B/N ML</td>
                                <td>Contador Color ML</td>
                            <?php } ?>
                            <td>Nivel Toner Negro</td>
                            <td>Nivel Toner Cyan</td>
                            <td>Nivel Toner Magenta</td>
                            <td>Nivel Toner Amarillo</td>
                        <?php } else { ?>
                            <td>Fecha</td>
                            <td>Contador B/N</td>
                            <?php if (!$impresora) { ?>
                                <td>Contador B/N ML</td>
                            <?php } ?>
                            <td>Nivel Toner Negro</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if ($val == 1) { ?>
                            <td>
                                <input type="text" id="fecha<?php echo $contador ?>" name="fecha<?php echo $contador ?>" class="tamanoinput fecha"/>
                            </td>
                            <td>
                                <input type="text" id="contadorbn<?php echo $contador ?>" name="contadorbn<?php echo $contador ?>"  class="tamanoinput"/>
                            </td>
                            <td>
                                <input type="text" id="contadorcl<?php echo $contador ?>" name="contadorcl<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                            <?php if (!$impresora) { ?>
                                <td>
                                    <input type="text" id="contadorbnml<?php echo $contador ?>" name="contadorbnml<?php echo $contador ?>" class="tamanoinput"/>
                                </td>
                                <td>
                                    <input type="text" id="contadorclml<?php echo $contador ?>" name="contadorclml<?php echo $contador ?>" class="tamanoinput"/>
                                </td>
                            <?php } ?>
                            <td>
                                <input type="text" id="NivelTN<?php echo $contador ?>" name="NivelTN<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                            <td>
                                <input type="text" id="NivelTC<?php echo $contador ?>" name="NivelTC<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                            <td>
                                <input type="text" id="NivelTM<?php echo $contador ?>" name="NivelTM<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                            <td>
                                <input type="text" id="NivelTA<?php echo $contador ?>" name="NivelTA<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                        <?php } else { ?>
                            <td>
                                <input type="text" id="fecha<?php echo $contador ?>" name="fecha<?php echo $contador ?>" class="tamanoinput fecha"/>
                            </td>
                            <td>
                                <input type="text" id="contadorbn<?php echo $contador ?>" name="contadorbn<?php echo $contador ?>"  class="tamanoinput"/>
                            </td>
                            <?php if (!$impresora) { ?>
                                <td>
                                    <input type="text" id="contadorbnml<?php echo $contador ?>" name="contadorbnml<?php echo $contador ?>" class="tamanoinput"/>
                                </td>
                            <?php } ?>
                            <td>
                                <input type="text" id="NivelTN<?php echo $contador ?>" name="NivelTN<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                        <?php } ?>
                    </tr>
                </table>
                <?php
            }
            ?>
            <input type="hidden" id="nserie<?php echo $contador ?>" name="nserie<?php echo $contador ?>" value="<?php echo $rsp['NoSerie'] ?>"/>
            <?php
            $contador++;
            echo "<br/>";
        }
        ?>
        <input type="submit" id="aceptar" class="boton" value="Aceptar"/>
    </form>

    <script type="text/javascript" language="javascript">
        validarextra(<?php echo $contador ?>);
    <?php echo $llamadas; ?>
    </script>
<?php
}?>
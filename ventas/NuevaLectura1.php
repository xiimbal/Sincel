<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$nserie = "";
$llamadas = ""; //script llamando funciones para que se valide el minimo que debe ingresar
if (isset($_POST['id'])) {
    $nserie = $_POST['id'];
}
$val = 0;
if (isset($_POST['tipo'])) {
    $val = $_POST['tipo'];
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
WHERE c_inventarioequipo.NoSerie='" . $nserie . "'
ORDER BY Fecha DESC");
$impresora = true;
$query2 = $catalogo->obtenerLista("SELECT c_caracteristicaequipo.IdCaracteristicaEquipo AS ID FROM c_inventarioequipo
INNER JOIN k_equipocaracteristicaformatoservicio ON k_equipocaracteristicaformatoservicio.NoParte=c_inventarioequipo.NoParteEquipo
INNER JOIN c_caracteristicaequipo ON c_caracteristicaequipo.IdCaracteristicaEquipo=k_equipocaracteristicaformatoservicio.IdCaracteristicaEquipo
WHERE c_inventarioequipo.NoSerie='" . $nserie . "';");
while ($rs = mysql_fetch_array($query2)) {
    if ($rs['ID'] == 2) {
        $impresora = false;
    }
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/NuevaLectura.js"></script>
<script>
    $(document).ready(function() {
        $('.boton').button().css('margin-top', '20px');
    });
</script>
<style type="text/css">
    .tamanoinput {width: 95px;}
</style>
<?php
if ($rs = mysql_fetch_array($query)) {
    ?>
    <form id="formNuevaLectura">
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
                        <?php
                        echo $rs['ContadorBN'];
                        $llamadas.="contadorbn('contadorbn'," . $rs['ContadorBN'] . ");";
                        ?>
                    </td>
                    <td>
                        <?php
                        echo $rs['ContadorCL'];
                        $llamadas.="contadorcl('contadorcl'," . $rs['ContadorCL'] . ");";
                        ?>
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
                        <?php
                        echo $rs['ContadorBN'];
                        $llamadas.="contadorbn('contadorbn'," . $rs['ContadorBN'] . ");";
                        ?>
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
                        <input type="text" id="fecha" name="fecha" class="tamanoinput fecha"/>
                    </td>
                    <td>
                        <input type="text" id="contadorbn" name="contadorbn"  class="tamanoinput"/>
                    </td>
                    <td>
                        <input type="text" id="contadorcl" name="contadorcl" class="tamanoinput"/>
                    </td>
                    <?php if (!$impresora) { ?>
                        <td>
                            <input type="text" id="contadorbnml" name="contadorbnml" class="tamanoinput"/>
                        </td>
                        <td>
                            <input type="text" id="contadorclml" name="contadorclml" class="tamanoinput"/>
                        </td>
                    <?php } ?>
                    <td>
                        <input type="text" id="NivelTN" name="NivelTN" class="tamanoinput"/>
                    </td>
                    <td>
                        <input type="text" id="NivelTC" name="NivelTC" class="tamanoinput"/>
                    </td>
                    <td>
                        <input type="text" id="NivelTM" name="NivelTM" class="tamanoinput"/>
                    </td>
                    <td>
                        <input type="text" id="NivelTA" name="NivelTA" class="tamanoinput"/>
                    </td>
                <?php } else { ?>
                    <td>
                        <input type="text" id="fecha" name="fecha" class="tamanoinput fecha"/>
                    </td>
                    <td>
                        <input type="text" id="contadorbn" name="contadorbn"  class="tamanoinput"/>
                    </td>
                    <?php if (!$impresora) { ?>
                        <td>
                            <input type="text" id="contadorbnml" name="contadorbnml" class="tamanoinput"/>
                        </td>
                    <?php } ?>
                    <td>
                        <input type="text" id="NivelTN" name="NivelTN" class="tamanoinput"/>
                    </td>
                <?php } ?>

            </tr>
        </table>
        <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
        <input type="hidden" id="nserie" name="nserie" value="<?php echo $_POST['id'] ?>"/>
    </form>
    <?php
} else {
    ?>
    <form id="formNuevaLectura">
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
                        <input type="text" id="fecha" name="fecha" class="tamanoinput fecha"/>
                    </td>
                    <td>
                        <input type="text" id="contadorbn" name="contadorbn"  class="tamanoinput"/>
                    </td>
                    <td>
                        <input type="text" id="contadorcl" name="contadorcl" class="tamanoinput"/>
                    </td>
                    <?php if (!$impresora) { ?>
                        <td>
                            <input type="text" id="contadorbnml" name="contadorbnml" class="tamanoinput"/>
                        </td>
                        <td>
                            <input type="text" id="contadorclml" name="contadorclml" class="tamanoinput"/>
                        </td>
                    <?php } ?>
                    <td>
                        <input type="text" id="NivelTN" name="NivelTN" class="tamanoinput"/>
                    </td>
                    <td>
                        <input type="text" id="NivelTC" name="NivelTC" class="tamanoinput"/>
                    </td>
                    <td>
                        <input type="text" id="NivelTM" name="NivelTM" class="tamanoinput"/>
                    </td>
                    <td>
                        <input type="text" id="NivelTA" name="NivelTA" class="tamanoinput"/>
                    </td>
                <?php } else { ?>
                    <td>
                        <input type="text" id="fecha" name="fecha" class="tamanoinput fecha"/>
                    </td>
                    <td>
                        <input type="text" id="contadorbn" name="contadorbn"  class="tamanoinput"/>
                    </td>
                    <?php if (!$impresora) { ?>
                        <td>
                            <input type="text" id="contadorbnml" name="contadorbnml" class="tamanoinput"/>
                        </td>
                    <?php } ?>
                    <td>
                        <input type="text" id="NivelTN" name="NivelTN" class="tamanoinput"/>
                    </td>
                <?php } ?>
            </tr>
        </table>
        <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
        <input type="hidden" id="nserie" name="nserie" value="<?php echo $_POST['id'] ?>"/>
    </form>
<?php } ?>
<br/>

<script type="text/javascript" language="javascript">
<?php echo $llamadas; ?>
</script>
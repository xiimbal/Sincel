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

<form id="formNuevaLectura">
    <div class="bg-light p-4 rounded container-fluid">
        <?php if ($rs = mysql_fetch_array($query)): ?>
            <?php if ($val == 1): ?>
                <!-- Datos de la impresora -->
                <div class="form-row">
                    <div class="form-group col-12 col-md-4">
                        <label for="" class="m-0">Fecha
                            <span class="text-danger">
                                <?php echo $rs['Fecha'] ?>
                            </span>
                        </label>
                        <input type="text" id="fecha" name="fecha" class="form-control fecha"/>
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label for="" class="m-0">Contador B/N
                            <span class="text-danger">
                                <?php
                                    echo $rs['ContadorBN'];
                                    $llamadas.="contadorbn('contadorbn'," . $rs['ContadorBN'] . ");";
                                ?>
                            </span>
                        </label>
                        <input type="text" id="contadorbn" name="contadorbn"  class="form-control"/>
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label for="" class="m-0">Contador Color
                            <span class="text-danger">
                                <?php
                                    echo $rs['ContadorCL'];
                                    $llamadas.="contadorcl('contadorcl'," . $rs['ContadorCL'] . ");";
                                ?>
                            </span>
                        </label>
                        <input type="text" id="contadorcl" name="contadorcl" class="form-control"/>
                    </div>
                </div>
                <!-- Si no hay impresora -->
                <?php if (!$impresora): ?>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label for="" class="m-0">Contador B/N ML
                                <span class="text-danger">
                                    <?php echo $rs['ContadorBNML'] ?>
                                </span>
                            </label>
                            <input type="text" id="contadorbnml" name="contadorbnml" class="form-control"/>
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label for="" class="m-0">Contador Color ML
                                <span class="text-danger">
                                    <?php echo $rs['ContadorCLML'] ?>
                                </span>
                            </label>
                            <input type="text" id="contadorclml" name="contadorclml" class="form-control"/>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- Nivel de toner -->
                <div class="form-row">
                    <div class="form-group col-12 col-md-3">
                        <label for="" class="m-0">Nivel Toner Negro
                            <span class="text-danger">
                                <?php echo $rs['NivelTonNegro'] ?>
                            </span>
                        </label>
                        <input type="text" id="NivelTN" name="NivelTN" class="form-control"/>
                    </div>
                    <div class="form-group col-12 col-md-3">
                        <label for="" class="m-0">Nivel Toner Cyan
                            <span class="text-danger">
                                <?php echo $rs['NivelTonCian'] ?>
                            </span>
                        </label>
                        <input type="text" id="NivelTC" name="NivelTC" class="form-control"/>
                    </div>
                    <div class="form-group col-12 col-md-3">
                        <label for="" class="m-0">Nivel Toner Magenta
                            <span class="text-danger">
                                <?php echo $rs['NivelTonMagenta'] ?>
                            </span>
                        </label>
                        <input type="text" id="NivelTM" name="NivelTM" class="form-control"/>
                    </div>
                    <div class="form-group col-12 col-md-3">
                        <label for="" class="m-0">Nivel Toner Amarillo
                            <span class="text-danger">
                                <?php echo $rs['NivelTonAmarillo'] ?>
                            </span>
                        </label>
                        <input type="text" id="NivelTA" name="NivelTA" class="form-control"/>
                    </div>
                </div>
            <?php else: ?>
                <div class="form-row">
                    <div class="form-group col-md-6 col-12">
                        <label for="" class="m-0">Fecha
                            <span class="text-danger">
                                <?php echo $rs['Fecha'] ?>
                            </span>
                        </label>
                        <input type="text" id="fecha" name="fecha" class="form-control fecha"/>
                    </div>
                    <div class="form-group col-md-6 col-12">
                        <label for="" class="m-0">Contador B/N
                            <span class="text-danger">
                                <?php
                                    echo $rs['ContadorBN'];
                                    $llamadas.="contadorbn('contadorbn'," . $rs['ContadorBN'] . ");";
                                ?>
                            </span>
                        </label>
                        <input type="text" id="contadorbn" name="contadorbn"  class="form-control"/>
                    </div>
                </div>
                <div class="form-row">
                    <?php if (!$impresora): ?>
                        <div class="form-group col-md-6 col-12">
                            <label for="" class="m-0">Contador B/N ML
                                <span class="text-danger">
                                    <?php echo $rs['ContadorBNML'] ?>
                                </span>
                            </label>
                            <input type="text" id="contadorbnml" name="contadorbnml" class="form-control"/>
                        </div>
                    <?php endif; ?>
                    <div class="form-group col-md-6 col-12">
                        <label for="" class="m-0">Nivel Toner Negro
                            <span class="text-danger">
                                <?php echo $rs['NivelTonNegro'] ?>
                            </span>
                        </label>
                        <input type="text" id="NivelTN" name="NivelTN" class="form-control"/>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($val == 1): ?>
                <!-- Datos de la impresora -->
                <div class="form-row">
                    <div class="form-group col-12 col-md-4">
                        <label for="" class="m-0">Fecha</label>
                        <input type="text" id="fecha" name="fecha" class="form-control fecha"/>
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label for="" class="m-0">Contador B/N</label>
                        <input type="text" id="contadorbn" name="contadorbn"  class="form-control"/>
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label for="" class="m-0">Contador Color</label>
                        <input type="text" id="contadorcl" name="contadorcl" class="form-control"/>
                    </div>
                </div>
                <!-- Si no hay impresora -->
                <?php if (!$impresora): ?>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label for="" class="m-0">Contador B/N ML</label>
                            <input type="text" id="contadorbnml" name="contadorbnml" class="form-control"/>
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label for="" class="m-0">Contador Color ML</label>
                            <input type="text" id="contadorclml" name="contadorclml" class="form-control"/>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- Nivel de toner -->
                <div class="form-row">
                    <div class="form-group col-12 col-md-3">
                        <label for="" class="m-0">Nivel Toner Negro</label>
                        <input type="text" id="NivelTN" name="NivelTN" class="form-control"/>
                    </div>
                    <div class="form-group col-12 col-md-3">
                        <label for="" class="m-0">Nivel Toner Cyan</label>
                        <input type="text" id="NivelTC" name="NivelTC" class="form-control"/>
                    </div>
                    <div class="form-group col-12 col-md-3">
                        <label for="" class="m-0">Nivel Toner Magenta</label>
                        <input type="text" id="NivelTM" name="NivelTM" class="form-control"/>
                    </div>
                    <div class="form-group col-12 col-md-3">
                        <label for="" class="m-0">Nivel Toner Amarillo</label>
                        <input type="text" id="NivelTA" name="NivelTA" class="form-control"/>
                    </div>
                </div>
            <?php else: ?>
                <div class="form-row">
                    <div class="form-group col-md-6 col-12">
                        <label for="" class="m-0">Fecha</label>
                        <input type="text" id="fecha" name="fecha" class="form-control fecha"/>
                    </div>
                    <div class="form-group col-md-6 col-12">
                        <label for="" class="m-0">Contador B/N</label>
                        <input type="text" id="contadorbn" name="contadorbn"  class="form-control"/>
                    </div>
                </div>
                <div class="form-row">
                    <?php if (!$impresora): ?>
                        <div class="form-group col-md-6 col-12">
                            <label for="" class="m-0">Contador B/N ML</label>
                            <input type="text" id="contadorbnml" name="contadorbnml" class="form-control"/>
                        </div>
                    <?php endif; ?>
                    <div class="form-group col-md-6 col-12">
                        <label for="" class="m-0">Nivel Toner Negro</label>
                        <input type="text" id="NivelTN" name="NivelTN" class="form-control"/>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <input type="submit" id="aceptar" class="btn btn-secondary" name="aceptar" value="Guardar"/>
        <input type="hidden" id="nserie" name="nserie" value="<?php echo $_POST['id'] ?>"/>
    </div>     
</form>
<script type="text/javascript" language="javascript">
<?php echo $llamadas; ?>
</script>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/NuevaLectura.js"></script>
<script>
    $(document).ready(function() {
        $('.boton').button().css('margin-top', '20px');
    });
</script>
<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/AlmacenEquipo.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "almacen/lista_almacenEquipo.php";
$idAlmacen = "";
$noSerie = "";
$noParte = "";
$fechaHora = "";
$Ubicacion = "";
$read = "";
$tipo = "default";
$codigo = "";
$manual = "";
if (isset($_POST['typeInsert'])) {
    $tipo = $_POST['typeInsert'];
    $fechaHora = $_POST['fecha'];
    $noSerie = $_POST['serie'];
}

if ($tipo == "manual")
    $manual = "checked";
else if ($tipo == "default")
    $codigo = "checked";
//almacen filtro
$catalogo = new Catalogo();
$idUsuario = "";
$userAlmacen = "";
$almacenPredeterminado = "";
$almacen1 = $catalogo->obtenerLista("SELECT IdPuesto,IdUsuario,IdAlmacen  FROM c_usuario WHERE IdUsuario='" . $_SESSION['idUsuario'] . "'");
if ($rs = mysql_fetch_array($almacen1)) {
    $idUsuario = $rs['IdPuesto'];
    $userAlmacen = $rs['IdUsuario'];
    $almacenPredeterminado = $rs['IdAlmacen'];
}
//else
//    $consulta = "SELECT * FROM c_almacen a WHERE a.Activo=1 ORDER BY a.nombre_almacen ASC";
if ($idUsuario == '24') {
    $consulta = "SELECT *,us.IdAlmacen AS pred 
        FROM k_responsablealmacen ra,c_almacen a ,c_usuario us 
        WHERE ra.IdUsuario='" . $userAlmacen . "' AND a.Activo=1 AND ra.IdAlmacen=a.id_almacen AND ra.IdUsuario=us.IdUsuario AND a.TipoAlmacen = 1
        ORDER BY a.nombre_almacen ASC";
} else {
    $consulta = "SELECT * FROM c_almacen a WHERE a.Activo=1 ORDER BY a.nombre_almacen ASC";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_almacenEquipo.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
                $("#equipo").focus();
                // alert($("input[name='tipo']:checked").val());
            });
        </script>
        <script>
            $(function() {
                $('#fechaHora').datepicker({dateFormat: 'yy-mm-dd'});
            });
            if ($("#fechaHora").val() === "") {
                $("#fechaHora").val(getFechaF(new Date()));
            }
            function procesarTexto()
            {
                if ($("#equipo").val() == "" || $("#serie").val() == "") {
                    var texto = $("#equipo").val();
                    var equipo = texto.substring(0, 10);
                    var serie = texto.substring(10, 20);
                    $("#equipo").val(equipo);
                    $("#serie").val(serie);
                }
            }

            function getHoraF(d)
            {
                var hora = d.getHours();
                var min = d.getMinutes();
                var seg = d.getSeconds();
                var str_segundo = new String(seg);
                if (str_segundo.length == 1)
                    seg = "0" + seg;

                var str_minuto = new String(min);
                if (str_minuto.length == 1)
                    min = "0" + min;

                var str_hora = new String(hora);
                if (str_hora.length == 1)
                    hora = "0" + hora;
                return hora + ":" + min + ":" + seg;
            }
            function getFechaF(date) {
                var day = ('0' + date.getDate()).slice(-2).toString();
                var month = date.getMonth() + 1;
                var year = date.getFullYear();
                return year + '-' + month + '-' + day;
            }
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $obj = new AlmacenEquipo();
                $obj->getRegistroById($_POST['id']);
                $idAlmacen = $obj->getIdAlmacen();
                $noSerie = $obj->getNoSerie();
                $noParte = $obj->getNoParteEquipo();
                $fechaHora = $obj->getFechaIngreso();
                $Ubicacion = $obj->getUbicacion();
                $read = "readonly='readonly'";
            }
            ?>
            <style>
                .ui-multiselect{
                    width: 100%!important;
                }
            </style>
            <form id="formAlamcenEquipo" name="formAlamcenEquipo" action="/" method="POST">
                <div class="form-row">
                    <div class="form-group col-md-2 offset-md-8"><input <?php echo $codigo; ?> type="radio" id="tipo" name="tipo" value="codigoBarras gg" onclick="formaIsertarEquipo();"/>Código de barras</div>
                    <div class="form-group col-md-2"><input <?php echo $manual; ?> type="radio" id="tipo" name="tipo" value="manual" onclick="formaIsertarEquipo();"/>Manual</div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="equipo">No parte<span class="obligatorio"> *</span></label>
                        <?php if ($tipo == "default") { ?>
                            <input class="form-control" id="equipo" name="equipo" onblur='procesarTexto();' value="<?php echo $noParte; ?>" />
                         <?php } else if ($tipo == "manual") { ?>
                            <select  id="equipo" name="equipo" class="filtro">
                                    <option value="0">Seleccione una opción</option>
                                    <?php
                                    $obj0 = new Catalogo();
                                    $query0 = $obj0->getListaAlta('c_equipo', 'Modelo');
                                    while ($rs = mysql_fetch_array($query0)) {
                                        $s = "";
                                        if ($noParte != "" && $noParte == $rs['id_almacen'])
                                            $s = "selected";
                                        echo "<option value='" . $rs['NoParte'] . "' " . $s . ">" . $rs['Modelo'] . " - " . $rs['NoParte'] . "</option>";
                                    }
                                    ?>
                            </select>
                         <?php } ?>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="serie">No serie<span class="obligatorio"> *</span></label>
                        <input class="form-control" id="serie" name="serie" value="<?php echo $noSerie; ?>" <?php echo $read; ?> onkeyup="quitarblancos('serie');"/>    
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fechaHora">Fecha<span class="obligatorio"> *</span></label>
                        <input class="form-control" id="fechaHora" name="fechaHora" value="<?php echo $fechaHora; ?>"/>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="almacen">Almacén<span class="obligatorio"> *</span></label>
                        <select class="custom-select" id="almacen" name="almacen">
                                <?php
                                $obj1 = new Catalogo();
                                $query1 = $obj1->obtenerLista($consulta);
                                while ($rs = mysql_fetch_array($query1)) {
                                    $s = "";
                                    if ($almacenPredeterminado != "" && $almacenPredeterminado == $rs['id_almacen'] || $idAlmacen != "" && $idAlmacen == $rs['id_almacen'] || $rs['pred'] == $rs['id_almacen'])
                                        $s = "selected";
                                    echo "<option value=" . $rs['id_almacen'] . " " . $s . ">" . $rs['nombre_almacen'] . "</option>";
                                }
                                ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="ubicacion">Ubicación</label>
                        <input class="form-control" type="text" id="ubicacion" name="ubicacion" value="<?php echo $Ubicacion; ?>"/>
                    </div>
                    <div class="form-group col-md-3 p-4">
                        <input type="submit" class="btn btn-success btn-block" value="Guardar" />
                    </div>
                    <div class="form-group col-md-3 p-4">
                        <input type="submit" class="btn btn-danger btn-block" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                        return false;"/>
                    </div>
                    <div class="form-group col-md-3 p-4">
                        <input type="button" class="btn btn-warning btn-block" value="Limpiar" onclick="LimpiarContenido();
                        return false;"/>
                        <input type="hidden" id="id" name="id" value="<?php echo $noSerie ?>"/>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>

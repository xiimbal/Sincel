
<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Ubicacion.class.php");
$pagina_lista = "catalogos/lista_ubicacion.php";

$ubicacion = new Ubicacion();
$idUbicacion = "";
$descripcion = "";
$destino = "";
$calle = "";
$exterior = "";
$colonia = "";
$delegacion = "";
$cp = "";
$estado = "";
$latitud = "";
$longitud = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_ubicacion.js"></script> 
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_GET['id']) && $_GET['id'] != "") {

                $ubicacion->getRegistroById($_GET['id']);
                $idUbicacion = $ubicacion->getIdUbicacion();
                $descripcion = $ubicacion->getDescripcion();
                $calle = $ubicacion->getCalle();
                $exterior = $ubicacion->getExterior();
                $colonia = $ubicacion->getColonia();
                $delegacion = $ubicacion->getDelegacion();
                $cp = $ubicacion->getCp();
                $estado = $ubicacion->getEstado();
                $latitud = $ubicacion->getLatitud();
                $longitud = $ubicacion->getLongitud();
            }
            ?>
            <form id="formUbicacion" name="formUbicacion" action="/" method="POST">
                <fieldset>
                    <legend>Domicilio Ubicacion</legend> 
                    <table style="width:100%"> 
                        <tr>
                            <td>Descripción<span class='obligatorio'> *</span></td><td><input  style="width: 100%" type="text" id="txtDescripcion" name="txtDescripcion" value="<?php echo $descripcion ?>"></td>
                        </tr>
                        <tr>
                            <td>Calle</td><td><input type="text" id="txtCalle" name="txtCalle" value="<?php echo $calle ?>" ></td>
                            <td>No. Exterior</td><td><input type="text" id="txtExterior" name="txtExterior" value="<?php echo $exterior ?>" ></td>
                        </tr>
                        <tr>
                            <td>Colonia</td><td><input type="text" id="txtColonia" name="txtColonia" value="<?php echo $colonia ?>" ></td>
                            <td>Delegación</td><td><input type="text" id="txtDelegacion" name="txtDelegacion" value="<?php echo $delegacion ?>" ></td>
                        </tr>
                        <tr>
                            <td>C.P.</td><td><input type="text" id="txtcp" name="txtcp" value="<?php echo $cp ?>" ></td>
                             <td>Estado</td>
                            <td>
                                <select id="slcEstado" name="slcEstado" >
                                    <option value="0">Seleccione un estado</option>
                                    <?php
                                    $catalogo = new Catalogo();
                                    $queryEstado = $catalogo->getListaAlta("c_ciudades", "Ciudad");
                                    while ($rs = mysql_fetch_array($queryEstado)) {
                                        $s = "";
                                        if ($estado != "" && $estado == $rs['IdCiudad'])
                                            $s = "selected";
                                        echo "<option value='" . $rs['IdCiudad'] . "' $s>" . $rs['Ciudad'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Latitud</td><td><input type="number" id="Latitud" name="Latitud" value="<?php echo $latitud ?>" step="any" ></td>
                            <td>Longitud</td><td><input type="number" id="Longitud" name="Longitud" value="<?php echo $longitud ?>" step="any" ></td>
                        </tr>
                    </table>
<!--                    <table>
                        <tr>
                            <td rowspan="2"> 
                                <input align="center" type="button" value="Buscar Ubicación" class="boton" title="Buscar Domicilio según coordenadas" onclick="getLatLngText();" />                             
                            </td>
                            <td>
                                <div id="fotocargandoPI" style="width:100%; display: none; ">
                                    <img src="resources/img/loading.gif"/>                             
                                </div>
                            </td>
                        </tr>
                    </table>-->
                </fieldset>
                <input type="submit" name="submit" class="boton" value="Guardar" position="right"/>
                <?php
                echo "<input type=\"submit\" class=\"boton\" value=\"Cancelar\" onclick=\"cancelar(); return false;\"/>";
                echo "<input type='hidden' id='id' name='id' value='$idUbicacion'/> ";
                ?>
            </form>
        </div>
    </body>
</html>

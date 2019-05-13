<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
//include_once("../WEB-INF/Classes/TFSCliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Campania.class.php");
$pagina_lista = "catalogos/lista_campania.php";

$idCampania = "";
$cliente = "";
$auxcliente = "";
$localidad = "";
$read = "";
$descripcion = "";
$area = "";
$activo = "checked='checked'";

if (isset($_POST['claveCliente'])) {
    // $clienteLocalidad = $_POST['claveCliente'];
    $auxcliente = $_POST['claveCliente'];
    $cliente = $_POST['auxcliente'];
    $localidad = $_POST['auxlocalidad'];
    $descripcion = $_POST['descripcion1'];
    $area = $_POST['area1'];
    $idCampania = $_POST['idCampania'];
}
//echo $auxcliente;
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_campania.js"></script>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $obj = new Campania();
                $obj->getRegistroById($_POST['id']);
                $idCampania = $obj->getIdCampania();
                $cliente = $obj->getCliente();
                $localidad = $obj->getLocalidad();
                $descripcion = $obj->getDescripcion();
                $area = $obj->getArea();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
            }
            ?>
            <form id="formCampania" name="formCampania" action="/" method="POST">
                <table style="width: 95%;">
                    <tr>
                        <td style="width: 15%"><label for="cliente">Cliente</label><span class='obligatorio'> *</span></td>
                        <td>
                            <select class="filtro" id="cliente" name="cliente" style="width: 200px" onchange="verLocalidad('catalogos/alta_campania.php');">
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                                echo "<option value='0' >Selecciona un cliente</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($auxcliente == $rs['ClaveCliente'] || $cliente == $rs['ClaveCliente']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['ClaveCliente'] . " " . $s . ">" . $rs['NombreRazonSocial'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>


                        <td><label for="localidad">Localidad</label><span class='obligatorio'> *</span></td>
                        <td>
                            <select id="localidad" name="localidad" style="width: 200px" class="filtro">
                                <?php
                                if ($auxcliente != "")
                                    $clienteConsulta = $auxcliente;
                                else if ($cliente != "")
                                    $clienteConsulta = $cliente;
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT cc.ClaveCentroCosto,cc.ClaveCliente,cc.Nombre FROM c_centrocosto cc WHERE cc.ClaveCliente='" . $clienteConsulta . "' ORDER BY cc.Nombre ASC");
                                echo "<option value='0' >Selecciona una localidad</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s1 = "";
                                    if ($localidad != "" && $localidad == $rs['ClaveCentroCosto']) {
                                        $s1 = "selected";
                                    }
                                    echo "<option value=" . $rs['ClaveCentroCosto'] . " " . $s1 . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                            <br/>
                            <div id='mensajeError0'></div>
                        </td>
                    </tr>   
                    <tr>
                        <td><label for="descripcion">Descripci&oacute;n</label><span class='obligatorio'> *</span></td>
                        <td><input type="text" id="descripcion" name="descripcion" cols="60" value="<?php echo $descripcion; ?>"></td>
                        <td><label for="area">
                            Cuadrante
                            </label><span class='obligatorio'> *</span>
                        </td>
                        <td>
                            <select id="area" name="area" class="filtro">
                                <?php
                                /* Inicializamos la clase */
                                $query = $catalogo->obtenerLista("SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
                                    INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) ORDER BY Nombre;");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if (!empty($area) && $rs['IdEstado'] == $area) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?> 
                            </select>
                            <div id="error_area" style="font-size: 12px; color: red;"></div>
                        </td>
                        <td>
                            <input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo
                        </td>
                    </tr>

                </table>
                <br/><br/>
                <input type="submit" name="submit" class="boton" value="Guardar" />                
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                        return false;"/>                            
                       <?php
                       echo "<input type='hidden' id='tipo' name='tipo' value='2'/> ";
                       echo "<input type='hidden' id='idCliente' name='idCliente' value='" . $cliente . "'/> ";
                       echo "<input type='hidden' id='idLocalidad' name='idLocalidad' value='" . $localidad . "'/> ";
                       echo "<input type='hidden' id='descripcion1' name='descripcion1' value='" . $descripcion . "'/> ";
                       echo "<input type='hidden' id='area1' name='area1' value='" . $area . "'/> ";
                       echo "<input type='hidden' id='idCampania' name='idCampania' value='" . $idCampania . "'/> ";
                       ?>

            </form>
        </div>
    </body>
</html>
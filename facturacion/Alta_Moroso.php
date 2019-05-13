<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once('../WEB-INF/Classes/Cliente.class.php');
include_once('../WEB-INF/Classes/Catalogo.class.php');
$cliente = new Cliente();
$cliente->getRegistroById($_GET['id']);
$tipo = $cliente->getIdTipoMorosidad();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
        <script src="../resources/js/jquery/jquery-ui.min.js"></script>    
        <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
        <!-- multiselect -->
        <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
        <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
        <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">
        <!-- Tables -->
        <script type="text/javascript" language="javascript" src="../resources/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/media/js/TableTools.min.js"></script>
        <link href="../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/sicop.css" rel="stylesheet" type="text/css">  
        <script type="text/javascript" language="javascript" src="../resources/js/funciones.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/facturacion/AltaMoroso.js"></script>
    </head>
    <body>
        <div class="principal">
            <h2 class="titulos">
                <div id="titulo"></div>
            </h2>
            <div id="cargando" style="width:80%; margin-left: 50%; display: none; ">
                <img src="../resources/images/cargando.gif"/>                          
            </div>
            <div id="loading_text" style="width:80%; margin-top: 5px;  margin-left: 45%;"></div>
            <div id="mensajes" style="font-size: 12px;"></div>
            <div id="contenidos" style="position: relative; width: 100%; margin-left: 0px; display: block;">
                <form id="formmoroso">
                    <?php
                    $catalogo = new Catalogo();
                    if ($tipo == 2) {
                        ?>
                        <h4>Tipo de morosidad Localidad del cliente <?php echo $cliente->getNombreRazonSocial(); ?></h4>
                        <select name="morosos[]" class="filtro" multiple="multiple" id="morosos">
                            <?php
                            $query = "SELECT * FROM c_centrocosto AS c WHERE c.ClaveCliente='" . $_GET['id'] . "'";
                            $result = $catalogo->obtenerLista($query);
                            while ($rs = mysql_fetch_array($result)) {
                                $s = "";
                                if ($rs['Moroso'] == 1) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['ClaveCentroCosto'] . "' " . $s . ">" . $rs['Nombre'] . "</option>";
                            }
                            ?>
                        </select>
                        <?php
                    } else {
                        ?>
                        <h4>Tipo de morosidad Centro de costo del cliente <?php echo $cliente->getNombreRazonSocial(); ?></h4>
                        <select name="morosos[]" class="filtro" multiple="multiple" id="morosos">
                            <?php
                            $query = "SELECT * FROM c_cen_costo AS c WHERE c.ClaveCliente='" . $_GET['id'] . "'";
                            $result = $catalogo->obtenerLista($query);
                            while ($rs = mysql_fetch_array($result)) {
                                $s = "";
                                if ($rs['Moroso'] == 1) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['id_cc'] . "' " . $s . ">" . $rs['nombre'] . "</option>";
                            }
                            ?>
                        </select>
                        <?php
                    }
                    ?>
                    <?php
                    if (isset($_GET ['id']) && $_GET['id'] != "") {
                        echo "<input type=\"hidden\" id=\"clave\" name=\"clave\" value=\"" . $_GET['id'] . "\"/>";
                    }
                    ?>
                    <input type="submit" id="submit_equipo" name="submit_equipo" class="boton" value="Guardar" style="margin-left: 30%;"/>
                </form>
            </div>
        </div>

    </body>
</html>
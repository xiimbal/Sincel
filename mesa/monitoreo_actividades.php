<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
/*
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$catalogo = new Catalogo();*/
$fecha = date("Y-m-d");
$fechaHora = date("Y-m-d H:i:s");
?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <!-- JS -->
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
        <script src="../resources/js/jquery/jquery-ui.min.js"></script>
        <!-- Tables -->
        <script type="text/javascript" language="javascript" src="../resources/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/media/js/TableTools.min.js"></script>
        <link href="../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
        <!-- Para esta página en especifico-->
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/monitoreo_actividades.js"></script>
    </head>
    <body>
        <h2><b>Hora Monitor:</b> <?php echo $fechaHora?></h2><br>
        <div id="monitor">
            Cargando información...
        </div>
        <input type="hidden" id="fecha" value="<?php echo $fecha?>">
        <input type="hidden" id="fechaHora" value="<?php echo $fechaHora?>">
        <input type="hidden" id="monitoreoActividades" value="1">
    </body>
</html>
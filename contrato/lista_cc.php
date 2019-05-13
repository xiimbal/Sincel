<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes_arbol.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
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
    </head>
    <body>
        <input type="hidden" value="<?php echo $_GET['id'] ?>" name="clavecliente" id="clavecliente"/>
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

            </div>
    </body>
</html>
<?php if ($permisos_grid->getAlta()) { ?>
<script lang="text/javascript">
    cambiarContenidos("tabla_cc.php?id=<?php echo $_GET['id']; ?>", "Centro de costo");
</script>
<?php } ?>
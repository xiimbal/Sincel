<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Factura2.class.php");

$pagada = "";
if(isset($_GET['pagado']) && $_GET['pagado']=="true"){
    $pagada = "&pagado=true";
}

if(!isset($_GET['cxc'])){    
    $cxc = "";
}else{
    $cxc = "&cxc=true";
}

if(!isset($_GET['cfdi'])){    
    $cfdi = "";
}else{
    $cfdi = "&cfdi=true";
}

$factura = new Factura();
$factura->getRegistroById($_GET['factura']);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <!-- JS -->
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
        <script src="../resources/js/jquery/jquery-ui.min.js"></script>     
        <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery.maskedinput.min.js"></script>
        <script type="text/javascript" src="../resources/js/funciones.js"></script>        
        <!-- Tables -->
        <script type="text/javascript" language="javascript" src="../resources/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/media/js/TableTools.min.js"></script>
        <link href="../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/TableTools.css" rel="stylesheet" type="text/css">        
    </head>
    <body>
        <input type="hidden" value="<?php echo $_GET['factura'] ?>" name="idpp" id="idpp"/>
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
            <div id="contenidos_invisibles" style="position: relative; width: 100%; margin-left: 0px; display: none;;"></div>
        </div>
    </body>
</html>
<script lang="text/javascript">
    cambiarContenidos("lista_pago_parcial.php?RFC=<?php echo $_GET['RFC']; ?>&factura=<?php echo $_GET['factura']; ?><?php echo $pagada."".$cxc."".$cfdi; ?>", "Pago Parcial Factura <?php echo $factura->getSerie().$factura->getFolio(); ?>");
</script>
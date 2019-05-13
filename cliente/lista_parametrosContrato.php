<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");

if(isset($_GET['contrato']) && $_GET['contrato'] != ""){
    $catalogo = new Catalogo();
    $consulta = "SELECT * FROM k_contrato WHERE NoContrato = '".$_GET['contrato']."'";
    $result = $catalogo->obtenerLista($consulta);

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <!-- JS -->
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.9.1.js"></script>
        <script src="../resources/js/jquery/jquery-ui-1.10.3.custom.min.js"></script>        
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
        <!-- multiselect -->
        <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
        <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
        <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">

        <link href="../resources/css/sicop.css" rel="stylesheet" type="text/css">            
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/banco/MovimientoBancos.js"></script>        
    </head>
    <body>
        <br/>
        <p style="font-size:160%;">Contrato: <?php echo $_GET['contrato']; ?></p>
        <br/><br/>
        <table id="tlistaMovimientos" style="min-width: 100%;">
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    while($rs = mysql_fetch_array($result)){
                        echo "<tr>";
                        echo "<td>".$rs['campo']."</td>";
                        echo "<td>".$rs['valor']."</td>";
                        echo "</tr>";
                    }
                ?>
            </tbody>
        </table>
    </body>
</html>
<?php
}else{
    echo "<b>No se ha recibido el parámetro para hacer la operación</b>";
}
?>


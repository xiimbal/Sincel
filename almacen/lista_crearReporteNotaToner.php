<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
if (isset($_GET['ticket']) && $_GET['ticket'] != "") {
    $idTicket = $_GET['ticket'];

//    
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="expires" content="-1">
        <link rel="shortcut icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
        <title>Genesis</title>
        <meta http-equiv="expires" content="-1">
        <!-- JS -->
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
        <script src="../resources/js/jquery/jquery-ui.min.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
        <script type="text/javascript" src="../resources/js/funciones.js"></script>   

        <!--easyui-->
        <script type="text/javascript" src="../resources/js/jquery.easyui.min.js"></script>
        <link rel="stylesheet" type="text/css" href="../resources/css/arbol/easyui.css">

        <!-- Tables -->
        <script type="text/javascript" language="javascript" src="../resources/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/media/js/TableTools.min.js"></script>
        <link href="../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/sicop.css" rel="stylesheet" type="text/css">  

        <link id="linkCSS" href=".././css/Site.css" rel="stylesheet" type="text/css" media="all">
        <link href=".././css/Site.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/menu-12.css" rel="stylesheet" type="text/css" media="all">
        <style>
            .contenido{
                width: 800px;
                margin-left:auto;
                margin-right:auto;
            }

            .style1{
                width: 30%;
            }
        </style>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/lista_crearNota.js"></script>
        <script>
//            function CrearReporte(notaTicket) {
//                var controler = "WEB-INF/Controllers/Controler_EntregaRefacciones.php";
//                var pagina = "almacen/lista_listoParaEntregar.php";
////                var id = new Array();
////                var contador = 0;
////                $("input:checkbox:checked").each(function() {
////                    //cada elemento seleccionado
////                    id[contador] = $(this).val();
////                    contador++;
////
////                });
//                window.open("../reportes/ReporteSolicitudRefacciones.php?idNota=" + notaTicket, "_blanck");
//            }
             function CrearReporteToner(notaTicket) {
                var controler = "WEB-INF/Controllers/Controler_EntregaRefacciones.php";
                var pagina = "almacen/lista_listoParaEntregar.php";
//                var id = new Array();
//                var contador = 0;
//                $("input:checkbox:checked").each(function() {
//                    //cada elemento seleccionado
//                    id[contador] = $(this).val();
//                    contador++;
//
//                });
                window.open("../reportes/ReporteSolicitudToner.php?idNota=" + notaTicket, "_blanck");
            }
        </script>
    </head>
    <body>
<!--        <input type="button" class="boton" value="Generar Reporte" onclick="CrearReporte();"/>-->
        <table id="tCrearNota" style="min-width: 100%">
            <thead>
                <tr>
                    <?php
                    $cabeceras = array("Nota", "Toner", "Cantidad", "Fecha", "");
                    for ($i = 0; $i < (count($cabeceras)); $i++) {
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                    }
                    ?>                                                                      
                </tr>
            </thead>
            <tbody>
                <?php
                $catalogo = new Catalogo();
                $query = $catalogo->obtenerLista("SELECT nt.IdNotaTicket,mc.IdMovimiento,nt.DiagnosticoSol,c.Modelo,c.NoParte,mc.CantidadMovimiento,mc.Fecha
                                FROM movimiento_componente mc,c_componente c,c_notaticket nt
                                WHERE nt.IdTicket='" . $idTicket . "'
                                AND mc.NoParteComponente=c.NoParte
                                AND mc.IdNotaTicket=nt.IdNotaTicket
                                ORDER BY mc.Fecha ASC  ");
                $contador = 1;
                while ($rs = mysql_fetch_array($query)) {
                    echo "<tr>";
                    echo "<td align='center' scope='row'>" . $rs['DiagnosticoSol'] . "</td>";
                    echo "<td align='center' scope='row'>" . $rs['Modelo'] . "<br/>(" . $rs['NoParte'] . ")</td>";
                    echo "<td align='center' scope='row'>" . $rs['CantidadMovimiento'] . "</td>";
                    echo "<td align='center' scope='row'>" . $rs['Fecha'] . "</td>";
                    ?>
                <td align='center' scope='row'>
                    <!--<input type="button" class="boton" value="Generar Reporte" onclick="CrearReporte('<?php echo $rs['IdNotaTicket'] ?>');"/>-->
                    <a href='#' onclick="CrearReporteToner('<?php echo $rs['IdNotaTicket'] ?>');
                    return false;" title='Generar reporte' ><img src="../resources/images/icono_impresora.png" width="24" height="24"/></a>
                </td>
                <?php
                echo "</tr>";
                $contador++;
            }
            ?>
        </tbody>
    </table>

</body>
</html>
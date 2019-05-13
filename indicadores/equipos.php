<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    echo "2";
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Indicadores.class.php");

$emisor = "";
$ejecutivo = "";
$cliente = "";
$liga = "";

if (isset($_GET['Emisor']) && $_GET['Emisor'] != "") {
    $emisor = $_GET['Emisor'];
}
if (isset($_GET['Ejecutivo']) && $_GET['Ejecutivo'] != "") {
    $ejecutivo = $_GET['Ejecutivo'];
}
if (isset($_GET['Cliente']) && $_GET['Cliente'] != "") {
    $cliente = $_GET['Cliente'];
}

if (isset($_POST['razonSocial']) && $_POST['razonSocial'] != "") {
    $emisor = $_POST['razonSocial'];
}
if (isset($_POST['ejecutivo']) && $_POST['ejecutivo'] != "") {
    $ejecutivo = $_POST['ejecutivo'];
}
if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
    $cliente = $_POST['cliente'];
    $liga = "indicadores/equipos.php?Emisor=$emisor&Ejecutivo=$ejecutivo&Cliente=$cliente";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Equipos</title>
        <meta charset="utf-8"/>        
    </head>
    <body>        
        <div id="posicion">
            <?php if (!empty($liga)) { ?>
                <a class="highslide" href="<?php echo $liga; ?>" 
                   onclick="return hs.htmlExpand(this, {objectType: 'iframe', width: 1020, headingText: 'Equipos', targetX: 'posicion 1px', targetY: null});">
                   <?php } ?>
                <table style="width: 75%;">
                    <thead>
                        <tr style="background-color: #F79646;">
                            <th>Equipo</th>
                            <th>Cantidad</th>
                            <th>Costo (US $)</th>
                        </tr>
                    </thead>            
                    <tbody style="background-color: #FCD5B4;">
                        <?php
                        $indicadores = new Indicadores();
                        /* Equipos en arrendamiento */
                        $indicadores->obtenerEquiposArrendamiento($emisor, $ejecutivo, $cliente);
                        /* Equipos en almacén */
                        //$indicadores->imprimirTablaEquiposPorAlmacen();
                        /* Equipos en taller */
                        //$indicadores->imprimirFila("En taller", $indicadores->obtenerEquiposEnTaller());
                        /* Equipos en demostración */
                        $indicadores->imprimirTablaTipoSolicitud(3, "En garantía", $emisor, $ejecutivo, $cliente);
                        /* Equipos en demostración */
                        //$indicadores->imprimirTablaTipoSolicitud(4, "En demostración", $emisor, $ejecutivo, $cliente);
                        /* Equipos en demostración */
                        //$indicadores->imprimirTablaTipoSolicitud(2, "En Backup", $emisor, $ejecutivo, $cliente);
                        ?>
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #F79646;">
                            <td>Total</td>
                            <td style='text-align: right;'>  <?php echo number_format($indicadores->getTotalEquipos(), 0); ?> </td>
                            <td style='text-align: right;'>  <?php echo "$ " . number_format($indicadores->getCostoToal(), 2); ?> </td>
                        </tr>
                    </tfoot>
                </table>       
                <div style="color: blue; font-size: 11px;">* Estos datos son a fecha actual</div>
                <?php if (!empty($liga)) { ?>
                </a>
            <?php } ?>
        </div>        
    </body>
</html>
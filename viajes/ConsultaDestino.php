<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$destino = "DESTINO TICKET";
if (isset($_GET['destino'])) {
    $destino = $_GET['destino'];
}

if (isset($_GET['IdTicket'])) {
    $idTicket = $_GET['IdTicket'];
}

//print_r("GET = ");
//print_r($_GET);
//print_r("POST = ");
//print_r($_POST);
//$totalTicket = count($idTicket);
$catalogo = new Catalogo();
$catalogo->setEmpresa($empresa);
$query = $catalogo->obtenerLista("SELECT * FROM k_plantilla_asistencia WHERE IdTicket = " . $idTicket . ";");
if (mysql_num_rows($query) > 0) {
    $rs = mysql_fetch_array($query);
    $query1 = $catalogo->obtenerLista("SELECT ca.IdArea, cd.IdDomicilio, ca.Descripcion AS Campania, CONCAT(cu.Nombre,' ',cu.ApellidoPaterno,' ',cu.ApellidoMaterno) AS Nombre, cp.TipoEvento
                                      FROM c_usuario cu, k_plantilla kp, c_area ca, c_domicilio_usturno cd, c_plantilla cp WHERE ca.IdArea=cd.IdCampania AND cd.IdUsuario=kp.idUsuario 
                                      AND cu.IdUsuario=kp.idUsuario AND kp.idPlantilla=cp.idPlantilla AND idK_PLantilla = " . $rs['idK_Plantilla'] . ";");
    $rs1 = mysql_fetch_array($query1);
    $Campania = $rs1['Campania'];
    if ($rs1['TipoEvento'] == 1) {
        $Evento = "Aforo";
        $queryc = $catalogo->obtenerLista("SELECT ct.Nombre
                                      FROM c_estado ct LEFT JOIN c_area ca ON ca.IdEstado=ct.IdEstado WHERE ca.IdArea= " . $rs1['IdArea'] . ";");
        $rsc = mysql_fetch_array($queryc);
        $Cuadrante = $rsc['Nombre'];
    } else {
        $Evento = "Desaforo";
        $queryc = $catalogo->obtenerLista("SELECT ct.Nombre
                                      FROM c_estado ct LEFT JOIN c_domicilio_usturno cd ON cd.IdArea=ct.IdEstado WHERE cd.IdDomicilio= " . $rs1['IdDomicilio'] . ";");
        $rsc = mysql_fetch_array($queryc);
        $Cuadrante = $rsc['Nombre'];
        
    }
    $NombreEmpleado = $rs1['Nombre'];
} else {
    $query = $catalogo->obtenerLista("SELECT CONCAT(cu.Nombre,' ',cu.ApellidoPaterno,' ',cu.ApellidoMaterno) AS Nombre, ca.Descripcion AS Campania, ct.Nombre AS Cuad
                                      FROM c_area ca, c_especial ce, c_estado ct, c_usuario cu WHERE cu.IdUsuario=ce.idUsuario AND ct.IdEstado=ce.Cuadrante 
                                      AND ca.IdArea=ce.idCampania AND ce.idTicket = " . $idTicket . ";");
    $rs = mysql_fetch_array($query);
    $Campania = $rs['Campania'];
    $Cuadrante = $rs['Cuad'];
    $Evento = "Viaje Especial";
    $NombreEmpleado = $rs['Nombre'];
}
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
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>                
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/alta_cliente.js"></script>        
    </head>
    <body>
        <table>
            <tr>
                <td>Empleado:</td>
                <td>
                    <?php
                    echo $NombreEmpleado;
                    ?>
                </td>
            </tr>
            <tr>
                <td>Ticket:</td>
                <td>
                    <?php
                    echo $idTicket;
                    ?>
                </td>
            </tr>
            <tr>
                <td>Campaña:</td>
                <td>
                    <?php
                    echo $Campania;
                    ?>
                </td>
            </tr>
            <tr>
                <td>Tipo de Evento:</td>
                <td>
                    <?php
                    echo $Evento;
                    ?>
                </td>
            </tr>
            <tr>
                <td>Cuadrante:</td>
                <td>
                    <?php
                    echo $Cuadrante;
                    ?>
                </td>
            </tr>
<!--            <tr>
                <td>Cantidad de Empleados: <?php //echo $totalTicket;         ?></td>
            </tr>-->
            <tr>
                <td>Destino:</td>
                <td><?php
                    echo $destino;
                    ?>
                </td>
            </tr>
        </table>
    </body>
</html>
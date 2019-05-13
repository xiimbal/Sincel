<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$destino = "DESTINO TICKET";
if (isset($_GET['idPlantilla'])) {
    $idPlantilla = $_GET['idPlantilla'];
}

if (isset($_GET['idArea'])) {
    $idArea = $_GET['idArea'];
}

if (isset($_GET['idTicket'])) {
    $idTicket = $_GET['idTicket'];
}

//print_r("GET = ");
//print_r($_GET);
//print_r("POST = ");
//print_r($_POST);
//$totalTicket = count($idTicket);
$catalogo = new Catalogo();
$catalogo->setEmpresa($empresa);
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
        <?php
        if (isset($idPlantilla) && $idPlantilla != "") {
            $w = 0;
            $asignados = 0;
            $consulta1 = "SELECT CONCAT(cu.Nombre,' ',cu.ApellidoPaterno,' ',ApellidoMaterno) AS Empleado, kpa.IdTicket, cp.TipoEvento, cp.idPlantilla, ct.EstatusAsigna, 
                 cp.idCampania, ca.Descripcion AS Campania, cp.idTurno, ctu.descripcion AS Turno,
                 (CASE 
                 WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 2 
                 THEN CONCAT(dut.Calle,' ',dut.NoExterior,' No. Int1. ',dut.NoInterior,', ',dut.Colonia,', ',dut.Delegacion,', ',dut.Estado,', ',dut.CodigoPostal,'. Cita <b>origen: ',cp.Fecha,' ',cp.Hora,'</b>')
                 WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 1 
                 THEN CONCAT(dcc.Calle,' ',dcc.NoExterior,' No. Int3. ',dcc.NoInterior,', ',dcc.Colonia,', ',dcc.Delegacion,', ',dcc.Estado,', ',dcc.CodigoPostal,'. Cita <b>destino: ',cp.Fecha,' ',cp.Hora,'</b>') 
                 ELSE NULL END) AS LugarDestino
                 FROM c_plantilla cp INNER JOIN k_plantilla kp ON cp.idPlantilla=kp.idPlantilla
                 INNER JOIN k_plantilla_asistencia kpa ON kp.idK_Plantilla=kpa.idK_Plantilla INNER JOIN c_ticket ct ON ct.IdTicket=kpa.IdTicket
                 INNER JOIN c_usuario cu ON kp.idUsuario=cu.IdUsuario INNER JOIN c_area ca ON ca.IdArea=cp.idCampania INNER JOIN c_turno ctu ON ctu.idTurno=cp.idTurno
                 LEFT JOIN c_centrocosto AS cc ON ct.ClaveCentroCosto = cc.ClaveCentroCosto
                 LEFT JOIN c_domicilio AS dcc ON dcc.IdDomicilio = (SELECT MIN(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = cc.ClaveCentroCosto)
                 LEFT JOIN c_domicilio_usturno AS dut ON dut.IdUsuario = cu.IdUsuario 
                 WHERE cp.idPlantilla=" . $idPlantilla . " AND ct.AreaAtencion=" . $idArea . " AND ct.EstadoDeTicket=3 AND cp.Estatus=2;";
            $result1 = $catalogo->obtenerLista($consulta1);
            while ($rs1 = mysql_fetch_array($result1)) {
                if ($w == 0) {
                    ?>
                    <table>
                        <tr>
                            <td>Campaña:</td><td><b><?php echo $rs1['Campania']; ?></b></td>
                            <td>Turno:</td><td><b><?php echo $rs1['Turno']; ?></b></td>
                            <td>Tipo de Evento:</td><td><b><?php
                                if ($rs1['TipoEvento'] == 1) {
                                    echo "Aforo";
                                } else {
                                    echo "Desaforo";
                                }
                                ?></b></td>
                        </tr>
                        <tr></tr>
                        <tr></tr>
                    </table>

            <?php if ($rs1['TipoEvento'] == 1) { ?>
                        <table>
                            <tr>
                                <td>Destino:</td>
                                <td><?php
                                    echo $rs1['LugarDestino'];
                                    ?>
                                </td>
                            </tr>
                        </table>
                    <?php } 
                }
                $w++;
                ?>
                <table>
                    <tr>
                        <td>Empleado <?php echo $w;?>: <b><?php echo $rs1['Empleado'];?></b> (Ticket: <b><?php echo $rs1['IdTicket'];?></b>)</td>
                    </tr>

                <!--            <tr>
                    <td>Cantidad de Empleados: <?php //echo $totalTicket;                ?></td>
                </tr>-->

        <?php if ($rs1['TipoEvento'] == 2) { ?>
                        <tr>
                            <td>Destino: <?php echo $rs1['LugarDestino']; ?></td>
                        </tr>
                        <tr><td></td></tr>
                        <?php
                    }
                    if ($rs1['EstatusAsigna'] == 1) {
                        $asignados++;
                    }
                }
                ?>
                <tr></tr>
                <tr>
                    <td><?php echo $asignados; ?> empleados asignados de <?php echo $w; ?> empleado(s)</td>  
                </tr>
            </table>
            <?php
        } else {
            $query = $catalogo->obtenerLista("SELECT CONCAT(cu.Nombre,' ',cu.ApellidoPaterno,' ',cu.ApellidoMaterno) AS Nombre, ca.Descripcion AS Campania, ct.Nombre AS Cuad
                                      FROM c_area ca, c_especial ce, c_estado ct, c_usuario cu WHERE cu.IdUsuario=ce.idUsuario AND ct.IdEstado=ce.Cuadrante 
                                      AND ca.IdArea=ce.idCampania AND ce.idTicket = " . $idTicket . ";");
            $rs = mysql_fetch_array($query);
            $Campania = $rs['Campania'];
            $Cuadrante = $rs['Cuad'];
            $Evento = "Viaje Especial";
            $NombreEmpleado = $rs['Nombre'];
            ?>
            <table>
                <tr>
                    <td>Empleado:</td>
                    <td><?php echo $NombreEmpleado;?></td>
                </tr>
                <tr>
                    <td>Ticket:</td>
                    <td><?php echo $idTicket;?></td>
                </tr>
                <tr>
                    <td>Campaña:</td>
                    <td><?php echo $Campania;?></td>
                </tr>
                <tr>
                    <td>Tipo de Evento:</td>
                    <td><?php echo $Evento;?></td>
                </tr>
                <tr>
                    <td>Cuadrante:</td>
                    <td><?php echo $Cuadrante;?></td>
                </tr>
                <!--            <tr>
                    <td>Cantidad de Empleados: <?php //echo $totalTicket;                ?></td>
                </tr>-->
                <tr>
                    <td>Destino:</td>
                    <td><?php
                        echo $destino;
                        ?>
                    </td>
                </tr>
            </table>
            <?php
        }
        ?>
    </body>
</html>
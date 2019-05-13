<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_GET['ticket'])) {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Ticket.class.php");

if (isset($_GET['ticket']) && $_GET['ticket'] != "") {
    $idTicket = $_GET['ticket'];
}

$ticket_obj = new Ticket();
if (!$ticket_obj->getTicketByID($idTicket)) {
    echo "Error: no se pudo obtener la información del ticket";
    return;
}

$refacciones_enviadas = array();
$refacciones_listas = array();
$refacciones_canceladas = array();
/* Arrays para tablas */
$salidas = array();
$entregadas = array();
$backorder = array();
$canceladas = array();

//Archivo original lista_crearReporteNota.php180418

$cabeceras = array("Fecha","NoParte","Descripcion","ContadorBN","ContadorCL","Ubicacion","Cantidad","Almacen");
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

        <script type="text/javascript" language="javascript" src="../resources/js/paginas/lista_crearNota.js"></script>
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

    </head>
    <body>
        <input type="image" src="../resources/images/icono_impresora.png" style="width: 30px; height: 30px; float: right;" title="Imprimir reporte" onclick="javascript:window.print();
                return false;">

        <?php
        echo "<h2>Ticket: " . $ticket_obj->getIdTicket() . "</h2>";
        echo "<br/>Serie: <b>" . $ticket_obj->getNoSerieEquipo() . "</b> Modelo: <b>" . $ticket_obj->getModeloEquipo() . "</b>";
        echo "<br/>Cliente: <b>" . $ticket_obj->getNombreCliente() . "</b>";
        echo "<br/>Localidad: <b>" . $ticket_obj->getNombreCentroCosto() . "</b>";
        echo "<input type='hidden' id='NoSerie' name='NoSerie' value='" . $ticket_obj->getNoSerieEquipo() . "' />"
        ?>
        <br/><br/>
        <?php
        $catalogo = new Catalogo();
        /* Datos de refacciones entregadas */
        $result = $catalogo->obtenerLista("SELECT nt.IdNotaTicket, nt.FechaHora AS Fecha,nt.DiagnosticoSol, nt.IdEstatusAtencion, 
                e.Nombre AS Estado, nr.NoParteComponente AS NoParte,  kac.Ubicacion,lt.ContadorBN, lt.ContadorCL,
                c.Modelo, c.Descripcion , SUM(nr.Cantidad) AS Cantidad,(select ca.nombre_almacen from c_almacen as ca LEFT JOIN k_almacencomponente AS kac ON kac.id_almacen = ca.id_almacen limit 1)AS Almacen
                FROM `c_notaticket` AS nt
                LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
                LEFT JOIN c_estado AS e ON e.IdEstado = nt.IdEstatusAtencion
                LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
                LEFT JOIN k_almacencomponente AS kac ON kac.id_almacen = nr.IdAlmacen AND kac.NoParte = nr.NoParteComponente
                LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = (SELECT MAX(id_lecturaticket) FROM c_lecturasticket WHERE fk_idticket = nt.IdTicket)
                WHERE nt.IdTicket = $idTicket AND !ISNULL(nr.IdNotaTicket) AND IdEstatusAtencion IN(17)
                GROUP BY c.NoParte ORDER BY Fecha DESC;");
        $i = 0;
        while ($rs = mysql_fetch_array($result)) {
            foreach ($cabeceras as $key => $value) {                
                $entregadas[$i][$value] = $rs[$value];
            }           
            $i++;
            $refacciones_enviadas[$rs['NoParte']] = $rs['Cantidad'];
        }
        
        /* Datos de refacciones de salida (listas para entregar) */
        $result = $catalogo->obtenerLista("SELECT nt.IdNotaTicket, nt.FechaHora AS Fecha,nt.DiagnosticoSol, nt.IdEstatusAtencion, 
            e.Nombre AS Estado, nr.NoParteComponente AS NoParte, 
            c.Modelo, c.Descripcion ,kac.Ubicacion,lt.ContadorBN, lt.ContadorCL,SUM(nr.Cantidad) AS Cantidad,(select ca.nombre_almacen from c_almacen as ca LEFT JOIN k_almacencomponente AS kac ON kac.id_almacen = ca.id_almacen limit 1)AS Almacen
            FROM `c_notaticket` AS nt
            LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
            LEFT JOIN c_estado AS e ON e.IdEstado = nt.IdEstatusAtencion
            LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
            LEFT JOIN k_almacencomponente AS kac ON kac.id_almacen = nr.IdAlmacen AND kac.NoParte = nr.NoParteComponente
            LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = (SELECT MAX(id_lecturaticket) FROM c_lecturasticket WHERE fk_idticket = nt.IdTicket)
            WHERE nt.IdTicket = $idTicket AND !ISNULL(nr.IdNotaTicket) AND IdEstatusAtencion IN(21)
            GROUP BY c.NoParte ORDER BY Fecha DESC;");
        if ($i > 0) {
            $i = 0;
        }
        while ($rs = mysql_fetch_array($result)) {
            if (isset($refacciones_enviadas[$rs['NoParte']]) && $refacciones_enviadas[$rs['NoParte']] >= $rs['Cantidad']) {
                continue;
            } else if (isset($refacciones_enviadas[$rs['NoParte']])) {
                $cantidad = (int) $rs['Cantidad'] - (int) $refacciones_enviadas[$rs['NoParte']];
            } else {
                $cantidad = (int) $rs['Cantidad'];
            }
            
            foreach ($cabeceras as $key => $value) {                
                $salidas[$i][$value] = $rs[$value];
            }            
            $salidas[$i]['Cantidad'] = $cantidad;
            $i++;
            $refacciones_listas[$rs['NoParte']] = $cantidad;
        }
        
        /* Datos de refacciones canceladas */        
        $result = $catalogo->obtenerLista("SELECT nt.IdNotaTicket, nt.FechaHora AS Fecha,nt.DiagnosticoSol, nt.IdEstatusAtencion, 
            e.Nombre AS Estado, nr.NoParteComponente AS NoParte, 
            c.Modelo, c.Descripcion ,kac.Ubicacion,lt.ContadorBN, lt.ContadorCL,SUM(nr.Cantidad) AS Cantidad,(select ca.nombre_almacen from c_almacen as ca LEFT JOIN k_almacencomponente AS kac ON kac.id_almacen = ca.id_almacen limit 1)AS Almacen
            FROM `c_notaticket` AS nt
            LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
            LEFT JOIN c_estado AS e ON e.IdEstado = nt.IdEstatusAtencion
            LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
            LEFT JOIN k_almacencomponente AS kac ON kac.id_almacen = nr.IdAlmacen AND kac.NoParte = nr.NoParteComponente
            LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = (SELECT MAX(id_lecturaticket) FROM c_lecturasticket WHERE fk_idticket = nt.IdTicket)
            WHERE nt.IdTicket = $idTicket AND !ISNULL(nr.IdNotaTicket) AND IdEstatusAtencion IN(9)                        
            GROUP BY c.NoParte HAVING Cantidad = 0 ORDER BY Fecha DESC;");
        if ($i > 0) {
            $i = 0;
        }
        while ($rs = mysql_fetch_array($result)) {
            if ((isset($refacciones_enviadas[$rs['NoParte']]) && $refacciones_enviadas[$rs['NoParte']] >= $rs['Cantidad']) || (isset($refacciones_listas[$rs['NoParte']]) && $refacciones_listas[$rs['NoParte']] >= $rs['Cantidad']) || (isset($refacciones_listas[$rs['NoParte']]) && isset($refacciones_enviadas[$rs['NoParte']]) && ($refacciones_listas[$rs['NoParte']] + $refacciones_enviadas[$rs['NoParte']]) >= $rs['Cantidad'])) {
                continue;
            } else if (isset($refacciones_enviadas[$rs['NoParte']]) && isset($refacciones_listas[$rs['NoParte']])) {
                $cantidad = (int) $rs['Cantidad'] - ((int) $refacciones_enviadas[$rs['NoParte']] + (int) $refacciones_listas[$rs['NoParte']]);
            } else if (isset($refacciones_enviadas[$rs['NoParte']])) {
                $cantidad = (int) $rs['Cantidad'] - (int) $refacciones_enviadas[$rs['NoParte']];
            } else if (isset($refacciones_listas[$rs['NoParte']])) {
                $cantidad = (int) $rs['Cantidad'] - (int) $refacciones_listas[$rs['NoParte']];
            } else {
                $cantidad = (int) $rs['Cantidad'];
            }            
            
            foreach ($cabeceras as $key => $value) {
                $canceladas[$i][$value] = $rs[$value];
            }                       
            $canceladas[$i]['Cantidad'] = 0;
            $i++;
            $refacciones_canceladas[$rs['NoParte']] = $rs['Cantidad'];
        }

        /* Datos de refacciones en backorder */
        $result = $catalogo->obtenerLista("SELECT nt.IdNotaTicket, nt.FechaHora AS Fecha,nt.DiagnosticoSol, nt.IdEstatusAtencion, 
            e.Nombre AS Estado, nr.NoParteComponente AS NoParte, 
            c.Modelo, c.Descripcion ,kac.Ubicacion,lt.ContadorBN, lt.ContadorCL,SUM(nr.Cantidad) AS Cantidad,(select ca.nombre_almacen from c_almacen as ca LEFT JOIN k_almacencomponente AS kac ON kac.id_almacen = ca.id_almacen limit 1)AS Almacen
            FROM `c_notaticket` AS nt
            LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
            LEFT JOIN c_estado AS e ON e.IdEstado = nt.IdEstatusAtencion
            LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
            LEFT JOIN k_almacencomponente AS kac ON kac.id_almacen = nr.IdAlmacen AND kac.NoParte = nr.NoParteComponente
            LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = (SELECT MAX(id_lecturaticket) FROM c_lecturasticket WHERE fk_idticket = nt.IdTicket)
            WHERE nt.IdTicket = $idTicket AND !ISNULL(nr.IdNotaTicket) AND IdEstatusAtencion IN(20)
            GROUP BY c.NoParte ORDER BY Fecha DESC;");
        if ($i > 0) {
            $i = 0;
        }
        while ($rs = mysql_fetch_array($result)) {
            if ((isset($refacciones_enviadas[$rs['NoParte']]) && $refacciones_enviadas[$rs['NoParte']] >= $rs['Cantidad']) || (isset($refacciones_listas[$rs['NoParte']]) && $refacciones_listas[$rs['NoParte']] >= $rs['Cantidad']) || (isset($refacciones_listas[$rs['NoParte']]) && isset($refacciones_enviadas[$rs['NoParte']]) && ($refacciones_listas[$rs['NoParte']] + $refacciones_enviadas[$rs['NoParte']]) >= $rs['Cantidad'])) {
                continue;
            } else if (isset($refacciones_enviadas[$rs['NoParte']]) && isset($refacciones_listas[$rs['NoParte']])) {
                $cantidad = (int) $rs['Cantidad'] - ((int) $refacciones_enviadas[$rs['NoParte']] + (int) $refacciones_listas[$rs['NoParte']]);
            } else if (isset($refacciones_enviadas[$rs['NoParte']])) {
                $cantidad = (int) $rs['Cantidad'] - (int) $refacciones_enviadas[$rs['NoParte']];
            } else if (isset($refacciones_listas[$rs['NoParte']])) {
                $cantidad = (int) $rs['Cantidad'] - (int) $refacciones_listas[$rs['NoParte']];
            } else {
                $cantidad = (int) $rs['Cantidad'];
            }
            
            if(isset($refacciones_canceladas[$rs['NoParte']])){
                continue;
            }
            
            foreach ($cabeceras as $key => $value) {
                $backorder[$i][$value] = $rs[$value];
            }           
            $backorder[$i]['Cantidad'] = $cantidad;
            $i++;
        }                
        
        if(!empty($salidas)){
            echo "<br/><br/><fieldset>";
            echo "<legend>Salida de refacciones</legend>";
            echo "<table class='tabladatos' style='width: 100%;'>";
            echo "<thead>";
            for ($i = 0; $i < (count($cabeceras)); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            echo "</thead><tbody>";
            for ($j = 0; $j < (count($salidas)); $j++) {
                echo "<tr>";
                for ($i = 0; $i < (count($cabeceras)); $i++) {
                    echo "<td align='center' scope='row'>" . $salidas[$j][$cabeceras[$i]] . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</fieldset>";
        }   
        
        if(!empty($entregadas)){
            echo "<br/><br/><fieldset>";
            echo "<legend>Refacciones entregadas</legend>";
            echo "<table class='tabladatos' style='width: 100%;'>";
            echo "<thead>";
            for ($i = 0; $i < (count($cabeceras)); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            echo "</thead><tbody>";
            for ($j = 0; $j < (count($entregadas)); $j++) {
                echo "<tr>";
                for ($i = 0; $i < (count($cabeceras)); $i++) {
                    echo "<td align='center' scope='row'>" . $entregadas[$j][$cabeceras[$i]] . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</fieldset>";
        }                             
        
        if(!empty($backorder)){
            echo "<br/><br/><fieldset>";
            echo "<legend>Backorder</legend>";
            echo "<table class='tabladatos' style='width: 100%;'>";
            echo "<thead>";
            for ($i = 0; $i < (count($cabeceras)); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            echo "</thead><tbody>";
            for ($j = 0; $j < (count($backorder)); $j++) {
                echo "<tr>";
                for ($i = 0; $i < (count($cabeceras)); $i++) {
                    echo "<td align='center' scope='row'>" . $backorder[$j][$cabeceras[$i]] . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</fieldset>";
        }
        
        if(!empty($canceladas)){
            echo "<br/><br/><fieldset>";
            echo "<legend>Canceladas</legend>";
            echo "<table class='tabladatos' style='width: 100%;'>";
            echo "<thead>";
            for ($i = 0; $i < (count($cabeceras)-1); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            echo "</thead><tbody>";
            for ($j = 0; $j < (count($canceladas)); $j++) {
                echo "<tr>";
                for ($i = 0; $i < (count($cabeceras) - 1); $i++) {
                    echo "<td align='center' scope='row'>" . $canceladas[$j][$cabeceras[$i]] . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</fieldset>";
        }
        ?>        
    </body>
</html>
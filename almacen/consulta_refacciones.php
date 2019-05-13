<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['serie'])) {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/MovimientoComponente.class.php");

$NoSerie = $_POST['serie'];
$catalogo = new Catalogo();
$parametros = new Parametros();
$movimiento_componente = new MovimientoComponente();

if ($parametros->getRegistroById(8)) {
    $liga = $parametros->getDescripcion();
} else {
    $liga = "http://genesis2.techra.com.mx/genesis2/";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?php
            if(!isset($_POST['div'])){
                echo '<script type="text/javascript" language="javascript" src="resources/js/paginas/consulta_refacciones.js"></script>';
            }else{
                echo '<script type="text/javascript" language="javascript" src="../resources/js/paginas/consulta_refacciones.js"></script>';
            }
        ?>        
    </head>
    <body>
        <fieldset>
            <legend>Piezas cambiadas</legend>
            <table class='dataTable' style="width: 100%;">
                <thead>
                    <tr>
                        <?php
                        echo "<tr>";
                        //echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. Serie</th>";
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. de parte</th>";
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\">Tipo</th>";
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\">Modelo</th>";
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\">Cantidad</th>";
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de entrega</th>";
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\">Ticket</th>";
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\">Contador BN</th>";
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\">Contador Color</th>";
                        echo "</tr>";
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $consulta = "SELECT t.IdTicket, nt.DiagnosticoSol, nr.NoParteComponente, tc.Nombre AS TipoComponente, 
                        nr.Cantidad, nr.FechaCreacion AS FechaEntrega, CONCAT(c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS Modelo,
                        lt.ContadorBN, lt.ContadorCL
                        FROM `c_ticket` AS t
                        LEFT JOIN c_notaticket AS nt ON t.IdTicket = nt.IdTicket
                        LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
                        LEFT JOIN c_componente AS c ON nr.NoParteComponente = c.NoParte
                        LEFT JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
                        LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = (SELECT MAX(id_lecturaticket) FROM c_lecturasticket WHERE fk_idticket = t.IdTicket)
                        WHERE t.TipoReporte <> 15 AND t.NoSerieEquipo = '$NoSerie' AND nt.IdEstatusAtencion = 17 AND !ISNULL(nr.IdNotaTicket) ";
                    if(isset($_POST['idTicket'])){
                        $consulta.= " AND t.IdTicket = ".$_POST['idTicket'];
                    }
                    $consulta .= " GROUP BY t.IdTicket,nr.NoParteComponente ORDER BY t.IdTicket;";
                    //echo $consulta;
                    $result = $catalogo->obtenerLista($consulta);
                    $idTicketAnterior = "";
                    while ($rs = mysql_fetch_array($result)) {
                        if($idTicketAnterior != $rs['IdTicket']){
                            $resultDevoluciones = $movimiento_componente->getDevolucionByTicket($rs['IdTicket']);
                            while($rsDevolucion = mysql_fetch_array($resultDevoluciones)){
                                echo "<tr>";                        
                                echo "<td align='center' scope='row'>" . $rsDevolucion['NoParteComponente'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rsDevolucion['TipoComponente'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rsDevolucion['Modelo'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rsDevolucion['Cantidad'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rsDevolucion['FechaEntrega'] . "</td>";                        
                                echo "<td align='center' scope='row'>";
                                echo "<a href='" . $liga . "/principal.php?mnu=mesa&action=lista_ticket&id=" . $rsDevolucion['IdTicket'] . "' target='_blank' title='Ver ticket " . 
                                        $rsDevolucion['IdTicket'] . "'>" . $rsDevolucion['IdTicket'] . "</a>";
                                echo "</td>";
                                echo "<td align='center' scope='row'></td>";
                                echo "<td align='center' scope='row'></td>";
                                echo "</tr>";
                            }
                        }
                        $idTicketAnterior = $rs['IdTicket'];
                        echo "<tr>";                        
                        echo "<td align='center' scope='row'>" . $rs['NoParteComponente'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['TipoComponente'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Cantidad'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['FechaEntrega'] . "</td>";                        
                        echo "<td align='center' scope='row'>";
                        echo "<a href='" . $liga . "/principal.php?mnu=mesa&action=lista_ticket&id=" . $rs['IdTicket'] . "' target='_blank' title='Ver ticket " . $rs['IdTicket'] . "'>" . $rs['IdTicket'] . "</a>";
                        echo "</td>";
                        echo "<td align='center' scope='row'>" . $rs['ContadorBN'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['ContadorCL'] . "</td>";
                        echo "</tr>";                        
                    }
                    ?>
                </tbody>                        
            </table>
        </fieldset>
    </body>
</html>

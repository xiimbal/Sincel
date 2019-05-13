<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

$parametros = new Parametros();
$parametros->getRegistroById("7");
$liga = $parametros->getDescripcion();
$parametros->getRegistroById("8");
$liga_propia = $parametros->getDescripcion();



$permisos_grid = new PermisosSubMenu();
$same_page = "facturacion/validar_viaticos.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$nombre_objeto = $permisos_grid->getNombreTicketSistema();
$catalogo = new Catalogo();

/* Para mantener los filtros y paginados de la tabla */
if (isset($_GET['page']) && isset($_GET['filter'])) {
    $filter = str_replace("_XX__XX_", " ", $_GET['filter']);
    $page = $_GET['page'];
} else {
    $page = "0";
    $filter = "";
}

$ticket = "";
$buscar = "";
$where = "";
if(isset($_POST['buscar']) && $_POST['buscar'] == "1"){
    $buscar = "1";
    if(isset($_POST['ticket']) && $_POST['ticket'] != ""){
        $ticket = $_POST['ticket'];
        $where = "AND t.IdTicket = $ticket";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="/resources/js/paginas/facturacion/validar_viaticos.js"></script>   
    </head>
    <body>
        <div class="principal">
            <table style="width: 100%">
                <tr>
                    <td><?php echo $nombre_objeto?></td>
                    <td><input type="text" style="width: 50px" id="ticket" name="ticket" value="<?php echo $ticket?>"></td>
                    <td><button type="button" onclick="BuscarTicketsValidarViaticos('<?php echo $same_page?>', 'ticket')" class="boton">Buscar viáticos</button></td>
                </tr>
            </table>
            <br>
            <div id="agrupar" style="display: none">
                <input id="boton_atender" name="boton_atender" type="button" style=" margin-left: 90%" class="boton" value="Validar"  onclick="validarSeleccionados();" />
                <div style="display: inline; float: right;" id="div1">
                    <input type="checkbox" id="slc_todo_solicitado" onclick="seleccionarTodosSolicitados();"/>
                    <div id="mensaje_sel" style="display: inline; ">Seleccionar todo</div>    
                </div>
            </div>
            <?php if($buscar == "1"){?>
            <table class="dataTable" id="tabla1" style="width: 100%;">
                <thead>
                    <tr>
                        <?php 
                        $cabeceras = array("No. $nombre_objeto" , "No. de viáticos a validar", "Viáticos a validar","","");
                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                            echo "<th width=\"2%\" align=\"center\" >" . $cabeceras[$i] . "</th>";
                        }
                        ?>                        
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $consulta = "SELECT t.IdTicket, COUNT(*) AS conteo, COUNT(ve.IdNotaTicketFacturar) AS validado FROM c_ticket t 
                    INNER JOIN c_notaticket nt ON nt.IdTicket = t.IdTicket
                    INNER JOIN c_estado e ON e.IdEstado = nt.IdEstatusAtencion INNER JOIN k_serviciove ve ON ve.IdNotaTicket = nt.IdNotaTicket
                    WHERE e.FlagValidacion = 1 $where GROUP BY t.IdTicket HAVING conteo != validado";
                    $result = $catalogo->obtenerLista($consulta);
                    $contador = 0;
                    while($rs = mysql_fetch_array($result)){
                        $query = $catalogo->obtenerLista("SELECT e.Nombre, nt.DiagnosticoSol FROM c_notaticket nt INNER JOIN c_estado e ON e.IdEstado = nt.IdEstatusAtencion
                        WHERE e.FlagValidacion = 1 AND nt.IdTicket = " . $rs['IdTicket']);
                        $viaticos = "";
                        while($rs1 = mysql_fetch_array($query)){
                            $viaticos .= "<b>" . $rs1['Nombre'] . "</b>(" . $rs1['DiagnosticoSol'] . "),";
                        }
                    ?>
                    <tr>
                        <td align='center' scope='row'><?php echo $rs['IdTicket']?></td>
                        <td align='center' scope='row'><?php echo $rs['conteo']?></td>
                        <td align='center' scope='row'><?php echo trim($viaticos, ",")?></td>
                        <td align='center' scope='row'><a href='#' onclick="cambiarContenidos('facturacion/form_validar_viaticos.php?id=<?php echo $rs['IdTicket']?>', 'Validar viáticos');
                        return false;" title='Editar'><img src="resources/images/Apply.png" width="24" height="24"/></a></td>
                        <td align="center" scope="row">
                            <input type="checkbox" name="ckViaticos<?php echo $contador?>" id="ckViaticos<?php echo $contador?>" value="<?php echo $rs['IdTicket']?>">
                        </td>
                    </tr>
                    <?php 
                        $contador += 1;
                    }?>
                </tbody>
            </table>
            <input type="hidden" id="contador" name="contador" value="<?php echo $contador?>">
            <?php }?>
        </div>
    </body>
</html>
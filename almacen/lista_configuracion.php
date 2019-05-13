<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "almacen/lista_configuracion.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$controlador = $_SESSION['ruta_controler'] . "Controler_Configuracion.php";

$cabeceras = array("No. Serie", "Modelo", "", "");
$columnas = array("NoSerie", "NoParte", "id_bitacora");
$tabla = "c_bitacora";
$order_by = "id_bitacora";
$alta = "almacen/configuracion.php";

$NoSerie = "";
$Modelo = "";
$Where = "";
if(isset($_POST['NoSerie']) && $_POST['NoSerie']!=""){
    $NoSerie = $_POST['NoSerie'];
    if($Where == ""){
        $Where = " WHERE b.NoSerie = '$NoSerie' ";
    }else{
        $Where .= " AND b.NoSerie = '$NoSerie' ";
    }
}

if(isset($_POST['Modelo']) && $_POST['Modelo']!=""){
    $Modelo = $_POST['Modelo'];
    if($Where == ""){
        $Where = " WHERE e.Modelo LIKE '%$Modelo%' ";
    }else{
        $Where .= " AND e.Modelo LIKE '%$Modelo%' ";
    }
}

if($Where!=""){
    $Where.=" AND b.Activo = 1";
}else{
    $Where = "WHERE b.Activo = 1";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">
            <div class="form-row">
                <?php if ($permisos_grid->getAlta()) { ?>
                    <div class="form-group col-md-1">
                        <button class="btn btn-info" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' ><i class="fal fa-plus"></i></button>
                    </div>
                <?php } ?>
                <div class="form-group col-md-4">
                    <label for="NoSerieConfi">Serie</label>
                    <input class="form-control" type="text" id="NoSerieConfi" name="NoSerieConfi" value="<?php echo $NoSerie; ?>"/>
                </div>
                <div class="form-group col-md-4">
                    <label for="ModeloConfi">Modelo</label>
                    <input class="form-control" type="text" id="ModeloConfi" name="ModeloConfi" value="<?php echo $Modelo; ?>"/>
                </div>
                <div class="form-group col-md-3 p-4">
                    <input type="button" id="ver_equiposConfi" name="ver_equiposConfi" value="Mostrar equipos" class="btn btn-success btn-block" onclick="mostrarEquiposConfiguracion('<?php echo $same_page; ?>','NoSerieConfi','ModeloConfi')"/>
                </div>
            </div>
            <?php if (isset($_POST['mostrar'])) { ?>
                <div class="table-responsive">
                    <table id="tAlmacen" class="tabla_datos m-2">
                    <thead>
                        <tr>
                            <?php
                            for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                            }
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        /* Inicializamos la clase */
                        $catalogo = new Catalogo();
                        $consulta = "SELECT id_bitacora, b.id_solicitud, b.NoSerie, CONCAT(e.Modelo,' / ',b.NoParte) AS NoParte,
                        s.estatus AS estatusSolicitud,t.IdTicket,
                        IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'0',
                        IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,'1',/*Solicitud de retiro*/
                        IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,'2',
                        IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND cal.id_almacen=9 AND (ISNULL(rh.NumReporte) OR rh.Retirado = 0),'3',/*Solicitud retiro aceptada*/
                        IF(rh.Retirado = 1 AND meq.pendiente = 1,'4',/*Retirado*/
                        IF(meq.pendiente = 0,'0',/*Aceptado en almacen*/
                        '0')))))) AS MoverRojo 
                        FROM `c_bitacora` AS b
                        LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte
                        LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora = b.id_bitacora)
                        LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral
                        LEFT JOIN movimientos_equipo AS meq ON meq.id_movimientos = (SELECT MAX(id_movimientos) FROM movimientos_equipo WHERE NoSerie = b.NoSerie AND DATE(Fecha) = DATE(csrg.FechaReporte) AND clave_centro_costo_anterior = csr.ClaveLocalidad)
                        LEFT JOIN reportes_movimientos AS rm ON rm.id_movimientos = meq.id_movimientos
                        LEFT JOIN reportes_historicos AS rh ON rh.NumReporte = rm.id_reportes
                        LEFT JOIN k_almacenequipo AS ke ON ke.NoSerie=b.NoSerie
                        LEFT JOIN c_almacen AS cal ON cal.id_almacen=ke.id_almacen
                        LEFT JOIN c_solicitud AS s ON s.id_solicitud = b.id_solicitud 
                        LEFT JOIN c_ticket AS t ON t.IdTicket = 
                        (SELECT MAX(t2.IdTicket) FROM c_ticket AS t2 
                        LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t2.IdTicket)
                        LEFT JOIN c_pedido AS p ON p.IdTicket = t2.IdTicket
                        WHERE (t2.NoSerieEquipo = b.NoSerie OR p.ClaveEspEquipo = b.NoSerie) AND t2.EstadoDeTicket NOT IN(2,4) AND (ISNULL(nt.IdEstatusAtencion) OR nt.IdEstatusAtencion NOT IN(16,59)))
                        $Where 
                        GROUP BY NoSerie ORDER BY NoSerie DESC;";     
                        
                        $query = $catalogo->obtenerLista($consulta);                           
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<tr>";
                            for ($i = 0; $i < count($columnas) - 1; $i++) {
                                echo "<td align='center' scope='row'>" . $rs[$columnas[$i]] . "</td>";
                            }
                            ?>
                        <td align='center' scope='row'>   
                            <?php if ($permisos_grid->getModificar()) { ?>
                                <?php
                                if ($rs['MoverRojo'] == "1") {
                                    echo "El equipo tiene una solicitud de retiro";
                                } else if($rs['MoverRojo'] == "3"){
                                    echo "Solicitud de retiro aceptada";
                                } else if($rs['MoverRojo'] == "4"){
                                    echo "Falta entrada a almacén";
                                }else if ($rs['estatusSolicitud'] == "1" || $rs['estatusSolicitud'] == "0" || $rs['estatusSolicitud'] == "2" || $rs['estatusSolicitud'] == "4") {
                                    echo "Equipo en solicitud: ".$rs['id_solicitud'];
                                } else if($rs['IdTicket']){
                                    echo "Equipo en ticket abierto: ".$rs['IdTicket'];                                
                                }else {                                                                    
                                    ?>
                                    <a class="h5 text-dark" href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs[$columnas[count($columnas) - 1]]; ?>");
                                            return false;' title='Editar Registro' >
                                            <i class="fal fa-pencil"></i>
                                        <!--img src="resources/images/Modify.png"/-->
                                    <?php }
                                }
                                ?>
                            </a>
                        </td>
                        <?php
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                    </table>
                </div>
        <?php } ?>
        </div>
    </body>
</html>
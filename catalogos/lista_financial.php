<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "catalogos/lista_financial.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$controlador = $_SESSION['ruta_controler'] . "Controler_Financial.php";
$cabeceras = array("Fecha", "Fecha Préstamo","Concepto", "Id Operador", "Importe", "Comentarios", "Folio" ,"Semana", "Estatus", "Tp retención", "Año", "Mes", "Operador Rep", "", "");
$alta = "catalogos/alta_financial.php";
$catalogo = new Catalogo();

$where = "f.Activo = 1";
$IdConductor = "";
$IdConcepto = "";
$IdRetencion = "";
$IdEstatus = "";
$FechaInicioPrestamo = "";
$FechaFinPrestamo = "";
$FechaInicioConcepto = "";
$FechaFinConcepto = "";
$Folio = "";

if(isset($_POST['IdConductor']) && !empty($_POST['IdConductor'])){
    $IdConductor = $_POST['IdConductor'];
    $where .= " AND f.IdOperador = $IdConductor ";
}

if(isset($_POST['IdConcepto']) && !empty($_POST['IdConcepto'])){
    $IdConcepto = $_POST['IdConcepto'];
    $where .= " AND k.IdConcepto = $IdConcepto ";
}

if(isset($_POST['IdTipoRetencion']) && !empty($_POST['IdTipoRetencion'])){
    $IdRetencion = $_POST['IdTipoRetencion'];
    $where .= " AND f.IdTipoRetencion = $IdRetencion ";
}

if(isset($_POST['IdEstatus']) && !empty($_POST['IdEstatus'])){
    $IdEstatus = $_POST['IdEstatus'];
    $where .= " AND f.IdEstatus = $IdEstatus ";
}

if(isset($_POST['FechaInicioPrestamo']) && !empty($_POST['FechaInicioPrestamo'])){
    $FechaInicioPrestamo = $_POST['FechaInicioPrestamo'];
    $where .= " AND f.Fecha >= '$FechaInicioPrestamo' ";
}

if(isset($_POST['FechaFinPrestamo']) && !empty($_POST['FechaFinPrestamo'])){
    $FechaFinPrestamo = $_POST['FechaFinPrestamo'];
    $where .= " AND f.Fecha <= '$FechaFinPrestamo' ";
}

if(isset($_POST['FechaInicioConcepto']) && !empty($_POST['FechaInicioConcepto'])){
    $FechaInicioConcepto = $_POST['FechaInicioConcepto'];
    $where .= " AND k.Fecha >= '$FechaInicioConcepto' ";
}

if(isset($_POST['FechaFinConcepto']) && !empty($_POST['FechaFinConcepto'])){
    $FechaFinConcepto = $_POST['FechaFinConcepto'];
    $where .= " AND k.Fecha <= '$FechaFinConcepto' ";
}

if(isset($_POST['Folio']) && !empty($_POST['Folio'])){
    $Folio = $_POST['Folio'];
    $where .= " AND f.IdPrestamo = $Folio ";
}

?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_financial.js"></script>
    </head>
    <body>
        <div class="principal">
            <table>
                <tr>
                    <td>Chofer</td>
                    <td>
                        <select id="IdConductor" name="IdConductor" class="filtro" style="max-width: 220px;">
                            <option value="">Todos los conductores</option>
                            <?php
                                $consulta = ("SELECT usu.IdUsuario, Loggin, CONCAT(usu.Nombre,' ',usu.ApellidoPaterno,' ',ApellidoMaterno) AS nombre_completo,
                                    correo, per.Nombre AS puesto,(SELECT CASE WHEN usu.Activo = 1 THEN 'Activo' ELSE 'Inactivo' END) AS Activo,
                                    CONCAT (cv.Placas,' - ',cv.Modelo) AS Vehiculo
                                    FROM c_usuario AS usu 
                                    LEFT JOIN c_puesto AS per ON per.IdPuesto = usu.IdPuesto 
                                    LEFT JOIN c_domicilio_usturno AS cd ON cd.IdUsuario=usu.IdUsuario 
                                    LEFT JOIN c_vehiculo AS cv ON cd.IdVehiculo=cv.IdVehiculo 
                                    WHERE usu.IdPuesto=101 OR usu.IdPuesto=108 OR usu.IdPuesto=109  AND usu.Activo = 1
                                    ORDER BY nombre_completo ASC;");
                                $result = $catalogo->obtenerLista($consulta);
                                while($rs = mysql_fetch_array($result)){
                                    $s = "";
                                    if($rs['IdUsuario'] == $IdConductor){
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='".$rs['IdUsuario']."' $s>".$rs['nombre_completo']."</option>";
                                }
                            ?>
                        </select>
                    </td>
                    <td>Concepto</td>
                    <td>
                        <select id="IdConcepto" name="IdConcepto" class="filtro" style="max-width: 220px;">
                            <option value="">Todos los conceptos</option>
                            <?php
                                $result = $catalogo->getListaAlta("c_conceptofinancial", "Concepto");
                                while($rs = mysql_fetch_array($result)){
                                    $s = "";
                                    if($rs['IdConcepto'] == $IdConcepto){
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='".$rs['IdConcepto']."' $s>".$rs['Concepto']."</option>";
                                }
                            ?>
                        </select>
                    </td>
                    <td>Tipo de Retención</td>
                    <td>
                        <select id="IdTipoRetencion" name="IdTipoRetencion" class="filtro" style="max-width: 220px;">
                            <option value="">Todos los tipos de retención</option>
                            <?php
                                $result = $catalogo->getListaAlta("c_tiporetencion", "TipoRetencion");
                                while($rs = mysql_fetch_array($result)){
                                    $s = "";
                                    if($rs['IdTipoRetencion'] == $IdRetencion){
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='".$rs['IdTipoRetencion']."' $s>".$rs['TipoRetencion']."</option>";
                                }
                            ?>
                        </select>
                    </td>
                    <td>Estatus</td>
                    <td>
                        <select id="IdEstatus" name="IdEstatus" class="filtro" style="max-width: 220px;">
                            <option value="">Todos los estatus</option>
                            <?php
                                $result = $catalogo->getListaAlta("c_estatusfinancial", "Estatus");
                                while($rs = mysql_fetch_array($result)){
                                    $s = "";
                                    if(empty($IdEstatus) && !isset($_POST['IdEstatus']) && $rs['IdEstatus'] == "1"){
                                        $s = "selected='selected'";
                                    }
                                    
                                    if($rs['IdEstatus'] == $IdEstatus){
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='".$rs['IdEstatus']."' $s>".$rs['Estatus']."</option>";
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Fecha Inicio Préstamo</td>
                    <td><input type="text" id="FechaInicioPrestamo" name="FechaInicioPrestamo" value="<?php echo $FechaInicioPrestamo; ?>" class="fecha" maxlength="10" style="max-width: 220px;"/></td>
                    <td>Fecha Fin Préstamo</td>
                    <td><input type="text" id="FechaFinPrestamo" name="FechaFinPrestamo" value="<?php echo $FechaFinPrestamo; ?>" class="fecha" maxlength="10" style="max-width: 220px;"/></td>
                    <td>Fecha Inicio Concepto</td>
                    <td><input type="text" id="FechaInicioConcepto" name="FechaInicioConcepto" value="<?php echo $FechaInicioConcepto; ?>" class="fecha" maxlength="10" style="max-width: 220px;"/></td>
                    <td>Fecha Fin Concepto</td>
                    <td><input type="text" id="FechaFinConcepto" name="FechaFinConcepto" value="<?php echo $FechaFinConcepto; ?>" class="fecha" maxlength="10" style="max-width: 220px;"/></td>
                </tr>
                <tr>
                    <td>Folio</td>
                    <td><input type="number" id="Folio" name="Folio" value="<?php echo $Folio; ?>"</td>
                    <td colspan="5"></td>
                    <td>
                        <input type="button" id="buscar_financial" name="buscar_financial" value="Buscar"
                               onclick="buscarFinancial('<?php echo $same_page; ?>','IdConductor', 'IdConcepto', 'IdTipoRetencion', 'IdEstatus', 
                                           'FechaInicioPrestamo', 'FechaFinPrestamo', 'FechaInicioConcepto', 'FechaFinConcepto','Folio'); return false;" class="boton"/>
                    </td>
                </tr>
            </table>
            <?php if ($permisos_grid->getAlta()) { ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>", "Nuevo Financial");' style="float: right; cursor: pointer;" />  
            <?php             
            }
            if(isset($_POST['MostrarTabla'])){
            ?>
                <br/><br/><br/>
                <table id="tAlmacen" class="tabla_datos"  width="100%">
                    <thead>
                        <tr>
                            <?php
                            for ($i = 0; $i < (count($cabeceras)); $i++) {
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                            }
                            ?>                        
                        </tr>
                    </thead>
                    <tbody>
                        <?php                    
                        $query = $catalogo->obtenerLista("SELECT f.IdPrestamo,k.Fecha,f.Fecha AS FechaP, c.Concepto, u.IdUsuario AS IdCred, 
                            (CASE WHEN c.IdTipo = 2 THEN k.Importe ELSE (-1 * k.Importe) END) AS Importe,
                            k.Comentario,CONCAT( k.Semana,': ',DATE_FORMAT(k.FechaSemana,'%Y-%m-%d') ) AS Semana,e.Estatus,r.TipoRetencion,
                            YEAR(k.FechaSemana) AS Anio, MONTH(k.FechaSemana) AS Mes,
                            CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Operador
                            FROM c_financial AS f 
                            LEFT JOIN c_usuario AS u ON u.IdUsuario = f.IdOperador
                            LEFT JOIN c_estatusfinancial AS e ON e.IdEstatus = f.IdEstatus
                            LEFT JOIN c_tiporetencion AS r ON r.IdTipoRetencion = f.IdTipoRetencion
                            LEFT JOIN k_financial AS k ON k.IdFinancial = f.IdPrestamo
                            LEFT JOIN c_conceptofinancial AS c ON c.IdConcepto = k.IdConcepto
                            WHERE $where;");
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<tr>";
                            echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['Fecha'] . "</td>";
                            echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['FechaP'] . "</td>";                        
                            echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['Concepto'] . "</td>";
                            echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['IdCred'] . "</td>";
                            echo "<td width=\"20%\" align=\"center\" scope=\"row\">$" . number_format($rs['Importe'], 2) . "</td>";
                            echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['Comentario'] . "</td>";
                            echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['IdPrestamo'] . "</td>";
                            echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['Semana'] . "</td>";
                            echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['Estatus'] . "</td>";
                            echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['TipoRetencion'] . "</td>";
                            echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['Anio'] . "</td>";
                            echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['Mes'] . "</td>";
                            echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['Operador'] . "</td>";
                            ?> 

                        <td align='center' scope='row'>
                            <?php if ($permisos_grid->getModificar()) { ?>
                                <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs["IdPrestamo"]; ?>");return false;' title='Editar Registro' >
                                    <img src="resources/images/Modify.png"/>
                                </a>
                            <?php } ?>
                        </td>
                        <td align='center' scope='row'> 
                            <?php if ($permisos_grid->getBaja()) { ?>
                                <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs["IdPrestamo"]; ?>", "<?php echo $same_page; ?>");return false;'>
                                    <img src="resources/images/Erase.png"/>
                                </a> 
                            <?php } ?>
                        </td>
                        <?php
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </body>
</html>
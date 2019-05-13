<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/PagoParcialProveedor.class.php");
include_once("../WEB-INF/Classes/FacturaProveedor.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
    
$permisos_grid = new PermisosSubMenu();
$same_page = "facturacion/ReporteFacturacionProveedores.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$pago = new PagoParcialProveedor();
$pago->setId_factura($_GET['factura']);
$factura = new FacturaProveedor();
$factura->getRegistroById($pago->getId_factura());
$totalPagado = 0;

$cabeceras = array("Proveedor", "Importe", "Fecha", "Referencia", "Observaciones", "", "");
$alta = "Alta_Pago_Parcial_Proveedor.php?RFC=" . $_GET['RFC'] . "&factura=" . $_GET['factura'];

$query = $pago->getTabla();

?>

<script type="text/javascript" language="javascript" src="../resources/js/paginas/facturacion/lista_pago_parcial_proveedor.js"></script>   

<?php 

if(!isset($_GET['pagado']) && $permisos_grid->getAlta()){ ?>
    <img class="imagenMouse" src="../resources/images/add.png" title="Nuevo pago parcial" 
         onclick='cambiarContenidos("<?php echo $alta; ?>", "Nuevo pago parcial");' style="float: right; cursor: pointer;" />  
<?php } ?>
<br/><br/><br/>
<table id="tcc" style="width: 100%;">
    <thead>
        <tr>
            <?php
            for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
            echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        /* Inicializamos la clase */
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";            
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['NombreComercial'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['importeFormato'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['fechapago'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['referencia'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['observaciones'] . "</td>";
            $totalPagado += $rs['importe'];
            ?>
        <td align='center' scope='row' width="2%"> 
            <?php if($factura->getTotal() != $totalPagado && $permisos_grid->getModificar()){ ?>
                <a href='#' onclick='cambiarContenidos("Alta_Pago_Parcial_Proveedor.php?RFC=<?php echo $_GET['RFC']; ?>&pago=<?php echo $rs['id_pago'] . "&factura=" . $_GET['factura']; ?>", "Editar Pago Parcial");
                        return false;' title='Editar Pago Parcial' ><img src="../resources/images/Modify.png"/></a>
            <?php } ?>
        </td>
        <td align='center' scope='row' width="2%"> 
            <?php if($permisos_grid->getBaja() && $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 18)){ ?>
            <a href='#' onclick="eliminarPP(<?php echo $rs['id_pago'] ?>);">
                <img src="../resources/images/Erase.png"/>
            </a> 
            <?php } ?>
        </td>                                        
        <?php
        echo "</tr>";
    }
    ?>
    </tbody>
</table>



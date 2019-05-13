<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/PagoParcial.class.php");
include_once("../WEB-INF/Classes/Factura2.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
    
$permisos_grid = new PermisosSubMenu();
$same_page = "facturacion/ReporteFacturacion_net.php?cxc=1";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$pago = new PagoParcial();
$pago->setId_factura($_GET['factura']);
$factura = new Factura();
$factura->setIdFactura($pago->getId_factura());
$factura->getRegistroById();

$cabeceras = array("Receptor", "Importe", "Fecha", "Referencia", "Observaciones", "");

$alta = "Alta_Pago_Parcial.php?RFC=" . $_GET['RFC'] . "&factura=" . $_GET['factura'];

$query = $pago->getTabla(false);    
$cxc = "";
?>

<script type="text/javascript" language="javascript" src="../resources/js/paginas/remision/lista_pago_parcial.js"></script>   

<?php 

if(!isset($_GET['pagado']) && $factura->getFacturaPagada() == 0 && $permisos_grid->getAlta()){ ?>
    <img class="imagenMouse" src="../resources/images/add.png" title="Nuevo pago parcial" 
         onclick='cambiarContenidos("<?php echo $alta; ?>", "Nuevo pago parcial");' style="float: right; cursor: pointer;" />  
<?php } ?>
<br/><br/><br/>
<table id="tcc" style="width: 100%;">
    <thead>
        <tr>
            <?php
            for ($i = 0; $i < (count($cabeceras) - 1); $i++) {
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
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['NombreRazonSocial'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['importe'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['fechapago'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['referencia'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['observaciones'] . "</td>";
            ?>
        <td align='center' scope='row' width="2%"> 
            <?php if($permisos_grid->getModificar() && $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 18)){ ?>
                <a href='#' onclick='cambiarContenidos("Alta_Pago_Parcial.php?RFC=<?php echo $_GET['RFC']; ?>&pago=<?php echo $rs['id_pago'] . "&factura=" . $_GET['factura']."".$cxc; ?>", "Editar Pago Parcial");
                        return false;' title='Editar Pago Parcial' ><img src="../resources/images/Modify.png"/></a>
            <?php } ?>
        </td>
        <td align='center' scope='row' width="2%"> 
            <?php 
            if($permisos_grid->getBaja() && $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 18)){ ?>
                <a href='#' onclick="eliminarPP(<?php echo $rs['id_pago'] ?>,'<?php echo $cxc; ?>');">
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
<?php
if((int)$factura->getCFDI33() == 1){
    echo "<br/>Factura en CFDI 3.3";
}
?>
<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/CatalogoFacturacion.class.php");
include_once("../../Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "Bancos/conciliacionBancaria.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$cabecera1 = array("Tipo conciliacion", "Estatus conciliacion", "Fecha","Tipo","Referencia - Descripcion","Monto");
$cabecera2 = array("Fecha", "Cliente", "Monto","Tipo","Pago - Descripcion","Boton de conciliacion");

$fechaI = "2000-01";
$fechaF = "2100-01";
$limit = "limit 500";
if (isset($_POST['fecha_ini']) && $_POST['fecha_ini'] != ""){
    $fechaI = $_POST['fecha_ini'];
    $limit = null; 
}
if(isset($_POST['fecha_f']) && $_POST['fecha_f'] != ""){
    $fechaF = $_POST['fecha_f'];
    $limit = null;
}
$j = 100;
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/banco/lista_conciliacionBancaria.js"></script>
<div id="mensajes"></div>
<form id="formconciliacionBancaria">
<div id="box" style="width:47%; display: inline-block;">
    <object data="resources/images/BannerBancos.png" width="100%">
    </object>
</div>
<div id="box" style="width:47%; display: inline-block; vertical-align:top;">
    <object data="resources/images/BannerSistema.png" width="100%">
    </object>
</div>
<div id="box" style="width:47%; display: inline-block;">
    <table id="tconciliacionBancaria2">
    <thead>
        <tr>
            <?php
            for ($i = 0; $i < (count($cabecera1)); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabecera1[$i] . "</th>";
            }
        ?>  
        </tr>
    </thead>
    <tbody>
        <?php
            $movimientosBanco = "";
            if($_POST['botconciliados'] == 0){
                $movimientosBanco = "Select * from c_movimientoBancario Where fecha >= '".$fechaI."-01' AND fecha <= '".$fechaF."-01' AND id_pago IS NULL";
            }else{
                $movimientosBanco = "Select * from c_movimientoBancario Where fecha >= '".$fechaI."-01' AND fecha <= '".$fechaF."-01'";
            }
            
            $catalogo = new Catalogo();
            $result = $catalogo->obtenerLista($movimientosBanco);
            while($rs = mysql_fetch_array($result))
            {
                $idMovimiento = $rs['id_movimientoBancario'];
        ?>
        <tr>
            <td><?php if(isset($rs['tipoConciliacion'])){ echo $rs['tipoConciliacion']; }?></td>
            <td>
            <?php
                if(isset($rs['id_pago'])){
                    echo "Si";
                }else{
                    echo "<input type = 'radio' id='radio_$idMovimiento' name='radio_mov' value='$idMovimiento'>No ";
                }
            ?>
            </td>
            <td><?php echo $rs['fecha']; ?></td>
            <td><?php echo $rs['tipo']; ?></td>
            <td><?php echo $rs['descripcion']; ?></td>
            <td><?php echo $rs['monto']; ?></td>
        </tr>
        <?php
            }
        ?>
    </tbody>
  </table>
</div>
<div id="box" style="width:47%; display: inline-block; vertical-align:top;">
  <table id="tconciliacionBancaria3">
    <thead>
        <tr>
        <?php
        for ($i = 0; $i < (count($cabecera2)); $i++) {
            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabecera2[$i] . "</th>";
        }
        ?>  
        </tr>
    </thead>
    <tbody>
        <?php
            $movimientosSistema = "SELECT * FROM c_factura f
                        WHERE f.FacturaPagada = 1 AND f.FechaFacturacion >= '".$fechaI."-01' "
                        . "AND f.FechaFacturacion <= '".$fechaF."-31'";
            $catalogofact = new CatalogoFacturacion();
            $result = $catalogofact->obtenerLista($movimientosSistema);
            while($rs = mysql_fetch_array($result))
            {
                $idMovimiento = $rs['IdFactura'];
                $conciliado = "SELECT id_pago from c_movimientoBancario WHERE fecha >= '".$fechaI."-01' AND fecha <= '".$fechaF."-01' AND id_pago = ".$idMovimiento;
                $result2 = $catalogo->obtenerLista($conciliado);
                if(($_POST['botconciliados'] == 0 && mysql_num_rows($result2) == 0) ||  $_POST['botconciliados'] == 1)
                {
        ?>
        <tr>
            <td><?php echo $rs['FechaFacturacion']?></td>
            <td><?php echo $rs['NombreReceptor']?></td>
            <td><?php echo $rs['Total']?></td>
            <td>Deposito</td>
            <td><?php echo $rs['Observaciones']?></td>
            <td>
            <?php       
                if(mysql_num_rows($result2) == 0){
            ?>
            <input type='button' class='boton' onclick="conciliar('<?php echo $idMovimiento; ?>');return false;" value='Conciliar'/>
            <?php }else{
                    if($_POST['botdesconciliar'] == 1) {?>
            <input type='button' class='boton' onclick="desconciliar('<?php echo $idMovimiento; ?>','Deposito');return false;" value='Desconciliar'/>
            <?php 
                    }
                }?>
            </td>
        </tr>
        <?php
                }
            }
        ?>
        <?php
            $movimientosSistema = "SELECT *,p.NombreComercial FROM c_factura_proveedor f 
                        LEFT JOIN c_proveedor as p ON f.IdEmisor = p.ClaveProveedor 
                        WHERE f.Fecha >= '".$fechaI."-01' "
                        . "AND f.Fecha <= '".$fechaF."-31'";
            $result = $catalogo->obtenerLista($movimientosSistema);
            while($rs = mysql_fetch_array($result))
            {
                $idMovimiento = $rs['IdFacturaProveedor'];
                $conciliado = "SELECT id_pago from c_movimientoBancario WHERE fecha >= '".$fechaI."-01' AND fecha <= '".$fechaF."-01' AND id_pago = ".$idMovimiento;
                $result2 = $catalogo->obtenerLista($conciliado);
                if(($_POST['botconciliados'] == 0 && mysql_num_rows($result2) == 0) ||  $_POST['botconciliados'] == 1)
                {
        ?>
        <tr>
            <td><?php echo $rs['Fecha']?></td>
            <td><?php echo $rs['NombreComercial']?></td>
            <td><?php echo $rs['Total']?></td>
            <td>Retiro</td>
            <td> No hay observaciones </td>
            <td>
            <?php 
                if(mysql_num_rows($result2) == 0){
            ?>
            <input type='button' class='boton' onclick="conciliar('<?php echo $idMovimiento; ?>');return false;" value='Conciliar'/>
            <?php }else{
                    if($_POST['botdesconciliar'] == 1) { ?>
            <input type='button' class='boton' onclick="desconciliar('<?php echo $idMovimiento; ?>','Retiro');return false;" value='Desconciliar'/>
                <?php               
                    } 
                }?>
            </td>
        </tr>
        <?php
                }
            }
        ?>
    </tbody>
  </table>
<div>
</form>



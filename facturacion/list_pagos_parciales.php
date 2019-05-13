<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
ini_set('error_reporting', E_ALL);
error_reporting(-1);
include_once("../WEB-INF/Classes/PagoParcial.class.php");// PCM 12/02/2019
include_once("../WEB-INF/Classes/MultiPagosParciales.class.php");
include_once("../WEB-INF/Classes/Factura.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
  
$pagoP = new PagoParcial();// PCM 12/02/2019
$permisos_grid = new PermisosSubMenu();
$catFac = new CatalogoFacturacion();
$same_page = "facturacion/ReporteFacturacion_net.php?cxc=1";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$facturas = $_GET['factura'];
//echo "Facturas ".$facturas;
$arr_fac = explode("_", $facturas);
$tam = sizeof($arr_fac);
$AbonosFac = $_GET['abonoFacturas'];
$arr_abn_fac = explode("_", $AbonosFac);
$tam = $tam -1;
$compara = "";
$idMPago = "";
$result = "";
//$pago = new PagoParcial();
$pago = new MultiPagosParciales();
//$pago->setId_factura($_GET['factura']);
$pago->setId_factura($arr_fac[0]);
$factura = new Factura_NET();
$factura->getRegistroById($pago->getId_factura());
$pago->setIdmpago($idMPago);
if((int)$factura->getCFDI33() == 1){
    $cabeceras = array("Folio","Receptor", "Importe", "Fecha", "Referencia", "Observaciones","PDF", "", "", "");
}else{
    $cabeceras = array("Folio","Receptor", "Importe", "Fecha", "Referencia", "Observaciones","PDF", "", "");
}

if(!isset($_GET['cxc'])){
    $query = $pago->getTabla($facturas,false);    
    $cxc = "";
}else{
    $query = $pago->getTabla($facturas,true); 
    $alta .= "&cxc=true";
    $cxc = "&cxc=true";
}
$queryCanceladas = $pago->getCanceladas($facturas);
 $alta = "Alta_Multi_Pago.php?RFC=" . $_GET['RFC']."&factura=".$_GET['factura']."".$pagada."".$cxc;
 //$alta = "Alta_Multi_Pago.php?RFC=" . $_GET['RFC']."&factura=".$_GET['factura'];
?>

<script type="text/javascript" language="javascript" src="../resources/js/paginas/facturacion/lista_pagos_parciales.js"></script>   

<?php 

if(!isset($_GET['pagado']) && $factura->getFacturaPagada() == 0 && $permisos_grid->getAlta()){ ?>
    <img class="imagenMouse" src="../resources/images/add.png" title="Nuevo pago parcial" 
         onclick='cambiarContenidos("<?php echo $alta;?>", "Nuevo pago parcial");' style="float: right; cursor: pointer;" />  
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
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['FolioPP']. "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['NombreRazonSocial'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['importe'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['fechapago'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['referencia'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['observaciones'] . "</td>";            
            if(empty($rs['PathPDFPre'])){
            /*echo "<td align='center' width=\"2%\" scope='row'>"
                    . "<a style='cursor:pointer;' onclick=\"timbrarPrePago(".$rs['id_pago'].",'$cxc','$notaR'); return false;\">"
                    . "<img src='../resources/images/pdf_descarga.png' width='32px' height='32px' title=' PrePago'/></a>"
                    . "</td>"; */
                  ?>
             <script>
                    timbrarPrePagoMulti(<?php echo $rs['id_pago']; ?>,'$cxc','<?php echo $notaR; ?>');
                </script>
                  <?php
                  echo "<td align='center' width=\"2%\" scope='row'></td>";
            }else{
                echo "<td align='center' width=\"2%\" scope='row'>"
                    . "<a href='../" . $rs['PathPDFPre'] . "' target='_blank'><img src='../resources/images/pdf_descarga.png' title='PDF PrePago' style='width: 32px; height: 32px;'/></a><br/>"
                    . "</td>";
            }
            if((int)$factura->getCFDI33() == 1){
                if(empty($rs['FolioFiscal'])){
                  /*echo "<td align='center' width=\"2%\" scope='row'>"
                    . "<a href='../" . $rs['PathPDF'] . "' target='_blank'><img src='../resources/images/pdf_descarga.png' title='PDF Pago' style='width: 32px; height: 32px;'/></a><br/>"
                    . "</td>";*/
                    echo "<td align='center' width=\"2%\" scope='row'>"
                    . "<a style='cursor:pointer;' onclick=\"timbrarPago(".$rs['id_pago'].",'$cxc','$notaR'); return false;\">"
                    . "<img src='../resources/images/facturar2.png' width='32px' height='32px' title='Timbrar pago'/></a>"
                    . "</td>"; 
                }else{
                    echo "<td align='center' width=\"2%\" scope='row'>"
                    . "<a href='../" . $rs['PathPDF'] . "' target='_blank'><img src='../resources/images/pdf_descarga.png' title='PDF Pago' style='width: 32px; height: 32px;'/></a><br/>"
                    . "<a href='../" . $rs['PathXML'] . "' target='_blank'><img src='../resources/images/icono_xml.png' title='XML Pago' style='width: 32px; height: 32px;'/></a>"
                    . "</td>";
                    
                    echo "<td align='center' width=\"2%\" scope='row'></td>";
                    
                    echo "<td align='center' width=\"2%\" scope='row'>";
                        ?>
                <a href='#' onclick="cancelarPago(<?php echo $rs['id_pago'] ?>,'<?php echo $_GET['factura']; ?>');">
                    <img src="../resources/images/Erase.png"/>
                </a>
        
                        <?php
                   echo "</td>";
                    
                }
            }
            ?>
    <?php if(empty($rs['FolioFiscal']) && $permisos_grid->getModificar() && ($rs['EstadoFactura'] == "NP" || ($rs['EstadoFactura'] == "P" && $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 18)))){ ?>
        <td align='center' scope='row' width="2%"> 
            <!--<a href='#' onclick='cambiarContenidos("Alta_Pago_Parcial.php?RFC=<?php //echo $_GET['RFC']; ?>&pago=<?php //echo $rs['id_pago'] . "&factura=" . $_GET['factura']."".$cxc; ?>", "Editar Pago Parcial");
                        return false;' title='Editar Pago Parcial' ><img src="../resources/images/Modify.png"/></a>-->
            <a href='#' onclick='cambiarContenidos("Alta_Multi_Pago.php?RFC=<?php echo $_GET['RFC']; ?>&pago=<?php echo $rs['id_pago'] . "&factura=" . $_GET['factura']."".$cxc; ?>", "Editar Multi Pago Parcial");
                        return false;' title='Editar Pago Parcial' ><img src="../resources/images/Modify.png"/></a>
                        </td>
     <?php } ?>
        <?php
        //echo $rs['id_pago']; 
            if(empty($rs['FolioFiscal']) && $permisos_grid->getBaja() && ($rs['EstadoFactura'] == "NP" || ($rs['EstadoFactura'] == "P" && $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 18)))){ ?>
        <td align='center' scope='row' width="2%"> 
            <a href='#' onclick="eliminarPP(<?php echo $rs['id_pago']; ?>,'<?php echo $cxc; ?>');">
                    <img src="../resources/images/Erase.png"/>
                </a> 
        </td>
        <?php } ?>
        <?php
        echo "</tr>";
    }
    while ($row = mysql_fetch_array($queryCanceladas)) {
        //*************************************** Modificacion de codigo        *JT 18/10/18
            echo "<tr>";            
            echo "<td align='center' width=\"2%\" scope='row'>" . $row['FolioPP']. "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $row['NombreRazonSocial'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $row['importe'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $row['fechapago'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $row['referencia'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $row['observaciones'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>CANCELADA</td>";
            echo "<td align='center' width=\"2%\" scope='row'></td>";
            echo "<td align='center' width=\"2%\" scope='row'></td>";
            echo "<td align='center' width=\"2%\" scope='row'></td>";
            echo "</tr>";
        //**************************************************************************************
    }
    ?>
    </tbody>
</table><br>
<?php
// PCM 12/02/2019
array_pop($arr_fac);
foreach ($arr_fac as $valor => $fac) {
	//$pago->setId_factura($_GET['factura']);
	$pagoP->setId_factura($fac);
	//$factura = new Factura_NET();
	$factura->getRegistroById($pagoP->getId_factura());
	
     //echo $fac;    
    //$pagoP->setId_factura($fac);
    
    if(!isset($_GET['cxc'])){
        $query = $pagoP->getTabla(false);    
        $cxc = "";
    }else{
        $query = $pagoP->getTabla(true); 
        $alta .= "&cxc=true";
        $cxc = "&cxc=true";
    }

    $queryCanceladas = $pagoP->getCanceladas();
    $row_can = mysql_num_rows($queryCanceladas);
    $row_cnt = mysql_num_rows($query);
    
    if ($row_cnt == 0 && $row_can == 0) {
    	//echo "vacio";
    }else{
  		//echo "algo";
  		echo "<h2 class=\"titulos\"> Pago Parcial Factura ".$factura->getFolio()."</h2>";
    ?>
    <table id="tcc<?php echo $valor;?>" style="width: 100%;">
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
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['Serie'] .'-'.$rs['FolioP']. "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['NombreRazonSocial'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['importe'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['fechapago'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['referencia'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['observaciones'] . "</td>";
            if(empty($rs['PathPDFPre'])){
            /*echo "<td align='center' width=\"2%\" scope='row'>"
                    . "<a style='cursor:pointer;' onclick=\"timbrarPrePago(".$rs['id_pago'].",'$cxc','$notaR'); return false;\">"
                    . "<img src='../resources/images/pdf_descarga.png' width='32px' height='32px' title=' PrePago'/></a>"
                    . "</td>"; */
                  ?>
                <script>
                    timbrarPrePago(<?php echo $rs['id_pago']; ?>,'$cxc','<?php echo $notaR; ?>');
                </script>
                  <?php
                  echo "<td align='center' width=\"2%\" scope='row'></td>";
            }else{
                echo "<td align='center' width=\"2%\" scope='row'>"
                    . "<a href='../" . $rs['PathPDFPre'] . "' target='_blank'><img src='../resources/images/pdf_descarga.png' title='PDF PrePago' style='width: 32px; height: 32px;'/></a><br/>"
                    . "</td>";
            }
            if((int)$factura->getCFDI33() == 1){
                if(empty($rs['FolioFiscal'])){
                  /*echo "<td align='center' width=\"2%\" scope='row'>"
                    . "<a href='../" . $rs['PathPDF'] . "' target='_blank'><img src='../resources/images/pdf_descarga.png' title='PDF Pago' style='width: 32px; height: 32px;'/></a><br/>"
                    . "</td>";*/
                    echo "<td align='center' width=\"2%\" scope='row'>"
                    . "<a style='cursor:pointer;' onclick=\"timbrarPago(".$rs['id_pago'].",'$cxc','$notaR'); return false;\">"
                    . "<img src='../resources/images/facturar2.png' width='32px' height='32px' title='Timbrar pago'/></a>"
                    . "</td>"; 
                }else{
                    echo "<td align='center' width=\"2%\" scope='row'>"
                    . "<a href='../" . $rs['PathPDF'] . "' target='_blank'><img src='../resources/images/pdf_descarga.png' title='PDF Pago' style='width: 32px; height: 32px;'/></a><br/>"
                    . "<a href='../" . $rs['PathXML'] . "' target='_blank'><img src='../resources/images/icono_xml.png' title='XML Pago' style='width: 32px; height: 32px;'/></a>"
                    . "</td>";
                    
                    echo "<td align='center' width=\"2%\" scope='row'></td>";
                    
                    echo "<td align='center' width=\"2%\" scope='row'>";
                        ?>
                <a href='#' onclick="cancelarPago(<?php echo $rs['id_pago'] ?>,<?php echo $_GET['factura']; ?>);">
                    <img src="../resources/images/Erase.png"/>
                </a>
        
                        <?php
                   echo "</td>";
                    
                }
            }
            ?>
    <?php if(empty($rs['FolioFiscal']) && $permisos_grid->getModificar() && ($rs['EstadoFactura'] == "NP" || ($rs['EstadoFactura'] == "P" && $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 18)))){ ?>
        <td align='center' scope='row' width="2%"> 
            <a href='#' onclick='cambiarContenidos("Alta_Pago_Parcial.php?RFC=<?php echo $_GET['RFC']; ?>&pago=<?php echo $rs['id_pago'] . "&factura=" . $_GET['factura']."".$cxc; ?>", "Editar Pago Parcial");
                        return false;' title='Editar Pago Parcial' ><img src="../resources/images/Modify.png"/></a>
                        </td>
     <?php } ?>
        <?php 
            if(empty($rs['FolioFiscal']) && $permisos_grid->getBaja() && ($rs['EstadoFactura'] == "NP" || ($rs['EstadoFactura'] == "P" && $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 18)))){ ?>
        <td align='center' scope='row' width="2%"> 
            <a href='#' onclick="eliminarPP(<?php echo $rs['id_pago'] ?>,'<?php echo $cxc; ?>');">
                    <img src="../resources/images/Erase.png"/>
                </a> 
        </td>
        <?php } ?>
        <?php
        echo "</tr>";
    }
    while ($row = mysql_fetch_array($queryCanceladas)) {
        //*************************************** Modificacion de codigo        *JT 18/10/18
            echo "<tr>";            
            echo "<td align='center' width=\"2%\" scope='row'>" . $row['Serie'] .'-'.$rs['FolioP']. "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $row['NombreRazonSocial'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $row['importe'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $row['fechapago'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $row['referencia'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $row['observaciones'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>CANCELADA</td>";
            echo "<td align='center' width=\"2%\" scope='row'></td>";
            echo "<td align='center' width=\"2%\" scope='row'></td>";
            echo "<td align='center' width=\"2%\" scope='row'></td>";
            echo "</tr>";
        //**************************************************************************************
    }
    ?>
    </tbody>
</table><br>
<?php
	}
	
}
if((int)$factura->getCFDI33() == 1){
	    echo "<br/>Factura en CFDI 3.3";
	}
?>
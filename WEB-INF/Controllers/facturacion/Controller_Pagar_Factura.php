<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/ReporteFacturacion.class.php");
include_once("../../Classes/Factura.class.php");
if(isset($_POST['marcarPagadas'])){
    $factura = new Factura_NET();
    if($factura->marcarPagadas(true)){
        echo "Las facturas fueron marcadas como pagadas exitosamente";
    }else{
        echo "No todas las facturas pudieron ser marcadas como pagadas";
    }
}else{
    if(isset($_POST['folio']) && $_POST['folio']!=""){
        $reporte = new ReporteFacturacion();
        $reporte->setFolio($_POST['folio']);
        $factura = new Factura_NET();
        $factura->getRegistroByFolio($_POST['folio']);
        if($reporte->CambiarPagado($_POST['tipo'])){
            echo "Se ha cambiado el status correctamente";                    
        }else{
            echo "Verifique que el folio exista";
        }
    }else{
        echo "No se recibió el folio";
    }
}
?>

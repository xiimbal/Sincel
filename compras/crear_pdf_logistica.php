<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include("../WEB-INF/Classes/Catalogo.class.php");

include '../WEB-INF/Classes/Plantilla_PDF.Class.php';

$catalogo = new Catalogo();

$sql="SELECT oc.NoPedido, al.nombre_almacen,  koc.Cantidad, cda.Ciudad, oc.FechaOrdenCompra, oc.Notas, me.Nombre
	FROM k_orden_compra AS koc
	RIGHT JOIN c_orden_compra AS oc ON koc.IdOrdenCompra = oc.Id_orden_compra 
	INNER JOIN c_almacen AS al ON oc.IdAlmacen = al.id_almacen 
	INNER JOIN c_domicilio_almacen AS cda ON al.id_almacen = cda.IdAlmacen
	INNER JOIN c_mensajeria AS me ON me.IdMensajeria = oc.Transportista ";

$pdf = new PDF('L');
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFillColor(232, 232, 232);
$pdf->SetFont('Arial','B',15);

$pdf->Cell(28, 6, 'No', 1, 0,'C', 1);
$pdf->Cell(40, 6, 'Tienda', 1, 0, 'C', 1);
$pdf->Cell(20, 6, 'Bultos', 1, 0, 'C', 1);
$pdf->Cell(70, 6, 'Ciudad destino', 1, 0, 'C', 1);
$pdf->Cell(30, 6, 'Fecha', 1, 0, 'C', 1);
$pdf->Cell(50, 6, 'Notas', 1, 0, 'C', 1);
$pdf->Cell(40, 6, 'Con. Vecu.', 1, 1, 'C', 1);


if (isset($empresa)) 
{
	$catalogo->setEmpresa($empresa);
} 

$pdf->SetFont('Arial','B',12);

$query = $catalogo->obtenerLista($sql);


while ($fila=mysql_fetch_array($query))

{

	$pdf->Cell(28, 6, $fila['NoPedido'], 1, 0,'C', 1);
	$pdf->Cell(40, 6, $fila['nombre_almacen'], 1, 0,'C', 1);
	$pdf->Cell(20, 6, $fila['Cantidad'], 1, 0,'C', 1);
	$pdf->Cell(70, 6, $fila['Ciudad'], 1, 0,'C', 1);
	$pdf->Cell(30, 6, $fila['FechaOrdenCompra'], 1, 0,'C', 1);
	$pdf->Cell(50, 6, $fila['Notas'], 1, 0,'C', 1);
	$pdf->Cell(40, 6, $fila['Nombre'], 1, 1,'C', 1);
}

//$pdf->AddPage();

$pdf->Output();


?>
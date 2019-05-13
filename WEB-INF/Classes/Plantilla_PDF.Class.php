<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");

require '../PDF/FPDF/fpdf.php';

/**
 * 
 */
class PDF extends FPDF
{
	
	function Header()
	{
		
		$this->SetFont('Arial','B',15);
		$this->Cell (30);
		$this->Cell(220, 10, 'Reporte Logistica', 0,0,'C');

		$this->Ln(20);
	}

	function Footer()
	{
		$this->SetY(-15);
		$this->SetFont('Arial', 'I', 8);
		$this->Cell (0, 10, 'Pagina '. $this->PageNo() .'/{nb}', 0, 0,'C');
	}
}

?>
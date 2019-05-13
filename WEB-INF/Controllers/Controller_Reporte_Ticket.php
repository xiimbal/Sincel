<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_GET['idMovimiento'])) {
    header("Location: ../../../index.php");
}
$idMovimiento = $_GET['idMovimiento'];
$hoy = date("d-m-Y");
require_once('../Classes/PHPExcel/IOFactory.php');
require_once('../Classes/PHPExcel.php');
include_once("../Classes/Catalogo.class.php");

function cellColor($objPHPExcel, $cells, $color) {
    $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()
            ->applyFromArray(array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => $color)
    ));
}

function getStyle($bold, $color, $size, $name, $cursive) {
    $styleArray = array(
        'font' => array(
            'bold' => $bold,
            'italic' => $cursive,
            'color' => array('rgb' => $color),
            'size' => $size,
            'name' => $name
        ),
        'alignment' => array(
            'wrap' => true,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    ));
    return $styleArray;
}

function getStyleBorder() {
    $styleArray = array(
        'borders' => array(
            'outline' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('argb' => '000000'),
            ),
        ),
    );
    return $styleArray;
}

$objPHPExcel = new PHPExcel();
$catalogo = new Catalogo();

$consulta = "SELECT nt.IdNotaTicket,t.IdTicket,t.FechaHora,c.NombreRazonSocial,t.DescripcionReporte,nt.DiagnosticoSol,
nt.FechaHora AS fechaNota,mc.CantidadMovimiento,cc.Modelo,a.nombre_almacen,t.NombreCentroCosto,c.RFC,d.Calle,d.Colonia,d.Delegacion,d.Estado,d.CodigoPostal,
f.RazonSocial,CONCAT(f.Telefono) AS telefono,ct.Nombre AS contacto,cc.Descripcion,
CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono)AS domicilioFiscal
FROM c_notaticket nt,c_ticket t,movimiento_componente mc, c_cliente c,c_componente cc,c_domicilio d,c_contacto ct,c_datosfacturacionempresa f,c_almacen a
WHERE nt.IdTicket=t.IdTicket
AND nt.IdNotaTicket=mc.IdNotaTicket
AND t.ClaveCliente=c.ClaveCliente
AND d.ClaveEspecialDomicilio=t.ClaveCliente
AND c.ClaveCliente=ct.ClaveEspecialContacto
AND c.IdDatosFacturacionEmpresa=f.IdDatosFacturacionEmpresa
AND cc.NoParte=mc.NoParteComponente
AND mc.IdAlmacenAnterior=a.id_almacen
AND mc.IdMovimiento='" . $idMovimiento . "'
GROUP BY mc.IdMovimiento 
";
$query = $catalogo->obtenerLista($consulta);
// Establecer propiedades
$objPHPExcel->getProperties()
        ->setCreator("")
        ->setLastModifiedBy("")
        ->setTitle("Documento Excel")
        ->setSubject("Documento Excel")
        ->setDescription("Reporte de cambio de equipo")
        ->setKeywords("Excel Office 2007 openxml php")
        ->setCategory("Reportes");
$styleArray = getStyleBorder(); //Estilo para aplicar bordes
// Agregar Informacion
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'FORMATO DE MOVIMIENTOS  SCG - ODF')->mergeCells('A1:H2')
        ->setCellValue('A4', 'SE FACTURA POR ')->mergeCells('A4:B5')
        //->setCellValue('C4', 'SERVICIOS CORPORATIVOS GENESIS')->mergeCells('C4:E5')
        ->setCellValue('A7', 'TIPO DE MOVIMIENTO')->mergeCells('A7:B8')
        ->setCellValue('G7', 'FECHA')->mergeCells('G7:G8');

$i = 22;
$domicilio_fiscal = "";
$razon = "";
while ($rs = mysql_fetch_array($query)) {
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H7', $hoy)->mergeCells('H7:I8');
    $mensaje_destino = "DESCRIPCION DE EQUIPOS - ACCESORIOS - CONSUMIBLES";
    $mensaje_origen = "DATOS GENERALES";
    $domicilio_fiscal = $rs['domicilioFiscal']; //$rs['facturacion'];
    $nombre_origen = $rs['NombreRazonSocial'];
    $razon = $rs['RazonSocial'];
    /* Cliente anterior */
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('C7', 'SALIDA DE ALMACÉN:' . $rs['nombre_almacen'] . ' ')->mergeCells('C7:E8')
            ->setCellValue('A10', $mensaje_origen)->mergeCells('A10:I10')
            ->setCellValue('A11', 'NOMBRE ó RAZON SOCIAL')->mergeCells('A11:B11')
            ->setCellValue('C11', $nombre_origen)->mergeCells('C11:I11')
            ->setCellValue('A12', 'CONTACTO COMERCIAL')->mergeCells('A12:B12')
            ->setCellValue('C12', $rs['contacto'])->mergeCells('C12:F12')
            ->setCellValue('G12', 'RFC')
            ->setCellValue('H12', $rs['RFC'])->mergeCells('H12:I12');
    /* Direccion cliente anterior */
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A14', 'CALLE Y NÚMERO')->mergeCells('A14:B14')
            ->setCellValue('C14', $rs['Calle'])->mergeCells('C14:I14')
            ->setCellValue('A15', 'COLONIA')->mergeCells('A15:B15')
            ->setCellValue('C15', $rs['Colonia'])->mergeCells('C15:I15')
            ->setCellValue('A16', 'DELEGACION ó MUNICIPIO')->mergeCells('A16:B16')
            ->setCellValue('C16', $rs['Delegacion'])->mergeCells('C16:I16')
            ->setCellValue('A17', 'CIUDAD / ESTADO')->mergeCells('A17:B17')
            ->setCellValue('C17', $rs['Estado'])->mergeCells('C17:I17')
            ->setCellValue('A18', 'TELEFONO Y EXTENSION')->mergeCells('A18:B18')
            ->setCellValue('C18', $rs['telefono'])->mergeCells('G18:I18')
            ->setCellValue('E18', 'C. POSTAL')->mergeCells('E18:F18')
            ->setCellValue('G18', $rs['CodigoPostal'])->mergeCells('G18:I18');
    /* Cliente NUEVO */
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A20', $mensaje_destino)->mergeCells('A20:I20')
            ->setCellValue('A21', 'CANTIDAD')->mergeCells('A21:B21')
            ->setCellValue('C21', 'MODELO')->mergeCells('C21:D21')
            ->setCellValue('E21', 'DESCRIPCIÓN')->mergeCells('E21:G21')
            ->setCellValue('H21', 'No SERIE')->mergeCells('H21:I21');
    // $cantidad = "1";
    // if($rs['tipo']=="1"){
    //$cantidad = $rs['Cantidad'];
    //}
    /* Agregamos los equipos, componente */
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $i, $rs['CantidadMovimiento'])->mergeCells('A' . $i . ':B' . $i)
            ->setCellValue('C' . $i, $rs['Modelo'])->mergeCells('C' . $i . ':D' . $i)
            ->setCellValue('E' . $i, $rs['Descripcion'])->mergeCells('E' . $i . ':G' . $i)
            ->setCellValue('H' . $i, '')->mergeCells('H' . $i . ':I' . $i);
    $i++;
    /* Direccion cliente NUEVO */
    /* $objPHPExcel->setActiveSheetIndex(0)
      ->setCellValue('A24', 'CALLE Y NÚMERO')->mergeCells('A24:B24')
      ->setCellValue('C24', "")->mergeCells('C24:I24')
      ->setCellValue('A25', 'COLONIA')->mergeCells('A25:B25')
      ->setCellValue('C25', "")->mergeCells('C25:I25')
      ->setCellValue('A26', 'DELEGACION ó MUNICIPIO')->mergeCells('A26:B26')
      ->setCellValue('C26', "")->mergeCells('C26:I26')
      ->setCellValue('A27', 'CIUDAD / ESTADO')->mergeCells('A27:B27')
      ->setCellValue('C27', "")->mergeCells('C27:I27')
      ->setCellValue('A28', 'TELEFONO Y EXTENSION')->mergeCells('A28:B28')
      ->setCellValue('E28', 'TELEFONO')->mergeCells('E28:F28')
      ->setCellValue('G28', "")->mergeCells('G28:I28');
      /* Datos generales */
    /* $objPHPExcel->setActiveSheetIndex(0)
      ->setCellValue('A30', 'DATOS GENERALES')->mergeCells('A30:I30')
      ->setCellValue('A31', 'CONTADOR NEGRO')->mergeCells('A31:B31')
      ->setCellValue('C31', "")->mergeCells('C31:F31')
      ->setCellValue('A32', 'CONTADOR COLOR')->mergeCells('A32:B32')
      ->setCellValue('C32', "")->mergeCells('C32:F32')
      ->setCellValue('A33', 'NIVEL DE TONER NEGRO')->mergeCells('A33:B33')
      ->setCellValue('C33', "")->mergeCells('C33:F33')
      ->setCellValue('G31', 'NIVEL DE TONER CYAN')->mergeCells('G31:H31')
      ->setCellValue('I31', "")
      ->setCellValue('G32', 'NIVEL DE TONER MAGENTA')->mergeCells('G32:H32')
      ->setCellValue('I32', "")
      ->setCellValue('G33', 'NIVEL DE TONER AMARILLO')->mergeCells('G33:H33')
      ->setCellValue('I33', ""); */
}

/* Observaciones */
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . ($i + 1), 'OBSERVACIONES')->mergeCells('A' . ($i + 1) . ':I' . ($i + 1))->mergeCells('A' . ($i + 2) . ':I' . ($i + 4))
        ->setCellValue('A' . ($i + 5) . '', 'CLIENTE')->mergeCells('A' . ($i + 5) . ':C' . ($i + 5))
        ->setCellValue('D' . ($i + 5) . '', 'EJECUTIVO DE CUENTAS')->mergeCells('D' . ($i + 5) . ':F' . ($i + 5))
        ->setCellValue('G' . ($i + 5) . '', 'AUTORIZACIÓN')->mergeCells('G' . ($i + 5) . ':I' . ($i + 5))
//        ->setCellValue('A' . ($i + 6) . '', 'FIRMA')->mergeCells('A' . ($i + 6) . ':A' . ($i + 7))
//        ->setCellValue('D' . ($i + 6) . '', 'NOMBRE')->mergeCells('D' . ($i + 6) . ':D' . ($i + 7))
//        ->setCellValue('G' . ($i + 6) . '', 'NOMBRE')->mergeCells('G' . ($i + 6) . ':G' . ($i + 7))
        ->setCellValue('G' . ($i + 8) . '', 'LIC. CLAUDIA MORENO')->mergeCells('G' . ($i + 8) . ':I' . ($i + 10))
        ->setCellValue('A' . ($i + 11) . '', 'FIRMA')->mergeCells('A' . ($i + 11) . ':C' . ($i + 11) . '')
        ->setCellValue('D' . ($i + 11) . '', 'FIRMA')->mergeCells('D' . ($i + 11) . ':F' . ($i + 11) . '')
        ->setCellValue('G' . ($i + 11) . '', 'FIRMA')->mergeCells('G' . ($i + 11) . ':I' . ($i + 11) . '')
        ->setCellValue('A' . ($i + 13), $domicilio_fiscal)->mergeCells('A' . ($i + 13) . ':K' . ($i + 13))
        ->setCellValue('C4', $razon)->mergeCells('C4:E5');
/* Firmas */
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B' . ($i + 6) . ':C' . ($i + 7) . '')->mergeCells('E' . ($i + 6) . ':F' . ($i + 7) . '')
        ->mergeCells('H' . ($i + 6) . ':I' . ($i + 7) . '')->mergeCells('A' . ($i + 8) . ':C' . ($i + 10) . '')->mergeCells('D' . ($i + 8) . ':F' . ($i + 10) . '')
        ->mergeCells('G' . ($i + 8) . ':I' . ($i + 10) . '');

//Ponemos los colores de las celdas
cellColor($objPHPExcel, 'A11:A12', 'C0C0C0'); //Cliente viejo
cellColor($objPHPExcel, 'A14:A18', 'C0C0C0'); //Localidad vieja

cellColor($objPHPExcel, 'G7', 'C0C0C0'); //Fecha
cellColor($objPHPExcel, 'G12', 'C0C0C0'); //RFC nuevo
cellColor($objPHPExcel, 'E18', 'C0C0C0'); //Telefono nuevo

cellColor($objPHPExcel, 'A4', '404040'); //Se factura por
cellColor($objPHPExcel, 'A7', '404040'); //Tipo de movimiento
cellColor($objPHPExcel, 'A10', '404040'); //Cliente anterior
cellColor($objPHPExcel, 'A13:I13', '404040'); //
cellColor($objPHPExcel, 'A20', '404040'); //Cliente anterior
//cellColor($objPHPExcel, 'A30:I30', '404040'); //Datos generales
cellColor($objPHPExcel, 'A' . ($i + 1), '404040'); //Observaciones
//cellColor($objPHPExcel, 'A39:I39', '404040'); //Cliente anterior
//Estilos de letras
$styleArray = getStyle(true, "000000", 18, "Calibri", false);
$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
$styleArray = getStyle(true, "FFFFFF", 10, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($styleArray);
$styleArray = getStyle(true, "000000", 12, "Arial", false);

$objPHPExcel->getActiveSheet()->getStyle('C7')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('H7')->applyFromArray($styleArray);
$styleArray = getStyle(true, "FFFFFF", 10, "Arial", true);
$objPHPExcel->getActiveSheet()->getStyle('A10')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A20')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A' . ($i + 1))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A30:I30')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A34')->applyFromArray($styleArray);
/* $objPHPExcel->getActiveSheet()->getStyle('A39:I39')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('A39:I39')->applyFromArray($styleArray); */
$styleArray = getStyle(true, "000000", 9, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('C4')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A11:A12')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A14:A18')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A31:A33')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G31:G33')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G7')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G12')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('E18')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G22')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('E28')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A40')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('D40')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G40')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A45')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('D45')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G45')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G42')->applyFromArray($styleArray);
$styleArray = getStyle(true, "800000", 6, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A' . ($i + 13))->applyFromArray($styleArray);
//Borders
$styleArray = getStyleBorder();
$objPHPExcel->getActiveSheet()->getStyle('C4:E5')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C7:E8')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G7:G8')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('H7:I8')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A11:B11')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C11:I11')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A12:B12')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C12:F12')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G12')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('H12:I12')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A14:B14')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A15:B15')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A16:B16')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A17:B17')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A18:B18')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C14:I14')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C15:I15')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C16:I16')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C17:I17')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C18:D18')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('E18:F18')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G18:I18')->applyFromArray($styleArray);

$objPHPExcel->getActiveSheet()->getStyle('A20:I20')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A21:B21')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C21:D21')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('E21:G21')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('H21:I21')->applyFromArray($styleArray);
/* Equipos y componentes */
for ($fila = 22; $fila < $i; $fila++) {
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':B' . $fila . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C' . $fila . ':D' . $fila . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('E' . $fila . ':G' . $fila . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('H' . $fila . ':I' . $fila . '')->applyFromArray($styleArray);
}
$objPHPExcel->getActiveSheet()->getStyle('A' . ($i + 1) . ":I" . ($i + 1))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A' . ($i + 2) . ":I" . ($i + 4))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A' . ($i + 5) . ":C" . ($i + 5))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('D' . ($i + 5) . ":F" . ($i + 5))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G' . ($i + 5) . ":I" . ($i + 5))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A' . ($i + 6) . ":C" . ($i + 7))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('D' . ($i + 6) . ":F" . ($i + 7))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G' . ($i + 6) . ":I" . ($i + 7))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A' . ($i + 8) . ":C" . ($i + 10))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('D' . ($i + 8) . ":F" . ($i + 10))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G' . ($i + 8) . ":I" . ($i + 10))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A' . ($i + 11) . ":C" . ($i + 11))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('D' . ($i + 11) . ":F" . ($i + 11))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G' . ($i + 11) . ":I" . ($i + 11))->applyFromArray($styleArray);

/* $objPHPExcel->getActiveSheet()->getStyle('A21:B21')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('C21:I21')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('A22:B22')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('C22:F22')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('G22')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('H22:I22')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('A24:B24')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('A25:B25')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('A26:B26')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('A27:B27')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('A28:B28')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('C24:I24')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('C25:I25')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('C26:I26')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('C27:I27')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('C28:D28')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('E28:F28')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('G28:I28')->applyFromArray($styleArray);

  $objPHPExcel->getActiveSheet()->getStyle('A31:B31')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('A32:B32')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('A33:B33')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('C31:F31')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('C32:F32')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('C33:F33')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('G31:H31')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('G32:H32')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('G33:H33')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('I31')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('I32')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('I33')->applyFromArray($styleArray);

  $objPHPExcel->getActiveSheet()->getStyle('A35:I38')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('A40:A41')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('D40:D41')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('G40:G41')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('A45:C45')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('D45:F45')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('G45:I45')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('G42:I44')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('D42:F44')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('A42:C44')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('B40:C41')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('E40:F41')->applyFromArray($styleArray);
  $objPHPExcel->getActiveSheet()->getStyle('H40:I41')->applyFromArray($styleArray); */

/* Ajustar el width de las columnas */
//$objPHPExcel->getActiveSheet()->getColumnDimension('A:B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(23);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(57);
//$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

/* Poner imagen */
$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
$objDrawing->setName("name");
$objDrawing->setDescription("Description");
$objDrawing->setPath('../../resources/images/kyocera_reporte.png');
$objDrawing->setCoordinates('H1');
$objDrawing->setOffsetX(1);
$objDrawing->setOffsetY(5);

// Renombrar Hoja
$objPHPExcel->getActiveSheet()->setTitle('FORMATO DE ENTREGA');

// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
$objPHPExcel->setActiveSheetIndex(0);

// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ReporteMovimiento.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>

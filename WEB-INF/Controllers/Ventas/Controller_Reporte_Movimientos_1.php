<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_GET['idMov'])) {
    header("Location: ../../../index.php");
}
require_once('../../Classes/PHPExcel/IOFactory.php');
require_once('../../Classes/PHPExcel.php');
include_once("../../Classes/Catalogo.class.php");

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

$consulta = "SELECT 
meq.id_movimientos, meq.tipo_movimiento, meq.NoSerie, meq.pendiente, meq.Fecha, 
CONCAT(c1.ClaveCliente,' - ',c1.NombreRazonSocial) AS cliente_anterior, c1.RFC AS rfc_anterior, con1.Nombre AS contacto_anterior, con1.Telefono AS telefono_anterior,
CONCAT(cc1.ClaveCentroCosto, ' - ' ,cc1.Nombre) AS centro_anterior, c2.RFC AS rfc_nuevo, con2.Nombre AS contacto_nuevo, con2.Telefono AS telefono_nuevo,
CONCAT(c2.ClaveCliente,' - ',c2.NombreRazonSocial) AS cliente_nuevo,
CONCAT(cc2.ClaveCentroCosto, ' - ' ,cc2.Nombre) AS centro_nuevo,
CONCAT(dom1.Calle,' No. Ext. ',dom1.NoExterior, ' No. Int. ',dom1.NoInterior) AS calle_anterior, 
dom1.Colonia AS colonia_anterior, dom1.Delegacion AS delegacion_anterior, dom1.Ciudad AS ciudad_anterior,
CONCAT(dom2.Calle,' No. Ext. ',dom2.NoExterior, ' No. Int. ',dom2.NoInterior) AS calle_nuevo, 
dom2.Colonia AS colonia_nuevo, dom2.Delegacion AS delegacion_nuevo, dom2.Ciudad AS ciudad_nuevo,
alm1.nombre_almacen AS almacen_anterior, alm2.nombre_almacen AS almacen_nuevo,
cl.ContadorBNML, cl.ContadorBNPaginas, cl.ContadorColorML, cl.ContadorColorPaginas, cl.NivelTonAmarillo, cl.NivelTonCian, cl.NivelTonMagenta, cl.NivelTonNegro
FROM `movimientos_equipo` AS meq 
LEFT JOIN c_cliente AS c1 ON c1.ClaveCliente = meq.clave_cliente_anterior
LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = meq.clave_cliente_nuevo
LEFT JOIN c_centrocosto AS cc1 ON cc1.ClaveCentroCosto = meq.clave_centro_costo_anterior
LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = meq.clave_centro_costo_nuevo
LEFT JOIN c_contacto AS con1 ON con1.ClaveEspecialContacto = cc1.ClaveCentroCosto
LEFT JOIN c_contacto AS con2 ON con2.ClaveEspecialContacto = cc2.ClaveCentroCosto
LEFT JOIN c_domicilio AS dom1 ON dom1.ClaveEspecialDomicilio = cc1.ClaveCentroCosto
LEFT JOIN c_domicilio AS dom2 ON dom2.ClaveEspecialDomicilio = cc2.ClaveCentroCosto
LEFT JOIN c_almacen AS alm1 ON alm1.id_almacen = meq.almacen_anterior
LEFT JOIN c_almacen AS alm2 ON alm2.id_almacen = meq.almacen_nuevo
LEFT JOIN c_lectura AS cl ON cl.IdLectura = meq.id_lectura
WHERE meq.id_movimientos = " . $_GET['idMov'] . ";";

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
        ->setCellValue('C4', 'SERVICIOS CORPORATIVOS GENESIS')->mergeCells('C4:E5')
        ->setCellValue('A7', 'TIPO DE MOVIMIENTO')->mergeCells('A7:B8')
        ->setCellValue('C7', 'MOVIMIENTO DE EQUIPO')->mergeCells('C7:E8')
        ->setCellValue('G7', 'FECHA')->mergeCells('G7:G8');

if ($rs = mysql_fetch_array($query)) {
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H7', $rs['Fecha'])->mergeCells('H7:I8');
    $mensaje_destino = "";
    $mensaje_origen = "";
    $nombre_destino = "";
    $nombre_origen = "";
    switch ($rs['tipo_movimiento']){
        case "1":
            $mensaje_origen = "CLIENTE ANTERIOR";
            $mensaje_destino = "CLIENTE NUEVO";
            $nombre_origen = $rs['cliente_anterior'];
            $nombre_destino = $rs['cliente_nuevo'];
            break;
        case "2":
            $mensaje_origen = "ALMACÉN ANTERIOR";
            $mensaje_destino = "CLIENTE NUEVO";
            $nombre_origen = $rs['almacen_anterior'];
            $nombre_destino = $rs['cliente_nuevo'];
            break;
        case "3":
            $mensaje_origen = "CLIENTE ANTERIOR";
            $mensaje_destino = "ALMACÉN NUEVO";
            $nombre_origen = $rs['cliente_anterior'];
            $nombre_destino = $rs['almacen_nuevo'];
            break;
        case "4":
            $mensaje_origen = "ALMACÉN ANTERIOR";
            $mensaje_destino = "ALMACÉN NUEVO";
            $nombre_origen = $rs['almacen_anterior'];
            $nombre_destino = $rs['almacen_nuevo'];
            break;
        case "5":
            $mensaje_origen = "CLIENTE ANTERIOR";
            $mensaje_destino = "CLIENTE NUEVO";
            $nombre_origen = $rs['cliente_anterior'];
            $nombre_destino = $rs['cliente_nuevo'];
            break;
        default:
            break;
    }
    
    /* Cliente anterior */
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A10', $mensaje_origen)->mergeCells('A10:I10')
            ->setCellValue('A11', 'NOMBRE ó RAZON SOCIAL')->mergeCells('A11:B11')
            ->setCellValue('C11', $nombre_origen)->mergeCells('C11:I11')
            ->setCellValue('A12', 'CONTACTO COMERCIAL')->mergeCells('A12:B12')
            ->setCellValue('C12', $rs['contacto_anterior'])->mergeCells('C12:F12')
            ->setCellValue('G12', 'RFC')
            ->setCellValue('H12', $rs['rfc_anterior'])->mergeCells('H12:I12');
    /* Direccion cliente anterior */
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A14', 'CALLE Y NÚMERO')->mergeCells('A14:B14')
            ->setCellValue('C14', $rs['calle_anterior'])->mergeCells('C14:I14')
            ->setCellValue('A15', 'COLONIA')->mergeCells('A15:B15')
            ->setCellValue('C15', $rs['colonia_anterior'])->mergeCells('C15:I15')
            ->setCellValue('A16', 'DELEGACION ó MUNICIPIO')->mergeCells('A16:B16')
            ->setCellValue('C16', $rs['delegacion_anterior'])->mergeCells('C16:I16')
            ->setCellValue('A17', 'CIUDAD / ESTADO')->mergeCells('A17:B17')
            ->setCellValue('C17', $rs['ciudad_anterior'])->mergeCells('C17:I17')
            ->setCellValue('A18', 'TELEFONO Y EXTENSION')->mergeCells('A18:B18')
            ->setCellValue('E18', 'TELEFONO')->mergeCells('E18:F18')
            ->setCellValue('G18', $rs['telefono_anterior'])->mergeCells('G18:I18');
    /* Cliente NUEVO */
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A20', $mensaje_destino)->mergeCells('A20:I20')
            ->setCellValue('A21', 'NOMBRE ó RAZON SOCIAL')->mergeCells('A21:B21')
            ->setCellValue('C21', $nombre_destino)->mergeCells('C21:I21')
            ->setCellValue('A22', 'CONTACTO COMERCIAL')->mergeCells('A22:B22')
            ->setCellValue('C22', $rs['contacto_nuevo'])->mergeCells('C22:F22')
            ->setCellValue('H22', $rs['rfc_nuevo'])
            ->setCellValue('G22', 'RFC');
    /* Direccion cliente NUEVO */
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A24', 'CALLE Y NÚMERO')->mergeCells('A24:B24')
            ->setCellValue('C24', $rs['calle_nuevo'])->mergeCells('C24:I24')
            ->setCellValue('A25', 'COLONIA')->mergeCells('A25:B25')
            ->setCellValue('C25', $rs['colonia_nuevo'])->mergeCells('C25:I25')
            ->setCellValue('A26', 'DELEGACION ó MUNICIPIO')->mergeCells('A26:B26')
            ->setCellValue('C26', $rs['delegacion_nuevo'])->mergeCells('C26:I26')
            ->setCellValue('A27', 'CIUDAD / ESTADO')->mergeCells('A27:B27')
            ->setCellValue('C27', $rs['ciudad_nuevo'])->mergeCells('C27:I27')
            ->setCellValue('A28', 'TELEFONO Y EXTENSION')->mergeCells('A28:B28')
            ->setCellValue('E28', 'TELEFONO')->mergeCells('E28:F28')
            ->setCellValue('G28', $rs['telefono_nuevo'])->mergeCells('G28:I28');
    /* Datos generales */
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A30', 'DATOS GENERALES')->mergeCells('A30:I30')
            ->setCellValue('A31', 'CONTADOR NEGRO')->mergeCells('A31:B31')
            ->setCellValue('C31', $rs['ContadorBNML'])->mergeCells('C31:F31')
            ->setCellValue('A32', 'CONTADOR COLOR')->mergeCells('A32:B32')
            ->setCellValue('C32', $rs['ContadorColorML'])->mergeCells('C32:F32')
            ->setCellValue('A33', 'NIVEL DE TONER NEGRO')->mergeCells('A33:B33')
            ->setCellValue('C33', $rs['NivelTonNegro'])->mergeCells('C33:F33')
            ->setCellValue('G31', 'NIVEL DE TONER CYAN')->mergeCells('G31:H31')
            ->setCellValue('I31', $rs['NivelTonCian'])
            ->setCellValue('G32', 'NIVEL DE TONER MAGENTA')->mergeCells('G32:H32')
            ->setCellValue('I32', $rs['NivelTonMagenta'])
            ->setCellValue('G33', 'NIVEL DE TONER AMARILLO')->mergeCells('G33:H33')
            ->setCellValue('I33', $rs['NivelTonAmarillo']);
    /* Observaciones */
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A34', 'OBSERVACIONES')->mergeCells('A34:I34')->mergeCells('A35:I38')
            ->setCellValue('A39', 'CLIENTE')->mergeCells('A39:C39')
            ->setCellValue('D39', 'EJECUTIVO DE CUENTAS')->mergeCells('D39:F39')
            ->setCellValue('G39', 'AUTORIZACIÓN')->mergeCells('G39:I39')
            ->setCellValue('A40', 'NOMBRE')->mergeCells('A40:A41')
            ->setCellValue('D40', 'NOMBRE')->mergeCells('D40:D41')
            ->setCellValue('G40', 'NOMBRE')->mergeCells('G40:G41')
            ->setCellValue('G42', 'LIC. CLAUDIA MORENO / LIC. JOSUE MORENO')->mergeCells('G42:I44')
            ->setCellValue('A45', 'FIRMA')->mergeCells('A45:C45')
            ->setCellValue('D45', 'FIRMA')->mergeCells('D45:F45')
            ->setCellValue('G45', 'FIRMA')->mergeCells('G45:I45')
            ->setCellValue('A47', 'AV. RIO CHURUBUSCO No. 267, COL. PRADO CHURUBUSCO, COYOACAN, MEXICO D.F. C.P. 04230   TELS 5697-2887 y 5646-1681')->mergeCells('A47:K47');
    /* Firmas */
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B40:C41')->mergeCells('E40:F41')
            ->mergeCells('H40:I41')->mergeCells('A42:C44')->mergeCells('D42:F44')
            ->mergeCells('G42:I44');
}

//Ponemos los colores de las celdas
cellColor($objPHPExcel, 'A11:A12', 'C0C0C0'); //Cliente viejo
cellColor($objPHPExcel, 'A14:A18', 'C0C0C0'); //Localidad vieja
cellColor($objPHPExcel, 'A21:A22', 'C0C0C0'); //Cliente nuevo
cellColor($objPHPExcel, 'A24:A28', 'C0C0C0'); //Localida nueva
cellColor($objPHPExcel, 'A31:A33', 'C0C0C0'); //Contadores
cellColor($objPHPExcel, 'G31:G33', 'C0C0C0'); //Niveles
cellColor($objPHPExcel, 'G7', 'C0C0C0'); //Fecha
cellColor($objPHPExcel, 'G12', 'C0C0C0'); //RFC nuevo
cellColor($objPHPExcel, 'E18', 'C0C0C0'); //Telefono nuevo
cellColor($objPHPExcel, 'G22', 'C0C0C0'); //RFC nuevo
cellColor($objPHPExcel, 'E28', 'C0C0C0'); //Telefono nuevo
cellColor($objPHPExcel, 'A4', '404040'); //Se factura por
cellColor($objPHPExcel, 'A7', '404040'); //Tipo de movimiento
cellColor($objPHPExcel, 'A10', '404040'); //Cliente anterior
cellColor($objPHPExcel, 'A13:I13', '404040'); //
cellColor($objPHPExcel, 'A20', '404040'); //Cliente anterior
cellColor($objPHPExcel, 'A23:I23', '404040'); //
cellColor($objPHPExcel, 'A30:I30', '404040'); //Datos generales
cellColor($objPHPExcel, 'A34', '404040'); //Observaciones
cellColor($objPHPExcel, 'A39:I39', '404040'); //Cliente anterior
//Estilos de letras
$styleArray = getStyle(true, "000000", 18, "Calibri", false);
$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
$styleArray = getStyle(true, "FFFFFF", 10, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($styleArray);
$styleArray = getStyle(true, "000000", 12, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('C4')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C7')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('H7')->applyFromArray($styleArray);
$styleArray = getStyle(true, "FFFFFF", 10, "Arial", true);
$objPHPExcel->getActiveSheet()->getStyle('A10')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A20')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A30:I30')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A34')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A39:I39')->applyFromArray($styleArray);
$styleArray = getStyle(true, "000000", 9, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A11:A12')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A14:A18')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A21:A22')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A24:A28')->applyFromArray($styleArray);
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
$styleArray = getStyle(true, "800000", 8, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A47')->applyFromArray($styleArray);
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

$objPHPExcel->getActiveSheet()->getStyle('A21:B21')->applyFromArray($styleArray);
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
$objPHPExcel->getActiveSheet()->getStyle('H40:I41')->applyFromArray($styleArray);

/* Ajustar el width de las columnas */
//$objPHPExcel->getActiveSheet()->getColumnDimension('A:B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(23);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

/* Poner imagen */
$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
$objDrawing->setName("name");
$objDrawing->setDescription("Description");
$objDrawing->setPath('../../../resources/images/kyocera_reporte.png');
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

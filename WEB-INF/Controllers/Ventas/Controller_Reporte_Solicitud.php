<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_GET['folio'])) {
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

$consulta = "SELECT DISTINCT(ClaveCentroCosto) FROM k_solicitud WHERE id_solicitud = " . $_GET['folio'] . ";"; /* Obtenemos las localidades de la solicitud */
$query_solicitud = $catalogo->obtenerLista($consulta);

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

$fila_inicial = 10;
while ($resultSet = mysql_fetch_array($query_solicitud)) {
    // Agregar Informacion
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.($fila_inicial-9), 'FORMATO DE SOLICITUD DE EQUIPOS')->mergeCells('A'.($fila_inicial-9).':H'.($fila_inicial-8))
            ->setCellValue('A'.($fila_inicial-6), 'SE FACTURA POR ')->mergeCells('A'.($fila_inicial-6).':B'.($fila_inicial-5))            
            ->setCellValue('A'.($fila_inicial-3), 'TIPO DE MOVIMIENTO')->mergeCells('A'.($fila_inicial-3).':B'.($fila_inicial-2))
            ->setCellValue('C'.($fila_inicial-3), 'SOLICITUD DE EQUIPO')->mergeCells('C'.($fila_inicial-3).':E'.($fila_inicial-2))
            ->setCellValue('G'.($fila_inicial-3), 'FECHA')->mergeCells('G'.($fila_inicial-3).':G'.($fila_inicial-2));
    
    $consulta = "SELECT ( CASE WHEN c.estatus = 0 THEN ks.cantidad ELSE ks.cantidad_autorizada END) AS autorizada, ks.tipo,  ks.Modelo AS NoParte, c.comentario, cc.Nombre AS localidad, c.fecha_solicitud AS fecha,
        cli.NombreRazonSocial, cli.RFC, co.Nombre AS contacto, bi.NoSerie,  d.*, cc.Nombre As centro,
        (CASE WHEN ks.tipo = 0 THEN (SELECT MAX(e.Modelo) FROM `c_equipo` AS e WHERE e.NoParte = ks.Modelo) ELSE (SELECT MAX(com.Modelo) FROM `c_componente` AS com WHERE com.NoParte = ks.Modelo) END) AS Modelo,
        f.RazonSocial AS razon,CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono) AS facturacion
        FROM c_solicitud AS c
        INNER JOIN k_solicitud AS ks ON c.id_solicitud = " . $_GET['folio'] . " AND ks.id_solicitud = c.id_solicitud
        INNER JOIN c_centrocosto AS cc ON ks.ClaveCentroCosto = cc.ClaveCentroCosto AND ks.ClaveCentroCosto = '" . $resultSet['ClaveCentroCosto'] . "'
        INNER JOIN c_cliente AS cli ON cli.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_bitacora AS bi ON bi.id_solicitud = c.id_solicitud AND bi.NoParte = ks.Modelo
        LEFT JOIN c_domicilio AS d ON d.ClaveEspecialDomicilio = ks.ClaveCentroCosto
        LEFT JOIN c_contacto AS co ON co.ClaveEspecialContacto = cc.ClaveCentroCosto
        LEFT JOIN c_datosfacturacionempresa AS f ON f.IdDatosFacturacionEmpresa = cli.IdDatosFacturacionEmpresa;";


    $query = $catalogo->obtenerLista($consulta);

    $i = $fila_inicial + 12;
    $domicilio_fiscal = "";
    $razon = "";
    while ($rs = mysql_fetch_array($query)) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.($fila_inicial-3), $rs['fecha'])->mergeCells('H'.($fila_inicial-3).':I'.($fila_inicial-2));
        $mensaje_destino = "DESCRIPCION DE EQUIPOS - ACCESORIOS - CONSUMIBLES";
        $mensaje_origen = "DATOS GENERALES";
        $domicilio_fiscal = $rs['facturacion'];
        $nombre_origen = $rs['NombreRazonSocial'];
        $razon = $rs['razon'];
        /* Cliente anterior */
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $fila_inicial, $mensaje_origen)->mergeCells('A' . $fila_inicial . ':I' . $fila_inicial)
                ->setCellValue('A' . ($fila_inicial + 1), 'NOMBRE ó RAZON SOCIAL')->mergeCells('A' . ($fila_inicial + 1) . ':B' . ($fila_inicial + 1))
                ->setCellValue('C' . ($fila_inicial + 1), $nombre_origen)->mergeCells('C' . ($fila_inicial + 1) . ':I' . ($fila_inicial + 1))
                ->setCellValue('A' . ($fila_inicial + 2), 'CONTACTO COMERCIAL')->mergeCells('A' . ($fila_inicial + 2) . ':B' . ($fila_inicial + 2))
                ->setCellValue('C' . ($fila_inicial + 2), $rs['contacto'])->mergeCells('C' . ($fila_inicial + 2) . ':F' . ($fila_inicial + 2))
                ->setCellValue('G' . ($fila_inicial + 2), 'RFC')
                ->setCellValue('H' . ($fila_inicial + 2), $rs['RFC'])->mergeCells('H' . ($fila_inicial + 2) . ':I' . ($fila_inicial + 2))
                ->setCellValue('A' . ($fila_inicial + 3), $rs['centro'])->mergeCells('A' . ($fila_inicial + 3) . ':I' . ($fila_inicial + 3));
        /* Direccion cliente anterior */
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . ($fila_inicial + 4), 'CALLE Y NÚMERO')->mergeCells('A' . ($fila_inicial + 4) . ':B' . ($fila_inicial + 4))
                ->setCellValue('C' . ($fila_inicial + 4), $rs['Calle'])->mergeCells('C' . ($fila_inicial + 4) . ':I' . ($fila_inicial + 4))
                ->setCellValue('A' . ($fila_inicial + 5), 'COLONIA')->mergeCells('A' . ($fila_inicial + 5) . ':B' . ($fila_inicial + 5))
                ->setCellValue('C' . ($fila_inicial + 5), $rs['Colonia'])->mergeCells('C' . ($fila_inicial + 5) . ':I' . ($fila_inicial + 5))
                ->setCellValue('A' . ($fila_inicial + 6), 'DELEGACION ó MUNICIPIO')->mergeCells('A' . ($fila_inicial + 6) . ':B' . ($fila_inicial + 6))
                ->setCellValue('C' . ($fila_inicial + 6), $rs['Delegacion'])->mergeCells('C' . ($fila_inicial + 6) . ':I' . ($fila_inicial + 6))
                ->setCellValue('A' . ($fila_inicial + 7), 'CIUDAD / ESTADO')->mergeCells('A' . ($fila_inicial + 7) . ':B' . ($fila_inicial + 7))
                ->setCellValue('C' . ($fila_inicial + 7), $rs['Ciudad'])->mergeCells('C' . ($fila_inicial + 7) . ':I' . ($fila_inicial + 7))
                ->setCellValue('A' . ($fila_inicial + 8), 'TELEFONO Y EXTENSION')->mergeCells('A' . ($fila_inicial + 8) . ':B' . ($fila_inicial + 8))
                ->setCellValue('E' . ($fila_inicial + 8), 'C. POSTAL')->mergeCells('E' . ($fila_inicial + 8) . ':F' . ($fila_inicial + 8))
                ->setCellValue('G' . ($fila_inicial + 8), $rs['CodigoPostal'])->mergeCells('G' . ($fila_inicial + 8) . ':I' . ($fila_inicial + 8));
        /* Cliente NUEVO */
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . ($fila_inicial + 10) . '', $mensaje_destino)->mergeCells('A' . ($fila_inicial + 10) . ':I' . ($fila_inicial + 10) . '')
                ->setCellValue('A' . ($fila_inicial + 11) . '', 'CANTIDAD')->mergeCells('A' . ($fila_inicial + 11) . ':B' . ($fila_inicial + 11) . '')
                ->setCellValue('C' . ($fila_inicial + 11) . '', 'MODELO')->mergeCells('C' . ($fila_inicial + 11) . ':D' . ($fila_inicial + 11) . '')
                ->setCellValue('E' . ($fila_inicial + 11) . '', 'DESCRIPCIÓN')->mergeCells('E' . ($fila_inicial + 11) . ':G' . ($fila_inicial + 11) . '')
                ->setCellValue('H' . ($fila_inicial + 11) . '', 'No SERIE')->mergeCells('H' . ($fila_inicial + 11) . ':I' . ($fila_inicial + 11) . '');
        //$cantidad = "1";
        //if ($rs['tipo'] == "1") {
        $cantidad = $rs['autorizada'];
        //}
        /* Agregamos los equipos, componente */
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $cantidad)->mergeCells('A' . $i . ':B' . $i)
                ->setCellValue('C' . $i, $rs['Modelo'])->mergeCells('C' . $i . ':D' . $i)
                ->setCellValue('E' . $i, $rs['comentario'])->mergeCells('E' . $i . ':G' . $i)
                ->setCellValue('H' . $i, $rs['NoSerie'])->mergeCells('H' . $i . ':I' . $i);
        $i++;        
    }

    /* Observaciones */
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . ($i + 1), 'OBSERVACIONES')->mergeCells('A' . ($i + 1) . ':I' . ($i + 1))->mergeCells('A' . ($i + 2) . ':I' . ($i + 4))
            ->setCellValue('A' . ($i + 5) . '', 'CLIENTE')->mergeCells('A' . ($i + 5) . ':C' . ($i + 5))
            ->setCellValue('D' . ($i + 5) . '', 'EJECUTIVO DE CUENTAS')->mergeCells('D' . ($i + 5) . ':F' . ($i + 5))
            ->setCellValue('G' . ($i + 5) . '', 'AUTORIZACIÓN')->mergeCells('G' . ($i + 5) . ':I' . ($i + 5))
            ->setCellValue('A' . ($i + 6) . '', 'NOMBRE')->mergeCells('A' . ($i + 6) . ':A' . ($i + 7))
            ->setCellValue('D' . ($i + 6) . '', 'NOMBRE')->mergeCells('D' . ($i + 6) . ':D' . ($i + 7))
            ->setCellValue('G' . ($i + 6) . '', 'NOMBRE')->mergeCells('G' . ($i + 6) . ':G' . ($i + 7))
            ->setCellValue('G' . ($i + 8) . '', 'LIC. CLAUDIA MORENO')->mergeCells('G' . ($i + 8) . ':I' . ($i + 10))
            ->setCellValue('A' . ($i + 11) . '', 'FIRMA')->mergeCells('A' . ($i + 11) . ':C' . ($i + 11) . '')
            ->setCellValue('D' . ($i + 11) . '', 'FIRMA')->mergeCells('D' . ($i + 11) . ':F' . ($i + 11) . '')
            ->setCellValue('G' . ($i + 11) . '', 'FIRMA')->mergeCells('G' . ($i + 11) . ':I' . ($i + 11) . '')
            ->setCellValue('A' . ($i + 13), $domicilio_fiscal)->mergeCells('A' . ($i + 13) . ':K' . ($i + 13))
            ->setCellValue('C'.($fila_inicial-6), $razon)->mergeCells('C'.($fila_inicial-6).':E'.($fila_inicial-5));
    /* Firmas */
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B' . ($i + 6) . ':C' . ($i + 7) . '')->mergeCells('E' . ($i + 6) . ':F' . ($i + 7) . '')
            ->mergeCells('H' . ($i + 6) . ':I' . ($i + 7) . '')->mergeCells('A' . ($i + 8) . ':C' . ($i + 10) . '')->mergeCells('D' . ($i + 8) . ':F' . ($i + 10) . '')
            ->mergeCells('G' . ($i + 8) . ':I' . ($i + 10) . '');

    //Ponemos los colores de las celdas
    cellColor($objPHPExcel, 'A' . ($fila_inicial + 1) . ':A' . ($fila_inicial + 2) . '', 'C0C0C0'); //Cliente viejo
    cellColor($objPHPExcel, 'A' . ($fila_inicial + 4) . ':A' . ($fila_inicial + 8) . '', 'C0C0C0'); //Localidad vieja

    cellColor($objPHPExcel, 'G'.($fila_inicial-3), 'C0C0C0'); //Fecha
    cellColor($objPHPExcel, 'G' . ($fila_inicial + 2) . '', 'C0C0C0'); //RFC nuevo
    cellColor($objPHPExcel, 'E' . ($fila_inicial + 8) . '', 'C0C0C0'); //Telefono nuevo

    cellColor($objPHPExcel, 'A'.($fila_inicial-6), '404040'); //Se factura por
    cellColor($objPHPExcel, 'A'.($fila_inicial-3), '404040'); //Tipo de movimiento
    cellColor($objPHPExcel, 'A' . ($fila_inicial + 0) . '', '404040'); //Cliente anterior
    cellColor($objPHPExcel, 'A' . ($fila_inicial + 3) . ':I'.($fila_inicial + 3), '404040'); //
    cellColor($objPHPExcel, 'A' . ($fila_inicial + 10) . '', '404040'); //Cliente anterior
    cellColor($objPHPExcel, 'A' . ($i + 1), '404040'); //Observaciones
    //Estilos de letras
    $styleArray = getStyle(true, "000000", 18, "Calibri", false);
    $objPHPExcel->getActiveSheet()->getStyle('A'.($fila_inicial-9))->applyFromArray($styleArray);
    $styleArray = getStyle(true, "FFFFFF", 10, "Arial", false);
    $objPHPExcel->getActiveSheet()->getStyle('A'.($fila_inicial-6))->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A'.($fila_inicial-3))->applyFromArray($styleArray);
    $styleArray = getStyle(true, "000000", 12, "Arial", false);

    $objPHPExcel->getActiveSheet()->getStyle('C'.($fila_inicial-3))->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('H'.($fila_inicial-3))->applyFromArray($styleArray);
    $styleArray = getStyle(true, "FFFFFF", 10, "Arial", true);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 0) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 3) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 10) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($i + 1))->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 20) . ':I' . ($fila_inicial + 20) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 24) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A39:I39')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A39:I39')->applyFromArray($styleArray);
    $styleArray = getStyle(true, "000000", 9, "Arial", false);
    $objPHPExcel->getActiveSheet()->getStyle('C'.($fila_inicial-6))->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 1) . ':A' . ($fila_inicial + 2) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 4) . ':A' . ($fila_inicial + 8) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 21) . ':A' . ($fila_inicial + 23) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('G' . ($fila_inicial + 21) . ':G' . ($fila_inicial + 23) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('G7')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('G' . ($fila_inicial + 2) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('E' . ($fila_inicial + 8) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('G' . ($fila_inicial + 12) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('E' . ($fila_inicial + 18) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 30) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('D' . ($fila_inicial + 30) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('G' . ($fila_inicial + 30) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 35) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('D' . ($fila_inicial + 35) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('G' . ($fila_inicial + 35) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('G' . ($fila_inicial + 32) . '')->applyFromArray($styleArray);
    $styleArray = getStyle(true, "800000", 6, "Arial", false);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($i + 13))->applyFromArray($styleArray);
    //Borders
    $styleArray = getStyleBorder();
    $objPHPExcel->getActiveSheet()->getStyle('C'.($fila_inicial-6).':E'.($fila_inicial-5))->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C'.($fila_inicial-3).':E'.($fila_inicial-2))->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('G'.($fila_inicial-3).':G'.($fila_inicial-2))->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('H'.($fila_inicial-3).':I'.($fila_inicial-2))->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 1) . ':B' . ($fila_inicial + 1) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C' . ($fila_inicial + 1) . ':I' . ($fila_inicial + 1) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 2) . ':B' . ($fila_inicial + 2) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C' . ($fila_inicial + 2) . ':F' . ($fila_inicial + 2) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('G' . ($fila_inicial + 2) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('H' . ($fila_inicial + 2) . ':I' . ($fila_inicial + 2) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 4) . ':B' . ($fila_inicial + 4) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 5) . ':B' . ($fila_inicial + 5) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 6) . ':B' . ($fila_inicial + 6) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 7) . ':B' . ($fila_inicial + 7) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 8) . ':B' . ($fila_inicial + 8) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C' . ($fila_inicial + 4) . ':I' . ($fila_inicial + 4) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C' . ($fila_inicial + 5) . ':I' . ($fila_inicial + 5) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C' . ($fila_inicial + 6) . ':I' . ($fila_inicial + 6) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C' . ($fila_inicial + 7) . ':I' . ($fila_inicial + 7) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C' . ($fila_inicial + 8) . ':D' . ($fila_inicial + 8) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('E' . ($fila_inicial + 8) . ':F' . ($fila_inicial + 8) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('G' . ($fila_inicial + 8) . ':I' . ($fila_inicial + 8) . '')->applyFromArray($styleArray);

    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 10) . ':I' . ($fila_inicial + 10) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A' . ($fila_inicial + 11) . ':B' . ($fila_inicial + 11) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C' . ($fila_inicial + 11) . ':D' . ($fila_inicial + 11) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('E' . ($fila_inicial + 11) . ':G' . ($fila_inicial + 11) . '')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('H' . ($fila_inicial + 11) . ':I' . ($fila_inicial + 11) . '')->applyFromArray($styleArray);
    /* Equipos y componentes */
    for ($fila = ($fila_inicial+11); $fila < $i; $fila++) {
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

    /* Poner imagen */
    $objDrawing = new PHPExcel_Worksheet_Drawing();
    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
    $objDrawing->setName("name");
    $objDrawing->setDescription("Description");
    $objDrawing->setPath('../../../resources/images/kyocera_reporte.png');
    $objDrawing->setCoordinates('H'.($fila_inicial-9));
    $objDrawing->setOffsetX(1);
    $objDrawing->setOffsetY(5);
    
    $fila_inicial = $fila_inicial + $i + 10;    
}

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

// Renombrar Hoja
$objPHPExcel->getActiveSheet()->setTitle('FORMATO DE ENTREGA');

// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
$objPHPExcel->setActiveSheetIndex(0);

// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ReporteSolicitud.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>

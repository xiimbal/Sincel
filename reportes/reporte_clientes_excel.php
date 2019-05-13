<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

ini_set("memory_limit","512M");
set_time_limit (0);
require_once('../WEB-INF/Classes/PHPExcel/CachedObjectStorageFactory.php');
require_once('../WEB-INF/Classes/PHPExcel/Settings.php');
$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
$cacheSettings = array( 'memoryCacheSize' => '32MB');
PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

include_once("../WEB-INF/Classes/Catalogo.class.php");
require_once('../WEB-INF/Classes/PHPExcel/IOFactory.php');
require_once('../WEB-INF/Classes/PHPExcel.php');
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
/* include_once("../WEB-INF/Classes/Catalogo.class.php");
  include_once("../WEB-INF/Classes/Menu.class.php");
  include_once("../WEB-INF/Classes/Usuario.class.php"); */
$permiso_facturar = new PermisosSubMenu();
$usuario = new Usuario();

$dias = array("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado");
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

$fecha = $dias[date('w')] . ", " . date('j') . " de " . $meses[date('n') - 1] . " del " . date('Y');

function cellColor($objPHPExcel, $cells, $color) {
    $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()
            ->applyFromArray(array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => $color)
    ));
}

function getStyle($bold, $color, $size, $name, $cursive) {
    $styleArray = array('font' => array('bold' => $bold, 'italic' => $cursive, 'color' => array('rgb' => $color), 'size' => $size, 'name' => $name),
        'alignment' => array('wrap' => true, 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    ));
    return $styleArray;
}

$objPHPExcel = new PHPExcel();
$catalogo = new Catalogo();
$where = "";
$permiso_inctivos = $permiso_facturar->tienePermisoEspecial($_SESSION['idUsuario'], 23);
$id_cliente = "";
$id_vendedor = "";
$rfc = "";
$estatus = "";
$tipo = "";
if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)) {
    $where = " AND c.EjecutivoCuenta = '" . $_SESSION['idUsuario'] . "'";
}
if (isset($_POST['sl_cliente']) && $_POST['sl_cliente'] != "0") {
    $id_cliente = $_POST['sl_cliente'];
    if ($where == "") {
        $where = " WHERE c.ClaveCliente = '$id_cliente'";
    } else {
        $where .= " AND c.ClaveCliente = '$id_cliente'";
    }
}
if (isset($_POST['txt_rfc']) && $_POST['txt_rfc'] != "") {
    $rfc = $_POST['txt_rfc'];
    if ($where == "") {
        $where = " WHERE c.RFC = '$rfc'";
    } else {
        $where .= " AND c.RFC = '$rfc'";
    }
}
if (isset($_POST['sl_vendedor']) && $_POST['sl_vendedor'] != "0") {
    $id_vendedor = $_POST['sl_vendedor'];
    if ($where == "") {
        $where = " WHERE u.IdUsuario = '$id_vendedor'";
    } else {
        $where .= " AND u.IdUsuario = '$id_vendedor'";
    }
}
if ($permiso_inctivos) {
    if (isset($_POST['sl_estatus']) && $_POST['sl_estatus'] != "") {
        $estatus = $_POST['sl_estatus'];
        if ($where == "") {
            $where = " WHERE c.Activo = '$estatus'";
        } else {
            $where .= " AND c.Activo = '$estatus'";
        }
    }
} else {
    if ($where == "") {
        $where = " WHERE c.Activo = '1'";
    } else {
        $where .= " AND c.Activo = '1'";
    }
}
if (isset($_POST['sl_tipo']) && $_POST['sl_tipo'] != "0") {
    $tipo = $_POST['sl_tipo'];
    if ($where == "") {
        $where = " WHERE c.Modalidad = '$tipo'";
    } else {
        $where .= "  AND c.Modalidad= '$tipo'";
    }
}
$consulta = "SELECT c.ClaveCliente AS clave,NombreRazonSocial,c.RFC,tc.Nombre AS tipoCliente,cg.Nombre AS NombreGrupo,CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS vendedor,IF(c.Activo=1,'Activo','Inactivo') AS estatus,
                cc.ClaveCentroCosto,cc.Nombre AS Nombre,fe.RFC AS RFCEmisor,ctt.Nombre AS contacto,ctt.Telefono AS Telefono,ctt.Celular AS Celular,ctt.CorreoElectronico AS CorreoElectronico,
                d.Calle AS Calle,d.NoExterior AS NoExterior,d.NoInterior AS NoInterior,d.Colonia AS Colonia,d.Ciudad AS Ciudad,d.Estado AS Estado,d.Delegacion AS Delegacion,d.Pais AS Pais,d.CodigoPostal AS CodigoPostal
                FROM c_cliente c LEFT JOIN c_usuario u ON c.EjecutivoCuenta = u.IdUsuario LEFT JOIN c_clientegrupo  cg ON c.ClaveGrupo = cg.ClaveGrupo LEFT JOIN c_clientemodalidad tc ON tc.IdTipoCliente = c.Modalidad 
                LEFT JOIN c_centrocosto cc ON c.ClaveCliente=cc.ClaveCliente LEFT JOIN c_datosfacturacionempresa AS fe ON fe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa 
                LEFT JOIN c_contacto ctt ON ctt.ClaveEspecialContacto = cc.ClaveCentroCosto LEFT JOIN c_domicilio d ON d.ClaveEspecialDomicilio = cc.ClaveCentroCosto $where;";
$query = $catalogo->obtenerLista($consulta);
$objPHPExcel->getProperties()
        ->setCreator("")
        ->setLastModifiedBy("")
        ->setTitle("Documento Excel")
        ->setSubject("Documento Excel")
        ->setDescription("Reporte de facturación")
        ->setKeywords("Excel Office 2007 openxml php")
        ->setCategory("Reportes");

$fila_inicial = 3;
$fila_inicial_backup = $fila_inicial;
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . (1), 'REPORTE DE CLIENTES')->mergeCells('A1:V1')
        ->setCellValue('A' . (2), $fecha)->mergeCells('A2:V2')
        ->setCellValue('A' . ($fila_inicial), 'ClaveCliente')
        ->setCellValue('B' . ($fila_inicial), 'NombreRazonSocial')
        ->setCellValue('C' . ($fila_inicial), 'RFC')
        ->setCellValue('D' . ($fila_inicial), 'Vendedor')
        ->setCellValue('E' . ($fila_inicial), 'Tipo')
        ->setCellValue('F' . ($fila_inicial), 'Estatus')
        ->setCellValue('G' . ($fila_inicial), 'RFCEmisor')
        ->setCellValue('H' . ($fila_inicial), 'ClaveCentroCosto')
        ->setCellValue('I' . ($fila_inicial), 'Nombre centro costo')
        ->setCellValue('J' . ($fila_inicial), 'Calle')
        ->setCellValue('K' . ($fila_inicial), 'NoExterior')
        ->setCellValue('L' . ($fila_inicial), 'NoInterior')
        ->setCellValue('M' . ($fila_inicial), 'Colonia')
        ->setCellValue('N' . ($fila_inicial), 'Ciudad')
        ->setCellValue('O' . ($fila_inicial), 'Estado')
        ->setCellValue('P' . ($fila_inicial), 'Delegacion')
        ->setCellValue('Q' . ($fila_inicial), 'Pais')
        ->setCellValue('R' . ($fila_inicial), 'CodigoPostal')
        ->setCellValue('S' . ($fila_inicial), 'contacto')
        ->setCellValue('T' . ($fila_inicial), 'Telefono')
        ->setCellValue('U' . ($fila_inicial), 'Celular')
        ->setCellValue('V' . ($fila_inicial), 'CorreoElectronico');
$fila_inicial++;
$bool = TRUE;
while ($rs = mysql_fetch_array($query)) {
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . ($fila_inicial), $rs['clave'])
            ->setCellValue('B' . ($fila_inicial), $rs['NombreRazonSocial'])
            ->setCellValue('C' . ($fila_inicial), $rs['RFC'])
            ->setCellValue('D' . ($fila_inicial), $rs['vendedor'])
            ->setCellValue('E' . ($fila_inicial), $rs['tipoCliente'])
            ->setCellValue('F' . ($fila_inicial), $rs['estatus'])
            ->setCellValue('G' . ($fila_inicial), $rs['RFCEmisor'])
            ->setCellValue('H' . ($fila_inicial), $rs['ClaveCentroCosto'])
            ->setCellValue('I' . ($fila_inicial), $rs['Nombre'])
            ->setCellValue('J' . ($fila_inicial), $rs['Calle'])
            ->setCellValue('K' . ($fila_inicial), $rs['NoExterior'])
            ->setCellValue('L' . ($fila_inicial), $rs['NoInterior'])
            ->setCellValue('M' . ($fila_inicial), $rs['Colonia'])
            ->setCellValue('N' . ($fila_inicial), $rs['Ciudad'])
            ->setCellValue('O' . ($fila_inicial), $rs['Estado'])
            ->setCellValue('P' . ($fila_inicial), $rs['Delegacion'])
            ->setCellValue('Q' . ($fila_inicial), $rs['Pais'])
            ->setCellValue('R' . ($fila_inicial), $rs['CodigoPostal'])
            ->setCellValue('S' . ($fila_inicial), $rs['contacto'])
            ->setCellValue('T' . ($fila_inicial), $rs['Telefono'])
            ->setCellValue('U' . ($fila_inicial), $rs['Celular'])
            ->setCellValue('V' . ($fila_inicial), $rs['CorreoElectronico']);

    if ($bool) {
        cellColor($objPHPExcel, 'A' . $fila_inicial . ':V' . $fila_inicial, 'ddebf7'); //TITULO REPORTE
        $bool = FALSE;
    } else {
        $bool = TRUE;
    }
    $fila_inicial++;
}
cellColor($objPHPExcel, 'A1:V1', '5b9bd5'); //TITULO REPORTE
cellColor($objPHPExcel, 'A2:V2', '5b9bd5'); //TITULO REPORTE
cellColor($objPHPExcel, 'A' . $fila_inicial_backup . ':V' . $fila_inicial_backup, '5b9bd5'); //TITULO REPORTE
$styleArray = getStyle(true, "000000", 12, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A1:V1')->applyFromArray($styleArray); /* TITULO */
$styleArray = getStyle(true, "000000", 10, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A2:V2')->applyFromArray($styleArray); /* TITULO */
$styleArray = getStyle(true, "000000", 10, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inicial_backup . ':V' . $fila_inicial_backup)->applyFromArray($styleArray); /* Cabeceras de la tabla */


$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
// Renombrar Hoja
$objPHPExcel->getActiveSheet()->setTitle('Clientes');

// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
$objPHPExcel->setActiveSheetIndex(0);

// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Clientes.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

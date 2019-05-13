<?php

session_start();

ini_set("memory_limit", "512M");
set_time_limit(0);

include_once("WEB-INF/Classes/Catalogo.class.php");
require_once('WEB-INF/Classes/PHPExcel/IOFactory.php');
require_once('WEB-INF/Classes/PHPExcel.php');
include_once("WEB-INF/Classes/ConexionMultiBD.class.php");
include_once("WEB-INF/Classes/Mail.class.php");
include_once("WEB-INF/Classes/ParametroGlobal.class.php");

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

$day = date('w');
$week_start = date('Y-m-d', strtotime('saturday last week'));
$week_end = date('Y-m-d', strtotime('friday this week'));

$TipoReporte = "";
$Area = "";
$Cliente = "";
$Usuario = "";
$where1 = "";

if(isset($_POST['fecha_inicio']) && $_POST['fecha_inicio'] != ""){
    $week_start = $_POST['fecha_inicio'];
    /*$FechaInicio = " AND t.FechaHora >= '".$_POST['fecha_inicio']." 00:00:00' ";
    $where1 .= $FechaInicio;*/
}

if(isset($_POST['fecha_fin']) && $_POST['fecha_fin'] != ""){
    $week_end = $_POST['fecha_fin'];
    /*$FechaFin = " AND t.FechaHora <= '".$_POST['fecha_fin']." 23:59:59' ";
    $where1 .= $FechaFin;*/
}

if(isset($_POST['reporte_ticket']) && $_POST['reporte_ticket'] != ""){
    $TipoReporte = " AND t.TipoReporte = ".$_POST['reporte_ticket'];
    $where1 .= $TipoReporte;
}

if(isset($_POST['area_ticket']) && $_POST['area_ticket'] != ""){
    $Area = " AND t.AreaAtencion = ".$_POST['area_ticket'];
    $where1 .= $Area;
}

if(isset($_POST['cliente']) && $_POST['cliente'] != ""){
    $Cliente = " AND t.ClaveCliente = '".$_POST['cliente']."'";
    $where1 .= $Cliente;
}

if(isset($_POST['usuario']) && $_POST['usuario'] != ""){
    $Usuario = " AND u.IdUsuario = ".$_POST['usuario'];    
}

$con = new ConexionMultiBD();
if(isset($_POST['IdEmpresa']) && $_POST['IdEmpresa']!=""){
    $result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE id_empresa = ".$_POST['IdEmpresa'].";");
}else{
    $result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
}
$con->Desconectar();
while ($rs_multi = mysql_fetch_array($result_bases)) {
    echo "<br/><br/>Procesando empresa " . $rs_multi['nombre_empresa'];
    $empresa = $rs_multi['id_empresa'];

    /* Inicializamos la clase */
    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);
    $objPHPExcel = new PHPExcel();
    $parametroGlobal = new ParametroGlobal();
    $parametroGlobal->setEmpresa($empresa);
// Establecer propiedades
    $objPHPExcel->getProperties()
            ->setCreator("")
            ->setLastModifiedBy("")
            ->setTitle("Documento Excel")
            ->setSubject("Documento Excel")
            ->setDescription("Reporte semanal de tickets")
            ->setKeywords("Excel Office 2007 openxml php")
            ->setCategory("Reportes");

    

//Encabezado
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B1', 'Tickets trabajados de la semana del ' . $catalogo->formatoFechaReportes($week_start) . ' al ' . $catalogo->formatoFechaReportes($week_end))->mergeCells('B1:K1');
    cellColor($objPHPExcel, 'B1:K1', '5b9bd5'); //TITULO REPORTE
    $styleArray = getStyle(true, "FFFFFF", 12, "Arial", false);
    $objPHPExcel->getActiveSheet()->getStyle('B1:K1')->applyFromArray($styleArray); /* TITULO */

    $fila_inicial = 3;

// ******************** Resumen por area
    $total_area = 0;
    $consulta = "SELECT COUNT(t.IdTicket) AS Cuenta, e1.Nombre AS AreaAtencion 
    FROM c_ticket AS t
    LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)
    LEFT JOIN c_estado AS e1 ON e1.IdEstado = t.AreaAtencion
    WHERE ((t.FechaHora BETWEEN '$week_start 00:00:00' AND '$week_end 23:59:59') OR (nt.FechaHora BETWEEN '$week_start 00:00:00' AND '$week_end 23:59:59'))
    AND (ISNULL(nt.IdNotaTicket) OR nt.IdEstatusAtencion NOT IN(12, 22, 47, 17, 21, 24, 58, 60) )
    $where1 
    GROUP BY t.AreaAtencion
    ORDER BY Cuenta DESC;";
    $result = $catalogo->obtenerLista($consulta);
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B' . $fila_inicial, 'Área')
            ->setCellValue('C' . $fila_inicial, 'Tickets');
    cellColor($objPHPExcel, 'B' . $fila_inicial . ':C' . $fila_inicial, '5b9bd5'); //TITULO tabla
    $styleArray = getStyle(false, "FFFFFF", 12, "Arial", false);

    $fila_inicial++;
//Recorremos todos los registros
    $bool = true;
    while ($rs = mysql_fetch_array($result)) {
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B' . ($fila_inicial), $rs['AreaAtencion'])
                ->setCellValue('C' . ($fila_inicial), (int) $rs['Cuenta']);
        $total_area += (int) $rs['Cuenta'];
        if ($bool) {
            cellColor($objPHPExcel, 'B' . $fila_inicial . ':C' . $fila_inicial, 'ddebf7'); //TITULO REPORTE
            $bool = FALSE;
        } else {
            $bool = TRUE;
        }
        $fila_inicial++;
    }
//Ponemos el pie de la tabla
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B' . $fila_inicial, 'Total')
            ->setCellValue('C' . $fila_inicial, $total_area);
    cellColor($objPHPExcel, 'B' . $fila_inicial . ':C' . $fila_inicial, '5b9bd5'); //pie tabla
    $styleArray = getStyle(false, "000000", 12, "Arial", false);
    $objPHPExcel->getActiveSheet()->getStyle('B' . $fila_inicial . ':C' . $fila_inicial)->applyFromArray($styleArray); /* pie tabla */
    $fila_inicial+=2;

//************************* Resumen general
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B' . $fila_inicial, 'Abiertos antiguos')
            ->setCellValue('C' . $fila_inicial, 'Cerrados esta semana')
            ->setCellValue('D' . $fila_inicial, 'Total abiertos');
    cellColor($objPHPExcel, 'B' . $fila_inicial . ':D' . $fila_inicial, '5b9bd5'); //TITULO tabla
    $styleArray = getStyle(false, "FFFFFF", 12, "Arial", false);
    $fila_inicial++;
//Abiertos antiguos
    $consulta = "SELECT COUNT(t.IdTicket) AS cuenta 
        FROM c_ticket AS t
        LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket)
        WHERE 
        t.EstadoDeTicket <> 4 AND t.EstadoDeTicket <> 2 
        AND (ISNULL(nt.IdEstatusAtencion) OR (nt.IdEstatusAtencion <> 16 AND nt.IdEstatusAtencion <> 59))
        AND t.FechaHora < '$week_start 00:00:00' $where1;";
    $result = $catalogo->obtenerLista($consulta);
    $abiertos_antiguos = 0;
    while ($rs = mysql_fetch_array($result)) {
        $abiertos_antiguos = (int) $rs['cuenta'];
    }

//Cerrados esta semana
    $consulta = "SELECT COUNT(nt.IdNotaTicket) AS cuenta 
        FROM c_notaticket AS nt
        LEFT JOIN c_ticket AS t ON t.IdTicket = nt.IdTicket
        WHERE nt.IdEstatusAtencion = 16 AND nt.FechaHora BETWEEN '$week_start 00:00:00' AND '$week_end 23:59:59' $where1;";
    $result = $catalogo->obtenerLista($consulta);
    $cerrados_this_week = 0;
    while ($rs = mysql_fetch_array($result)) {
        $cerrados_this_week = (int) $rs['cuenta'];
    }

//Total abiertos
    $consulta = "SELECT COUNT(t.IdTicket) AS cuenta 
        FROM c_ticket AS t
        LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket)
        WHERE 
        t.EstadoDeTicket <> 4 AND t.EstadoDeTicket <> 2 
        AND (ISNULL(nt.IdEstatusAtencion) OR (nt.IdEstatusAtencion <> 16 AND nt.IdEstatusAtencion <> 59))
        AND t.FechaHora $where1;";
    $result = $catalogo->obtenerLista($consulta);
    $total_abiertos = 0;
    while ($rs = mysql_fetch_array($result)) {
        $total_abiertos = (int) $rs['cuenta'];
    }

    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B' . $fila_inicial, $abiertos_antiguos)
            ->setCellValue('C' . $fila_inicial, $cerrados_this_week)
            ->setCellValue('D' . $fila_inicial, $total_abiertos);

// ******************** Tecnicos
    $fila_inicial = 3;
    $consulta = "SELECT COUNT(IdTicket) AS Cuenta, Loggin, IdUsuario, Usuario
        FROM(
        SELECT t.IdTicket, u.Loggin, u.IdUsuario, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Usuario
        FROM c_usuario AS u
        LEFT JOIN c_notaticket AS nt ON nt.UsuarioCreacion = u.Loggin
        LEFT JOIN c_ticket AS t ON nt.IdTicket = t.IdTicket
        WHERE ((t.FechaHora BETWEEN '$week_start 00:00:00' AND '$week_end 23:59:59') OR (nt.FechaHora BETWEEN '$week_start 00:00:00' AND '$week_end 23:59:59'))
            AND nt.IdEstatusAtencion NOT IN(12, 22, 47, 17, 21, 24, 58, 60) $where1 $Usuario
        GROUP BY u.IdUsuario, t.IdTicket
        ORDER BY t.IdTicket
        ) AS t_1
        GROUP BY IdUsuario
        ORDER BY Cuenta DESC;";
    $result = $catalogo->obtenerLista($consulta);
//Encabezado
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('E' . $fila_inicial, 'Técnicos')
            ->setCellValue('F' . $fila_inicial, 'Atendidos')
            ->setCellValue('G' . $fila_inicial, 'Cerrados')
            ->setCellValue('H' . $fila_inicial, 'Pend/Ref')
            ->setCellValue('I' . $fila_inicial, 'Promedio * Día')
            ->setCellValue('J' . $fila_inicial, 'Efectividad (%)');
    cellColor($objPHPExcel, 'E' . $fila_inicial . ':J' . $fila_inicial, '5b9bd5'); //TITULO tabla
    $styleArray = getStyle(false, "FFFFFF", 12, "Arial", false);
    $fila_inicial++;
//Recorremos todos los registros
    $bool = true;
    while ($rs = mysql_fetch_array($result)) {
        /* Cerrados */
        
        $consulta = "SELECT COUNT(IdTicket) AS cuenta FROM(
            SELECT t.IdTicket
            FROM c_notaticket AS nt 
            LEFT JOIN c_ticket AS t ON nt.IdTicket = t.IdTicket
            LEFT JOIN c_usuario AS u ON u.Loggin = nt.UsuarioCreacion
            WHERE nt.IdEstatusAtencion = 16 AND ((t.FechaHora BETWEEN '$week_start 00:00:00' AND '$week_end 23:59:59') OR (nt.FechaHora BETWEEN '$week_start 00:00:00' AND '$week_end 23:59:59'))
            AND u.IdUsuario = " . $rs['IdUsuario'] . " $where1
            GROUP BY t.IdTicket
            ORDER BY t.IdTicket
            ) AS t_1;";
        $result2 = $catalogo->obtenerLista($consulta);
        $cerrados = 0;
        while ($rs2 = mysql_fetch_array($result2)) {
            $cerrados = $rs2['cuenta'];
        }
        /* Pendientes de refaccion */
        $consulta = "SELECT SUM(PendienteRefaccion) AS TicketsPendientes
        FROM
        (
            SELECT 
            (
            CASE WHEN 
            ISNULL(SUM(nr.Cantidad) -
            (
                    SELECT SUM(nr2.Cantidad) AS totalSurtidas 
                    FROM c_ticket AS t2
                    LEFT JOIN c_notaticket AS nt2 ON nt2.IdTicket = t2.IdTicket 
                    LEFT JOIN k_nota_refaccion AS nr2 ON nr2.IdNotaTicket = nt2.IdNotaTicket
                    WHERE t2.IdTicket = t.IdTicket AND (nt2.IdEstatusAtencion=21 OR nt2.IdEstatusAtencion = 68)
                    AND !ISNULL(nr2.IdNotaTicket)
                    GROUP BY t2.IdTicket
                    ORDER BY t2.IdTicket
            ) 
            ) THEN 1
            WHEN 
            (SUM(nr.Cantidad) -
            (
                    SELECT SUM(nr2.Cantidad) AS totalSurtidas 
                    FROM c_ticket AS t2
                    LEFT JOIN c_notaticket AS nt2 ON nt2.IdTicket = t2.IdTicket 
                    LEFT JOIN k_nota_refaccion AS nr2 ON nr2.IdNotaTicket = nt2.IdNotaTicket
                    WHERE t2.IdTicket = t.IdTicket AND (nt2.IdEstatusAtencion=21 OR nt2.IdEstatusAtencion = 68)
                    AND !ISNULL(nr2.IdNotaTicket)
                    GROUP BY t2.IdTicket
                    ORDER BY t2.IdTicket
            ) 
            ) > 0 THEN 1
            ELSE 0
            END)AS PendienteRefaccion, 
            t.IdTicket
            FROM c_usuario AS u
            LEFT JOIN c_notaticket AS nt ON nt.UsuarioCreacion = u.Loggin
            LEFT JOIN c_ticket AS t ON nt.IdTicket = t.IdTicket 
            LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
            WHERE ((t.FechaHora BETWEEN '$week_start 00:00:00' AND '$week_end 23:59:59') OR (nt.FechaHora BETWEEN '$week_start 00:00:00' AND '$week_end 23:59:59'))
            AND u.IdUsuario = " . $rs['IdUsuario'] . " AND (nt.IdEstatusAtencion=9) AND nt.IdEstatusAtencion NOT IN(12, 22, 47, 17, 21, 24, 58, 60) $where1
            AND !ISNULL(nr.IdNotaTicket) AND nr.Cantidad > 0
            GROUP BY u.IdUsuario, t.IdTicket
            ORDER BY t.IdTicket) AS t_1";
        $pendientes = 0;
        $result2 = $catalogo->obtenerLista($consulta);
        while ($rs2 = mysql_fetch_array($result2)) {
            $pendientes = (int) $rs2['TicketsPendientes'];
        }

        $divisor = ((int) $rs['Cuenta']) - $pendientes;
        if ($divisor == 0) {
            $divisor = 1;
        }

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('E' . $fila_inicial, $rs['Usuario'] . " (" . $rs['Loggin'] . ")")
                ->setCellValue('F' . $fila_inicial, $rs['Cuenta'])
                ->setCellValue('G' . $fila_inicial, $cerrados)
                ->setCellValue('H' . $fila_inicial, $pendientes)
                ->setCellValue('I' . $fila_inicial, number_format((((float) $rs['Cuenta']) / 5), 2))
                ->setCellValue('J' . $fila_inicial, number_format(( ($cerrados / ($divisor)) * 100), 2));
        $total_area += (int) $rs['Cuenta'];
        if ($bool) {
            cellColor($objPHPExcel, 'E' . $fila_inicial . ':J' . $fila_inicial, 'ddebf7'); //TITULO REPORTE
            $bool = FALSE;
        } else {
            $bool = TRUE;
        }
        $fila_inicial++;
    }

    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
// Renombrar Hoja
    $objPHPExcel->getActiveSheet()->setTitle('Tickets');

// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
    $objPHPExcel->setActiveSheetIndex(0);
    
    $nombre = $empresa.'_ReporteSemanalTickets'.$meses[$month-1].$year;
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    
    if($parametroGlobal->getRegistroById("11")){
        $path = $parametroGlobal->getValor();
    }else{
        $path = "/html/www/";
    }
    
    $ruta_server = $path.''.$nombre.'.xls';
    $objWriter->save($ruta_server);
    
// Enviamos por correo el archivo generado
    $mail = new Mail();
    $mail->setEmpresa($empresa);
    $mail->setAttachPDF($ruta_server);
    
    if($parametroGlobal->getRegistroById("8")){
        $mail->setFrom($parametroGlobal->getValor());
    }else{
        $mail->setFrom("scg-salida@scgenesis.mx");
    }
    $mail->setSubject("Reporte Semanal de tickets ". $catalogo->formatoFechaReportes($week_end));
    $message = "<br/>A continuación se adjunta el reporte semanal de tickets del " . $catalogo->formatoFechaReportes($week_start) . ' al ' . $catalogo->formatoFechaReportes($week_end)." <br/>";
    $mail->setBody($message);
    /* Obtenemos los correos a quien mandaremos el mail */
        
    $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 19;");
    $correos = array();
    $z = 0;
    while ($rs = mysql_fetch_array($query4)) {
        $correos[$z] = $rs['correo'];
        $z++;
    }
    foreach ($correos as $value) {
        if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
            $mail->setTo($value);
            if ($mail->enviarMailPDF() == "1") {
                echo "<br/>Un correo fue enviado a $value.";
            } else {
                echo "<br/>Error: No se pudo enviar el correo para autorizar.";
            }
        }
    }

}
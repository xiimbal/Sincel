<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['no_serie'])) {
    header("Location: ../index.php");
}
if (isset($_POST['boton_almacen']) && $_POST['boton_almacen'] == "Buscar") {
    header('Content-Type: text/html; charset=utf-8');
    ini_set("memory_limit", "256M");
    set_time_limit(0);

    include_once("../WEB-INF/Classes/MovimientoAlmacen.class.php");
    include_once("../WEB-INF/Classes/Catalogo.class.php");
    $movimiento = new MovimientoAlmacen();
    $catalogo = new Catalogo();
    ?>
    <!DOCTYPE>
    <html lang="es">
        <head>
            <style>
                table{
                    border-collapse:collapse;
                    width: 100%;
                }            
                .borde{border: 1px solid #000;}
            </style>
            <title>Reporte de movimientos en almacén</title>
            <link rel="icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
        </head>
        <body>
            <a href=javascript:window.print(); style="margin: 85%;">Imprimir</a>
            <?php
            echo "Filtros empleados:<br/>";
            if (isset($_POST['no_serie']) && $_POST['no_serie'] != "") {
                echo "<br/><b>No. serie: </b>" . $_POST['no_serie'];
            }
            $tipos = array();
            if (isset($_POST['tipo'])) {
                $tipos = $_POST['tipo'];

                echo "<br/><b>Tipo: </b>";
                foreach ($tipos as $value) {
                    if ($value == "0") {
                        echo "Equipo, ";
                        if ($_POST['modelo'] != "") {
                            echo "<br/><b>Modelo: </b>";
                            $result = $catalogo->obtenerLista("SELECT Modelo FROM `c_equipo` WHERE NoParte = '" . $_POST['modelo'] . "';");
                            while ($rs = mysql_fetch_array($result)) {
                                echo $rs['Modelo'];
                            }
                        }
                    } else {
                        $result = $catalogo->obtenerLista("SELECT Nombre FROM `c_tipocomponente` WHERE IdTipoComponente = " . $value . ";");
                        while ($rs = mysql_fetch_array($result)) {
                            echo $rs['Nombre'] . ", ";
                        }
                        if ($_POST['modelo'] != "") {
                            echo "<br/><b>Modelo: </b>";
                            $result = $catalogo->obtenerLista("SELECT Modelo FROM `c_componente` WHERE NoParte = '" . $_POST['modelo'] . "';");
                            while ($rs = mysql_fetch_array($result)) {
                                echo $rs['Modelo'];
                            }
                        }
                    }
                }
            }

            echo "<br/><b>Entrada/Salida: </b>";
            if ($_POST['tipo_es'] == "0") {
                echo "Todos";
            } else if ($_POST['tipo_es'] == "1") {
                echo "Entradas";
            } else if ($_POST['tipo_es'] == "2") {
                echo "Salidas";
            }
            echo "<br/><b>Cliente: </b>";
            if ($_POST['cliente'] == "0") {
                echo "Todos los clientes";
            } else {
                $result = $catalogo->obtenerLista("SELECT NombreRazonSocial FROM c_cliente WHERE ClaveCliente = '" . $_POST['cliente'] . "'");
                while ($rs = mysql_fetch_array($result)) {
                    echo $rs['NombreRazonSocial'];
                }
            }
            echo "<br/><b>Localidad: </b>";
            if ($_POST['localidad'] == "") {
                echo "Todas las localidades";
            } else {
                $result = $catalogo->obtenerLista("SELECT Nombre FROM c_centrocosto WHERE ClaveCentroCosto = '" . $_POST['localidad'] . "'");
                while ($rs = mysql_fetch_array($result)) {
                    echo $rs['Nombre'];
                }
            }

            if (isset($_POST['fecha_inicio']) && $_POST['fecha_inicio'] != "") {
                echo "<br/><b>Fecha Inicio: </b>" . $_POST['fecha_inicio'];
            }
            if (isset($_POST['fecha_fin']) && $_POST['fecha_fin'] != "") {
                echo "<br/><b>Fecha fin: </b>" . $_POST['fecha_fin'];
            }
            echo "<br/><b>Almacén: </b>";
            if ($_POST['almacen'] == "0") {
                echo "Todos los almacenes";
            } else {
                $result = $catalogo->obtenerLista("SELECT nombre_almacen FROM `c_almacen` WHERE id_almacen = " . $_POST['almacen'] . ";");
                while ($rs = mysql_fetch_array($result)) {
                    echo $rs['nombre_almacen'];
                }
            }

            echo "<br/><br/><table>";
            echo "<thead><tr>";
            echo "<th class='borde'>Tipo</th>
                    <th class='borde'>Fecha</th><th class='borde'>Almacen</th><th class='borde'>Movimiento</th><th class='borde'>Modelo</th>
                    <th class='borde'>Cantidad</th>";
            echo "<th class='borde'>NoSerie</th><th class='borde'>Cliente o almacén</th><th class='borde'>Localidad</th>
                    <th class='borde'>Comentario</th><th class='borde'>Ticket</th>
                    <th class='borde'>Serie Destino</th><th class='borde'>Modelo Destino</th>
                    <th class='borde'>Estado Destino</th><th class='borde'>Delegación Destino</th>
                    <th class='borde'>Usuario</th>";
            echo "</tr></thead>";
            echo "<tbody>";
            if (($_POST['no_serie'] != "") || in_array("0", $tipos) && count($tipos) == 1) {/* Vamos a obtener movimientos solo de equipos */
                if (!isset($_POST['ticket']) || $_POST['ticket'] == "") {//El ticket es exclusivo de componentes, así que si hay ticket filtrado, no se muestran equipos
                    $result = $catalogo->obtenerLista($movimiento->generarConsultaEquipos($_POST['no_serie'], $_POST['modelo'], $_POST['tipo_es'], $_POST['cliente'], $_POST['localidad'], $_POST['fecha_inicio'], $_POST['fecha_fin'], $_POST['almacen'], $_POST['ticket'], $_POST['compras']));
                    $movimiento->imprimirFilasEquipos($result);
                }
            } else if (!in_array("0", $tipos) && count($tipos) > 0) {/* Vamos a obtener movimientos solo de componentes */
                $result = $catalogo->obtenerLista($movimiento->generarConsultaComponentes($_POST['tipo'], $_POST['no_serie'], $_POST['modelo'], $_POST['tipo_es'], $_POST['cliente'], $_POST['localidad'], $_POST['fecha_inicio'], $_POST['fecha_fin'], $_POST['almacen'], $_POST['ticket'], $_POST['compras']));
                $movimiento->imprimirFilasComponentes($result, $_POST['tipo_es'], $_POST['almacen']);
            } else {/* Vamos a obtener movimientos de equipos y componentes por igual */
                if (!isset($_POST['ticket']) || $_POST['ticket'] == "") {//El ticket es exclusivo de componentes, así que si hay ticket filtrado, no se muestran equipos
                    $result = $catalogo->obtenerLista($movimiento->generarConsultaEquipos($_POST['no_serie'], $_POST['modelo'], $_POST['tipo_es'], $_POST['cliente'], $_POST['localidad'], $_POST['fecha_inicio'], $_POST['fecha_fin'], $_POST['almacen'], $_POST['ticket'], $_POST['compras']));
                    $movimiento->imprimirFilasEquipos($result);
                }

                $result2 = $catalogo->obtenerLista($movimiento->generarConsultaComponentes($_POST['tipo'], $_POST['no_serie'], $_POST['modelo'], $_POST['tipo_es'], $_POST['cliente'], $_POST['localidad'], $_POST['fecha_inicio'], $_POST['fecha_fin'], $_POST['almacen'], $_POST['ticket'], $_POST['compras']));
                $movimiento->imprimirFilasComponentes($result2, $_POST['tipo_es'], $_POST['almacen']);
            }
            echo "</tbody>";
            echo "</table>";
            ?> 
        </body>
    </html>
            <?php
        } else if (isset($_POST['boton_almacen']) && $_POST['boton_almacen'] == "Excel") {
            ini_set("memory_limit", "512M");
            set_time_limit(0);

            require_once('../WEB-INF/Classes/PHPExcel/IOFactory.php');
            require_once('../WEB-INF/Classes/PHPExcel.php');
            include_once('../WEB-INF/Classes/Catalogo.class.php');
            include_once("../WEB-INF/Classes/MovimientoAlmacen.class.php");

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

            $dias = array("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado");
            $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
            $fecha = $dias[date('w')] . ", " . date('j') . " de " . $meses[date('n') - 1] . " del " . date('Y');

            $tipos = array();
            if (isset($_POST['tipo'])) {
                $tipos = $_POST['tipo'];
            }

            $catalogo = new Catalogo();
            $movimiento = new MovimientoAlmacen();
            $objPHPExcel = new PHPExcel();
            // Establecer propiedades
            $objPHPExcel->getProperties()
                    ->setCreator("")
                    ->setLastModifiedBy("")
                    ->setTitle("Documento Excel")
                    ->setSubject("Documento Excel")
                    ->setDescription("Reporte de almacenes")
                    ->setKeywords("Excel Office 2007 openxml php")
                    ->setCategory("Reportes");

            $fila_inicial = 2;
            $fila_inicial_backup = $fila_inicial;
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . (1), 'REPORTE DE MOVIMIENTOS DE ALMACÉN')->mergeCells('A1:I1')
                    ->setCellValue('J' . (1), $fecha)->mergeCells('J1:P1')
                    ->setCellValue('A' . ($fila_inicial), 'Tipo')
                    ->setCellValue('B' . ($fila_inicial), 'Fecha')
                    ->setCellValue('C' . ($fila_inicial), 'Almacén')
                    ->setCellValue('D' . ($fila_inicial), 'Movimiento')
                    ->setCellValue('E' . ($fila_inicial), 'Modelo')
                    ->setCellValue('F' . ($fila_inicial), 'Cantidad')
                    ->setCellValue('G' . ($fila_inicial), 'NoSerie')
                    ->setCellValue('H' . ($fila_inicial), 'Cliente o almacén')
                    ->setCellValue('I' . ($fila_inicial), 'Localidad')
                    ->setCellValue('J' . ($fila_inicial), 'Comentario')
                    ->setCellValue('K' . ($fila_inicial), 'Ticket')
                    ->setCellValue('L' . ($fila_inicial), 'Serie destino')
                    ->setCellValue('M' . ($fila_inicial), 'Modelo destino')
                    ->setCellValue('N' . ($fila_inicial), 'Estado Destino')
                    ->setCellValue('O' . ($fila_inicial), 'Delegación Destino')
                    ->setCellValue('P' . ($fila_inicial), 'Usuario');
            $fila_inicial++;
            $bool = TRUE;
            $result = "";
            $result2 = "";

            if (($_POST['no_serie'] != "") || in_array("0", $tipos) && count($tipos) == 1) {/* Vamos a obtener movimientos solo de equipos */
                if (!isset($_POST['ticket']) || $_POST['ticket'] == "") {//El ticket es exclusivo de componentes, así que si hay ticket filtrado, no se muestran equipos
                    $result = $catalogo->obtenerLista($movimiento->generarConsultaEquipos($_POST['no_serie'], $_POST['modelo'], $_POST['tipo_es'], $_POST['cliente'], $_POST['localidad'], $_POST['fecha_inicio'], $_POST['fecha_fin'], $_POST['almacen'], $_POST['ticket'], $_POST['compras']));
                }
            } else if (!in_array("0", $tipos) && count($tipos) > 0) {/* Vamos a obtener movimientos solo de componentes */
                $result2 = $catalogo->obtenerLista($movimiento->generarConsultaComponentes($_POST['tipo'], $_POST['no_serie'], $_POST['modelo'], $_POST['tipo_es'], $_POST['cliente'], $_POST['localidad'], $_POST['fecha_inicio'], $_POST['fecha_fin'], $_POST['almacen'], $_POST['ticket'], $_POST['compras']));
            } else {/* Vamos a obtener movimientos de equipos y componentes por igual */
                if (!isset($_POST['ticket']) || $_POST['ticket'] == "") {//El ticket es exclusivo de componentes, así que si hay ticket filtrado, no se muestran equipos
                    $result = $catalogo->obtenerLista($movimiento->generarConsultaEquipos($_POST['no_serie'], $_POST['modelo'], $_POST['tipo_es'], $_POST['cliente'], $_POST['localidad'], $_POST['fecha_inicio'], $_POST['fecha_fin'], $_POST['almacen'], $_POST['ticket'], $_POST['compras']));
                }
                $result2 = $catalogo->obtenerLista($movimiento->generarConsultaComponentes($_POST['tipo'], $_POST['no_serie'], $_POST['modelo'], $_POST['tipo_es'], $_POST['cliente'], $_POST['localidad'], $_POST['fecha_inicio'], $_POST['fecha_fin'], $_POST['almacen'], $_POST['ticket'], $_POST['compras']));
            }                        

            if (isset($result) && $result != "") {
                $estado = ""; $delegacion = "";                                
                while ($rs = mysql_fetch_array($result)) {
                    $almacen = "";
                    if (isset($rs['almacen'])) {
                        $almacen = $rs['almacen'];
                    }
                    $cliente = "";
                    if (isset($rs['cliente']) && $rs['cliente'] != $almacen) {
                        $cliente = $rs['cliente'];
                    }
                    
                    if($rs['ES'] == "Salida"){//Salida del almacen, es decir, destino es la localidad
                        $estado = $rs['EstadoLocalidad'];
                        $delegacion = $rs['DelegacionLocalidad'];
                    }else if($rs['ES'] == "Entrada"){//Entrada al almacen, es decir. destino es almacen
                        $estado = $rs['EstadoAlmacen'];
                        $delegacion = $rs['DelegacionAlmacen'];
                    }

                    if (isset($rs['id_reportes'])) {
                        $x = "Movimiento: " . $rs['id_reportes'];
                    } else if ($rs['id_compra'] && is_numeric($rs['id_compra'])) {
                        $x = "Compra: " . $rs['id_compra'];
                    } else {
                        switch ($rs['Pantalla']) {
                            case "Entrada_Orden_Individual":
                                $x = "Entrada por compra";
                                break;
                            case "PHP Almacen-Equipo":
                                $x = "Entrada inventario equipo";
                                break;
                            case "PHP Movimiento_equipos_solicitud":
                                $x = "Solicitud de equipo";
                                break;
                            default:
                                $x = $rs['Pantalla'];
                                break;
                        }
                    }

                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . ($fila_inicial), "Equipo")
                            ->setCellValue('B' . ($fila_inicial), $rs['Fecha'])
                            ->setCellValue('C' . ($fila_inicial), $almacen)
                            ->setCellValue('D' . ($fila_inicial), $rs['ES'])
                            ->setCellValue('E' . ($fila_inicial), $rs['Modelo'])
                            ->setCellValue('F' . ($fila_inicial), "1")
                            ->setCellValue('G' . ($fila_inicial), $rs['NoSerie'])
                            ->setCellValue('H' . ($fila_inicial), $cliente)
                            ->setCellValue('I' . ($fila_inicial), $rs['localidad'])
                            ->setCellValue('J' . ($fila_inicial), $rs['Comentario'])
                            ->setCellValue('K' . ($fila_inicial), $x)
                            ->setCellValue('L' . ($fila_inicial), "")
                            ->setCellValue('M' . ($fila_inicial), "")
                            ->setCellValue('N' . ($fila_inicial), $estado)
                            ->setCellValue('O' . ($fila_inicial), $delegacion)
                            ->setCellValue('P' . ($fila_inicial), $rs['UsuarioUltimaModificacion']);
                    if ($bool) {
                        cellColor($objPHPExcel, 'A' . $fila_inicial . ':P' . $fila_inicial, 'ddebf7'); //TITULO REPORTE
                        $bool = FALSE;
                    } else {
                        $bool = TRUE;
                    }
                    $fila_inicial++;
                }
            }
            if (isset($result2) && $result2 != "") {
                $estado = ""; $delegacion = "";                
            
                $array_series_destino = array();
                while ($rs = mysql_fetch_array($result2)) {
                    /*if ($_POST['tipo_es'] == 2 && $_POST['almacen'] != null && $_POST['almacen'] != "" && $_POST['almacen'] != "0" && $_POST['almacen'] != $rs['idAlmacenOrigen']) {//Si es salida, no se pintan salidas desde otro almacen
                        continue;
                    }*/
                    $almacen = "";
                    if (isset($rs['almacen'])) {
                        $almacen = $rs['almacen'];
                    }
                    $cliente = "";
                    if (isset($rs['cliente']) && $rs['cliente'] != $almacen) {
                        $cliente = $rs['cliente'];
                    }
                    
                    $es = $rs['ES'];
                    /*if( ($_POST['tipo_es'] == 1 || $_POST['tipo_es'] == 2) && !empty($_POST['almacen']) && $rs['ES'] == "Entrada" && $rs['IdalmacenAux'] != $_POST['almacen']){//Cambiamos para que las "entradas" de otros almacenes, sean salidas del almacen seleccionado
                        $aux = $almacen;
                        $almacen = $cliente;
                        $cliente = $aux;
                        $es = "Salida";
                    }*/
                    
                    //$es = $rs['ES'];
                    if($rs['IdalmacenAux'] != $_POST['almacen'] && $rs['ES'] == "Salida"){//Cambiamos para que las "entradas" de otros almacenes, sean salidas del almacen seleccionado
                        $aux = $almacen;
                        $almacen = $cliente;
                        $cliente = $aux;
                        $es = "Salida";
                    }
                    
                    if($rs['ES'] == "Salida"){//Salida del almacen, es decir, destino es la localidad
                        $estado = $rs['EstadoLocalidad'];
                        $delegacion = $rs['DelegacionLocalidad'];
                    }else if($rs['ES'] == "Entrada"){//Entrada al almacen, es decir. destino es almacen
                        $estado = $rs['EstadoAlmacen'];
                        $delegacion = $rs['DelegacionAlmacen'];
                    }
                    
                    $serie = "";
                    $modelo = "";
                    if (isset($rs['IdTicket']) && $rs['IdTicket'] != "") {
                        $x = "Ticket: " . $rs['IdTicket'];
                        if ($rs['TipoReporte'] != "15") {
                            $serie = $rs['NoSerieEquipo'];
                            $modelo = $rs['ModeloEquipo'];
                        } else if ($rs['Resurtido'] == "1") {
                            $serie = "Resurtido de mini-almacén";
                        } else {//En caso que sea un ticket de toner y que no sea de resurtido, se busca a que equipo había sido solicitado                    
                            $consulta = "SELECT dnr.Componente, dnr.NoSerieEquipo, dnr.Cantidad, e.Modelo  
                            FROM c_notaticket AS nt
                            LEFT JOIN k_detalle_notarefaccion AS dnr ON dnr.IdNota = nt.IdNotaTicket
                            LEFT JOIN c_bitacora AS b ON b.NoSerie = dnr.NoSerieEquipo
                            LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
                            WHERE nt.IdTicket = " . $rs['IdTicket'] . " AND nt.IdEstatusAtencion = 67 AND dnr.Componente = '" . $rs['NoParteComponente'] . "'
                            ORDER BY NoSerieEquipo;";
                            $result3 = $catalogo->obtenerLista($consulta);
                            while ($rs2 = mysql_fetch_array($result3)) {
                                if (!isset($array_series_destino[$rs['IdTicket']][$rs['NoParteComponente']][$rs2['NoSerieEquipo']])) {
                                    $serie = $rs2['NoSerieEquipo'];
                                    $modelo = $rs2['Modelo'];
                                    $array_series_destino[$rs['IdTicket']][$rs['NoParteComponente']][$rs2['NoSerieEquipo']] = true;
                                    break;
                                }
                            }
                        }
                    } else if ($rs['id_compra'] && is_numeric($rs['id_compra'])) {
                        $x = "Compra: " . $rs['id_compra'];
                    } else {
                        if ($rs['Pantalla'] == "PHP Movimiento_equipos_solicitud") {
                            $x = "Solicitud de equipo";
                        } else if ($rs['Pantalla'] == "Entrada al almacén" || $rs['Pantalla'] == "Salida del almacén") {
                            $x = "Entrada manual al almacén";
                        }
                    }

                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . ($fila_inicial), $rs['TipoComponente'])
                            ->setCellValue('B' . ($fila_inicial), $rs['Fecha'])
                            ->setCellValue('C' . ($fila_inicial), $almacen)
                            ->setCellValue('D' . ($fila_inicial), $es)
                            ->setCellValue('E' . ($fila_inicial), $rs['Modelo'])
                            ->setCellValue('F' . ($fila_inicial), $rs['CantidadMovimiento'])
                            ->setCellValue('G' . ($fila_inicial), $rs['NoParteComponente'])
                            ->setCellValue('H' . ($fila_inicial), $cliente)
                            ->setCellValue('I' . ($fila_inicial), $rs['localidad'])
                            ->setCellValue('J' . ($fila_inicial), $rs['Comentario'])
                            ->setCellValue('K' . ($fila_inicial), $x)
                            ->setCellValue('L' . ($fila_inicial), $serie)
                            ->setCellValue('M' . ($fila_inicial), $modelo)
                            ->setCellValue('N' . ($fila_inicial), $estado)
                            ->setCellValue('O' . ($fila_inicial), $delegacion )
                            ->setCellValue('P' . ($fila_inicial), $rs['UsuarioUltimaModificacion']);
                    if ($bool) {
                        cellColor($objPHPExcel, 'A' . $fila_inicial . ':P' . $fila_inicial, 'ddebf7'); //TITULO REPORTE
                        $bool = FALSE;
                    } else {
                        $bool = TRUE;
                    }
                    $fila_inicial++;
                }
            }
            //

            cellColor($objPHPExcel, 'A1:N1', '5b9bd5'); //TITULO REPORTE
            cellColor($objPHPExcel, 'A' . $fila_inicial_backup . ':P' . $fila_inicial_backup, '5b9bd5'); //TITULO REPORTE
            $styleArray = getStyle(true, "000000", 12, "Arial", false);
            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArray); /* TITULO */
            $styleArray = getStyle(true, "000000", 10, "Arial", false);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inicial_backup . ':P' . $fila_inicial_backup)->applyFromArray($styleArray); /* Cabeceras de la tabla */
            $styleArray = getStyle(true, "000000", 9, "Arial", false);
            $objPHPExcel->getActiveSheet()->getStyle('J1:N1')->applyFromArray($styleArray); /* Fecha y hora */

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(60);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(60);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);

            // Renombrar Hoja
            $objPHPExcel->getActiveSheet()->setTitle('Movimientos de almacenes');

            // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
            $objPHPExcel->setActiveSheetIndex(0);

            // Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Movimientos de almacenes.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }
        ?>
<?php

session_start();
    
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$catalogo = new Catalogo();
$permisos_grid = new PermisosSubMenu();

$arrayFacturas = array();
$hayIVA = false;
$iva = 1.16;

$fecha = "'".date("Y-m",strtotime("-1 month"))."-01'";//Para precargar mes anterior
$fechaText = date("Y-m",strtotime("-1 month"))."-01"; //Para precargar mes anterior

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}
if(isset($parametros['iva']) && $parametros['iva'] != ""){
    $hayIVA = true;
}

if(isset($parametros['fecha']) && $parametros['fecha'] != ""){
    $fechaText = $parametros['fecha'];
    $fecha = "'".$parametros['fecha']."'";    
}
$contratoActual = "";
$contratos_procesados = array();
$servicio_procesado = array();
$contadorAnexosAFacturarDia = 0;
$contadorAnexosFacturadosDia = 0;
$contadorAnexosAFacturarMes = 0;
$contadorAnexosFacturadosMes = 0;
$contadorContratosAFacturarMes = 0;
$contadorContratosFacturadosMes = 0;
$contadorContratosAFacturarDia = 0;
$contadorContratosFacturadosDia = 0;
$totalFacturadoMes = 0;
$totalFacturadoDia = 0;
$totalRentasMes = 0;
$totalRentasDia = 0;
$totalAFacturarMes = 0;
$totalAFacturarDia = 0;
$contratoFacturado = false;
$contratoFacturadoDia = false;

$consultaTotales = "SELECT ctt.NoContrato, f.IdFactura AS IdFactura,
		(CASE 
		WHEN !ISNULL(kgim.IdKServicioGIM) THEN kgim.IdKServicioGIM 
		WHEN !ISNULL(kgfa.IdKServicioGFA) THEN kgfa.IdKServicioGFA
		END) AS IdKServicio,
		(CASE WHEN DATE(f.FechaCreacion) = DATE(NOW()) THEN 1 ELSE 0 END) AS facturadoHoy,
		(SELECT 
		(CASE WHEN ISNULL(IdFacturaDetalle) THEN NULL 
		WHEN IdServicio < 1000 THEN COUNT(IdFactura) 
		ELSE 1 END) 
		FROM c_facturadetalle
		WHERE IdFactura = f.IdFactura AND 
		(
		(IdKServicio = kgim.IdKServicioGIM AND IdServicio = kgim.IdServicioGIM) OR
		(IdKServicio = kgfa.IdKServicioGFA AND IdServicio = kgfa.IdServicioGFA)
		) 
		) AS NumeroEquipos, 
		(SELECT 
		(CASE 
		WHEN IdServicio < 1000 THEN COUNT(IdFactura) * RentaMensual 
		ELSE RentaMensual END) 
		FROM c_facturadetalle 
		WHERE IdFactura = f.IdFactura AND 
		(
		(IdKServicio = kgim.IdKServicioGIM AND IdServicio = kgim.IdServicioGIM) OR
		(IdKServicio = kgfa.IdKServicioGFA AND IdServicio = kgfa.IdServicioGFA)
		) 
		) 
		AS RentaFacturada,  
		fp.FolioTimbrado, 
		(
		SELECT (CASE WHEN !ISNULL(f2.IdFactura) THEN fp.FolioTimbrado ELSE NULL END) 
		FROM c_factura AS f2 
		WHERE f2.IdFactura = f.IdFactura
		AND f2.idFactura = 
		(
				SELECT MIN(IdFactura) FROM c_facturadetalle WHERE 
				IdFactura = f.IdFactura AND 
				(
						(IdKServicio = kgim.IdKServicioGIM AND IdServicio = kgim.IdServicioGIM) OR
						(IdKServicio = kgfa.IdKServicioGFA AND IdServicio = kgfa.IdServicioGFA)
				) 
		)
		) AS FolioTimbradoCalculado,
		fd.RentaMensual,
		c.NombreRazonSocial,
		(
		SELECT SUM(cpt.Cantidad * cpt.PrecioUnitario) 
		FROM c_conceptos AS cpt 
		WHERE cpt.idFactura = f.IdFactura
		AND cpt.idFactura = 
		(
				SELECT MIN(IdFactura) FROM c_facturadetalle WHERE 
				IdFactura = f.IdFactura AND 
				(
				(IdKServicio = kgim.IdKServicioGIM AND IdServicio = kgim.IdServicioGIM) OR
				(IdKServicio = kgfa.IdKServicioGFA AND IdServicio = kgfa.IdServicioGFA)
				) 
		)
		) AS TotalSinIVA,
		(CASE WHEN DAY(kacc.Fecha) = DAY(NOW()) THEN 1 ELSE 0 END) AS hoy, 
		(CASE WHEN DAY(kacc.Fecha) < DAY(NOW()) THEN 1 ELSE 0 END) AS posibleAtrasado, 
		DAY(kacc.Fecha) AS DiaCorte,
		(CASE 
		WHEN !ISNULL(kgim.IdKServicioGIM) THEN kgim.RentaMensual 
		WHEN !ISNULL(kgfa.IdKServicioGFA) THEN kgfa.RentaMensual 
		ELSE 0 END) AS RentaAFacturar, cat.ClaveAnexoTecnico, c.ClaveCliente
		FROM c_contrato AS ctt
		LEFT JOIN c_cliente AS c ON c.ClaveCliente = ctt.ClaveCliente
		LEFT JOIN c_factura AS f ON (f.NoContrato = ctt.NoContrato AND MONTH($fecha) = MONTH(f.FechaFacturacion) AND YEAR($fecha) = YEAR(f.FechaFacturacion) AND !ISNULL(f.Sello))
		LEFT JOIN c_folio_prefactura AS fp ON (f.Folio = fp.Folio AND f.RFCEmisor = fp.IdEmisor)
		LEFT JOIN c_facturadetalle AS fd ON fd.IdFactura = f.IdFactura
		LEFT JOIN c_anexotecnico AS cat ON cat.NoContrato = ctt.NoContrato AND cat.Activo = 1
		LEFT JOIN k_anexoclientecc AS kacc ON kacc.ClaveAnexoTecnico = cat.ClaveAnexoTecnico
		LEFT JOIN k_serviciogim AS kgim ON kgim.IdAnexoClienteCC = kacc.IdAnexoClienteCC
		LEFT JOIN k_serviciogfa AS kgfa ON kgfa.IdAnexoClienteCC = kacc.IdAnexoClienteCC
		WHERE ctt.Activo = 1 AND (ISNULL(f.IdFactura) OR (!ISNULL(fp.FolioTimbrado)))
		AND (
				(!ISNULL(kgim.IdKServicioGIM) AND kgim.FechaCreacion < DATE_ADD($fecha, INTERVAL 1 month )) 
				OR (!ISNULL(kgfa.IdKServicioGFA) AND kgfa.FechaCreacion < DATE_ADD($fecha, INTERVAL 1 month ))
		)
		GROUP BY ctt.NoContrato, FolioTimbradoCalculado, kacc.IdAnexoClienteCC, kgim.IdKServicioGIM, kgfa.IdKServicioGFA
		HAVING ISNULL(IdFactura) OR (!ISNULL(IdFactura) AND !ISNULL(FolioTimbradoCalculado))

		UNION 
			
		SELECT ctt.NoContrato, f.IdFactura AS IdFactura,
		(CASE 
		WHEN !ISNULL(kim.IdKServicioIM) THEN kim.IdKServicioIM 
		WHEN !ISNULL(kfa.IdKServicioFA) THEN kfa.IdKServicioFA
		END) AS IdKServicio,
		(CASE WHEN DATE(f.FechaCreacion) = DATE(NOW()) THEN 1 ELSE 0 END) AS facturadoHoy,
		(SELECT 
		(CASE WHEN ISNULL(IdFacturaDetalle) THEN NULL 
		WHEN IdServicio < 1000 THEN COUNT(IdFactura) 
		ELSE 1 END) 
		FROM c_facturadetalle
		WHERE IdFactura = f.IdFactura AND 
		(
		(IdKServicio = kim.IdKServicioIM AND IdServicio = kim.IdServicioIM) OR
		(IdKServicio = kfa.IdKServicioFA AND IdServicio = kfa.IdServicioFA)
		) 
		) AS NumeroEquipos, 
		(SELECT 
		(CASE 
		WHEN IdServicio < 1000 THEN COUNT(IdFactura) * RentaMensual 
		ELSE RentaMensual END) 
		FROM c_facturadetalle 
		WHERE IdFactura = f.IdFactura AND 
		(
		(IdKServicio = kim.IdKServicioIM AND IdServicio = kim.IdServicioIM) OR
		(IdKServicio = kfa.IdKServicioFA AND IdServicio = kfa.IdServicioFA)
		) 
		) 
		AS RentaFacturada,  
		fp.FolioTimbrado, 
		(
		SELECT (CASE WHEN !ISNULL(f2.IdFactura) THEN fp.FolioTimbrado ELSE NULL END) 
		FROM c_factura AS f2 
		WHERE f2.IdFactura = f.IdFactura
		AND f2.idFactura = 
		(
				SELECT MIN(IdFactura) FROM c_facturadetalle WHERE 
				IdFactura = f.IdFactura AND 
				(
						(IdKServicio = kim.IdKServicioIM AND IdServicio = kim.IdServicioIM) OR
						(IdKServicio = kfa.IdKServicioFA AND IdServicio = kfa.IdServicioFA)
				) 
		)
		) AS FolioTimbradoCalculado,
		fd.RentaMensual,
		c.NombreRazonSocial,
		(
		SELECT SUM(cpt.Cantidad * cpt.PrecioUnitario) 
		FROM c_conceptos AS cpt 
		WHERE cpt.idFactura = f.IdFactura
		AND cpt.idFactura = 
		(
				SELECT MIN(IdFactura) FROM c_facturadetalle WHERE 
				IdFactura = f.IdFactura AND 
				(
				(IdKServicio = kim.IdKServicioIM AND IdServicio = kim.IdServicioIM) OR
				(IdKServicio = kfa.IdKServicioFA AND IdServicio = kfa.IdServicioFA)
				) 
		)
		) AS TotalSinIVA,
		(CASE WHEN DAY(kacc.Fecha) = DAY(NOW()) THEN 1 ELSE 0 END) AS hoy, 
		(CASE WHEN DAY(kacc.Fecha) < DAY(NOW()) THEN 1 ELSE 0 END) AS posibleAtrasado, 
		DAY(kacc.Fecha) AS DiaCorte,
		(CASE 
		WHEN !ISNULL(kim.IdKServicioIM)  AND kim.IdServicioIM <> 300
		THEN 
		kim.RentaMensual * 
		(
				SELECT COUNT(cie_1.NoSerie) AS cuenta
				FROM `k_servicioim` AS kim_1
				INNER JOIN c_inventarioequipo AS cie_1 ON 
				(cie_1.IdKServicio = kim_1.IdKServicioIM OR (ISNULL(cie_1.IdKServicio) AND cie_1.IdAnexoClienteCC = kim_1.IdAnexoClienteCC))
				AND cie_1.ClaveEspKServicioFAIM = kim_1.IdServicioIM
				LEFT JOIN c_bitacora AS b_1 ON b_1.NoSerie = cie_1.NoSerie
				WHERE kim_1.IdKServicioIM = kim.IdKServicioIM AND b_1.VentaDirecta = 0
		)
		WHEN !ISNULL(kfa.IdKServicioFA) AND kfa.IdServicioFA <> 3
		THEN 
		kfa.RentaMensual *
		(
				SELECT COUNT(cie_1.NoSerie) AS cuenta
				FROM `k_serviciofa` AS kim_1
				INNER JOIN c_inventarioequipo AS cie_1 ON 
				(cie_1.IdKServicio = kim_1.IdKServicioFA OR (ISNULL(cie_1.IdKServicio) AND cie_1.IdAnexoClienteCC = kim_1.IdAnexoClienteCC))
				AND cie_1.ClaveEspKServicioFAIM = kim_1.IdServicioFA
				LEFT JOIN c_bitacora AS b_1 ON b_1.NoSerie = cie_1.NoSerie
				WHERE kim_1.IdKServicioFA = kfa.IdKServicioFA AND b_1.VentaDirecta = 0
		) 
		ELSE 0 END) AS RentaAFacturar, cat.ClaveAnexoTecnico, c.ClaveCliente
		FROM c_contrato AS ctt
		LEFT JOIN c_cliente AS c ON c.ClaveCliente = ctt.ClaveCliente
		LEFT JOIN c_factura AS f ON (f.NoContrato = ctt.NoContrato AND MONTH($fecha) = MONTH(f.FechaFacturacion) AND YEAR($fecha) = YEAR(f.FechaFacturacion) AND !ISNULL(f.Sello))
		LEFT JOIN c_folio_prefactura AS fp ON (f.Folio = fp.Folio AND f.RFCEmisor = fp.IdEmisor)
		LEFT JOIN c_facturadetalle AS fd ON fd.IdFactura = f.IdFactura
		LEFT JOIN c_anexotecnico AS cat ON cat.NoContrato = ctt.NoContrato AND cat.Activo = 1
		LEFT JOIN k_anexoclientecc AS kacc ON kacc.ClaveAnexoTecnico = cat.ClaveAnexoTecnico
		LEFT JOIN k_servicioim AS kim ON kim.IdAnexoClienteCC = kacc.IdAnexoClienteCC
		LEFT JOIN k_serviciofa AS kfa ON kfa.IdAnexoClienteCC = kacc.IdAnexoClienteCC
		WHERE ctt.Activo = 1 AND (ISNULL(f.IdFactura) OR (!ISNULL(fp.FolioTimbrado)))
		AND (
				(!ISNULL(kim.IdKServicioIM) AND kim.FechaCreacion < DATE_ADD($fecha, INTERVAL 1 month ))
				OR (!ISNULL(kfa.IdKServicioFA)AND kfa.FechaCreacion < DATE_ADD($fecha, INTERVAL 1 month ))
		)
		GROUP BY ctt.NoContrato, FolioTimbradoCalculado, kacc.IdAnexoClienteCC, kim.IdKServicioIM, kfa.IdKServicioFA
		HAVING ISNULL(IdFactura) OR (!ISNULL(IdFactura) AND !ISNULL(FolioTimbradoCalculado))

		ORDER BY NombreRazonSocial;";

$result = $catalogo->obtenerLista($consultaTotales);
$tablaDetalle = "<table id='detalle' name='detalle' style='width: 100%;'>"
                ."<thead><tr>"
                ."<th>NoContrato</th>"
                ."<th>Servicio</th>"
                ."<th>Número Equipos</th>"
                ."<th>Folio factura timbrada</th>"
                ."<th>Renta Facturada</th>"
                ."<th>Total Factura SIN IVA</th>"
                ."<th>Cliente</th>"
                ."<th>Renta A Facturar</th>"               
                ."<th>Día corte</th>"
                ."<th>Facturado</th>"
                ."</tr></thead>"
                ."<tbody>";
$yaContado = false;
while ($rs = mysql_fetch_array($result)) {
    $contar_monto_factura = true;
    if(isset($rs['IdFactura'])){
        if(in_array($rs['IdFactura'], $arrayFacturas)){            
            $contar_monto_factura = false;
        }
        array_push($arrayFacturas, $rs['IdFactura']);
    }
    $facturado = false;
    if(isset($rs['FolioTimbradoCalculado']) && !empty($rs['FolioTimbradoCalculado'])){
        $facturado = true;
    }
    if((int)$rs['hoy'] == 1){
        $contadorAnexosAFacturarDia++;
    }else if((int)$rs['posibleAtrasado'] == 1 && !$facturado){
        $contadorAnexosAFacturarDia++;
    }
    if(isset($rs['RentaAFacturar']) && $rs['RentaAFacturar'] != 0){
        $totalAFacturarMes += (float)$rs['RentaAFacturar'];
        if((int)$rs['hoy'] == 1){
            $totalAFacturarDia += (float)$rs['RentaAFacturar'];
        }else if((int)$rs['posibleAtrasado'] == 1 && !$facturado){
            $totalAFacturarDia += (float)$rs['RentaAFacturar'];
        }
    }
    $contadorAnexosAFacturarMes++;
    $cadenaFacturada = "";
    $cadenaAFacturar = "";
    $cadenaTotalSINIVA = "";
    if(isset($rs['RentaFacturada'])){
        $cadenaFacturada = "$".number_format($rs['RentaFacturada'], 2, '.', ',');
    }
    if(isset($rs['RentaAFacturar'])){
        $cadenaAFacturar = "$".number_format($rs['RentaAFacturar'], 2, '.', ',');
    }
    if(isset($rs['TotalSinIVA'])){
        $cadenaTotalSINIVA = "$".number_format($rs['TotalSinIVA'], 2, '.', ',');
    }
    $hoy = "No";
    if(isset($rs['FolioTimbradoCalculado']) && !empty($rs['FolioTimbradoCalculado'])){
        $hoy = "Sí";
    }
    $tablaDetalle .= "<tr><td>".$rs['NoContrato']."</td>"
                    ."<td>".$rs['IdKServicio']."</td>"
                    ."<td>".$rs['NumeroEquipos']."</td>"
                    ."<td>".$rs['FolioTimbradoCalculado']."</td>"
                    ."<td>".$cadenaFacturada."</td>"
                    ."<td>".$cadenaTotalSINIVA."</td>"
                    ."<td>".$rs['NombreRazonSocial']."</td>"
                    ."<td>".$cadenaAFacturar."</td>"
                    ."<td>".$rs['DiaCorte']."</td>";
    if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 41) && $hoy == "No") {
        $tablaDetalle .= "<td><a target='_blank' href='contrato/Parametros_ReportePDF.php?anexo=".$rs['ClaveAnexoTecnico']. "&cliente=".$rs['ClaveCliente']."&contrato=".$rs['NoContrato']."&fecha=".$fechaText."'>$hoy</a></td></tr>";
    }else{
        $tablaDetalle .= "<td>$hoy</td></tr>";
    }
    //if(strcmp($rs['NoContrato'], $contratoActual) == 0){    //Es el mismo contrato que el anterior pero es otro anexo
    if(in_array($rs['NoContrato'], $contratos_procesados)){        
        if(!$facturado){//El anexo no ha sido facturado
            $contratoFacturado = false;
            $contratoFacturadoDia = false;
            if(!$yaContado){
                $contadorContratosAFacturarDia++;
                $yaContado = true;
            }
        }else{
            $contadorAnexosFacturadosMes++; 
            if($contar_monto_factura && isset($rs['TotalSinIVA']) && $rs['TotalSinIVA'] != ""){
                $totalFacturadoMes += (float)$rs['TotalSinIVA'];
            }
            if(isset($rs['RentaFacturada']) && $rs['RentaFacturada'] != ""){
                $totalRentasMes += (float)$rs['RentaFacturada'];
            }
            if((int)$rs['facturadoHoy'] == 1){
                $contadorAnexosFacturadosDia++;
                if($contar_monto_factura && isset($rs['TotalSinIVA']) && $rs['TotalSinIVA'] != ""){
                    $totalFacturadoDia += (float)$rs['TotalSinIVA'];
                }
                if(isset($rs['RentaFacturada']) && $rs['RentaFacturada'] != ""){
                    $totalRentasDia += (float)$rs['RentaFacturada'];
                }
            }else{
                $contratoFacturadoDia = false;
            }
        }
    }else{
        $yaContado = false;
        /*if($contratoFacturado){ //Si todos los anexos del contrato fueron facturados, el contrato ha sido facturado
            $contadorContratosFacturadosMes++;
        }*/
        if((int)$rs['facturadoHoy'] == 1 && isset($rs['FolioTimbradoCalculado']) && !empty($rs['FolioTimbradoCalculado'])){            
            $contadorContratosFacturadosDia++;
        }
        $contratoFacturado = false;
        $contadorContratosAFacturarMes++;
        $contratoActual = $rs['NoContrato'];   
        array_push($contratos_procesados, $rs['NoContrato']);
        if((int)$rs['hoy'] == 1){
            $contadorContratosAFacturarDia++;
        }
        
        if($facturado){ //Este anexo sí ha sido facturado
            $contratoFacturado = true;
            $contadorContratosFacturadosMes++;
            $contadorAnexosFacturadosMes++;
            if($contar_monto_factura && isset($rs['TotalSinIVA']) && $rs['TotalSinIVA'] != ""){
                $totalFacturadoMes += (float)$rs['TotalSinIVA'];
            }
            if(isset($rs['RentaFacturada']) && $rs['RentaFacturada'] != ""){
                $totalRentasMes += (float)$rs['RentaFacturada'];
            }
            if((int)$rs['facturadoHoy'] == 1){
                $contadorAnexosFacturadosDia++;
                $contratoFacturadoDia = true;
                if($contar_monto_factura && isset($rs['TotalSinIVA']) && $rs['TotalSinIVA'] != ""){
                    $totalFacturadoDia += (float)$rs['TotalSinIVA'];
                }
                if(isset($rs['RentaFacturada']) && $rs['RentaFacturada'] != ""){
                    $totalRentasDia += (float)$rs['RentaFacturada'];
                }
            }
        }else{
            if((int)$rs['posibleAtrasado'] == 1){
                $contadorContratosAFacturarDia++;
                $yaContado = true;
            }
        }
    }        
}
if($hayIVA){
    $totalAFacturarMes *= $iva;
    $totalAFacturarDia *= $iva;
    $totalFacturadoMes *= $iva;
    $totalFacturadoDia *= $iva;
    $totalRentasMes *= $iva;
    $totalRentasDia *= $iva;
}
$totalExcedentesMes = $totalFacturadoMes - $totalRentasMes;
$totalExcedentesDia = $totalFacturadoDia - $totalRentasDia;
$tablaDetalle .= "</tbody></table>";
$chartJQuery = "{" .
            "name: 'Faltantes'," .
            "y: " . ($contadorContratosAFacturarMes - $contadorContratosFacturadosMes).
            "},";
$chartJQuery .= "{" .
            "name: 'Facturados'," .
            "y: " . $contadorContratosFacturadosMes .
            "}";
$chartJQueryDia = "{" .
            "name: 'Faltantes'," .
            "y: " . ($contadorContratosAFacturarDia - $contadorContratosFacturadosDia) .
            "},";
$chartJQueryDia .= "{" .
            "name: 'Facturados'," .
            "y: " . $contadorContratosFacturadosDia .
            "}";
?>
<html>
    <head>
        <!--script src="../resources/js/jquery/jquery-1.11.3.min.js"></script-->
        <script type="text/javascript" src="resources/js/paginas/resumen.js"></script>
        <link href="resources/css/resumen.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div id="main_panel" style="clear: both;overflow: hidden;height: 1%;">
            <h1 style="border-bottom: 1px solid black;">Indicadores de desempeño</h1><br/>
            <div id="tabs">
                <ul>
                    <li><a href="#tabs-1">Resumen contratos</a></li>
                    <li><a href="#tabs-2">Indice de ingresos</a></li>
                    <li><a href="#tabs-3">Antigüedad de saldo</a></li>
                    <li><a href="#tabs-4">Top ten deudores</a></li>
                </ul>
                <div id="tabs-1">
                    <div id="arreglarAltura" style="height: 600px">
                    <form id="formResumenContratos" name="formResumenContratos">
                    <table style="width: 90%">
                        <tr>
                            <td style="width: 30%"><h2><b>Contratos</b></h2></td>
                            <td style="width: 30%">
                                <label for="fecha">Periodo: </label>
                                <input type="text" class="fecha" id="fecha" name="fecha" value="<?php echo $fechaText;?>" />
                            </td>
                            <td style="width: 10%">
                                <?php if($hayIVA){ ?>
                                <input type="checkbox" name="iva" id="iva" value="IVA" checked>Incluye I.V.A
                                <?php }else{ ?>
                                <input type="checkbox" name="iva" id="iva" value="IVA">Incluye I.V.A
                                <?php } ?>
                            </td>
                            <td style="width: 20%">
                                <input type="submit" class="button" id="submit_contratos" name="submit_contratos" value="Recalcular" style="margin-left: 85%;"/>
                            </td>
                            <td></td>
                        </tr>
                    </table>
                    </form>
                    <div class='module-summary'>
                        <div class='span6'>
                            <div class='summaryView'>
                                <?php if($fechaText == ""){ ?>
                                    <h4>DEL MES<?php echo strtoupper(substr($catalogo->formatoFechaReportes(date("Y")."-".date("m")."-".date("d")),5)); ?></h4>
                                <?php }else{    ?>
                                    <h4>DEL MES<?php echo strtoupper(substr($catalogo->formatoFechaReportes($fechaText),5)); ?></h4>
                                <?php } ?>
                                <hr>
                                <table style="width: 100%;">
                                    <tr valign="middle">
                                        <td style="width: 420px;">
                                            <div id="grafica" name="grafica" style="height: 270px;"></div>
                                        </td>
                                        <td>
                                            <h3>Total rentas a facturar (Pesos)</h3>
                                            <br/><h3><b>$<?php echo number_format($totalAFacturarMes, 2, '.', ','); ?></b></h3>
                                        </td>
                                    </tr>
                                </table>
                                <h3>Total rentas facturadas</h3>
                                <table id="totales" name="totales">
                                    <thead>
                                        <tr>
                                            <td>Total Renta</td>
                                            <td>Total Excedentes</td>
                                            <td>Total</td>
                                            <td>Moneda</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>$<?php echo number_format($totalRentasMes, 2, '.', ','); ?></td>
                                            <td>$<?php echo number_format($totalExcedentesMes, 2, '.', ','); ?></td>
                                            <td>$<?php echo number_format($totalFacturadoMes, 2, '.', ','); ?></td>
                                            <td>MXN</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <br/>
                                <table style="width: 100%">
                                    <tr>
                                        <td><h3>Contratos totales</h3></td>
                                        <td><h3><b><?php echo $contadorContratosAFacturarMes; ?></b></h3></td>
                                        <td><h3>Contratos facturados</h3></td>
                                        <td><h3><b><?php echo $contadorContratosFacturadosMes; ?></b></h3></td>
                                    </tr>
                                    <tr>
                                        <td><h3>Servicios totales</h3></td>
                                        <td><h3><b><?php echo $contadorAnexosAFacturarMes; ?></b></h3></td>
                                        <td><h3>Servicios facturados</h3></td>
                                        <td><h3><b><?php echo $contadorAnexosFacturadosMes; ?></b></h3></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a onclick="verDetalle();return false;" href="#"><div id="textoDetalle">Ver detalle</div></a>
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class='span6'>
                            <div class='summaryView'>
                                <h4>DEL DÍA <?php echo strtoupper($catalogo->formatoFechaReportes(date("Y")."-".date("m")."-".date("d"))); ?></h4>
                                <hr>
                                <table style="width: 100%;">
                                    <tr valign="middle">
                                        <td style="width: 420px;">
                                            <div id="graficaDia" name="graficaDia" style="height: 270px;"></div>
                                        </td>
                                        <td>
                                            <h3>Total rentas a facturar (Pesos)</h3>
                                            <br/><h3><b>$<?php echo number_format($totalAFacturarDia, 2, '.', ','); ?></b></h3>
                                        </td>
                                    </tr>
                                </table>
                                <h3>Total rentas facturadas hoy</h3>
                                <table id="totalesDia" name="totalesDia">
                                    <thead>
                                        <tr>
                                            <td>Total Renta</td>
                                            <td>Total Excedentes</td>
                                            <td>Total</td>
                                            <td>Moneda</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>$<?php echo number_format($totalRentasDia, 2, '.', ','); ?></td>
                                            <td>$<?php echo number_format($totalExcedentesDia, 2, '.', ','); ?></td>
                                            <td>$<?php echo number_format($totalFacturadoDia, 2, '.', ','); ?></td>
                                            <td>MXN</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <br/>
                                <table style="width: 100%">
                                    <tr>
                                        <td><h3>Contratos totales</h3></td>
                                        <td><h3><b><?php echo $contadorContratosAFacturarDia; ?></b></h3></td>
                                        <td><h3>Contratos facturados</h3></td>
                                        <td><h3><b><?php echo $contadorContratosFacturadosDia; ?></b></h3></td>
                                    </tr>
                                    <tr>
                                        <td><h3>Servicios totales</h3></td>
                                        <td><h3><b><?php echo $contadorAnexosAFacturarDia ?></b></h3></td>
                                        <td><h3>Servicios facturados</h3></td>
                                        <td><h3><b><?php echo $contadorAnexosFacturadosDia ?></b></h3></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <table style="width: 100%">
                            <tr>
                                <td></td>
                            </tr>
                        </table>
                        <div id="mostrarDetalle" name="mostrarDetalle" style="display: none; padding-top: 15px; margin-top: 15px">
                            <?php echo $tablaDetalle; ?>
                        </div>
                    </div>
                    </div>
                </div>
                <div id="tabs-2"></div>
                <div id="tabs-3"></div>
                <div id="tabs-4"></div>
            </div>
        </div>
        <script type="text/javascript">   
        $(function () {
            jQuery.cssProps.opacity = 'opacity';
            $('#grafica').highcharts({
                chart: {
                    margin: [0, 0, 0, 0],
                    spacingTop: 0,
                    spacingBottom: 0,
                    spacingLeft: 0,
                    spacingRight: 0,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: ''
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y}</b>'
                },
                plotOptions: {                   
                    pie: {                        
                        size:'65%',
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'

                            }
                        }
                    }
                },
                credits: {
                    text: "",
                    href: ""
                },
                series: [{
                    name: 'Contratos',
                    colorByPoint: true,
                    data: [
                        <?php echo $chartJQuery?>
                    ]
                }]
            });
            
            $('#graficaDia').highcharts({
                chart: {
                    margin: [0, 0, 0, 0],
                    spacingTop: 0,
                    spacingBottom: 0,
                    spacingLeft: 0,
                    spacingRight: 0,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: ''
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y}</b>'
                },
                plotOptions: {
                    pie: {
                        size:'65%',
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'

                            }
                        }
                    }
                },
                credits: {
                    text: "",
                    href: ""
                },
                series: [{
                    name: 'Contratos',
                    colorByPoint: true,
                    data: [
                        <?php echo $chartJQueryDia?>
                    ]
                }]
            });
        });
    </script>
    </body>
</html>
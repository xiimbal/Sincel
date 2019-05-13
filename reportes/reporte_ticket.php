<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
	header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/LecturaTicket.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../WEB-INF/Classes/Ticket.class.php");

$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();

$parametros = new Parametros();
$mostrarContadores = true;
if($parametros->getRegistroById("13") && $parametros->getValor() == "0"){
	$mostrarContadores = false;
}

$catalogo = new Catalogo();
$lecturaTicket = new LecturaTicket();
$ticket = new Ticket();

$idTicket = $_GET['idTicket'];
if(!$ticket->getTicketByID($idTicket)){
	
}
$tipoReporte = "";
$orden = "";
$claveCliente = "";
$claveLocalidad = "";
$nombreCliente = "";
$nombreLocalidad = "";
$contacto = "";
$telefono1 = "";
$extencion1 = "";
$telefono2 = "";
$extencion2 = "";
$celular = "";
$correo = "";
$fechaHoraTicket = "";
$serieFalla = "";
$ModeloFalla = "";
$domicilio = "";
$contadorNegro = "";
$contadorColor = "";
$nivelNegro = "";
$nivelCia = "";
$nivelMagenta = "";
$nivelAmarillo = "";
$descripcionReporte = "";
$observacionAdicional = "";
$domicilioCliente = "";
$ticketCliente = "";
$ticketDistribucion = "";
$EstadoTicketDatos = "";
$resurtido = "0";
$NoGuia = "";
$HrAtencion = "";


$series = array();
$consulta = "SELECT 
	(SELECT CASE WHEN t.AreaAtencion = 2 THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie, 
	t.TipoReporte,t.ClaveCliente,t.ClaveCentroCosto,t.NombreCliente,t.NombreCentroCosto,t.NombreResp,t.EstadoDeTicket,t.Resurtido,
	t.Telefono1Resp,t.Telefono2Resp,t.Extension1Resp,t.Extension2Resp, t.CelularResp,t.CorreoEResp,t.FechaHora,t.NoSerieEquipo,
	t.ModeloEquipo,t.DescripcionReporte,d.Calle,d.NoExterior,d.NoInterior,d.Estado,d.Colonia,d.Delegacion,d.Ciudad,d.CodigoPostal,t.ObservacionAdicional,t.NoTicketCliente,t.NoTicketDistribuidor,
                   (CASE WHEN ISNULL(t.NoGuia) OR t.NoGuia = '' THEN ((SELECT GROUP_CONCAT(DISTINCT(k_enviotoner.NoGuia) SEPARATOR ', ') AS NoGuia FROM `k_enviotoner`
                                INNER JOIN c_pedido ON c_pedido.IdPedido = k_enviotoner.IdSolicitud
                                INNER JOIN c_ticket ON c_ticket.IdTicket = c_pedido.IdTicket
                                WHERE c_ticket.IdTicket = t.IdTicket GROUP BY c_ticket.IdTicket)) ELSE t.NoGuia END) AS NoGuia,
                   t.HorarioAtenInicAtenc,t.HorarioAtenFinAtenc
	FROM c_ticket t 
	LEFT JOIN c_domicilio d ON d.ClaveEspecialDomicilio=t.ClaveCentroCosto 
	WHERE t.IdTicket='$idTicket' ORDER BY NumSerie DESC, IdDomicilio;";


$queryTicket = $catalogo->obtenerLista($consulta);
if ($rs = mysql_fetch_array($queryTicket)) {
	$series = explode(", ", $rs['NumSerie']);
	$tipoReporte = $rs['TipoReporte'];
	$claveCliente = $rs['ClaveCliente'];
	$claveLocalidad = $rs['ClaveCentroCosto'];
	$claveLocalidadEstadoTicket = $rs['ClaveCentroCosto'];
	$nombreCliente = $rs['NombreCliente'];
	$nombreLocalidad = $rs['NombreCentroCosto'];
	$contacto = $rs['NombreResp'];
	$telefono1 = $rs['Telefono1Resp'];
	$telefono2 = $rs['Telefono2Resp'];
	$extencion1 = $rs['Extension1Resp'];
	$extencion2 = $rs['Extension2Resp'];
	$celular = $rs['CelularResp'];
	$correo = $rs['CorreoEResp'];
	$fechaHoraTicket = $rs['FechaHora'];
	$serieFalla = $rs['NoSerieEquipo'];
	$ModeloFalla = $rs['ModeloEquipo'];
	$descripcionReporte = $rs['DescripcionReporte'];
	$observacionAdicional = $rs['ObservacionAdicional'];
	$ticketCliente = $rs['NoTicketCliente'];
	$ticketDistribucion = $rs['NoTicketDistribuidor'];
	$EstadoTicketDatos = $rs['EstadoDeTicket'];
	$domicilioCliente = $rs['Calle'] . "," . $rs['NoExterior'] . ",No. Int: ".$rs['NoInterior']."," . $rs['Colonia'] . "," . $rs['Delegacion'] . "," . $rs['Ciudad'] . "," . $rs['CodigoPostal'];
	$resurtido = $rs['Resurtido'];	
	$NoGuia = $rs['NoGuia'];
	$HrAtencion = $rs['HorarioAtenInicAtenc'] . " a " . $rs['HorarioAtenFinAtenc'];	
	
}

sort($series);
if ($tipoReporte != "15") {
	$orden = "Orden de Servicio";
	if ($serieFalla != "") {
		$lecturaTicket->setNoSerie($serieFalla);
		$lecturaTicket->getLecturaByTicket($idTicket);
		$fechaContadorAnterior = $lecturaTicket->getFechaA();
		$contadorNegro = $lecturaTicket->getContadorBNA();
		$contadorColor = $lecturaTicket->getContadorColorA();
		$nivelNegro = $lecturaTicket->getNivelNegroA();
		$nivelCia = $lecturaTicket->getNivelCiaA();
		$nivelMagenta = $lecturaTicket->getNivelMagentaA();
		$nivelAmarillo = $lecturaTicket->getNivelAmarilloA();
	}
} else if ($tipoReporte == "15") {
	if($ticket->getCambioToner() != "1"){
		$orden = "Orden de Tóner";
	}else{
		$orden = "Cambio de tóner (NO SURTIR)";
	}
}
list($fechaTicket, $horaTicket) = explode(" ", $fechaHoraTicket);
list($anio, $mes, $dia) = explode("-", $fechaTicket);
$fechaTicket = $dia . "-" . $mes . "-" . $anio;
$idDatosFacturacion = "";
$nombreLogo = "";
$queryFacturacion = $catalogo->obtenerLista("SELECT df.Telefono,df.ImagenPHP,df.IdDatosFacturacionEmpresa,df.Calle,df.NoExterior,df.Colonia,df.Delegacion,df.Estado,df.CP FROM c_datosfacturacionempresa df WHERE df.IdDatosFacturacionEmpresa=(SELECT c.IdDatosFacturacionEmpresa FROM c_cliente c WHERE c.ClaveCliente='$claveCliente')");
while ($rs = mysql_fetch_array($queryFacturacion)) {
	$idDatosFacturacion = $rs['IdDatosFacturacionEmpresa'];
	$nombreLogo = $rs['ImagenPHP'];
	$telefonos = $rs['Telefono'];
	$domicilio = $rs['Calle'] . "," . $rs['NoExterior'] . "," . $rs['Colonia'] . "," . $rs['Delegacion'] . "," . $rs['Estado'] . "," . $rs['CP'];
	if ($telefonos != "") {
		$domicilio = $domicilio . "<br/>Telefonos: " . $telefonos;
	}
}

if (($EstadoTicketDatos == "2" || $EstadoTicketDatos == "4") && $tipoReporte != "15" && $ticket->getCambioToner() != "1") {
	$consultaLocalidad = "SELECT c.ClaveCliente,c.NombreRazonSocial,c.IdTipoCliente,c.IdEstatusCobranza, cc.Nombre AS localidad,
	td.Nombre AS tdomicilio,d.Calle,d.Colonia,d.Delegacion, 
	(CASE WHEN !ISNULL(cc.ClaveZona) THEN cc.ClaveZona ELSE c.ClaveZona END) AS zona, d.NoExterior,
	d.NoInterior,d.Ciudad,d.CodigoPostal,d.Estado,
	(SELECT z.fk_id_gzona FROM c_zona z WHERE z.ClaveZona=cc.ClaveZona OR z.ClaveZona=c.ClaveZona LIMIT 1) AS ubicacion, 
	(SELECT GROUP_CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) FROM k_tfscliente tfsc,c_usuario u WHERE cc.ClaveCliente=tfsc.ClaveCliente AND cc.ClaveCliente=tfsc.ClaveCliente AND u.IdUsuario=tfsc.IdUsuario GROUP BY tfsc.ClaveCliente) as tfs,
	(SELECT ct.Nombre FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS nombreContacto,
	(SELECT ct.Telefono FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS Telefono,
	(SELECT ct.Celular FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS Celular,
	(SELECT ct.CorreoElectronico FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS CorreoElectronico
	FROM c_centrocosto cc,c_domicilio d,c_cliente c,c_tipodomicilio td
	WHERE cc.ClaveCentroCosto=d.ClaveEspecialDomicilio AND cc.ClaveCliente=c.ClaveCliente AND td.IdTipoDomicilio=d.IdTipoDomicilio 
	AND cc.ClaveCentroCosto='$claveLocalidadEstadoTicket'";
} else {
	if ($tipoReporte != "15") {
		$consultaLocalidadEquipo = "SELECT ie.NoSerie,(SELECT CASE WHEN ISNULL(ie.IdKserviciogimgfa) 
											THEN (SELECT cc.ClaveCentroCosto FROM k_anexoclientecc an,c_centrocosto cc WHERE cc.ClaveCentroCosto=an.CveEspClienteCC AND an.IdAnexoClienteCC=ie.IdAnexoClienteCC)
											ELSE (SELECT cc.ClaveCentroCosto FROM c_centrocosto cc,k_serviciogimgfa sg WHERE sg.IdKserviciogimgfa=ie.IdKserviciogimgfa AND sg.ClaveCentroCosto=cc.ClaveCentroCosto)END )AS Localidad                                            
											FROM c_inventarioequipo ie,k_equipocaracteristicaformatoservicio fs WHERE ie.NoSerie='$serieFalla' AND fs.NoParte=ie.NoParteEquipo AND fs.IdTipoServicio<>2 ORDER BY fs.IdFormatoEquipo ASC LIMIT 1";
		$queryConsultaLocalidad = $catalogo->obtenerLista($consultaLocalidadEquipo);
		while ($rs = mysql_fetch_array($queryConsultaLocalidad)) {
			$claveLocalidad = $rs['Localidad'];
		}
		
		$consultaDomicilioLocalidad = "SELECT c.NombreRazonSocial,cc.Nombre,d.Calle,d.NoExterior,d.NoInterior,d.Colonia,d.Delegacion,d.Estado,
			d.CodigoPostal,d.Ciudad
			FROM c_domicilio d,c_cliente c,c_centrocosto cc 
			WHERE d.ClaveEspecialDomicilio='$claveLocalidad' AND d.ClaveEspecialDomicilio=cc.ClaveCentroCosto AND cc.ClaveCliente=c.ClaveCliente";
		$queryConsultaDomicilio = $catalogo->obtenerLista($consultaDomicilioLocalidad);
		if ($rs = mysql_fetch_array($queryConsultaDomicilio)) {
			$nombreCliente = $rs['NombreRazonSocial'];
			$nombreLocalidad = $rs['Nombre'];
			$domicilioCliente = $rs['Calle'] . "," . $rs['NoExterior'] . ", No. Int: ".$rs['NoInterior']." ," . $rs['Colonia'] . "," . $rs['Delegacion'] . "," . $rs['Ciudad'] . "," . $rs['CodigoPostal'];
		}
		
	}else if($resurtido == "1"){
		$consultaDomicilioLocalidad = "SELECT krt.IdTicket, krt.IdAlmacen, a.nombre_almacen, da.Calle, da.NoExterior, 
			da.NoInterior, da.Colonia, da.Ciudad, da.Estado, da.Pais, da.CodigoPostal, da.Delegacion 
			FROM `k_resurtidotoner` AS krt
			LEFT JOIN c_almacen AS a ON a.id_almacen = krt.IdAlmacen
			LEFT JOIN c_domicilio_almacen AS da ON da.IdAlmacen = a.id_almacen
			WHERE IdTicket = $idTicket
			GROUP BY IdTicket;";
		$queryConsultaDomicilio = $catalogo->obtenerLista($consultaDomicilioLocalidad);
		while ($rs = mysql_fetch_array($queryConsultaDomicilio)) {
			$nombreCliente = "Almacén: ".$rs['nombre_almacen'];
			$nombreLocalidad = "";            
			$domicilioCliente = $rs['Calle'] . ", " . $rs['NoExterior'] . ", No. Int: ".$rs['NoInterior']." , " . $rs['Colonia'] . ", " . $rs['Delegacion'] . ", " . $rs['Ciudad'] . ", " . $rs['CodigoPostal'];
		}
		
	}else if($ticket->getCambioToner() == "1"){
		$consultaDomicilioLocalidad = "SELECT a.nombre_almacen
			FROM `k_minialmacenlocalidad` AS kma
			LEFT JOIN c_almacen AS a ON a.id_almacen = kma.IdAlmacen
			WHERE kma.ClaveCentroCosto = '".$ticket->getClaveCentroCosto()."';";
		$queryConsultaDomicilio = $catalogo->obtenerLista($consultaDomicilioLocalidad);
		$nombreCliente = "Mini-almacén: ";
		while ($rs = mysql_fetch_array($queryConsultaDomicilio)) {
			$nombreCliente = "Mini-almacén: ".$rs['nombre_almacen'];
		}        
		$nombreLocalidad = "Cambio de tóner <b>(NO SURTIR)</b>";
	}
	$consultaLocalidad = "SELECT c.ClaveCliente,c.NombreRazonSocial,c.IdTipoCliente,c.IdEstatusCobranza, cc.Nombre AS localidad,
	td.Nombre AS tdomicilio,d.Calle,d.Colonia,d.Delegacion, 
	(CASE WHEN !ISNULL(cc.ClaveZona) THEN cc.ClaveZona ELSE c.ClaveZona END) AS zona, d.NoExterior,
	d.NoInterior,d.Ciudad,d.CodigoPostal,d.Estado,
	(SELECT z.fk_id_gzona FROM c_zona z WHERE z.ClaveZona=cc.ClaveZona OR z.ClaveZona=c.ClaveZona LIMIT 1) AS ubicacion, 
	(SELECT GROUP_CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) FROM k_tfscliente tfsc,c_usuario u WHERE cc.ClaveCliente=tfsc.ClaveCliente AND cc.ClaveCliente=tfsc.ClaveCliente AND u.IdUsuario=tfsc.IdUsuario GROUP BY tfsc.ClaveCliente) as tfs,
	(SELECT ct.Nombre FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS nombreContacto,
	(SELECT ct.Telefono FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS Telefono,
	(SELECT ct.Celular FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS Celular,
	(SELECT ct.CorreoElectronico FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS CorreoElectronico
	FROM c_centrocosto cc,c_domicilio d,c_cliente c,c_tipodomicilio td
	WHERE cc.ClaveCentroCosto=d.ClaveEspecialDomicilio AND cc.ClaveCliente=c.ClaveCliente AND td.IdTipoDomicilio=d.IdTipoDomicilio 
	AND cc.ClaveCentroCosto='$claveLocalidad'";
}	
?>
<!DOCTYPE html>
<html lang="es">
	<head>     
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>Reporte <?php echo $nombre_objeto; ?></title>
		<link rel="icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
		<style>
			.BorderTabla td,th {
				border: solid black;
				border-width:1px;
				border-spacing: 0px;
				border-collapse: collapse
			}
			img.imagen{width:150px; height:70px;}
			img.imagens{width:150px; height:70px;}
			@media print {
				* { margin: 0 !important; padding: 0 !important; }
				#controls, .footer, .footerarea{ display: none; }
				html, body {
					/*changing width to 100% causes huge overflow and wrap*/
					height:80%; 
					background: #FFF; 
					font-size: 9.5pt;
				}
				img.imagen{width:75px; height:30px;}
				img.imagens{width:75px; height:30px;}
				template { width: auto; left:0; top:0; }
				li { margin: 0 0 10px 20px !important;}
			}
		</style>
	</head>
	<body style="font-size:12px;font-family:Arial;height:50%;">
		<link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
		<script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
		<script src="../resources/js/jquery/jquery-ui.min.js"></script>        
		<script type="text/javascript" language="javascript" src="../resources/js/paginas/reporte_ticket.js"></script>
		<a href=javascript:window.print(); style="margin-left: 85%;">Imprimir</a> 
		<br/><br/>
		<?php
		if($tipoReporte != "15"){
			foreach ($series as $key => $value) {
				echo "<input type='hidden' id='serie_$key' name='serie_$key' value='$value'/>";
			}
		}
		echo "<input type='hidden' id='numero_series' name='numero_series' value='" . count($series) . "'/>";
		echo "<input type='hidden' id='id_ticket' name='id_ticket' value='$idTicket'/>";
		?>	

		<table style="width: 100%">
			<tr>                
				<td style="width: 50%"><img src="../<?php echo $nombreLogo; ?>"/></td>
				<td style="width: 10%"></td>
				<td style="width: 45%;font-size:12px;font-family:Arial;">
					<?php echo $domicilio; ?>
				</td>
			</tr>
			<tr>
				<td align='center' colspan="3" style="font-size:14px;font-family:Arial;"><b><?php echo $orden; ?></b><br/><br/></td>
				<tr></tr>
				<?php 
				if($tipoReporte != "15"){?>
				<td align='center' colspan="3" style="font-size:14px;font-family:Arial;"><b><?php
				$tipoTicket = $catalogo -> obtenerLista("SELECT c_estado.Nombre AS TipoTicket FROM c_estado INNER JOIN c_ticket ON c_estado.IdEstado = c_ticket.TipoReporte WHERE c_ticket.IdTicket = '$idTicket'");
				while ($rss = mysql_fetch_array($tipoTicket)) {	
					echo "Tipo de Reporte: ". $rss['TipoTicket']." ";			
				 }?></b><br/><br/></td> <?php } ?>					 	 
				
			</tr>           
			<tr>
				<td style="width: 50%;font-size:12px;font-family:Arial;">
					<?php echo $nombreCliente . " / " . $nombreLocalidad; ?><br/>
					<?php echo $domicilioCliente; ?>
				</td>
				<td rowspan="2">
					<table>
						<tr><td></td><td></td><td><b>No. de <?php echo $nombre_objeto; ?>:</b><br/></td></tr>
					</table>
					<table class="BorderTabla" style="margin-left: 15px;">
						<thead style="background-color: grey;">
							<tr>
								<?php
								if ($ticketCliente != "")
									echo "<th align='center' style='font-size:12px;font-family:Arial;'>Cliente</th>";
								?>
								<?php
								if ($ticketDistribucion != "")
									echo "<th align='center' style='font-size:12px;font-family:Arial;'>Distibución</th>";
								?>
								<th align='center' style='font-size:12px;font-family:Arial;'><?php //echo $_SESSION['nombreEmpresa']; ?></th>
							</tr>
						</thead>
						<tbody style="background-color: #D3D6FF">
							<tr>
								<?php
								if ($ticketCliente != "")
									echo "<td align='center' style='font-size:12px;font-family:Arial;'>$ticketCliente</td>";
								?>
								<?php
								if ($ticketDistribucion != "")
									echo "<td align='center' style='font-size:12px;font-family:Arial;'>$ticketDistribucion</td>";
								?>
								<td style="color: red;font-size:12px;font-family:Arial;">
									<?php                                    
									echo $idTicket;
									?>
								</td>                                
							</tr>
						</tbody>                        
					</table>                     
				</td>        
				<td><br/><?php echo "<div style='margin-left: 45%;' id=\"div_ticket\" ></div>"; ?></td>
			</tr>
			<tr>
				<td  style="width: 45%;font-size:12px;font-family:Arial;" colspan="2"></td>
				<td align='center' style="width: 45%"></td>               
			</tr>
		</table>        
		<table style="width: 90%;">
			<tr>
				<td style="width: 50%">
					<table style="width: 100%">
						<tr>
							<td style=font-size:12px;font-family:Arial;><b>Contacto:</b>
									<div style="display: inline; font-size:12px;font-family:Arial;"><?php echo $contacto; ?></div>
							</td>                            
						</tr>
					</table>
					<table style="width: 100%">
						<tr>
							<?php
							if ($telefono1 != "")
								echo "<td style='width: 25%'><b>Teléfono 1:</b> $telefono1</td>";
							else
								echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>";
							if ($extencion1 != "")
								echo "<td style='width: 25%;font-size:12px;font-family:Arial;'> <b>Ext 1: </b>$extencion1</td>";
							else
								echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>";
							if ($telefono2 != "")
								echo "<td style='width: 25%;font-size:12px;font-family:Arial;'><b>Teléfono 2: </b>$telefono2;</td>";
							else
								echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>";
							if ($extencion2 != "")
								echo "<td style='width: 25%;font-size:12px;font-family:Arial;'><b>Ext 2</b>:$extencion2</td>";
							else
								echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>";

							?>
						</tr>
					</table>
					<table style="width: 100%">
						<tr>
							<?php
							if ($celular != "")
								echo "<td style='width: 25%;font-size:12px;font-family:Arial;'><b>Celular: </b>$celular</td>";
							/*else
								echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>";*/
							if ($correo != ""){
								echo "</tr><tr>";
								echo "<td style='width: 25%;font-size:12px;font-family:Arial;'><b>Correo: </b>$correo</td></tr>";
							}
							/*else
								echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>";*/
							if ($HrAtencion != ""){
								echo "</tr><tr>";
								echo "<td style='width: 25%;font-size:12px;font-family:Arial;'><b>Horario de Atencion: </b>$HrAtencion</td></tr>";}
							/*else
								echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>";*/
							?>
					</table>
				</td>
				<td style="width: 50%">
					<?php
					if ($tipoReporte != "15") {
						echo "<table style='width: 100%'><tr>";
						if ($tipoReporte != "302") {
							echo "<td style='font-size:12px;font-family:Arial;'><b>Modelo: </b>$ModeloFalla</td>";
							echo "<td style='font-size:12px;font-family:Arial;'><div style='margin-left: 95%;'><b>Serie:</b></div></td>
							<td style='font-size:12px;font-family:Arial;'><b></b>";                            
							echo "<div id='cbNoSerie_0' style='max-width:100%; margin-left: 32%;'></div>";
						}
						echo "</tr><tr>";
						//echo $series[0];
						echo "</td>";
					if ($NoGuia !="") {
						echo "</tr><tr>";
						echo "<tr></tr><td style='font-size:12px;font-family:Arial;'><b>Número de Guía: </b>$NoGuia</td>";
						}   
						echo "<td colspan='4' style='font-size:12px;font-family:Arial;'><b>Fecha de levantamiento: </b> $fechaTicket $horaTicket</td>";
					   

						echo "</tr><tr>";
						if ($tipoReporte != "302") {
							if($mostrarContadores !=""){
								echo "<td style='font-size:12px;font-family:Arial;'><b>Contador B/N:</b>$contadorNegro</td>";
							
									if ($contadorColor != ""){
								echo "<td style='font-size:12px;font-family:Arial;'><b>Contador color: </b>$contadorColor</td>";
									}
							}                       
						
						echo "</tr><tr>";						
						if ($nivelNegro != "")
							echo "<td style='font-size:12px;font-family:Arial;'><b>Nivel negro:</b>$nivelNegro </td>";

						else
							echo "<td><b></b></td>";
						if ($nivelCia != "")
							echo "<td style='font-size:12px;font-family:Arial;'><b>Nivel cian:</b> $nivelCia</td>";
						else
							echo "<td><b></b></td>";
						if ($nivelMagenta != "")
							echo "<td style='font-size:12px;font-family:Arial;'><b>Nivel magenta:</b> $nivelMagenta</td>";
						else
							echo "<td><b></b></td>";
						if ($nivelAmarillo != "")
							echo "<td style='font-size:12px;font-family:Arial;'><b>Nivel amarillo:</b> $nivelAmarillo</td>";
						else
							echo "<td><b></b></td>";  
						}                      
						echo "</tr></table>";                                                                                       
					}
					else if ($tipoReporte == "15") {
						echo "<table style='width: 100%'><tr>";
						echo "<td align='left'style='font-size:12px;font-family:Arial;'><b>Fecha levantamiento de $nombre_objeto: </b>$fechaTicket $horaTicket </td>";
						if ($NoGuia !="") {
							echo "</tr><tr>";
							echo "<td align='left' style='font-size:12px;font-family:Arial;'><b>Numero de Guia:</b> $NoGuia </td>";  
						}
						echo "</table>";
						
					}
					?>
				</td>
			</tr>         
		</table>    
		<table>
			<td style="width: 100%">       
			<?php			
			$prioridad = $catalogo -> obtenerLista("SELECT c_prioridadticket.Prioridad AS Prioridad FROM c_prioridadticket INNER JOIN c_ticket ON c_prioridadticket.IdPrioridad = c_ticket.Prioridad WHERE c_ticket.IdTicket = '$idTicket'");
			while ($rss = mysql_fetch_array($prioridad)) {				
				echo "<td align='right' style='font-size:12px;font-family:Arial;'><b>PR". $rss['Prioridad']." </b></td>";	
				}						
			
			?> 
			</td>
		</table>	  
		<?php       
		if ($tipoReporte == "15") { ?>
			<fieldset>
				<legend><b>Datos de los Equipos</b></legend> 
				<table style="width: 100%;" class="BorderTabla">                    
					<thead style="background-color: grey;">      
						<tr>
							<td align="center" colspan="2">Equipo</td>
							<td align="center" colspan="4">Contadores</td>
							<td align="center" colspan="4">Niveles</td>
							<td align="center" colspan="4">Pedido</td>
						</tr>
						<tr>
							<th align="center" style="width: 11%;">No Serie</th>
							<th align="center" style="width: 8%;">Modelo</th>
							<th align="center" style="width: 11%;">Fecha/Hora</th>
							<th align="center" style="width: 5%;">B/N</th>
							<th align="center" style="width: 5%;">Color</th>
							<th align="center" style="width: 5%;">Rendimiento</th>
							<th align="center" style="width: 5%;">Negro</th>
							<th align="center" style="width: 5%;">Cian</th>
							<th align="center" style="width: 5%;">Amarillo</th>
							<th align="center" style="width: 5%;">Magenta</th>

							<th align="center"  style="width: 5%;">Cantidad Solicitada</th>
							<th align="center"  style="width: 25%;">Modelo / No parte</th>
						</tr>
					</thead>
					<tbody style="background-color: #D3D6FF;">
						<?php
						$queryPedido = $catalogo->obtenerLista("SELECT p.ClaveEspEquipo,p.Modelo FROM c_pedido p 
							WHERE p.IdTicket='$idTicket' GROUP BY p.ClaveEspEquipo");
						$contador = 0;
						$key = 0;
						while ($rs = mysql_fetch_array($queryPedido)) {
							echo "<input type='hidden' id='serie_$key' name='serie_$key' value='".$rs['ClaveEspEquipo']."'/>";
							$key++;
							$lecturaTicket->setNoSerie($rs['ClaveEspEquipo']);
							$lecturaTicket->getLecturaBYNoSerieAndTicket($idTicket);                            
							$consultaPedido = "SELECT c.NoParte, c.Modelo, c.Descripcion,dn.NoSerieEquipo,dn.Cantidad, c.Rendimiento, c.IdColor
								FROM c_notaticket nt,k_detalle_notarefaccion dn,c_componente c
								WHERE nt.IdNotaTicket=dn.IdNota AND dn.Componente=c.NoParte AND nt.IdTicket='$idTicket' 
								AND dn.NoSerieEquipo='" . $rs['ClaveEspEquipo'] . "' ORDER BY NoSerieEquipo DESC;";

							$queryTonerSolicitado = $catalogo->obtenerLista($consultaPedido);
							$tamanoConslta1 = mysql_num_rows($queryTonerSolicitado); // obtenemos el número de filas 
							$tamanoConslta = $tamanoConslta1 + 1;
							$cadenaToners = "";
							$rendimientoColor = 0;
							$rendimientoNegro = 0;
							$impresionesBN = 0;
							$impresionesCL = 0;
							while ($toner = mysql_fetch_array($queryTonerSolicitado)) {
								$cadenaToners.= "<tr>"
								. "<td align='center'>" . $toner['Cantidad'] . "</td><td align='center'>" . $toner['Modelo'] . " / " . $toner['NoParte'] . "</td>"
								. "</tr>";
								if(isset($toner['IdColor']) && ($toner['IdColor'] == "2" || $toner['IdColor'] == "3" || $toner['IdColor'] == "4")){
									$rendimientoColor = (int)$toner['Rendimiento'];
								}else{
									$rendimientoNegro = (int)$toner['Rendimiento'];
								}
							}
							echo "<tr>";
							echo "<td align='center' rowspan='$tamanoConslta' style='border: 0px white solid;' >
								&nbsp;&nbsp;&nbsp;&nbsp;<div id='cbNoSerie_$contador' style='max-width:100%;' class='imagenimpr'></div>&nbsp;&nbsp;&nbsp;&nbsp;
							  </td>";
							/*echo "<td align='center' rowspan='$tamanoConslta' style='border: 0px white solid;' >
								&nbsp;&nbsp;&nbsp;&nbsp;".$rs['ClaveEspEquipo']."&nbsp;&nbsp;&nbsp;&nbsp;
							  </td>";*/
							echo "<td align='center' rowspan='$tamanoConslta'>" . $rs['Modelo'] . "</td>"
							. "<td align='center' rowspan='$tamanoConslta'>" . $lecturaTicket->getFecha(); 
							if($lecturaTicket->getFechaA() != ""){
								echo "<br/>Anterior:<br/> ".$lecturaTicket->getFechaA();
							} 
							echo "</td><td align='center' rowspan='$tamanoConslta'>" . $lecturaTicket->getContadorBN();
							if($lecturaTicket->getContadorBNA() != ""){
								echo "<br/>Anterior: ".$lecturaTicket->getContadorBNA();
								$impresionesBN = $lecturaTicket->getContadorBN() - $lecturaTicket->getContadorBNA();
								echo "<br/>Impresiones: ".$impresionesBN;
							} echo "</td><td align='center' rowspan='$tamanoConslta'>" . $lecturaTicket->getContadorColor();
							
							if($lecturaTicket->getContadorColorA() != ""){
								echo "<br/>Anterior: ".$lecturaTicket->getContadorColorA(); 
								$impresionesCL = $lecturaTicket->getContadorColor() - $lecturaTicket->getContadorColorA();
								echo "<br/>Impresiones: ".$impresionesCL;
							}
							echo "</td>";
							/********* Calculos de rendimiento **************/
							$porcentajeRendimientoNegro = 0;
							if($rendimientoNegro != 0){
								$porcentajeRendimientoNegro = ($impresionesBN * 100) / $rendimientoNegro;
							}
							
							$porcentajeRendimientoColor = 0;
							if($rendimientoColor != 0){
								$porcentajeRendimientoColor = ($impresionesCL * 100) / $rendimientoColor;
							}
							
							if($porcentajeRendimientoNegro == 0){
								if($lecturaTicket->getContadorBNA() == ""){
										echo "<td class='borde centrado' rowspan='$tamanoConslta'>Sin rendimiento por lectura anterior";
								}else{
										echo "<td class='borde centrado' rowspan='$tamanoConslta'>Sin rendimiento";
								}
							}else{
								if($porcentajeRendimiento < 0){
									echo "<td class='borde centrado' rowspan='$tamanoConslta'> 0 % de <br/>".$rendimientoNegro."";
								}else{
									echo "<td class='borde centrado' rowspan='$tamanoConslta'> ".number_format($porcentajeRendimientoNegro) ."% de ".$rendimientoNegro."";
								}
							}
							if($lecturaTicket->getContadorColor() != ""){
								echo "<br/>Color: ";
								if($porcentajeRendimientoColor == 0){
									if($lecturaTicket->getContadorColorA() == ""){
											echo "Sin rendimiento por lectura anterior";
									}else{
											echo "Sin rendimiento";
									}
								}else{
									if($porcentajeRendimientColor < 0){
										echo "0 % de <br/>".$rendimientoColor."";
									}else{
										echo number_format($porcentajeRendimientoColor) ."% de ".$rendimientoColor."";
									}
								}
							}
							echo "</td>";
							/********* Calculos de rendimiento **************/
							
							echo "<td align='center' rowspan='$tamanoConslta'>" . $lecturaTicket->getNivelNegro();
							if($lecturaTicket->getNivelNegroA() != ""){
								echo "<br/>Anterior: ".$lecturaTicket->getNivelNegroA();                            
							} 
							echo "</td><td align='center' rowspan='$tamanoConslta'>" . $lecturaTicket->getNivelCia();
							
							if($lecturaTicket->getNivelCiaA() != ""){
								echo "<br/>Anterior: ".$lecturaTicket->getNivelCiaA();
							}
							echo "</td><td align='center' rowspan='$tamanoConslta'>" . $lecturaTicket->getNivelAmarillo();
							if($lecturaTicket->getNivelAmarilloA() != ""){
							echo "<br/>Anterior: ".$lecturaTicket->getNivelAmarilloA();
							} 
							echo "</td><td align='center' rowspan='$tamanoConslta'>" . $lecturaTicket->getNivelMagenta();
							if($lecturaTicket->getNivelMagentaA() != ""){
							echo "<br/>Anterior: ".$lecturaTicket->getNivelMagentaA();
							}
							echo "</td></tr>";
							$contador++;
							echo $cadenaToners;
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
			</fieldset>
			<fieldset>
				<legend><b>Recepción Toner</b></legend> 
				<?php
				/*$consultaTonerEnviado = "SELECT c.Modelo,c.NoParte,c.Descripcion,SUM(nr.Cantidad) AS Cantidad FROM c_notaticket nt,k_nota_refaccion nr,c_componente c 
					WHERE nt.IdTicket='$idTicket' AND nt.IdEstatusAtencion=66 AND nt.IdNotaTicket=nr.IdNotaTicket AND nr.NoParteComponente=c.NoParte GROUP BY c.NoParte";*/
				$consultaTonerEnviado = "SELECT c.Modelo,c.NoParte,c.Descripcion,SUM(nr.Cantidad) AS Cantidad, CONCAT(DATE(nt.FechaHora),' ',HOUR(nt.FechaHora)) AS FechaEnvio, nt.FechaHora
					FROM c_notaticket nt
					INNER JOIN k_nota_refaccion AS nr ON nt.IdNotaTicket=nr.IdNotaTicket
					INNER JOIN c_componente AS c ON nr.NoParteComponente=c.NoParte 
					WHERE nt.IdTicket=$idTicket AND nt.IdEstatusAtencion=66
					GROUP BY c.NoParte,FechaEnvio ORDER BY FechaHora,c.Modelo;";
				$queryTonerEnviado = $catalogo->obtenerLista($consultaTonerEnviado);
				$numero = mysql_num_rows($queryTonerEnviado);
				if ($numero > 0) {
					echo "<table style='width: 50%' class='BorderTabla'>";
					echo "<thead><thead style='background-color: grey;'><th align='center'>Cantidad</th><th align='center'>Modelo</th><th align='center'>No parte</th><th align='center'>Fecha Envío</th></thead><tbody style='background-color: #D3D6FF;'>";
					while ($rs = mysql_fetch_array($queryTonerEnviado)) {
						echo "<tr><td align='center'>" . $rs['Cantidad'] . "</td><td align='center'>" . $rs['Modelo'] . "</td><td align='center'>" . $rs['NoParte'] . "</td><td align='center'>" . $rs['FechaHora'] . "</td></tr>";
					}
					echo "</tbody><table>";
				}
				?>
			</fieldset> 
			<fieldset style="width: 98%;height: 50px">
				<legend><b>Observaciones Adicionales</b></legend>
				<?php echo $observacionAdicional ?>
			</fieldset> 
			<fieldset>
				<legend><b>Evaluación 1 mala   3 regular   5 excelente</b></legend> 
				<table style="width: 100%;height: 40px" class="BorderTabla">
					<thead style="background-color: grey;"><tr><td align="center" style="width: 40%;">Técnico</td><td align="center" style="width: 20%;">Puntualidad</td><td align="center" style="width: 20%;">Actitud</td><td align="center" style="width: 20%;">Conocimientos</td></tr></thead>
					<tbody style="background-color: #D3D6FF;"><tr><td align="center"></td><td align="center">1 2 3 4 5</td><td align="center">1 2 3 4 5</td><td align="center">1 2 3 4 5</td></tr></tbody>
				</table>
			</fieldset>
			<fieldset>
				<legend><b>Comentarios y Sugerencias</b></legend> 
				<table style="width: 100%;height: 150px">
					<tr><td></td></tr>
				</table>
			</fieldset>    

		<?php } else if ($tipoReporte != "15") {
				$estatus_mostrado = array();
			?>
			<fieldset>
				<legend><b>Problema reportado</b></legend> 
				<?php echo $descripcionReporte ?>
			</fieldset>
			<fieldset>
				<legend><b>Acciones realizadas / Acciones pendientes de realizar</b></legend> 
				<table style="width: 100%;height: 40px;" class="BorderTabla">
					<thead style="background-color: grey;">
						<tr></tr>
						<tr>
							<th align="center" style="width: 10%;">Fecha</th>
							<th align="center" style="width: 40%;">Diagnóstico</th>
							<th align="center" style="width: 10%;">Técnico</th>
							<th align="center" style="width: 10%;">Status</th>

						</tr>
					</thead>
					<tbody style="background-color: #D3D6FF;">
						<?php
						$consultaNotas = "SELECT nt.IdNotaTicket,nt.FechaHora,nt.DiagnosticoSol,nt.UsuarioUltimaModificacion,e.Nombre,nt.IdEstatusAtencion 
							FROM c_notaticket nt,c_estado e 
							WHERE nt.IdEstatusAtencion=e.IdEstado AND nt.IdTicket='$idTicket' AND (nt.IdEstatusAtencion = 24 OR nt.MostrarCliente=1) 
							ORDER BY nt.FechaHora DESC;";
						$queryNotas = $catalogo->obtenerLista($consultaNotas);   
						
						while ($rs = mysql_fetch_array($queryNotas)) {
							//Si el estatus ya se mostro una vez, ya no se vuelve a mostrar
							if(isset($estatus_mostrado[$rs['IdEstatusAtencion']]) && $estatus_mostrado[$rs['IdEstatusAtencion']]){
								continue;
							}
							list($fecha, $hora) = explode(" ", $rs['FechaHora']);
							list($anio1, $mes1, $dia1) = explode("-", $fecha);
							echo "<tr>";
							echo "<td align='center'  >" . $dia1 . "-" . $mes1 . "-" . $anio1 . " " . $hora . "</td>"
								. "<td align='center'>" . $rs['DiagnosticoSol'] . "";

							if ($rs['IdEstatusAtencion'] == "9" || $rs['IdEstatusAtencion'] == "24") {                                                                     
								$consultaRefacionesSolicitadas = "SELECT c.Modelo,c.NoParte,nr.Cantidad,c.Descripcion,
									(CASE WHEN nt.IdEstatusAtencion <> 24 THEN nr.Cantidad WHEN !ISNULL(nr2.CantidadNota) THEN nr2.CantidadNota ELSE nr.Cantidad END) AS CantidadNota
									FROM k_nota_refaccion AS nr
									INNER JOIN c_componente AS c ON c.NoParte=nr.NoParteComponente
									LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = nr.IdNotaTicket
									LEFT JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (
									SELECT MIN(nt3.IdNotaTicket) 
									FROM c_notaticket AS nt3
									INNER JOIN k_nota_refaccion AS nr3 ON nr3.IdNotaTicket = nt3.IdNotaTicket 
									WHERE nt3.IdTicket = nt.IdTicket AND nt3.IdEstatusAtencion = 9 AND nr3.NoParteComponente = nr.NoParteComponente
									)
									LEFT JOIN k_nota_refaccion AS nr2 ON nr2.IdNotaTicket = nt2.IdNotaTicket AND nr2.NoParteComponente = nr.NoParteComponente
									WHERE nt.IdTicket = $idTicket AND nt.IdEstatusAtencion = ".$rs['IdEstatusAtencion']." GROUP BY NoParte, nr.IdNotaTicket;";
								$queryConsultaRefaccion = $catalogo->obtenerLista($consultaRefacionesSolicitadas);
								if(mysql_num_rows($queryConsultaRefaccion) > 0){
									echo "<table>";
									echo "<tr>";
									echo "<th align='center' style='width: 5%;'>cantidad</th>";
									echo "<th align='center' style='width: 30%;'>Refacción</th>";
									echo "</tr>";
									while ($refaccion = mysql_fetch_array($queryConsultaRefaccion)) {
										echo "<tr>"
										. "<td align='center'>" . $refaccion['CantidadNota'] . "</td>"
										. "<td align='center'>" . $refaccion['Modelo'] . " / " . $refaccion['NoParte'] . " / " . $refaccion['Descripcion'] . "</td>"
										. "</tr>";
									}
									echo "</table>";
								}
								$estatus_mostrado[$rs['IdEstatusAtencion']] = true;
							}
							echo "</td>";
							echo "<td align='center'>" . $rs['UsuarioUltimaModificacion'] . "</td>"
							. "<td align='center'>" . $rs['Nombre'] . "</td>"
							. "</tr>";
						}
						?>
					</tbody>
				</table>
			</fieldset>
			<fieldset>
				<legend></legend> 
				<fieldset>
					<legend><b>Observaciones Adicionales</b></legend> 
					<table style="width: 98%;height: 50px">
						<tr><td><?php echo $observacionAdicional; ?></td></tr>
					</table>
				</fieldset>
				<fieldset>
					<legend><b>Cierre</b></legend> 
					<table style="width: 100%; height: 40px" class="BorderTabla">
						<thead style="background-color: grey;"><tr><th align="center" style="width: 10%;">Fecha</th><th align="center"  style="width: 10%;">Hora</th>
								<?php if($mostrarContadores){ ?>
									<th align="center"  style="width: 20%;">Contador B/N</th><th align="center"  style="width: 20%;">Contador color</th><th align="center"  style="width: 10%;">Nivel tóner negro</th><th align="center"  style="width: 10%;">Nivel tóner cian</th><th align="center"  style="width: 10%;">Nivel tóner amarillo</th><th align="center"  style="width: 10%;">Nivel tóner magenta</th></tr></thead>
								<?php } ?>
						<tbody style="background-color: #D3D6FF;">
							<?php
							$consultaNotas2 = "SELECT nt.IdEstatusAtencion FROM c_notaticket nt,c_estado e WHERE nt.IdEstatusAtencion=e.IdEstado AND nt.IdTicket='$idTicket' AND nt.MostrarCliente=1 ORDER BY nt.FechaHora DESC";
							$queryestatus = $catalogo->obtenerLista($consultaNotas2);
							$IdEstatusAtencion = "";
							if ($rs = mysql_fetch_array($queryestatus)) {
								$IdEstatusAtencion = $rs['IdEstatusAtencion'];
							}
							if (($EstadoTicketDatos != 2 && $EstadoTicketDatos != 4) || ($IdEstatusAtencion != 16 && $IdEstatusAtencion != 59)) {
								$fechaLecturaTicket = "<br/>";
								$hora = "<br/>";
								$contadorNegro = "<br/>";
								$contadorColor = "<br/>";
								$nivelNegro = "<br/>";
								$nivelCia = "<br/>";
								$nivelAmarillo = "<br/>";
								$nivelMagenta = "<br/>";
							} else {
								if ($fechaContadorAnterior == "") {
									$fecha = "";
									$hora = "";
									$fechaLecturaTicket = "";
								} else {
									list($fecha, $hora) = explode(" ", $fechaContadorAnterior);
									list($anio1, $mes1, $dia1) = explode("-", $fecha);
									$fechaLecturaTicket = $dia1 . "-" . $mes1 . "-" . $anio1;
								}
							}
							echo "<tr>"
							. "<td align='center'>" . $fechaLecturaTicket . "</td>"
							. "<td align='center'>" . $hora . "</td>";
							if($mostrarContadores){
								echo "<td align='center'>" . $contadorNegro . "</td>"
								. "<td align='center'>" . $contadorColor . "</td>"
								. "<td align='center'>" . $nivelNegro . "</td>"
								. "<td align='center'>" . $nivelCia . "</td>"
								. "<td align='center'>" . $nivelAmarillo . "</td>"
								. "<td align='center'>" . $nivelMagenta . "</td></tr>";
							}
							?>
						</tbody>
					</table>
				</fieldset>
				<fieldset>
					<legend><b>Evaluación 1 mala   3 regular   5 excelente</b></legend> 
					<table style="width: 100%;height: 40px" class="BorderTabla">
						<thead style="background-color: grey;"><tr><td align="center" style="width: 40%;">Técnico</td><td align="center" style="width: 20%;">Puntualidad</td><td align="center" style="width: 20%;">Actitud</td><td align="center" style="width: 20%;">Conocimientos</td></tr></thead>
						<tbody style="background-color: #D3D6FF;"><tr><td align="center"></td><td align="center">1 2 3 4 5</td><td align="center">1 2 3 4 5</td><td align="center">1 2 3 4 5</td></tr></tbody>
					</table>
				</fieldset>
				<fieldset>
					<legend><b>Comentarios y Sugerencias</b></legend> 
					<table style="width: 100%;height: 150px">
						<tr><td></td></tr>
					</table>
				</fieldset>               
			</fieldset>
		<?php } ?>
		<fieldset>
			<legend><b>Firmas</b></legend> 
			<table style="width:  100%" >
				<tr><td style="width: 50%" align="center">Firma de conformidad</td><td style="width: 50%" align="center">Ingeniero <?php //echo $_SESSION['nombreEmpresa']; ?></td></tr>
				<tr><td align="center" style="width: 50%"><br/><br/><HR width=300px > </td><td align="center" style="width: 50%"><br/><br/><HR width=300px ></td></tr>
				<tr><td style="width: 50%" align="center"><?php echo $contacto; ?></td><td style="width: 50%" align="center"></td></tr>
			</table>
		</fieldset>
	</body>	
</html>
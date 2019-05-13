<?php
header('Content-Type: text/html; charset=UTF-8');

session_start();
ini_set("memory_limit", "600M");
//echo ini_get("post_max_size");

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" /* || !isset($_POST['rfc']) || !isset($_POST['rfcFacturacion']) */) {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/XMLAbraham.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Factura.class.php");
include_once("../WEB-INF/Classes/Factura2.class.php");
include_once("../WEB-INF/Classes/FacturaConceptoExtra.class.php");
include_once("../WEB-INF/Classes/Concepto.class.php");
include_once("../WEB-INF/Classes/Contrato.class.php");
include_once("../WEB-INF/Classes/DatosFacturacionEmpresa.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/Proveedor.class.php");
include_once("../WEB-INF/Classes/Menu.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Empresa.class.php");
//include_once("../WEB-INF/Classes/Factura_Proveedor.class.php");
include_once("../WEB-INF/Classes/Orden_Compra.class.php");
include_once("../WEB-INF/Classes/TicketNR.class.php");
include_once("../WEB-INF/Classes/Financial.class.php");
include_once("../WEB-INF/Classes/FinancialDetalle.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$nombre_objeto = $permisos_grid->getNombreTicketSistema();
$nombre_tecnico = $permisos_grid->getNombreTecnicoSistema();

$usuario = $_SESSION['user'];
$pantalla = "PHP facturarReporteLectura";
$mensajePagina = "";

//Primero, vamos a obtener todos los clientes que vamos a facturar, así como sus cantidades.
$tickets = $_POST['tickets'];
$tickets2 = implode(",", $tickets);
$pagos = $_POST['pagos'];
$descuentos = $_POST['descuentos'];
$usuarioPago = array();
$rfcReceptor = array();
$ticketsitos = array();
$monto = array();
$pagoTicket = array();
$contrato2 = array();
$tickets_rfc = array();
$facturaViaticos = array();
$tickets_rfc_proveedor = array();
$monto_ticket = array();
$monto_ticket2 = array();
$monto_final_ticket = array();
$descuento_ticket = array();
$forma_pago_rfc = array();
$catalogo = new Catalogo();
$proveedor = new Proveedor();
$proveedor_viaticos = array();
$ticketNR = new TicketNR();
$ticketNR->setUsuarioCreacion($_SESSION['user']);
$ticketNR->setUsuarioUltimaModificacion($_SESSION['user']);
$ticketNR->setPantalla($pantalla);

//Ahora pagoTicket es una array algo así $pagoTicket[IdTicket] = monto
$consultaAgruparTicket2 = "SELECT tt.IdUsuario, tt.IdTicket, u.RFC, 
                            CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno,u.ApellidoMaterno) AS Nombre, dfe.IdDatosFacturacionEmpresa AS IdReceptor
                            FROM c_ticket t
                            INNER JOIN k_tecnicoticket AS tt ON tt.IdTicket = t.IdTicket
                            INNER JOIN c_usuario AS u ON u.IdUsuario = tt.IdUsuario
                            INNER JOIN c_cliente AS c ON c.ClaveCliente = t.ClaveCliente
                            INNER JOIN c_datosfacturacionempresa AS dfe ON dfe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa
                            WHERE t.IdTicket IN ($tickets2) ";
$resultTickets2 = $catalogo->obtenerLista($consultaAgruparTicket2);
while ($rsTicket = mysql_fetch_array($resultTickets2)) {
    if(!isset($rsTicket['RFC']) || empty($rsTicket['RFC'])){
        echo "<br/>El $nombre_tecnico ".$rsTicket['Nombre']." no tiene RFC registrado en el catálogo de choferes, es necesario registra dicho RFC para poder generarle su comisión";
        exit;
    }
}

$tickets_precio_fijo = array();
for ($x = 0; $x < count($tickets); $x++) {
    //Obtendremos el RFC del cliente, de la empresa a que factura y su monto del ticket
    $algo = "";
    $algo.= $tickets[$x];
    //Cambie el query para que ahora en lugar de tomar en cuenta los CobrarSiNo, tome en cuenta la FlagCobrar, que depende de cada estado de la nota del ticket.
    $consulta = "SELECT
	c.RFC AS RFCReceptor,
	dfe.RFC AS RFCEmisor,
	co.NoContrato,
	t.ClaveCentroCosto,
	e.PrecioParticular,
	t.IdTicket,
	u.CostoFijo,
	u.IdFormaPago,
	(
		CASE
		WHEN ! ISNULL(e.PrecioParticular) THEN
			(e.PrecioParticular)
		WHEN ! ISNULL(u.CostoFijo) THEN
			(u.CostoFijo)
		WHEN ! ISNULL(tr.IdTarifa) THEN
			(ktr.Costo)
		WHEN csve.IdServicioVE = 74 THEN
			sve.Cantidad
		ELSE
			(
				sve.cantidad * csve.PrecioUnitario
			)
		END
	) AS monto,
	(CASE WHEN edo.IdEstado IN (274, 275, 276) THEN 1
		ELSE 0	END) AS esViatico,
	sve.cantidad,
	u2.RFC AS RFCProveedor,
	sve.CobrarSiNo,
	sve.PagarSiNo, edo.FlagCobrar, sve.Validado AS notaValidada
        FROM
	c_ticket t
INNER JOIN c_cliente AS c ON c.ClaveCliente = t.ClaveCliente
INNER JOIN c_datosfacturacionempresa AS dfe ON dfe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa
INNER JOIN k_serviciove AS sve ON sve.IdTicket = t.IdTicket
INNER JOIN c_notaticket nt ON nt.IdNotaTicket = sve.IdNotaTicket
INNER JOIN c_estado edo ON edo.IdEstado = nt.IdEstatusAtencion
INNER JOIN c_serviciosve AS csve ON sve.IdServicioVE = csve.IdServicioVE
LEFT JOIN c_tarifarango AS tr ON tr.IdTarifa = csve.IdTarifa
LEFT JOIN k_tarifarango AS ktr ON ktr.IdDetalleTarifa = (
	SELECT
		MAX(ktr2.IdDetalleTarifa)
	FROM
		k_tarifarango AS ktr2
	WHERE
		ktr2.IdTarifa = tr.IdTarifa
	AND sve.cantidad >= ktr2.RangoInicial
	AND sve.cantidad <= ktr2.RangoFinal
)
INNER JOIN c_contrato AS co ON co.ClaveCliente = c.ClaveCliente
LEFT JOIN c_especial AS e ON e.idTicket = t.IdTicket
LEFT JOIN c_usuario AS u ON u.IdUsuario = e.idUsuario
LEFT JOIN k_tecnicoticket AS tt ON tt.IdTicket = t.IdTicket
LEFT JOIN c_usuario AS u2 ON u2.IdUsuario = tt.IdUsuario
WHERE t.IdTicket = $algo AND sve.Validado = 1 GROUP BY sve.IdPartida;";
    //echo $consulta;
    $result = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($result)) {
        if (isset($rs['PrecioParticular']) && !empty($rs['PrecioParticular'])) {
            $tickets_precio_fijo[$rs['IdTicket']] = $rs['PrecioParticular'];
        } else if (isset($rs['CostoFijo']) && !empty($rs['CostoFijo'])) {
            $tickets_precio_fijo[$rs['IdTicket']] = $rs['CostoFijo'];
        }

        $rfcEmisor[$rs['RFCReceptor']] = $rs['RFCEmisor'];
        $contrato2[$rs['RFCReceptor']] = $rs['NoContrato'];
        $claveCentroCosto[$rs['RFCReceptor']] = $rs['ClaveCentroCosto'];
        $forma_pago_rfc[$rs['RFCReceptor']] = $rs['IdFormaPago'];

        if (isset($rs['monto']) && $rs['FlagCobrar'] == "1" && $rs['notaValidada'] == "1") {       
            $monto_aux = $rs['monto'];
        } else {
            $monto_aux = 0;
        }
        
        
        if (isset($monto[$rs['RFCReceptor']])) {
            $monto[$rs['RFCReceptor']] += $monto_aux;
        } else {
            $monto[$rs['RFCReceptor']] = $monto_aux;
        }

        /* Guardamos los id de tickets que se van a facturar por cada RFC */
        if (!isset($tickets_rfc[$rs['RFCReceptor']]) || !is_array($tickets_rfc[$rs['RFCReceptor']])) {
            $tickets_rfc[$rs['RFCReceptor']] = array();
        }
        
        if (!isset($tickets_rfc_proveedor[$rs['RFCProveedor']]) || !is_array($tickets_rfc_proveedor[$rs['RFCProveedor']])) {
            $tickets_rfc_proveedor[$rs['RFCProveedor']] = array();
        }
        
        if(!isset($proveedor_viaticos[$rs['RFCProveedor']])){
            if($rs['PagarSiNo'] == "1"){
                $proveedor_viaticos[$rs['RFCProveedor']] = (float)$rs['monto'];
            }else{
                $proveedor_viaticos[$rs['RFCProveedor']] = 0;
            }
        }else if($rs['PagarSiNo'] == "1"){
            $proveedor_viaticos[$rs['RFCProveedor']] += (float)$rs['monto'];
        }
        
        if(!in_array($tickets[$x], $tickets_rfc[$rs['RFCReceptor']])){
            array_push($tickets_rfc[$rs['RFCReceptor']], $tickets[$x]);            
        }
        array_push($tickets_rfc_proveedor[$rs['RFCProveedor']], $tickets[$x]);
        //Aquí vamos a decidir que arreglo llenar, si es viático se llena $monto_ticket2 (para que posteriormente se depósite al chofer al 100%)
        if($rs['esViatico']){
            if(isset($monto_ticket2[$rs['IdTicket']])){
                $monto_ticket2[$rs['IdTicket']] += $monto_aux;
            }else{
                $monto_ticket2[$rs['IdTicket']] = $monto_aux;
            }
        }else{
            if(isset($monto_ticket[$rs['IdTicket']])){
                $monto_ticket[$rs['IdTicket']] += $monto_aux;        
            }  else {
                $monto_ticket[$rs['IdTicket']] = $monto_aux;        
            }
        }
            
    }
}

/* Para insertar en las tablas para la factura en PHP */
foreach ($rfcEmisor as $clave => $valor) {
    $factura2 = new Factura(); //Este objeto guarda los datos en la bd
    $concepto_obj = new Concepto();
    $parametros = new Parametros();
    $xml = new XMLAbraham();
    $cliente = new Cliente();
    $domicilio = new Localidad();
    $datosFacturacion = new DatosFacturacionEmpresa();
    $domicilioFiscal = new Localidad();
    $contrato = new Contrato();
    $menu = new Menu();
    $obj_direccion = new Localidad();

    $parametros->getRegistroById("8");
    $liga = $parametros->getDescripcion();
    $cliente->getRegistroByRFC($clave);
    if (!$domicilio->getLocalidadByClaveTipo($cliente->getClaveCliente(), "3")) {
        $domicilio->getLocalidadByClave($cliente->getClaveCliente());
    }


    $datosFacturacion->getRegistroByRFC($valor);
    $contrato->getRegistroById($contrato2[$clave]);
    //if(isset($_POST['forma_pago']) && $_POST['forma_pago']!=""){    //Está condición por el momento no se cumplirá
    //    $MetodoPago = $_POST['forma_pago'];
    //}
    if (isset($forma_pago_rfc[$clave])) {
        $MetodoPago = $forma_pago_rfc[$clave];
    } else {
        $MetodoPago = "Transferencia electrónica de fondos";
    }
    $FormaPago = "Pago en una sola exhibicion";

    $xml->setFecha(date("Y-m-d") . "T" . date("H:i:s"));
    $xml->setSello("");
    $xml->setAnoAprobacion("");
    $xml->setNoAprobacion("");
    $xml->setFormaDePago($FormaPago);
    $xml->setMetodoDePago($MetodoPago);
    $xml->setTipoDeComprobante("ingreso");
    $xml->setLugarExpedicion("MEXICO,DISTRITO FEDERAL");
    $xml->setEmisor_rfc($datosFacturacion->getRFC());
    $xml->setEmisor_nombre($datosFacturacion->getRazonSocial());
    $xml->setEmisor_Dom_CP($domicilioFiscal->getCodigoPostal());
    $xml->setEmisor_Dom_Calle($domicilioFiscal->getCalle());
    $xml->setEmisor_Dom_Col($domicilioFiscal->getColonia());
    $xml->setEmisor_Dom_Est($domicilioFiscal->getEstado());
    $xml->setEmisor_Dom_Mun($domicilioFiscal->getDelegacion());
    $xml->setEmisor_Dom_NoExt($domicilioFiscal->getNoExterior());
    $xml->setEmisor_Dom_NoInt($domicilioFiscal->getNoInterior());
    $xml->setEmisor_Dom_Pais($domicilioFiscal->getPais());

    //$xml->setNoOrden($_POST['orden']); $xml->setNoProveedor($_POST['proveedor']); $xml->setObsDentroXML($_POST['obs_dentro_xml']); 
    //$xml->setObsFueraXML($_POST['obs_fuera_xml']);
    $xml->setNoOrden("");
    $xml->setNoProveedor("");
    $xml->setObsDentroXML("");
    $xml->setObsFueraXML("");
    $xml->setReceptor_rfc($cliente->getRFC());
    $xml->setReceptor_nombre($cliente->getNombreRazonSocial());
    $xml->setReceptor_Dom_CP($domicilio->getCodigoPostal());
    $xml->setReceptor_Dom_Calle($domicilio->getCalle());
    $xml->setReceptor_Dom_Col($domicilio->getColonia());
    $xml->setReceptor_Dom_Est($domicilio->getEstado());
    $xml->setReceptor_Dom_Mun($domicilio->getDelegacion());
    $xml->setReceptor_Dom_NoExt($domicilio->getNoExterior());
    $xml->setReceptor_Dom_NoInt($domicilio->getNoInterior());
    $xml->setReceptor_Dom_Pais($domicilio->getPais());
    $idDomicilio = $domicilio->getIdDomicilio();

    $xml->setExpedido_CP($xml->getEmisor_Dom_CP());
    $xml->setExpedido_Calle($xml->getEmisor_Dom_Calle());
    $xml->setExpedido_Col($xml->getEmisor_Dom_Col());
    $xml->setExpedido_Estado($xml->getEmisor_Dom_Est());
    $xml->setExpedido_Mun($xml->getEmisor_Dom_Mun());
    $xml->setExpedido_NoExt($xml->getEmisor_Dom_NoExt());
    $xml->setExpedido_NoInt($xml->getEmisor_Dom_NoInt());
    $xml->setExpedido_Pais($xml->getEmisor_Dom_Pais());

    $iva = 0.16;
    $imprimir_cero = 0;
    
    /* Datos para guardar en la bd */
    $factura2->setIdEmpresa($datosFacturacion->getIdDatosFacturacionEmpresa());
    //Aunque los campos dicen setRFC, hay que mandarles la clave del cliente y el id de la empresa dee facturacion
    $factura2->setRFCEmisor($datosFacturacion->getIdDatosFacturacionEmpresa());
    $factura2->setRFCReceptor($cliente->getClaveCliente());
    $factura2->setPeriodoFacturacion($_POST['periodo_facturacion']);
    $factura2->setIdDomicilioFiscal($idDomicilio);
    $factura2->setUsuarioCreacion($usuario);
    $factura2->setUsuarioUltimaModificacion($usuario);
    $factura2->setPantalla($pantalla);
    $factura2->setFormaPago($FormaPago);
    $factura2->setMetodoPago($MetodoPago);
    $factura2->setId_TipoFactura("2");
    $factura2->setNumCtaPago($contrato->getNumeroCuenta());
    $factura2->setTipoArrendamiento("1"); //Se guarda el tipo de arrendamiento 1, todas estas facturas son de arrendamiento

    $num_facturas = 1;  //Para los viajes especiales solo habrá un servicio por cliente
    for ($i = 1; $i <= $num_facturas; $i++) {
        $conceptos = array();
        $Impuestos_Trasladado = array();
        $total = 0;
        $subtotal = 0;
        //Obtenemos el numero de conceptos por cada factura    
        if (isset($_POST['conceptos_factura_' . $i])) {
            $num_conceptos = 1;
        } else {
            $num_conceptos = 0;
        }

        $obj_direccion->getLocalidadByClaveTipo($claveCentroCosto[$clave], "5");
        $idDomicilio2 = $obj_direccion->getIdDomicilio();

        if (isset($idDomicilio2) && $idDomicilio2 != "") {
            if ($domicilioFiscal->getLocalidadById($obj_direccion->getIdDomicilio())) {
                $xml->setReceptor_Dom_CP($domicilioFiscal->getCodigoPostal());
                $xml->setReceptor_Dom_Calle($domicilioFiscal->getCalle());
                $xml->setReceptor_Dom_Col($domicilioFiscal->getColonia());
                $xml->setReceptor_Dom_Est($domicilioFiscal->getEstado());
                $xml->setReceptor_Dom_Mun($domicilioFiscal->getDelegacion());
                $xml->setReceptor_Dom_NoExt($domicilioFiscal->getNoExterior());
                $xml->setReceptor_Dom_NoInt($domicilioFiscal->getNoInterior());
                $xml->setReceptor_Dom_Pais($domicilioFiscal->getPais());
                $idDomicilio = $domicilioFiscal->getIdDomicilio();
                $factura2->setIdDomicilioFiscal($idDomicilio);
            }
        }
        $nombreTickets = "";
        $contadorTickets = 0;
        //Insertamos la factura
        if ($factura2->NuevaPreFactura()) {
            $aux_array = $tickets_rfc[$clave];
            $ticketNR->setIdNotaRemision($factura2->getIdFactura());
            foreach ($aux_array as $value) {
                $ticketNR->setIdTicket($value);
                if (!$ticketNR->nuevoRegistro()) {
                    echo "<br/>No se pudo asociar la nota de remisión " . $factura2->getFolio() . " con el $nombre_objeto $value, favor de reportarlo con el administrador";
                }else{
                    $nombreTickets .= "$value,";
                    $contadorTickets += 1;
                    echo "<br>La Nota de Remisión " . $factura2->getFolio() . " con el $nombre_objeto $value se asoció con éxito";
                }
            }
        } else {
            $mensajePagina.= "<br/>Error: no se pudo generar la factura $i";
            continue;
        }

        $cantidad = 1;
        $um = "Servicio";
        if($contadorTickets > 1){
            $concepto = "Concepto por viajes especiales." . $nombre_objeto . "s: " . trim($nombreTickets, ",");
        }else{
            $concepto = "Concepto por viajes especiales. $nombre_objeto: " . trim($nombreTickets, ",");
        }
        
        $pu = $monto[$clave];
        $importe = $monto[$clave];
        $encabezado = true;

        //echo "<br/> Agregando $cantidad $um $concepto $pu $importe para la factura $i";
        if ($importe == 0 && $cantidad == 0 && $concepto == "" && $importe == 0 && !$encabezado) {
            continue;
        }

        if ($importe > 0 || $imprimir_cero == 1 || $encabezado) {
            if ($concepto == "") {
                continue;
            }
            $concepto_obj->setIdFactura($factura2->getIdFactura());
            $concepto_obj->setPrecioUnitario($pu);
            $concepto_obj->setCantidad($cantidad);
            $concepto_obj->setUnidad($um);
            $concepto_obj->setDescripcion($concepto);
            $concepto_obj->setUsuarioCreacion($usuario);
            $concepto_obj->setUsuarioUltimaModificacion($usuario);
            $concepto_obj->setPantalla($pantalla);
            $concepto_obj->setTipo("null");
            $concepto_obj->setId_articulo("");
            if ($encabezado) {
                $concepto_obj->setEncabezado("1");
            } else {
                $concepto_obj->setEncabezado("0");
            }
            if (!$concepto_obj->nuevoRegistro()) {
                $mensajePagina.= "<br/>Error: no se pudo insertar el concepto $concepto de la factura $i";
                continue;
            }
            //echo "<br/> Agregando $cantidad $um $concepto $pu $importe para la factura $i";
            $iva_concepto = $importe * $iva;
            $concepto1 = array($cantidad, $um, $concepto, $pu, $importe);
            array_push($conceptos, $concepto1);
            $xml->setConceptos($conceptos);

            $impuestos1 = array("IVA", "" . ($iva * 100), $iva_concepto);
            array_push($Impuestos_Trasladado, $impuestos1);

            $subtotal += $importe;
            $total += ($importe + $iva_concepto);
        }

        if (isset($_SESSION['idUsuario']) && $menu->tieneSubmenu($_SESSION['idUsuario'], 92)) {
            $mensajePagina.= "<br/>Se registró la nueva pre-factura con el folio <a href='" . $liga . "principal.php?mnu=facturacion&action=alta_factura&id=" . $factura2->getIdFactura() . "' 
                target='_blank'>" . $factura2->getFolio() . "</a><br/>";
        } else {
            $mensajePagina.= "Se registró la nueva pre-factura con el folio" . $factura2->getFolio();
        }
    }
}

//En cada valor del array pagos tenemos algo así IdTicket,monto ej. 55326,1000, guardaremos esto en un array 
//clave valor para que sea más fácil asociar el ticket con el pago
foreach ($pagos as $value) {
    $array = explode(",", $value);
    $pagoTicket[$array[0]] = $array[1];
}

foreach ($descuentos as $value) {
    $array = explode(",", $value);
    if((int)$array[1] > 100){
        $array[1] = 100;
    }
    $descuento_ticket[$array[0]] = $array[1];
}

//Ahora pagoTicket es una array algo así $pagoTicket[IdTicket] = monto
$consultaAgruparTicket = "SELECT tt.IdUsuario, tt.IdTicket, IF(ISNULL(u.ProveedorFactura),u.RFC, u.ProveedorFactura) AS RFC, 
        CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno,u.ApellidoMaterno) AS Nombre, dfe.IdDatosFacturacionEmpresa AS IdReceptor,
        p.PorcentajeServicio
        FROM c_ticket t
        INNER JOIN k_tecnicoticket AS tt ON tt.IdTicket = t.IdTicket
        INNER JOIN c_usuario AS u ON u.IdUsuario = tt.IdUsuario
        INNER JOIN c_cliente AS c ON c.ClaveCliente = t.ClaveCliente
        INNER JOIN c_datosfacturacionempresa AS dfe ON dfe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa
        LEFT JOIN c_proveedor AS p ON p.RFC = u.RFC
        WHERE t.IdTicket IN ($tickets2) ";
//echo "$consultaAgruparTicket <br>";
$resultTickets = $catalogo->obtenerLista($consultaAgruparTicket);
$arrayTicketsByRFC = array();
while ($rsTicket = mysql_fetch_array($resultTickets)) {
    if(!isset($rsTicket['RFC']) || empty($rsTicket['RFC'])){
        echo "<br/>El $nombre_tecnico ".$rsTicket['Nombre']." no tiene RFC registrado en el catalogo de choferes, es necesario registra dicho RFC para poder generarle su comisión";
        continue;
    }
    //Aquí guardamos los tickets por RFC, sólo los concatenamos si son del mismo RFC.
    $arrayTicketsByRFC[$rsTicket['RFC']] .= $rsTicket['IdTicket'] . ",";
    //
    $porcentaje_servicio = 0.70;
    if(isset($rsTicket['PorcentajeServicio']) && ($rsTicket['PorcentajeServicio'])!="" ){
        $porcentaje_servicio = (int)$rsTicket['PorcentajeServicio'] / 100;
    }
   
    if (isset($usuarioPago[$rsTicket['RFC']])) {
        //Si hubo pago al chofer, entonces le registramos la parte proporcional a este pago
        if(isset($pagoTicket[$rsTicket['IdTicket']]) && $pagoTicket[$rsTicket['IdTicket']]!=""){
            $usuarioPago[$rsTicket['RFC']] += $pagoTicket[$rsTicket['IdTicket']];
            $monto_final_ticket[$rsTicket['IdTicket']] = $pagoTicket[$rsTicket['IdTicket']];
            $facturaViaticos[$rsTicket['IdTicket']] = 0;
        }else{//No hubo pago al chofer, entonces aquí facturamos los gastos de viáticos o lo de los otros estados
            if(isset($monto_ticket[$rsTicket['IdTicket']])){//Por lo que se cargo que no fue viático
                $usuarioPago[$rsTicket['RFC']] += $monto_ticket[$rsTicket['IdTicket']] * $porcentaje_servicio;
                $monto_final_ticket[$rsTicket['IdTicket']] += $monto_ticket[$rsTicket['IdTicket']] * $porcentaje_servicio;
                $facturaViaticos[$rsTicket['IdTicket']] = 0;
            }
            if(isset($monto_ticket2[$rsTicket['IdTicket']])){//Por lo que se cargó y si fue viático.
                $usuarioPago[$rsTicket['RFC']] += $monto_ticket2[$rsTicket['IdTicket']];
                $monto_final_ticket[$rsTicket['IdTicket']] += $monto_ticket2[$rsTicket['IdTicket']];
                $facturaViaticos[$rsTicket['IdTicket']] = 1;
            }
        }
    } else {//Mismos casos que arriba.
        $rfcReceptor[$rsTicket['RFC']] = $rsTicket['IdReceptor'];
        if(isset($pagoTicket[$rsTicket['IdTicket']]) && $pagoTicket[$rsTicket['IdTicket']]!=""){
            $usuarioPago[$rsTicket['RFC']] = $pagoTicket[$rsTicket['IdTicket']];    
            $monto_final_ticket[$rsTicket['IdTicket']] = $pagoTicket[$rsTicket['IdTicket']];
            $facturaViaticos[$rsTicket['IdTicket']] = 0;
        }else{
            if(isset($monto_ticket[$rsTicket['IdTicket']])){//Por lo que se cargo que no fue viático
                $usuarioPago[$rsTicket['RFC']] = $monto_ticket[$rsTicket['IdTicket']] * $porcentaje_servicio;
                $monto_final_ticket[$rsTicket['IdTicket']] = $monto_ticket[$rsTicket['IdTicket']] * $porcentaje_servicio;
                $facturaViaticos[$rsTicket['IdTicket']] = 0;
            }
            if(isset($monto_ticket2[$rsTicket['IdTicket']])){//Por lo que se cargó y si fue viático.
                $usuarioPago[$rsTicket['RFC']] = $monto_ticket2[$rsTicket['IdTicket']];
                $monto_final_ticket[$rsTicket['IdTicket']] = $monto_ticket2[$rsTicket['IdTicket']];
                $facturaViaticos[$rsTicket['IdTicket']] = 1;
            }
        }

        //Si este usuario no está dado de alta como proveedor lo damos de alta.
        $proveedor = new Proveedor();
        $proveedor->setRfc($rsTicket['RFC']);
        if (!$proveedor->getRegistroByRFC()) {    //Damos de alta el usuario como proveedor para poder crear una factura
            //Vamos a asignarle una clave de proveedor
            $consultaClave = "SELECT SUBSTRING(MAX(p.ClaveProveedor),2) AS Clave FROM c_proveedor AS p WHERE p.ClaveProveedor RLIKE '^P[0-9]*$';";
            $resultClave = $catalogo->obtenerLista($consultaClave);
            if ($rsClave = mysql_fetch_array($resultClave)) {
                $clave = (int) $rsClave['Clave'];
            }
            $clave++;   //Incrementamos en uno la clave
            $proveedor->setClave("P" . $clave);
            $proveedor->setNombre($rsTicket['Nombre']);
            $proveedor->setNotificar(0);
            $proveedor->setTipo(2);
            $proveedor->setActivo(1);
            $proveedor->setUsuarioCreacion($_SESSION['user']);
            $proveedor->setUsuarioModificacion($_SESSION['user']);
            $proveedor->setPorcentajeServicio(70);
            if ($proveedor->newRegistro()) {
                echo "Se ha dado de alta al usuario con RFC: " . $rsTicket['RFC'] . " como proveedor para poder generarle facturas<br/>";
            } else {
                echo "Hubo un error al registrar al proveedor";
            }
        }
    }
    
}
/*
foreach ($usuarioPago as $key => $value) {//Se recorren los proveedores para añadir los viaticos que se les tienen que pagar.
    if(isset($proveedor_viaticos[$key]) && !empty($proveedor_viaticos[$key])){
        $usuarioPago[$key] += $proveedor_viaticos[$key];
    }
}*/

//Después de haber obtenido lo que hay que pagarle a cada proveedor, insertamos la factura al proveedor.
foreach ($usuarioPago as $clave => $valor) {  //Recordando que la clave es el RFC y el valor el pago
    $proveedor = new Proveedor();
    $proveedor->setRfc($clave);    
    /*Antes de insertar el pago, verificamos que no deba nada de financial*/
    $financial = new Financial();
    $usuario = new Usuario();
    if($usuario->getRegistroByRFC($clave) && $financial->getRegistroByUsuario($usuario->getId())){//Si es true, quiere decir que el operador debe dinero y se le tiene que descontar
        $detalle = new FinancialDetalle();
        
        $total_a_cobrar = 0;        
        //$temporal12 = "";
        foreach ($tickets_rfc_proveedor[$clave] as $value) {//Obtenemos el monto que se va a descontar segun los porcentajes de descuento            
            $total_a_cobrar += ( ((float)$monto_final_ticket[$value]) * ((float)$descuento_ticket[$value] / 100) );
        }
        
        if($financial->getTotal() < $total_a_cobrar){//Si el total del descuento es mayor a lo que se debe, entonces lo que se paga ya sólo será lo que se debe
            $total_a_cobrar = $financial->getTotal();
        }
        
        $valor -= $total_a_cobrar;//Se le descuenta a la CxP
        if($total_a_cobrar > 0){
            /*Agregamos el concepto en financial*/
            $detalle->setIdFinancial($financial->getIdPrestamo());
            $detalle->setIdConcepto(3);
            $detalle->setImporte($total_a_cobrar);
            $detalle->setComentario("Descuento directo de servicios realizados: ".  implode(",", $tickets_rfc_proveedor[$clave]));
            $detalle->setFecha(date('Y')."-".date('m')."-".date('d'));
            $date = new DateTime($detalle->getFecha());
            $week = $date->format("W");
            $last_monday = date('Y-m-d', strtotime('previous monday', strtotime($detalle->getFecha())) ); 
            $detalle->setSemana($week);
            $detalle->setFechaSemana($last_monday);
            $detalle->setUsuarioCreacion($_SESSION['user']);
            $detalle->setUsuarioUltimaModificacion($_SESSION['user']);
            $detalle->setPantalla($pantalla);
            if($detalle->newRegistro()){
                foreach ($tickets_rfc_proveedor[$clave] as $value) {//Obtenemos el monto que se va a descontar segun los porcentajes de descuento            
                    $detalle->setIdTicket($value);
                    $detalle->newRegistroFinancialTicket();
                }
                
                $obj2 = new Financial();
                if($obj2->getRegistroById($detalle->getIdFinancial()) && $obj2->getIdEstatus() == 1 && $obj2->isFinancialPagado()){
                    $obj2->setIdEstatus(2);
                    if($obj2->updateRegistro()){
                        echo "<br/>El registro de préstamo ".$detalle->getIdFinancial()." fue marcada como cerrado, se ha cubierto totalmente el monto prestado<br/>";
                    }else{
                        echo "<br/>No se pudo cerrar el registro de forma automática<br/>";
                    }
                }else if($obj2->getIdEstatus() == 2 && !$obj2->isFinancialPagado()){
                    $obj2->setIdEstatus(1);
                    if($obj2->updateRegistro()){
                        echo "<br/>El registro de préstamo ".$detalle->getIdFinancial()." fue marcada como abierto, no se ha cubierto el monto prestado<br/>";
                    }else{
                        echo "<br/>No se pudo abrir el registro de forma automática<br/>";
                    }
                }
            }else{
                echo "Atención: no se pudo registrar el descuento de financial por $ ".  number_format($total_a_cobrar, 2).", por favor notificar al administrador";
            }
        }
    }
    
    if($proveedor->getRegistroByRFC()){
        $oc = new Orden_Compra();
        $oc->setFechaOC(gmdate('Y-m-d h:i:s \G\M\T'));
        //Vamos a validar, si tiene el campo ProveedorFacturar en nulo, entonces se facturará a si mismo, caso contrario, será el otro quién lo haga.
        $oc->setFacturaEmisor($proveedor->getClave());
        $oc->setCondicionPago(3);//En efectivo
        $oc->setEstatus(70);//Surtido
        $oc->setActivo(1);
        $empresa = new Empresa();
        $oc->setFacturaRecptor($empresa->getIdEmpresaFacturarTickets());
        $oc->setPantalla("PHP facturarTickets");
        $oc->setUsuarioCreacion($_SESSION['user']);
        $oc->setUsuarioModificacion($_SESSION['user']);
        $oc->setFactura_Ticket(1);
        $texto = "<br>Se factura el $nombre_objeto " . trim($arrayTicketsByRFC[$proveedor->getRfc()],",") . " con valor $valor" ;
        $oc->setDescripcion_Ticket($texto);
        $oc->setSubtotal_Ticket($valor);
        $oc->setTotal_Ticket($valor * 1.16);
        $oc->setNo_pedido("$nombre_objeto " . trim($arrayTicketsByRFC[$proveedor->getRfc()],","));
        if($oc->newRegistroTicket()){
            //Como hubo éxito, entonces guardaremos las reclaciones entre ticket y factura en la nueva tabla :v!!!
            $datitos = explode(",", trim($arrayTicketsByRFC[$proveedor->getRfc()],","));
            if(!empty($datitos) && count($datitos) > 0){
                foreach ($datitos AS $ticketsByRFC){
                    $oc->registrarRelacionTyF($ticketsByRFC,$monto_final_ticket[$ticketsByRFC], $facturaViaticos[$ticketsByRFC]);
                }
            }
            echo "$texto. ";
            if (isset($_SESSION['idUsuario']) && $menu->tieneSubmenu($_SESSION['idUsuario'], 97)) {//97 es el id para este menú.
                $mensajePagina.= "<br/>Se creo la orden de compra con el folio <a href='" . $liga . "compras/reporte_orden_compra.php?id=" . $oc->getIdOrdenCompra() . "' 
                    target='_blank'>" . $oc->getIdOrdenCompra() . "</a><br/>";
            } else {
                $mensajePagina.= "Se creo la orden de compra <b>" . $oc->getIdOrdenCompra() . "</b>.";
            }
        }else{
            echo "Hubo un error al generar la orden de compra para la facturación<br/>";            
        }
    }else{
        echo "<br/>No se pudo encontrar al proveedor por RFC ($clave)";
    }
}
echo "<br/>Después de timbrar las facturas, agregar una nota distinta a facturar para que los ticket ya no aparezcan en pendientes facturar";
echo "<br>";
//print_r($usuarioPago);
?>
<html>
    <head>
        <title>Generaci&oacute;n de pre-facturas</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    </head>
    <body>
        <?php
        echo $mensajePagina;
        ?>
    </body>
</html>

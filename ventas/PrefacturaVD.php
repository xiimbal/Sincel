<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Factura2.class.php");
include_once("../WEB-INF/Classes/Concepto.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/Menu.class.php");
include_once("../WEB-INF/Classes/DatosFacturacionEmpresa.class.php");
include_once("../WEB-INF/Classes/Contrato.class.php");
include_once("../WEB-INF/Classes/ccliente.class.php");

$parametros = new Parametros();
$menu = new Menu();
$datosFacturacion = new DatosFacturacionEmpresa();
$contrato = new Contrato();

$parametros->getRegistroById("8");
$liga = $parametros->getDescripcion();
$pantalla = "Prefactura VD PHP";
$usuario = $_SESSION['user'];
$MetodoPago = "5";
$FormaPago = "1";
$IdUsoCFDI = "3";  
$no_cuenta = "";

$consulta = "SELECT
    c_ventadirecta.Fecha AS Fecha,
    c_ventadirecta.IdVentaDirecta AS Num,
    CASE c_ventadirecta.Estatus
        WHEN 1 THEN 'Registrada'
        WHEN 2 THEN 'Facturada'
        WHEN 3 THEN 'Cancelada'
        WHEN 4 THEN 'Cerrada'
    END  AS Status,
    c_ventadirecta.Clave_Localidad AS ClaveCentroCosto,
    kvd.Cantidad AS Cantidad,
    kvd.TipoProducto AS Tipo,
    kvd.IdProduto AS IdProduto,
    (CASE WHEN kvd.TipoProducto = 0 THEN te.IdClaveProdServ ELSE tc.IdClaveProdServ END) AS IdClaveProdServ,
    (CASE WHEN kvd.TipoProducto = 0 THEN te.IdUnidadMedida ELSE tc.IdUnidadMedida END) AS IdUnidadMedida,
    kvd.Costo AS Costo,
    c_cliente.NombreRazonSocial AS NombreRazonSocial,
    c_cliente.RFC AS RFC,
    c_cliente.Modalidad,
    c_cliente.ClaveCliente AS ClaveCliente,
    c_datosfacturacionempresa.RazonSocial AS Razon,
    c_datosfacturacionempresa.IdDatosFacturacionEmpresa AS IDEmisor,
    CONCAT(c_datosfacturacionempresa.RazonSocial,' ',c_datosfacturacionempresa.Calle,' No. Ext. ',c_datosfacturacionempresa.NoExterior,' No. Int. ',c_datosfacturacionempresa.NoInterior,' ,COL. ',c_datosfacturacionempresa.Colonia,' ',c_datosfacturacionempresa.Delegacion,', ',c_datosfacturacionempresa.Pais,' ',c_datosfacturacionempresa.Estado,' C.P. ',c_datosfacturacionempresa.CP,' TELS.',c_datosfacturacionempresa.Telefono) AS facturacion,
    CONCAT(c_usuario.Nombre,' ',c_usuario.ApellidoMaterno,' ',c_usuario.ApellidoPaterno) AS Contacto,
    (CASE WHEN kvd.TipoProducto = 0 THEN (SELECT MAX(e.Modelo) FROM `c_equipo` AS e WHERE e.NoParte = kvd.IdProduto) ELSE (SELECT MAX(com.Modelo) FROM `c_componente` AS com WHERE com.NoParte = kvd.IdProduto) END) AS Modelo,
    (CASE WHEN kvd.TipoProducto = 0 THEN (SELECT e.Descripcion FROM `c_equipo` AS e WHERE e.NoParte = kvd.IdProduto) ELSE (SELECT com.Descripcion FROM `c_componente` AS com WHERE com.NoParte = kvd.IdProduto) END) AS Descripcion,
    c_domicilio.IdDomicilio AS Id_Domicilio,
    c_bitacora.NoSerie AS Serie,
    GROUP_CONCAT(
        DISTINCT(
            CONCAT(' SERIE ',c_bitacora.NoSerie,' ',
            (CASE WHEN kvd.TipoProducto = 0 THEN (SELECT MAX(e.Modelo) FROM `c_equipo` AS e WHERE e.NoParte = kvd.IdProduto) 
            ELSE (SELECT MAX(com.Modelo) FROM `c_componente` AS com WHERE com.NoParte = kvd.IdProduto) 
            END)
        )
    ) SEPARATOR '') AS Des
    FROM c_ventadirecta
    INNER JOIN k_ventadirectadet AS kvd ON kvd.IdVentaDirecta = c_ventadirecta.IdVentaDirecta
    INNER JOIN c_cliente ON c_cliente.ClaveCliente = c_ventadirecta.ClaveCliente
    INNER JOIN c_usuario ON c_usuario.IdUsuario= c_cliente.EjecutivoCuenta
    LEFT JOIN c_datosfacturacionempresa ON c_datosfacturacionempresa.IdDatosFacturacionEmpresa = c_cliente.IdDatosFacturacionEmpresa
    LEFT JOIN c_domicilio ON c_domicilio.ClaveEspecialDomicilio=c_cliente.ClaveCliente AND c_domicilio.IdTipoDomicilio = 3        
    LEFT JOIN c_bitacora ON c_bitacora.id_solicitud=c_ventadirecta.id_solicitud AND c_bitacora.NoParte = kvd.IdProduto
    LEFT JOIN c_componente AS cmp ON cmp.NoParte = kvd.IdProduto
    LEFT JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = cmp.IdTipoComponente
    LEFT JOIN c_equipo AS e ON e.NoParte = kvd.IdProduto
    LEFT JOIN c_tipoequipo AS te ON te.IdTipoEquipo = e.IdTipoEquipo
    WHERE c_ventadirecta.IdVentaDirecta = " . $_POST['vd'] . " 
    GROUP BY kvd.IdVentaDirectaDet;";
$catalogo = new Catalogo();
$query_solicitud = $catalogo->obtenerLista($consulta);
$factura_creada = false;
/* Datos para guardar en la bd */
$factura2 = new Factura();
        
while ($rs = mysql_fetch_array($query_solicitud)) {
    $datosFacturacion->getRegistroById($rs['IDEmisor']);

    if (!$factura_creada) {         
        if($rs['Modalidad'] != "3"){//Si es cliente de venta, los datos de la pre-factura se cargan desde k_ventaconfiguracion
            $result_contrato = $contrato->getRegistroValidacion($rs['ClaveCliente']);
            while($rs_contrato =  mysql_fetch_array($result_contrato)){
                if($rs_contrato['IdMetodoPago'] != ""){
                    $MetodoPago = $rs_contrato['IdMetodoPago'];
                }
                if($rs_contrato['FormaPago'] != ""){
                    $FormaPago = $rs_contrato['FormaPago'];
                }

                if($rs_contrato['IdUsoCFDI'] != ""){
                    $IdUsoCFDI = $rs_contrato['IdUsoCFDI'];
                }
                $no_cuenta = $rs_contrato['NumeroCuenta'];
            }            
        }else{
            $ccliente = new ccliente();
            $ccliente->setClaveCliente($rs['ClaveCliente']);
            if($ccliente->getDatosFiscalesVenta()){
                if($ccliente->getMetodoPago() != ""){
                    $MetodoPago = $ccliente->getMetodoPago();
                }
                if($ccliente->getFormaPago() != ""){
                    $FormaPago = $ccliente->getFormaPago();
                }
                if($ccliente->getIdUsoCFDI() != ""){
                    $IdUsoCFDI = $ccliente->getIdUsoCFDI();
                }
                $no_cuenta = $ccliente->getNumero_cuenta();                
            }
        }
        
   
        $factura2->setIdEmpresa($datosFacturacion->getIdDatosFacturacionEmpresa());
        //Aunque los campos dicen setRFC, hay que mandarles la clave del cliente y el id de la empresa dee facturacion
        $factura2->setRFCEmisor($datosFacturacion->getIdDatosFacturacionEmpresa());
        $factura2->setRFCReceptor($rs['ClaveCliente']);
        $factura2->setPeriodoFacturacion($rs['Fecha']);
        $factura2->setIdDomicilioFiscal($rs['Id_Domicilio']);
        $factura2->setUsuarioCreacion($usuario);
        $factura2->setUsuarioUltimaModificacion($usuario);
        $factura2->setPantalla($pantalla);
        $factura2->setFormaPago($FormaPago);
        $factura2->setMetodoPago($MetodoPago);
        $factura2->setNumCtaPago($no_cuenta);
        $factura2->setId_TipoFactura(1);
        $factura2->setTipoArrendamiento("1"); //Se guarda el tipo de arrendamiento 1, todas estas facturas son de arrendamiento    
        $factura2->setTipoArrendamiento(2);

        if ($datosFacturacion->getIdSerie() != "") {
            $factura2->setIdSerie($datosFacturacion->getIdSerie());
        }

        if ((int) $datosFacturacion->getCfdi33() == 1) {
            $factura2->setCFDI33(1);
            $factura2->setIdUsoCFDI($IdUsoCFDI);
        } else {
            $factura2->setCFDI33(0);
        }

        if ($factura2->NuevaPreFactura()) {
            $factura_creada = true;
        } else {
            echo "Error: No se pudo generar la prefactura";
            break;
        }
    }

    $concepto_obj = new Concepto();
    if ((int) $datosFacturacion->getCfdi33() == 1) {
        if (isset($rs['IdClaveProdServ']) && !empty($rs['IdClaveProdServ'])) {
            $idClave = $rs['IdClaveProdServ'];
        } else {
            $idClave = 24639;
        }

        if (isset($rs['IdUnidadMedida']) && !empty($rs['IdUnidadMedida'])) {
            $idUM = $rs['IdUnidadMedida'];
        } else {
            $idUM = 1729;
        }

        $idProductoEmpresa = 0;
        $consulta = "SELECT * FROM k_empresaproductosat eps WHERE IdDatosFacturacionEmpresa = " . $datosFacturacion->getIdDatosFacturacionEmpresa() .
                " AND IdClaveProdServ = $idClave AND IdUnidadMedida = $idUM;";
        $result2 = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($result2) > 0) {
            while ($rs2 = mysql_fetch_array($result2)) {
                $idProductoEmpresa = $rs2['IdEmpresaProductoSAT'];
            }
        } else {
            $insert = "INSERT INTO k_empresaproductosat VALUES(0," . $datosFacturacion->getIdDatosFacturacionEmpresa() . ",$idClave,$idUM,'sistemas',NOW(),'sistemas',NOW(),'Facturar Reporte Lectura');";
            //echo $insert;
            $idProductoEmpresa = $catalogo->insertarRegistro($insert);
        }
        $concepto_obj->setIdEmpresaProductoSAT($idProductoEmpresa);
        $concepto_obj->setUnidad("");
    } else {
        $concepto_obj->setUnidad("Servicio");
        $concepto_obj->setIdEmpresaProductoSAT("");
    }
    $concepto_obj->setIdFactura($factura2->getIdFactura());
    $concepto_obj->setPrecioUnitario($rs['Costo']);
    $concepto_obj->setCantidad($rs['Cantidad']);
    if ($rs['Serie'] != "") {
        $concepto_obj->setDescripcion($rs['Des']);
    } else {
        $concepto_obj->setDescripcion($rs['Modelo']);
    }
    $concepto_obj->setUsuarioCreacion($usuario);
    $concepto_obj->setUsuarioUltimaModificacion($usuario);
    $concepto_obj->setPantalla($pantalla);
    $concepto_obj->setTipo($rs['Tipo']);
    $concepto_obj->setId_articulo($rs['IdProduto']);        
    
    if (!$concepto_obj->nuevoRegistro()) {
        echo "<br/>Error: no se pudo insertar el concepto ".$concepto_obj->getDescripcion()." de la factura";
    }    
}

if($factura_creada){
    $catalogo->obtenerLista("UPDATE c_ventadirecta SET id_prefactura='" . $factura2->getIdFactura() . "',Estatus=2,UsuarioUltimaModificacion='" . $_SESSION['user'] . "',FechaUltimaModificacion=NOW() WHERE IdVentaDirecta='" . $_POST['vd'] . "'");

    if (isset($_SESSION['idUsuario']) && $menu->tieneSubmenu($_SESSION['idUsuario'], 92)) {
        if ((int) $datosFacturacion->getCfdi33() == 1) {
            $pagina = "alta_factura_33";
        } else {
            $pagina = "alta_factura";
        }
        
        echo "Se registró la nueva pre-factura con el folio <a href='" . $liga . "principal.php?mnu=facturacion&action=$pagina&id=" . $factura2->getIdFactura() . "' 
                target='_blank'>" . $factura2->getFolio() . "</a><br/>";
    } else {
        echo "Se registró la nueva pre-factura con el folio" . $factura2->getFolio();
    }
}
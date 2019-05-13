<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../../Classes/PagoParcial.class.php");
include_once("../../Classes/SaldosAFavor.class.php");
include_once("../../Classes/Factura.class.php");
include_once("../../Classes/ccliente.class.php");
include_once("../../Classes/Banco.class.php");

if(isset ($_POST['comprueba_pago']) && isset($_POST['idFactura']) && isset($_POST['pago']) ){
    $pago = new PagoParcial();
    if($pago->verificaPagoMayor($_POST['pago'], $_POST['idFactura'], $_POST['idPago'])){
        echo "El pago por ".$_POST['pago']." supera el monto total de la factura, desea continuar?";
        return;
    }
    echo "";
}else if (isset($_POST['pago']) && $_POST['pago'] != "") {  
    $pp = new PagoParcial();
    $saf = new SaldosAFavor();
    $saf->setIdPagoParcial($_POST['pago']);
    if(!$saf->verificarSaldoAFavorUsado()){
        $saf->restaurarDatosByPago();
        if($saf->eliminarSaldoAFavorByPago()){
            echo "Se ha eliminado el saldo a favor asociado a este pago";
        }
        $pp->setId_pago($_POST['pago']);
        if(isset($_POST['cxc'])){
            $cxc = true;
        }else{
            $cxc = false;
        }
        $pp->getRegistrobyID($cxc);
        if ($pp->deleteRegistro($cxc)) {
            echo "Se ha eliminado el pago parcial exitosamente";
        } else {
            echo "Error: el pago parcial tiene valores dependientes";
        }
    }else{
        echo "<h2>Error: Los siguientes pago ocupan saldo a favor que se genero de este pago: ".$saf->getMensajes()."</h2>";
    }
} else {
    $saf = new SaldosAFavor();
    $parametros = "";
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $pago = new PagoParcial();
    $saf->setRFC($parametros['RFC']);
    if(isset($parametros['cxc_activo'])){
        $cxc = true;
    }else{
        $cxc = false;
    }
    $importeTotal = 0;
    if(isset($parametros['importe']) && $parametros['importe'] != "" && $parametros['importe'] > 0){
        $importeTotal += (double)$parametros['importe'];
    }
    if(isset($parametros['cantidadSaldo']) && $parametros['cantidadSaldo'] != "" && $parametros['cantidadSaldo'] > 0){
        $importeTotal += (double)$parametros['cantidadSaldo'];
    }
    $pago->setIdEmpresaFactura($parametros['idEmpresaFactura']);
    $pago->setIdFormaPago($parametros['forma_pago']);
    $pago->setIdTipoCadena($parametros['TipoCadPago']);
    $pago->setCertPago($parametros['CertPago']);
    $pago->setCadPago($parametros['CadPago']);
    $pago->setSelloPago($parametros['SelloPago']);
    $pago->setIdSerie($parametros['serie']);
    $pago->setReferencia($parametros['referencia']);
    $pago->setId_factura($parametros['factura']);
    $pago->setObservaciones($parametros['observaciones']);
    $pago->setFechapago($parametros['fecha']);    
    $pago->setCuentaBancaria($parametros['cuentaBancarias']);
    $pago->setImporte($importeTotal);
    $pago->setRFCBancoEmisorOrd($parametros['RFCBancoEmisor']);
    $pago->setNomBancoEmisorOrd($parametros['NombreBancoEmisor']);
    $pago->setCtaOrdenante($parametros['ClaveInterbancariaEmisor']);
    $pago->setPantalla("PHP Controller_PagoParcial");
    $pago->setUsuarioCreacion($_SESSION['user']);
    $pago->setUsuarioUltimaModificacion($_SESSION['user']);
    
    $catalogo = new Catalogo();
    $query = "SELECT b.IdBanco, c.noCuenta from c_cuentaBancaria c"
            . " LEFT JOIN c_banco b ON b.IdBanco = c.idBanco WHERE c.RFC='". $parametros['RFC'] . "'";
    $result = $catalogo->obtenerLista($query);
    $noCuenta = $banco = "";
    while($rs = mysql_fetch_array($result))
    {
        $noCuenta = $rs['noCuenta'];
        $banco = $rs['IdBanco'];
    }
    $pago->setPantalla("PHP Controller_PagoParcial");
    $pago->setUsuarioCreacion($_SESSION['user']);
    $pago->setUsuarioUltimaModificacion($_SESSION['user']);
    
    if (isset($parametros['pago']) && $parametros['pago'] != "") {
        $pago->setId_pago($parametros['pago']);
        $saf->setIdPagoParcial($parametros['pago']);
        $saf->restaurarDatosByPago();
        if ($pago->updateRegistro($cxc)) {
            echo "<h2>Se ha editado el pago parcial exitosamente</h2>";
        } else {
            echo "Error: El pago parcial no se logro editar";
        }
    } else {
        if ($pago->nuevoRegistroPago($cxc)) {
            echo "<h2>Se ha registrado el nuevo pago parcial</h2>";
        } else {
            echo "Error: El pago parcial no se registro";            
        }
    }
    $saf->setIdPagoParcial($pago->getId_pago());
    $saf->setIdFactura($pago->getId_factura());
    $saf->setPantalla("PHP Controller_PagoParcial");
    $saf->setUsuarioCreacion($_SESSION['user']);
    $saf->setUsuarioUltimaModificacion($_SESSION['user']);
    if(isset($parametros['cantidadSaldo']) && $parametros['cantidadSaldo'] != "" && $parametros['cantidadSaldo'] > 0){
        $saf->restarCantidadDeSaldos((double)$parametros['cantidadSaldo']);
    }
    
    if($pago->verificaPagoMayor($importeTotal, $parametros['factura'], $pago->getId_pago())){
        $saf->setCantidad($pago->getImporteExtra());        
        if($saf->nuevoRegistro()){
            echo "<br/><h2>Se ha incrementado el saldo a favor de éste cliente</h2>";
        }
    }
    
    $cliente = new ccliente();
    $factura = new Factura_NET();
    
    $factura->getRegistroById($pago->getId_factura());
    $cliente->getregistrobyRFC($factura->getRFCReceptor());
    
    if($parametros['RFCBancoEmisor'] != "" || $parametros['NombreBancoEmisor'] != "" || $parametros['ClaveInterbancariaEmisor'] != ""){
        $datosCuentas = $pago->getCuentasFiscales($pago->getId_factura());
        if ($datosCuentas != false) {
            if($parametros['RFCBancoEmisor'] != $datosCuentas['RfcEmisorCtaOrd'] ||
               $parametros['NombreBancoEmisor'] != $datosCuentas['NomBancoEmisorOrd'] ||
               $parametros['ClaveInterbancariaEmisor'] != $datosCuentas['CtaOrdenante']){
                $IdBanco = "";
                if($parametros['RFCBancoEmisor'] != "" || $parametros['NombreBancoEmisor'] != ""){
                    $banco = new Banco();
                    $banco->setRFC($parametros['RFCBancoEmisor']);
                    $banco->setActivo(1);
                    $banco->setNombre($parametros['NombreBancoEmisor']);
                    $banco->setUsuarioCreacion($_SESSION['user']);
                    $banco->setUsuarioUltimaModificacion($_SESSION['user']);
                    $banco->setPantalla("Alta Pago Parcial");
                    if(!$banco->newRegistro()){
                        echo "Error: No se logro registrar el nuevo banco";
                        return;
                    }
                    $IdBanco = $banco->getIdBanco();
                }

                $banco = "";
                if(isset($IdBanco) && !empty($IdBanco)){
                    $banco = "IdBanco = $IdBanco, ";
                }
                
                $update = "UPDATE c_contrato SET NumeroCuenta = '".$parametros['ClaveInterbancariaEmisor']."', $banco
                        UsuarioUltimaModificacion = '".$_SESSION['user']."', FechaUltimaModificacion = NOW(), Pantalla = 'Alta Pago Parcial' 
                        WHERE ClaveCliente = '".$cliente->getClaveCliente()."'";
                $query = $catalogo->obtenerLista($update);
                if ($query != 1) {
                    echo "Error: No se logro editar la configuración de la cuenta del contrato del cliente";
                }
                
                if($cliente->getModalidad() == "3"){     
                    $update = "UPDATE k_ventaconfiguracion SET NumeroCuenta = '".$parametros['ClaveInterbancariaEmisor']."', $banco
                        UsuarioUltimaModificacion = '".$_SESSION['user']."', FechaUltimaModificacion = NOW(), Pantalla = 'Alta Pago Parcial' 
                        WHERE ClaveCliente = '".$cliente->getClaveCliente()."'";
                    $query = $catalogo->obtenerLista($update);
                    if ($query != 1) {
                        echo "Error: No se logro editar la configuración de la cuenta del contrato del cliente";
                    }
                }
            }
        }
    }
}
?>
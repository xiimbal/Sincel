<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../../Classes/PagoParcial.class.php");
include_once("../../Classes/MultiPagosParciales.class.php");
include_once("../../Classes/SaldosAFavor.class.php");
include_once("../../Classes/ccliente.class.php");
include_once("../../Classes/Banco.class.php");
include_once("../../Classes/Factura3.class.php");
include_once("../../Classes/Catalogo.class.php");

if (isset($_POST['idpago']) && $_POST['idpago'] != "" && $_POST['idpago'] != 0) {  //Es cuando se edita
        $idFac = $_POST['factura'];
        $arr_fac = explode("_", $idFac);
        $tam = sizeof($arr_fac);
        $tam = $tam-1;
        $pp = new MultiPagosParciales();
        $saf = new SaldosAFavor();
        $saf->setIdPagoParcial($_POST['idpago']);
        if(!$saf->verificarSaldoAFavorUsado2()){
            $saf->restaurarDatosByPago2();
            if($saf->eliminarSaldoAFavorByPago2()){
                echo "Se ha eliminado el saldo a favor asociado a este pago";
            }
            $pp->setId_pago($_POST['idpago']);
            if(isset($_POST['cxc'])){
                $cxc = true;
            }else{
                $cxc = false;
            }
            $pp->getRegistrobyID($cxc);
            for ($i=0; $i < $tam ; $i++) { 
                $pp->setId_factura($arr_fac[$i]);
                //echo "FActura ". $pp->getId_factura();
                if ($pp->deleteRegistroFac($cxc)) {
                    echo "..";
                }else{
                    echo "...";
                }
            }
            if ($pp->deleteRegistroPago($cxc)) {
                echo "Se ha eliminado el pago parcial exitosamente";
            } else {
                echo "Error: el pago parcial tiene valores dependientes";
            }
        }else{
            echo "<h2>Error: Los siguientes pago ocupan saldo a favor que se genero de este pago: ".$saf->getMensajes()."</h2>";
        }
    }else{
        //echo "Entramos en el else";
        $idFac = $_POST['idFacturas'];
        $arr_fac = explode("_", $idFac);//arreglo de las facturas
        //$tam = sizeof($arr_fac);//vueltas que dara
        $AbonosFac = $_POST['abonoFacturas'];
        $arr_abn_fac = explode("_", $AbonosFac);//arreglo de los abonos
        $Abntam = sizeof($arr_abn_fac);
        $importeT= $_POST['pago'];
        $arr_importeT = explode("_", $importeT);//arreglo de los pagos totales por factura
        $pagoTam = sizeof($arr_importeT);
        $pagoTam = $pagoTam-1;
        $Abntam = $Abntam-1; //es para dar vueltas de insercion es numero de vueltas - 1

       for ($j=0; $j <number_format($pagoTam) ; $j++) {   
            validar($j);
        }

        
    }

function validar($j){
    if(isset ($_POST['comprueba_pago']) && isset($arr_fac[$j]) && isset($arr_importeT[$j]) ){
        $pago = new MultiPagosParciales();
        if($pago->verificaPagoMayor($arr_importeT[$j], $arr_fac[$j], $_POST['idpago'])){
            echo "El pago por ".$arr_importeT[$j]." supera el monto total de la factura, desea continuar?";
            return;
        }
    }
}

if (validar($j) == false) {
    //echo " pasa al validar ";
            agregarpago();
            agregarFacturas();
 } 

function agregarpago(){
    //echo " Entra al agregar pago ";
        $saf = new SaldosAFavor();
        $parametros = "";
        if (isset($_POST['form'])) {
            $parametros = "";
            parse_str($_POST['form'], $parametros);
        }
        $pago = new MultiPagosParciales();
            if(isset($parametros['cxc_activo'])){
                $cxc = true;
                $cxcV = "1";
                //echo "CXC ". $cxcV;
            }else{
                $cxc = false;
                $cxcV = "0";
                //echo "CXC ". $cxcV;
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
            $pago->setObservaciones($parametros['observaciones']);
            $pago->setFechapago($parametros['fecha']);    
            $pago->set_idsFacturas($parametros['factura']);
            $pago->setCuentaBancaria($parametros['cuentaBancarias']);
            $pago->setImporte($importeTotal);
            $pago->setImporteGeneral($parametros['TotalFacturar']);
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
                    echo "<h2>Se ha registrado el nuevo pago parcial cxc </h2>";
                } else {
                    echo "Error: El pago parcial no se registro";            
                }
            }
}

function agregarFacturas(){
        $saf = new SaldosAFavor();
        $parametros = "";
        if (isset($_POST['form'])) {
            $parametros = "";
            parse_str($_POST['form'], $parametros);
        }
        $pago = new MultiPagosParciales();
        //$pago = new PagoParcial();
        $arr_par_fac = explode("_", $parametros['factura']);
        $tam = sizeof($arr_par_fac);//vueltas que dara
        $tam = $tam-1;
        $arr_Id_pag_det= explode("_", $parametros['pagDet']);
        for ($i=0; $i <$tam ; $i++) { 
                $abono = "abonado_".$i;

                $saf->setRFC($parametros['RFC']);
                if(isset($parametros['cxc_activo'])){
                    $cxc = true;
                }else{
                    $cxc = false;
                }
                $importeTotal = 0;
                if(isset($parametros[$abono]) && $parametros[$abono] != "" && $parametros[$abono] > 0){
                    $importeTotal += (double)$parametros[$abono];
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
                //$pago->setId_factura($parametros['factura']);
                $pago->setId_factura($arr_par_fac[$i]);
                $pago->setImportePagadoXFactura($importeTotal);
                $pago->setObservaciones($parametros['observaciones']);
                $pago->setFechapago($parametros['fecha']);    
                $pago->setCuentaBancaria($parametros['cuentaBancarias']);
                //$pago->setImporte($importeTotal);
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
                    $saf->restaurarDatosByPago2();
                    if ($pago->updateRegistroFac($cxc)) {
                        echo "<h2>Se ha editado el pago parcial exitosamente</h2>";
                    } else {
                        echo "Error: El pago parcial no se logro editar";
                    }
                } else {
                    if ($pago->nuevoPagoFac($cxc)) {
                        echo "<h2>Se ha registrado el nuevo pago parcial cxc La factura es ".$arr_par_fac[$i]."</h2>";
                    } else {
                        echo "Error: El pago parcial no se registro Monto";            
                    }
                }
                $saf->setIdPagoParcial($pago->getId_pago());
                $saf->setIdFactura($pago->getId_factura());
                $saf->setPantalla("PHP Controller_PagoParcial");
                $saf->setUsuarioCreacion($_SESSION['user']);
                $saf->setUsuarioUltimaModificacion($_SESSION['user']);
                if(isset($parametros['cantidadSaldo']) && $parametros['cantidadSaldo'] != "" && $parametros['cantidadSaldo'] > 0){
                    $saf->restarCantidadDeSaldos2((double)$parametros['cantidadSaldo']);
                }
                
                if($pago->verificaPagoMayor($importeTotal, $arr_par_fac[$i], $pago->getId_pago())){
                    $saf->setCantidad($pago->getImporteExtra());        
                    if($saf->nuevoRegistro2()){
                        echo "<br/><h2>Se ha incrementado el saldo a favor de éste cliente</h2>";
                    }
                }
                $cliente = new ccliente();
                $factura = new Factura_NET3(); 
                
                $factura->getRegistroById($pago->getId_factura());
                echo $factura->getRegistroById($pago->getId_factura());
                
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
}
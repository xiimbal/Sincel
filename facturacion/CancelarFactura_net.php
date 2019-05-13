<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
//Importamos las librerias de NU-SOAP
require_once('../WEB-INF/Classes/nu_soap/nusoap.php');
include_once('../WEB-INF/Classes/Cancelar_Factura.class.php');
include_once('../WEB-INF/Classes/PAC.class.php');
include_once('../WEB-INF/Classes/DatosFacturacionEmpresa.class.php');
include_once('../WEB-INF/Classes/Factura.class.php');

if (isset($_GET['folio']) && $_GET['folio']) {
    $cancelar = new Cancelar_Factura();
    $cancelar->setIdFactura($_GET['folio']);
    $cancelar->setUsuarioUltimaModificacion($_SESSION['user']);
    $cancelar->setPantalla("Cancelar Factura");
    
    $factura = new Factura_NET();
    $pac = new PAC();
    $empresa = new DatosFacturacionEmpresa();
    if(!$factura->getRegistroById($_GET['folio'])){
        echo "Error: no se pudo obtener los datos de la factura";
        exit;
    }
    
    $empresa->getRegistroByRFC($factura->getRFCEmisor());
    
    $pac->setId_pac($empresa->getId_pac());
    if(!$pac->getRegistrobyID()){
        echo "Error: no se encontraron los datos del PAC";
        exit;
    }
    
    if ($rs = mysql_fetch_array($cancelar->findidFactura())) {
        if ($rs['folioFiscal'] != "") {
            if ($rs['Diferencia'] >= 3) {
            //if ($rs['Diferencia'] == NULL || $rs['Diferencia'] >= 0) {
                $soapclient = new nusoap_client($pac->getDireccion_cancelacion(), $esWSDL = true);
                $certificado;
                $llave;
                $password;
                $uuid = $rs['folioFiscal'];
                $bol = true;
                //echo $rs['RFCEmisor'];    SCG9606117F7
                if ($rs['RFCEmisor'] == "ODF0403226G7") {//ODF
                    $certificado = $cancelar->getCertificado_odf();
                    $llave = $cancelar->getLlave_odf();
                    $password = $cancelar->getLlave_privada_odf();
                } elseif ($rs['RFCEmisor'] == 'GSC080505189') {//GSC
                    $certificado = $cancelar->getCertificado_gsc();
                    $llave = $cancelar->getLlave_gsc();
                    $password = $cancelar->getLlave_privada_gsc();
                } elseif ($rs['RFCEmisor'] == 'AST1310011K2') {//AST
                    $certificado = $cancelar->getCertificado_ast();
                    $llave = $cancelar->getLlave_ast();
                    $password = $cancelar->getLlave_privada_ast();
                } elseif ($rs['RFCEmisor'] == 'SCG9606117F7') {//SCG
                    $certificado = $cancelar->getCertificado_scg();
                    $llave = $cancelar->getLlave_scg();
                    $password = $cancelar->getLlave_privada_scg();
                } elseif ($rs['RFCEmisor'] == 'CJS130516R33') {//CJS
                    $certificado = $cancelar->getCertificado_sic();
                    $llave = $cancelar->getLlave_sic();
                    $password = $cancelar->getLlave_privada_sic();
                } elseif( $rs['RFCEmisor'] == 'XII120823851'){//Ximbal
                    $certificado = $cancelar->getCertificado_xiimbal();
                    $llave = $cancelar->getLlave_xiimbal();
                    $password = $cancelar->getLlave_privada_ximbal();
                }elseif( $rs['RFCEmisor'] == 'AAEM650629IP7'){//Alarplus
                    $certificado = $cancelar->getCertificado_alarplus();
                    $llave = $cancelar->getLlave_alarplus();
                    $password = $cancelar->getLlave_privada_alarplus();
                }elseif( $rs['RFCEmisor'] == 'DIC131001487'){//Documento Integral
                    $certificado = $cancelar->getCertificado_dic();
                    $llave = $cancelar->getLlave_dic();
                    $password = $cancelar->getLlave_privada_dic();
                    
                }elseif($rs['RFCEmisor'] == 'LARO870426LS7'){
                    $certificado = $cancelar->getCertificado_oscar();
                    $llave = $cancelar->getLlave_oscar();
                    $password = $cancelar->getLlave_privada_oscar();
                    
                 }  elseif($rs['RFCEmisor'] == 'AAA010101AAA'){
                    $certificado = $cancelar->getCertificado_prueba();
                    $llave = $cancelar->getLlave_prueba();
                    $password = $cancelar->getLlave_privada_prueba();
                } 
                else {
                    $bol = FALSE;
                }
                $cancelar->setRFC($rs['RFCEmisor']);
                if ($bol) {
                    $sol = array('certificado' => $certificado, 'llavePrivada' => $llave, 'password' => $password, 'uuid' => $uuid);
                    
                    $usuario = $pac->getUsuario();
                    $clave = $pac->getPassword();
                    $can = array('solicitud' => $sol, 'usuario' => $usuario, 'clave' => $clave);
                    
                    $soap_cancelacion = $soapclient->call('cancelacion', $can);
                    if ($soap_cancelacion == false) {
                        echo "No se logro contactar con el PAC, y no se pudo cancelar el No de Folio " . $rs['Folio'] . " con el Folio Fiscal " . $rs['folioFiscal'];
                    } else {
                        if (strpos($soap_cancelacion["return"], 'CANCELADO CORRECTAMENTE') !== false) {
                            if ($cancelar->CancelarFactura($soap_cancelacion["return"])) {                                
                                echo "La factura No. " . $rs['Folio'] . " se ha cancelado exitosamente";
                            } else {
                                echo "No se canceló, error en la base de datos, con el folio " . $rs['Folio'] . " con el Folio Fiscal " . $rs['folioFiscal'];
                            }
                        } else {
                            if (strpos($soap_cancelacion["return"], 'previamente cancelado') !== false) {
                                if ($cancelar->CancelarFacturaSin()) {                                    
                                    echo "La factura No " . $rs['Folio'] . " con el Folio Fiscal " . $rs['folioFiscal'] . " ya fue cancelada previamente en el SAT";
                                } else {
                                    echo "No se cancelo error en la base de datos, con el folio " . $rs['Folio'] . " con el Folio Fiscal " . $rs['folioFiscal'];
                                }
                            } else {
                                echo "Error al cancelar: ".$soap_cancelacion["return"];
                            }
                        }
                    }
                } else {
                    echo "No se cuenta con los datos suficientes de la empresa del emisor (certificado y llave) para cancelar el No de Folio " . $rs['Folio'];
                }
            } else {// Modificacion Diego: para cancelar sin necesidad de esperar 72 horas.
                if ($cancelar->CancelarFactura($soap_cancelacion["return"])) {                                
                                echo "La factura No. " . $rs['Folio'] . " se ha cancelado exitosamente";
                } else {
                    echo "No se canceló, error en la base de datos, con el folio " . $rs['Folio'] . " con el Folio Fiscal " . $rs['folioFiscal']. " Prueba Diego";
                }
                /*$cancelar->setPendientePorcancelar("1");
                echo "El SAT refleja las facturas timbradas despúes de 72 horas, por favor intentelo más tarde";*/
            }
        } else {
            echo "La factura con el No de Folio " . $rs['Folio'] . "no contiene uuid";
        }
    } else {
        echo "La factura no existe.";
    }
} else {
    echo "No se recibio el folio";
}
?>


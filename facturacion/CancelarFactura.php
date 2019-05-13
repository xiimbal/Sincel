<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
//Importamos las librerias de NU-SOAP
require_once('../WEB-INF/Classes/nu_soap/nusoap.php');
include_once('../WEB-INF/Classes/Cancelar_Factura.class.php');
if (isset($_GET['folio']) && $_GET['folio']) {
    $cancelar = new Cancelar_Factura();
    $cancelar->setIdFactura($_GET['folio']);
    if ($rs = mysql_fetch_array($cancelar->findidFactura())) {
        if ($rs['folioFiscal'] != "") {
            if ($rs['Diferencia'] >= 3) {
                $soapclient = new nusoap_client('http://www.sefactura.com.mx/sefacturapac/TimbradoService?wsdl', $esWSDL = true);
                $certificado;
                $llave;
                $password;
                $uuid = $rs['folioFiscal'];
                $bol = true;
                if ($rs['RFCEmisor'] == "ODF0403226G7") {
                    $certificado = $cancelar->getCertificado_odf();
                    $llave = $cancelar->getLlave_odf();
                    $password = $cancelar->getLlave_privada_odf();
                } elseif ($rs['RFCEmisor'] == 'GSC080505189') {
                    $certificado = $cancelar->getCertificado_gsc();
                    $llave = $cancelar->getLlave_gsc();
                    $password = $cancelar->getLlave_privada_gsc();
                } elseif ($rs['RFCEmisor'] == 'AST1310011K2') {
                    $certificado = $cancelar->getCertificado_ast();
                    $llave = $cancelar->getLlave_ast();
                    $password = $cancelar->getLlave_privada_ast();
                } elseif ($rs['RFCEmisor'] == 'SCG9606117F7') {
                    $certificado = $cancelar->getCertificado_scg();
                    $llave = $cancelar->getLlave_scg();
                    $password = $cancelar->getLlave_privada_scg();
                } else {
                    $bol = FALSE;
                }
                if ($bol) {
                    $sol = array('certificado' => $certificado, 'llavePrivada' => $llave, 'password' => $password, 'uuid' => $uuid);
                    $usuario = "scrombow";
                    $clave = "coem770117";
                    $can = array('solicitud' => $sol, 'usuario' => $usuario, 'clave' => $clave);
                    $soap_cancelacion = $soapclient->call('cancelacion', $can);
                    if ($soap_cancelacion == false) {
                        echo "No se logro contactar con el PAC, y no se pudo cancelar el No de Folio " . $rs['Folio'] . " con el Folio Fiscal " . $rs['folioFiscal'];
                    } else {
                        if (strpos($soap_cancelacion["return"], 'CANCELADO CORRECTAMENTE') !== false) {
                            if ($cancelar->CancelarFactura($soap_cancelacion["return"])) {
                                echo "La factura No. " . $_GET['folio'] . " se ha cancelado exitosamente";
                            } else {
                                echo "No se cancelo error en la base de datos, con el folio " . $rs['Folio'] . " con el Folio Fiscal " . $rs['folioFiscal'];
                            }
                        } else {
                            if (strpos($soap_cancelacion["return"], 'previamente cancelado') !== false) {
                                if ($cancelar->CancelarFacturaSin()) {
                                    echo "La factura No " . $rs['Folio'] . " con el Folio Fiscal " . $rs['folioFiscal'] . " ya fue cancelada previamente en el SAT";
                                } else {
                                    echo "No se cancelo error en la base de datos, con el folio " . $rs['Folio'] . " con el Folio Fiscal " . $rs['folioFiscal'];
                                }
                            } else {
                                echo $soap_cancelacion["return"];
                            }
                        }
                    }
                } else {
                    echo "No se cuenta con los datos suficientes de la empresa del emisor para cancelar el No de Folio " . $rs['Folio'];
                }
            } else {
                $cancelar->setPendientePorcancelar("1");
                echo "El SAT refleja las facturas timbradas despúes de 72 horas, por favor intentelo más tarde";
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


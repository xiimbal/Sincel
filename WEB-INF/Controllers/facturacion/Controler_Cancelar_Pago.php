<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
//Importamos las librerias de NU-SOAP
require_once('../../Classes/nu_soap/nusoap.php');
include_once('../../Classes/Cancelar_Factura.class.php');
include_once('../../Classes/PAC.class.php');
include_once("../../Classes/Empresa.class.php");
include_once('../../Classes/Factura.class.php');
include_once("../../Classes/PagoParcial.class.php");

if(!isset($_POST['pago']) || $_POST['pago'] == ""){
    echo "Error: no se ha recibido el id del pago";
    exit;
}

$pagoParcial = new PagoParcial();
$pagoParcial->setId_pago($_POST['pago']);
$pagoParcial->getRegistrobyID(true);



$factura = new Factura_NET();
$factura->getRegistrobyID($pagoParcial->getId_factura());

                
                $empresa = new Empresa();
                $empresa->setRFC($factura->getRFCEmisor());
                $empresa->getRegistroByRFC($empresa->getRFC());


$pac = new PAC();
$pac->setId_pac($empresa->getId_pac());

if(!$pac->getRegistrobyID()){
        echo "Error: no se encontraron los datos del PAC";
        exit;
    }
    
    $soapclient = new nusoap_client($pac->getDireccion_cancelacion(), $esWSDL = true);
    
    $cancelar = new Cancelar_Factura();
    
    $certificado = "";
    $llave = "";
    $password = "";
    $uuid = $pagoParcial->getFolioFiscal();
    $bol = TRUE;
    if ($factura->getRFCEmisor() == "ODF0403226G7") {//ODF
                    $certificado = $cancelar->getCertificado_odf();
                    $llave = $cancelar->getLlave_odf();
                    $password = $cancelar->getLlave_privada_odf();
                } elseif ($factura->getRFCEmisor() == 'GSC080505189') {//GSC
                    $certificado = $cancelar->getCertificado_gsc();
                    $llave = $cancelar->getLlave_gsc();
                    $password = $cancelar->getLlave_privada_gsc();
                } elseif ($factura->getRFCEmisor() == 'AST1310011K2') {//AST
                    $certificado = $cancelar->getCertificado_ast();
                    $llave = $cancelar->getLlave_ast();
                    $password = $cancelar->getLlave_privada_ast();
                } elseif ($factura->getRFCEmisor() == 'SCG9606117F7') {//SCG
                    $certificado = $cancelar->getCertificado_scg();
                    $llave = $cancelar->getLlave_scg();
                    $password = $cancelar->getLlave_privada_scg();
                } elseif ($factura->getRFCEmisor() == 'CJS130516R33') {//CJS
                    $certificado = $cancelar->getCertificado_sic();
                    $llave = $cancelar->getLlave_sic();
                    $password = $cancelar->getLlave_privada_sic();
                } elseif( $factura->getRFCEmisor() == 'XII120823851'){//Ximbal
                    $certificado = $cancelar->getCertificado_xiimbal();
                    $llave = $cancelar->getLlave_xiimbal();
                    $password = $cancelar->getLlave_privada_ximbal();
                }elseif( $factura->getRFCEmisor() == 'AAEM650629IP7'){//Alarplus
                    $certificado = $cancelar->getCertificado_alarplus();
                    $llave = $cancelar->getLlave_alarplus();
                    $password = $cancelar->getLlave_privada_alarplus();
                }elseif( $factura->getRFCEmisor() == 'DIC131001487'){//Documento Integral
                    $certificado = $cancelar->getCertificado_dic();
                    $llave = $cancelar->getLlave_dic();
                    $password = $cancelar->getLlave_privada_dic();
                }elseif($factura->getRFCEmisor() == 'LARO870426LS7'){
                    $certificado = $cancelar->getCertificado_oscar();
                    $llave = $cancelar->getLlave_oscar();
                    $password = $cancelar->getLlave_privada_oscar();
                }elseif ($factura->getRFCEmisor() == 'AAA010101AAA') {
                    $certificado = $cancelar->getCertificado_prueba();
                    $llave = $cancelar->getLlave_prueba();
                    $password = $cancelar->getLlave_privada_prueba();
                } else {
                    $bol = FALSE;
                }
                
                //$llave = "MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCXVHLByBCIMIr7 wGp7Rp2FSDt/u8k3Q9bZCqKWy/rSko0RaNRKz3RicT94MU+TvgAiA0Fzfh0vbjFg 2J2lEWpUiBfQjhQnYfzh9j5/lJpDeHLpa9g2pys7VSDvInVzOnPWh5+YTqO4Q8Vz At7jrhldxUuDm5MU1fvTbLzgFdpTHtu0h60gdA4bi7q1G2cF5vv6+Hbc929ri4AX Pg4sveTxvjCLu6DSjPPWgTRnI0ycPvZPlsyYj3d2aUICHnWAQ/vEFsZWAoMYZ7Wt 5PcpYFymFwmJNJNKmrrwSdY0kVX+QY4yFGzrLWkKefZ5IB4eOXPYJ8CUVetfDVtH zHg87ikdAgMBAAECggEALS8Z1KJXzVIxLVoWcRh0kAcxPMJlIgsvaz6xrTTaf2Ui mcAjIvMuXPZTbR/MEuD4SS+Pq1xMeoz8UV5cM50vkm3QLoU9n0SyrQVJQ+6q4Npl 9SwuMqNXVS/l1YEEcJNTYwq7rE5OtAYIPn7s7i5dhJIUKgeZsu7xcf9VpdLgjVCD qGgJw/EfhagR7iPF+PKoeyRyBZI9xuHmtElHVgn2/Qv/16UJv0YpAqRgVq7YQzZC c7yo0Y2+3dqHabRg+MnIKkN4pBFBzYxsjwM7YUDk/8zFlF5kwCS74ep0JWWSYAJ1 3DYDtCYSyWk1DvxX9Srv/S2htZM6MnhboafjLch4QQKBgQDRGGLpYdqGt6/cXKQe JGWFrG33AMiYKrd4NOw7LK7kzrQESeaeAXSwr2eOnNV3tDMyslkjpC05m3Lbefsh Ul6Qj/Qj9PEIpv7e4X4r++O/FsA9X6iQFicMEDzRYYjm4AfFggYrhzmjXh2rNACL KRX5i9wIRGQuoAG7KuZyYWSBuwKBgQC5Rsv75S6FNUpKe8RC2nw13Vaf9uua2W1+ spg2pWfKqw88vvFATQOj9A9aFJ+wqrvwRziua5xtbch9gHK7M9Nnl565Tk8muueO OUBaFeHYXsDaYZfTFILOZU4/b6//r6QK2cO892VXyUydbRXavCpRX8s2EoxtwfFG mgbStX+HBwKBgQCICHKJXXU7QhPyrH7FcW5vKgAcu3DFtrzIQr4RvX9HMsdhJucX kuDk9ijMWnJyv1Szvd5KVsxpdx2hdlmQkzMcn9r47alGtMaKIG/ik6zWrCmDhFF4 9ECRE5tNqUPU2JmVwILdHMu94kQxFtLntmIqiPgslLoMr2KQ71cfwQcPcwKBgQCk iNKtqCFf+qs26iKonA6iZyV+eXFR2rT6RvAV114NBUxKzebBC6On/h2ECbymz3iH MTiM7NPF+jCKA3/f725WGLfEKF7yLhlknEMhvT0LQVpSlUiXEyf20tBiVXUew4QS fsDtF2bQRtvbEfzOezu5eDCmnGJJNmpmIHLevH+8EQKBgF9Ff09RISQJHbABka8f wj8sdBKWG3TUQ2SwQ9U3L/Y/unuyaRUF+J3wFRYBMQGu0jzLG5TFfAVZAc3VJCBj xG6K8WnJS6OM9ycV0qBa2WnkC7M7uAt4K9IEIqlOljY/R2tBN7qHZwE7nCLS88rv L5YWIiKp71SlXyoGLfM0h7bl";
                if($bol){ //echo $certificado."<br><br><br>".$llave."<br><br><br>".$password."<br><br><br>";
                    $sol = array('certificado' => $certificado, 'llavePrivada' => $llave, 'password' => $password, 'uuid' => $uuid);
                    $usuario = $pac->getUsuario();
                    $clave = $pac->getPassword();
                    $can = array('solicitud' => $sol, 'usuario' => $usuario, 'clave' => $clave);
                    
                    $soap_cancelacion = $soapclient->call('cancelacion', $can);
                    if ($soap_cancelacion == false) {
                        echo "No se logro contactar con el PAC, y no se pudo cancelar el No de Folio " . $pagoParcial->getFolio() . " con el Folio Fiscal " . $pagoParcial->getFolioFiscal();
                    }else{
                        if (strpos($soap_cancelacion["return"], 'CANCELADO CORRECTAMENTE') !== false) {
                            
                            if($pagoParcial->CancelarPago()){
                                echo "Se ha cancelado correctamente el pago con número de folio: ".$pagoParcial->getFolio();
                            }else{
                                echo "Error: no se ha podido cancelar el pago con número de folio: ".$pagoParcial->getFolio();
                            }
                        } else {
                            if (strpos($soap_cancelacion["return"], 'previamente cancelado') !== false) {
                                echo "El pago ya fue cancelado";
                            } else {
                                echo "Error al cancelar: ".$soap_cancelacion["return"];
                            }
                        }
                    }
                    
                }else {
                    echo "No se cuenta con los datos suficientes de la empresa del emisor (certificado y llave) para cancelar el No de Folio ".$pagoParcial->getFolio();
                }
    
 


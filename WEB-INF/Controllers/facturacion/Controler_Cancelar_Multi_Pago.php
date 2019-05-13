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
include_once("../../Classes/MultiPagosParciales.class.php");


if(!isset($_POST['pago']) || $_POST['pago'] == ""){
    echo "Error: no se ha recibido el id del pago";
    exit;
}

//$pagoParcial = new PagoParcial();
$pagoParcial = new MultiPagosParciales();
$pagoParcial->setId_pago($_POST['pago']);
$pagoParcial->getRegistrobyID(true);

$idsFac = $_POST['factura'];
$arr_fac = explode("_", $idsFac);
$tam = sizeof($arr_fac);
$tam= $tam-1;
$factura = new Factura_NET();

//for ($i=0; $i < $tam ; $i++) { 
    //$factura->getRegistrobyID($pagoParcial->getId_factura());
    $factura->getRegistrobyID($arr_fac[0]);
            
        $empresa = new Empresa();
        $empresa->setRFC($factura->getRFCEmisor());
        echo "Empresa RFC ". $empresa->getRFC();
        $empresa->getRegistroByRFC($empresa->getRFC());

    $pac = new PAC();
    $pac->setId_pac($empresa->getId_pac());
    echo "PAC ". $pac->getId_pac();

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
    echo "UUID ". $uuid;
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
                /*echo "Certificado ".$certificado;
                echo "LLave ".$llave;
                echo "PSS ".$password;*/

                if($bol){ //echo $certificado."<br><br><br>".$llave."<br><br><br>".$password."<br><br><br>";
                    $sol = array('certificado' => $certificado, 'llavePrivada' => $llave, 'password' => $password, 'uuid' => $uuid);
                    $usuario = $pac->getUsuario();
                    echo "USER ". $usuario;
                    $clave = $pac->getPassword();
                    echo "CLAVE ". $clave;
                    $can = array('solicitud' => $sol, 'usuario' => $usuario, 'clave' => $clave);
                    
                    $soap_cancelacion = $soapclient->call('cancelacion', $can);
                    //var_dump($soap_cancelacion);
                    if ($soap_cancelacion == false) {
                        echo "No se logro contactar con el PAC, y no se pudo cancelar el No de Folio " . $pagoParcial->getFolio() . " con el Folio Fiscal " . $pagoParcial->getFolioFiscal();
                    }else{
                        if (strpos($soap_cancelacion["return"], 'CANCELADO CORRECTAMENTE') !== false) {
                            //echo "CAncelado Correctamente";
                            if($pagoParcial->CancelarPago()){//Se inserta el pago en c_multipagosparcialescancelados
                                //echo "Se pudo insertar en c_multipagosparcialescancelados";
                                for ($i=0; $i < $tam; $i++) { 
                                  //$pagoParcial->setId_factura($arr_fac[$i]);
                                  $pagoParcial->getFacturasbyID($arr_fac[$i],$_POST['pago']);//se asignan los valores a las variables
                                  if ($pagoParcial->CancelarPagoFac($_POST['pago'])) {//se insertan en c_multipagoscanceladosdetalle, y se eliminan de la tabla c_multipagosdetalle y actualiza la factura 
                                      //echo "listo.";
                                  }else{
                                    //echo "no listo";
                                  }
                                }
                                if ($pagoParcial->CancelarPago2($_POST['pago'])) {//se borra de la tabla c_multipagosparciales
                                  echo "Se ha cancelado correctamente el pago con número de folio: ".$pagoParcial->getFolio();
                                }
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

//}


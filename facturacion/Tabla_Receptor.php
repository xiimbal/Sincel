<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Empresa.class.php");
include_once("../WEB-INF/Classes/Contrato.class.php");
include_once("../WEB-INF/Classes/RegimenFiscal.class.php");
$cliente = new Cliente();
$contrato = new Contrato();
$regimenFiscal = new RegimenFiscal();
if (isset($_POST['id']) && $_POST['id'] != "") {
    $cliente->getRegistroById($_POST['id']);
    $result = $contrato->getRegistroValidacionVencidos($cliente->getClaveCliente());
    
    if(mysql_num_rows($result) > 0){        
        while($rs = mysql_fetch_array($result)){
            if (!empty($rs['NumeroCuenta']) || !empty($rs['IdMetodoPago']) || !empty($rs['FormaPago']) || !empty($rs['IdUsoCFDI'])){
                $numero_cuenta = $rs['NumeroCuenta'];
                $metodoPago = $rs['IdMetodoPago'];
                $formaPago = $rs['FormaPago'];
                $usoCFDI = $rs['IdUsoCFDI'];
                break;
            }            
        }
    }else{
        $numero_cuenta = "";
        $metodoPago = "";
        $formaPago = "";
        $usoCFDI = "";
    }
    
    $empresa = new Empresa();
    $empresa->setId($cliente->getIdDatosFacturacionEmpresa());
    $empresa->getRegistrobyID();
    $regimenFiscal->getRegistroById($empresa->getRegimenFiscal());    
    echo $empresa->getId() . "||#||" . $empresa->getRFC() . "||#||" . $empresa->getRazonSocial() . "||#||" . $empresa->getRegimenFiscal() . " " . 
            $regimenFiscal->getDescripcion() . "||#||" .$empresa->getPais()."," .$empresa->getEstado(). "||#||" .$numero_cuenta . "||#||" .
            $empresa->getIdSerie() . "||#||" .$metodoPago . "||#||" .$formaPago . "||#||" .$usoCFDI;
}
?>
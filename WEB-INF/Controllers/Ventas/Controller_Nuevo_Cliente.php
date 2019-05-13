<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/ccliente.class.php");
include_once("../../Classes/Cliente.class.php");
//include_once("../../Classes/Validaciones_extras.class.php");
if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
    $cliente = new ccliente();
    $cliente->setEstatusCobranza($parametros['EstatusCobranza']);
    $cliente->setRazonSocial($parametros['RazonSocial']);
    $cliente->setTipoCliente($parametros['TipoCliente']);
    $cliente->setGiro($parametros["Giro"]);
    $cliente->setTipoDomicilio("3");
    $cliente->setModalidad($parametros['modalidad2']);
    $cliente->setIdDatosFacturacionEmpresa($parametros['razon_cliente2']);
    $cliente->setCalleF($parametros['CalleF']);
    $cliente->setNoExtF($parametros['NoExteriorF']);
    $cliente->setNoIntF($parametros['NoInteriorF']);
    $cliente->setColoniaF($parametros['ColoniaF']);
    $cliente->setCiudadF($parametros['CiudadF']);
    $cliente->setEstadoF($parametros['EstadoF']);
    $cliente->setDelegacionF($parametros['DelegacionF']);
    $cliente->setCPF($parametros['CPF']);
    $cliente->setRFCD($parametros['RFCD']);
    $cliente->setFechaCreacion("NOW()");
    $cliente->setFechaMoficacion("NOW()");
    $cliente->setUsuarioCreacion($_SESSION['user']);
    $cliente->setUsuarioModificacion($_SESSION['user']);
    $cliente->setPantalla("PHP Controller_Nuevo_Cliente.php");
    $cliente->setEjecutivoCuenta($parametros['ejecutivocuenta']);
    $cliente->setEjecutivoAtencionCliente($parametros['ejecutivoatencion']);
    $cliente->setClaveZona($parametros['zona']);
    $cliente->setClaveGrupo($parametros['grupo']);
    $cliente->setLocalidad($parametros['LocalidadF']);
    $cliente->setLatitud($parametros['latitud']);
    $cliente->setLongitud($parametros['longitud']);
    $cliente->setCalificacion($parametros['calificacion']);
    $cliente->setComentario($parametros['comentario']);
    $cliente->setImagen($parametros['imagen_url']);
    $cliente->setIdAddenda($parametros['addenda_cliente']);
    $cliente->setIdCuentaBancaria($parametros['cuentaBancaria']);
    $cliente->setReferenciaNumerica($parametros['referenciaNum']);
    
    if (isset($parametros['chkActivo']) && $parametros['chkActivo'] != "") {
        $cliente->setActivo("1");
    } else {
        $cliente->setActivo("0");
    }
    
    if (isset($parametros['verPDF']) && $parametros['verPDF'] != "") {
        $cliente->setVerCClientePDF("1");
    } else {
        $cliente->setVerCClientePDF("0");
    }
    
    if (isset($parametros['condiciones_pago']) && $parametros['condiciones_pago'] != "") {
        $cliente->setMostarCondicionesPago("1");
    } else {
        $cliente->setMostarCondicionesPago("0");
    }
    
    if (isset($parametros['mostrar_pdf']) && $parametros['mostrar_pdf'] != "") {
        $cliente->setMostrarAddenda("1");
    } else {
        $cliente->setMostrarAddenda("0");
    }
    
    if (isset($parametros['CorreoE1D']) && $parametros['CorreoE1D'] != "") {
        $cliente->setCorreoE1D($parametros['CorreoE1D']);
    }
    if (isset($parametros['CorreoE2D']) && $parametros['CorreoE2D'] != "") {
        $cliente->setCorreoE2D($parametros['CorreoE2D']);
    }
    if (isset($parametros['CorreoE3D']) && $parametros['CorreoE3D'] != "") {
        $cliente->setCorreoE3D($parametros['CorreoE3D']);
    }
    if (isset($parametros['CorreoE4D']) && $parametros['CorreoE4D'] != "") {
        $cliente->setCorreoE4D($parametros['CorreoE4D']);
    }
    
    $cliente->setTelefono($parametros['telefono']);
    $cliente->setEmail($parametros['correo']);
    $cliente->setSitioweb($parametros['sitio_web']);
    $cliente->setHorario($parametros['horario']);
    $cliente->setFacebook($parametros['facebook']);
    $cliente->setTwitter($parametros['twitter']);    
    
    $subcategorias = array();
    $numer_categorias = $parametros['numero_categoria'];
    for($i=1; $i<=$numer_categorias; $i++){        
        array_push($subcategorias, $parametros['categoria'.$i]);
    }
    $cliente->setCategorias($subcategorias);
    
    if (isset($parametros['id']) && $parametros['id'] != "") {//actualizar
        $cliente_aux = new Cliente();
        if($cliente_aux->getRegistroByRFC($cliente->getRFCD()) && $cliente_aux->getClaveCliente() != $parametros['id']){
            echo "Error: el rfc <b>".$cliente->getRFCD()."</b> ya se encuentra registrado en el sistema";
        }else{
            $cliente->setIdCliente($parametros['id']);
            $cliente->setIdDomicilio($parametros['domicilioid']);
            echo $cliente->update();
        }
    } else {//insertar un nuevo registro       
        $cliente_aux = new Cliente();
        if(!$cliente_aux->getRegistroByRFC($cliente->getRFCD())){
            echo $cliente->nuevoRegistro();        
        }else{
            echo "Error: el rfc <b>".$cliente->getRFCD()."</b> ya se encuentra registrado en el sistema";
        }        
    }
}
?>

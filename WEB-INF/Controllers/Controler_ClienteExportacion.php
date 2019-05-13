<?php

if(isset($_POST['CCliente']) || isset($_GET['CCliente'])){
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../../index.php");
    }
    include_once("../Classes/ClientesExportacion.class.php");
    include_once("../Classes/Mail.class.php");
    include_once("../Classes/Catalogo.class.php");
    include_once("../Classes/Usuario.class.php");
    include_once("../Classes/Cliente.class.php");
    include_once("../Classes/Factura.class.php");
    include_once("../Classes/CentroCosto.class.php");
    include_once("../Classes/Parametros.class.php");
    include_once("../Classes/ParametroGlobal.class.php");
    include_once("../Classes/ParametroGlobal.class.php");
    $usuario = $_SESSION['user'];
    $empresa = $_SESSION['idEmpresa'];
}else{
    include_once("WEB-INF/Classes/ClientesExportacion.class.php");
    include_once("WEB-INF/Classes/Mail.class.php");
    include_once("WEB-INF/Classes/Catalogo.class.php");
    include_once("WEB-INF/Classes/Usuario.class.php");
    include_once("WEB-INF/Classes/Cliente.class.php");
    include_once("WEB-INF/Classes/Factura.class.php");
    include_once("WEB-INF/Classes/CentroCosto.class.php");
    include_once("WEB-INF/Classes/Parametros.class.php");
    include_once("WEB-INF/Classes/ParametroGlobal.class.php");
    include_once("WEB-INF/Classes/ParametroGlobal.class.php");
    $usuario = "CRON PHP";
    if(isset($_GET['uguid'])){
        $empresa = $_GET['uguid'];
    }else{
        echo "La liga no está completa, favor de reportarla a sistemas";
        return;
    }
}

$parametroGlobal = new ParametroGlobal();
$parametroGlobal->setEmpresa($empresa);
$catalogo = new Catalogo();
$catalogo->setEmpresa($empresa);
$mail = new Mail();
$mail->setEmpresa($empresa);
$parametros = new Parametros();
$parametros->setEmpresa($empresa);
$obj = new ClientesExportacion();
$obj->setEmpresa($empresa);
$cliente = new Cliente();
$cliente->setEmpresa($empresa);

date_default_timezone_get();
$anio = date('Y');
$mes = date('m');
$dia = date('d');
$fecha = $dia . "-" . $mes . "-" . $anio;

if (isset($_POST['claveCliente'])) {
    $clave = $_POST['claveCliente'];
    $tipo = $_POST['tipo'];
    $obj->setPonerMoroso($tipo);
    $obj->setUsuarioCreacion($usuario);
    $obj->setUsuarioModificacion($usuario);
    $obj->setPantalla("No volver a poner como moroso");    
    if ($obj->NoVolverAPonerComoMoroso($clave)) {
        if ($obj->GetNombreClienteCambiado($clave))
            echo "El cliente <b>" . $obj->getNombreCliente() . "</b> se modificó correctamente.<br/>";
    } else {
        if ($obj->GetNombreClienteCambiado($clave))
            echo "El cliente <b>" . $obj->getNombreCliente() . "</b> no se modificó.<br/>";
    }    
}else if ((isset($_POST['CCliente']) && $_POST['CCliente'] == "CCliente") || (!isset($_POST['CCliente']))) {
    //Como se corre por cron, ya puede entrar de cualquier forma
    //obtener datos de los clientes 
    $obj->ObtenerClienteEquipos();
    $obj->ObtenerClientesfacturacion();
    $obj->ObtenerClientesMorosos();
    //clieentes de las bases
    $clientesFacturacion = $obj->getClientesFacturacion();
    $clientesEquipo = $obj->getClientesEquipos();
    $clientesMorosos = $obj->getClientesMorosos();
    $folioMoroso = $obj->getSerieMoroso();
    $folioFacturacion = $obj->getFolio();
    $fechaFacturacion = $obj->getFechaFacturada();
    $xml = $obj->getXml();
    /*foreach ($folioMoroso as $key => $value) {
        echo "<br/>$key: $value";
    }*/
    
    //datos de losclientes normales facturacion
    $clientesFacturacionNombre = $obj->getNombreFacturacion();
    $clientesFacturacionRFCEmisor = $obj->getRFCEmisor();
    //datos de los clientes morosos 

    $clientesFacturacionMorosos = array();
    $clientesFacturacionNormales = array();
    $nombreMorosos = array();
    $rfcEmisorMoroso = array();
    $nombreNormal = array();
    $rfcEmisorNormal = array();
    $folioClienteMoroso = array();
    $folioClienteNormal = array();
    $fechaClienteMoroso = array();
    $folioFacturaMorosa = array();
    $xmlMoroso = array();
    $xmlNormal = array();
    $fechaClienteNormal = array();
    $contadorTipoCliente = 0;
    $contadorTipoClienteMoroso = 0;
    $contadorTipoClienteNormal = 0;
    $message_newclientes = "";
    
    while ($contadorTipoCliente < count($clientesFacturacion)) {
        if (in_array($clientesFacturacion[$contadorTipoCliente], $clientesMorosos)) {//cliente morosos
            $clientesFacturacionMorosos[$contadorTipoClienteMoroso] = $clientesFacturacion[$contadorTipoCliente];
            $nombreMorosos[$contadorTipoClienteMoroso] = $clientesFacturacionNombre[$contadorTipoCliente];
            $rfcEmisorMoroso[$contadorTipoClienteMoroso] = $clientesFacturacionRFCEmisor[$contadorTipoCliente];
            $folioClienteMoroso[$contadorTipoClienteMoroso] = $folioFacturacion[$contadorTipoCliente];
            $fechaClienteMoroso[$contadorTipoClienteMoroso] = $fechaFacturacion[$contadorTipoCliente];            
            $xmlMoroso[$contadorTipoClienteMoroso] = $xml[$contadorTipoCliente];
            //echo "<br/>Buscando ".$clientesFacturacion[$contadorTipoCliente];
            $index_moroso = array_search($clientesFacturacion[$contadorTipoCliente], $clientesMorosos);
            if($index_moroso >= 0 || $index_moroso!=false){
                $folioFacturaMorosa[$contadorTipoClienteMoroso] = $folioMoroso[$index_moroso];
            }else{
                $folioFacturaMorosa[$contadorTipoClienteMoroso] = "";
            }                            
            //echo "<br/>Procesando index $index_moroso ".$folioFacturaMorosa[$contadorTipoClienteMoroso]." con ".$clientesFacturacion[$contadorTipoCliente];
            $contadorTipoClienteMoroso++;
        } else {//clientes no morosos
            $clientesFacturacionNormales[$contadorTipoClienteNormal] = $clientesFacturacion[$contadorTipoCliente];
            $nombreNormal[$contadorTipoClienteNormal] = $clientesFacturacionNombre[$contadorTipoCliente];
            $rfcEmisorNormal[$contadorTipoClienteNormal] = $clientesFacturacionRFCEmisor[$contadorTipoCliente];
            $folioClienteNormal[$contadorTipoClienteNormal] = $folioFacturacion[$contadorTipoCliente];
            $fechaClienteNormal[$contadorTipoClienteNormal] = $fechaFacturacion[$contadorTipoCliente];
            $xmlNormal[$contadorTipoClienteNormal] = $xml[$contadorTipoCliente];
            $contadorTipoClienteNormal++;
        }
        $contadorTipoCliente++;
    }
    // echo count($rfcEmisorMoroso) . "  " . count($folioClienteMoroso) . "<br/>";
    // echo "Total " . $contadorTipoCliente;
    //separacion de clientes morosos y normales
//    echo "facturacion morosos existentes" . count($clientesFacturacionMorosos) . "---" . count($nombreMorosos) . "---" . count($rfcEmisorMoroso) . "<br/>";
//    echo "facturacion morosos no existenet" . count($clientesFacturacionNormales) . "--" . count($nombreNormal) . "" . count($rfcEmisorNormal) . "<br/>";
    //verificar si los normales existen en equipo
    $contdorComprobacionNormal = 0;
    $contdorComprobacionNormal1 = 0;
    $contdorComprobacionNormal2 = 0;
    $nombreClientesNormalesExistentes = array();
    $RFCClientesNormalesExistentes = array();
    $nombreClientesNormalesNOExistentes = array();
    $RFCClientesNormalesNOExistentes = array();
    $clientesExitentesNormales = array();
    $clientesNoExitentesNormales = array();
    $xmlNoExistentesNormales = array();
    $xmlExistentesNormales = array();
    while ($contdorComprobacionNormal < count($clientesFacturacionNormales)) {
        if (in_array($clientesFacturacionNormales[$contdorComprobacionNormal], $clientesEquipo)) {//existe el cliente en equipos
            $clientesExitentesNormales[$contdorComprobacionNormal1] = $clientesFacturacionNormales[$contdorComprobacionNormal];
            $nombreClientesNormalesExistentes[$contdorComprobacionNormal1] = $nombreNormal[$contdorComprobacionNormal];
            $RFCClientesNormalesExistentes[$contdorComprobacionNormal1] = $rfcEmisorNormal[$contdorComprobacionNormal];
            $xmlExistentesNormales[$contdorComprobacionNormal1] = $xmlNormal[$contdorComprobacionNormal];
            $contdorComprobacionNormal1++;
        } else {
            $clientesNoExitentesNormales[$contdorComprobacionNormal2] = $clientesFacturacionNormales[$contdorComprobacionNormal];
            $nombreClientesNormalesNOExistentes[$contdorComprobacionNormal2] = $nombreNormal[$contdorComprobacionNormal];
            $RFCClientesNormalesNOExistentes[$contdorComprobacionNormal2] = $rfcEmisorNormal[$contdorComprobacionNormal];
            $xmlNoExistentesNormales[$contdorComprobacionNormal2] = $xmlNormal[$contdorComprobacionNormal];
            $contdorComprobacionNormal2++;
        }
        $contdorComprobacionNormal++;
    }
//    echo "Clientes normales existentes en equipo" . count($clientesExitentesNormales) . "--" . count($nombreClientesNormalesExistentes) . "--" . count($RFCClientesNormalesExistentes) . "<br/>";
//    echo "Clientes normales NO existentes en equipo" . count($clientesNoExitentesNormales) . "--" . count($nombreClientesNormalesNOExistentes) . "--" . count($RFCClientesNormalesNOExistentes) . "<br/>"; //equipos para insertar

    $contdorComprobacionMorosos = 0;
    $contdorComprobacionMorosos1 = 0;
    $contdorComprobacionMorosos2 = 0;
    $nombreMorosoExistenteEquipo = array();
    $RfcMorosoExistenteEquipo = array();
    $nombreMorosoNOExistenteEquipo = array();
    $RfcMorosoNOExistenteEquipo = array();
    $clientesExitentesMorosos = array();
    $clientesNoExitentesMorosos = array();
    $folioClientesExistentesMoroso = array();
    $folioClientesExistentesNormales = array();
    $fechaClientesExistentesMoroso = array();
    $fechaClientesExistentesNormales = array();
    $xmlMorososNoExistentes = array();
    $xmlMorososExistentes = array();
    $folioExistenteMoroso = array();

    //print_r($clientesEquipo);
    //echo $clientesEquipo[0] . "----" . $clientesFacturacionMorosos[0] . "<br/>";
    while ($contdorComprobacionMorosos < count($clientesFacturacionMorosos)) {
        if (in_array($clientesFacturacionMorosos[$contdorComprobacionMorosos], $clientesEquipo)) {
            $clientesExitentesMorosos[$contdorComprobacionMorosos1] = $clientesFacturacionMorosos[$contdorComprobacionMorosos];
            $nombreMorosoExistenteEquipo[$contdorComprobacionMorosos1] = $nombreMorosos[$contdorComprobacionMorosos];
            $RfcMorosoExistenteEquipo[$contdorComprobacionMorosos1] = $rfcEmisorMoroso[$contdorComprobacionMorosos];
            $folioClientesExistentesMoroso[$contdorComprobacionMorosos1] = $folioClienteMoroso[$contdorComprobacionMorosos];
            $fechaClientesExistentesMoroso[$contdorComprobacionMorosos1] = $fechaClienteMoroso[$contdorComprobacionMorosos];            
            $xmlMorososExistentes[$contdorComprobacionMorosos1] = $xmlMoroso[$contdorComprobacionMorosos];
            $folioExistenteMoroso[$contdorComprobacionMorosos1] = $folioFacturaMorosa[$contdorComprobacionMorosos];
            //echo "Agregando ".$clientesExitentesMorosos[$contdorComprobacionMorosos1]." con ".$folioExistenteMoroso[$contdorComprobacionMorosos1];
            $contdorComprobacionMorosos1++;
        } else {
            if(empty($xmlMorososNoExistentes[$contdorComprobacionMorosos1])){
                continue;
            }
            $clientesNoExitentesMorosos[$contdorComprobacionMorosos2] = $clientesFacturacionMorosos[$contdorComprobacionMorosos];
            $nombreMorosoNOExistenteEquipo[$contdorComprobacionMorosos2] = $nombreMorosos[$contdorComprobacionMorosos];
            $RfcMorosoNOExistenteEquipo[$contdorComprobacionMorosos2] = $rfcEmisorMoroso[$contdorComprobacionMorosos];
            $folioClientesExistentesNormales[$contdorComprobacionMorosos2] = $folioClienteMoroso[$contdorComprobacionMorosos];
            $fechaClientesExistentesNormales[$contdorComprobacionMorosos2] = $fechaClienteMoroso[$contdorComprobacionMorosos];
            $xmlMorososNoExistentes[$contdorComprobacionMorosos2] = $xmlMoroso[$contdorComprobacionMorosos];
            
            $contdorComprobacionMorosos2++;
        }
        $contdorComprobacionMorosos++;
    }
    if (!$clientesNoExitentesMorosos == null) {//insertar clientes morosos no existentes
        $contadorInsert = 0;
        $numeroIsertados = 0;
        $calle = "";
        $colonia = "";
        $estado = "";
        $nExterio = "0";
        $nInterior = "0";
        $municipio = "";
        $pais = "";
        $cp = "";
        $message_newclientes .= "Se encontraron los siguientes nuevos clientes <b>Morosos</b> en facturación que no se encontraron en Operación:";
        while ($contadorInsert < count($clientesNoExitentesMorosos)) {
            echo "<br/> $contadorInsert: ".$xmlMorososNoExistentes[$contadorInsert];
            $xmlCliente = new SimpleXMLElement($xmlMorososNoExistentes[$contadorInsert]); //se lee el string como xml
            $calle = $xmlCliente->Receptor->Domicilio["calle"]; //se asigna el valor de la calle 
            $nExterio = $xmlCliente->Receptor->Domicilio["noExterior"];
            $nInterior = $xmlCliente->Receptor->Domicilio["noInterior"];
            $colonia = $xmlCliente->Receptor->Domicilio["colonia"];
            $municipio = $xmlCliente->Receptor->Domicilio["municipio"];
            $estado = $xmlCliente->Receptor->Domicilio["estado"];
            $pais = $xmlCliente->Receptor->Domicilio["pais"];
            $cp = $xmlCliente->Receptor->Domicilio["codigoPostal"];
            $cliente->setIdEstatusCobranza(2);
            $obj->datosFacturacion($RfcMorosoNOExistenteEquipo[$contadorInsert]);
            $cliente->setIdDatosFacturacionEmpresa($obj->getIdDatosFacturacionEmpresa());
            $cliente->setNombreRazonSocial($nombreMorosoNOExistenteEquipo[$contadorInsert]);
            $cliente->setRFC($clientesNoExitentesMorosos[$contadorInsert]);
            $cliente->setActivo(1);
            $cliente->setUsuarioCreacion($usuario);
            $cliente->setUsuarioUltimaModificacion($usuario);
            $cliente->setPantalla("Exportación de clientes morosos");

            $cliente->setCalle($calle);
            $cliente->setIdTipoDomicilio(3);
            $cliente->setNoExterior($nExterio);
            $cliente->setNoInterior($nInterior);
            $cliente->setColonia($colonia);
            $cliente->setCiudad($estado); //$pais
            $cliente->setEstado($pais);
            $cliente->setDelegacion($municipio);
            $cliente->setPais($pais);
            $cliente->setCp($cp);
            /*Obtenemos la zona que se pone por default segun los parametros globales*/
            $parametro = new ParametroGlobal();
            $parametro->setEmpresa($empresa);
            if($parametro->getRegistroById("2")){
                $zona = $parametro->getValor();
            }else{
                $zona = "Z06";
            }
            $cliente->setClaveZona($zona);
            
            $message_newclientes .= "<br/>".$cliente->getNombreRazonSocial()." con el RFC ".$cliente->getRFC();
            $numeroIsertados++;
            $contadorInsert++;
        }
        echo "Se encontraron <b>" . $numeroIsertados . "</b> nuevo(s) cliente(s) moroso(s)<br/>";
    }
    else{
        echo "No se encontraron clientes morosos<br/>";
    }
    
    if (!$clientesNoExitentesNormales == null) {//Insertar clientes normales
        $contadorInsert = 0;
        $numeroIsertados1 = 0;
        $calle = "";
        $colonia = "";
        $estado = "";
        $nExterio = "0";
        $nInterior = "0";
        $municipio = "";
        $pais = "";
        $cp = "";
        $message_newclientes .= "<br/><br/>Se encontraron los siguientes nuevos clientes en facturación que no se encontraron en Operación:";
        while ($contadorInsert < count($clientesNoExitentesNormales)) {
            $xmlCliente = new SimpleXMLElement($xmlNoExistentesNormales[$contadorInsert]);
            $calle = $xmlCliente->Receptor->Domicilio["calle"];
            $nExterio = $xmlCliente->Receptor->Domicilio["noExterior"];
            $nInterior = $xmlCliente->Receptor->Domicilio["noInterior"];
            $colonia = $xmlCliente->Receptor->Domicilio["colonia"];
            $municipio = $xmlCliente->Receptor->Domicilio["municipio"];
            $estado = $xmlCliente->Receptor->Domicilio["estado"];
            $pais = $xmlCliente->Receptor->Domicilio["pais"];
            $cp = $xmlCliente->Receptor->Domicilio["codigoPostal"];
            $cliente->setIdEstatusCobranza(1);
            $obj->datosFacturacion($RFCClientesNormalesNOExistentes[$contadorInsert]);
            $cliente->setIdDatosFacturacionEmpresa($obj->getIdDatosFacturacionEmpresa());
            $cliente->setNombreRazonSocial($nombreClientesNormalesNOExistentes[$contadorInsert]);
            $cliente->setRFC($clientesNoExitentesNormales[$contadorInsert]);
            $cliente->setActivo(1);
            $cliente->setUsuarioCreacion($usuario);
            $cliente->setUsuarioUltimaModificacion($usuario);
            $cliente->setPantalla("Exportación de clientes normales");
            $cliente->setCalle($calle);
            $cliente->setIdTipoDomicilio(3);
            $cliente->setNoExterior($nExterio);
            $cliente->setNoInterior($nInterior);
            $cliente->setColonia($colonia);
            $cliente->setCiudad($estado); //$pais
            $cliente->setEstado($pais);
            $cliente->setDelegacion($municipio);
            $cliente->setPais($pais);
            $cliente->setCp($cp);
            /*Obtenemos la zona que se pone por default segun los parametros globales*/
            $parametro = new ParametroGlobal();
            $parametro->setEmpresa($empresa);
            if($parametro->getRegistroById("2")){
                $zona = $parametro->getValor();
            }else{
                $zona = "Z06";
            }
            $cliente->setClaveZona($zona);
            
            $message_newclientes .= "<br/>".$cliente->getNombreRazonSocial()." con el RFC ".$cliente->getRFC();
            $contadorInsert++;
            $numeroIsertados1++;
        }
        echo "Se encontraron <b>" . $numeroIsertados1 . "</b> cliente(s) normale(s)<br/>";
    }
    else{
        echo "No se encontraron clientes normales <br/>";    
    }
    
    if($parametroGlobal->getRegistroById("8")){
        $mail->setFrom($parametroGlobal->getValor());
    }else{
        $mail->setFrom("scg-salida@scgenesis.mx");
    }
    $mail->setSubject("Nuevos clientes de facturación");
    $mail->setBody($message_newclientes);
    
    if($message_newclientes != ""){
        $result = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE TipoSolicitud = 4;");
        while($rs = mysql_fetch_array($result)){
            if(isset($rs['correo']) && $rs['correo']!="" && filter_var($rs['correo'], FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                $mail->setTo($rs['correo']);
                $mail->enviarMail();        
            }
        }
    }
    
    $obj->ClientesNoMarcados();
    $clientesParaEditar = $obj->getClienteNoMarcados();
    $cont = 0;
    $regitroEdit = array();
    $regitroEditNombre = array();
    $regitroEditRFC = array();
    $regitroEditFolio = array();
    $regitroEditFecha = array();
    $contb = 0;
    while ($cont < count($clientesExitentesMorosos)) {
        if (in_array($clientesExitentesMorosos[$cont], $clientesParaEditar)) {
            $regitroEdit[$contb] = $clientesExitentesMorosos[$cont];
            $regitroEditNombre[$contb] = $nombreMorosoExistenteEquipo[$cont];
            $regitroEditRFC[$contb] = $RfcMorosoExistenteEquipo[$cont];            
            $regitroEditFolio[$contb] = $folioClientesExistentesMoroso[$cont];
            $regitroEditFecha[$contb] = $fechaClientesExistentesMoroso[$cont];
            $registroFolioMoroso[$contb] = $folioExistenteMoroso[$cont];
            $contb++;
        }
        $cont++;
    }
    
    if ($regitroEdit != null) {//modificar clientes existentes
        $contadorEdit = 0;
        $numeroEditados = 0;
        $noCambiarPorPREF = array();
        //$factura = new Factura_NET();
        while ($contadorEdit < count($regitroEdit)) {
            //$factura->getRegistroByFolio($registroFolioMoroso[$contadorEdit]);
            //if($factura->getSerie()!=null || $factura->getSerie()==""){//Si no es pre-factura
            $obj->setIdEstatusCobranza(2);
            $obj->setUsuarioModificacion($usuario);
            $obj->setPantalla("Editar clientes morosos existentes");
            if ($obj->editCliente($regitroEdit[$contadorEdit])) {
                $numeroEditados++;
            }else{
                echo "El cliente <b>" . $regitroEditNombre[$contadorInsert] . "</b> no se modificó  <br/>";
            }
            $noCambiarPorPREF[$contadorEdit] = false;
            /*}else{
                $noCambiarPorPREF[$contadorEdit] = true;
            }*/
            $contadorEdit++;
        }

        $str = "'" . implode("','", $regitroEdit) . "'";        
        $correos = array();
        
        $tipoServidor = "";
        $query0 = $catalogo->obtenerLista("SELECT Valor FROM c_parametro p WHERE p.IdParametro=9");
        while ($rs = mysql_fetch_array($query0)) {
            $tipoServidor = $rs["Valor"];
        }
        
        if ($tipoServidor == "0") {//correos para pruebas
            $z = 0;
            $query7 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud=3;");
            while ($rs = mysql_fetch_array($query7)) {//correo de correos de solicitud
                if(isset($rs['correo']) && $rs['correo']!=""){
                    $correos[$z] = $rs['correo'];
                    $z++;
                }
            }            
        } else if ($tipoServidor == "1") {
            $query5 = $catalogo->obtenerLista("SELECT c.ClaveCliente,c.NombreRazonSocial,u.IdUsuario,CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS ejecutivo,u.correo,c.EjecutivoCuenta FROM c_cliente c,c_usuario u WHERE c.EjecutivoCuenta=u.IdUsuario AND !ISNULL(c.EjecutivoCuenta) AND c.EjecutivoCuenta<>'' AND c.RFC IN ($str) GROUP BY c.EjecutivoCuenta");
            $z = 0;
            while ($rs = mysql_fetch_array($query5)) {//correos de los ejecutivos de cuentas 
                if(isset($rs['correo']) && $rs['correo']!=""){
                    $correos[$z++] = $rs['correo'];                    
                }
            }            
            $query6 = $catalogo->obtenerLista("SELECT u.IdUsuario,CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS nombre,u.correo FROM c_usuario u WHERE u.IdPuesto=15 AND u.Activo=1 AND !ISNULL(u.correo)  AND u.correo<>'' ");
            while ($rs = mysql_fetch_array($query6)) {//correo de cuentas por cobrar
                if(isset($rs['correo']) && $rs['correo']!=""){
                    $correos[$z++] = $rs['correo'];                    
                }
            }
            $query7 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud=3;");
            while ($rs = mysql_fetch_array($query7)) {//correo de correos de solicitud
                if(isset($rs['correo']) && $rs['correo']!=""){
                    $correos[$z++] = $rs['correo'];                    
                }
            }
        }


        $cont = 0;        
        $message = "<html><b>Lista de clientes con cambio de estatus a moroso de " . $fecha . ".</b><br/><br/><table border='1'>";
        $message.="<tr><th>Folio factura</th><th>Cliente</th><th>Ejecutivo</th><th>Marcar como suspendido</th></tr>";
        $contador00 = 0;

        while ($cont < count($registroFolioMoroso)) {
            if(!$noCambiarPorPREF[$cont]){
                $clave = $mail->generaPass();
                $parametros->getRegistroById("8");
                $liga = $parametros->getDescripcion()."/cambiaSuspendido.php?RFC=".$regitroEdit[$cont]."&clv=$clave&uguid=$empresa";
                $id = $catalogo->insertarRegistro("INSERT INTO c_mailgeneral(id_mail, contestada, clave, liga, UsuarioCreacion, FechaCreacion, 
                    UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
                    VALUES(0, 0, MD5('$clave'), '$liga', '$usuario', NOW(), '$usuario', NOW(), 'Controler_ClienteExportacion.PHP');");
                $liga .= "&esp=$id";
                $obj->EjecutivoCliente($regitroEdit[$cont]);
                $message.="<tr><td>" . $registroFolioMoroso[$cont] . "</td>
                    <td>" . $regitroEditNombre[$cont] . "</td><td>" . $obj->getNombreEjecutivo() . "</td>
                    <td>$liga</td></tr>";
            }
            $cont++;
        }
        $message.= "</table></html> ";
        $mail->setSubject("Nuevos clientes morosos");
        $mail->setBody($message);
        foreach ($correos as $value) {
            if(isset($value) && $value!=""){
                if(isset($value) && $value!="" && filter_var($value, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                    $mail->setTo($value);
                    if ($mail->enviarMail() == "1") {

                    } else {
                        echo "Error: El correo no se pudo enviar.";
                    }
                }
            }
        }    
        /*Los envio a mi correo par monitorear los mail enviados*/
        
        /*Correos para el cron por default*/
        $result2 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5;");
        while($rs2 = mysql_fetch_array($result2)){
            if(isset($rs2['correo']) && $rs2['correo']!="" && filter_var($rs2['correo'], FILTER_VALIDATE_EMAIL)){
                $mail->setTo($rs2['correo']);
                $mail->enviarMail();
            }
        }
        echo "Se cambiaron <b>" . $numeroEditados . "</b> clientes a morosos <br/>";
    } else {
        echo "No se editaron clientes existentes <br/>";
    }
}
?>

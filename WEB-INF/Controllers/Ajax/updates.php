<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}

include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/ParametroGlobal.class.php");
$parametroGlobal = new ParametroGlobal();

$catalogo = new Catalogo();
if(isset($_POST['copia_domicilio']) && isset($_POST['ClaveCliente']) && isset($_POST['ClaveLocalidad'])){
    include_once("../../Classes/Localidad.class.php");
    $domicilio = new Localidad();
    if($domicilio->getLocalidadByClave($_POST['ClaveCliente'])){
        $domicilio->setClaveEspecialDomicilio($_POST['ClaveLocalidad']);
        if($domicilio->newRegistro("5")){
            echo "Dirección copiada exitosamente";
        }else{
            echo "Error: Hubo un error al copiar el domicilio";
        }
    }else{
        echo "Error: no se encontró domicilio del cliente";
    }
}else if(isset ($_POST['NoVenta']) && isset ($_POST['facturar'])){
    include_once("../../Classes/VentaDirecta.class.php");
    $vd = new VentaDirecta();
    $vd->setId($_POST['NoVenta']);
    if($vd->marcarFacturada()){
        echo "La venta directa ".$_POST['NoVenta']." fue marcada como facturada";
    }else{
        echo "Error: No se pudo marcar como facturada la venta";
    }
}else if(isset($_POST['cliente']) && isset($_POST['estatus']) && isset($_POST['suspendido'])){
    include_once("../../Classes/Cliente.class.php");
    $obj = new Cliente();
    $obj->getRegistroById($_POST['cliente']);
    if($_POST['suspendido'] == "true"){
        if($obj->marcarComoSuspendidoRFC(true)){
            echo "Se ha marcado al cliente ".$obj->getNombreRazonSocial()." como suspendido";
        }else{
            echo "Error: no se ha podido marcar al cliente como suspendido, intenta de nuevo por favor.";
        }
    }else{
        if($obj->marcarComoSuspendidoRFC(0)){
            //echo "Se ha marcado al cliente ".$obj->getNombreRazonSocial()." como no suspendido";
        }else{
            //echo "Error: no se ha podido marcar al cliente como no suspendido, intenta de nuevo por favor.";
        }
    }
}else if (isset($_POST['cliente']) && isset($_POST['estatus'])) {
    include_once("../../Classes/Mail.class.php");
    include_once("../../Classes/Cliente.class.php");
    include_once("../../Classes/Usuario.class.php");    
    $mail = new Mail();    
    $obj = new Cliente();
    if ($_POST['estatus'] == "1") {
        $estatus = 2;
    } else {
        $estatus = 1;
    }
    $obj->setClaveCliente($_POST['cliente']);
    $obj->setIdEstatusCobranza($estatus);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setPantalla('PHP valida cliente');
    if ($obj->cambiarEstatusCobranza()) {
        if ($estatus == 2) {
            if ($obj->obtenerDatosClientesCorreo($_POST['cliente'])) {
                $cliente = $obj->getNombreRazonSocial();                
                $ejecutivo = $obj->getNombreEjecutivo();
                $correo = $obj->getCorreoEjecutivo();
                //enviar correo
                $correos = array();                

                if($parametroGlobal->getRegistroById("8")){
                    $mail->setFrom($parametroGlobal->getValor());
                }else{
                    $mail->setFrom("scg-salida@scgenesis.mx");
                }
                $mail->setSubject("Moroso: " . $cliente);
                $message = "El cliente " . $cliente . " del ejecutivo " . $ejecutivo . " fue cambiado a moroso ";
                //correos de la tabla de correos solicitud

                $tipoServidor = "";
                $query0 = $catalogo->obtenerLista("SELECT * FROM c_parametro p WHERE p.IdParametro=9");
                while ($rs = mysql_fetch_array($query0)) {
                    $tipoServidor = $rs["Descripcion"];
                }
                if ($tipoServidor == "Pruebas") {
                    $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud=3;");
                    $z = 0;
                    while ($rs = mysql_fetch_array($query4)) {
                        $correos[$z] = $rs['correo'];
                        $z++;
                    }
                } else if ($tipoServidor == "Producción") {
                    if ($correo != "") {
                        $correos[0] = $correo; //correo del ejecutivo de cuenta  
                        $z = 1;
                    }
                    else
                        $z = 0;
                    $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud=3;");

                    while ($rs = mysql_fetch_array($query4)) {//correos de c_correosolicitud
                        $correos[$z] = $rs['correo'];
                        $z++;
                    } $z2 = $z;
                    //correos de cuetas por cobrar
                    $query5 = $catalogo->obtenerLista("SELECT u.IdUsuario,CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS nombre,u.correo
                    FROM c_usuario u WHERE u.IdPuesto=15 AND u.Activo=1 AND u.correo<>'null' AND u.correo<>'' ");
                    while ($rs1 = mysql_fetch_array($query5)) {
                        $correos[$z2] = $rs1['correo'];
                        $z2++;
                    }
                } else {
                    echo "No se pueden mandar los correos";
                }


                $mail->setBody($message);
                foreach ($correos as $value) {
                    if(isset($value) && $value!="" && filter_var($value, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                        $mail->setTo($value);
                         if ($mail->enviarMail() == "1") {
    //                     echo "Se envio un correo de aviso.";
                        } else {
                            echo "Error: El correo no se pudo enviar.";
                        }
                    }
                }
                // echo "El cliente " . $obj->getNombreRazonSocial() . " fue actualizado de estatus de cobranza";
            }
            echo "El cliente " . $obj->getNombreRazonSocial() . " fue actualizado de estatus de cobranza";
        } else {
            if ($obj->getNombreClienteById())
                echo "El cliente " . $obj->getNombreRazonSocial() . " fue actualizado de estatus de cobranza";
        }
    } else {
        echo"Error: hubo un error al actualizar, intenta de nuevo por favor";
    }
} else if (isset($_POST['idTicket']) && isset($_POST['reabrir'])) {
    /*Re-abrimos el ticket especificado*/
    include_once("../../Classes/Ticket.class.php");
    $obj = new Ticket();
    $respuesta = $obj->reAbrirTicket($_POST['idTicket']);
    if ($respuesta == 1) {
        echo "El ticket " . $_POST['idTicket'] . " se re-abrio correctamente";
    } else if ($respuesta == 2) {
        echo "Error: No se pudo agregar la nueva nota";
    } else {
        echo "Error: No se pudo actualizar el estado del ticket";
    }
}else if(isset ($_POST['contrato']) && isset ($_POST['cc']) && isset($_POST['client']) && isset ($_POST['anexo'])){
    include_once("../../Classes/Anexo.class.php");
    $anexo = new Anexo();
    /*En caso de que no exista, creamos anexos y/o asociamos a las localidad elegida. Tambien se crean los servicios*/    
    $result = $anexo->getAnexosDeContratoLocalidad($_POST['contrato'], $_POST['cc']);
    if(mysql_num_rows($result) == 0){ /*En caso de que no haya anexos asociados a esta localidad*/
        $anexo->setClaveCC($_POST['cc']); 
        $anexo->setUsuarioCreacion($_SESSION['user']);
        $anexo->setUsuarioUltimaModificacion($_SESSION['user']);
        $anexo->setPantalla("PHP background updates.php");
        $result = $anexo->getAnexosDeContrato($_POST['contrato']);
        if(mysql_num_rows($result) > 0){/*Existen anexos pero no estan asociados a esta localidad*/
            while($rs = mysql_fetch_array($result)){/*Recorremos los anexos y los asociamos a la localidad*/
                $anexo->setClaveAnexoTecnico($rs['ClaveAnexoTecnico']);                
                $anexo->setFechaElaboracion($rs['FechaElaboracion']);               
                $anexo->newK_anexoClienteCC();
            }
        }else{/*No hay anexos creados para el contrato*/            
            $anexo->setNoContrato($_POST['contrato']);
            $anexo->setFechaElaboracion(date('Y')."-".date('m')."-".date('d'));
            $anexo->setActivo("1");
            $anexo->newRegistro();
        }
    }
}else if(isset ($_POST['idAnexoClienteCC']) && isset ($_POST['catalogo_servicios'])){
    include_once("../../Classes/ServicioIM.class.php");
    include_once("../../Classes/ServicioGIM.class.php");
    $servicio = new ServicioIM();
    $result = $servicio->getServiciosAnexoByIdAnexoClienteCC($_POST['idAnexoClienteCC'],"im");
    $result2 = $servicio->getServiciosAnexoByIdAnexoClienteCC($_POST['idAnexoClienteCC'],"gim");
    $result3 = $servicio->getServiciosAnexoByIdAnexoClienteCC($_POST['idAnexoClienteCC'],"fa");
    $result4 = $servicio->getServiciosAnexoByIdAnexoClienteCC($_POST['idAnexoClienteCC'],"gfa");
    if(mysql_num_rows($result) == 0 && mysql_num_rows($result2) == 0 &&
        mysql_num_rows($result3) == 0 && mysql_num_rows($result4) == 0){/* No existen servicios para este anexoClienteCC*/
        echo "Entro: ";
        if(isset($_POST['tipo']) && $_POST['tipo']=="0"){/*Servicio global*/
            echo "Entro: 0";
            $servicioGIM = new KServicioGIM();
            $servicioGIM->setIdAnexoClienteCC($_POST['idAnexoClienteCC']);
            $servicioGIM->setIdServicioGIM("1050");
            $servicioGIM->setRentaMensual("0");
            $servicioGIM->setPaginasIncluidasBN("0");$servicioGIM->setPaginasIncluidasColor("0");
            $servicioGIM->setCostoPaginaProcesadaBN("0"); $servicioGIM->setCostoPaginaProcesadaColor("0");
            $servicioGIM->setCostoPaginasExcedentesBN("0"); $servicioGIM->setCostoPaginasExcedentesColor("0");
            $servicioGIM->setUsuarioCreacion($_SESSION['user']); $servicioGIM->setUsuarioUltimaModificacion($_SESSION['user']);
            $servicioGIM->setPantalla("PHP background updates.php");
            $servicioGIM->newRegistro();
        }else{
            echo "Entro: 1";
            $servicio->setIdServicioIM("100");
            $servicio->setIdAnexoClienteCC($_POST['idAnexoClienteCC']);
            $servicio->setRentaMensual("0");
            $servicio->setPaginasIncluidasBN("0");
            $servicio->setPaginasIncluidasColor("0");
            $servicio->setCostoPaginasExcedentesBN("0");
            $servicio->setCostoPaginasExcedentesColor("0");
            $servicio->setCostoPaginaProcesadaBN("0");
            $servicio->setCostoPaginaProcesadaColor("0");
            $servicio->setUsuarioCreacion($_SESSION['user']);
            $servicio->setUsuarioUltimaModificacion($_SESSION['user']);
            $servicio->setPantalla("PHP background updates.php");
            $servicio->newRegistro();
        }
    }else{
        echo "No hay que crear";
    }
}else if(isset ($_POST['cc']) && isset ($_POST['crear'])){
    include_once("../../Classes/Anexo.class.php");
    include_once("../../Classes/Contrato.class.php");
    include_once("../../Classes/CentroCosto.class.php");
    $anexo = new Anexo();
    $cc = new CentroCosto();
    $cc->getRegistroById($_POST['cc']);
    $anexo->setClaveCC($_POST['cc']); 
    $anexo->setUsuarioCreacion($_SESSION['user']);
    $anexo->setUsuarioUltimaModificacion($_SESSION['user']);
    $anexo->setPantalla("PHP background updates.php");
    $result = $anexo->getAnexosDeCliente($cc->getClaveCliente());//Obtenemos todos los anexos del cliente
    if(mysql_num_rows($result) > 0){/*Existen anexos pero no estan asociados a esta localidad*/        
        while($rs = mysql_fetch_array($result)){/*Recorremos los anexos y los asociamos a la localidad*/            
            if(!$anexo->hayKAnexoClienteByClave($rs['ClaveAnexoTecnico'], $_POST['cc'])){//Si el anexo actual no esta asociado a la localidad actual                
                $anexo->setClaveAnexoTecnico($rs['ClaveAnexoTecnico']);                
                $anexo->setFechaElaboracion($rs['FechaElaboracion']);               
                $anexo->newK_anexoClienteCC();
            }
        }
    }else{
        $contrato = new Contrato;
        $result = $contrato->getRegistroValidacion($cc->getClaveCliente());
        if(mysql_num_rows($result) > 0){/*El cliente si tiene contratos*/
            while($rs = mysql_fetch_array($result)){
                $anexo->setNoContrato($rs['NoContrato']);
                $anexo->setFechaElaboracion(date('Y')."-".date('m')."-".date('d'));
                $anexo->setActivo("1");
                $anexo->newRegistro();
            }
        }else{/*El cliente no tiene contratos creados*/
            $contrato->newRegistroDefault("2014-01-01", "2014-12-31", $cc->getClaveCliente(), $anexo->getPantalla());
            $anexo->setNoContrato($contrato->getNoContrato());
            $anexo->setFechaElaboracion(date('Y')."-".date('m')."-".date('d'));
            $anexo->setActivo("1");
            $anexo->newRegistro();
        }
    }    
}else if(isset ($_POST['localidad']) && isset ($_POST['anexo']) && isset ($_POST['contrato']) && isset ($_POST['IdAnexo'])){
    include_once("../../Classes/Catalogo.class.php"); 
    $consulta = "SELECT MIN(kacc.IdAnexoClienteCC) AS IdAnexoClienteCC FROM `k_anexoclientecc` AS kacc
        INNER JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = kacc.ClaveAnexoTecnico
        WHERE cat.NoContrato = '".$_POST['contrato']."' AND kacc.ClaveAnexoTecnico = '".$_POST['anexo']."' AND kacc.CveEspClienteCC = '".$_POST['localidad']."';";
    $catalogo = new Catalogo();
    $result = $catalogo->obtenerLista($consulta);
    while($rs = mysql_fetch_array($result)){
        echo $rs['IdAnexoClienteCC'];
    }
}
?>

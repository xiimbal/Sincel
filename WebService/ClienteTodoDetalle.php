<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/ccliente.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/HistoricoPosiciones.class.php");

function consultaDetalleCliente($ClaveCliente, $latitud, $longitud, $IdSession) {
    $empresa = 3;
    $session = new Session();
    $session->setEmpresa($empresa);
    if (!empty($IdSession)) {
        $resultadoLoggin = (int) $session->logginWithSession($IdSession);
    } else {
        $resultadoLoggin = 0;
    }

    if ($resultadoLoggin > 0 || empty($IdSession)) {
        $historico_posiciones = new HistoricoPosiciones();
        $historico_posiciones->setEmpresa($empresa);
        
        $cliente = new Cliente();
        $cliente->setEmpresa($empresa);
        $cliente->setIdUsuario($resultadoLoggin);
        
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $cliente_detalle = new ccliente();
        $cliente_detalle->setEmpresa($empresa);
        if (!$cliente->getRegistroById($ClaveCliente)) {
            return json_encode(-3);
        }

        $cliente_detalle->getregistrobyID($ClaveCliente);
        /* Obtiene todas las categorias */
        $resultCategorias = $cliente_detalle->obtieneMultiCategoria();
        $aux = array();
        while ($rs = mysql_fetch_array($resultCategorias)) {
            array_push($aux, $rs['IdGiro']);
        }        

        $cliente_aux = array();
        $cliente_aux['Giro'] = ($cliente_detalle->getGiro());
        $cliente_aux['Horario'] = ($cliente_detalle->getHorario());
        $cliente_aux['Facebook'] = ($cliente_detalle->getFacebook());
        $cliente_aux['Twitter'] = ($cliente_detalle->getTwitter());
        $cliente_aux['CategoriaSecundaria'] = $aux;
        $cliente_aux['Email'] = ($cliente->getEmail());
        $cliente_aux['SitioWeb'] = ($cliente->getSitioweb());
        $cliente_aux['Favorito'] = ($cliente->isFavorito($ClaveCliente)) ? 1 : 0;

        $calificaciones = array();
        $result = $cliente->getCalificacionesCliente(null);
        $suma_calis = 0;
        $count = 0;
        $promedio = 0;
        $cliente_aux['NumeroOpiniones'] = mysql_num_rows($result);
        if (mysql_num_rows($result) > 0) {
            $i = 1;
            while ($rs = mysql_fetch_array($result)) {
                if($i == mysql_num_rows($result)){
                    $cliente_aux['UltimoTitulo'] = $rs['Titulo'];
                    $cliente_aux['UltimaCalificacion'] = $rs['Calificacion'];
                    $cliente_aux['UltimoMensaje'] = $rs['Mensaje'];
                    $cliente_aux['FechaUltimoMensaje'] = $rs['FechaCreacion'];
                }
                $i++;
                //break;
            }
            if (mysql_data_seek($result, 0)) {
                $result = $cliente->getCalificacionesCliente(null);
            }
            while ($rs = mysql_fetch_array($result)) {
                $suma_calis += (int) $rs['Calificacion'];
                $count++;
                if(isset($rs['Calificacion'])){
                    $calificaciones[$rs['Calificacion']] ++;
                }else{
                    $calificaciones[$rs['Calificacion']] = 1;
                }
            }
            $promedio = ($suma_calis / $count);
        } else {
            $cliente_aux['PrimerCalificacion'] = "";
            $cliente_aux['PrimerOpinion'] = "";
        }
        
        $cliente_aux['Promedio'] = $promedio;
        $cliente_aux['Calificaciones'] = $calificaciones;
        $consulta = "SELECT COUNT(ClaveCliente) AS cuenta FROM k_favoritocliente WHERE ClaveCliente = '$ClaveCliente' GROUP BY ClaveCliente ORDER BY cuenta DESC;";
        $result = $catalogo->obtenerLista($consulta);
        $numero_favorito = 0;
        while ($rs = mysql_fetch_array($result)) {
            $numero_favorito = $rs['cuenta'];
        }
        $cliente_aux['VecesFavorito'] = $numero_favorito;
        
        //Se calcula la distancia
        if(!empty($latitud) && !empty($longitud)){
            $localidad = new Localidad();
            $cliente_aux['distanciaKm'] = $localidad->calcularDistancia($cliente_detalle->getLatitud(), $cliente_detalle->getLongitud(), $latitud, $longitud);
        }else{
            $cliente_aux['distanciaKm'] = 0;
        }
        
        $cliente_final = array();
        array_push($cliente_final, $cliente_aux);
        
        $historico_posiciones->setIdUsuario($resultadoLoggin);
        $historico_posiciones->setLatitud($latitud); $historico_posiciones->setLongitud($longitud);
        $historico_posiciones->setRadio("NULL");
        $historico_posiciones->setClaveCliente($ClaveCliente);
        $historico_posiciones->setIdGiro("NULL"); $historico_posiciones->setIdTipoContacto("NULL");        
        $historico_posiciones->setIdWebService(3);
        $historico_posiciones->setPantalla("Cliente Todo Detalle WS7");        
        $historico_posiciones->setUsuarioCreacion($resultadoLoggin); $historico_posiciones->setUsuarioUltimaModificacion($resultadoLoggin);
        $historico_posiciones->setRespuesta(json_encode(array_values($cliente_final)));
        $historico_posiciones->newRegistro();
        
        return json_encode(array_values($cliente_final));
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("clientetododetalle", "urn:clientetododetalle");
$server->register("consultaDetalleCliente", array("ClaveCliente" => "xsd:string", "latitud" => "xsd:float", "longitud" => "xsd:float", "IdSession" => "xsd:string"), array("return" => "xsd:string"), "urn:clientetododetalle", "urn:clientetododetalle#consultaDetalleCliente", "rpc", "encoded", "Obtiene el detalle del cliente especificado");

$server->service($HTTP_RAW_POST_DATA);
/* $server->register("getProd");
  $server->service($HTTP_RAW_POST_DATA); */
?>
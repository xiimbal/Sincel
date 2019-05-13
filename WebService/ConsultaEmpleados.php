<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

function obtenerEmpleados($IdCampania, $fecha, $IdSession) {

    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $fecharray = getdate();
        //print_r($fecharray);
        if (strlen($fecharray['mon']) == 1) {
            $mes = "0" . $fecharray['mon'];
        } else {
            $mes = $fecharray['mon'];
        }
        if (strlen($fecharray['mday']) == 1) {
            $dia = "0" . $fecharray['mday'];
        } else {
            $dia = $fecharray['mday'];
        }
        $fechaActual = $fecharray['year'] . "-" . $mes . "-" . $dia;
        //echo "</br>".$fecha;
        
        if(isset($IdCampania)&&!empty($IdCampania)){
            if($IdCampania>0){
                $whereCampania="cp.idCampania= " . $IdCampania . " AND";
            }else{$whereCampania="";}
        }else{$whereCampania="";}
        
        if(isset($fecha)&&!empty($fecha)){
            if($fecha!=""){
                $whereFecha="AND cp.Fecha='" . $fecha . "'";
            }else{$whereFecha="";}
        }else{$whereFecha="";}
        
        $busqueda = array();
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $query = $catalogo->obtenerLista("SELECT cp.idPlantilla,(SELECT CASE WHEN cp.TipoEvento = 1 THEN 'Aforo' ELSE 'Desaforo' END) AS TipoEvento, cp.Hora, cp.Fecha, cp.idCampania, ca.Descripcion AS NombreCampania, ctu.idTurno, 
                                                    ctu.descripcion AS NombreTurno, usu.IdUsuario, CONCAT(usu.Nombre,' ',usu.ApellidoPaterno,' ',ApellidoMaterno) AS nombreCompleto, cd.Codigo,
                                                    kpa.IdTicket, ces.Nombre AS NombreCuadranteCampania, cest.Nombre AS NombreCuadranteEmpleado
                                                    FROM c_turno AS ctu, c_plantilla AS cp, c_usuario AS usu,k_plantilla AS kp, k_plantilla_asistencia AS kpa,
                                                    c_estado AS ces, c_area AS ca, c_estado AS cest,c_domicilio_usturno AS cd
                                                    WHERE cp.idPlantilla=kp.idPlantilla AND kp.idK_Plantilla=kpa.idK_Plantilla AND 
                                                    ca.IdArea=cp.idCampania AND ctu.idTurno=cp.idTurno AND kp.idUsuario=usu.IdUsuario AND kp.idUsuario=cd.IdUsuario AND  
                                                    ces.IdEstado=ca.IdEstado AND  cest.IdEstado=cd.IdArea AND $whereCampania
                                                    cp.Estatus=2 AND kpa.Asistencia=1 $whereFecha
                                                    ORDER BY cp.Hora, usu.Nombre ASC;");
        while ($rs = mysql_fetch_array($query)) {
            $aux = array();
            $aux['IdPlantilla'] = $rs['idPlantilla'];
            $aux['TipoEvento'] = $rs['TipoEvento'];
            $aux['Hora'] = $rs['Hora'];
            $aux['Fecha'] = $rs['Fecha'];
            $aux['IdCampania'] = $rs['idCampania'];
            $aux['NombreCampania'] = $rs['NombreCampania'];
            $aux['IdTurno'] = $rs['idTurno'];
            $aux['NombreTurno'] = $rs['NombreTurno'];
            $aux['IdUsuario'] = $rs['IdUsuario'];
            $aux['NombreCompleto'] = $rs['nombreCompleto'];
            $aux['NoCodigoBarras'] = $rs['Codigo'];
            $aux['IdTicket'] = $rs['IdTicket'];
            $aux['NombreCuadranteCampania'] = $rs['NombreCuadranteCampania'];
            $aux['NombreCuadranteEmpleado'] = $rs['NombreCuadranteEmpleado'];
            array_push($busqueda, $aux);
        }
        
        if(isset($IdCampania)&&!empty($IdCampania)){
            if($IdCampania>0){
                $whereCampaniaE="ce.idCampania= " . $IdCampania . " AND";
            }else{$whereCampaniaE="";}
        }else{$whereCampaniaE="";}
        
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $query = $catalogo->obtenerLista("SELECT ce.idEspecial,ce.Hora, usu.IdUsuario, CONCAT(usu.Nombre,' ',usu.ApellidoPaterno,' ',ApellidoMaterno) AS nombreCompleto, cd.Codigo,
                                                    ce.idTicket, cti.FechaHora, ce.idCampania, ca.Descripcion AS NombreCampania, ce.idTurno, ctu.descripcion AS NombreTurno, ces.Nombre AS NombreCuadranteEmpleado
                                                    FROM c_especial AS ce, c_usuario AS usu, c_ticket AS cti, c_area AS ca, c_turno AS ctu, c_estado AS ces, c_domicilio_usturno AS cd
                                                    WHERE ce.idUsuario=usu.IdUsuario AND cti.IdTicket=ce.idTicket AND ce.idCampania=ca.IdArea AND ce.idTurno=ctu.idTurno AND ce.Cuadrante=ces.IdEstado AND ce.idUsuario=cd.IdUsuario AND
                                                    $whereCampaniaE cti.EstadoDeTicket=3 
                                                    ORDER BY ce.Hora, usu.Nombre ASC;");
        while ($rs = mysql_fetch_array($query)) {
            $date = explode(" ", $rs['FechaHora']);
            //$date[0]="2016-12-15";
            if ($date[0] >= $fechaActual) {
                $aux = array();
                $aux['IdEspecial'] = $rs['idEspecial'];
                $aux['TipoEvento'] = "Viaje Especial";
                $aux['Hora'] = $rs['Hora'];
                $aux['Fecha'] = $date[0];
                $aux['IdCampania'] = $rs['idCampania'];
                $aux['NombreCampania'] = $rs['NombreCampania'];
                $aux['IdTurno'] = $rs['idTurno'];
                $aux['NombreTurno'] = $rs['NombreTurno'];
                $aux['IdUsuario'] = $rs['IdUsuario'];
                $aux['NombreCompleto'] = $rs['nombreCompleto'];
                $aux['NoCodigoBarras'] = $rs['Codigo'];
                $aux['IdTicket'] = $rs['IdTicket'];
                $aux['NombreCuadranteCampania'] = "Viaje Especial";
                $aux['NombreCuadranteEmpleado'] = $rs['NombreCuadranteEmpleado'];
                array_push($busqueda, $aux);
            }
        }

        $json = array_values($busqueda);
        return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($json));
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("consultaEmpleados", "urn:consultaEmpleados");

$server->register("obtenerEmpleados", array("IdCampania" => "xsd:int", "fecha" => "xsd:string", "IdSession" => "xsd:string"), array("return" => "xsd:string"), "urn:consultaEmpleados", "urn:consultaEmpleados#ObtenerEmpleados", "rpc", "encoded", "Consulta de Empleados Loyalty");
$server->service($HTTP_RAW_POST_DATA);
?>
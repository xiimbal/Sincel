<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Evento.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

function getNotas($IdTickets, $IdUsuarios, $Estatus, $Pagina, $IdSession) {
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }

    $registros_por_pagina = 10;
    $indice = $registros_por_pagina * ($Pagina - 1);

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $where = "";
        $where_estatus = "";

        if (!empty($IdTickets)) {
            $where .= " AND t.IdTicket IN($IdTickets) ";
        }

        if (!empty($IdUsuarios)) {
            $where .= " AND ktt.IdUsuario IN($IdUsuarios) ";
        } else {
            $where .= " AND ktt.IdUsuario IN($resultadoLoggin) ";
        }

        if (!empty($Estatus)) {
            $where_estatus .= " AND IdEstatusAtencion IN($Estatus) ";
        }

        $consulta = "SELECT nt.IdNotaTicket, nt.IdTicket, nt.DiagnosticoSol, nt.IdEstatusAtencion, 
            nt.UsuarioSolicitud, nt.FechaHora, nt.Titulo, nt.PathImagen, nt.NombreImagen, nt.Latitud, nt.Longitud, nt.MinutosDefase,
            (CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno)) AS Usuario, e.Nombre AS Estado
            FROM c_ticket AS t
            LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket )
            LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = t.IdTicket
            LEFT JOIN c_usuario AS u ON u.IdUsuario = ktt.IdUsuario
            LEFT JOIN c_estado AS e ON e.IdEstado = nt.IdEstatusAtencion
            WHERE !ISNULL(nt.IdTicket) 
            GROUP BY t.IdTicket 
            ORDER BY nt.IdTicket DESC;";

        $busqueda = array();
        $result = $catalogo->obtenerLista($consulta);

        while ($rs = mysql_fetch_array($result)) {
            $aux = array();
            $aux['IdTicket'] = $rs['IdTicket'];
            $aux['Titulo'] = $rs['Titulo'];
            $aux['Comentario'] = $rs['DiagnosticoSol'];
            $filename = $rs['PathImagen'];

            if ($filename != "") {
                $tmpfile = "../" . $filename;   // temp filename     
                //Buscamos la imagen re-escalada, si no existe, no se envia nada
                $index_last_dot = strrpos($tmpfile, ".");
                $location_aux = substr($tmpfile, 0, $index_last_dot);
                $location_aux .= "resized_300_techra";
                $location_aux .= substr($tmpfile, $index_last_dot);

                if (!empty($location_aux)) {
                    $tmpfile = $location_aux;
                }

                if (file_exists($tmpfile)) {
                    $handle = fopen($tmpfile, "r");                  // Open the temp file
                    $contents = fread($handle, filesize($tmpfile));  // Read the temp file            
                    fclose($handle);                                 // Close the temp file

                    $decodeContent = base64_encode($contents);     // Decode the file content, so that we code send a binary string to SOAP            
                    $aux['NombreFoto'] = $filename;
                    $aux['Foto'] = $decodeContent;
                } else {
                    $cliente_aux['NombreFoto'] = "";
                    $cliente_aux['Foto'] = NULL;
                }
            } else {
                $aux['NombreFoto'] = $filename;
                $aux['Foto'] = NULL;
            }
            
            $aux['NombreUsuario'] = $rs['Usuario'];
            $aux['FechaHora'] = $rs['FechaHora'];
            $aux['IdEstatus'] = $rs['IdEstatusAtencion'];
            $aux['Estatus'] = $rs['Estado'];
            array_push($busqueda, $aux);
        }
        $json = array_values($busqueda);
        return json_encode($json);
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("consultaNota", "urn:consultaNota");
$server->register("getNotas", array("IdTickets" => "xsd:string", "IdUsuarios" => "xsd:string", "Estatus" => "xsd:string", "Pagina" => "xsd:int", "IdSession" => "xsd:string"), array("return" => "xsd:string"), "urn:consultaNota", "urn:consultaNota#getNotas", "rpc", "encoded", "Obtiene las notas de los tickets segun los filtros especificados");
$server->service($HTTP_RAW_POST_DATA);
?>
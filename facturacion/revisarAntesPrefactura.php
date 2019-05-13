<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
ini_set("memory_limit", "600M");

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" /* || !isset($_POST['rfc']) || !isset($_POST['rfcFacturacion']) */) {
    header("Location: index.php");
}


include_once("../WEB-INF/Classes/Catalogo.class.php");

$catalogo = new Catalogo();
$tickets = $_POST['tickets'];
$tickets2 = implode(",", $tickets);
$respuesta = "";

if(!empty($tickets2)){    
    $query = "SELECT t.IdTicket, MAX(tnr.IdNotaRemision) AS NTR, MAX(toc.IdOrdenCompra) AS OC FROM c_ticket t
    LEFT JOIN k_ticketnr tnr ON tnr.IdTicket = t.IdTicket LEFT JOIN k_tickets_oc toc ON toc.IdTicket = t.IdTicket
    WHERE t.IdTicket IN ($tickets2) GROUP BY t.IdTicket";
    $result = $catalogo->obtenerLista($query);
    while($rs = mysql_fetch_array($result)){
        if(!empty($rs['NTR']) || !empty($rs['OC'])){
            $respuesta .= $rs['IdTicket'] . "," . $rs['NTR'] . "," . $rs['OC'] . ";";
        }
    }
}

echo trim($respuesta,";");


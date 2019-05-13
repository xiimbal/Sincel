<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$catalogo = new Catalogo();
$parametro = new Parametros();

$parametro->getRegistroById(44);
$registros = $parametro->getValor();
$parametro->getRegistroById(45);
$minutos = $parametro->getValor();
$pagina = "0";
$fecha = date("Y-m-d");
$fechaHora = date("Y-m-d H:i:s");
$limiteInferior = 0;
$limiteSuperior = $registros;

$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();
$nombre_nota = $permisos_grid2->getTitulo(7); //Nombre de las notas en la empresa actual


if(isset($_POST['pagina']) && $_POST['pagina'] != ""){
    $pagina = $_POST['pagina'];    
    if($pagina == 0){
        $limiteInferior = 0;
    }else{
        $limiteInferior = ($pagina * $registros) + 1;
    }
    $limiteSuperior = (($pagina + 1) * $registros);
}
if(isset($_POST['fecha']) && $_POST['fecha'] != ""){
    $fecha = $_POST['fecha'];
}
if(isset($_POST['fechaHora']) && $_POST['fechaHora'] != ""){
    $fechaHora = $_POST['fechaHora'];
}

$arreglo = array("$nombre_nota","$nombre_objeto","Fecha de Inicio","Fecha de Fin","$nombre_puesto asignado","Prioridad","Tipo","Estado","Progreso");
$tabla = "";

$consulta = "SELECT nt.DiagnosticoSol AS actividad, t.Nombre AS proyecto, nt.FechaInicio,IF(nt.Progreso = '100' OR et.IdEstadoTicket = 2, nt.FechaHora, '') AS FechaFin, 
CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS tecnicoAsignado,nt.Descripcion, pt.Prioridad, e.Nombre AS tipo, et.Nombre AS estado, 
nt.Progreso, DATEDIFF('$fecha',nt.FechaInicio) AS diferencia, IF(nt.Progreso = '100' OR et.IdEstadoTicket = 2,NULL,ee.color) AS color FROM c_notaticket nt INNER JOIN c_ticket t ON t.IdTicket = nt.IdTicket
INNER JOIN c_estado e ON e.IdEstado = nt.IdEstatusAtencion INNER JOIN k_flujoestado k ON k.IdEstado = e.IdEstado INNER JOIN c_usuario u ON u.IdUsuario = nt.IdTecnicoAsignado
LEFT JOIN c_prioridadticket pt ON pt.IdPrioridad = nt.Prioridad LEFT JOIN c_estadoticket et ON et.IdEstadoTicket = nt.IdEstadoNota
LEFT JOIN c_escalamientoEstado ee ON ee.idEscalamiento = (SELECT x.idEscalamiento FROM c_escalamientoEstado x WHERE x.IdEstado = nt.IdEstatusAtencion AND DATEDIFF('$fecha',nt.FechaInicio) >= x.tiempoEnvio
ORDER BY x.tiempoEnvio DESC LIMIT 1) WHERE k.IdFlujo = 11 AND IF(nt.Progreso = '100' OR et.IdEstadoTicket = 2,'$fechaHora' BETWEEN nt.FechaHora AND DATE_ADD(nt.FechaHora,INTERVAL $minutos MINUTE),true)
LIMIT $limiteInferior, $limiteSuperior";
$result = $catalogo->obtenerLista($consulta);
if(mysql_num_rows($result) > 0){
    $tabla = "<table class='tabla' style='width: 100%'>";
    $tabla .= "<thead><tr>";
    for($i = 0; $i < count($arreglo); $i++){
        $tabla .= "<th width=\"2%\" align=\"center\" scope=\"col\">" . $arreglo[$i] . "</th>";
    }
    $tabla .= "</tr></thead><tbody>";
    while($rs = mysql_fetch_array($result)){
        $color = "";
        if(!empty($rs['color'])){
            $color = "background-color: #" . $rs['color'] . ";color:white;";
        }
        $progreso = $rs['Progreso'];
        if(empty($progreso)){
            $progreso = "0";
        }
        $tabla .= "<tr>"
                . "<td align='center' scope='row' style='$color'><span title='" . $rs['Descripcion'] . "'>" . $rs['actividad'] . "</span></td>"
                . "<td align='center' scope='row' style='$color'>" . $rs['proyecto'] . "</td>"
                . "<td align='center' scope='row' style='$color'>" . $rs['FechaInicio'] . "</td>"
                . "<td align='center' scope='row' style='$color'>" . $rs['FechaFin'] . "</td>"
                . "<td align='center' scope='row' style='$color'>" . $rs['tecnicoAsignado'] . "</td>"
                . "<td align='center' scope='row' style='$color'>" . $rs['Prioridad'] . "</td>"
                . "<td align='center' scope='row' style='$color'>" . $rs['tipo'] . "</td>"
                . "<td align='center' scope='row' style='$color'>" . $rs['estado'] . "</td>"
                . "<td align='center' scope='row' style='$color'>" . $progreso . "%  </td>"
                . "</tr>";
    }
    $tabla .= "</tbody></table>";
    
}
echo $tabla;
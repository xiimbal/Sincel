<?php
date_default_timezone_set('America/Mexico_City');
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Puesto.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "mesa/orden_operador.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$tiene_filtro = false;
$Fecha = date('Y') . "-" . date('m') . "-" . date('d');
//echo $Fecha;

/* Obtenemos el nombre del objeto como se maneja en el sistema (i.e. Ticket, Evento, etc.) */
$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();
$nombre_estado = $permisos_grid2->getNombreTipoReporteSistema();
$latitud = $permisos_grid2->getLatitudSistema();
$longitud = $permisos_grid2->getLongitudSistema();

$tecnicos = array();
$servicio = "";
$areas = "";
$base = 1;
$catalogo = new Catalogo();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type='text/javascript' language='javascript' src='resources/js/paginas/ordenOperador.js'></script>
        <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <!-- FontAwesome para iconos -->
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="principal">              
            <form id="formOrdenOperador" name="formOrdenOperador" action="/" method="POST"> 
                <div class="container-fluid">  
                     <div class="form-row">
                      
    <!--                    <td>
                            Cuadrante:
                        </td>
                        <td>
                            <select id="area" name="area" class="select">
                        <?php
                        /* Inicializamos la clase */
//                            $query = $catalogo->obtenerLista("SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
//                                    INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) WHERE e.Activo = 1 ORDER BY Nombre;");
//                            while ($rs = mysql_fetch_array($query)) {
//                                $s = "";
//                                if (!empty($areas) && $rs['IdEstado'] == $areas) {
//                                    $s = "selected='selected'";
//                                }
//                                echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
//                            }
                        ?> 
                            </select>
                        </td>-->
                        <div class="form-group col-md-6">
                        <label>Base</label>
                            <select class="form-control" id="base" name="base" class="select">
                                <?php
                                /* Inicializamos la clase */
                                $query = $catalogo->obtenerLista("SELECT * FROM c_base_operador WHERE Activo = 1 ORDER BY IdBase;");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if (!empty($base) && $rs['IdBase'] == $base) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rs['IdBase'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?> 
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                        <label> Operador:</label>
                            <select class="form-control" id="operador" name="operador" class="select">
                                <option value="" >Selecciona un Operador</option>
                                <?php
                                $puestos = "OR IdPuesto= 108 OR IdPuesto= 109";
                                /* Inicializamos la clase */
                                $query = $catalogo->obtenerLista("SELECT (SELECT CASE WHEN ISNULL(coo.IdUsuario) THEN cu.IdUsuario ELSE '' END) AS IdUsuariosU, CONCAT(cu.Loggin,'-',cu.Nombre,' ',cu.ApellidoPaterno,' ',cu.ApellidoMaterno) AS Usuario FROM c_usuario AS cu
                                        LEFT JOIN c_orden_operador AS coo ON coo.IdUsuario=cu.IdUsuario AND DATE(coo.FechaHora)=DATE(NOW())
                                        WHERE cu.Activo = 1 AND (IdPuesto= 101)
                                        GROUP BY cu.IdUsuario
                                        ORDER BY Nombre;");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if (!empty($operador) && $rs['IdUsuario'] == $operador) {
                                        $s = "selected='selected'";
                                    }
                                    if ($rs['IdUsuariosU'] != '') {
                                        echo "<option value='" . $rs['IdUsuariosU'] . "' $s>" . $rs['Usuario'] . "</option>";
                                    }
                                }
                                ?> 
                            </select>
                        </div>  
                                    
                            <button class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3"  id="agregarOperador" onclick="AgregarOperador('operador', 'base', 'mesa/orden_operador.php');
                                    return false;">Agregar Operador</button>
                            <div id='error_agregar' style='color:red;'></div>
               
                       <div class="form-group col-md-4"> 
                           <div id='error_accion' style='color:red;'></div>
                          <center> <a href='#' onclick='subirOperador("<?php echo $same_page; ?>"); return false;' id="subirOperador" title="Subir Operador"  style="cursor: pointer;"><img  src="resources/images/dFlecha.png" width="24" height="24"/></a> </center>    
                         </div>
                        <div class="form-group col-md-4">
                            <div id='error_accion' style='color:red;'></div>
                            <center><a href='#' onclick='bajarOperador("<?php echo $same_page; ?>"); return false;' id="bajarOperador" title="Bajar Operador"  style="cursor: pointer;"><img  src="resources/images/upFlecha.png" width="24" height="24"/></a><center>     
                        </div>
                        <div class="form-group col-md-4">
                           <div id='error_accion' style='color:red;'></div>
                           <center><a href='#' onclick='eliminarOperador("<?php echo $same_page; ?>"); return false;' id="eliminarOperador" title="Quitar Operador"  style="cursor: pointer;"><img  src="resources/images/Erase.png" width="24" height="24"/></a></center>
                        </div>
                            <div class="form-group col-md-4">
                            <select class="form-control" id="servicio" name="servicio" class="select">
                                <option class="form-control" value="" >Selecciona un Servicio</option>
                                <?php
                                /* Inicializamos la clase */
                                $tickets = array();
                                /* $query = $catalogo->obtenerLista("SELECT IdTicket FROM c_ticket WHERE Activo = 1 AND EstadoDeTicket NOT IN(2,4);");
                                  while ($rs = mysql_fetch_array($query)) {
                                  array_push($tickets, $rs['IdTicket']);
                                  }
                                  foreach ($tickets as $value) {
                                  $s = "";
                                  if (!empty($servicio) && $value == $servicio) {
                                  $s = "selected='selected'";
                                  }
                                  $query2 = $catalogo->obtenerLista("SELECT * FROM c_notaticket WHERE IdTicket= " . $value . ";");
                                  if (mysql_num_rows($query2) < 1) {
                                  echo "<option value='" . $value . "' $s>" . $value . "</option>";
                                  } else {
                                  if (mysql_num_rows($query2) == 1) {
                                  $rs2 = mysql_fetch_array($query2);
                                  if ($rs2['IdEstatusAtencion'] == 22) {
                                  echo "<option value='" . $value . "' $s>" . $value . " (Asignado)</option>";
                                  }
                                  }
                                  }
                                  } */


                                $query = $catalogo->obtenerLista("SELECT t.IdTicket, nt.IdNotaTicket
                                    FROM c_ticket AS t
                                    LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion = 22)
                                    WHERE t.Activo = 1 AND t.EstadoDeTicket NOT IN(2,4);");
                                while ($rs = mysql_fetch_array($query)) {                                    
                                    $s = "";
                                    if (!empty($servicio) && $value == $servicio) {
                                        $s = "selected='selected'";
                                    }
                                    if (!isset($rs['IdNotaTicket']) || empty($rs['IdNotaTicket'])) {
                                        echo "<option value='" . $rs['IdTicket'] . "' $s>" . $rs['IdTicket'] . "</option>";
                                    } else {
                                        echo "<option value='" . $rs['IdTicket'] . "' $s>" . $rs['IdTicket'] . " (Asignado)</option>";
                                    }
                                }
                                ?> 
                            </select>
                        </div>

                       
                            <button class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" id="asigna_operador" onclick="relacionarOperadorServicio('mesa/orden_operador.php');
                                    return false;">Asignar <?php echo $nombre_objeto; ?></button>
                            <div id='error_operador' style='color:red;'></div>  
                      
                    </div>
                </div>
                <?php
                $l="AND coo.Activo = 1";
                $consulta = "SELECT coo.*, cp.Nombre AS Puesto, cu.Loggin, CONCAT(cu.Nombre,' ',cu.ApellidoPaterno,' ',cu.ApellidoMaterno) AS Usuario FROM c_orden_operador AS coo
                                        LEFT JOIN c_usuario AS cu ON cu.IdUsuario = coo.IdUsuario
                                        LEFT JOIN c_puesto AS cp ON cp.IdPuesto = cu.IdPuesto
                                        WHERE DATE(FechaHora) = '" . $Fecha . "' 
                                        ORDER BY coo.Orden;";
                $result = $catalogo->obtenerLista($consulta);
                //echo $consulta;
//            $LatitudesTecnicos = "";
//            $LongitudesTecnicos = "";
//            $FechaHoraTecnicos = "";
//            $userTecnico = "";
//            $PorcentajeBateria = "";
                ?> 
                <h2><?php echo "Operadores"; ?></h2>
                <table class="tablaUsuarios">
                    <thead>
                        <tr>
                            <?php
                            $cabeceras = array("Posición", "LN", "Estatus", "Operador", "Selecionar");
                            for ($i = 0; $i < (count($cabeceras)); $i++) {
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                            }
                            ?>                                                                      
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($rs = mysql_fetch_array($result)) {
                            echo "<tr>";
                            echo "<td align='center' scope='row'>" . $rs['Orden'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Puesto'] . "</td>";
                            if($rs['Activo']==0){
                                echo "<td align='center' scope='row'>Asignado</td>";
                            }else{
                                echo "<td align='center' scope='row'></td>";
                            }
                            echo "<td align='center' scope='row'>" . $rs['Loggin'] . "-" . $rs['Usuario'] . "</td>";
                            echo "<td align='center' scope='row'><input type='radio' id='radio_op" . $rs['IdUsuario'] . "' name='radio_op' value='" . $rs['IdOrdenOperador'] . "'/></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </form>
        </div>
    </body>
</html>
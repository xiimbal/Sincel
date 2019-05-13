<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}

include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "mesa/biliotecaSoluciones.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$cabecera = array("Ticket","Cliente","Localidad","NoSerie","Modelo","Descripcion Nota","Observacion","Diagnostico Nota","Manuales","");
$atributosABuscar = array("t.NoSerieEquipo","t.ModeloEquipo","t.DescripcionReporte","t.ObservacionAdicional","nt.DiagnosticoSol","p.ClaveEspEquipo","p.Modelo");

$palabra = "";
$fechaInicio = "";
$fechaFin = "";
if(isset($_POST['palabra']) && $_POST['palabra'] != ""){
    $palabra = $_POST['palabra'];
}
$palabras = explode(" ", $palabra);  //separamos la palabra por espacios para hacer una búsqueda más precisa, el orden será igual

$where = "";

$like = "'%";
for($i = 0; $i < count($palabras); $i++){   //Añadimos las palabras a buscar, en el orden que vienen
    $like.= "$palabras[$i]%";
}
$like .= "'";

if(count($atributosABuscar) != 0){  //Si existen atributos a buscar, entonces si hay filtros
    $where = "WHERE ";
}

foreach($atributosABuscar AS $atributo){
    $where .= "$atributo LIKE $like OR ";
}
$where = substr($where,0, -3);
if(isset($_POST['fecha_inicio']) && $_POST['fecha_inicio'] != ""){
    $where .= " AND t.FechaHora >= ".$_POST['fecha_inicio'];
}
if(isset($_POST['fecha_fin']) && $_POST['fecha_fin'] != ""){
    $where .= " AND t.FechaHora <= ".$_POST['fecha_fin'];
}

$query = "SELECT t.IdTicket, t.NombreCliente AS Cliente, t.TipoReporte, 
    t.NombreCentroCosto AS Localidad,
    (CASE WHEN !ISNULL(p.ClaveEspEquipo) THEN p.ClaveEspEquipo ELSE t.NoSerieEquipo END) AS Serie,
    (CASE WHEN !ISNULL(p.Modelo) THEN p.Modelo ELSE t.ModeloEquipo END) AS Modelo, 
    t.DescripcionReporte AS Descripcion,
    (CASE WHEN !ISNULL(e.PathEspecificacionesTecnicas) THEN e.PathEspecificacionesTecnicas ELSE e2.PathEspecificacionesTecnicas END) AS PathEspecificacionesTecnicas,
    (CASE WHEN !ISNULL(e.PathGuiaOperacionAvanza) THEN e.PathGuiaOperacionAvanza ELSE e2.PathGuiaOperacionAvanza END) AS PathGuiaOperacionAvanza,
    (CASE WHEN !ISNULL(e.PathListaPartes) THEN e.PathListaPartes ELSE e2.PathListaPartes END) AS PathListaPartes,
    (CASE WHEN !ISNULL(e.PathOperacion) THEN e.PathOperacion ELSE e2.PathOperacion END) AS PathOperacion,
    (CASE WHEN !ISNULL(e.PathManualServicio) THEN e.PathManualServicio ELSE e2.PathManualServicio END) AS PathManualServicio,
    t.ObservacionAdicional AS Observacion, nt.DiagnosticoSol AS Diagnostico 
    FROM c_ticket t
    LEFT JOIN c_notaticket AS nt ON t.IdTicket = nt.IdTicket 
    LEFT JOIN c_pedido AS p ON t.IdTicket = p.IdTicket 
    LEFT JOIN c_bitacora AS b ON p.ClaveEspEquipo = b.NoSerie 
    LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
    LEFT JOIN c_bitacora AS b2 ON b2.NoSerie = t.NoSerieEquipo
    LEFT JOIN c_equipo AS e2 ON e2.NoParte = b2.NoParte
    $where GROUP BY(t.IdTicket);";
$catalogo = new Catalogo();
$result = $catalogo->obtenerLista($query);

?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/controller_tabla_busqueda.js"></script>
<script type="text/javascript" language="javascript" src="resources/js/paginas/lista_ticket.js"></script>
<table class="table-responsive" id="tbusqueda">
    <thead>
        <tr>
            <?php
            for ($i = 0; $i < (count($cabecera)); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabecera[$i] . "</th>";
            }
        ?>  
        </tr>
    </thead>
    <tbody>
        <?php
            while($rs = mysql_fetch_array($result))
            {
                echo "<tr>";
                echo "<td>".$rs['IdTicket']."</td>";
                echo "<td>".$rs['Cliente']."</td>";
                echo "<td>".$rs['Localidad']."</td>";
                echo "<td>".$rs['Serie']."</td>";
                echo "<td>".$rs['Modelo']."</td>";
                echo "<td>".$rs['Descripcion']."</td>";
                echo "<td>".$rs['Observacion']."</td>";
                echo "<td>".$rs['Diagnostico']."</td>";
                echo "<td>";
                if(isset($rs['PathEspecificacionesTecnicas']) && $rs['PathEspecificacionesTecnicas'] != ""){
                    echo "<a href='WEB-INF/Controllers/documentos/equipos/" . $rs['PathEspecificacionesTecnicas'] . "' target='_blank'>Especificaciones Técnicas </a>";
                    echo "<br/>";
                }
                if(isset($rs['PathGuiaOperacionAvanza']) && $rs['PathGuiaOperacionAvanza'] != ""){
                    echo "<a href='WEB-INF/Controllers/documentos/equipos/" . $rs['PathGuiaOperacionAvanza'] . "' target='_blank'>Guía operación </a>";
                    echo "<br/>";
                }
                if(isset($rs['PathListaPartes']) && $rs['PathListaPartes'] != ""){
                    echo "<a href='WEB-INF/Controllers/documentos/equipos/" . $rs['PathListaPartes'] . "' target='_blank'>Lista de partes </a>";
                    echo "<br/>";
                }
                if(isset($rs['PathOperacion']) && $rs['PathOperacion'] != ""){
                    echo "<a href='WEB-INF/Controllers/documentos/equipos/" . $rs['PathOperacion'] . "' target='_blank'>Guía de operación </a>";
                    echo "<br/>";
                }
                if(isset($rs['PathManualServicio']) && $rs['PathManualServicio'] != ""){
                    echo "<a href='WEB-INF/Controllers/documentos/equipos/" . $rs['PathManualServicio'] . "' target='_blank'>Manual de servicio </a>";
                    echo "<br/>";
                }
                echo "</td>";
                echo "<td>"
                ?>
                <a href='#' onclick='detalleTicket("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "1", "", "0");
                                                            return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                <?php
                echo "</td></tr>";
            }
        ?>
    </tbody>
</table>
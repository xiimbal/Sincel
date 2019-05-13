<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

$mostrar = 0;
$autorizadas = "";
$rechazadas = 0;
$rechazadas_consulta = "";

if (isset($_GET['mostrar'])) {
    $mostrar = $_GET['mostrar'];
    if ($mostrar == 1) {
        $autorizadas = " OR c_solicitud.estatus=1 ";
    }
}
if (isset($_GET['rechazada'])) {
    $rechazadas = $_GET['rechazada'];
    if ($rechazadas == 1) {
        $rechazadas_consulta = " OR c_solicitud.estatus=3 ";
    }
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
    
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/Autorizaciones_Solicitud.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$cabeceras = array("Número de solicitud", "Fecha", "Cliente", "Localidades", "Número de equipos", "Número de componentes", "Status", "", "", "");
$columnas = array("ID", "Fecha", "Cliente", "localidades", "NumEquipos", "NumCompo", "Status");
$catalogo = new Catalogo();
$consulta = "SELECT c_solicitud.fecha_solicitud AS Fecha,
	c_cliente.NombreRazonSocial AS Cliente,
        (SELECT group_concat(Nombre separator ', ') FROM c_centrocosto AS cc, k_solicitud AS ks
	WHERE ks.id_solicitud = c_solicitud.id_solicitud AND cc.ClaveCentroCosto = ks.ClaveCentroCosto GROUP BY k_solicitud.id_solicitud) AS localidades,
	SUM(IF(k_solicitud.tipo=0,k_solicitud.cantidad,0)) AS NumEquipos,
	SUM(IF(k_solicitud.tipo=1,k_solicitud.cantidad,0)) AS NumCompo,
	CASE c_solicitud.estatus
			WHEN 0 THEN 'Registrada'
			WHEN 1 THEN 'Autorizada'
			WHEN 2 THEN 'Corregida'
			WHEN 3 THEN 'Rechazada'
			WHEN 4 THEN 'Cancelada'
			WHEN 5 THEN 'Surtida'
			ELSE ''
	END AS Status ,
	c_solicitud.id_solicitud AS ID
        FROM c_solicitud
        INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
        INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
        WHERE c_solicitud.estatus=0 $autorizadas $rechazadas_consulta 
        GROUP BY ID DESC";
$query = $catalogo->obtenerLista($consulta);
$alta = "ventas/AutorizarSolicitud.php"
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/lista_sol_equipo_autorizar.js"></script>
<!--link responsivo-->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/fontawesome/css/all.min.css" rel="stylesheet">
<div style="float: right;">
    <label for="checksc">Autorizadas</label><input type="checkbox" id="checksc" value="1" onchange="autorizadas();" 
        <?php if ($mostrar == 1) { echo "checked"; } ?>
    />
    <label for="checkrec">Rechazadas</label><input type="checkbox" id="checkrec" value="1" onchange="autorizadas();" 
        <?php if ($rechazadas == 1) { echo "checked"; } ?>
    />
</div><br/><br/>
<table id="tsolequipo" class="table-responsive">
    <thead>
        <tr>
            <?php
            for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            ?>                        
        </tr>
    </thead>
    <tbody>
        <?php
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";
            for ($i = 0; $i < count($columnas); $i++) {
                echo "<td align='center' scope='row'>" . $rs[$columnas[$i]] . "</td>";
            }
            if($permisos_grid->getModificar()){
                echo "<td align='center' scope='row'> <a href='#' onclick='editarRegistro(\"" . $alta . "\", \"" . $rs[$columnas[0]] . "\");return false;' title='Autorizar Registro' ><img src=\"resources/images/Apply.png\"/></a></td>";
            }else{
                echo "<td align='center' scope='row'></td>";
            }
            ?>                        
            <?php
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

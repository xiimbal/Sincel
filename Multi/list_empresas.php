<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Empresa.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "Multi/list_empresas.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$usuario = new Usuario();
$empresa = new Empresa();
$mostrar = 0;
if (isset($_GET['mostrar'])) {
    $mostrar = $_GET['mostrar'];
}
$cabeceras = array("Nombre", "Fecha Creación", "RFC", "País", "Activo", "CFDI", "PAC", "Folios" ,"Empresa", "");
$columnas = array("RazonSocial", "FechaCreacion", "RFC", "Pais", "Activo");
$query = $empresa->getTablaEmpresas();
$alta = "Multi/EditarEmpresa.php";
$eliminar = "WEB-INF/Controllers/Multi/Controller_eliminar_empresa.php";
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/Multi/list_empresas.js"></script>
<?php if ($permisos_grid->getAlta()) { ?>
    <p style="float: right; cursor: pointer;" >Nueva Empresa</p><img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("Multi/EditarEmpresa.php", "Empresa");' style="float: right; cursor: pointer;" />  
    
    <p style="float: right; cursor: pointer;" >Nuevo CFDI</p><img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("Multi/Alta_csd.php", "Nuevo CFDI");' style="float: right; cursor: pointer;" />  
    
    <p style="float: right; cursor: pointer;" >Nuevo PAC</p><img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("Multi/Alta_PAC.php", "Nuevo PAC");' style="float: right; cursor: pointer;" />  
<?php } ?>
<br/><br/><br/>
<table id="tempresas">
    <thead>
        <tr>
            <?php
            for ($i = 0; $i < (count($cabeceras) - 1); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
            ?>                        
        </tr    >
    </thead>
    <tbody>
        <?php
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";
            for ($i = 0; $i < count($columnas); $i++) {
                echo "<td align='center' scope='row'>" . $rs[$columnas[$i]] . "</td>";
            }
            if ($permisos_grid->getModificar()) {
                echo "<td align='center' scope='row'><a href='#' onclick=\"cambiarContenidos('Multi/Alta_csd.php?id=" . $rs['id_Cfdi'] . "', 'Editar CFDI'); return false;\"><img src='resources/images/Modify.png' title='Editar CFDI' style='width: 32px; height: 32px;'/></a></td>";
            } else {
                echo "<td align='center' scope='row'></td>";
            }
            if ($permisos_grid->getModificar()) {
                echo "<td align='center' scope='row'><a href='#' onclick=\"cambiarContenidos('Multi/Alta_PAC.php?id=" . $rs['id_pac'] . "','Editar PAC'); return false;\"><img src='resources/images/Modify.png' title='Editar PAC' style='width: 32px; height: 32px;'/></a></td>";
            } else {
                echo "<td align='center' scope='row'></td>";
            }
            if ($permisos_grid->getModificar()) {
                echo "<td align='center' scope='row'><a href='#' onclick=\"cambiarContenidos('Multi/Alta_folio.php?rfc=" . $rs['RFC'] . "','Editar RFC'); return false;\"><img src='resources/images/Modify.png' title='Editar RFC' style='width: 32px; height: 32px;'/></a></td>";
            } else {
                echo "<td align='center' scope='row'></td>";
            }
            ?>            
        <td align='center' scope='row'> 
            <?php if ($permisos_grid->getModificar()) { ?>
                <a style="cursor:pointer;" onclick="cambiarContenidos('Multi/EditarEmpresa.php?id=<?php echo $rs['idEmpresa'] ?>', 'Empresa');" title='Editar' ><img src="resources/images/Modify.png" width="24" height="24"/></a>
            <?php } ?>
        </td>  
        <td align='center' scope='row'>
            <?php if ($permisos_grid->getBaja()) { ?>
                <a onclick="eliminarEmpresa('<?php echo $rs['idEmpresa']; ?>');" title='Eliminar' ><img src="resources/images/Erase.png" width="24" height="24"/></a>
            <?php } ?>
        </td>  
        <?php
        echo "</tr>";
        $contador++;
    }
    ?>
</tbody>
</table>
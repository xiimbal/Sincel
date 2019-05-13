<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/GrupoEmpresas.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "Multi/list_grupo_empresas.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$usuario = new Usuario();
$empresa = new GrupoEmpresas();
$cabeceras = array("Nombre", "", "");
$columnas = array("Descripcion");
$query = $empresa->getTablaGrupoEmpresas();
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/Multi/list_GrupoEmpresas.js"></script>
<?php if ($permisos_grid->getAlta()) { ?>
    <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("Multi/Editar_GrupoEmpresas.php", "Grupo - Empresa");' style="float: right; cursor: pointer;" />  
<?php } ?>
<br/><br/><br/>
<table id="tempresas">
    <thead>
        <tr>
            <?php
            for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
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
            ?>            
        <td align='center' scope='row'> 
            <?php if ($permisos_grid->getModificar()) { ?>
                <a  onclick="cambiarContenidos('Multi/Editar_GrupoEmpresas.php?id=<?php echo $rs['IdGrupoEmpresa']?>', 'Empresa');" title='Editar' ><img src="resources/images/Modify.png" width="24" height="24"/></a>
            <?php } ?>
        </td>  
        <td align='center' scope='row'>
            <?php if ($permisos_grid->getBaja()) { ?>
            <a onclick="eliminarGrupoEmpresa('<?php echo $rs['IdGrupoEmpresa'];?>');" title='Eliminar' ><img src="resources/images/Erase.png" width="24" height="24"/></a>
            <?php } ?>
        </td>  
        <?php
            echo "</tr>";
            $contador++;
        }
        ?>
</tbody>
</table>
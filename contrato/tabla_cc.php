<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "contrato/lista_cc.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);


$cabeceras = array("Nombre", "Cliente", "", "");
$alta = "alta_cc.php?id=" . $_GET['id'];
?>
<script type="text/javascript" language="javascript" src="../resources/js/paginas/ventas/lista_centro_costo.js"></script>
<img class="imagenMouse" src="../resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>", "Nuevo centro de costo");' style="float: right; cursor: pointer;" />  
<br/><br/><br/>
<table id="tcc">
    <thead>
        <tr>
            <?php
            for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
            echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        /* Inicializamos la clase */
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("SELECT cen.id_cc,c.NombreRazonSocial,cen.nombre FROM c_cen_costo AS cen
INNER JOIN c_cliente AS c ON c.ClaveCliente=cen.ClaveCliente
WHERE cen.ClaveCliente='" . $_GET['id'] . "'");
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['nombre'] . "</td>";
            echo "<td align='center' width=\"2%\" scope='row'>" . $rs['NombreRazonSocial'] . "</td>";
            ?>
        <td align='center' scope='row' width="2%"> 
            <a href='#' onclick='cambiarContenidos("alta_cc.php?id=<?php echo $_GET['id']."&clave=".$rs['id_cc']; ?>", "Editar centro de costo");
                            return false;' title='Editar Centro de Costo' ><img src="../resources/images/Modify.png"/></a>

        </td>
        <td align='center' scope='row' width="2%"> 
            <a href='#' onclick='eliminarCC(<?php echo $rs['id_cc'] ?>)'>
                <img src="../resources/images/Erase.png"/>
            </a> 
        </td>                                        
        <?php
        echo "</tr>";
    }
    ?>
</tbody>
</table>
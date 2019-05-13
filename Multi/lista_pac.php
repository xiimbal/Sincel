<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/PAC.class.php");
$pac = new PAC();
$query = $pac->getTabla();
$cabeceras = Array("Nombre", "Usuario", "", "");
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/Multi/multi_lista_pac.js"></script>
<button class="boton" id="regresa" name="regresar" onclick="cambiarContenidos('Multi/list_empresas.php','Empresas')">Empresas</button>
<br/><br/><br/>
<img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("Multi/Alta_PAC.php", "Nuevo PAC");' style="float: right; cursor: pointer;" />  
<br/><br/><br/>
<table id="tpac" style="max-width: 100%;">
    <thead>
        <tr>
            <?php
            for ($i = 0; $i < count($cabeceras); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            ?>                        
        </tr>
    </thead>
    <tbody>
        <?php
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";
            //echo "<td align='center' scope='row'>" . $rs['id_pac'] . "</td>";
            echo "<td align='center' scope='row'>" . $rs['nombre'] . "</td>";
            echo "<td align='center' scope='row'>" . $rs['usuario'] . "</td>";
            //echo "<td align='center' scope='row'><a href='#' onclick=\"cambiarContenidos('Multi/Alta_csd.php?id=" . $rs['id_Cfdi'] . "', 'Nuevo CFDI');\" ><img src='resources/images/Modify.png' title='Editar CSD' style='width: 32px; height: 32px;'/></a></td>";
            echo "<td align='center' scope='row'><a href='#' onclick=\"cambiarContenidos('Multi/Alta_PAC.php?id=" . $rs['id_pac'] . "','Editar PAC');\"><img src='resources/images/Modify.png' title='Editar PAC' style='width: 32px; height: 32px;'/></a></td>";
            echo "<td align='center' scope='row'><a href='#' onclick='EliminarPAC(" . $rs['id_pac'] . ")'><img src='resources/images/Erase.png' title='Eliminar PAC' style='width: 32px; height: 32px;'/></a></td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

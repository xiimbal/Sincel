<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Folio.class.php");
$folio = new Folio();
$query = $folio->getTabla();
$cabeceras = Array("RFC Empresa", "Folio inicial", "Folio final", "Serie", "No aprobacion", "Ano aprobacion", "Ultimo folio", "Canceladas" , "Activo", "", "");
?>
<script type="text/javascript" language="javascript" src="../resources/js/paginas/Multi/multi_lista_folios.js"></script>

<br/><br/><br/>
<img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("Multi/Alta_folio.php", "Nuevo Folio");' style="float: right; cursor: pointer;" />  

<br/><br/><br/>
<table id="tfolio" style="max-width: 100%;">
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
            echo "<td align='center' scope='row'>" . $rs['RFCEmisor'] . "</td>";
            echo "<td align='center' scope='row'>" . $rs['FolioInicial'] . "</td>";
            echo "<td align='center' scope='row'>" . $rs['FolioFinal'] . "</td>";
            echo "<td align='center' scope='row'>" . $rs['Serie'] . "</td>";
            echo "<td align='center' scope='row'>" . $rs['NoAprobacion'] . "</td>";
            echo "<td align='center' scope='row'>" . $rs['AnioAprobacion'] . "</td>";
            echo "<td align='center' scope='row'>" . $rs['UltimoFolio'] . "</td>";
            echo "<td align='center' scope='row'>" . $rs['Canceladas'] . "</td>";
            $activo = "";
            if ($rs['Activo'] == 1) {
                $activo = "Activo";
            } else {
                $activo = "Inactivo";
            }
            echo "<td align='center' scope='row'>$activo</td>";
            echo "<td align='center' scope='row'><a href='#' onclick=\"cambiarContenidos('Multi/Alta_folio.php?id=" . $rs['IdFolio'] . "','Editar Folio');\"><img src='resources/images/Modify.png' title='Editar Folio' style='width: 32px; height: 32px;'/></a></td>";
            echo "<td align='center' scope='row'><a href='#' onclick='EliminarFolio(" . $rs['IdFolio'] . ")'><img src='resources/images/Erase.png' title='Eliminar Folio' style='width: 32px; height: 32px;'/></a></td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

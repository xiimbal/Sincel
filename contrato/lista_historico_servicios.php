<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_historico_servicios.js"></script>
    </head>
    <body>
        <br/><br/>
        <form id="filtroHistorico">
            <table style="width: 100%;">
                <tr>
                    <td>Cliente</td>
                    <td>
                        <select id="cliente" name="cliente" style="width: 200px;" class="filtroselect">
                            <?php
                            $query = $catalogo->obtenerLista("SELECT ClaveCliente,NombreRazonSocial FROM c_cliente WHERE Activo = 1 ORDER BY NombreRazonSocial ASC;");
                            echo "<option value=''>Selecciona una opción</option>";
                            while ($row = mysql_fetch_array($query)) {
                                echo "<option value='".$row['ClaveCliente']."'>".$row['NombreRazonSocial']."</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>Contrato</td>
                    <td>
                        <select id="contrato" name="contrato" style="width: 200px;" class="filtroselect"></select>
                    </td>
                    <td>Anexo</td>
                    <td>
                        <select id="anexo" name="anexo" style="width: 200px;" class="filtroselect"></select>
                    </td>
                    <td><input type="button" id="enviar" name="enviar" value="Mostrar" class="boton"/></td>
                </tr>
            </table>
        </form>
        <br/><br/>
        <div id="servicios" name="servicios"></div>
    </body>
</html>

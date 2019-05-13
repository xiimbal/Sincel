<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Usuario.class.php");
?>
<html>
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/Pendientes_Pago.js"></script>
    </head>
    <body>
        <form target="_blank" action="facturacion/Pendientes_Pago_excel.php" method="POST">
            <table style="width: 100%;">
                <tr>
                    <td>Fecha inicio</td>
                    <td>
                        <input type="text" class='fecha' id="fecha1" name="fecha1" value="<?php
                        if (isset($_GET['fecha1']) && $_GET['fecha1'] != "") {
                            $llamar = true;
                            echo $_GET['fecha1'];
                        }
                        ?>"/>
                    </td>
                    <td>Fecha Fin</td>
                    <td>
                        <input type="text" class='fecha' id="fecha2" name="fecha2" value="<?php
                        if (isset($_GET['fecha2']) && $_GET['fecha2'] != "") {
                            $llamar = true;
                            echo $_GET['fecha2'];
                        }
                        ?>"/>
                    </td>
                    <td><label for="ejecutivo">Ejecutivo: </label></td>
                    <td>
                        <select id="ejecutivo" name="ejecutivo" style="max-width: 200px;" class="filtroselect">
                            <option value="">Todos los ejecutivos</option>
                            <?php
                            $usuario = new Usuario();
                            $result = $usuario->getUsuariosByPuesto("11");
                            while ($rs = mysql_fetch_array($result)) {
                                $s = "";
                                if (isset($_POST['ejecutivo']) && $_POST['ejecutivo'] == $rs['IdUsuario']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . " " . $rs['ApellidoMaterno'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <input type="submit" class="button" value="Generar reporte arrendamiento" name="reporte1"/>
                        <br/><br/>
                        <!--<input type="submit" class="button" value="Generar reporte por facturar" name="reporte2"/>-->
                    </td>                    
                </tr>    
                <tr>
                    <td></td>
                    <td><input type="checkbox" id="agrupar_grupo" name="agrupar_grupo" value="1" />Agrupar por grupo de clientes</td>
                    <td></td>
                    <td><input type="checkbox" id="fecha_facturacion" name="fecha_facturacion" value="1" />Por fecha de facturación</td>
                </tr>
            </table>
        </form>
    </body>
</html>
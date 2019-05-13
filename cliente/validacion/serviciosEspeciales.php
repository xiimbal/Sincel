<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../WEB-INF/Classes/ServiciosVE.class.php");
include_once("../../WEB-INF/Classes/Catalogo.class.php");
$serviciosVE = new ServiciosVE();
$catalogo = new Catalogo();

$id = "";
$div = "servicios_p2";
$pagina = "lista_servicios_parti.php";
$ClaveAnexo = "";
$CC = "";
if(isset($_GET['Anexo'])){  //Cuando llega por get significa que estamos editando un registro, por POST es nuevo.
    $id = $_GET['Anexo'];
    $serviciosVE->setIdAnexoClienteCC($_GET['Anexo']);
    $ClaveAnexo = $_GET['Anexo'];
}else if($_POST['anexo']){
    $ClaveAnexo = $_POST['anexo'];
}

if(isset($_GET['CC'])){
    $CC = $_GET['CC'];
}

$numero = 1;
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/serviciosEspeciales.js"></script>
    </head>
    <body>
        <form id="formServiciosEspeciales" name="formServiciosEspeciales" method="POST" >
        <table style="width: 100%" id="tserviciosEspeciales">
            <tr>
                <td colspan="4" style="text-align:center"><h2>Valores para impresión de factura</h2></td>
            </tr>
            <tr>
                <td style="text-align:center; max-width: 20%;">Nombre</td>
                <td style="text-align:center; max-width: 20%;">Precio Unitario</td>
                <td style="text-align:center; max-width: 25%;">Tarifa</td>
                <td style="text-align:center; max-width: 5%;">Servicio Variable</td>
                <td style="max-width: 25%;"></td>
            <tr>    
            <?php 
            if(!empty($id)){
                $result = $serviciosVE->getServiciosVEByAnexo();
            }else{
                $result = null;
            }

            if(empty($id) || mysql_num_rows($result) == 0){
                echo "<tr id='row_$numero'>";
                echo "<td style='text-align:center'><input type='text' name='nombre_$numero' id='nombre_$numero' style='width: 80%;'></td>";
                echo "<td style='text-align:center'><input type='text' name='precio_$numero' id='precio_$numero' style='width: 60%;'></td>";
                echo "<td style='text-align:center'>";
                echo "<select id='tarifa_$numero' name='tarifa_$numero' style='width: 80%;'>";
                $queryTarifa = $catalogo->getListaAlta("c_tarifarango", "Tarifa");
                echo "<option value='' >Selecciona</option>";
                while ($rsTarifa= mysql_fetch_array($queryTarifa)) {
                    echo "<option value=" . $rsTarifa['IdTarifa'] . ">" . $rsTarifa['Tarifa'] . "</option>";
                }
                echo "</select>";
                echo "</td>";
                echo "<td style='text-align:center'><input type='checkbox' name='variable_$numero' id='variable_$numero' value='Activo'></td>";
                echo "<td style='text-align:center'>";
                echo "<select id='estado_$numero' name='estado_$numero' required='required' style='width: 90%;'>";
                $queryEstado = $catalogo->getListaAlta("c_estado", "Nombre");
                echo "<option value='' >Selecciona una opción</option>";
                while ($rsEstado = mysql_fetch_array($queryEstado)) {
                    echo "<option value=" . $rsEstado['IdEstado'] . ">" . $rsEstado['Nombre'] . "</option>";
                }
                echo "</select>";
                echo "</td>";
                echo '<td style="text-align:center"><input type="image" src="../resources/images/add.png" title="Agregar otro servicio" onclick="agregarConcepto(); return false;" /></td>';
                echo "<td></td>";
                echo "</tr>";
            } else{ 
                while($rs = mysql_fetch_array($result)){
                    echo "<tr id='row_$numero'>";
                    echo "<td style='text-align:center'><input type='text' name='nombre_$numero' id='nombre_$numero' value = '".$rs['NombreServicio']."' style='width: 80%;'></td>";
                    echo "<td style='text-align:center'><input type='text' name='precio_$numero' id='precio_$numero' value = '".$rs['PrecioUnitario']."' style='width: 60%;'></td>";
                    echo "<td><select id='tarifa_$numero' name='tarifa_$numero' style='width: 80%;'>";
                    $queryTarifa = $catalogo->getListaAlta("c_tarifarango", "Tarifa");
                    echo "<option value='' >Selecciona</option>";
                    while ($rsTarifa= mysql_fetch_array($queryTarifa)) {
                        $s = "";
                        if ($rs['IdTarifa'] != "" && $rs['IdTarifa'] == $rsTarifa['IdTarifa']) {
                            $s = "selected";
                        }
                        echo "<option value=" . $rsTarifa['IdTarifa'] . " " . $s . ">" . $rsTarifa['Tarifa'] . "</option>";
                    }
                    echo "</select></td>";
                    $s = "";
                    if((int)$rs['Tipo'] == 1){
                        $s = "checked";
                    }
                    echo "<td style='text-align:center'><input type='checkbox' name='variable_$numero' id='variable_$numero' value='Activo' $s></td>";
                    echo "<td style='text-align:center'>";
                    echo "<select id='estado_$numero' name='estado_$numero' required='required' disabled style='width: 90%;'>";
                    $queryEstado = $catalogo->getListaAlta("c_estado", "Nombre");
                    echo "<option value='' >Selecciona una opción</option>";
                    while ($rsEstado = mysql_fetch_array($queryEstado)) {
                        $s = "";
                        if ($rs['IdEstado'] != "" && $rs['IdEstado'] == $rsEstado['IdEstado']) {
                            $s = "selected";
                        }
                        echo "<option value=" . $rsEstado['IdEstado'] . " " . $s . ">" . $rsEstado['Nombre'] . "</option>";
                    }
                    echo "</select>";
                    echo "</td>";
                    echo '<td style="text-align:center"><input type="image" src="../resources/images/add.png" title="Agregar otro servicio" onclick="agregarConcepto(); return false;" /></td>';
                    echo "<td><input type='image' src='../resources/images/Erase.png' title='Eliminar este servicio' onclick='borrarConcepto(" . $numero . "); return false;'/></td>";
                    echo "<input type = 'hidden' name='idServicioVe$numero' id='idServicioVe$numero' value='".$rs['IdServicioVE']."' />";
                    $numero++; 
                    echo "</tr>";
                }
                $numero--;
             } 
             ?>
        </table>
            <input type="hidden" id="div_pagina" name="div_pagina" value="<?php echo $div; ?>"/>
            <input type="hidden" id="pagina" name="pagina" value="<?php echo $pagina; ?>"/>
            <input type="hidden" id="claveAnexo" name="claveAnexo" value="<?php echo $ClaveAnexo; ?>"/>
            <input type="hidden" id="cc" name="cc" value="<?php echo $CC; ?>"/>
            <input type="hidden" name="prefijo" id="prefijo" value="ve"/>
            <input type="hidden" id="numero_conceptos" name="numero_conceptos" value="<?php echo $numero; ?>"/>
            <input type="submit" id="cancelar_servicio" class="boton" id="cancelar" value="Cancelar" style="float: right; margin-right: 5px;"
                   onclick="cargarDependencia('<?php echo $div; ?>', '../cliente/validacion/<?php echo $pagina; ?>', '<?php echo $ClaveAnexo; ?>', null, '<?php echo $CC; ?>');
                    return false;"/>
            <input type="submit" class="boton" value="Guardar" id="guardar_servicio_im" style="float: right; margin-right: 5px;" />
        </form>
    </body>
</html>

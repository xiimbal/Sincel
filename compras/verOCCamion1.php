<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Orden_Compra.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$orden_compra = new Orden_Compra();

$id = $_GET['id'];
$orden_compra->getRegistroById($id);

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <!-- JS -->
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.9.1.js"></script>
        <script src="../resources/js/jquery/jquery-ui-1.10.3.custom.min.js"></script>        
        <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery.maskedinput.min.js"></script>
        <script type="text/javascript" src="../resources/js/funciones.js"></script>                   

        <!-- Tables -->
        <script type="text/javascript" language="javascript" src="../resources/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/media/js/TableTools.min.js"></script>
        <link href="../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
        <!-- multiselect -->
        <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
        <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
        <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">

        <link href="../resources/css/sicop.css" rel="stylesheet" type="text/css">                  
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/compras/verOCCamion.js"></script>        
    </head>
    <body>
        <div class="principal" style="max-width: 80%;">
            <div id="cargando" style="width:80%; margin-left: 50%; display: none; ">
                <img src="../resources/images/cargando.gif"/>                          
            </div>
            <div id="loading_text" style="width:80%; margin-top: 5px;  margin-left: 45%; display: none;"></div>
            
            <h3>Proovedor: <?php echo $orden_compra->getNombreProveedor(); ?></h3>
            
            <table style="width: 100%; border: none;">
                <thead>
                    <tr>
                        <td style="width: 10%;">Cantidad</td>
                        <td style="width: 10%;">Empaque</td>
                        <td style="width: 10%;">Producto</td>
                        <td style="width: 10%;">Kg</td>
                        <td style="width: 10%;">Precio</td>
                        <td style="width: 10%;">Total</td>
                        <td style="width: 10%;"></td>
                        <td style="width: 10%;"></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $catalogo = new Catalogo();
                        $consulta = "SELECT * FROM k_orden_compra WHERE IdOrdenCompra = $id";
                        $result = $catalogo->obtenerLista($consulta);
                        while($rs = mysql_fetch_array($result)){
                            echo "<tr>";
                            echo "<td>".$rs['Cantidad']."</td>";
                            echo "<td>".$rs['Empaque']."</td>";
                            echo "<td>".$rs['NoParteComponente']."</td>";
                            echo "<td>".$rs['Kg']."</td>";
                            echo "<td>".$rs['PrecioUnitario']."</td>";
                            echo "<td>".$rs['PrecioTotal']."</td>";
                            echo "<td><input type='image' src='../resources/images/Modify.png' title='Modificar' onclick=\"alert('Editar')\"/></td>";
                            echo "<td><input type='image' src='../resources/images/Erase.png' title='Eliminar' onclick=\"alert('Eliminar')\"/></td>";
                            echo "</tr>";
                        }
                        
                    ?>
                </tbody>
            </table>
            <br/>
            <table id="tProductos" style="width:120%;">
                <tr>
                    <td style="width: 7%;">Cantidad</td>
                    <td style="width: 23%;">Empaque</td>
                    <td style="width: 23%;">Producto</td>
                    <td style="width: 7%;">Kg</td>
                    <td style="width: 15%;">Precio</td>
                    <td style="width: 15%;">Total</td>
                    <td style="width: 10%;"></td>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="cantidad" id="cantidad" style="width: 90%;" />
                    </td>
                    <td>
                        <input type="text" readonly="readonly" name="empaque" id="empaque" style="width: 95%;"/>
                    </td>
                    <td>
                        <select name="producto" id="producto" class="select" style="width: 95%;" onchange="cargarEmpaque(); return false;">
                            <option value=''>Seleccione un producto</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="kg" id="kg" onblur="calcularTotal();" style="width: 90%;"/>
                    </td>
                    <td>
                        <input type="text" name="precio" id="precio" onblur="calcularTotal();" style="width: 95%;"/>
                    </td>
                    <td>
                        <input type="text" name="total" id="total" style="width: 95%;"/>
                    </td>
                    <td>
                        <input type="image" src="../resources/images/add.png" title="Agregar" onclick="agregarProducto(); return false;"  style="margin-bottom: -10px;"/>
                    </td>
                </tr>
            </table>
            
        </div>
    </body>
</html>


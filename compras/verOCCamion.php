<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Orden_Compra.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$orden_compra = new Orden_Compra();

$id = $_GET['id'];
$ticket_id = $_GET['ticket_id'];
$orden_compra->getRegistroById($id);

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" href="../css/fontawesome/css/all.min.css">
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
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    </head>
    <body>
        <div class="principal" style="max-width: 80%;">
            <div id="cargando" style="width:80%; margin-left: 50%; display: none; ">
                <img src="../resources/images/cargando.gif"/>                          
            </div>
            <div id="loading_text" style="width:80%; margin-top: 5px;  margin-left: 45%; display: none;"></div>
            
            <h3>Proovedor: <?php echo $orden_compra->getNombreProveedor(); ?></h3>
            
            <form id="editar_producto" name="editar_producto">
            <table class="table table-responsive" id="tProductos">
                <thead class="thead-dark">
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
                        $consulta = "SELECT oc.*,c.Descripcion,tc.Nombre AS TipoComponente FROM k_orden_compra oc
                            LEFT JOIN c_componente c ON c.NoParte = oc.NoParteComponente
                            LEFT JOIN c_tipocomponente tc ON tc.IdTipoComponente = c.IdTipoComponente
                            WHERE IdOrdenCompra = $id";
                        $productos = 0;
                        $result = $catalogo->obtenerLista($consulta);
                        while($rs = mysql_fetch_array($result)){
                            echo "<tr id='tr_".$rs['IdDetalleOC']."'>";
                            echo "<td style='width: 7%;'><input type='text' style='width: 90%;' id='cantidad_".$rs['IdDetalleOC']."' name='cantidad_".$rs['IdDetalleOC']."' disabled value='".$rs['Cantidad']."'/></td>";
                            echo "<td>".$rs['TipoComponente']."</td>";
                            echo "<td>".$rs['Descripcion']."</td>";
                            echo "<td style='width: 7%;'><input type='text' style='width: 90%;' id='kg_".$rs['IdDetalleOC']."' name='kg_".$rs['IdDetalleOC']."' disabled value='".$rs['Kg']."'/></td>";
                            echo "<td style='width: 7%;'><input type='text' style='width: 90%;' id='precio_".$rs['IdDetalleOC']."' name='precio_".$rs['IdDetalleOC']."' disabled value='".$rs['PrecioUnitario']."'/></td>";
                            echo "<td style='width: 7%;'><input type='text' style='width: 90%;' id='total_".$rs['IdDetalleOC']."' name='total_".$rs['IdDetalleOC']."' disabled value='".$rs['PrecioTotal']."'/></td>";
                            echo "<td id='td_concepto_".$rs['IdDetalleOC']."'><a class='text-dark' style='cursor: pointer;' onclick=\"habilitarProducto(".$rs['IdDetalleOC'].");\"><i class='fal fa-pencil'></i><!--img  src='../resources/images/Modify.png' /--></a></td>";
                            echo "<td><a class='text-dark' style='cursor: pointer;' onclick=\"eliminarProducto(".$rs['IdDetalleOC'].");\"><i class='fal fa-trash'></i><!--img src='../resources/images/Erase.png' /--></a></td>";
                            echo "</tr>";
                            $productos++;
                        }
                    ?>
                </tbody>
            </table>
            <input type="hidden" id="ticket_id" name="ticket_id" value="<?php echo $ticket_id; ?>"/>
            </form>
            <br/>
            <form id="frmProductos" name="frmProductos">
            <table class="table table-responsive">
                <thead class='thead-dark'>
                    <tr>
                    <td style="width: 7%;">Cantidad</td>
                    <td style="width: 23%;">Empaque</td>
                    <td style="width: 23%;">Producto</td>
                    <td style="width: 7%;">Kg</td>
                    <td style="width: 15%;">Precio</td>
                    <td style="width: 15%;">Total</td>
                    <td style="width: 10%;"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width: 7%;">
                        <input type="text" name="cantidad" id="cantidad" style="width: 90%;" />
                        </td>
                        <td style="width: 23%;">
                            <select name="empaque" id="empaque" class="select" style="width: 23%;" onchange="cargarProductos(); return false;">
                                <option value=''>Seleccione un empaque</option>
                                <?php
                                    $result = $catalogo->getListaAlta("c_tipocomponente", "Nombre");
                                    while($rs = mysql_fetch_array($result)){
                                        echo "<option value='".$rs['IdTipoComponente']."'>".$rs['Nombre']."</option>";
                                    }
                                ?>
                            </select>
                        </td>
                        <td>
                            <select name="producto" id="producto" class="select" style="width: 23%;">
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
                            <button class="btn btn-success" title="Agregar" onclick="agregarProducto(); return false;"  style="margin-bottom: -10px;"><i class="fal fa-plus-square"></i></button>
                            <!--input type="image" src="../resources/images/add.png" title="Agregar" onclick="agregarProducto(); return false;"  style="margin-bottom: -10px;"/-->
                        </td>
                    </tr>
                </tbody>
                
            </table>
            <input type="hidden" id="ticket_id" name="ticket_id" value="<?php echo $ticket_id; ?>"/>
            <input type="hidden" id="id" name="id" value="<?php echo $id; ?>"/>
            </form>
        </div>
    </body>
</html>


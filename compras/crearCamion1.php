<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Contacto.class.php");
include_once("../WEB-INF/Classes/Proveedor.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$contacto = new Contacto();
$proveedor = new Proveedor();

?>
<!DOCTYPE html>
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/compras/crearCamion.js"></script>
    </head>
    <body>
        <br/>
        <form id="frmCamion" name="frmCamion">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 4%;">
                        <label for="chofer">Chofer </label>
                    </td>
                    <td style="width: 16%;">
                        <select id="chofer" name="chofer" class="select">
                            <option value="">Selecciona un chofer</option>
                            <?php
                                $result = $contacto->getContactosPorIdTipo(7);
                                while($rs = mysql_fetch_array($result)){
                                    echo "<option value='".$rs['IdContacto']."'>".$rs['Nombre']."</option>";
                                }
                            ?>
                        </select>
                    </td>
                    <td style="width: 1%;"></td>
                    <td style="width: 5%;">
                        <label for="pesoBruto">Peso Bruto </label>
                    </td>
                    <td style="width: 10%;">
                        <input type="text" name="pesoBruto" id="pesoBruto" style="width: 90%;"/>
                    </td>
                    <td style="width: 3%;">
                        <label for="tara">Tara </label>
                    </td>
                    <td style="width: 10%;">
                        <input type="text" name="tara" id="tara" style="width: 90%;"/>
                    </td>
                    <td style="width: 3%;">
                        <label for="neto">Neto </label>
                    </td>
                    <td style="width: 10%;">
                        <input type="text" name="neto" id="neto" style="width: 90%;"/>
                    </td>
                    <td style="width: 5%;">
                        <label for="total">Costo Total </label>
                    </td>
                    <td style="width: 10%;">
                        <input type="text" name="costoTotal" id="costoTotal" style="width: 90%;"/>
                    </td>
                    <td>
                        <input type="button" class="boton" id="crearCamion" name="crearCamion" value="Crear camión" onclick="creandoCamion(); return false;">
                    </td>
                </tr>
            </table>
            <fieldset >
                <legend>Domicilio destino</legend>
                <div id="tabs" style="width: 98%;">
                    <ul>
                        <li><a href="#tabs-1">Almacen</a></li>
                        <li><a href="#tabs-2">Otra</a></li>
                    </ul>
                    <div id="tabs-1" style="background-color: #A4A4A4">
                        <select class="select" name="almacen" id="almacen">
                            <option value="">Selecciona un almacen</option>
                            <?php
                                $catalogo = new Catalogo();
                                $result = $catalogo->getListaAlta("c_almacen", "nombre_almacen");
                                while($rs = mysql_fetch_array($result)){
                                    echo "<option value='".$rs['id_almacen']."'>".$rs['nombre_almacen']."</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div id="tabs-2" style="background-color: #A4A4A4">
                        <table style="width: 90%">
                            <tr>
                                <td style="width:8%;">Nombre destino</td>
                                <td style="width:15%;"><input type="text" id="destino" name="destino" /></td>
                                <td style="width:5%;">Calle</td>
                                <td style="width:15%;"><input type="text" id="calle" name="calle" /></td>
                                <td style="width:7%;">No. Exterior</td>
                                <td style="width:10%;"><input type="text" id="exterior" name="exterior" /></td>
                                <td style="width:7%;">No. Interior</td>
                                <td style="width:10%;"><input type="text" id="interior" name="interior" /></td>
                                <td style="width:5%;">Colonia</td>
                                <td style="width:15%;"><input type="text" id="colonia" name="colonia" /></td>
                            </tr>
                            <tr>
                                <td colspan="10">
                                    <table style="width:90%;">
                                        <tr>
                                            <td style="width:4%;">Ciudad</td>
                                            <td style="width:15%;"><input type="text" id="ciudad" name="ciudad" /></td>
                                            <td style="width:5%;">Delegación</td>
                                            <td style="width:15%;"><input type="text" id="delegacion" name="delegacion" /></td>
                                            <td style="width:4%;">C.P.</td>
                                            <td style="width:8%;"><input type="text" id="cp" name="cp" /></td>
                                            <td style="width:5%;">Estado</td>
                                            <td style="width:15%;"><input type="text" id="estado" name="estado" /></td>
                                            <td style="width:5%;">Latitud</td>
                                            <td style="width:9%;"><input type="text" id="latitud" name="latitud" /></td>
                                            <td style="width:5%;">Longitud</td>
                                            <td style="width:9%;"><input type="text" id="longitud" name="longitud" /></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>Comentarios</td>
                                <td colspan="3">
                                    <textarea name="comentarios" id="comentarios">

                                    </textarea>
                                </td>
                                <td colspan="6"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>Ordenes de compra</legend>
                <div id="ocs">
                    
                </div>
            </fieldset>
        </form>
        <form id="frmOc" name="frmOc">
            <label for="proveedor">Proveedor </label>
            <select id="proveedor" name="proveedor" class="select" onchange="cargarProductosProveedor();">
                <option value="">Selecciona un proveedor</option>
                <?php
                    $result = $proveedor->getUsuarios();
                    while($rs = mysql_fetch_array($result)){
                        echo "<option value='".$rs['ClaveProveedor']."'>".$rs['NombreComercial']."</option>";
                    }
                ?>
            </select>
            &nbsp;&nbsp;&nbsp;&nbsp;<input type="image" src="resources/images/add.png" title="Agregar Orden Compra" onclick="agregarOc(); return false;"  style="margin-bottom: -10px;"/>
            <br/><br/>
            <table id="tProductos" style="width:80%;">
                <tr>
                    <td>Cantidad</td>
                    <td>Empaque</td>
                    <td>Producto</td>
                    <td>Kg</td>
                    <td>Precio</td>
                    <td>Total</td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="cantidad" id="cantidad" />
                    </td>
                    <td>
                        <input type="text" readonly="readonly" name="empaque" id="empaque" />
                    </td>
                    <td>
                        <select name="producto" id="producto" class="select" onchange="cargarEmpaque(); return false;">
                            <option value=''>Seleccione un producto</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="kg" id="kg" onblur="calcularTotal();"/>
                    </td>
                    <td>
                        <input type="text" name="precio" id="precio" onblur="calcularTotal();"/>
                    </td>
                    <td>
                        <input type="text" name="total" id="total" />
                    </td>
                    <td>
                        <input type="image" src="resources/images/add.png" title="Agregar" onclick="agregarProducto(); return false;"  style="margin-bottom: -10px;"/>
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>
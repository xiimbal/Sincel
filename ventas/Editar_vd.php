<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $urlextra = "?cliente=" . $_GET['cliente'];
    if (isset($_GET['vendedor'])) {
        $urlextra .= "&vendedor=" . $_GET['vendedor'];
    }
    $catalogo = new Catalogo();
    $llamadas = "";
    $queryprin = $catalogo->obtenerLista("SELECT c_ventadirecta.ClaveCliente AS ClaveCliente,
                c_ventadirecta.Clave_Localidad AS Localidad,
		c_ventadirecta.Fecha AS Fecha,
		c_ventadirecta.Estatus AS Estatus,
		k_ventadirectadet.Cantidad AS Cantidad,
		k_ventadirectadet.TipoProducto AS Tipo,
		k_ventadirectadet.IdProduto AS IdProduto,
		k_ventadirectadet.Costo AS Costo
                FROM
                c_ventadirecta
                INNER JOIN k_ventadirectadet ON k_ventadirectadet.IdVentaDirecta=c_ventadirecta.IdVentaDirecta
                WHERE c_ventadirecta.IdVentaDirecta=" . $id);
    $rss = mysql_fetch_array($queryprin);
    ?>          
    <script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/Nueva_venta_directa.js"></script>
    <style>
        .size{width: 140px;}
    </style>   
    <form id="ventadirecta">
        <input type="hidden" name="NoVenta" id="NoVenta" value="<?php echo $id ?>"/>
        <table>
            <tr>
                <?php
                $catalogo = new Catalogo();
                $query = $catalogo->obtenerLista("SELECT c_puesto.IdPuesto FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario']);
                $rs = mysql_fetch_array($query);

                if ($rs['IdPuesto'] != 11) {
                    ?>
                    <td>
                        <label for="vendedor">Vendedor:</label>
                    </td>
                    <td>
                        <select id="vendedor" name="vendedor" class="filtro" width="200" style="width: 200px">
                            <option value="">Selecciona el vendedor</option>
                            <?php
                            $query = $catalogo->obtenerLista("SELECT c_usuario.IdUsuario,CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoPaterno,\" \",c_usuario.ApellidoMaterno) AS Nombre FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_puesto.IdPuesto=11 ORDER BY Nombre");
                            if (isset($_GET['vendedor'])) {
                                while ($rs = mysql_fetch_array($query)) {
                                    if ($rs['IdUsuario'] == $_GET['vendedor']) {
                                        echo "<option value=\"" . $rs['IdUsuario'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                                    }
                                }
                            } else {
                                while ($rs = mysql_fetch_array($query)) {
                                    echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <label for="cliente">Cliente:</label>
                    </td>
                    <td>
                        <select id="cliente" name="cliente" class="filtro" width="200" style="width: 200px" >
                            <?php
                            $query = $catalogo->obtenerLista("SELECT * FROM c_cliente WHERE c_cliente.ClaveCliente='" . $rss['ClaveCliente'] . "'");
                            $rs = mysql_fetch_array($query);
                            echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreRazonSocial'] . "</option>";
                            ?>
                        </select>
                    </td>
                    <?php
                } else {
                    ?>
                    <td>
                        <label for="cliente">Cliente:</label>
                    </td>
                    <td>
                        <input type="hidden" id="vendedor" name="vendedor" value="<?php echo $_SESSION['idUsuario']; ?>"/>
                        <select id="cliente" name="cliente" class="filtro" width="200" style="width: 200px" >
                            <?php
                            $query = $catalogo->obtenerLista("SELECT * FROM c_cliente WHERE c_cliente.ClaveCliente='" . $rss['ClaveCliente'] . "'");
                            $rs = mysql_fetch_array($query);
                            echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreRazonSocial'] . "</option>";
                            ?>
                        </select>
                    </td>
                <?php } ?>
                <td>Localidad</td>
                <td>
                    <select id="localidad" name="localidad" class="filtro localidad" width="200" style="width: 200px" >
                        <option value="">Selecciona la localidad</option>
                        <?php
                        $query = $catalogo->obtenerLista("SELECT c_centrocosto.ClaveCentroCosto AS ID,c_centrocosto.Nombre AS Nombre 
                            FROM c_centrocosto 
                            INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                            WHERE c_cliente.ClaveCliente='" . $rs['ClaveCliente'] . "'");
                        while ($rsp = mysql_fetch_array($query)) {
                            if ($rsp['ID'] == $rss['Localidad']) {
                                echo "<option value=\"" . $rsp['ID'] . "\" selected>" . $rsp['Nombre'] . "</option>";
                            } else {
                                echo "<option value=\"" . $rsp['ID'] . "\" >" . $rsp['Nombre'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </td>
            <tr/>
            <tr>
                <td><label for="fecha">Fecha</label></td>
                <td><input type="text" id="Fecha" name="Fecha" class="fecha" value="<?php echo $rss['Fecha'] ?>" width="200" style="width: 200px"/></td>
                <td></td><td></td>
            </tr>
        </table>        
        <br/><br/><br/>
        <table id="pedidos">
            <?php
            $queryprin = $catalogo->obtenerLista("SELECT c_ventadirecta.ClaveCliente AS ClaveCliente,
		c_ventadirecta.Fecha AS Fecha,
		c_ventadirecta.Estatus AS Estatus,
		k_ventadirectadet.Cantidad AS Cantidad,
		k_ventadirectadet.TipoProducto AS Tipo,
		k_ventadirectadet.IdProduto AS IdProduto,
		k_ventadirectadet.Costo AS Costo
                FROM c_ventadirecta
                INNER JOIN k_ventadirectadet ON k_ventadirectadet.IdVentaDirecta=c_ventadirecta.IdVentaDirecta
                WHERE c_ventadirecta.IdVentaDirecta=" . $id);
            $contador = 1;
            while ($rss = mysql_fetch_array($queryprin)) {
                ?>
                <tr>
                    <td>
                        <label for="numero<?php echo $contador ?>">
                            Cantidad
                        </label>
                    </td>
                    <td>
                        <input type="text" id="numero<?php echo $contador ?>" name="numero<?php echo $contador ?>" class="size" value="<?php echo $rss['Cantidad'] ?>"  style="width: 50px;" onkeyup="calcularcostocant('numero<?php echo $contador ?>', 'costo<?php echo $contador ?>', 'total<?php echo $contador ?>', 'costotro<?php echo $contador ?>');"/>
                    </td>
                    <td>
                        <label for="tipo<?php echo $contador ?>">
                            Tipo
                        </label>
                    </td>
                    <td>
                        <select id="tipo<?php echo $contador ?>" class="size filtro" name="tipo<?php echo $contador ?>" onchange="cambiarselectmodelo('tipo<?php echo $contador ?>', 'modelo<?php echo $contador ?>');" >
                            <option value="">Selecciona el tipo</option>
                            <?php
                            if ($rss['Tipo'] == 0) {
                                echo "<option value=\"0\" selected>Equipo</option>";
                                $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
                                while ($rs = mysql_fetch_array($query2)) {
                                    if ($rss['Tipo'] == $rs['ID']) {
                                        echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                                    }
                                }
                            } else {
                                echo "<option value=\"0\" >Equipo</option>";
                                $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
                                $query3 = $catalogo->obtenerLista("SELECT DISTINCT
                            c_tipocomponente.IdTipoComponente AS ID
                            FROM c_componente
                            INNER JOIN c_tipocomponente ON c_tipocomponente.IdTipoComponente=c_componente.IdTipoComponente
                            WHERE c_componente.NoParte='" . $rss['IdProduto'] . "'");
                                $rst = mysql_fetch_array($query3);
                                while ($rs = mysql_fetch_array($query2)) {
                                    if ($rst['ID'] == $rs['ID']) {
                                        echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <label for="modelo<?php echo $contador ?>">
                            Modelo
                        </label>
                    </td>
                    <td>
                        <select id="modelo<?php echo $contador ?>" name="modelo<?php echo $contador ?>" class="size filtro">
                            <?php
                            if ($rss['Tipo'] == 0) {
                                $query3 = $catalogo->obtenerLista("SELECT DISTINCT
                                c_equipo.Modelo AS Modelo,
                                c_equipo.NoParte AS Parte 
                                FROM c_equipo
                                ORDER BY Modelo");
                                while ($rsp = mysql_fetch_array($query3)) {
                                    if ($rsp['Parte'] == $rss['IdProduto']) {
                                        echo "<option value=\"" . $rsp['Parte'] . "\" selected>" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rsp['Parte'] . "\" >" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                                    }
                                }
                            } else {
                                $query3 = $catalogo->obtenerLista("SELECT DISTINCT
                                c_componente.Modelo AS Modelo,
                                c_componente.NoParte AS Parte 
                                FROM c_componente
                                ORDER BY Modelo");
                                while ($rsp = mysql_fetch_array($query3)) {
                                    if ($rsp['Parte'] == $rss['IdProduto']) {
                                        echo "<option value=\"" . $rsp['Parte'] . "\" selected>" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rsp['Parte'] . "\" >" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <label for="costo<?php echo $contador ?>">
                            Costo
                        </label>
                    </td>
                    <td>
                        <select id="costo<?php echo $contador ?>" name="costo<?php echo $contador ?>" class="size filtro" onchange="calcularcostop('numero1', 'costo1', 'total1', 'costotro1');">
                            <?php
                            $precio = 0;
                            if ($rss['Tipo'] == 0) {
                                $query3 = $catalogo->obtenerLista("SELECT c_precios_abc.Precio_A,c_precios_abc.Precio_B,c_precios_abc.Precio_C FROM c_equipo
INNER JOIN c_precios_abc ON c_precios_abc.Id_precio_abc=c_equipo.Id_precios_abc
        WHERE c_equipo.NoParte='" . $rss['IdProduto'] . "'");
                                if ($rsp = mysql_fetch_array($query3)) {
                                    echo "<option value=\"\">Selecciona el precio</option>";
                                    if ($rsp['Precio_A'] == $rss['Costo']) {
                                        echo "<option value=\"" . $rsp['Precio_A'] . "\" selected>Precio A: " . $rsp['Precio_A'] . "</option>";
                                        $precio = $rsp['Precio_A'];
                                    } else {
                                        echo "<option value=\"" . $rsp['Precio_A'] . "\" >Precio A: " . $rsp['Precio_A'] . "</option>";
                                    }
                                    if ($rsp['Precio_B'] != "") {
                                        if ($rsp['Precio_B'] == $rss['Costo']) {
                                            echo "<option value=\"" . $rsp['Precio_B'] . "\" selected>Precio B: " . $rsp['Precio_B'] . "</option>";
                                            $precio = $rsp['Precio_B'];
                                        } else {
                                            echo "<option value=\"" . $rsp['Precio_B'] . "\" >Precio B: " . $rsp['Precio_B'] . "</option>";
                                        }
                                    }
                                    if ($rsp['Precio_C'] != "") {
                                        if ($rsp['Precio_B'] == $rss['Costo']) {
                                            echo "<option value=\"" . $rsp['Precio_C'] . "\" selected>Precio C: " . $rsp['Precio_C'] . "</option>";
                                            $precio = $rsp['Precio_C'];
                                        } else {
                                            echo "<option value=\"" . $rsp['Precio_C'] . "\" >Precio C: " . $rsp['Precio_C'] . "</option>";
                                        }
                                    }
                                    if ($precio == 0) {
                                        echo "<option value=\"none\" selected >Otro</option>";
                                    } else {
                                        echo "<option value=\"none\" >Otro</option>";
                                    }
                                } else {
                                    echo "<option value=\"\">Selecciona otro</option>";
                                    echo "<option value=\"none\" >Otro</option>";
                                }
                            } else {
                                $query3 = $catalogo->obtenerLista("SELECT c_precios_abc.Precio_A,c_precios_abc.Precio_B,c_precios_abc.Precio_C FROM c_componente
INNER JOIN c_precios_abc ON c_precios_abc.Id_precio_abc=c_componente.Id_precios_abc
WHERE c_componente.NoParte='" . $rss['IdProduto'] . "'");
                                if ($rsp = mysql_fetch_array($query3)) {
                                    echo "<option value=\"\">Selecciona el precio</option>";
                                    if ($rsp['Precio_A'] == $rss['Costo']) {
                                        echo "<option value=\"" . $rsp['Precio_A'] . "\" selected>Precio A: " . $rsp['Precio_A'] . "</option>";
                                        $precio = $rsp['Precio_A'];
                                    } else {
                                        echo "<option value=\"" . $rsp['Precio_A'] . "\" >Precio A: " . $rsp['Precio_A'] . "</option>";
                                    }
                                    if ($rsp['Precio_B'] != "") {
                                        if ($rsp['Precio_B'] == $rss['Costo']) {
                                            echo "<option value=\"" . $rsp['Precio_B'] . "\" selected>Precio B: " . $rsp['Precio_B'] . "</option>";
                                            $precio = $rsp['Precio_B'];
                                        } else {
                                            echo "<option value=\"" . $rsp['Precio_B'] . "\" >Precio B: " . $rsp['Precio_B'] . "</option>";
                                        }
                                    }
                                    if ($rsp['Precio_C'] != "") {
                                        if ($rsp['Precio_B'] == $rss['Costo']) {
                                            echo "<option value=\"" . $rsp['Precio_C'] . "\" selected>Precio C: " . $rsp['Precio_C'] . "</option>";
                                            $precio = $rsp['Precio_C'];
                                        } else {
                                            echo "<option value=\"" . $rsp['Precio_C'] . "\" >Precio C: " . $rsp['Precio_C'] . "</option>";
                                        }
                                    }
                                    if ($precio == 0) {
                                        echo "<option value=\"none\" selected >Otro</option>";
                                    } else {
                                        echo "<option value=\"none\" >Otro</option>";
                                    }
                                } else {
                                    echo "<option value=\"\">Selecciona otro</option>";
                                    echo "<option value=\"none\" >Otro</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td id="otrolabel<?php echo $contador ?>">
                        <label for="costotro<?php echo $contador ?>">
                            Otro
                        </label>
                    </td>
                    <td id="otroinput<?php echo $contador ?>">
                        <input type="text" id="costotro<?php echo $contador ?>" name="costotro<?php echo $contador ?>" onkeyup="calcularcosto('numero1', 'costotro1', 'total1');" <?php
                    if ($precio == 0) {
                        echo "value=\"" . $rss['Costo'] . "\"";
                        $llamadas.="showcosto('" . $contador . "');";
                    } else {
                        echo "value=\"0\"";
                        $llamadas.="hidecosto('" . $contador . "');";
                    }
                            ?>>
                    </td>
                    <td>
                        <label for="total<?php echo $contador ?>">
                            Total
                        </label>
                    </td>
                    <td>
                        <input type="text" id="total<?php echo $contador ?>" name="total<?php echo $contador ?>" class="size" value="<?php echo $rss['Costo'] * $rss['Cantidad'] ?>" style="width: 50px;" readonly="readonly"/>
                    </td>
                </tr>
                <?php
                $contador++;
            }
            ?>
        </table>
        <img class="imagenMouse" src="resources/images/Erase.png" title="Borrar fila" onclick='eliminarfilaulti();' style="float: right; cursor: pointer;" /><img class="imagenMouse" src="resources/images/add.png" title="Nueva fila" onclick='agregarcamposol();' style="float: right; cursor: pointer;" />  
        <br/><br/>
        <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
        <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('ventas/Ventas_Directas.php<?php echo $urlextra ?>', 'Ventas directas');"/>
    </form>

    <script>
    <?php
    echo "setcontador(" . $contador . ");";
    echo "setpaginaExito('ventas/Ventas_Directas.php" . $urlextra . "');";
    echo $llamadas;
    ?>
    </script>
<?php } ?>
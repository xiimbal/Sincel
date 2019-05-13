<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");

$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT c_puesto.IdPuesto FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario']);
$rs = mysql_fetch_array($query);
$vendedor = false;
if ($rs['IdPuesto'] == 11) {
    $vendedor = true;
}
$llamar = false;
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/menu_mttos.js"></script>
 <!--link responsivo-->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/fontawesome/css/all.min.css" rel="stylesheet"> 

<br/><br/>
  
<div class="container-fluid">
      <div class="form-row">
        <!--Vendedor-->
    <?php if (!$vendedor) { ?>
        <div class="form-group col-md-4">
            <label for="vendedor">Vendedor</label>
            <td>
                <select class="form-control" id="vendedor" name="vendedor"  onchange="cargarclientes('vendedor', 'cliente');">
                    <?php
                    $query = $catalogo->obtenerLista("SELECT CONCAT(c_usuario.Nombre,' ',c_usuario.ApellidoPaterno,' ',c_usuario.ApellidoMaterno) AS Nombre,
                    c_usuario.IdUsuario AS ID FROM c_usuario
                    INNER JOIN c_puesto on c_usuario.IdPuesto=c_puesto.IdPuesto
                    WHERE c_puesto.IdPuesto=11
                    ORDER BY Nombre");
                    echo "<option value=''>Todos los vendedores</option>";
                    if (isset($_GET['vendedor']) && $_GET['vendedor'] != "") {
                        $llamar = true;
                        while ($rs = mysql_fetch_array($query)) {
                            if ($_GET['vendedor'] == $rs['ID']) {
                                echo "<option value='" . $rs['ID'] . "' selected>" . $rs['Nombre'] . "</option>";
                            } else {
                                echo "<option value='" . $rs['ID'] . "' >" . $rs['Nombre'] . "</option>";
                            }
                        }
                    } else {
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value='" . $rs['ID'] . "' >" . $rs['Nombre'] . "</option>";
                        }
                    }
                    ?> 
                </select>
            </div>       
            <?php
        } else {
            echo "<td></td><td></td>";
        }
        ?>
    <!--Cliente-->            
    <div class="form-group col-md-4">
        <label for="cliente">Cliente</label>
        <select  class="form-control" id="cliente" name="cliente" onchange="/*cargarlocalidades('cliente', 'localidad');*/">
            <?php
                echo "<option value=''>Todos los clientes</option>";
                if (isset($_GET['cliente']) && $_GET['cliente'] != "") {
                    $aux = "";
                    if (isset($_GET['vendedor']) && $_GET['vendedor'] != "") {
                        $aux = $_GET['vendedor'];
                    } else {
                        $aux = $_SESSION['idUsuario'];
                    }
                    $query = $catalogo->obtenerLista("SELECT DISTINCT
                                c_cliente.NombreRazonSocial AS cliente,
                                c_cliente.ClaveCliente AS ID,
                                IF(ISNULL(c_mantenimiento.IdMantenimiento),'(Sin mttos)','') AS mtto
                                FROM
                                    c_mantenimiento
                                RIGHT JOIN c_centrocosto ON c_centrocosto.ClaveCentroCosto = c_mantenimiento.ClaveCentroCosto
                                RIGHT JOIN c_cliente ON c_cliente.ClaveCliente = c_centrocosto.ClaveCliente
                                WHERE c_cliente.EjecutivoCuenta='" . $aux . "'
                                GROUP BY cliente
                                ORDER BY cliente");
                        $llamar = true;
                        while ($rs = mysql_fetch_array($query)) {
                            if ($_GET['cliente'] == $rs['ID']) {
                                echo "<option value='" . $rs['ID'] . "' selected>" . $rs['cliente'] . " " . $rs['mtto'] . "</option>";
                            } else {
                                echo "<option value='" . $rs['ID'] . "' >" . $rs['cliente'] . " " . $rs['mtto'] . "</option>";
                            }
                        }
                } elseif ($vendedor) {
                    $query = $catalogo->obtenerLista("SELECT DISTINCT
                                c_cliente.NombreRazonSocial AS cliente,
                                c_cliente.ClaveCliente AS ID,
                                IF(ISNULL(c_mantenimiento.IdMantenimiento),'(Sin mttos)','') AS mtto
                                FROM
                                c_mantenimiento
                                RIGHT JOIN c_centrocosto ON c_centrocosto.ClaveCentroCosto = c_mantenimiento.ClaveCentroCosto
                                RIGHT JOIN c_cliente ON c_cliente.ClaveCliente = c_centrocosto.ClaveCliente
                                WHERE c_cliente.EjecutivoCuenta='" . $_SESSION['idUsuario'] . "'
                                GROUP BY cliente
                                ORDER BY cliente");
                    while ($rs = mysql_fetch_array($query)) {
                        $s = "";
                        if ($_GET['cliente'] == $rs['ID']) {
                            $s = "selected";
                        }
                        echo "<option value='" . $rs['ID'] . "' $s>" . $rs['cliente'] . " " . $rs['mtto'] . "</option>";
                    }
                }
            ?> 
            </select>
    </div>
    <!--Localidad-->
    <div class="form-group col-md-4">
        <label for="localidad">Localidad</label>
        <select class="form-control" id="localidad" name="localidad">
            <option value="" >Todos las localidades</option>
            <?php
            if (isset($_GET['localidad']) && $_GET['localidad'] != "") {
                $query3 = $catalogo->obtenerLista("SELECT c_centrocosto.ClaveCentroCosto AS ID,c_centrocosto.Nombre AS Nombre 
                        FROM c_centrocosto 
                        INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                        WHERE c_cliente.ClaveCliente='" . $_GET['cliente'] . "'");
                echo "<option value=\"\" >Todas las localidades</option>";
                while ($rsp = mysql_fetch_array($query3)) {
                    if ($_GET['localidad'] == $rsp['ID']) {
                        echo "<option value=\"" . $rsp['ID'] . "\" selected>" . $rsp['Nombre'] . "</option>";
                    } else {
                        echo "<option value=\"" . $rsp['ID'] . "\" >" . $rsp['Nombre'] . "</option>";
                    }
                }
            }
            ?>
        </select>
    </div>
    <!--No. Serie-->
    <div class="form-group col-md-4">
        <label for="NoSerie">No. Serie</label>
        <input  class="form-control"class="form-control" type="text" id="NoSerie" name="NoSerie" value="<?php
            if (isset($_GET['noserie']) && $_GET['noserie'] != "") {
                $llamar = true;
                echo $_GET['noserie'];
            }
        ?>"/>
    </div>
    <!--Fecha-->
    <div class="form-group col-md-4">
        <label for="fecha1">Fecha</label>
        <input class="form-control" type="text" id="fecha1" name="Fecha1" class="fecha" value="<?php
            if (isset($_GET['fecha1']) && $_GET['fecha1'] != "") {
                $llamar = true;
                echo $_GET['fecha1'];
            }
        ?>"/>
    </div>
    <!--Hasta-->
    <div class="form-group col-md-4">
        <label for="fecha2">Hasta</label>
        <input class="form-control" type="text" id="fecha2" name="Fecha2" class="fecha" value="<?php
            if (isset($_GET['fecha2']) && $_GET['fecha2'] != "") {
                $llamar = true;
                echo $_GET['fecha2'];
            }    
        ?>"/>
    </div>
    <!--Enviar-->
    
        <input type="button" id="enviar" value="Mostrar" class="button btn btn-lang btn-block btn-outline-secondary mt-3 mb-3" onclick="enviardatos();"/>
    
</div>
</div>    
  
    <!--<tr>
        <?php if (!$vendedor) { ?>
            <td>Vendedor</td>
            <td>
                <select id="vendedor" name="vendedor" style="width: 200px;" onchange="cargarclientes('vendedor', 'cliente');">
                    <?php
                    $query = $catalogo->obtenerLista("SELECT CONCAT(c_usuario.Nombre,' ',c_usuario.ApellidoPaterno,' ',c_usuario.ApellidoMaterno) AS Nombre,
                    c_usuario.IdUsuario AS ID FROM c_usuario
                    INNER JOIN c_puesto on c_usuario.IdPuesto=c_puesto.IdPuesto
                    WHERE c_puesto.IdPuesto=11
                    ORDER BY Nombre");
                    echo "<option value=''>Todos los vendedores</option>";
                    if (isset($_GET['vendedor']) && $_GET['vendedor'] != "") {
                        $llamar = true;
                        while ($rs = mysql_fetch_array($query)) {
                            if ($_GET['vendedor'] == $rs['ID']) {
                                echo "<option value='" . $rs['ID'] . "' selected>" . $rs['Nombre'] . "</option>";
                            } else {
                                echo "<option value='" . $rs['ID'] . "' >" . $rs['Nombre'] . "</option>";
                            }
                        }
                    } else {
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value='" . $rs['ID'] . "' >" . $rs['Nombre'] . "</option>";
                        }
                    }
                    ?> 
                </select>
            </td>       
            <?php
        } else {
            echo "<td></td><td></td>";
        }
        ?>
        <td>Cliente</td>
        <td>
            <select id="cliente" name="cliente" style="width: 200px;" onchange="/*cargarlocalidades('cliente', 'localidad');*/">
                <?php
                echo "<option value=''>Todos los clientes</option>";
                if (isset($_GET['cliente']) && $_GET['cliente'] != "") {
                    $aux = "";
                    if (isset($_GET['vendedor']) && $_GET['vendedor'] != "") {
                        $aux = $_GET['vendedor'];
                    } else {
                        $aux = $_SESSION['idUsuario'];
                    }
                    $query = $catalogo->obtenerLista("SELECT DISTINCT
	c_cliente.NombreRazonSocial AS cliente,
	c_cliente.ClaveCliente AS ID,
	IF(ISNULL(c_mantenimiento.IdMantenimiento),'(Sin mttos)','') AS mtto
FROM
	c_mantenimiento
RIGHT JOIN c_centrocosto ON c_centrocosto.ClaveCentroCosto = c_mantenimiento.ClaveCentroCosto
RIGHT JOIN c_cliente ON c_cliente.ClaveCliente = c_centrocosto.ClaveCliente
WHERE c_cliente.EjecutivoCuenta='" . $aux . "'
GROUP BY cliente
ORDER BY cliente");
                    $llamar = true;
                    while ($rs = mysql_fetch_array($query)) {
                        if ($_GET['cliente'] == $rs['ID']) {
                            echo "<option value='" . $rs['ID'] . "' selected>" . $rs['cliente'] . " " . $rs['mtto'] . "</option>";
                        } else {
                            echo "<option value='" . $rs['ID'] . "' >" . $rs['cliente'] . " " . $rs['mtto'] . "</option>";
                        }
                    }
                } elseif ($vendedor) {
                    $query = $catalogo->obtenerLista("SELECT DISTINCT
	c_cliente.NombreRazonSocial AS cliente,
	c_cliente.ClaveCliente AS ID,
	IF(ISNULL(c_mantenimiento.IdMantenimiento),'(Sin mttos)','') AS mtto
FROM
	c_mantenimiento
RIGHT JOIN c_centrocosto ON c_centrocosto.ClaveCentroCosto = c_mantenimiento.ClaveCentroCosto
RIGHT JOIN c_cliente ON c_cliente.ClaveCliente = c_centrocosto.ClaveCliente
WHERE c_cliente.EjecutivoCuenta='" . $_SESSION['idUsuario'] . "'
GROUP BY cliente
ORDER BY cliente");
                    while ($rs = mysql_fetch_array($query)) {
                        $s = "";
                        if ($_GET['cliente'] == $rs['ID']) {
                            $s = "selected";
                        }
                        echo "<option value='" . $rs['ID'] . "' $s>" . $rs['cliente'] . " " . $rs['mtto'] . "</option>";
                    }
                }
                ?> 
            </select>
        </td>
        <td>Localidad</td>
        <td>
            <select id="localidad" name="localidad" style="width: 200px;" >
                <option value="" >Todos las localidades</option>
                <?php
                if (isset($_GET['localidad']) && $_GET['localidad'] != "") {
                    $query3 = $catalogo->obtenerLista("SELECT c_centrocosto.ClaveCentroCosto AS ID,c_centrocosto.Nombre AS Nombre 
FROM c_centrocosto 
INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
WHERE c_cliente.ClaveCliente='" . $_GET['cliente'] . "'");
                    echo "<option value=\"\" >Todas las localidades</option>";
                    while ($rsp = mysql_fetch_array($query3)) {
                        if ($_GET['localidad'] == $rsp['ID']) {
                            echo "<option value=\"" . $rsp['ID'] . "\" selected>" . $rsp['Nombre'] . "</option>";
                        } else {
                            echo "<option value=\"" . $rsp['ID'] . "\" >" . $rsp['Nombre'] . "</option>";
                        }
                    }
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td>NoSerie</td>
        <td>
            <input type="text" id="NoSerie" name="NoSerie" value="<?php
            if (isset($_GET['noserie']) && $_GET['noserie'] != "") {
                $llamar = true;
                echo $_GET['noserie'];
            }
            ?>"/>
        </td>
        <td>Fecha</td>
        <td>
            <input type="text" id="fecha1" name="Fecha1" class="fecha" value="<?php
            if (isset($_GET['fecha1']) && $_GET['fecha1'] != "") {
                $llamar = true;
                echo $_GET['fecha1'];
            }
            ?>"/>
        </td>
        <td>hasta</td>
        <td>
            <input type="text" id="fecha2" name="Fecha2" class="fecha" value="<?php
            if (isset($_GET['fecha2']) && $_GET['fecha2'] != "") {
                $llamar = true;
                echo $_GET['fecha2'];
            }
            ?>"/>
        </td>
    </tr>
</table>
<input type="button" id="enviar" value="Mostrar" class="boton" style="margin-left: 83%;" onclick="enviardatos();"/>
-->
<?php if(isset($_GET['regresar']) && $_GET['regresar'] != ""){?>
<input type="button" class="boton" onclick="cambiarContenidos('<?php echo $_GET['regresar']?>','');" value="Regresar">
<?php }?>
<div id="tablainfo"></div>
<input type="hidden" id="client" value="<?php echo $_GET['cliente']?>">
<?php
if ($llamar) {
    echo "<script type=\"text/javascript\" language=\"javascript\">enviardatos();</script>";
}
echo "<script type=\"text/javascript\" language=\"javascript\">cargarclientes('vendedor', 'cliente');</script>";
?>

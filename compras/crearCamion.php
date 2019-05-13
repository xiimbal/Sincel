<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Contacto.class.php");
include_once("../WEB-INF/Classes/Proveedor.class.php");
// include_once("../WEB-INF/Classes/AutorizarEspecial.class.php");
echo "hOLA";
include_once("../WEB-INF/Classes/DetalleEspecial.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Orden_Compra.class.php");
include_once("../WEB-INF/Classes/Almacen.class.php");
include_once("../WEB-INF/Classes/AutorizarEspecial.class.php");

$catalogo = new Catalogo();
$contacto = new Contacto();
$proveedor = new Proveedor();
$especial = new AutorizarEspecial();
$detalleEspecial = new DetalleEspecial();
$almacen = new Almacen();

$id = "";
$idChofer = 0;
$idAlmacen = 0;
$contadorOc = 0;
$labelBoton = "Crear camión";
$pesoBruto = "";
$tara = "";
$neto = "";
$costoTotal = "";
$destino = "";
$calle = "";
$noExterior = "";
$noInterior = "";
$colonia = "";
$ciudad = "";
$delegacion = "";
$cp = "";
$estado = "";
$latitud = "";
$longitud = "";
$comentarios = "";
$tab = "";
$ticket_id = "";
$almacenBoolean = false;
$chofer = $_GET['idTicket'];
echo "Ticket ".$_POST['idTicket'];
if (isset($_POST['idTicket']) || $chofer !="" ) {
    if (isset($_POST['idTicket'])) {
        $ticket_id = $_POST['idTicket'];
    } else{
        $ticket_id = $chofer;
    }    
    $labelBoton = "Editar camión";
    echo $ticket_id;
    $especial->getRegistroByIdTicket($ticket_id);
    $id = $especial->getIdEspecial();
    $detalleEspecial->getRegistroByIdEspecial($especial->getIdEspecial());
    $idChofer = $especial->getIdEmpleado();
    $idAlmacen = $especial->getIdAlmacen();
    if(!isset($idAlmacen) || empty($idAlmacen)){
        $tab = "<input type='hidden' id='tab' name='tab' value='1' />";
        $destino = $especial->getDestino();
        $calle = $especial->getCalle_or();
        $noExterior = $especial->getExterior_or();
        $noInterior = $especial->getInterior_or();
        $colonia = $especial->getColonia_or();
        $ciudad = $especial->getCiudad_or();
        $delegacion = $especial->getDelegacion_or();
        $cp = $especial->getCp_or();
        $estado = $especial->getEstado_or();
        $latitud = $especial->getLatitud_or();
        $longitud = $especial->getLongitud_or();
        $comentarios = $especial->getComentario_or();
    }
    $pesoBruto = $detalleEspecial->getPesoBruto();
    $tara = $detalleEspecial->getTara();
    $neto = $detalleEspecial->getNeto();
    $costoTotal = $detalleEspecial->getCostoTotal();
    
    if($almacen->getRegistroByNombre($ticket_id)){
        $almacenBoolean = true;
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/compras/crearCamion.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/ventas/alta_contacto.js"></script>
    </head>
    <div class="p-4  rounded">
    <body>

        <form id="frmCamion" name="frmCamion" class="p-2">
            <!--div class="container"-->
            <button class="btn btn-info" title="Nuevo Chofer" onclick='cambiarContenidosContacto("../cliente/validacion/alta_contacto_pakal.php?Cliente=<?php echo "10002"?>&idTicket=<?php echo $ticket_id; ?>");' style="float: right; cursor: pointer;"><i class="fal fa-plus-circle"></i></button>
                <div class="form-row">
                    <div class="form-group col-md-3">
                            <label for="chofer">Chofer</label>
                            <select id="chofer" name="chofer" class="custom-select">
                                <option value="">Selecciona un chofer</option>
                                <?php
                                    $result = $contacto->getContactosPorIdTipo(7);
                                    while($rs = mysql_fetch_array($result)){
                                        $s = "";
                                        if((int)$idChofer == (int)$rs['IdContacto']){
                                            $s = "selected";
                                        }
                                        echo "<option value='".$rs['IdContacto']."' $s>".$rs['Nombre']."</option>";
                                    }
                                ?>
                            </select>
                    </div>
                    <div class="form-group col-md-3">
                            <label for="pesoBruto">Peso Bruto </label>
                            <input class="form-control" type="text" name="pesoBruto" id="pesoBruto" value="<?php echo $pesoBruto ?>"/>
                    </div>
                    <div class="form-group col-md-3">
                            <label for="neto">Neto </label>
                            <input class="form-control" type="text" name="neto" id="neto" value="<?php echo $neto ?>"/>
                    </div>
                    <div class="form-group col-md-3">
                           <label for="total">Costo Total </label>
                           <input class="form-control" type="text" name="costoTotal" id="costoTotal" value="<?php echo $costoTotal ?>"/>
                    </div>
                    
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3 btn-block">
                           <input type="button" class="btn btn-success" id="crearCamion" name="crearCamion" value="<?php echo $labelBoton; ?>" onclick="creandoCamion(); return false;">
                    </div>
                </div>
            <section>
                <h3>Domicilio destino</h3>
            </section>
            <fieldset >
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
                                    $s = "";
                                    if((int)$idAlmacen == (int)$rs['id_almacen']){
                                        $s = "selected";
                                    }
                                    echo "<option value='".$rs['id_almacen']."' $s>".$rs['nombre_almacen']."</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div id="tabs-2" style="background-color: #A4A4A4">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="destino">Nombre destino</label>
                                <input class="form-control" type="text" id="destino" name="destino" value="<?php echo $destino ?>"/>
                                <label for="ciudad">Ciudad</label>
                                <input class="form-control" type="text" id="ciudad" name="ciudad" value="<?php echo $ciudad ?>" />
                                <label for="comentarios">Comentarios</label>
                                <textarea class="form-control" name="comentarios" id="comentarios"><?php echo $comentarios ?></textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="calle">Calle</label>
                                <input class="form-control" type="text" id="calle" name="calle" value="<?php echo $calle ?>" />
                                <label for="delegacion">Delegación</label>
                                <input class="form-control" type="text" id="delegacion" name="delegacion" value="<?php echo $delegacion ?>"/>
                                <label for="cp">C.P.</label>
                                <input class="form-control" type="text" id="cp" name="cp" value="<?php echo $cp ?>"/>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="exterior">No. Exterior</label>
                                <input class="form-control" type="text" id="exterior" name="exterior" value="<?php echo $noExterior ?>"/>
                                <label for="estado">Estado</label>
                                <input class="form-control" type="text" id="estado" name="estado" value="<?php echo $estado ?>" />
                                <label for="interior">No. Interior</label>
                                <input class="form-control" type="text" id="interior" name="interior" value="<?php echo $noInterior ?>" />
                            </div>
                            <div class="form-group col-md-6">
                                <label for="latitud">Latitud</label>
                                <input class="form-control" type="text" id="" name="latitud" value="<?php echo $latitud ?>" />
                                <label for="colonia">Colonia</label>
                                <input class="form-control" type="text" id="colonia" name="colonia" value="<?php echo $colonia ?>"/>
                                <label for="longitud">Longitud</label>
                                <input class="form-control" type="text" id="longitud" name="longitud" value="<?php echo $longitud ?>"/>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <h2>Proveedores</h2>
                <div id="ocs" class="row">
                    <?php 
                        if (isset($_POST['idTicket']) || $chofer != "") {
                            $consultaOC = "SELECT oc.Id_orden_compra, p.NombreComercial, toc.Posicion
                                FROM k_tickets_oc toc
                                LEFT JOIN c_orden_compra oc ON oc.Id_orden_compra = toc.IdOrdenCompra
                                LEFT JOIN c_proveedor p ON p.ClaveProveedor = oc.FacturaEmisor
                                WHERE IdTicket = ".$especial->getIdTicket() .
                                " ORDER BY toc.Posicion";
                            $resultOC = $catalogo->obtenerLista($consultaOC);
                            while($rsOC = mysql_fetch_array($resultOC)){
                                echo "<div id='div_oc_".$contadorOc."' class='col-lg-12 col-md-12 col-sm-12' style=''>" .
                                    "<input type='hidden' name='oc_".$contadorOc."' id='oc_".$contadorOc."' value='".$rsOC['Id_orden_compra']."' />".
                                    "<table style='width:90%;'><tr>" .
                                    "<td style='width:30%;'><h4>Proveedor: ".$rsOC['NombreComercial']." </h4></td>" .
                                    "<td style='width:30%;'>Orden en que se cargará camion: <input type='text' id='posicion_".$contadorOc."' name='posicion_".$contadorOc."' value='".$rsOC['Posicion']."'/></td>" .
                                    "<td style='width:20%;'>" .
                                    "<input type=\"image\" height=\"24px\" width=\"24px\" src=\"resources/images/ver.png\" title=\"Modificar\" onclick=\"lanzarPopUp('Orden compra','compras/verOCCamion.php?id=".$rsOC['Id_orden_compra']."&ticket_id=$ticket_id'); return false;\"/>" .
                                    "</td>" .
                                    "<td style='width:20%;'>" .
                                    "<input type='image' src='resources/images/Erase.png' title='Eliminar' onclick='eliminarOc(".$contadorOc."); return false;'/>" .
                                    "</td>" .
                                    "</tr></table>" .
                                    "</div>";
                                $contadorOc++;
                            }
                        }
                    ?>
                </div>
            </fieldset>
            <input type='hidden' name='contador' id='contador' value='<?php echo $contadorOc; ?>' />
            <input type='hidden' name='id' id='id' value='<?php echo $id; ?>' />
            <input type='hidden' name='ticket_id' id='ticket_id' value='<?php echo $ticket_id; ?>' />
            <?php echo $tab; ?>
        </form>
        <form id="frmOc" name="frmOc">
            <label for="proveedor">Proveedor </label>
            <select id="proveedor" name="proveedor" class="select">
                <option value="">Selecciona un proveedor</option>
                <?php
                    $result = $proveedor->getUsuarios();
                    while($rs = mysql_fetch_array($result)){
                        echo "<option value='".$rs['ClaveProveedor']."'>".$rs['NombreComercial']."</option>";
                    }
                ?>
            </select>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <button class="btn btn-info" title="Agregar Orden Compra" onclick="agregarOc(); return false;"  style="margin-bottom: -10px;"><i class="fal fa-plus-circle"></i></button>
            <!--input type="image" src="resources/images/add.png" title="Agregar Orden Compra" onclick="agregarOc(); return false;"  style="margin-bottom: -10px;"/-->
            <br/><br/>
                <table id="tProductos" class="table table-responsive">
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
                        <select name="empaque" id="empaque" class="select" onchange="cargarProductos(); return false;">
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
                        <select name="producto" id="producto" class="select">
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
                        <a class="btn btn-info text-light" onclick="agregarProducto(); return false;" style="cursor: pointer;margin-bottom: -10px;"><i class="fal fa-plus-circle"></i><!--img src="resources/images/add.png" title="Agregar"/--></a>
                    </td>
                </tr>
                </table>
        </form>
    </body>
</div>
</html>
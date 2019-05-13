<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
$controladorZona = $_SESSION['ruta_controler'] . "Controler_ProveedorZona.php";
$same_pageZona = "admin/lista_detalle_proveedor.php?tipo=zona";
$altaZona = "admin/alta_proveedorZona.php";

$controladorProducto = $_SESSION['ruta_controler'] . "Controler_ProveedorProducto.php";
$same_pageProducto = "admin/lista_detalle_proveedor.php?tipo=producto";
$altaProducto = "admin/alta_proveedorProducto.php";

$controladorServicio = $_SESSION['ruta_controler'] . "Controler_ProveedorServicio.php";
$same_pageServicio = "admin/lista_detalle_proveedor.php?tipo=servicio";
$altaServicio = "admin/alta_proveedorServicio.php";

$zona = "";
$producto = "";
$servicio = "";
echo "";
if (isset($_GET['tipo'])) {
    if ($_GET['tipo'] == "zona") {
        $zona = "active";
    } else if ($_GET['tipo'] == "producto") {
        $producto = "active";
    } else if ($_GET['tipo'] == "servicio") {
        $servicio = "active";
    }
} else {
    header("Location: index.php");
}
if (isset($_POST['id2']))
    $proveedor = $_POST['id2'];

$catalogo1 = new Catalogo();
$query1 = $catalogo1->obtenerLista("SELECT s.Descripcion FROM c_sucursal s WHERE s.ClaveSucursal='".$_POST['id']."'");
if ($rs = mysql_fetch_array($query1)) {
    echo "Sucursal: <b>" . $rs['Descripcion'] . " </b>";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Techra</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="description" content="Expand, contract, animate forms with jQuery wihtout leaving the page" />
        <meta name="keywords" content="expand, form, css3, jquery, animate, width, height, adapt, unobtrusive javascript"/>
        

        <link rel="stylesheet" type="text/css" href="resources/css/login/style.css" />
        <script src="resources/js/login/cufon-yui.js" type="text/javascript"></script>
        <script src="resources/js/login/ChunkFive_400.font.js" type="text/javascript"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/proveedorZona.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/proveedorProducto.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/proveedorServicio.js"></script>
        <script type="text/javascript" src="resources/js/funciones.js"></script>
        <script type="text/javascript">
            Cufon.replace('h1', {textShadow: '1px 1px #fff'});
            Cufon.replace('h2', {textShadow: '1px 1px #fff'});
            Cufon.replace('h3', {textShadow: '1px 1px #000'});
            Cufon.replace('.back');
        </script>
        <style>
            /*.dataTables_filter {
                    width: 65%;
                    float: right;
                    text-align: right;
            }
            div.dataTables_wrapper .ui-widget-header {
                max-width: 100%;
                height: 50px;
                color: white;
                font-weight: normal;
            }*/
        </style>
    </head>
    <body>
        <div class="wrapper">
            <div class="content" style='max-width: 900px;'>
                <div id="form_wrapper" class="form_wrapper" style='max-width: 900px;'>
                    <div class="zonas form_switched <?php echo $zona; ?>" style="width: 900px">
                        <h2>Zona</h2>
                        <div>
                            <a href="index.php" rel="servicios" class="linkform" style="float: right; margin-right: 15px;">Servicos del proveedor <span class="ui-icon ui-icon-arrowthick-1-e"></span></a>                            
                            <a href="index.php" rel="productos" class="linkform">Productos del proveedor<span class="ui-icon ui-icon-arrowthick-1-w"></span></a>
                            <br/>
                        </div>
                        <div>
                            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidosProv("<?php echo $altaZona; ?>", "<?php echo $_POST['id']; ?>");' style="float: right; cursor: pointer;" />  
                            <br/><br/><br/>
                            <table id="tProvZona" name="tProvZona" class="tabla_datos" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <td align='center' scope='row'>Sucursal</td><td align='center' scope='row'>Zona</td><td align='center' scope='row'>Tiempo maximo solucion</td><td></td><td></td>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $catalogo = new Catalogo();
                                    $query = $catalogo->obtenerLista("SELECT s.ClaveSucursal,z.ClaveZona ,s.Descripcion,z.NombreZona,pz.TiempoMaximoSolucion
                                                                        FROM c_zona z, c_sucursal s, k_proveedorzona pz
                                                                        where pz.ClaveZona=z.ClaveZona 
                                                                        AND pz.IdSucursal=s.ClaveSucursal
                                                                        AND pz.IdSucursal='" . $_POST['id'] . "'
                                                                        ORDER BY s.Descripcion");
                                    while ($rs = mysql_fetch_array($query)) {
                                        echo "<tr>";
                                        echo "<td align='center' scope='row'>" . $rs['Descripcion'] . "</td>";
                                        echo "<td align='center' scope='row'>" . $rs['NombreZona'] . "</td>";
                                        echo "<td align='center' scope='row'>" . $rs['TiempoMaximoSolucion'] . "</td>";
                                        ?>



                                    <td align='center' scope='row'> <a href='#' onclick='editarRegistroProv("<?php echo $altaZona; ?>", "<?php echo $rs['ClaveSucursal']; ?>", "<?php echo $rs['ClaveZona']; ?>");
                    return false;' title='Editar Registro' ><img src="resources/images/Modify.png"/></a></td>

                                    <td align='center' scope='row'> <a href='#' onclick='eliminarRegistroProv("<?php echo $controladorZona . "?id=" . $rs['ClaveSucursal'] . "&id2=" . $rs['ClaveZona'] ?>", "<?php echo $rs['ClaveSucursal']; ?>", "<?php echo $same_pageZona; ?>");
                    return false;'><img src="resources/images/Erase.png"/></a> </td> 
                                        <?php
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>                                 
                            </table>
                            <br/><br/>
                            <input type="button" value="Regresar" class="boton" style="float: right" onclick='editarRegistro("admin/lista_sucursal.php", "<?php echo $proveedor ?>");'/>
                        </div>

                    </div>
                    <div class="productos form_switched <?php echo $producto; ?> " style="width: 900px">
                        <h2>Productos</h2>
                        <div>
                            <a href="index.php" rel="zonas" class="linkform" style="float: right; margin-right: 15px;">Zonas del proveedor <span class="ui-icon ui-icon-arrowthick-1-e"></span></a>
                            <a href="index.php" rel="servicios" class="linkform">Servicios del proveedor<span class="ui-icon ui-icon-arrowthick-1-w"></span></a>
                            <br/>
                        </div>
                        <div>
                            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidosProv("<?php echo $altaProducto; ?>", "<?php echo $_POST['id']; ?>");' style="float: right; margin-right: 25px;  cursor: pointer;" />  
                            <br/><br/><br/>
                            <table id="tProvProduc" name="tProvProduc" class="tabla_datos" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <td align='center' scope='row'>Sucursal</td><td align='center' scope='row'>Producto</td><td></td><td></td>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $catalogo = new Catalogo();
                                    $query = $catalogo->obtenerLista("SELECT pp.IdSucursal,pp.IdProducto,s.Descripcion,pr.Nombre
                                                                        FROM k_proveedorproducto pp,c_sucursal s,c_producto pr
                                                                        where pp.IdSucursal=s.ClaveSucursal
                                                                        AND pp.IdProducto=pr.IdProducto
                                                                        AND pp.IdSucursal = '" . $_POST['id'] . "'
                                                                        ORDER BY s.Descripcion");
                                    while ($rs = mysql_fetch_array($query)) {
                                        echo "<tr>";
                                        echo "<td align='center' scope='row'>" . $rs['Descripcion'] . "</td>";
                                        echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                                        ?>



                                    <td align='center' scope='row'> <a href='#' onclick='editarRegistroProv("<?php echo $altaProducto; ?>", "<?php echo $rs['IdSucursal']; ?>", "<?php echo $rs['IdProducto']; ?>");
                    return false;' title='Editar Registro' ><img src="resources/images/Modify.png"/></a></td>

                                    <td align='center' scope='row'> <a href='#' onclick='eliminarRegistroProv("<?php echo $controladorProducto . "?id=" . $rs['IdSucursal'] . "&id2=" . $rs['IdProducto'] ?>", "<?php echo $rs['IdSucursal']; ?>", "<?php echo $same_pageProducto; ?>");
                    return false;'><img src="resources/images/Erase.png"/></a> </td> 
                                        <?php
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>                                 
                            </table>
                            <br/><br/>
                            <input type="button" value="Regresar" class="boton" style="float: right" onclick='editarRegistro("admin/lista_sucursal.php", "<?php echo $proveedor ?>");'/>
                        </div>
                    </div>
                    <div class="servicios  form_switched <?php echo $servicio; ?> " style="width: 900px">
                        <h2>Servicios</h2>
                        <div>                            
                            <a href="index.php" rel="zonas" class="linkform" style="float: right; margin-right: 15px;">Zonas del proveedor <span class="ui-icon ui-icon-arrowthick-1-e"></span></a>
                            <a href="index.php" rel="productos" class="linkform">Productos del proveedor<span class="ui-icon ui-icon-arrowthick-1-w"></span></a>
                            <br/>
                        </div>
                        <div>
                            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidosProv("<?php echo $altaServicio; ?>", "<?php echo $_POST['id']; ?>");' style="float: right; cursor: pointer;" />  
                            <br/><br/><br/>
                            <table id="tProvServicio" name="tProvServicio" class="tabla_datos" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <td align='center' scope='row'>Sucursal</td><td align='center' scope='row'>Servicio</td><td></td><td></td>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $catalogo = new Catalogo();
                                    $query = $catalogo->obtenerLista("SELECT ps.IdSucursal,ps.IdServicio,su.Descripcion,s.Nombre
                                                                                    FROM k_proveedorservicio ps,c_sucursal su,c_servicio s
                                                                                    WHERE ps.IdSucursal=su.ClaveSucursal
                                                                                    AND ps.IdServicio=s.IdServicio
                                                                                    AND ps.IdSucursal = '" . $_POST['id'] . "'
                                                                                    ORDER BY su.Descripcion");
                                    while ($rs = mysql_fetch_array($query)) {
                                        echo "<tr>";
                                        echo "<td align='center' scope='row'>" . $rs['Descripcion'] . "</td>";
                                        echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                                        ?>



                                    <td align='center' scope='row'> <a href='#' onclick='editarRegistroProv("<?php echo $altaServicio; ?>", "<?php echo $rs['IdSucursal']; ?>", "<?php echo $rs['IdServicio']; ?>");
                    return false;' title='Editar Registro' ><img src="resources/images/Modify.png"/></a></td>

                                    <td align='center' scope='row'> <a href='#' onclick='eliminarRegistroProv("<?php echo $controladorServicio . "?id=" . $rs['IdSucursal'] . "&id2=" . $rs['IdServicio'] ?>", "<?php echo $rs['IdSucursal']; ?>", "<?php echo $same_pageServicio; ?>");
                    return false;'><img src="resources/images/Erase.png"/></a> </td> 
                                        <?php
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>                                 
                            </table>
                            <br/><br/>
                            <input type="button" value="Regresar" class="boton" style="float: right" onclick='editarRegistro("admin/lista_sucursal.php", "<?php echo $proveedor ?>");'/>
                        </div>
                    </div>                    
                </div>
                <div class="clear"></div>
            </div>    

        </div>


        <!-- The JavaScript -->
        <script type="text/javascript">
            $(function() {
                //the form wrapper (includes all forms)
                var $form_wrapper = $('#form_wrapper'),
                        //the current form is the one with class active
                        $currentForm = $form_wrapper.children('.form_switched.active'),
                        //the change form links
                        $linkform = $form_wrapper.find('.linkform');

                //get width and height of each form and store them for later						
                $form_wrapper.children('.form_switched').each(function(i) {
                    var $theForm = $(this);
                    //solve the inline display none problem when using fadeIn fadeOut
                    if (!$theForm.hasClass('active'))
                        $theForm.hide();
                    $theForm.data({
                        width: $theForm.width(),
                        height: $theForm.height()
                    });
                });

                //set width and height of wrapper (same of current form)
                setWrapperWidth();

                /*
                 clicking a link (change form event) in the form
                 makes the current form hide.
                 The wrapper animates its width and height to the 
                 width and height of the new current form.
                 After the animation, the new form is shown
                 */
                $linkform.bind('click', function(e) {
                    var $link = $(this);
                    var target = $link.attr('rel');
                    $currentForm.fadeOut(400, function() {
                        //remove class active from current form
                        $currentForm.removeClass('active');
                        //new current form
                        $currentForm = $form_wrapper.children('.form_switched.' + target);
                        //animate the wrapper
                        $form_wrapper.stop()
                                .animate({
                            width: $currentForm.data('width') + 'px',
                            height: $currentForm.data('height') + 'px'
                        }, 500, function() {
                            //new form gets class active
                            $currentForm.addClass('active');
                            //show the new form
                            $currentForm.fadeIn(400);
                        });
                    });
                    e.preventDefault();
                });

                function setWrapperWidth() {
                    $form_wrapper.css({
                        width: $currentForm.data('width') + 'px',
                        height: $currentForm.data('height') + 'px'
                    });
                }

                /*
                 for the demo we disabled the submit buttons
                 if you submit the form, you need to check the 
                 which form was submited, and give the class active 
                 to the form you want to show
                 */
                /*$form_wrapper.find('input[type="submit"]')
                 .click(function(e) {
                 e.preventDefault();
                 });*/
            });
        </script>
    </body>
</html>
<?php
session_start();
include_once("WEB-INF/Classes/Menu.class.php");
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
//echo phpinfo();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="expires" content="-1">
        <link rel="icon" href="resources/images/logos/ra4.png" type="image/x-icon"/>
        <title>Genesis</title>
        <meta http-equiv="expires" content="-1">
        <!-- JS -->
        <link rel="stylesheet" href="resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
        <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>        
        <script type="text/javascript" src="resources/js/jquery/jquery.validate.js"></script>
        <script type="text/javascript" src="resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
        <script type="text/javascript" src="resources/js/funciones.js"></script>                   
        
        <!-- Tables -->
        <script type="text/javascript" language="javascript" src="resources/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="resources/media/js/TableTools.min.js"></script>
        <link href="resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
        <link href="resources/css/sicop.css" rel="stylesheet" type="text/css">  
        
        <!-- multiselect -->
        <script src="resources/js/multiselect/jquery.multiselect.min.js"></script>
        <script src="resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
        <link href="resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
        <link href="resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">
        
        <link id="linkCSS" href="./css/Site.css" rel="stylesheet" type="text/css" media="all">
        <link href="./css/Site.css" rel="stylesheet" type="text/css">
        <link href="resources/css/menu-12.css" rel="stylesheet" type="text/css" media="all">
        <style>
            .contenido{
                width: 800px;
                margin-left:auto;
                margin-right:auto;
            }
            
            .style1{
                width: 30%;
            }
        </style>
    </head>
    <body>        
        <div class="page">
            <div class="header">
                <!--<div style=" float:right;" class="title">
                    <h1>
                        FACTURACIÓN ELECTRÓNICA
                    </h1>
                </div>-->
                <div style=" float:left;width:960px;height:98px;overflow:hidden; ">                    
                    <img src="./img/Genesis-Kiosera_r1_c2_site.jpg" id="imgLogo">
                    <!--<div style="float: right; margin-right: 20px;">Inserta usuario aquí</div>-->
                </div>
            </div>
            <div class="clear hideSkiplink">
                <div class="menuloggin" style=" text-decoration:none; float:right; color:#BD5657; position:relative; padding-top:7px; padding-right:10px; height:25px;">                    
                    <div style="color: #8A0808; display: inline;"><a id="lnkbtnCerrarSesion" class="ButtonLink" href="#" onclick='cambiarContenidos("Cambiar_Datos_Usuario.php","Datos de Usuario"); return false;' style="color:#BD5657;"><?php echo $_SESSION['user']; ?></a> </div>| 
                    <a id="lnkbtnCerrarSesion" class="ButtonLink" href="sesion.php?cerrar=1" style="color:White;">Cerrar Sesi&oacute;n</a>
                </div>
                <div>
                    <?php
                    $menu = new Menu();
                    $opciones = $menu->getPermisos($_SESSION['idUsuario']);
                    $primero = true;
                    $menuActual = "";
                    ?>
                    <ul class="mi-menu">
                        <?php
                        while ($rs = mysql_fetch_array($opciones)) {
                            if ($menuActual != $rs['menu']) {
                                if (!$primero) {
                                    echo "</ul></li>";
                                } else {
                                    $primero = false;
                                }
                                echo "<li><a href='#'>" . $rs['menu'] . "</a><ul>";
                                $menuActual = $rs['menu'];
                            }
                            if ($rs['referencia'] != "#") {
                                echo "<li><a href='#' onclick='cambiarContenidos(\"" . $rs['referencia'] . "\",\"" . $rs['submenu'] . "\"); return false;'>" . $rs['submenu'] . "</a></li>";
                            } else {
                                echo "<li><a href='#'>" . $rs['submenu'] . "</a></li>";
                            }
                        }
                        echo "</ul></li>";
                        ?>                      
                    </ul>
                </div>
            </div>
            <div class="main" style="margin-left: 0px; width: 98%;">
                <h2 class="titulos">
                    <div id="titulo"></div>
                </h2>
                <div id="cargando" style="width:80%; margin-left: 50%; display: none; ">
                    <img src="resources/images/cargando.gif"/>                          
                </div>
                <div id="loading_text" style="width:80%; margin-top: 5px;  margin-left: 45%;"></div>
                <div id="mensajes" style="margin-left: 15px;"></div>                
                <div id="contenidos" style="position: relative; width: 100%; margin-left: 0px; display: block;"></div> 
                <div id="contenidos_invisibles" style="display: none;"></div>
            </div>

            <div class="DivEncabezado">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tbody><tr>
                            <td class="DTImagenEncabezado"></td>
                        </tr>
                    </tbody></table>
            </div>
            <div class="DivLineaEncabezado">
            </div>
        </div>
        <div class="footer">
        </div>
        <div id="detalleTicket">
            
        </div>
    </body>
</html>
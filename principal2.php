<?php
session_start();
include_once("WEB-INF/Classes/Menu.class.php");
include_once("WEB-INF/Classes/ParametroGlobal.class.php");
include_once("WEB-INF/Classes/PermisosSubMenu.class.php");
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

$permisos_grid = new PermisosSubMenu();
$redirigirResumenContrato = false;
if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 39)) {
    $redirigirResumenContrato = true;
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
       <!-- <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="expires" content="-1"> -->

        <!-- ADICION DE ETIQUETAS METATAGS PARA VISTA RESPONSIVE -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <!-- ADICION DE ETIQUETAS METATAGS PARA VISTA RESPONSIVE -->


        <link rel="icon" href="resources/images/logos/ra4.png" type="image/x-icon"/>
        <title><?php echo $_SESSION['nombreEmpresa']; ?></title>
        <meta http-equiv="expires" content="-1">
        <!-- JS -->
        <link rel="stylesheet" href="resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="resources/js/jquery/jquery-1.11.3.min.js"></script>
        <script src="resources/js/jquery/jquery-ui.min.js"></script>        
        <script type="text/javascript" src="resources/js/jquery/jquery.validate.js"></script>
        <script type="text/javascript" src="resources/js/file_validate/js/file-validator.js"></script>
        <link href="resources/js/file_validate/css/file-validator.css" rel="stylesheet" type="text/css">          
        <script type="text/javascript" src="resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
        <script type="text/javascript" src="resources/js/jquery/jquery.maskedinput.min.js"></script>
        <script type="text/javascript" src="resources/js/funciones.js"></script>
        <script src="resources/js/highcharts/highcharts.js"></script>
        <script src="resources/js/highcharts/modules/exporting.js"></script>
        
        <!-- Tables -->
        <script type="text/javascript" language="javascript" src="resources/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="resources/media/js/TableTools.min.js"></script>
        <link href="resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
        
        <!-- multiselect -->
        <script src="resources/js/multiselect/jquery.multiselect.min.js"></script>
        <script src="resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
        <link href="resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
        <link href="resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">
        <!-- colorPicker -->
        <script type="text/javascript" language="javascript" src="resources/js/colorpicker/js/colorpicker.js"></script>
        <link rel="stylesheet" media="screen" type="text/css" href="resources/js/colorpicker/css/colorpicker.css">    
		
		
        <link id="linkCSS" href="./css/Site.css" rel="stylesheet" type="text/css" media="all">
        <link href="./css/Site.css" rel="stylesheet" type="text/css">
        <link href="resources/css/menu-12.css" rel="stylesheet" type="text/css" media="all">
        
        
        <link href="resources/css/sicop.css" rel="stylesheet" type="text/css">

             <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        
        <!-- FontAwesome para iconos -->
        <link href="resources/css/Bootstrap 4/fontawesome/css/all.min.css" rel="stylesheet">

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
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true"></script>
        <script src="resources/js/jquery/jquery.datetimepicker.full.js"></script>
        <link href="resources/css/genesis/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
    </head>
    <body>        
        <div class="page">
            <div class="header">
                <!--<div style=" float:right;" class="title">
                    <h1>
                        FACTURACIÓN ELECTRÓNICA
                    </h1>
                </div>-->
                <div style=" float:left;width:960px;height:70px;overflow:hidden; ">     
                    <?php
                        $parametro = new ParametroGlobal();
                        $src = "";
                        $logo = "";
                        if($parametro->getRegistroById("5")){
                            $src = $parametro->getValor();
                        }
                        if($parametro->getRegistroById("10") && $parametro->getValor() != NULL && $parametro->getValor() != ""){
                            $logo = $parametro->getValor();
                        }
                    ?>
                    <div style="float: left; margin: 0 0 0 1%; font-size: 14px; font-weight: bold;">
                        <?php
                            if($logo != ""){
                                echo '<img src="./'.$logo.'" id="imgLogo2" style="margin-top: 0%;">';
                            }else{
                                echo $_SESSION['nombreEmpresa']; 
                            }
                        ?>
                    </div>
                    <img src="./<?php echo $src; ?>" id="imgLogo" style=" position: absolute;right: 0px;">                    
                </div>
            </div>
            <div class="clear hideSkiplink">
                <div class="menuloggin" style=" text-decoration:none; float:right; color:#BD5657; position:relative; padding-top:7px; padding-right:10px; height:25px;">                    
                    <div style="color: #8A0808; display: inline;">
                        <a id="lnkbtnCerrarSesion" class="ButtonLink" href="#" onclick='cambiarContenidos("Cambiar_Datos_Usuario.php","Datos de Usuario"); return false;' style="color:#BD5657;"><?php echo $_SESSION['user']; ?></a> 
                    </div>| 
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
                                echo "<li><a href='#' onclick='cambiarContenidos(\"" . $rs['referencia'] . "\",\"".$rs['menu']." > " . $rs['submenu'] . "\"); return false;'>" . $rs['submenu'] . "</a></li>";
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
                <div id="titulo" class="titulos"></div>                
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
<script >
//Si se manda un valor en 'tipo' que es el tipo de relacion para una prefactura cancelada ingresa al siguiente if
<?php if (isset($_GET['mnu']) && $_GET['mnu'] != "" && isset($_GET['action']) && $_GET['action'] != "" && isset($_GET['tipo']) && $_GET['tipo'] != "") { 
        $paginaCargar = $_GET['mnu']."/".$_GET['action'].".php?recargado=1&";        
        
        if(isset($_GET['id']) && isset($_GET['tipo'])){                        
            $paginaCargar .= "id=".$_GET['id']."&tipo=".$_GET['tipo']."&idfacAsust=".$_GET['idfacAsust']."&";
        }
        
        for($i=1;isset($_GET['param'.$i]);$i++){            
            $paginaCargar .= "param$i=".$_GET['param'.$i]."&";                        
        }
                
        $paginaCargar = substr($paginaCargar, 0, strlen($paginaCargar)-1);    
        ?>        
        cambiarContenidos('<?php echo $paginaCargar; ?>');
<?php } else if (isset($_GET['mnu']) && $_GET['mnu'] != "" && isset($_GET['action']) && $_GET['action'] != "") { 
        $paginaCargar = $_GET['mnu']."/".$_GET['action'].".php?recargado=1&";        
        
        if(isset($_GET['id'])){                        
            $paginaCargar .= "id=".$_GET['id']."&";                        
        }
        
        for($i=1;isset($_GET['param'.$i]);$i++){            
            $paginaCargar .= "param$i=".$_GET['param'.$i]."&";                        
        }
                
        $paginaCargar = substr($paginaCargar, 0, strlen($paginaCargar)-1);             
        ?>        
        cambiarContenidos('<?php echo $paginaCargar; ?>');
<?php 
        }else if($redirigirResumenContrato){
            $paginaCargar = "contrato/resumen.php";
        ?>
        cambiarContenidos('<?php echo $paginaCargar; ?>');
<?php
        } 
?>
</script>
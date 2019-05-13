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
        
        <!-- ADICIÓN DE METATAGS PARA WEB RESPONSIVE -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <!-- FIN DE LA ADICIÓN DE METATAGS PARA WEB RESPONSIVE -->


        <link rel="icon" href="resources/images/logos/ra4.png" type="image/x-icon"/>
        <title><?php echo $_SESSION['nombreEmpresa']; ?></title>        

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

        <!-- LINK HACIA BOOTSTRAP, ESTILOS DE LA SIDENAV E ICONOS FONTAWESOME -->
        <link href="resources/css/Bootstrap 4/css/all.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="resources/css/Bootstrap 4/css/BarraNavegacion.min.css" rel="stylesheet" type="text/css">

    </head>
    
    <body>
        
        <!-- BARRA DE NAVEGACION LATERAL -->
        <nav id="sidebar">

            <!-- CABECERA DEL SIDEBAR -->
            <div class="sidebar-header">

                <img class="mr-2" src="resources/images/logos/ARA.png" alt="Ara">
                <span>ARA</span>

                <button id="cerrar_sidebar" class="btn btn-link btn-sm">
                    <i class="far fa-arrow-left"></i>
                </button>

            </div>
            <!-- FIN DE LA CABECERA DEL SIDEBAR -->

            <!-- CONTENEDOR PRINCIPAL DEL SIDEBAR -->
            <div class="contenedor-principal-sidebar pb-5">

                <!-- CONTENEDOR DE LOS DATOS PERSONALES DEL USUARIO -->
                <div class="sidebar-user">

                    <!-- NOMBRE -->
                    <h5 id="nombre_usuario" class="text-center">
                        <?php echo $_SESSION['user']; ?>                        
                    </h5>

                    <!-- NOMBRE DE LA EMPRESA -->
                    <p id="correo_usuario" class="text-center">
                        <?php echo $_SESSION['nombreEmpresa']; ?>                        
                    </p>

                    <!-- FOTO DE PERFIL DEL USUARIO -->
                    <div class="container-fluid d-flex justify-content-center">

                        <img class="img-thumbnail" id="foto_usuario" src="resources/images/sin_foto_perfil.png" alt="Foto de perfil del usuario">

                    </div>
                    <!-- FIN DE LA FOTO DE PERFIL DEL USUARIO -->

                </div>
                <!-- FIN DEL CONTENEDOR DE LOS DATOS PERSONALES DEL USUARIO -->

                <!-- LISTA DEL MENU -->
                <div class="sidebar-items">
                    
                    <p class="titulo-menu text-uppercase">
                        Inicio
                    </p>

                    <?php

                        // CREACION DE LA INSTANCIA DEL MENU 
                        $menu = new Menu();                
                        //SE REALIZA LA CONSULTA A LA CLASE 
                        $consulta = $menu->getPermisos($_SESSION['idUsuario']);                

                        $cabecera_menu = "";
                        
                        while ($respuesta_servidor = mysql_fetch_array($consulta)) {
                            
                            
                            if ($cabecera_menu == '') {
                                
                                $cabecera_menu = $respuesta_servidor['menu'];
                                $id_links = explode(" ",$cabecera_menu);
                                $id_links[0];

                                echo('<ul class="sidebar-item-menu">');

                                echo('
                                        <div class="contenedor-item-menu">
                                            <a href="#'.$id_links[0].'-Menu" data-toggle="collapse" aria-controls="'.$id_links[0].'-Menu" aria-expanded="false" class="dropdown-toggle">
                                                '.$cabecera_menu .'
                                            </a>
                                        </div>                        
                                    ');

                                echo('
                                    <ul class="collapse" id="'.$id_links[0].'-Menu">                                    
                                        <li class="sidebar-sub_item list-unstyled">
                                            <a href="#" onclick="cambiarContenidos(\'' . $respuesta_servidor["referencia"] . '\',\''.$respuesta_servidor["menu"].' > ' . $respuesta_servidor["submenu"] . '\'); return false;">
                                                '.$respuesta_servidor['submenu']. ' 
                                            </a>
                                        </li>                                    
                                    </ul>
                                '); 

                            }elseif ($cabecera_menu == $respuesta_servidor['menu']) {
                                
                                $cabecera_menu = $respuesta_servidor['menu'];
                                $id_links = explode(" ",$cabecera_menu);
                                $id_links[0];
                                
                                echo('
                                        <ul class="collapse" id="'.$id_links[0].'-Menu">                                    
                                            <li class="sidebar-sub_item list-unstyled">
                                                <a href="#" onclick="cambiarContenidos(\'' . $respuesta_servidor["referencia"] . '\',\''.$respuesta_servidor["menu"].' > ' . $respuesta_servidor["submenu"] . '\'); return false;">
                                                    '.$respuesta_servidor['submenu'].'
                                                </a>
                                            </li>                                    
                                        </ul>
                                    ');                        
                            }elseif ($cabecera_menu != $respuesta_servidor['menu']){
                                
                                $cabecera_menu = $respuesta_servidor['menu'];                                
                                $id_links = explode(" ",$cabecera_menu);
                                $id_links[0];

                                echo('</ul><ul class="sidebar-item-menu">');
                                echo('
                                        <div class="contenedor-item-menu">
                                            <a href="#'.$id_links[0].'-Menu" data-toggle="collapse" aria-controls="'.$id_links[0].'-Menu" aria-expanded="false" class="dropdown-toggle">
                                                '.$cabecera_menu .'
                                            </a>
                                        </div>                        
                                    ');
                                
                                echo('
                                        <ul class="collapse" id="'.$id_links[0].'-Menu">                                    
                                            <li class="sidebar-sub_item list-unstyled">
                                                <a href="#" onclick="cambiarContenidos(\'' . $respuesta_servidor["referencia"] . '\',\''.$respuesta_servidor["menu"].' > ' . $respuesta_servidor["submenu"] . '\'); return false;">
                                                '.$respuesta_servidor['submenu']. ' 
                                                </a>
                                            </li>                                    
                                        </ul>
                                ');

                            }
                        }

                    ?>

                </div>
                <!-- LISTA DEL MENU -->

            </div>
            <!-- FIN DEL CONTENEDOR PRINCIPAL DEL SIDEBAR -->

        </nav>
        <!-- BARRA DE NAVEGACION LATERAL -->

        <!-- BARRA DE NAVEGACION SUPERIOR -->                    
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            
            <button class="btn btn-outline-light" type="button" id="sidebarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand" href="#">
                <img src="resources/images/logos/ARA.png" width="30" height="30" class="d-inline-block align-top" alt="">
                ARA
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse"
                data-target="#navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">

                <div class="mr-auto color2"></div>

                <li class="nav-item dropdown list-unstyled">

                    <a class="nav-link dropdown-toggle" id="nombre_usuario lnkbtnCerrarSesion" href="#" id="navbarDropdown"
                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="far fa-user mr-1" aria-hidden="true"></i>                            
                        <?php echo $_SESSION['user']; ?>
                    </a>

                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        
                        <a class="dropdown-item" href="#" onclick='cambiarContenidos("Cambiar_Datos_Usuario.php","Datos de Usuario"); return false;'>
                            <i class="far fa-user-cog mr-1"></i> Mi Perfil
                        </a>
                    
                        <a class="dropdown-item" href="sesion.php?cerrar=1">
                            <i class="far fa-sign-out-alt mr-1"></i> Cerrar Sesión
                        </a>

                    </div>
                    
                </li>

            </div>
        
        </nav>        
        <!-- FIN DE LA BARRA DE NAVEGACION SUPERIOR -->
                    

        <div class="container-fluid">  

            <div class="clear hideSkiplink">
                                
            </div>

            <div class="main" style="margin-left: 0px; width: 98%;">   

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li id="titulo" class="titulos breadcrumb-item active" aria-current="page">Principal</li>
                    </ol>
                </nav>

                <!-- <div id="titulo" class="titulos" style="background-color: blue;"></div>                 -->

                <div id="cargando" style="width:80%; margin-left: 50%; display: none; ">
                    <img src="resources/images/cargando.gif"/>                          
                </div>

                <div id="loading_text" style="width:80%; margin-top: 5px;  margin-left: 45%;"></div>

                <div id="mensajes" style="margin-left: 15px;"></div>                

                <div id="contenidos" class="container-fluid"></div> 

                <div id="contenidos_invisibles" style="display: none;"></div>

            </div>

        </div>
        
        <div class="footer">
        </div>
        
        <div id="detalleTicket">            
        </div>
                        
        <!--LINK HACIA LAS FUNCIONALIDADES DE BOOTSTRAP 4  -->
        <script src="resources/js/Bootstrap 4/popper.min.js"></script>   
        <script src="resources/js/Bootstrap 4/bootstrap.min.js"></script>  

    </body>

</html>

<!-- EFECTOS DE LA SIDEBAR -->
<script type="text/javascript">
    $(document).ready(function () {

        $('#sidebarCollapse').on('click', function () {

            $('#sidebar').toggleClass('active');

        });

        $('#cerrar_sidebar').on('click', function () {

            $('#sidebar').toggleClass('active');

        });

    });
</script>
<!-- FIN DE LOS EFECTOS DE LA SIDEBAR -->


<script >
//Si se manda un valor en 'tipo' que es el tipo de relacion para una prefactura cancelada ingresa al siguiente if
//Si se envia tambien el valor tipoFac se entra al siguiente if DDHL 26/03/2019
<?php if (isset($_GET['mnu']) && $_GET['mnu'] != "" && isset($_GET['action']) && $_GET['action'] != "" && isset($_GET['tipo']) && $_GET['tipo'] != "" && isset($_GET['tipoFac']) && $_GET['tipoFac'] != "") { 
        $paginaCargar = $_GET['mnu']."/".$_GET['action'].".php?recargado=1&";        
        
        if(isset($_GET['id']) && isset($_GET['tipo']) && isset($_GET['tipoFac'])){                        
            $paginaCargar .= "id=".$_GET['id']."&tipo=".$_GET['tipo']."&idfacAsust=".$_GET['idfacAsust']."&tipoFac=".$_GET['tipoFac']."&periodo=".$_GET['periodo']."&";
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
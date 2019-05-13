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

  function getElementos($val){  // Consultas para extraer imagen o datos de el usuario
    $parametro = new ParametroGlobal();
    $src = "";
    $logo = "";
    if ($val == 1){
      if($parametro->getRegistroById("5")){
          $src = $parametro->getValor();
      }
      echo $src;
    }else if ($val == 2){
      if($parametro->getRegistroById("10") && $parametro->getValor() != NULL && $parametro->getValor() != ""){
          $logo = $parametro->getValor();
      }
      if($logo != ""){
        echo '<img src="./'.$logo.'"  style="margin-top: 0%;">';
      }else{
        echo strtoupper($_SESSION['nombreEmpresa']);
      }
    }else if($val == 3){
      echo '<img src="./'.'img/logo-factury.jpeg'.'"  style="margin-top: 0%; width:95px;">';
    }         
  }
?>

<!DOCTYPE html>
<html lang="es">  
<head>
  <title><?php echo $_SESSION['nombreEmpresa']; ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="expires" content="-1">
  <link rel="icon" href="resources/images/logos/ra4.png" type="image/x-icon"/>
  <title><?php echo $_SESSION['nombreEmpresa']; ?></title>
  <meta http-equiv="expires" content="-1">        
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minmum-scale=1.0">
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
  <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true"></script>
  <script src="resources/js/jquery/jquery.datetimepicker.full.js"></script>
  <link href="resources/css/genesis/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
  
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
  <!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>     -->
  <link href="resources/css/multiple-select.css" rel="stylesheet" type="text/css">
  <!-- colorPicker -->
  <script type="text/javascript" language="javascript" src="resources/js/colorpicker/js/colorpicker.js"></script>
  <link rel="stylesheet" media="screen" type="text/css" href="resources/js/colorpicker/css/colorpicker.css">    
    
  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
  <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
  <!-- <img src="resources/img/open-iconic-master/svg/icon-name.svg" alt="icon name"> -->
  <link href="resources/img/open-iconic-master/font/css/open-iconic-bootstrap.css" rel="stylesheet">
    
<style>  
  .col-centrada{    
    float: none;
    margin: 0 auto;
  }
  .navbar, .navbar-brand{
    padding: 0;    
  }  
  .navbar-brand{
    margin-right: 50px;
  }
  .dropdown-toggle::after {
    border-top: .3em solid #393e46;
  }
  .dropdown-menu {
    background-color: #eeeeee;
  }
  .header-img{
    background-color: #f7f7f7;
   -webkit-background-size: cover;
   -moz-background-size: cover;
   -o-background-size: cover;
    background-size: cover;
    padding: 20px;
    margin: 0;
  }
  .user2, .alert_mensaje, #cargando{
    display: none;
  }
  .text-center{
    width:120px;
    /* height:40px; */
  }
  #imgLogo{
      width: 90px;
    }
  font{
    font-size: 13px;
    /* color: white;     */
  }
  .nav-item:hover, .dropdown-item:hover{
    background: #929aab;
  } 
  .dropdown-item{
    border-bottom: 1px solid gray;
  }  
  #menus{
    padding: 0;    
    color: white;
    font-size: 16px;
  }
  .btnMenu{
    padding: 0;    
    color: white;
    font-size: 16px;
  }
  .badge{
    height: 40px;
  }
  @media (max-width: 1250px) {        
    .text-center{
      width:125px;
    }
    font{
      font-size: 14px;
    }
    .user2{
      display: block;
    }
    .perfil{
      margin-bottom: 15px;
    }
    #menus{      
      font-size: 14px;  
    }
    .navbar-nav{
      max-height: 400px;
      overflow: auto;
    }
  }
  @media (max-width: 800px) {
    #menus{
      padding-left: 20px;  
      font-size: 16px;  
    }
    #imgLogo{
      width: 90px;
    }
  }
</style>

</head>

<body style="height:auto;" class="header-img">  

<!-- Barra de navegacion - parte superior -->

  <div class="row">
    <div class="col-7 col-xs-6 col-sm-6 col-md-7 col-lg-8 col-xl-9">
      <h2 style="color: black;"><b> <?php getElementos(2); ?></b></h2>
      <b style="color: black;"><?php echo $_SESSION['nombreUsuario']; ?></b>      
    </div>
    <div class="col-5 col-xs-6 col-sm-6 col-md-5 col-lg-4 col-xl-3">
      <div class="row">
        <div class="col-12 col-sm-6 col-md-6"> <!-- Botón para ver perfil -->
          <div class="perfil">
            <span class="text-center badge badge-pill badge-info" style="height:30px;">
              <a class="nav-link btnMenu" href="#" style="margin-top:2px;" onclick='cambiarContenidos("Cambiar_Datos_Usuario.php","Datos de Usuario"); return false;'>  
                <img src="img/user.png" alt="Perfil" style="width:17px; filter: invert(100%);">  
                
                <font><?php echo $_SESSION['user']; ?></font>
              
              </a>

          </div>

        </div>

        <div class="col-12 col-sm-6 col-md-6"> <!-- Botón para cerrar sesion -->
          
            <span class="text-center badge badge-pill badge-danger" style="height:30px;">
              
              <a class="nav-link btnMenu" href="sesion.php?cerrar=1" style="margin-top:2px;">
                
                <img src="img/logout.png" alt="Perfil" style="width:17px; filter: invert(100%);">  
                
                <font>Cerrar Sesión<font>

              </a>
            
            </span>
          
        </div>
        
      </div>

    </div>
    
  </div>
  
  <br>
<!-- Barra de navegacion - parte fija -->

  <nav class="navbar navbar-expand-xl navbar-dark sticky-top" style="z-index: 1; background: #eeeeee;">
          
      <a class="navbar-brand" href="#">

        <img src="./<?php getElementos(1); ?>" id="imgLogo"  style="margin-top: 0; width:75px;">  

      </a>

      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar" style="margin-right:10px; background-color: #929aab;">

        <span class="navbar-toggler-icon"></span>

      </button>

      <div class="p-0 collapse navbar-collapse" id="collapsibleNavbar">

        <?php

          $menu = new Menu();

          $opciones = $menu->getPermisos($_SESSION['idUsuario']);

          $primero = true;

          $menuActual = "";

        ?>

        <ul class="navbar-nav">

          <?php

          while ($rs = mysql_fetch_array($opciones)) {

              if ($menuActual != $rs['menu']) {

                  if (!$primero) {

                      echo "</div></li>";

                  } else {

                      $primero = false;

                  }

                  echo "<li class='nav-item dropdown' style='color: #393e46;'><a class='nav-link dropdown-toggle' href='#' data-toggle='dropdown' id='navbardrop'>
                          
                          <b id='menus' style='color: #393e46;'>" . ($rs['menu']) . "</b>
                          
                          </a><div class='dropdown-menu'>";
                  
                  $menuActual = $rs['menu'];

              }

              if ($rs['referencia'] != "#") {
                
                  // echo "<a class='dropdown-item' href='#' onclick='cambiarContenidos(\"" . $rs['referencia'] . "\",\"".$rs['menu']." > " . $rs['submenu'] . "\"); return false;'>" . $rs['submenu'] . "</a>";

                  echo "<a style='background-color: #eeeeee;' class='dropdown-item' href='#' onclick='cambiarContenidos(\"" . $rs['referencia'] . "\",\"".$rs['menu']." > " . $rs['submenu'] . "\"); return false;'>" . $rs['submenu']." ".$rs[''] . "</a>";
              
                } else {
                  
                  echo "<a href='#'>" . $rs['submenu'] . "</a>";
              
                }

          }

          echo "</ul></li>";

          ?>                      

        </ul> 

      </div> 

  </nav>

<!-- Contenidos y mensaje -->

  <div class="p-0 container-fluid" >   

    <div id="titulo" class="titulos"></div>      

    <div class="fixed-top alert_mensaje alert alert-secondary" role="alert">

      <center>

        <div id="mensajes"></div>

      </center>

    </div>

    <div id="contenidos" style="position: relative; width: 100%; height:110%; margin: 0; display: block;"><div style="height:380px"></div></div> 

    <div id="contenidos_invisibles" style="display: none;"></div>

  </div>

  <div class="DivLineaEncabezado">
  
  </div>

  <br><br><br>

<!-- Mensaje de pantalla completa -->
  <div id="cargando">

    <div class="d-flex fixed-top text-center" style="width: 100%; height: 100%; background: rgba(192,192,192,.3);">

      <div class="card border-primary mb-3 align-self-center text-center p-5 m-auto" style="width: 18rem; height: 10rem;">

          <div class="card-title">

            <img src="resources/images/cargando.gif" style="width: 40px;"/>                          

          </div>

          <div class="card-text text-primary">
            
            <div id="loading_text">Cargando...</div>

          </div>

      </div>

    </div>
  </div>

<!-- Pie de página -->

    <footer class="bg-light">

      <div class="footer-copyright mx-auto py-3" style="width:200px;">© 2018 Copyright:

        <a href=#> XIIMBAL</a>

      </div>

    </footer>

    <div id="detalleTicket">    
    
    </div>

  
  <script src="resources/js/multiple-select.js"></script>

</body>


</html>

<script >
<?php if (isset($_GET['mnu']) && $_GET['mnu'] != "" && isset($_GET['action']) && $_GET['action'] != "") { 
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
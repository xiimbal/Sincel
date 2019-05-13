<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../index.php");
    }
    include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
    
    $permisos_grid = new PermisosSubMenu();
    $same_page = "reportes/uploadCSV.php";
    $permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
        <meta http-equiv="X-UA-Compatible" content="IE=8" />
        <meta http-equiv="X-UA-Compatible" content="IE=7" />
        <meta name="description" content="SMUC Sistema de Monitoreo Ubicacion y Control"/>
        <meta name="keywords" content="CAOMI,Rackspace,Romas Informatica, MAGG, Mexico, ESCOM"/>
        <meta name="author" content="CAOMI"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>        
        <script type="text/javascript" src="resources/js/paginas/reporteUsoDiario.js"></script>
          <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <!-- FontAwesome para iconos -->
        <!--<link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet"> -->                         
    </head>    
    <body>
          <div class="principal">          
            <form action="/" method="post" id="fupload" name="fupload" enctype="multipart/form-data">
               <div class="container-fluid">
                  <div class="form-row"> 
                    <legend>Carga tu archivo</legend>
                     <div class="form-group col-md-12">
                       <input  class="form-control" type="file"  name="file" id="file" required/>
                             <?php if($permisos_grid->getModificar()){ ?>      
                       <input class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" type="submit"  name="upload" id="upload" value="Subir archivo" />
                            <?php } ?>  
                       </div>                                      
                    <!--<input pe="submit" class="buton" name="upload" id="upload" value="Cancelar" onclick="changeContenidos('logistica/lista_PuntosInteres.php', 'P u n t o s &nbsp; d e &nbsp; I n t e r &eacute; s', false); return false;"/>-->
                 </div>
             </div>
            </form>
          </div>
    </body>
</html>
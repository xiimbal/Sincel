<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">
        
        <title>Productos y servicios</title>
    </head>
    <body>       
        
            
                

                    <div class="container-fluid">
                        <div class="form-row">
                            <div class="form-group  col-md-4">
                    <select class="form-control" id="modalidad" name="modalidad" onchange="cargarArrendamiento(this.value);">
                        <?php
                        $catalogo = new Catalogo();
                        $query = $catalogo->getListaAlta('c_modalidad', 'Nombre');
                        echo "<option value='0' >Selecciona una opción</option>";
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value=" . $rs['Pagina'] . " >" . $rs['Nombre'] . "</option>";
                        }
                        ?>
                    </select>

              </div>
          </div>
 
        </div>


        <br/><br/>
         <div id="arrendamientos"></div>

    </body>
</html>
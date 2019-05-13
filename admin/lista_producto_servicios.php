<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Productos y servicios</title>        
    </head>
    <body>
        <select id="pys" name="pys" onchange="cargarPyS(this.value);">
            <option value="">Selecciona producto o servicio</option>
            <option value="admin/lista_equipos.php">Equipos</option>
            <option value="admin/lista_componentes.php">Componentes</option>
            <option value="admin/lista_arrendamiento.php">Arrendamiento</option>                        
        </select><br/><br/>
        <div id="contenidosSyP"></div>
    </body>
</html>

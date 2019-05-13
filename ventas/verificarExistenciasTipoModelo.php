<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Almacen.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
?>
<!DOCTYPE html>
<html lang="es">
    <body>
        <?php
            if (!isset($_GET['modelo']) || !isset($_GET['tipo'])) {
                echo "<br/><b>Es necesario seleccionar un Tipo y un Modelo para mostrar sus existencias</b>";
            } else {
                $almacen = new Almacen();
                $catalogo = new Catalogo();
                //Primero evaluámos si recibimos un equipo o un accesorio
                $tipo = $_GET['tipo'];
                $modelo = $_GET['modelo'];
                $idsol = $_GET['idsol'];
                //Obtenemos los almacenes disponibles para este usuario.
                $almacenes = $almacen->getAlmacenResponsable($_SESSION['idUsuario']);
                $listaAlmacenes = null;
                if ($almacenes != "") {
                    $listaAlmacenes = explode(",", $almacenes);
                }
                    
                $andLista = "";
                if (is_array($listaAlmacenes)) {
                    $andLista = "AND ac.id_almacen IN('" . implode($listaAlmacenes, "', '") . "')";
                }
                
                if ((int)$tipo > 0) {
                    $query = "SELECT ac.cantidad_existencia, c.Modelo, a.nombre_almacen, ac.NoParte FROM k_almacencomponente ac
                        LEFT JOIN c_componente AS c ON ac.NoParte = c.NoParte 
                        LEFT JOIN c_almacen AS a ON ac.id_almacen = a.id_almacen
                        WHERE c.IdTipoComponente = $tipo AND ac.NoParte = '$modelo' $andLista";
                } else {
                    //Estamos tratando con un equipo solo es importante el modelo. (un modelo tiene varios Números de Parte)
                    $query = "SELECT e.Modelo, a.nombre_almacen, ac.NoParte, COUNT(*) as cantidad_existencia FROM k_almacenequipo ac
                            LEFT JOIN c_almacen a ON a.id_almacen = ac.id_almacen
                            LEFT JOIN c_equipo e ON e.NoParte = ac.NoParte
                            WHERE ac.NoParte = '$modelo' $andLista GROUP BY(a.nombre_almacen)";
                }
                
                //echo $query;
                $result = $catalogo->obtenerLista($query);
                    
                if (mysql_num_rows($result) > 0) {
                    echo '<div class="table-responsive">';
                    echo "<table class='table'>
                            <thead class='thead-dark'>
                                <tr>
                                    <th>Almacen</th>
                                    <th>Modelo</th>
                                    <th>No. Parte</th>
                                    <th>Existencias</th>
                                </tr>
                            </thead>";
                    while ($rs = mysql_fetch_array($result)) {
                        echo "<tr>
                                <td>".$rs['nombre_almacen']."</td>
                                <td>".$rs['Modelo']."</td>
                                <td>".$rs['NoParte']."</td>
                                <td>".$rs['cantidad_existencia']."</td>
                            </tr>";
                    }
                    echo "</table>
                    </div>";
                } else {
                    echo "<br/>No se ha encontrado el modelo <b>$modelo</b> en los almacenes";
                }
            }
        ?>
        <a href="#" onclick="cambiarContenidos('ventas/lista_solicitud_series.php?id=<?php echo $idsol; ?>','Serie de equipos')" style="color: white; text-decoration: none;">Regresar a la serie de equipos</a>
    </body>
</table>
</html>

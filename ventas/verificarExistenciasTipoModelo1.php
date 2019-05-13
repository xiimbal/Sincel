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
    <head>
        <!-- JS -->
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.9.1.js"></script>
        <script src="../resources/js/jquery/jquery-ui-1.10.3.custom.min.js"></script>        
        <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery.maskedinput.min.js"></script>
        <script type="text/javascript" src="../resources/js/funciones.js"></script>                   

        <!-- Tables -->
        <script type="text/javascript" language="javascript" src="../resources/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/media/js/TableTools.min.js"></script>
        <link href="../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
        <!-- multiselect -->
        <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
        <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
        <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">

        <link href="../resources/css/sicop.css" rel="stylesheet" type="text/css">  
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/verificarExistenciasTipoModelo.js"></script>                    
    </head>
    <body>
    <?php
    if(!isset($_GET['modelo']) || !isset($_GET['tipo'])){
        echo "<br/><b>Es necesario seleccionar un Tipo y un Modelo para mostrar sus existencias</b>";
    }else{
        $almacen = new Almacen();
        $catalogo = new Catalogo();
        //Primero evaluámos si recibimos un equipo o un accesorio
        $tipo = $_GET['tipo'];
        $modelo = $_GET['modelo'];
        //Obtenemos los almacenes disponibles para este usuario.
        $almacenes = $almacen->getAlmacenResponsable($_SESSION['idUsuario']);
        $listaAlmacenes = null;
        if($almacenes != ""){
            $listaAlmacenes = explode(",", $almacenes);
        }
              
        $andLista = "";
        if(is_array($listaAlmacenes)){
            $andLista = "AND ac.id_almacen IN('" . implode($listaAlmacenes, "', '") . "')";
        }
        
        if((int)$tipo > 0){
            
            $query = "SELECT ac.cantidad_existencia, c.Modelo, a.nombre_almacen, ac.NoParte FROM k_almacencomponente ac
                 LEFT JOIN c_componente AS c ON ac.NoParte = c.NoParte 
                 LEFT JOIN c_almacen AS a ON ac.id_almacen = a.id_almacen
                 WHERE c.IdTipoComponente = $tipo AND ac.NoParte = '$modelo' $andLista";
            
        }else{
            //Estamos tratando con un equipo solo es importante el modelo. (un modelo tiene varios Números de Parte)
            $query = "SELECT e.Modelo, a.nombre_almacen, ac.NoParte, COUNT(*) as cantidad_existencia FROM k_almacenequipo ac
                    LEFT JOIN c_almacen a ON a.id_almacen = ac.id_almacen
                    LEFT JOIN c_equipo e ON e.NoParte = ac.NoParte
                    WHERE ac.NoParte = '$modelo' $andLista GROUP BY(a.nombre_almacen)";
            
        }
        
        //echo $query;
        $result = $catalogo->obtenerLista($query);
        
        if(mysql_num_rows($result) > 0)
        {
            echo "<table id='tverificar' width='100%'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>Almacen</th><th>Modelo</th><th>No. Parte</th><th>Existencias</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            while($rs = mysql_fetch_array($result)){
                echo "<tr>";
                echo "<td>".$rs['nombre_almacen']."</td>";
                echo "<td>".$rs['Modelo']."</td>";
                echo "<td>".$rs['NoParte']."</td>";
                echo "<td>".$rs['cantidad_existencia']."</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }else{
            echo "<br/>No se ha encontrado el modelo <b>$modelo</b> en los almacenes";
        }
    }
    ?>
    </body>
</html>

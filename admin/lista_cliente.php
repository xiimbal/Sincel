<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
$controlador = $_SESSION['ruta_controler'] . "Validacion/Controler_Cliente.php";

$cabeceras = array("Cliente", "RFC", "Tipo", "Estatus", "Giro", "Ejecutivo de cuenta", "Estatus","", "");
$columnas = array("NombreRazonSocial", "RFC", "tipo_cliente", "estatus_cobranza", "giro", "ejecutivo_Cuenta", "ClaveCliente");
$tabla = "c_cliente";
$order_by = "NombreRazonSocial";
$alta = "admin/alta_cliente.php";
$same_page = "admin/lista_cliente.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">
            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <br/><br/><br/>
            <table id="tAlmacen" class="tabla_datos">
                <thead>
                    <tr>
                        <?php
                        for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $consulta = "SELECT c.ClaveCliente, c.NombreRazonSocial, c.RFC, ctc.Nombre AS tipo_cliente, ec.Nombre AS estatus_cobranza, cg.Nombre AS giro, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS ejecutivo_Cuenta,c.Activo
                    FROM `$tabla` AS c
                    LEFT JOIN c_tipocliente AS ctc ON c.IdTipoCliente = ctc.IdTipoCliente
                    LEFT JOIN c_estatuscobranza AS ec ON ec.IdEstatusCobranza = c.IdEstatusCobranza
                    LEFT JOIN c_giro AS cg ON cg.IdGiro = c.IdGiro
                    LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta                   
                    ORDER BY $order_by;";
                    
                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs["NombreRazonSocial"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["RFC"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["tipo_cliente"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["estatus_cobranza"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["giro"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["ejecutivo_Cuenta"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["ClaveCliente"] . "</td>";
                        if ($rs['Activo'] == 1)
                            echo "<td align='center' scope='row'>Activo</td>";
                        else
                            echo "<td align='center' scope='row'>Inactivo</td>";
                        ?>
                    <td align='center' scope='row'>                                
                        <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs[$columnas[count($columnas) - 1]]; ?>");
                        return false;' title='Editar Estructura' >
                            <img src="resources/images/Modify.png"/>
                        </a>
                    </td>

                    <td align='center' scope='row'> <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs[$columnas[count($columnas) - 1]]; ?>", "<?php echo $same_page; ?>");
                        return false;'><img src="resources/images/Erase.png"/></a> </td>                                        
                        <?php
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </body>
</html>
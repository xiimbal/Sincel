<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
$usuario = new Usuario();

?>
<!DOCTYPE html>
<html lang="es">
    <head>               
        <script type="text/javascript" language="javascript" src="resources/js/paginas/ReporteLecturas.js"></script>
        <style>
            .ui-datepicker-calendar {
                display: none;
            }​
        </style>
    </head>    
    <body>
        <div class="principal">                     
            <div id="divinfoup">         
            </div>
            <br/>
            <form id="FormLectura" name="FormLectura" action="reportes/ReporteEquiposSinLectura.php" target="_blank" method="POST">
                <table style="width: 100%;">
                    <tr>
                        <?php
                        $catalogo = new Catalogo();
                        $query = $catalogo->obtenerLista("SELECT c_puesto.IdPuesto FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario']);
                        $rs = mysql_fetch_array($query);
                        if ($rs['IdPuesto'] != 11) {
                            ?>
                            <td><label for="vendedor">Vendedor:</label></td>
                            <td>
                                <select id="vendedor" name="vendedor" width="100" style="width: 100px" onchange="cargarclientes('vendedor', 'cliente');" class="select">
                                    <option value="">Selecciona el vendedor</option>
                                    <?php
                                    $query = $catalogo->obtenerLista("SELECT c_usuario.IdUsuario,
                                        CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoPaterno,\" \",c_usuario.ApellidoMaterno) AS Nombre 
                                        FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto 
                                        WHERE c_puesto.IdPuesto=11 AND c_usuario.Activo = 1 ORDER BY Nombre");
                                    while ($rs = mysql_fetch_array($query)) {
                                        echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><label for="cliente">Cliente:</label></td>
                            <td>
                                <select id="cliente" name="cliente" width="150" style="width: 100px" 
                                        onchange="cargarContratos('cliente', 'contrato');" class="select">
                                    <option value="">Selecciona el cliente</option>
                                    <?php
                                        if($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 21)){
                                            $query = $catalogo->obtenerLista("SELECT
                                                c_cliente.NombreRazonSocial AS NombreRazonSocial,
                                                c_cliente.ClaveCliente AS ClaveCliente
                                                FROM c_usuario
                                                INNER JOIN k_tfscliente ON k_tfscliente.IdUsuario=c_usuario.IdUsuario
                                                INNER JOIN c_cliente ON c_cliente.ClaveCliente = k_tfscliente.ClaveCliente
                                                WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1
                                                ORDER BY NombreRazonSocial ASC");
                                        }else if($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)){
                                            $query = $catalogo->obtenerLista("SELECT
                                                c_cliente.NombreRazonSocial AS NombreRazonSocial,
                                                c_cliente.ClaveCliente AS ClaveCliente
                                                FROM c_usuario
                                                INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                                WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1
                                                ORDER BY NombreRazonSocial ASC;");
                                        }else{
                                            $query = $catalogo->obtenerLista("SELECT * FROM c_cliente WHERE Activo=1 ORDER BY NombreRazonSocial");
                                        }
                                        while ($rs = mysql_fetch_array($query)) {                                           
                                            echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreRazonSocial'] . "</option>";
                                        }
                                    ?>
                                </select>
                            </td>
                            <td>Contrato</td>
                            <td>
                                <select id="contrato" name="contrato" width="150" style="width: 100px" 
                                        onchange="cargarZona('contrato', 'zona'); cargarAnexos('contrato', 'anexo');" class="select">
                                    <option value="">Todos los contratos</option>
                                </select>
                            </td>
                            <td>Zona</td>
                            <td>
                                <select id="zona" name="zona" width="150" style="width: 100px" 
                                        onchange="cargarCentroCosto('cliente', 'centro_costo','contrato','zona');" class="select">
                                    <option value="">Todas las zonas</option>
                                </select>
                            </td>

                        </tr>
                        <tr>
                            <td>Centro costo: </td>
                            <td>
                                <select id="centro_costo" name="centro_costo" width="150" style="width: 100px" 
                                        onchange="cargarCentroCosto('cliente', 'centro_costo','contrato','zona');" class="select">
                                    <option value="">Selecciona el centro de costo</option>
                                </select>
                            </td>
                            <td><label for="localidad">Localidad:</label></td>
                            <td>
                                <select id="localidad" name="localidad" width="150" style="width: 100px" class="select">
                                    <option value="">Todas las localidades</option>                            
                                </select>
                            </td>
                            <td><label for="anexo">Anexo:</label></td>
                            <td>
                                <select id="anexo" name="anexo" width="150" style="width: 100px" class="select" >
                                    <option value="">Todas los anexos</option>
                                    ?>
                                </select>
                            </td>
                            <?php
                        } else {
                            ?>
                            <td><label for="cliente">Cliente:</label></td>
                            <td>
                                <select id="cliente" name="cliente" width="150" style="width: 100px"
                                        onchange="cargarContratos('contrato', 'zona');" class="select"> 
                                    <option value="">Selecciona el cliente</option>
                                    <?php
                                    $query = $catalogo->obtenerLista("SELECT
                            c_cliente.NombreRazonSocial AS NombreCliente,
                            c_cliente.ClaveCliente AS ClaveCliente
                            FROM
                                    c_usuario
                            INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                            WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . "
                            ORDER BY NombreCliente ASC");
                                    while ($rs = mysql_fetch_array($query)) {
                                        echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreCliente'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>Contrato</td>
                            <td>
                                <select id="contrato" name="contrato" width="150" style="width: 100px" 
                                        onchange="cargarZona('contrato', 'zona'); cargarAnexos('contrato', 'anexo');" class="select">
                                    <option value="">Todos los contratos</option>
                                </select>
                            </td>
                            <td>Zona</td>
                            <td>
                                <select id="zona" name="zona" width="150" style="width: 100px" 
                                        onchange="cargarCentroCosto('cliente', 'centro_costo','contrato','zona');" class="select">
                                    <option value="">Todas las zonas</option>
                                </select>
                            </td>

                        </tr>
                        <tr>
                            <td>Centro costo: </td>
                            <td>
                                <select id="centro_costo" name="centro_costo" width="150" style="width: 100px" 
                                        onchange="cargarCentroCosto('cliente', 'centro_costo','contrato','zona');" class="select">
                                    <option value="">Selecciona el centro de costo</option>
                                </select>
                            </td>
                            <td><label for="localidad">Localidad:</label></td>
                            <td>
                                <select id="localidad" name="localidad" width="150" style="width: 100px" class="select">
                                    <option value="">Todas las localidades</option>                        
                                </select>
                            </td>
                            <td><label for="anexo">Anexo:</label></td>
                            <td>
                                <select id="anexo" name="anexo" width="150" style="width: 100px"  class="select">
                                    <option value="">Todas los anexos</option>                        
                                </select>
                            </td>
                        <?php } ?>                                
                        <td><label for="fecha">Fecha</label></td>
                        <td>
                            <input type="text" class="fecha" id="fecha" name="fecha" />
                            <input type="hidden" id="required_fecha" name="required_fecha" value="1" />
                        </td>
                    </tr>
                </table>
                <br/><br/>
                <input type="submit" class="button" id="submit_lecturas" name="submit_lecturas" value="Generar reporte" style="margin-left: 85%;"/>                               
            </form>                        
        </div>        
    </body>
</html>
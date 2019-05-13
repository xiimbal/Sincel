<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
$catalogo = new Catalogo();
$usuario = new Usuario();

$usuario->setId($_SESSION['idUsuario']);
$negocios = $usuario->obtenerNegociosDeUsuario();
$where = "";
if (!empty($negocios)) {
    $string_clientes = "";
    foreach ($negocios as $value) {
        $string_clientes .= "'$value',";
    }
    if (!empty($string_clientes)) {
        $string_clientes = substr($string_clientes, 0, strlen($string_clientes) - 1);
    }
    $where = " AND c_cliente.ClaveCliente IN($string_clientes) ";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>               
        <script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/estado_cuenta.js"></script>        
    </head>    
    <body>
        <div class="principal">           
            <form id="FormEdoCuenta" name="FormEdoCuenta" action="facturacion/estado_cuenta.php" target="_blank" method="POST">
                <table style="width: 100%;">
                    <tr>
                        <td><label for="cliente">Cliente:</label></td>
                        <td>
                            <select id="cliente" name="cliente[]" width="150" style="width: 100px" class="multiselect" multiple="multiple">                                
                                <?php
                                if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 21)) {
                                    $query = $catalogo->obtenerLista("SELECT
                                                c_cliente.NombreRazonSocial AS NombreRazonSocial,
                                                c_cliente.ClaveCliente AS ClaveCliente, cg.Nombre AS Grupo
                                                FROM c_usuario
                                                INNER JOIN k_tfscliente ON k_tfscliente.IdUsuario=c_usuario.IdUsuario
                                                INNER JOIN c_cliente ON c_cliente.ClaveCliente = k_tfscliente.ClaveCliente
                                                LEFT JOIN c_clientegrupo AS cg ON cg.ClaveZona = c_cliente.ClaveGrupo
                                                WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1 $where
                                                ORDER BY NombreRazonSocial ASC");
                                } else if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)) {
                                    $query = $catalogo->obtenerLista("SELECT
                                                c_cliente.NombreRazonSocial AS NombreRazonSocial,
                                                c_cliente.ClaveCliente AS ClaveCliente, cg.Nombre AS Grupo
                                                FROM c_usuario
                                                INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                                LEFT JOIN c_clientegrupo AS cg ON cg.ClaveZona = c_cliente.ClaveGrupo
                                                WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1 $where
                                                ORDER BY NombreRazonSocial ASC;");
                                } else {
                                    $query = $catalogo->obtenerLista("SELECT c_cliente.ClaveCliente,c_cliente.NombreRazonSocial, cg.Nombre AS Grupo FROM c_cliente LEFT JOIN c_clientegrupo AS cg ON cg.ClaveZona = c_cliente.ClaveGrupo WHERE c_cliente.Activo=1 $where ORDER BY NombreRazonSocial");
                                }
                                while ($rs = mysql_fetch_array($query)) {
                                    $grupo = "";
                                    if(isset($rs['Grupo']) && !empty($rs['Grupo'])){
                                        $grupo = " (".$rs['Grupo'].")";
                                    }
                                    echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreRazonSocial'] . " $grupo</option>";
                                }
                                ?>
                            </select>
                        </td>                            

                        <td><label for="fecha">Fecha Inicio</label></td>
                        <td><input type="text" class="fecha" id="fecha_inicio" name="fecha_inicio" /></td>
                        <td><label for="fecha">Fecha Fin</label></td>
                        <td><input type="text" class="fecha" id="fecha_fin" name="fecha_fin" /></td>
                        <td><input type="checkbox" id="mostrar_pagado" name="mostrar_pagado" value="1"/>Mostrar facturas pagadas</td>
                    </tr>
                </table>
                <br/><br/>
                <input type="submit" class="button" id="submit_lecturas" name="submit_lecturas" value="Generar edo. cuenta" style="margin-left: 85%;"/>                                
            </form>                        
        </div>        
    </body>
</html>
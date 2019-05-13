<?php
    session_start();

    include_once("../WEB-INF/Classes/Catalogo.class.php");
    include_once("../WEB-INF/Classes/Usuario.class.php");
    include_once("../WEB-INF/Classes/Menu.class.php");
    include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {        
        header("Location: index.php");
    }
    // Para mantener los filtros y paginados de la tabla
        if (isset($_GET['page']) && isset($_GET['filter'])) {
            $filter = str_replace("_XX__XX_", " ", $_GET['filter']);
            $page = $_GET['page'];
        } else {
            $page = "0";
            $filter = "";
        }
    $permisos_grid = new PermisosSubMenu();
    $same_page = "facturacion/clientes_morosos.php";
    $permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
?>

<!DOCTYPE html>
<html lang="es">
<body>
    <!-- <div class="principal" style="font-size: 14px;"> -->
    <input type="hidden" id="page" name="page" value="<?php echo $page; ?>"/>
    <input type="hidden" id="filter" name="filter" value="<?php echo $filter; ?>"/>
    <div class="row">
        <div class="col-8 col-md-6">
            <?php if($permisos_grid->getModificar()){ ?>
                <button class='btn btn-primary' title='Recalcular clientes morosos' onclick='VerClientesFacturacio(); return false;'>Recalcular clientes morosos</button>
            <?php } ?>
        </div>
        <div class="col-4 col-md-6 d-flex justify-content-end">
            <?php if($permisos_grid->getAlta()){ ?>
                <button class="btn btn-success" title="Nuevo" onclick='cambiarContenidos("ventas/cliente_nuevo.php","Facturación > Clientes morosos > Nuevo cliente");' style=" cursor: pointer;"><span class="oi oi-plus"></span> Nuevo</button>
            <?php } ?>
        </div>
    </div>
    <!-- inicio de la tabla -->
    <div class="bg-light rounded" style="height:100%; padding:8px;"> 
        <div class="table-responsive">
            <table class="table">
                <thead class="thead-dark">

                    <tr>

                        <?php

                        $cabeceras = array("Estatus", "Clave", "Nombre", "RFC","Tipo de morosidad", "Facturas", "", "No marcar moroso");
                    
                        echo "<th>" . $cabeceras[0] . "</th>";

                        echo "<th>" . $cabeceras[1] . "</th>";

                        echo "<th>" . $cabeceras[2] . "</th>";

                        echo "<th>" . $cabeceras[3] . "</th>";

                        echo "<th>" . $cabeceras[4] . "</th>";

                        echo "<th>" . $cabeceras[5] . "</th>";

                        echo "<th>" . $cabeceras[6] . "</th>";

                        echo "<th>" . $cabeceras[7] . "</th>";
                        
                        ?>                        

                    </tr>

                </thead>
                <tbody>                    
                    <?php

                        $catalogo = new Catalogo();

                        $usuario = new Usuario();

                        if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)) {/* Si es vendedor */

                            $query = $catalogo->obtenerLista("SELECT 

                                c.Suspendido AS Suspendido, c.ClaveCliente AS ClaveCliente, c.NombreRazonSocial AS NombreRazonSocial,

                                c.RFC AS RFC, c.IdEstatusCobranza AS IdEstatusCobranza, c.IdTipoMorosidad AS IdTipoMorosidad, c.Activo AS Activo,
                                
                                c.NoVolverMoroso AS NoVolverMoroso, IF(ISNULL(c.IdTipoMorosidad),'Cliente',tf.TipoFacturacion) AS NombreMoroso,
                                
                                IF(c.IdEstatusCobranza=2 && c.IdTipoMorosidad=1,'1',IF(cc.Moroso='1' && (c.IdTipoMorosidad=1 || ISNULL(c.IdTipoMorosidad)),'1',
                                
                                IF(ccc.Moroso='1'&& c.IdTipoMorosidad=3,'1',''))) AS Moroso FROM c_cliente AS c 
                                
                                LEFT JOIN c_centrocosto AS cc ON cc.ClaveCliente=c.ClaveCliente
                                
                                LEFT JOIN c_cen_costo AS ccc ON ccc.ClaveCliente=c.ClaveCliente
                                
                                LEFT JOIN c_tipofacturacion AS tf ON tf.IdTipoFacturacion=c.IdTipoMorosidad
                                
                                WHERE c.EjecutivoCuenta='" . $_SESSION['idUsuario'] . "' AND c.Activo = 1
                                
                                GROUP BY c.ClaveCliente ORDER BY Moroso DESC;");

                        } else {

                            $query = $catalogo->obtenerLista("SELECT c.Suspendido AS Suspendido, c.ClaveCliente AS ClaveCliente,
                                
                                c.NombreRazonSocial AS NombreRazonSocial, c.RFC AS RFC, c.IdEstatusCobranza AS IdEstatusCobranza,
                                
                                c.IdTipoMorosidad AS IdTipoMorosidad,c.Activo AS Activo, c.NoVolverMoroso AS NoVolverMoroso,
                                
                                IF(ISNULL(c.IdTipoMorosidad),'Cliente',tf.TipoFacturacion) AS NombreMoroso,
                                
                                IF(c.IdEstatusCobranza=2 && (c.IdTipoMorosidad=1 || ISNULL(c.IdTipoMorosidad)),'1',
                                
                                IF(cc.Moroso='1' && c.IdTipoMorosidad=2 ,'1',IF(ccc.Moroso='1'&& c.IdTipoMorosidad=3,'1',''))) AS Moroso
                                
                                FROM c_cliente AS c LEFT JOIN c_centrocosto AS cc ON cc.ClaveCliente=c.ClaveCliente
                                
                                LEFT JOIN c_cen_costo AS ccc ON ccc.ClaveCliente=c.ClaveCliente
                                
                                LEFT JOIN c_tipofacturacion AS tf ON tf.IdTipoFacturacion=c.IdTipoMorosidad
                                
                                WHERE c.Activo = 1 GROUP BY c.ClaveCliente ORDER BY Moroso DESC");

                        }

                        while ($rs = mysql_fetch_array($query)) {

                            $cambiar_estatus = true;

                            $select = 0;

                            if ($rs['Moroso'] == "1") {

                                $estatus = "Desmarcar como moroso";

                                $leyenda = "Moroso";

                                $liga = "facturacion/detalle_facturas_morosos.php?RFC=" . $rs['RFC'];

                                $select = 1;

                            } else {

                                $cambiar_estatus = false;

                                $estatus = "Marcar como moroso";

                                $leyenda = "Al corriente";

                                $liga = "";

                                $select = 2;

                            }

                            if ($rs['Suspendido']) {

                                $leyenda = "Suspendido";

                                $select = 0;

                            }

                            echo "<tr>";

                            echo "<td>$leyenda</td>";

                            echo "<td>" . $rs['ClaveCliente'] . "</td>";

                            echo "<td>" . $rs['NombreRazonSocial'] . "</td>";

                            echo "<td>" . $rs['RFC'] . "</td>";

                            echo "<td>" . $rs['NombreMoroso'] . "</td>";

                            echo "<td>";

                            if ($liga != "") {

                                echo "<a href='#' onclick='lanzarPopUp(\"Facturas no pagadas\",\"$liga\"); return false;'>Facturas no pagadas</a>";

                            }

                            echo "</td>";

                            echo "<td>";

                            if($permisos_grid->getModificar() && $rs['NoVolverMoroso'] == '0'){//Si tiene permiso de editar

                                if ($cambiar_estatus) {

                                    echo "<select id='ddl_estatus_" . $rs['ClaveCliente'] . "' name='ddl_estatus_" . $rs['ClaveCliente'] . "'>";

                                    $s = "";

                                    if ($select == 2) {

                                        $s = "selected = 'selected'";

                                    }

                                    echo "<option value='2' $s>Normal</option>";
                                    
                                    $s = "";

                                    if ($select == 1) {

                                        $s = "selected = 'selected'";

                                    }

                                    echo "<option value='1' $s>Moroso</option>";

                                    $s = "";

                                    if ($select == 0) {

                                        $s = "selected = 'selected'";

                                    }
                                    echo "<option value='0' $s>Suspendido</option>";
                                
                                    echo "<input type='button' class='boton' id='boton_" . $rs['ClaveCliente'] . "' value='Guardar' onclick='marcarClienteMorosoSuspendido(\"" . $rs['ClaveCliente'] . "\",\"ddl_estatus_" . $rs['ClaveCliente'] . "\",\"" . $rs['IdTipoMorosidad'] . "\"); return false;'/>";

                                } else {

                                    echo "<button class='boton btn btn-dark' id='boton_" . $rs['ClaveCliente'] . "' onclick='marcarClienteMoroso(\"" . $rs['ClaveCliente'] . "\",\"" . $rs['IdEstatusCobranza'] . "\",\"" . $rs['IdTipoMorosidad'] . "\",\"ddl_estatus_" . $rs['ClaveCliente'] . "\"); return false;'>$estatus</button>";

                                }

                            }

                            echo "</td>";

                            echo "<td>";

                            if($permisos_grid->getModificar()){

                                if ($rs['NoVolverMoroso'] == '1') {

                                    ?>

                                    <input type='checkbox' checked='checked' name="<?php echo $rs['ClaveCliente'] ?>" id="<?php echo $rs['ClaveCliente'] ?>"  value="<?php echo $rs['ClaveCliente'] ?>"  onclick="NoPonerMoroso('<?php echo $rs['ClaveCliente'] ?>');"/>

                                    <?php

                                } else {

                                    ?>

                                    <input type='checkbox' name="<?php echo $rs['ClaveCliente'] ?>" id="<?php echo $rs['ClaveCliente'] ?>"  value="<?php echo $rs['ClaveCliente'] ?>"  onclick="NoPonerMoroso('<?php echo $rs['ClaveCliente'] ?>');"/>

                                    <?php

                                }

                            } echo "</td>";

                            echo "</tr>";
                        }

                    ?>                    
                </tbody>
            </table>
        </div>
    </div>  
    <!-- fin de la tabla -->  
    <!-- </div> -->
    <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    <script type="text/javascript" language="javascript" src="resources/js/paginas/cliente_moroso.js"></script>
</body>
</html>
<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Contrato.class.php");
include_once("../WEB-INF/Classes/Contacto.class.php");
include_once("../WEB-INF/Classes/CentroCosto.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
$catalogo = new Catalogo();
if ($_POST['id'] != 6) {
    if (isset($_POST['solicitud'])) {
        $id_solicitud = $_POST['solicitud'];
        $cc_precagrados = "";

        /* Verificamos si el usuario tiene permiso para autorizar la venta directa en caso de que sea necesario */
        $usuario = new Usuario();
        
        $usuario->getRegistroById($_SESSION['idUsuario']);
        $idAlmacen = "";
        
        /*if ($usuario->getIdAlmacen() != null && $usuario->getIdAlmacen() != "") {
            $idAlmacen = $usuario->getIdAlmacen();
        }*/

        $query = $catalogo->obtenerLista("SELECT
	c_solicitud.fecha_solicitud AS Fecha,
        c_solicitud.fecha_regreso AS Fecha_Regreso,
        c_solicitud.IdFormaPago AS IdFormaPago,
        c_solicitud.dias_credito AS dias_credito,
        c_solicitud.dias_revision AS dias_revision,
        c_solicitud.id_tiposolicitud AS tiposolicitud,
        c_solicitud.estatus AS estatus,
        c_solicitud.id_almacen AS id_almacen,
        c_solicitud.IdContacto,
	c_cliente.ClaveCliente AS ClaveCliente,
	c_cliente.NombreRazonSocial AS Cliente,
	c_solicitud.id_solicitud AS ID,
	k_solicitud.cantidad AS Cantidad,
	k_solicitud.tipo AS Tipo,
        k_solicitud.NoSerie AS NoSerie,
        k_solicitud.Modelo AS Modelo,
        k_solicitud.ClaveCentroCosto AS Localidad,
        k_solicitud.TipoInventario
        FROM c_solicitud
        INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
        INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
        WHERE c_solicitud.id_solicitud =" . $id_solicitud . "
        ORDER BY k_solicitud.id_partida");
        $tipo_solicitud = 0;
        $estatus = -1;
        $rss = mysql_fetch_array($query);
        ?>
        <table>
            <tr>
                <td>
                    <label for="cliente">
                        Cliente
                    </label>
                </td>
                <td>
                    <select id="cliente" name="cliente" class="size filtro" onchange="cambiarccosto('cliente');" width="600" style="width: 600px;" >
                        <!--<option value="">Selecciona el cliente</option>-->
                        <?php
                        $query2 = $catalogo->obtenerLista("SELECT
                        c_cliente.NombreRazonSocial AS Nombre, c_cliente.ClaveCliente AS ID
                        FROM c_cliente
                        WHERE c_cliente.Activo=1 ORDER BY Nombre ASC");
                        while ($rs = mysql_fetch_array($query2)) {
                            if ($rs['ID'] == $rss['ClaveCliente']) {
                                echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";                                
                            }/* else {
                                echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                            }*/
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <div id="div_almacen" class='oculto'>
                        <label for="almacen">Almacén destino:</label>
                        <select id="almacen" name="almacen" class="size filtro" width="200" style="width: 200px;"  >
                            <option value="">Selecciona el almacén</option>
                            <?php
                            $consulta = "SELECT * FROM k_responsablealmacen ra,c_almacen a ,c_usuario us 
                                WHERE ra.IdUsuario='" . $_SESSION['idUsuario'] . "' AND (a.TipoAlmacen = 1 OR a.Surtir = 1) AND a.Activo=1 
                                AND ra.IdAlmacen=a.id_almacen AND ra.IdUsuario=us.IdUsuario ORDER BY a.nombre_almacen ASC";
                            $query2 = $catalogo->obtenerLista($consulta);

                            if(mysql_num_rows($query2) == 0){//Si no tiene almacen predeterminado
                                $consulta = "SELECT * FROM c_almacen a WHERE (a.TipoAlmacen = 1 OR a.Surtir = 1) AND a.Activo=1 ORDER BY a.nombre_almacen ASC";
                                $query2 = $catalogo->obtenerLista($consulta);
                            }
                            while ($rs = mysql_fetch_array($query2)) {
                                $s = "";
                                if ($rss['id_almacen'] != "" && $rs['id_almacen'] == $rss['id_almacen']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value=\"" . $rs['id_almacen'] . "\" $s>" . $rs['nombre_almacen'] . "</option>";
                            }
                            ?>                            
                        </select>
                        <input type="hidden" id="almacen_p" name="almacen_p" value="<?php echo $rss['id_almacen']; ?>" />
                    </div>                
                </td>                  
            </tr>
            <tr style="display: none;">
                <td>
                    <label for="cliente">
                        Tipo de solicitud
                    </label>
                </td>
                <td>                    
                    <select class="size filtro" id="tipo_solicitud" name="tipo_solicitud" style="width: 600px" onchange="cambioTipoSolicitud();">
                        <?php
                        $query2 = $catalogo->getListaAlta("c_tiposolicitud", "Nombre");
                        while ($rs = mysql_fetch_array($query2)) {
                            if ($rss['tiposolicitud'] == "6") {
                                echo "<option value='" . $rss['tiposolicitud'] . "'>Venta directa</option>";
                                break;
                            } else {
                                if ($rs['IdTipoMovimiento'] != "6") {
                                    $s = "";
                                    if ($rs['IdTipoMovimiento'] == $rss['tiposolicitud']) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rs['IdTipoMovimiento'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Fecha&nbsp;: <?php echo $rss['Fecha'] ?></td>                
            </tr>            
        </table>
        <table>
            <tr>
                <td><label for="dias_credito">D&iacute;as de cr&eacute;dito</label></td>
                <td>
                    <input type="text" id="dias_credito" name="dias_credito" value="<?php
                    if (isset($rss['dias_credito'])) {
                        echo $rss['dias_credito'];
                    }
                    ?>"/>
                </td>
                <td><label for="formas_pago">Formas de pago</label></td>
                <td>
                    <select id="formas_pago" name="formas_pago">
                        <?php
                        $query2 = $catalogo->getListaAlta("c_formapago", "Nombre");
                        echo "<option value=''>Selecciona una forma de pago</option>";
                        while ($rs = mysql_fetch_array($query2)) {
                            $s = "";
                            if (isset($rss['IdFormaPago']) && $rss['IdFormaPago'] == $rs['IdFormaPago']) {
                                $s = "selected='selected'";
                            }
                            echo "<option value='" . $rs['IdFormaPago'] . "' $s>" . $rs['Nombre'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td><label for="dias_revision">D&iacute;as de revisi&oacute;n de factura</label></td>
                <td>
                    <input type="text" id="dias_revision" name="dias_revision" value="<?php
                    if (isset($rss['dias_revision'])) {
                        echo $rss['dias_revision'];
                    }
                    ?>"/>
                </td>
            </tr>
            <tr>
                <td>
                    Contacto de atención a la solicitud:
                </td>
                <td>                    
                    <select id="contacto_sol" name="contacto_sol" style="width: 200px;" class="size filtro">
                        <?php                            
                            $contacto = new Contacto();
                            $resultContacto = $contacto->getTodosContactosCliente($rss['ClaveCliente']);
                            echo "<option value=\"null\">Selecciona el contacto</option>";
                            while($rsContacto = mysql_fetch_array($resultContacto)){
                                $s = "";
                                if($rss['IdContacto'] == $rsContacto['IdContacto']){
                                    $s = "selected = 'selected'";
                                }
                                echo "<option value='".$rsContacto['IdContacto']."' $s>".$rsContacto['Nombre']." (".$rsContacto['TipoContacto']." - ".$rsContacto['localidad'].")</option>";
                            }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <table id="retorno" style="display: none;">
            <tr>
                <td><label for="fecha_regreso">Fecha de devoluci&oacute;n</label></td>
                <td>
                    <input type="text" class="fecha" id="fecha_regreso" name="fecha_regreso" value="<?php
                    if (isset($rss['Fecha_Regreso'])) {
                        echo $rss['Fecha_Regreso'];
                    }
                    ?>"/>
                </td>
            </tr>
        </table>
        <div id="tabla_detalles">
        </div>
        <div id="tabla_edicion_sol">
        </div>
        <br/>
        Comentario
        <?php
        $comentario = "";
        $query = $catalogo->obtenerLista("SELECT comentario FROM `c_solicitud` WHERE id_solicitud = $id_solicitud;");
        while ($rs = mysql_fetch_array($query)) {
            $comentario = $rs['comentario'];
        }
        ?>
        <textarea id="comentario_normal" name="comentario_normal" style="resize: none; width: 95%;"><?php echo $comentario; ?></textarea>
        <br/>
        Comentario para quien autoriza
        <?php
        $comentario = "";
        $query = $catalogo->obtenerLista("SELECT comentario_creo FROM `c_solicitud` WHERE id_solicitud = $id_solicitud;");
        while ($rs = mysql_fetch_array($query)) {
            $comentario = $rs['comentario_creo'];
        }
        ?>
        <textarea id="comentario_creo" name="comentario_creo" style="resize: none; width: 95%;"><?php echo $comentario; ?></textarea>
        <br/>
        <input type="hidden" id="localidades_anteriores" name="localidades_anteriores" value=""/>
        <div id="datos_contratos">            
        </div>
        <?php
        $consulta = "SELECT ks.ClaveCentroCosto, ks.IdServicio, ks.IdAnexoClienteCC, ks.IdKServicio, kacc.ClaveAnexoTecnico, cat.NoContrato
            FROM `k_solicitud` AS ks
            LEFT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = ks.IdAnexoClienteCC
            LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = kacc.ClaveAnexoTecnico
            WHERE ks.id_solicitud = $id_solicitud GROUP BY ks.ClaveCentroCosto, ks.IdServicio, ks.IdKServicio ORDER BY id_partida;";
        $result = $catalogo->obtenerLista($consulta);
        $fila = 0;
        while ($rs = mysql_fetch_array($result)) {
            echo "<input type=\"hidden\" id=\"".$rs['ClaveCentroCosto']."_precargado\" name=\"".$rs['ClaveCentroCosto']."_precargado\" value=\"$fila\"/>";
            echo "<input type=\"hidden\" id=\"contrato_precargado$fila\" name=\"contrato_precargado$fila\" value=\"" . $rs['NoContrato'] . "\"/>";
            echo "<input type=\"hidden\" id=\"anexo_precargado$fila\" name=\"anexo_precargado$fila\" value=\"" . $rs['IdAnexoClienteCC'] . "\"/>";
            echo "<input type=\"hidden\" id=\"servicio_precargado$fila\" name=\"servicio_precargado$fila\" value=\"" . $rs['IdServicio'] . "-" . $rs['IdKServicio'] . "\"/>";
            $fila++;
        }
        echo "<input type=\"hidden\" id=\"num_filas\" name=\"num_filas\" value=\"" . $fila . "\"/>";
        ?>        
       <?php
        /* if($tipo_solicitud == "6" && $estatus == "0" && $autoriza_vd){
          echo "<input type=\"button\" id=\"autorizar\" class=\"boton\" name=\"autorizar\" value=\"Autorizar\" onclick=\"alert('Autorizar'); return false;\"/>";
          } */
        ?>
        <input type="hidden" id="solicitud" name="solicitud" value="<?php echo $id_solicitud; ?>"/>
        <input type="hidden" id="contador" name="contador" value="<?php echo $contador; ?>"/>
        <input type="hidden" id="cliente_propio" name="cliente_propio" value="0"/>
        <?php
        echo '<script type="text/javascript">actualizarDatosContratoPrecargados(\'' . $cc_precagrados . '\');cargarTablaDetalles();</script>';
    } else {
        ?>
        <table>
            <tr>
                <td>
                    <label for="cliente">
                        Cliente
                    </label>
                </td>
                <td>
                    <select id="cliente" name="cliente" class="size filtro" onchange="cambiarccosto('cliente');
                            cargarClientePropio('cliente');" width="600" style="width: 600px;"  >
                        <option value="">Selecciona el cliente</option>
                        <?php
                        $query2 = $catalogo->obtenerLista("SELECT
                            c_cliente.NombreRazonSocial AS Nombre, c_cliente.ClaveCliente AS ID
                            FROM c_cliente
                            WHERE c_cliente.Activo=1 ORDER BY Nombre ASC");
                        while ($rs = mysql_fetch_array($query2)) {
                            echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <div id="div_almacen" class='oculto'>
                        <label for="almacen">Almacén destino:</label>
                        <select id="almacen" name="almacen" class="size filtro" width="200" style="width: 200px;"  >
                            <option value="">Selecciona el almacén</option>
                            <?php
                            $consulta = "SELECT * FROM k_responsablealmacen ra,c_almacen a ,c_usuario us 
                                WHERE ra.IdUsuario='" . $_SESSION['idUsuario'] . "' AND (a.TipoAlmacen = 1 OR a.Surtir = 1) AND a.Activo=1 
                                AND ra.IdAlmacen=a.id_almacen AND ra.IdUsuario=us.IdUsuario ORDER BY a.nombre_almacen ASC";
                            $query2 = $catalogo->obtenerLista($consulta);

                            if(mysql_num_rows($query2) == 0){//Si no tiene almacen predeterminado
                                $consulta = "SELECT * FROM c_almacen a WHERE (a.TipoAlmacen = 1 OR a.Surtir = 1) AND a.Activo=1 ORDER BY a.nombre_almacen ASC";
                                $query2 = $catalogo->obtenerLista($consulta);
                            }
                                                        
                            while ($rs = mysql_fetch_array($query2)) {
                                $s = "";
                                if ($rss['id_almacen'] != "" && $rs['id_almacen'] == $rss['id_almacen']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value=\"" . $rs['id_almacen'] . "\" $s>" . $rs['nombre_almacen'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>                
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td><label for="dias_credito">D&iacute;as de cr&eacute;dito</label></td>
                <td>
                    <input type="text" id="dias_credito" name="dias_credito"/>
                </td>
                <td><label for="formas_pago">Formas de pago</label></td>
                <td>
                    <select id="formas_pago" name="formas_pago">
                        <?php
                        $query = $catalogo->getListaAlta("c_formapago", "Nombre");
                        echo "<option value=''>Selecciona una forma de pago</option>";
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value='" . $rs['IdFormaPago'] . "'>" . $rs['Nombre'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td><label for="dias_revision">D&iacute;as de revisi&oacute;n de factura</label></td>
                <td>
                    <input type="text" id="dias_revision" name="dias_revision"/>
                </td>
            </tr>
            <tr class="row_contacto" style="display: none;">
                <td>
                    Contacto de atención a la solicitud:
                </td>
                <td>
                    <select id="contacto_sol" name="contacto_sol" style="width: 200px;" class="size filtro">                        
                    </select>
                </td>
            </tr>
        </table>
        <table id="retorno" style="display: none;">
            <tr>
                <td><label for="fecha_regreso">Fecha de devoluci&oacute;n</label></td>
                <td>
                    <input type="text" class="fecha" id="fecha_regreso" name="fecha_regreso"/>
                </td>
            </tr>
        </table>    

        <div id="tabla_detalles">
        </div>
        <div id="tabla_edicion_sol">
        </div>
        <br/>
        Comentario
        <textarea id="comentario_normal" name="comentario_normal" style="resize: none; width: 95%;"></textarea>
        <br/>
        Comentario para quien autoriza
        <textarea id="comentario_creo" name="comentario_creo" style="resize: none; width: 95%;"></textarea>
        <br/>
        <input type="hidden" id="localidades_anteriores" name="localidades_anteriores" value=""/>
        <input type="hidden" id="cliente_propio" name="cliente_propio" value="0"/>
        <div id="datos_contratos"></div>    
        <?php
    }
} else {
    if (isset($_POST['solicitud'])) {
        $id_solicitud = $_POST['solicitud'];
        $cc_precagrados = "";

        /* Verificamos si el usuario tiene permiso para autorizar la venta directa en caso de que sea necesario */
        $usuario = new Usuario();

        $usuario->getRegistroById($_SESSION['idUsuario']);
        
        $idAlmacen = "";
        if ($usuario->getIdAlmacen() != null && $usuario->getIdAlmacen() != "") {
            $idAlmacen = $usuario->getIdAlmacen();
        }
        
        

        $query = $catalogo->obtenerLista("SELECT
	c_solicitud.fecha_solicitud AS Fecha,
        c_solicitud.fecha_regreso AS Fecha_Regreso,
        c_solicitud.IdFormaPago AS IdFormaPago,
        c_solicitud.dias_credito AS dias_credito,
        c_solicitud.dias_revision AS dias_revision,
        c_solicitud.id_tiposolicitud AS tiposolicitud,
        c_solicitud.estatus AS estatus,
        c_solicitud.id_almacen AS id_almacen,
        c_solicitud.IdContacto,
	c_cliente.ClaveCliente AS ClaveCliente,
	c_cliente.NombreRazonSocial AS Cliente,
	c_solicitud.id_solicitud AS ID,
	k_solicitud.cantidad AS Cantidad,
	k_solicitud.tipo AS Tipo,
        k_solicitud.NoSerie AS NoSerie,
        k_solicitud.Modelo AS Modelo,
        k_solicitud.ClaveCentroCosto AS Localidad,
        k_solicitud.TipoInventario,
        c_ventadirecta.Clave_Localidad AS ClaveCC,
        c_ventadirecta.Fecha AS FechaVD,
        c_cliente.EjecutivoCuenta AS EjecutivoCuenta
        FROM c_solicitud
        INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
        INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
        INNER JOIN c_ventadirecta ON c_ventadirecta.id_solicitud = c_solicitud.id_solicitud
        WHERE c_solicitud.id_solicitud =" . $id_solicitud . "
        ORDER BY k_solicitud.id_partida");
        $tipo_solicitud = 0;
        $estatus = -1;
        $rss = mysql_fetch_array($query);
        ?>
        <table>
            <?php
            $catalogo = new Catalogo();
            $query = $catalogo->obtenerLista("SELECT c_puesto.IdPuesto FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario']);
            $rs = mysql_fetch_array($query);
            if ($rs['IdPuesto'] != 11) {
                ?>
                <tr>
                    <td>
                        <label for="vendedor">Vendedor:</label>
                    </td>
                    <td>
                        <select id="vendedor" name="vendedor" class="filtro" width="200" style="width: 200px" 
                                onchange="cargarclientes('vendedor', 'cliente')">
                            <!--<option value="">Selecciona el vendedor</option>-->
                            <?php
                            $query = $catalogo->obtenerLista("SELECT c_usuario.IdUsuario,CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoPaterno,\" \",c_usuario.ApellidoMaterno) AS Nombre 
                                FROM `c_usuario` 
                                INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_puesto.IdPuesto=11 ORDER BY Nombre");
                            while ($rs = mysql_fetch_array($query)) {
                                if ($rs['IdUsuario'] == $rss['EjecutivoCuenta']) {
                                    echo "<option value=\"" . $rs['IdUsuario'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                } /*else {
                                    echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                                }*/
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <label for="cliente">Cliente:</label>
                    </td>
                    <td>
                        <select id="cliente" name="cliente" class="filtro" width="200" style="width: 200px" 
                                onchange="cambiarlocalidad('cliente', 'localidad_vd');">
                            <!--<option value="">Selecciona el cliente</option>-->
                            <?php
                            $query = $catalogo->obtenerLista("SELECT
                                        c_cliente.NombreRazonSocial AS NombreCliente,
                                        c_cliente.ClaveCliente AS ClaveCliente
                                        FROM c_usuario
                                        INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                        WHERE c_usuario.IdUsuario=" . $rss['EjecutivoCuenta'] . " AND c_cliente.Activo = 1
                                        ORDER BY NombreCliente ASC");
                            while ($rs = mysql_fetch_array($query)) {
                                if ($rs['ClaveCliente'] == $rss['ClaveCliente']) {
                                    echo "<option value=\"" . $rs['ClaveCliente'] . "\" selected>" . $rs['NombreCliente'] . "</option>";
                                } /*else {
                                    echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreCliente'] . "</option>";
                                }*/
                            }
                            ?>
                        </select>
                    </td>
                    <?php
                } else {
                    ?>
                <tr>
                    <td>
                        <label for="cliente">Cliente:</label>
                    </td>
                    <td>
                        <input type="hidden" id="vendedor" name="vendedor" value="<?php echo $_SESSION['idUsuario']; ?>"/>
                        <select id="cliente" name="cliente" width="200" style="width: 200px" onchange="cambiarlocalidad('cliente', 'localidad_vd');
                                cambiarccosto('cliente');
                                cargarClientePropio('cliente');">
                            <!--<option value="">Selecciona el cliente</option>-->
                            <?php
                            $query = $catalogo->obtenerLista("SELECT
                                    c_cliente.NombreRazonSocial AS NombreCliente,
                                    c_cliente.ClaveCliente AS ClaveCliente
                                    FROM c_usuario
                                    INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                    WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . "  AND c_cliente.Activo = 1
                                    ORDER BY NombreCliente ASC");
                            while ($rs = mysql_fetch_array($query)) {
                                if ($rs['ClaveCliente'] == $rss['ClaveCliente']) {
                                    echo "<option value=\"" . $rs['ClaveCliente'] . "\" selected>" . $rs['NombreCliente'] . "</option>";
                                }/* else {
                                    echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreCliente'] . "</option>";
                                }*/
                            }
                            ?>
                        </select>
                    </td>
                <?php } ?>
                <td>Localidad</td>
                <td>
                    <select id="localidad_vd" name="localidad_vd" class="filtro localidad" width="200" style="width: 200px" >
                        <!--<option value="">Selecciona la localidad</option>-->
                        <?php
                        $query = $catalogo->obtenerLista("SELECT c_centrocosto.ClaveCentroCosto AS ID,c_centrocosto.Nombre AS Nombre 
                            FROM c_centrocosto 
                            INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                            WHERE c_cliente.ClaveCliente='" . $rss['ClaveCliente'] . "' AND c_centrocosto.Activo = 1");
                        while ($rsp = mysql_fetch_array($query)) {
                            if ($rsp['ID'] == $rss['ClaveCC']) {
                                echo "<option value=\"" . $rsp['ID'] . "\" selected>" . $rsp['Nombre'] . "</option>";
                            } /*else {
                                echo "<option value=\"" . $rsp['ID'] . "\">" . $rsp['Nombre'] . "</option>";
                            }*/
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="fecha">Fecha</label></td>
                <?php
                $today = getdate();
                ?>
                <td><input type="text" id="Fecha" name="Fecha" class="fecha" width="200" 
                           style="width: 200px" value="<?php echo $rss['FechaVD']; ?>" readonly="readonly"/></td>
                <td></td><td></td>
            </tr>            
        </table> 
        <table>
            
            <tr>
                <td>
                    Contacto de atención a la solicitud:
                </td>
                <td>
                    <select id="contacto_sol" name="contacto_sol" style="width: 200px;" class="size filtro">
                        <?php
                            $contacto = new Contacto();
                            $resultContacto = $contacto->getTodosContactosCliente($rss['ClaveCliente']);
                            echo "<option value=\"null\">Selecciona el contacto</option>";
                            while($rsContacto = mysql_fetch_array($resultContacto)){
                                $s = "";
                                if($rss['IdContacto'] == $rsContacto['IdContacto']){
                                    $s = "selected = 'selected'";
                                }
                                echo "<option value='".$rsContacto['IdContacto']."' $s>".$rsContacto['Nombre']." (".$rsContacto['TipoContacto'].")</option>";
                            }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <table id="retorno" style="display: none;">
            <tr>
                <td><label for="fecha_regreso">Fecha de devoluci&oacute;n</label></td>
                <td>
                    <input type="text" class="fecha" id="fecha_regreso" name="fecha_regreso" readonly="readonly" value="<?php
                    if (isset($rss['Fecha_Regreso'])) {
                        echo $rss['Fecha_Regreso'];
                    }
                    ?>"/>
                </td>
            </tr>
        </table>
        <div id="tabla_detalles">
        </div>
        <div id="tabla_edicion_sol">
        </div>
        <br/>
        Comentario
        <?php
        $comentario = "";
        $query = $catalogo->obtenerLista("SELECT comentario FROM `c_solicitud` WHERE id_solicitud = $id_solicitud;");
        while ($rs = mysql_fetch_array($query)) {
            $comentario = $rs['comentario'];
        }
        ?>
        <textarea id="comentario_normal" name="comentario_normal" style="resize: none; width: 95%;"><?php echo $comentario; ?></textarea>
        <br/>
        Comentario para quien autoriza
        <?php
        $comentario = "";
        $query = $catalogo->obtenerLista("SELECT comentario_creo FROM `c_solicitud` WHERE id_solicitud = $id_solicitud;");
        while ($rs = mysql_fetch_array($query)) {
            $comentario = $rs['comentario_creo'];
        }
        ?>
        <textarea id="comentario_creo" name="comentario_creo" style="resize: none; width: 95%;"><?php echo $comentario; ?></textarea>
        <br/>
        <input type="hidden" id="localidades_anteriores" name="localidades_anteriores" value=""/>
        <div id="datos_contratos">            
        </div>
        <?php
        $consulta = "SELECT ks.ClaveCentroCosto, ks.IdServicio, ks.IdAnexoClienteCC, ks.IdKServicio, kacc.ClaveAnexoTecnico, cat.NoContrato
            FROM `k_solicitud` AS ks
            LEFT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = ks.IdAnexoClienteCC
            LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = kacc.ClaveAnexoTecnico
            WHERE ks.id_solicitud = $id_solicitud GROUP BY ks.ClaveCentroCosto, ks.IdServicio, ks.IdKServicio ORDER BY id_partida;";
        $result = $catalogo->obtenerLista($consulta);
        $fila = 0;
        while ($rs = mysql_fetch_array($result)) {
            echo "<input type=\"hidden\" id=\"".$rs['ClaveCentroCosto']."_precargado\" name=\"".$rs['ClaveCentroCosto']."_precargado\" value=\"$fila\"/>";
            echo "<input type=\"hidden\" id=\"contrato_precargado$fila\" name=\"contrato_precargado$fila\" value=\"" . $rs['NoContrato'] . "\"/>";
            echo "<input type=\"hidden\" id=\"anexo_precargado$fila\" name=\"anexo_precargado$fila\" value=\"" . $rs['IdAnexoClienteCC'] . "\"/>";
            echo "<input type=\"hidden\" id=\"servicio_precargado$fila\" name=\"servicio_precargado$fila\" value=\"" . $rs['IdServicio'] . "-" . $rs['IdKServicio'] . "\"/>";
            $fila++;
        }
        echo "<input type=\"hidden\" id=\"num_filas\" name=\"num_filas\" value=\"" . $fila . "\"/>";
        ?>        
         <?php
        /* if($tipo_solicitud == "6" && $estatus == "0" && $autoriza_vd){
          echo "<input type=\"button\" id=\"autorizar\" class=\"boton\" name=\"autorizar\" value=\"Autorizar\" onclick=\"alert('Autorizar'); return false;\"/>";
          } */
        ?>
        <input type="hidden" id="solicitud" name="solicitud" value="<?php echo $id_solicitud; ?>"/>
        <input type="hidden" id="contador" name="contador" value="<?php echo $contador; ?>"/>
        <input type="hidden" id="cliente_propio" name="cliente_propio" value="0"/>
        <?php
        echo '<script type="text/javascript">actualizarDatosContratoPrecargados(\'' . $cc_precagrados . '\');cargarTablaDetalles();</script>';
    } else {
        ?>
         <table>
            <?php
            $catalogo = new Catalogo();
            $query = $catalogo->obtenerLista("SELECT c_puesto.IdPuesto FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario']);
            $rs = mysql_fetch_array($query);
            if ($rs['IdPuesto'] != 11) {
                ?>
                <tr>
                    <td>
                        <label for="vendedor">Vendedor:</label>
                    </td>
                    <td>
                        <select id="vendedor" name="vendedor" class="filtro" width="200" style="width: 200px" 
                                onchange="cargarclientes('vendedor', 'cliente')">
                            <option value="">Selecciona el vendedor</option>
                            <?php
                            $query = $catalogo->obtenerLista("SELECT c_usuario.IdUsuario,CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoPaterno,\" \",c_usuario.ApellidoMaterno) AS Nombre FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_puesto.IdPuesto=11 ORDER BY Nombre");
                            if (isset($_GET['vendedor'])) {
                                while ($rs = mysql_fetch_array($query)) {
                                    if ($rs['IdUsuario'] == $_GET['vendedor']) {
                                        echo "<option value=\"" . $rs['IdUsuario'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                                    }
                                }
                            } else {
                                while ($rs = mysql_fetch_array($query)) {
                                    echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <label for="cliente">Cliente:</label>
                    </td>
                    <td>
                        <select id="cliente" name="cliente" class="filtro" width="200" style="width: 200px" onchange="cambiarlocalidad('cliente', 'localidad_vd');">
                            <option value="">Selecciona el cliente</option>
                            <?php
                            if (isset($_GET['cliente'])) {
                                $query = $catalogo->obtenerLista("SELECT
                                        c_cliente.NombreRazonSocial AS NombreCliente,
                                        c_cliente.ClaveCliente AS ClaveCliente
                                        FROM c_usuario
                                        INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                        WHERE c_usuario.IdUsuario=" . $_GET['vendedor'] . " AND c_cliente.Activo = 1
                                        ORDER BY NombreCliente ASC");
                                while ($rs = mysql_fetch_array($query)) {
                                    if ($rs['ClaveCliente'] == $_GET['cliente']) {
                                        echo "<option value=\"" . $rs['ClaveCliente'] . "\" selected>" . $rs['NombreCliente'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreCliente'] . "</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <?php
                } else {
                    ?>
                <tr>
                    <td>
                        <label for="cliente">Cliente:</label>
                    </td>
                    <td>
                        <input type="hidden" id="vendedor" name="vendedor" value="<?php echo $_SESSION['idUsuario']; ?>"/>
                        <select id="cliente" name="cliente" width="200" style="width: 200px" onchange="cambiarlocalidad('cliente', 'localidad_vd');
                                cambiarccosto('cliente');
                                cargarClientePropio('cliente');">
                            <option value="">Selecciona el cliente</option>
                            <?php
                            $query = $catalogo->obtenerLista("SELECT
                                    c_cliente.NombreRazonSocial AS NombreCliente,
                                    c_cliente.ClaveCliente AS ClaveCliente
                                    FROM c_usuario
                                    INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                    WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo = 1
                                    ORDER BY NombreCliente ASC");
                            if (isset($_GET['cliente'])) {
                                while ($rs = mysql_fetch_array($query)) {
                                    if ($rs['ClaveCliente'] == $_GET['cliente']) {
                                        echo "<option value=\"" . $rs['ClaveCliente'] . "\" selected>" . $rs['NombreCliente'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreCliente'] . "</option>";
                                    }
                                }
                            } else {
                                while ($rs = mysql_fetch_array($query)) {
                                    echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreCliente'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                <?php } ?>
                <td>Localidad</td>
                <td>
                    <select id="localidad_vd" name="localidad_vd" class="filtro localidad" width="200" style="width: 200px" >
                        <option value="">Selecciona la localidad</option>
                        <?php
                        if (isset($_GET['cliente'])) {
                            $query = $catalogo->obtenerLista("SELECT c_centrocosto.ClaveCentroCosto AS ID,c_centrocosto.Nombre AS Nombre 
                                FROM c_centrocosto 
                                INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                                WHERE c_cliente.ClaveCliente='" . $_GET['cliente'] . "'");
                            while ($rsp = mysql_fetch_array($query)) {
                                echo "<option value=\"" . $rsp['ID'] . "\" >" . $rsp['Nombre'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="fecha">Fecha</label></td>
                <?php
                $today = getdate();
                ?>
                <td><input type="text" id="Fecha" name="Fecha" class="fecha" width="200" 
                           style="width: 200px" value="<?php echo $today['year'] . "-" . $today['mon'] . "-" . $today['mday']; ?>" readonly="readonly"/></td>
                <td></td><td></td>
            </tr>
        </table> 
        <table>
            
            <tr class="row_contacto" style="display: none;">
                <td>
                    Contacto de atención a la solicitud:
                </td>
                <td>
                    <select id="contacto_sol" name="contacto_sol" style="width: 200px;" class="size filtro">                        
                    </select>
                </td>
            </tr>
        </table>
        <br/><br/><br/>
        <div id="tabla_detalles">
        </div>
        <div id="tabla_edicion_sol">
        </div>
        <br/>
        Comentario
        <textarea id="comentario_normal" name="comentario_normal" style="resize: none; width: 95%;"></textarea>
        <br/>
        Comentario para quien autoriza
        <textarea id="comentario_creo" name="comentario_creo" style="resize: none; width: 95%;"></textarea>
        <br/>
        <input type="hidden" id="localidades_anteriores" name="localidades_anteriores" value=""/>
        <input type="hidden" id="cliente_propio" name="cliente_propio" value="0"/>
        <div id="datos_contratos"></div>
        <?php
    }
}?>
<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
if (isset($_POST['id'])) {
    $id_solicitud = $_POST['id'];
    include_once("../WEB-INF/Classes/Catalogo.class.php");
    include_once("../WEB-INF/Classes/Contrato.class.php");
    include_once("../WEB-INF/Classes/CentroCosto.class.php");
    include_once("../WEB-INF/Classes/Usuario.class.php");
    
    $catalogo = new Catalogo();
    $cc_precagrados = "";    
    
    /*Verificamos si el usuario tiene permiso para autorizar la venta directa en caso de que sea necesario*/
    $usuario = new Usuario();
    
    $usuario->getRegistroById($_SESSION['idUsuario']);
    $idAlmacen = "";
    if($usuario->getIdAlmacen()!=null && $usuario->getIdAlmacen()!=""){
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
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/editar_sol_equipo.js"></script>
        <style>
            .size{width: 200px;}
        </style>
    </head>
    <body>
    <form id="solform">
        <table>
            <tr>
                <td>
                    <label for="cliente">
                        Cliente
                    </label>
                </td>
                <td>
                    <select id="cliente" name="cliente" class="size filtro" onchange="cambiarccosto('cliente');" width="600" style="width: 600px;" >
                        <option value="">Selecciona el cliente</option>
                        <?php                        
                        $query2 = $catalogo->obtenerLista("SELECT
                        c_cliente.NombreRazonSocial AS Nombre, c_cliente.ClaveCliente AS ID
                        FROM c_cliente
                        WHERE c_cliente.Activo=1 ORDER BY Nombre ASC");                        
                        while ($rs = mysql_fetch_array($query2)) {
                            if ($rs['ID'] != $rss['ClaveCliente']) {
                                echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                            } else {
                                echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                            }
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
                            $query2 = $catalogo->obtenerLista("SELECT a.id_almacen, a.nombre_almacen 
                                FROM `c_almacen` AS a WHERE a.TipoAlmacen = 1;");
                            while ($rs = mysql_fetch_array($query2)) {
                                $s = "";
                                if($idAlmacen!="" && $rs['id_almacen']==$idAlmacen){
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
            <tr>
                <td>
                    <label for="cliente">
                        Tipo de solicitud
                    </label>
                </td>
                <td>                    
                    <select class="size filtro" id="tipo_solicitud" name="tipo_solicitud" style="width: 600px" onchange="cambioTipoSolicitud();">
                        <?php
                            $query2 = $catalogo->getListaAlta("c_tiposolicitud", "Nombre");                            
                            while($rs = mysql_fetch_array($query2)){
                                if($rss['tiposolicitud'] == "6"){
                                    echo "<option value='".$rss['tiposolicitud']."'>Venta directa</option>";
                                    break;
                                }else{
                                    if($rs['IdTipoMovimiento']!="6"){
                                        $s = "";                                
                                        if($rs['IdTipoMovimiento'] == $rss['tiposolicitud']){
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value='".$rs['IdTipoMovimiento']."' $s>".$rs['Nombre']."</option>";
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
                    <input type="text" id="dias_credito" name="dias_credito" value="<?php if(isset($rss['dias_credito'])){ echo $rss['dias_credito'];} ?>"/>
                </td>
                <td><label for="formas_pago">Formas de pago</label></td>
                <td>
                    <select id="formas_pago" name="formas_pago">
                        <?php
                            $query2 = $catalogo->getListaAlta("c_formapago", "Nombre");
                            echo "<option value=''>Selecciona una forma de pago</option>";
                            while($rs = mysql_fetch_array($query2)){       
                                $s = "";
                                if(isset($rss['IdFormaPago']) && $rss['IdFormaPago'] == $rs['IdFormaPago']){
                                    $s = "selected='selected'";
                                }
                                echo "<option value='".$rs['IdFormaPago']."' $s>".$rs['Nombre']."</option>";
                            }
                        ?>
                    </select>
                </td>
                <td><label for="dias_revision">D&iacute;as de revisi&oacute;n de factura</label></td>
                <td>
                    <input type="text" id="dias_revision" name="dias_revision" value="<?php if(isset($rss['dias_revision'])){ echo $rss['dias_revision'];} ?>"/>
                </td>
            </tr>
        </table>
        <table id="retorno" style="display: none;">
            <tr>
                <td><label for="fecha_regreso">Fecha de devoluci&oacute;n</label></td>
                <td>
                    <input type="text" class="fecha" id="fecha_regreso" name="fecha_regreso" readonly="readonly" value="<?php if(isset($rss['Fecha_Regreso'])){ echo $rss['Fecha_Regreso'];} ?>"/>
                </td>
            </tr>
        </table>
        <img class="imagenMouse" src="resources/images/Erase.png" title="Nuevo" onclick='eliminarfilaulti();' style="float: right; cursor: pointer;" /> <br/> <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='agregarcamposol()' style="float: right; cursor: pointer;" />  
        <br/><br/><br/>
        <table id="tsolformtabla">
            <tr>
                <td>
                    <label for="numero1">
                        Cantidad
                    </label>
                </td>
                <td>
                    <input type="text" id="numero1" name="numero1" value ="<?php echo $rss['Cantidad'] ?>" maxlength="5" style="width: 50px;"/>
                </td>
                <td>
                    <label for="tipo1">
                        Tipo
                    </label>
                </td>
                <td>
                    <select id="tipo1" name="tipo1" class="tipo" onchange="cambiarselectmodelo('tipo1', 'modelo1'); 
                        mostrarTipoInventario('tipo1','tipo_inventario1','div_serie_cliente1');" style="width: 115px;">
                        <option value="">Seleccione tipo</option>

                        <?php
                        if ($rss['Tipo'] == 0) {
                            echo "<option value=\"0\" selected>Equipo</option>";
                            $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
                            while ($rs = mysql_fetch_array($query2)) {
                                if ($rss['Tipo'] == $rs['ID']) {
                                    echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                } else {
                                    echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                                }
                            }
                        } else {
                            echo "<option value=\"0\" >Equipo</option>";
                            $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
                            echo "SELECT DISTINCT
                                    c_tipocomponente.IdTipoComponente AS ID
                            FROM
                                    c_componente
                            INNER JOIN c_tipocomponente ON c_tipocomponente.IdTipoComponente=c_componente.IdTipoComponente
                            WHERE c_componente.Modelo='" . $rss['Modelo'] . "'";
                                                        $query3 = $catalogo->obtenerLista("SELECT DISTINCT
                                    c_tipocomponente.IdTipoComponente AS ID
                            FROM
                                    c_componente
                            INNER JOIN c_tipocomponente ON c_tipocomponente.IdTipoComponente=c_componente.IdTipoComponente
                            WHERE c_componente.NoParte='" . $rss['Modelo'] . "'");
                            $rst = mysql_fetch_array($query3);
                            while ($rs = mysql_fetch_array($query2)) {
                                if ($rst['ID'] == $rs['ID']) {
                                    echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                } else {
                                    echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                                }
                            }
                        }
                        ?>
                    </select>
                </td>
                
                <td>
                    <select id="modelo1" name="modelo1" class="size filtro" style="width: 250px;">
                        <option value="">Selecciona el modelo</option>
                        <?php
                        if ($rss['Tipo'] == 0) {
                            $query3 = $catalogo->obtenerLista("SELECT DISTINCT
                            c_equipo.Modelo AS Modelo,
                            c_equipo.NoParte AS Parte 
                            FROM
                            c_equipo
                            ORDER BY Modelo");
                            while ($rsp = mysql_fetch_array($query3)) {
                                if ($rsp['Parte'] == $rss['Modelo']) {
                                    echo "<option value=\"" . $rsp['Parte'] . "\" selected>" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                                } else {
                                    echo "<option value=\"" . $rsp['Parte'] . "\" >" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                                }
                            }
                        } else {
                            $query3 = $catalogo->obtenerLista("SELECT DISTINCT
                            c_componente.Modelo AS Modelo,
                            c_componente.NoParte AS Parte 
                            FROM
                            c_componente
                            ORDER BY Modelo");
                            while ($rsp = mysql_fetch_array($query3)) {
                                if ($rsp['Parte'] == $rss['Modelo']) {
                                    echo "<option value=\"" . $rsp['Parte'] . "\" selected>" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                                } else {
                                    echo "<option value=\"" . $rsp['Parte'] . "\" >" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                                }
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <select id="localidad1" name="localidad1" class="size filtro localidad" style="width: 250px;" 
                            onchange="actualizarDatosContrato(); mostrarEquiposLocalidad('localidad1','serie_con_cliente1');">
                        <?php
                        $query2 = $catalogo->obtenerLista("SELECT c_centrocosto.ClaveCentroCosto AS ID,
                            c_centrocosto.Nombre AS Nombre
                            FROM c_cliente
                            INNER JOIN c_centrocosto ON c_centrocosto.ClaveCliente=c_cliente.ClaveCliente
                            WHERE c_cliente.ClaveCliente='" . $rss['ClaveCliente'] . "'");
                        echo "<option value='' >Selecciona la localidad</option>";
                        while ($rs = mysql_fetch_array($query2)) {                            
                            if ($rs['ID'] == $rss['Localidad']) { 
                                $cc_precagrados = $rs['ID'];
                                echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                            } else {
                                echo "<option value=\"" . $rs['ID'] . "\" >" . $rs['Nombre'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <select id="tipo_inventario1" name="tipo_inventario1" style="display: none;">
                        <?php
                            $query2 = $catalogo->obtenerLista("SELECT idTipo AS ID, Nombre FROM `c_tipoinventario` WHERE idTipo IN(1,9) AND Activo = 1;");
                            while ($rsp = mysql_fetch_array($query2)) {
                                $s = "";
                                if($rsp['ID'] == $rss['TipoInventario']){
                                    $s = "selected = 'selected'";
                                }
                                echo "<option value=\"" . $rsp['ID'] . "\" $s>" . $rsp['Nombre'] . "</option>";
                            }
                        ?>
                    </select>
                    <div id="div_serie_cliente1" style="display: none;">
                        <label for="serie_con_cliente1">Equipo en localidad</label>
                        <select id="serie_con_cliente1" name="serie_con_cliente1">
                            <option value="">Selecciona un equipo</option>
                        </select>
                    </div>
                    <input type="hidden" id="serie_asociada1" name="serie_asociada1" value="<?php echo $rss['NoSerie']; ?>"/>
                </td>
            </tr>
            <?php
            $contador = 2;           
            $numResults = mysql_num_rows($query);
            while ($rss = mysql_fetch_array($query)) {
                ?>
                <tr>
                    <td>
                        <label for="numero<?php echo $contador ?>">
                            Cantidad
                        </label>
                    </td>
                    <td>
                        <input type="text" id="numero<?php echo $contador ?>" name="numero<?php echo $contador ?>" value ="<?php echo $rss['Cantidad'] ?>" maxlength="5" style="width: 50px;"/>
                    </td>
                    <td>
                        <label for="tipo<?php echo $contador ?>">
                            Tipo
                        </label>
                    </td>
                    <td>
                        <select id="tipo<?php echo $contador ?>" class="tipo" name="tipo<?php echo $contador ?>" 
                                onchange="cambiarselectmodelo('tipo<?php echo $contador; ?>', 'modelo<?php echo $contador; ?>'); 
                                mostrarTipoInventario('tipo<?php echo $contador; ?>','tipo_inventario<?php echo $contador ?>',
                                'div_serie_cliente<?php echo $contador ?>');" style="width: 115px;">
                            <option value="">Selecciona el tipo</option>
                            $id_tipocompo;
                            <?php
                            if ($rss['Tipo'] == 0) {
                                echo "<option value=\"0\" selected>Equipo</option>";
                                $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
                                while ($rs = mysql_fetch_array($query2)) {
                                    if ($rss['Tipo'] == $rs['ID']) {
                                        echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                                    }
                                }
                            } else {
                                echo "<option value=\"0\" >Equipo</option>";
                                $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
                                $query3 = $catalogo->obtenerLista("SELECT DISTINCT
                                        c_tipocomponente.IdTipoComponente AS ID
                                FROM c_componente
                                INNER JOIN c_tipocomponente ON c_tipocomponente.IdTipoComponente=c_componente.IdTipoComponente
                                WHERE c_componente.NoParte='" . $rss['Modelo'] . "'");
                                $rst = mysql_fetch_array($query3);
                                $id_tipocompo=$rst['ID'];
                                while ($rs = mysql_fetch_array($query2)) {
                                    if ($rst['ID'] == $rs['ID']) {
                                        echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </td>                    
                    <td>
                        <select id="modelo<?php echo $contador; ?>" name="modelo<?php echo $contador; ?>" class="size filtro" style="width: 250px;">
                            <option value="">Selecciona el modelo</option>
                            <?php
                            if ($rss['Tipo'] == 0) {
                                $query3 = $catalogo->obtenerLista("SELECT DISTINCT
                                    c_equipo.Modelo AS Modelo,
                                    c_equipo.NoParte AS Parte FROM c_equipo
                                    ORDER BY Modelo");
                                while ($rsp = mysql_fetch_array($query3)) {
                                    if ($rsp['Parte'] == $rss['Modelo']) {
                                        echo "<option value=\"" . $rsp['Parte'] . "\" selected>" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rsp['Parte'] . "\" >" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                                    }
                                }
                            } else {
                                $query3 = $catalogo->obtenerLista("SELECT DISTINCT
                                        c_componente.Modelo AS Modelo,
                                        c_componente.NoParte AS Parte 
                                FROM
                                        c_componente
                                INNER JOIN c_tipocomponente ON c_tipocomponente.IdTipoComponente=c_componente.IdTipoComponente
                                WHERE c_tipocomponente.IdTipoComponente=" . $id_tipocompo . "
                                        ORDER BY Modelo");
                                while ($rsp = mysql_fetch_array($query3)) {
                                    if ($rsp['Parte'] == $rss['Modelo']) {
                                        echo "<option value=\"" . $rsp['Parte'] . "\" selected>" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rsp['Parte'] . "\" >" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <select id="localidad<?php echo $contador; ?>" name="localidad<?php echo $contador; ?>" class="size filtro localidad" style="width: 250px;" 
                                onchange="actualizarDatosContrato(); mostrarEquiposLocalidad('localidad<?php echo $contador; ?>','serie_con_cliente<?php echo $contador; ?>');">
                            <?php
                            $query4 = $catalogo->obtenerLista("SELECT c_centrocosto.ClaveCentroCosto AS ID,
                            c_centrocosto.Nombre AS Nombre
                            FROM c_cliente
                            INNER JOIN c_centrocosto ON c_centrocosto.ClaveCliente=c_cliente.ClaveCliente
                            WHERE c_cliente.ClaveCliente='" . $rss['ClaveCliente'] . "'");
                            echo "<option value='' >Selecciona la localidad</option>";
                            while ($rs = mysql_fetch_array($query4)) {
                                if ($rs['ID'] == $rss['Localidad']) {
                                    $cc_precagrados .= ("&_&".$rs['ID']);
                                    echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                } else {
                                    echo "<option value=\"" . $rs['ID'] . "\" >" . $rs['Nombre'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <select id="tipo_inventario<?php echo $contador; ?>" name="tipo_inventario<?php echo $contador; ?>" style="display: none;">
                            <?php
                                $query2 = $catalogo->obtenerLista("SELECT idTipo AS ID, Nombre FROM `c_tipoinventario` WHERE idTipo IN(1,9) AND Activo = 1;");
                                while ($rsp = mysql_fetch_array($query2)) {
                                    $s = "";
                                    if($rsp['ID'] == $rss['TipoInventario']){
                                        $s = "selected = 'selected'";
                                    }
                                    echo "<option value=\"" . $rsp['ID'] . "\" $s>" . $rsp['Nombre'] . "</option>";
                                }
                            ?>
                        </select>
                        <div id="div_serie_cliente<?php echo $contador; ?>" style="display: none;">
                            <label for="serie_con_cliente<?php echo $contador; ?>">Equipo en localidad</label>
                            <select id="serie_con_cliente<?php echo $contador; ?>" name="serie_con_cliente<?php echo $contador; ?>">
                                <option value="">Selecciona un equipo</option>
                            </select>
                        </div>
                        <input type="hidden" id="serie_asociada<?php echo $contador; ?>" name="serie_asociada<?php echo $contador; ?>" 
                               value="<?php echo $rss['NoSerie']; ?>"/>
                    </td>
                </tr>
                <?php                
                $contador++;                                
            }            
            ?>
        </table>
        <br/>
        Comentario
        <?php 
            $comentario = "";
            $query = $catalogo->obtenerLista("SELECT comentario FROM `c_solicitud` WHERE id_solicitud = $id_solicitud;");
            while($rs = mysql_fetch_array($query)){
                $comentario = $rs['comentario'];
            }
        ?>
        <textarea id="comentario_normal" name="comentario_normal" style="resize: none; width: 95%;"><?php echo $comentario; ?></textarea>
        <br/>
        Comentario para quien autoriza
        <?php 
            $comentario = "";
            $query = $catalogo->obtenerLista("SELECT comentario_creo FROM `c_solicitud` WHERE id_solicitud = $id_solicitud;");
            while($rs = mysql_fetch_array($query)){
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
            while($rs = mysql_fetch_array($result)){
                echo "<input type=\"hidden\" id=\"contrato_precargado$fila\" name=\"contrato_precargado$fila\" value=\"".$rs['NoContrato']."\"/>";
                echo "<input type=\"hidden\" id=\"anexo_precargado$fila\" name=\"anexo_precargado$fila\" value=\"".$rs['IdAnexoClienteCC']."\"/>";
                echo "<input type=\"hidden\" id=\"servicio_precargado$fila\" name=\"servicio_precargado$fila\" value=\"".$rs['IdServicio'] ."-".$rs['IdKServicio']."\"/>";
                $fila++;
            }
            echo "<input type=\"hidden\" id=\"num_filas\" name=\"num_filas\" value=\"".$fila."\"/>";            
        ?>        
        <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
        <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('ventas/list_sol_equipo.php', 'Solicitudes');"/>
        <?php
            /*if($tipo_solicitud == "6" && $estatus == "0" && $autoriza_vd){
                echo "<input type=\"button\" id=\"autorizar\" class=\"boton\" name=\"autorizar\" value=\"Autorizar\" onclick=\"alert('Autorizar'); return false;\"/>";
            }*/
        ?>
        <input type="hidden" id="solicitud" name="solicitud" value="<?php echo $id_solicitud; ?>"/>
        <input type="hidden" id="contador" name="contador" value="<?php echo $contador; ?>"/>
        <input type="hidden" id="cliente_propio" name="cliente_propio" value="0"/>
    </form>    
<?php echo '<script type="text/javascript">actualizarDatosContratoPrecargados(\''.$cc_precagrados.'\');</script>'; } ?>
    </body>
</html>
<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
$catalogo = new Catalogo();
$equipo = new EquipoCaracteristicasFormatoServicio();

include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "almacen/autorizacion.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$consultaAlmacen = "";
$filtro_responsable_almacen = "";

$consultaAlmacen = "SELECT * FROM k_responsablealmacen ra,c_almacen a ,c_usuario us "
    . " WHERE ra.IdUsuario='" . $_SESSION['idUsuario'] . "' AND a.Activo=1 AND ra.IdAlmacen=a.id_almacen AND ra.IdUsuario=us.IdUsuario ORDER BY a.nombre_almacen ASC";
$queryAlmacen = $catalogo->obtenerLista($consultaAlmacen);
$idAlmacen = array();
$nombreAlmacen = array();
$contador = 0;
$mostrar_sin_idenitificar = false;

if(mysql_num_rows($queryAlmacen) == 0){
    $consultaAlmacen = "SELECT * FROM c_almacen a WHERE a.Activo=1 AND (a.TipoAlmacen = 1 OR a.Surtir = 1) ORDER BY a.nombre_almacen ASC";
    $queryAlmacen = $catalogo->obtenerLista($consultaAlmacen);
    while ($rs = mysql_fetch_array($queryAlmacen)) {
        $idAlmacen[$contador] = $rs["id_almacen"];
        $nombreAlmacen[$contador] = $rs["nombre_almacen"];
        $contador++;
    }
}else{
    while ($rs = mysql_fetch_array($queryAlmacen)) {
        $idAlmacen[$contador] = $rs["id_almacen"];
        $nombreAlmacen[$contador] = $rs["nombre_almacen"];
        $contador++;
    }
    $filtro_responsable_almacen = " AND a.id_almacen IN(".implode(",", $idAlmacen).") ";
}

$array_almacenes = array();

if(isset($_POST['almacenes']) && $_POST['almacenes']!=""){
    $filtro_responsable_almacen = " AND a.id_almacen IN(".$_POST['almacenes'].") ";
    $array_almacenes = explode(",", $_POST['almacenes']);
}else{
    $mostrar_sin_idenitificar = true;
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>        
    </head>
    <body>
        <div class="principal">
            <table style="width: 40%;">
                <tr>
                    <td><label for="almacen_filtro">Almacén: </label></td>
                    <td>
                        <select id="almacen_filtro" name="almacen_filtro[]" class="multiselect" multiple="multiple">
                            <?php                                
                                foreach ($idAlmacen as $key => $value) {
                                    if($value == 9){                                        
                                        continue;
                                    }
                                    $s = "";
                                    if(in_array($value, $array_almacenes)){
                                        $s = "selected='selected'";
                                    }                                    
                                    echo "<option value='$value' $s>" . $nombreAlmacen[$key] . "</option>";
                                }
                                /*Pintamos SIEMPRe equipos sin identificar*/
                                $consulta = "SELECT * FROM c_almacen a WHERE id_almacen = 9 ORDER BY a.nombre_almacen ASC;";
                                $query_si = $catalogo->obtenerLista($consulta);
                                while($rs_aux = mysql_fetch_array($query_si)){
                                    $s = "";
                                    if(in_array($rs_aux['id_almacen'], $array_almacenes)){
                                        $mostrar_sin_idenitificar = true;
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='".$rs_aux['id_almacen']."' $s>" . $rs_aux['nombre_almacen'] . "</option>";
                                }
                                
                            ?>
                        </select>
                    </td>
                    <td>
                        <input type="button" class="button" onclick="mostrarEntradasDeEquipo('<?php echo $same_page; ?>','almacen_filtro'); return false;" 
                               id="boton_aceptar" name="boton_aceptar" value="Mostrar equipos"/>
                    </td>
                </tr>
            </table>
            <br/><br/> 
            <?php if(isset($_POST['mostrar']) && $_POST['mostrar'] == "true"){ ?>
                <table id="tAlmacen" style='min-width: 100%'>
                    <thead>
                        <tr>
                            <th style='min-width: 2%; max-width: 2%; text-align: center'>Origen</th>
                            <th style='min-width: 30px; max-width: 30px; text-align: center'>No serie</th>
                            <th style='min-width: 30px; max-width: 30px; text-align: center'>Modelo</th>
                            <th style='min-width: 30px; max-width: 30px; text-align: center'>Fecha</th>                        
                            <th style='min-width: 2%; max-width: 2%; text-align: center'>Destino</th>
                            <th style='min-width: 2%; max-width: 2%; text-align: center'>Almacén</th>
                            <th style='min-width: 2%; max-width: 2%; text-align: center'>Contador B/N</th>
                            <th style='min-width: 2%; max-width: 2%; text-align: center'>Contador Color</th>
                            <th style='min-width: 2%; max-width: 20%; text-align: center'>Comentarios</th>
                            <th style='min-width: 2%; max-width: 20%; text-align: center'></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $consulta = "SELECT meq.id_movimientos, meq.NoSerie, meq.Fecha, a.id_almacen, b.id_bitacora, b.NoParte, e.Modelo,
                            (CASE WHEN !ISNULL(a.id_almacen) THEN a.id_almacen ELSE cc.ClaveCentroCosto END) AS id_nuevo,
                            (CASE WHEN !ISNULL(a.id_almacen) THEN a.nombre_almacen ELSE CONCAT(c.NombreRazonSocial,' - ',cc.Nombre) END) AS destino,
                            (CASE WHEN !ISNULL(a1.id_almacen) THEN a1.nombre_almacen ELSE CONCAT(c1.NombreRazonSocial,' - ',cc1.Nombre) END) AS origen,
                            IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'0',IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,'1',IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,'1',IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND kae.id_almacen=9,'1','0')))) AS MoverRojo,
                            rh.Retirado
                            FROM movimientos_equipo AS meq 
                            LEFT JOIN reportes_movimientos AS rm ON rm.id_movimientos = meq.id_movimientos
                            LEFT JOIN reportes_historicos AS rh ON rh.NumReporte = rm.id_reportes
                            LEFT JOIN c_almacen AS a ON meq.almacen_nuevo = a.id_almacen
                            LEFT JOIN c_cliente AS c ON meq.clave_cliente_nuevo = c.ClaveCliente 
                            LEFT JOIN c_centrocosto AS cc ON meq.clave_centro_costo_nuevo = cc.ClaveCentroCosto
                            LEFT JOIN c_almacen AS a1 ON meq.almacen_anterior = a1.id_almacen
                            LEFT JOIN c_cliente AS c1 ON meq.clave_cliente_anterior = c1.ClaveCliente 
                            LEFT JOIN c_centrocosto AS cc1 ON meq.clave_centro_costo_anterior = cc1.ClaveCentroCosto
                            LEFT JOIN c_bitacora AS b ON b.NoSerie = meq.NoSerie
                            LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte
                            LEFT JOIN k_almacenequipo AS kae ON kae.NoSerie = meq.NoSerie 
                            LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora = b.id_bitacora)
                            LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral
                            WHERE meq.pendiente = 1 AND kae.id_almacen = 9 AND !ISNULL(meq.almacen_nuevo) AND (!ISNULL(clave_cliente_anterior) OR !ISNULL(almacen_anterior)) AND meq.id_movimientos = (SELECT MAX(id_movimientos) FROM movimientos_equipo WHERE NoSerie = meq.NoSerie AND pendiente = 1 AND !ISNULL(almacen_nuevo))
                            $filtro_responsable_almacen";
                        if($mostrar_sin_idenitificar){
                        $consulta .= " UNION
                            SELECT 0 AS id_movimientos,k_almacenequipo.NoSerie,Fecha_ingreso AS Fecha,id_almacen,id_bitacora,c_bitacora.NoParte, 
                            c_equipo.Modelo ,id_almacen AS id_nuevo, '' AS destino, '' AS origen,
                            IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'0',IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,'1',IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,'1',IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND k_almacenequipo.id_almacen=9,'1','0')))) AS MoverRojo,
                            1
                            FROM k_almacenequipo 
                            INNER JOIN c_bitacora ON c_bitacora.NoSerie = k_almacenequipo.NoSerie
                            INNER JOIN c_equipo ON c_equipo.NoParte = c_bitacora.NoParte 
                            LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = 
                            (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro 
                            WHERE IdBitacora = c_bitacora.id_bitacora)
                            LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral
                            WHERE id_almacen = 9 
                            AND k_almacenequipo.NoSerie NOT IN(SELECT meq.NoSerie
                            FROM movimientos_equipo AS meq 
                            LEFT JOIN c_almacen AS a ON meq.almacen_nuevo = a.id_almacen
                            LEFT JOIN c_cliente AS c ON meq.clave_cliente_nuevo = c.ClaveCliente 
                            LEFT JOIN c_centrocosto AS cc ON meq.clave_centro_costo_nuevo = cc.ClaveCentroCosto
                            LEFT JOIN c_almacen AS a1 ON meq.almacen_anterior = a1.id_almacen
                            LEFT JOIN c_cliente AS c1 ON meq.clave_cliente_anterior = c1.ClaveCliente 
                            LEFT JOIN c_centrocosto AS cc1 ON meq.clave_centro_costo_anterior = cc1.ClaveCentroCosto
                            LEFT JOIN c_bitacora AS b ON b.NoSerie = meq.NoSerie
                            LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte
                            LEFT JOIN k_almacenequipo AS kae ON kae.NoSerie = meq.NoSerie                            
                            WHERE meq.pendiente = 1 AND kae.id_almacen = 9 AND !ISNULL(meq.almacen_nuevo) AND (!ISNULL(clave_cliente_anterior) OR !ISNULL(almacen_anterior)) AND meq.id_movimientos = (SELECT MAX(id_movimientos) FROM movimientos_equipo WHERE NoSerie = meq.NoSerie AND pendiente = 1 AND !ISNULL(almacen_nuevo)))";
                        }
                        $consulta .= " GROUP BY NoSerie ORDER BY id_movimientos DESC";
                        $query = $catalogo->obtenerLista($consulta);
                        $i = 0;
						$row_cont = mysql_num_rows($query);
                        while ($rs = mysql_fetch_array($query)) {/* Recorremos todos los tickets resultantes del query */
                            if($rs['MoverRojo'] == "1" && $rs['Retirado'] == "0"){//Cuando tiene un retiro sin el flujo completo, no se pone en esta pantalla
                                continue;
                            }
                            $i++;
                            $serie = $rs['NoSerie'];
                            echo "<tr>";
                            echo "<td align='center' scope='row'>" . $rs['origen'] . "</td>";
                            echo "<td align='center' scope='row'><a href='#' onclick='editarRegistro(\"almacen/alta_bitacora.php?consulta_tiquet=almacen/autorizacion.php\",\"" . $rs['id_bitacora'] . "\"); return false;'>" . $rs['NoSerie'] . "</a></td>";                        
                            echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Fecha'] . "</td>";                        
                            echo "<td align='center' scope='row'>" . $rs['destino'] . "</td>";
                            echo "<td align='center' scope='row'>";
                            echo "<select id='almacen_$i' name='almacen_$i' style='width: 145px'><option value='0'>Seleccione almacen</option> ";
                            $cont = 0;
                            while ($cont < count($idAlmacen)) {
                                $s = "";
                                if($idAlmacen[$cont] == $rs['id_nuevo']){
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $idAlmacen[$cont] . "' $s>" . $nombreAlmacen[$cont] . "</option>";
                                $cont++;
                            }
                            echo "</td>";                      
                            echo "<td <align='center' scope='row'><input type='text' id='contador_bn_$i' name='contador_bn_" . $rs['id_movimientos'] . "'/>
								<span id='span_$i' style='display:none;color:red'>Ingrese contador B/N</span></td>";
                            if($equipo->isColor($rs['NoParte']))
                            {
                                echo "<td <align='center' scope='row'><input type='text' id='contador_color_$i' name='contador_color_" . $rs['id_movimientos'] . "'/>
								<span id='spanColor_$i' style='display:none;color:red'>Ingrese contador Color</span></td>";
                            }else{
                                echo "<td></td>";
                            }
                            echo "<td align='center' scope='row'><input type='text' id='comentario_" . $rs['id_movimientos'] . "' name='comentario_" . $rs['id_movimientos'] . "'/>
                                </td>";
                            ?>
                            <td>
                            <?php
                                if($permisos_grid->getModificar()){
                                    if($equipo->isColor($rs['NoParte']))
                                    {
                            ?>
                                <input type='button' class='boton' onclick="actualizarEstatusMovimientoColor('almacen/autorizacion.php', '<?php echo $rs['id_movimientos']; ?>', '<?php echo "$i"; ?>', '<?php echo "comentario_" . $rs['id_movimientos']; ?>', '0', '9', '<?php echo $rs['NoSerie']; ?>' , '<?php echo "$row_cont"; ?>');return false;" value='Aceptar'/>
                            <?php
                                    }else{
                            ?>
                                <input type='button' class='boton' onclick="actualizarEstatusMovimiento('almacen/autorizacion.php', '<?php echo $rs['id_movimientos']; ?>', '<?php echo "$i"; ?>', '<?php echo "comentario_" . $rs['id_movimientos']; ?>', '0', '9', '<?php echo $rs['NoSerie']; ?>', '<?php echo "$row_cont"; ?>');return false;" value='Aceptar'/>
                            <?php
                                    }
                                }
                            ?>                        
                            </td>
                            <?php
                            echo "</tr>";                       
                        }
                        ?>
                    </tbody>               
                </table>
            <?php } ?>
        </div>
    </body>
</html>

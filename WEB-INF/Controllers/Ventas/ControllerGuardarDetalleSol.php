<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../../Classes/Componente.class.php");
include_once("../../Classes/Parametros.class.php");
include_once("../../Classes/Cliente.class.php");
include_once("../../Classes/CentroCosto.class.php");
include_once("../../Classes/Configuracion.class.php");
include_once("../../Classes/ReporteHistorico.class.php");

$llamadas = "";
$catalogo = new Catalogo();
$parametro_obj = new Parametros();
$reporte_historico = new ReporteHistorico();
?>
<div class='ui-state-highlight ui-corner-all' style='height: 33px;margin-top:15px; margin-bottom:15px;'><p><span class='ui-icon ui-icon-info' style='float: left;'></span>Pedido Capturado</p></div>
<?php
if ($_POST['tipo'] != 6) {
    if (!isset($_POST['SoloTabla'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);

        $partida = "1";
        if (isset($_POST['partida'])) {
            $partida = $_POST['partida'];
        }

        if (isset($parametros[$parametros['localidad']])) {
            $fila_cc = $parametros[$parametros['localidad']];
            $localidad = "'" . $parametros['localidad'] . "'";
        } else {
            $fila_cc = "";
            $localidad = "null";
        }

        if (isset($parametros['servicio' . $fila_cc])) {
            $datos_servicio = explode("-", $parametros['servicio' . $fila_cc]);
        } else {
            $datos_servicio = array();
            if (isset($parametros['localidad' . $partida])) {
                if (isset($parametros[$parametros['localidad' . $partida]])) {
                    $fila_aux = $parametros[$parametros['localidad' . $partida]];
                    $datos_servicio = explode("-", $parametros['servicio' . $fila_aux]);
                } else if (isset($parametros[$parametros['localidad' . $partida] . "_precargado"])) {
                    $fila_aux = $parametros[$parametros['localidad' . $partida] . "_precargado"];
                    $datos_servicio = explode("-", $parametros['servicio_precargado' . $fila_aux]);
                }
            }
        }

        if (!isset($datos_servicio[0]) || $datos_servicio[0] == "") {
            $datos_servicio[0] = "null";
        }
        if (!isset($datos_servicio[1]) || $datos_servicio[1] == "") {
            $datos_servicio[1] = "null";
        }

        if (isset($parametros['anexo' . $fila_cc]) && $parametros['anexo' . $fila_cc] != "") {
            $anexo = $parametros['anexo' . $fila_cc];
        } else {
            $anexo = "null";
            if (isset($parametros['localidad' . $partida])) {
                if (isset($parametros[$parametros['localidad' . $partida]])) {
                    $fila_aux = $parametros[$parametros['localidad' . $partida]];
                    if (isset($parametros['anexo' . $fila_aux]) && $parametros['anexo' . $fila_aux] != "") {
                        $anexo = $parametros['anexo' . $fila_aux];
                    }
                } else if (isset($parametros[$parametros['localidad' . $partida] . "_precargado"])) {
                    $fila_aux = $parametros[$parametros['localidad' . $partida] . "_precargado"];
                    if (isset($parametros['anexo_precargado' . $fila_aux]) && $parametros['anexo_precargado' . $fila_aux] != "") {
                        $anexo = $parametros['anexo_precargado' . $fila_aux];
                    }
                }
            }
        }

        $query = $catalogo->obtenerLista("SELECT MAX(id_partida) AS Maximo FROM k_solicitud WHERE id_solicitud=" . $_POST['solicitud']);
        $id_solicitud = $_POST['solicitud'];
        $maximo = 1;
        while ($rs = mysql_fetch_array($query)) {
            $maximo = $rs['Maximo'] + 1;
        }

        if ($parametros['tipo'] == "0" || (isset($parametros['tipo' . $partida]) && $parametros['tipo' . $partida] == "0")) {
            $enviar_correo = true;
            if (isset($_POST['partida'])) {
                if (isset($parametros['localidad' . $_POST['partida']])) {
                    $localidad = "'" . $parametros['localidad' . $_POST['partida']] . "'";
                } else {
                    $localidad = "null";
                    echo "Error: no se pudo agregar la partida, no hay localidad seleccionada";
                    return false;
                }

                $ubicacion = "";
                if (isset($parametros['ubicacion' . $_POST['partida']]) && $parametros['ubicacion' . $_POST['partida']] != "") {
                    $ubicacion = $parametros['ubicacion' . $_POST['partida']];
                }

                $retiro = "NULL";
                if (isset($parametros['retiro' . $_POST['partida']]) && $parametros['retiro' . $_POST['partida']] != "") {
                    $retiro = $parametros['retiro' . $_POST['partida']];
                    if ($retiro != "" && $retiro != "NULL" && !$reporte_historico->getRegistroById($retiro)) {
                        echo "Error: El retiro con folio $retiro no existe";
                        return false;
                    }
                }

                $consulta = "UPDATE k_solicitud SET cantidad='" . $parametros['numero' . $_POST['partida']] . "',Modelo='" . $parametros['modelo' . $_POST['partida']] . "',
                      ClaveCentroCosto=$localidad,tipo='" . $parametros['tipo' . $_POST['partida']] . "',IdAnexoClienteCC=$anexo,
                      IdServicio=" . $datos_servicio[0] . ",IdKServicio=" . $datos_servicio[1] . ",
                      TipoInventario=" . $parametros['tipo_inventario' . $_POST['partida']] . ",ReporteRetiro=$retiro, Ubicacion='$ubicacion',
                      UsuarioUltimaModificacion='" . $_SESSION['user'] . "',FechaUltimaModificacion=NOW(),Pantalla='PHP edicion_solicitud.php' WHERE id_solicitud=" . $_POST['solicitud'] . " AND id_partida='" . $_POST['partida'] . "'";

                $query2 = $catalogo->obtenerLista($consulta);
            } else {
                $ubicacion = "";
                if (isset($parametros['ubicacion']) && $parametros['ubicacion'] != "") {
                    $ubicacion = $parametros['ubicacion'];
                }

                $retiro = "NULL";
                if (isset($parametros['retiro']) && $parametros['retiro'] != "") {
                    $retiro = $parametros['retiro'];
                    if ($retiro != "" && $retiro != "NULL" && !$reporte_historico->getRegistroById($retiro)) {
                        echo "Error: El retiro con folio $retiro no existe";
                        return false;
                    }
                }

                $query2 = $catalogo->obtenerLista("INSERT INTO k_solicitud(id_solicitud,id_partida,cantidad,Modelo,ClaveCentroCosto,tipo,IdAnexoClienteCC,
                      IdServicio,IdKServicio,TipoInventario,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,
                      ReporteRetiro, Ubicacion)
                      VALUES('" . $id_solicitud . "','" . $maximo . "','" . $parametros['numero'] . "','" . $parametros['modelo'] . "',
                      $localidad,'" . $parametros['tipo'] . "'," . $anexo . "," . $datos_servicio[0] . "," . $datos_servicio[1] . ",
                      " . $parametros['tipo_inventario'] . ",'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),
                      'PHP edicion_solicitud.php',$retiro,'$ubicacion');");
            }
        } else {
            $extra = "";
            if (isset($_POST['partida'])) {
                $extra = $_POST['partida'];
            }
            if (isset($parametros['serie_con_cliente' . $extra]) && $parametros['serie_con_cliente' . $extra] != "") {
                $serie = "'" . $parametros['serie_con_cliente' . $extra] . "'";
            } else {
                $serie = "null";
            }
            //inicializamos toner
            $toner = 0;
            $toner_color = 0;
            //hacemos una consulta para ver las filas ya creadas
            $query = $catalogo->obtenerLista("SELECT
                k_solicitud.cantidad AS Cantidad,
                k_solicitud.tipo AS Tipo,
                k_solicitud.NoSerie AS NoSerie,
                k_solicitud.id_partida,
                k_solicitud.Modelo AS Modelo
                FROM c_solicitud
                INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
                INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
                WHERE c_solicitud.id_solicitud =" . $_POST['solicitud']);
            $equipo = new EquipoCaracteristicasFormatoServicio(); //inicializamos class
            $componente = new Componente();
            while ($rs = mysql_fetch_array($query)) {//verificamos si es equipo                
                if ($rs['Tipo'] == '0') {
                    //vemos si es color
                    if ($equipo->isColor($rs['Modelo'])) {
                        if ($parametro_obj->getRegistroById("11")) {
                            $toner_permitido = (int) $parametro_obj->getValor() * (int) $rs['Cantidad'];
                        } else {
                            $toner_permitido = 8 * (int) $rs['Cantidad'];
                        }
                        $toner_color += $toner_permitido;
                    }

                    if ($parametro_obj->getRegistroById("10")) {
                        $toner_permitido = (int) $parametro_obj->getValor() * (int) $rs['Cantidad'];
                    } else {
                        $toner_permitido = 2 * (int) $rs['Cantidad'];
                    }
                    $toner += $toner_permitido;
                } else {
                    if (!isset($rs['NoSerie'])) {//Si el equipo esta asociado a algun equipo ya instalado, esos toners no se descuentan.
                        $componente->getRegistroById($rs['Modelo']); //obtenemos los datos del componente                    
                        if ($componente->getTipo() == '2' && $_POST['partida'] != $rs['id_partida']) {//verificamos si es toner  
                            if ($componente->getColor() == NULL || $componente->getColor() == "1") {
                                $toner -= $rs['Cantidad']; //restamos la cantidad de toner previo
                            } else {
                                $toner_color -= $rs['Cantidad'];
                            }
                        }
                    }
                }
            }

            //buscamos los datos del nuevo componente
            $componente->getRegistroById($parametros['modelo' . $extra]);
            $boolean = true;
            if ($componente->getTipo() == '2') {//verificamos si es del tipo de toner
                if ($serie != "null") {//en caso de que traiga serie                    
                    $consulta = "SELECT * FROM c_inventarioequipo WHERE NoSerie=$serie";
                    $query2 = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query2)) {//verificamos que tenga registro                        
                        if ($equipo->isColor($rs['NoParteEquipo'])) {//vemos si es color
                            if ($parametro_obj->getRegistroById("11")) {
                                $toner_permitido = (int) $parametro_obj->getValor();
                            } else {
                                $toner_permitido = 8;
                            }
                            $toner_color = $toner_color + $toner_permitido;
                        }

                        if ($parametro_obj->getRegistroById("10")) {
                            $toner_permitido = (int) $parametro_obj->getValor();
                        } else {
                            $toner_permitido = 2;
                        }
                        $toner = $toner + $toner_permitido;
                    }
                }

                //echo "Color: ".$componente->getColor()." ".$parametros['numero'.$extra]." $toner $toner_color";
                if (
                        (($componente->getColor() != NULL && $componente->getColor() > 1) && ($toner_color - $parametros['numero' . $extra]) < 0) || (($componente->getColor() == NULL || $componente->getColor() == "1") && ($toner - $parametros['numero' . $extra]) < 0)
                ) {  //verificamos si toner es valido
                    $boolean = false;
                    /* En dado caso que sea un cliente propio, debe de dejar meter los toner que sean */
                    $cliente = new Cliente();
                    $cc = new CentroCosto();
                    if (isset($_POST['partida'])) {
                        $ClaveCC = $parametros['localidad' . $_POST['partida']];
                    } else {
                        $ClaveCC = $parametros['localidad'];
                    }

                    if ($cc->getRegistroById($ClaveCC)) {
                        if ($cliente->getRegistroById($cc->getClaveCliente()) && $cliente->getIdTipoCliente() == "7") {
                            $boolean = true;
                        }
                    }
                }
            } else {
                $boolean = true;
            }

            if ($boolean) {
                if (isset($_POST['partida'])) {
                    $ubicacion = "";
                    if (isset($parametros['ubicacion' . $_POST['partida']]) && $parametros['ubicacion' . $_POST['partida']] != "") {
                        $ubicacion = $parametros['ubicacion' . $_POST['partida']];
                    }

                    $retiro = "NULL";
                    if (isset($parametros['retiro' . $_POST['partida']]) && $parametros['retiro' . $_POST['partida']] != "") {
                        $retiro = $parametros['retiro' . $_POST['partida']];
                        if ($retiro != "" && $retiro != "NULL" && !$reporte_historico->getRegistroById($retiro)) {
                            echo "Error: El retiro con folio $retiro no existe";
                            return false;
                        }
                    }
                    $consulta = "UPDATE k_solicitud SET cantidad='" . $parametros['numero' . $_POST['partida']] . "',cantidad_autorizada='" . $parametros['numero' . $_POST['partida']] . "',
                        Modelo='" . $parametros['modelo' . $_POST['partida']] . "',ClaveCentroCosto= '" . $parametros['localidad' . $_POST['partida']] . "',
                      tipo='1',IdAnexoClienteCC=" . $anexo . ",IdServicio=" . $datos_servicio[0] . ",IdKServicio=" . $datos_servicio[1] . ",
                      TipoInventario=" . $parametros['tipo_inventario' . $_POST['partida']] . ",NoSerie=$serie,ReporteRetiro=$retiro, Ubicacion='$ubicacion',
                      UsuarioUltimaModificacion='" . $_SESSION['user'] . "',FechaUltimaModificacion=NOW(),Pantalla='PHP edicion_solicitud.php' WHERE id_solicitud=" . $_POST['solicitud'] . " AND id_partida='" . $_POST['partida'] . "'";
                } else {
                    $ubicacion = "";
                    if (isset($parametros['ubicacion']) && $parametros['ubicacion'] != "") {
                        $ubicacion = $parametros['ubicacion'];
                    }

                    $retiro = "NULL";
                    if (isset($parametros['retiro']) && $parametros['retiro'] != "") {
                        $retiro = $parametros['retiro'];
                        if ($retiro != "" && $retiro != "NULL" && !$reporte_historico->getRegistroById($retiro)) {
                            echo "Error: El retiro con folio $retiro no existe";
                            return false;
                        }
                    }

                    $consulta = "INSERT INTO k_solicitud(id_solicitud,id_partida,cantidad,cantidad_autorizada,Modelo,ClaveCentroCosto,
                      tipo,IdAnexoClienteCC,IdServicio,IdKServicio,TipoInventario,NoSerie,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,
                      FechaUltimaModificacion,Pantalla,ReporteRetiro,Ubicacion)
                      VALUES('" . $id_solicitud . "','" . $maximo . "','" . $parametros['numero'] . "','" . $parametros['numero'] . "',
                      '" . $parametros['modelo'] . "','" . $parametros['localidad'] . "','1'," . $anexo . "," . $datos_servicio[0] . "," . $datos_servicio[1] . ",
                      " . $parametros['tipo_inventario'] . ",$serie,'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),
                     'PHP edicion_solicitud.php',$retiro,'$ubicacion');";
                }
                //echo $consulta;
                $query2 = $catalogo->obtenerLista($consulta);
            } else {
                if ($componente->getColor() != NULL && $componente->getColor() > 1) {
                    if ($toner_color < 0) {
                        $toner_color = 0;
                    }
                    echo "Error: No se pudo agregar o actualizar el pedido de toner a color, ya que excede el limite máximo: <b>$toner_color</b> toners de color.";
                } else {
                    if ($toner < 0) {
                        $toner = 0;
                    }
                    echo "Error: No se pudo agregar o actualizar el pedido de toner negro, ya que excede el limite máximo: <b>$toner</b> toners b/n.";
                }
            }
        }
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
        k_solicitud.TipoInventario,
        k_solicitud.id_partida as id_partida,
        k_solicitud.ReporteRetiro,
        k_solicitud.Ubicacion
        FROM c_solicitud
        INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
        INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
        WHERE c_solicitud.id_solicitud =" . $_POST['solicitud'] . "
        ORDER BY k_solicitud.id_partida");
    ?>
    <table id="tsolformtabla">
        <?php
        $contador = 1;
        while ($rss = mysql_fetch_array($query)) {
            ?>
            <tr>
                <td>
                    <label for="numero<?php echo $rss['id_partida'] ?>">
                        Cantidad
                    </label>
                </td>
                <td>
                    <input type="text" id="numero<?php echo $rss['id_partida'] ?>" name="numero<?php echo $rss['id_partida'] ?>" value ="<?php echo $rss['Cantidad'] ?>" maxlength="5" style="width: 50px;" disabled="disabled"/>
                </td>
                <td>
                    <label for="tipo<?php echo $rss['id_partida'] ?>">
                        Tipo
                    </label>
                </td>
                <td>
                    <select id="tipo<?php echo $rss['id_partida'] ?>" class="tipo" name="tipo<?php echo $rss['id_partida'] ?>" 
                            onchange="cambiarselectmodelo('tipo<?php echo $rss['id_partida']; ?>', 'modelo<?php echo $rss['id_partida']; ?>');
                                            mostrarTipoInventario('tipo<?php echo $rss['id_partida']; ?>', 'tipo_inventario<?php echo $rss['id_partida'] ?>',
                                                    'div_serie_cliente<?php echo $rss['id_partida'] ?>');" style="width: 115px;" disabled="disabled">
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
                            $id_tipocompo = $rst['ID'];
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
                    <select id="modelo<?php echo $rss['id_partida']; ?>" name="modelo<?php echo $rss['id_partida']; ?>" class="size" style="width: 250px;" disabled="disabled">
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
                    <select id="localidad<?php echo $rss['id_partida']; ?>" name="localidad<?php echo $rss['id_partida']; ?>" class="size localidad" style="width: 250px;" 
                            onchange="actualizarDatosContrato();
                                            mostrarEquiposLocalidad('localidad<?php echo $rss['id_partida']; ?>', 'serie_con_cliente<?php echo $rss['id_partida']; ?>');" disabled="disabled">
                            <?php
                                $query4 = $catalogo->obtenerLista("SELECT c_centrocosto.ClaveCentroCosto AS ID,
                            c_centrocosto.Nombre AS Nombre
                            FROM c_cliente
                            INNER JOIN c_centrocosto ON c_centrocosto.ClaveCliente=c_cliente.ClaveCliente
                            WHERE c_cliente.ClaveCliente='" . $rss['ClaveCliente'] . "'");
                                echo "<option value='' >Selecciona la localidad</option>";
                                while ($rs = mysql_fetch_array($query4)) {
                                    if ($rs['ID'] == $rss['Localidad']) {
                                        $cc_precagrados .= ("&_&" . $rs['ID']);
                                        echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rs['ID'] . "\" >" . $rs['Nombre'] . "</option>";
                                    }
                                }
                                ?>
                    </select>
                </td>
                <td>
                    <select id="tipo_inventario<?php echo $rss['id_partida']; ?>" name="tipo_inventario<?php echo $rss['id_partida']; ?>" style="display: none;" >
                        <?php
                        $query2 = $catalogo->obtenerLista("SELECT idTipo AS ID, Nombre FROM `c_tipoinventario` WHERE idTipo IN(1,9) AND Activo = 1;");
                        while ($rsp = mysql_fetch_array($query2)) {
                            $s = "";
                            if ($rsp['ID'] == $rss['TipoInventario']) {
                                $s = "selected = 'selected'";
                            }
                            echo "<option value=\"" . $rsp['ID'] . "\" $s>" . $rsp['Nombre'] . "</option>";
                        }
                        ?>
                    </select>
                    <div id="div_serie_cliente<?php echo $rss['id_partida']; ?>" style="display: none;">
                        <label for="serie_con_cliente<?php echo $rss['id_partida']; ?>">Equipo en localidad</label>
                        <select id="serie_con_cliente<?php echo $rss['id_partida']; ?>" name="serie_con_cliente<?php echo $rss['id_partida']; ?>">
                            <option value="">Selecciona un equipo</option>
                        </select>
                    </div>
                    <input type="hidden" id="serie_asociada<?php echo $rss['id_partida']; ?>" name="serie_asociada<?php echo $rss['id_partida']; ?>" 
                           value="<?php echo $rss['NoSerie']; ?>"/>
                </td>
                <td>
                    <label for="ubicacion<?php echo $rss['id_partida']; ?>">Ubicación</label>
                </td>
                <td>
                    <input type="text" id="ubicacion<?php echo $rss['id_partida']; ?>" name="ubicacion<?php echo $rss['id_partida']; ?>" 
                           value='<?php echo $rss['Ubicacion'] ?>' disabled="disabled"/>
                </td>
                <td>
                    <label for="retiro<?php echo $rss['id_partida']; ?>"># retiro</label>
                </td>
                <td>
                    <input type="number" id="retiro<?php echo $rss['id_partida']; ?>" name="retiro<?php echo $rss['id_partida']; ?>"  value='<?php echo $rss['ReporteRetiro'] ?>' maxlength="6" style="width: 50px;" disabled="disabled"  maxlength="6"/>
                </td>
                <td id="editarsolrow<?php echo $rss['id_partida']; ?>">
                    <a onclick="editarfilasol(<?php echo $rss['id_partida']; ?>);"><img src="resources/images/Modify.png" title="Editar Fila"/></a>
                </td>
                <td>
                    <a onclick="eliminarfilasol(<?php echo $rss['id_partida']; ?>);"><img src="resources/images/Erase.png" title="Eliminar Fila"/></a>
                </td>
            </tr>
            <?php
            $contador++;
        }
        if ($contador > 1) {
            echo "<script>setfilas(1)</script>";
        } else {
            echo "<script>setfilas(0)</script>";
        }
        echo "<script>setcontador($contador)</script>";
        ?>
    </table>
    <br/>
    <?php
} else {
    if (!isset($_POST['SoloTabla'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
        if (isset($parametros[$parametros['localidad']])) {
            $fila_cc = $parametros[$parametros['localidad']];
            $localidad = "'" . $parametros['localidad'] . "'";
        } else {
            $fila_cc = "";
            $localidad = "null";
        }

        if (isset($parametros['servicio' . $fila_cc])) {
            $datos_servicio = explode("-", $parametros['servicio' . $fila_cc]);
        } else {
            $datos_servicio = array();
        }

        if (!isset($datos_servicio[0]) || $datos_servicio[0] == "") {
            $datos_servicio[0] = "null";
        }
        if (!isset($datos_servicio[1]) || $datos_servicio[1] == "") {
            $datos_servicio[1] = "null";
        }
        if (isset($parametros['anexo' . $fila_cc]) && $parametros['anexo' . $fila_cc] != "") {
            $anexo = $parametros['anexo' . $fila_cc];
        } else {
            $anexo = "null";
        }
        $query = $catalogo->obtenerLista("SELECT MAX(id_partida) AS Maximo FROM k_solicitud WHERE id_solicitud=" . $_POST['solicitud']);
        $id_solicitud = $_POST['solicitud'];
        $maximo = 1;
        while ($rs = mysql_fetch_array($query)) {
            $maximo = $rs['Maximo'] + 1;
        }

        $partida = "1";
        if (isset($_POST['partida'])) {
            $partida = $_POST['partida'];
        }

        if ($parametros['tipo'] == "0" || (isset($parametros['tipo' . $partida]) && $parametros['tipo' . $partida] == "0")) {
            $enviar_correo = true;
            if (isset($_POST['partida'])) {
                if (isset($parametros['localidad' . $_POST['partida']])) {
                    $localidad = "'" . $parametros['localidad' . $_POST['partida']] . "'";
                } else {
                    $localidad = "null";
                }

                $ubicacion = "";
                if (isset($parametros['ubicacion' . $_POST['partida']]) && $parametros['ubicacion' . $_POST['partida']] != "") {
                    $ubicacion = $parametros['ubicacion' . $_POST['partida']];
                }

                $retiro = "NULL";
                if (isset($parametros['retiro' . $_POST['partida']]) && $parametros['retiro' . $_POST['partida']] != "") {
                    $retiro = $parametros['retiro' . $_POST['partida']];
                    if ($retiro != "" && $retiro != "NULL" && !$reporte_historico->getRegistroById($retiro)) {
                        echo "Error: El retiro con folio $retiro no existe";
                        return false;
                    }
                }

                $query = $catalogo->obtenerLista("SELECT IdDetalleVD FROM k_solicitud WHERE id_solicitud='" . $_POST['solicitud'] . "' AND id_partida='" . $_POST['partida'] . "'");
                if ($rs = mysql_fetch_array($query)) {
                    $precio = 0;
                    if ($parametros['costo' . $_POST['partida']] == "none" || $parametros['costo' . $_POST['partida']] == "") {
                        $precio = $parametros['costotro' . $_POST['partida']];
                    } else {
                        $precio = $parametros['costo' . $_POST['partida']];
                    }
                    $query2 = $catalogo->obtenerLista("UPDATE k_solicitud SET cantidad='" . $parametros['numero' . $_POST['partida']] . "',
                      Modelo='" . $parametros['modelo' . $_POST['partida']] . "',tipo='" . $parametros['tipo' . $_POST['partida']] . "',IdAnexoClienteCC=$anexo,
                      IdServicio=" . $datos_servicio[0] . ",IdKServicio=" . $datos_servicio[1] . ",TipoInventario=" . $parametros['tipo_inventario' . $_POST['partida']] . ",
                      UsuarioUltimaModificacion='" . $_SESSION['user'] . "',FechaUltimaModificacion=NOW(),
                      Pantalla='PHP edicion_solicitud.php', ReporteRetiro=$retiro, Ubicacion='$ubicacion'
                      WHERE id_solicitud=" . $_POST['solicitud'] . " AND id_partida='" . $_POST['partida'] . "'");

                    $catalogo->obtenerLista("UPDATE k_ventadirectadet SET Cantidad='" . $parametros['numero' . $_POST['partida']] . "',IdProduto='" . $parametros['modelo' . $_POST['partida']] . "',Costo='$precio',UsuarioUltimaModificacion='" . $_SESSION['user'] . "',FechaUltimaModificacion=NOW(),Pantalla='PHP Nueva_Venta_Directa'
                    WHERE IdVentaDirectaDet='" . $rs['IdDetalleVD'] . "';");
                }
            } else {
                $precio = 0;
                if ($parametros['costo'] == "none" || $parametros['costo'] == "") {
                    $precio = $parametros['costotro'];
                } else {
                    $precio = $parametros['costo'];
                }

                $ubicacion = "";
                if (isset($parametros['ubicacion']) && $parametros['ubicacion'] != "") {
                    $ubicacion = $parametros['ubicacion'];
                }

                $retiro = "NULL";
                if (isset($parametros['retiro']) && $parametros['retiro'] != "") {
                    $retiro = $parametros['retiro'];
                    if ($retiro != "" && $retiro != "NULL" && !$reporte_historico->getRegistroById($retiro)) {
                        echo "Error: El retiro con folio $retiro no existe";
                        return false;
                    }
                }

                $idventadirecta = "";
                $query = $catalogo->obtenerLista("SELECT IdVentaDirecta FROM c_ventadirecta WHERE id_solicitud='" . $_POST['solicitud'] . "'");
                $id_ventadirectadet = "";
                if ($rs = mysql_fetch_array($query)) {

                    $id_ventadirectadet = $catalogo->insertarRegistro("INSERT INTO k_ventadirectadet(Cantidad,TipoProducto,IdProduto,Costo,IdVentaDirecta,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                        VALUES('" . $parametros['numero'] . "','" . $parametros['tipo'] . "','" . $parametros['modelo'] . "','" . $precio . "','" . $rs['IdVentaDirecta'] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Nueva_Venta_Directa')");

                    $query2 = $catalogo->obtenerLista("INSERT INTO k_solicitud(id_solicitud,id_partida,cantidad,Modelo,ClaveCentroCosto,tipo,IdAnexoClienteCC,
                      IdServicio,IdKServicio,TipoInventario,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,
                      Pantalla,IdDetalleVD,ReporteRetiro,Ubicacion)
                      VALUES('" . $id_solicitud . "','" . $maximo . "','" . $parametros['numero'] . "','" . $parametros['modelo'] . "',
                      '" . $parametros['localidad_vd'] . "','" . $parametros['tipo'] . "'," . $anexo . "," . $datos_servicio[0] . "," . $datos_servicio[1] . ",
                      " . $parametros['tipo_inventario'] . ",'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),
                      'PHP edicion_solicitud.php',$id_ventadirectadet,$retiro,'$ubicacion');");
                }
            }
        } else {
            $extra = "";
            if (isset($_POST['partida'])) {
                $extra = $_POST['partida'];
            }
            if (isset($parametros['serie_con_cliente' . $extra]) && $parametros['serie_con_cliente' . $extra] != "") {
                $serie = "'" . $parametros['serie_con_cliente' . $extra] . "'";
            } else {
                $serie = "null";
            }
            //inicializamos toner
            $toner = 0;
            //hacemos una consulta para ver las filas ya creadas
            $query = $catalogo->obtenerLista("SELECT
                k_solicitud.cantidad AS Cantidad,
                k_solicitud.tipo AS Tipo,
                k_solicitud.NoSerie AS NoSerie,
                k_solicitud.Modelo AS Modelo
                FROM c_solicitud
                INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
                INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
                WHERE c_solicitud.id_solicitud =" . $_POST['solicitud']);
            //inicializamos class
            $equipo = new EquipoCaracteristicasFormatoServicio();
            $componente = new Componente();
            //recorremos las filas
            while ($rs = mysql_fetch_array($query)) {
                //verificamos si es equipo
                if ($rs['Tipo'] == '0') {
                    //vemos si es color
                    if ($equipo->isColor($rs['Modelo'])) {
                        if ($parametro_obj->getRegistroById("11")) {
                            $toner_permitido = (int) $parametro_obj->getValor() * (int) $rs['Cantidad'];
                        } else {
                            $toner_permitido = 8 * (int) $rs['Cantidad'];
                        }
                        $toner+=$toner_permitido;
                    } else {
                        if ($parametro_obj->getRegistroById("10")) {
                            $toner_permitido = (int) $parametro_obj->getValor() * (int) $rs['Cantidad'];
                        } else {
                            $toner_permitido = 2 * (int) $rs['Cantidad'];
                        }
                        $toner+=$toner_permitido;
                    }
                } else {
                    //obtenemos los datos del componente
                    $componente->getRegistroById($rs['Modelo']);
                    //verificamos si es toner
                    if ($componente->getTipo() == '2') {
                        //restamos la cantidad de toner previo
                        $toner = $toner - $rs['Cantidad'];
                    }
                }
            }
            //buscamos los datos del nuevo componente
            $componente->getRegistroById($parametros['modelo' . $extra]);
            $boolean = true;
            //verificamos si es del tipo de toner
            if ($componente->getTipo() == '2') {
                //en caso de que traiga serie
                if ($serie != "null") {
                    //inicializamos
                    $consulta = "SELECT * FROM c_inventarioequipo WHERE NoSerie=$serie";
                    $query2 = $catalogo->obtenerLista($consulta);
                    //verifacamos que tenga registro
                    if ($rs = mysql_fetch_array($query2)) {
                        //vemos si es color
                        if ($equipo->isColor($rs['NoParteEquipo'])) {
                            if ($parametro_obj->getRegistroById("11")) {
                                $toner_permitido = (int) $parametro_obj->getValor() * (int) $rs['Cantidad'];
                            } else {
                                $toner_permitido = 8 * (int) $rs['Cantidad'];
                            }
                            $toner+=$toner_permitido;
                        } else {
                            if ($parametro_obj->getRegistroById("10")) {
                                $toner_permitido = (int) $parametro_obj->getValor() * (int) $rs['Cantidad'];
                            } else {
                                $toner_permitido = 2 * (int) $rs['Cantidad'];
                            }
                            $toner+=$toner_permitido;
                        }
                    }
                } else {
                    $toner = toner + 2;
                }

                if (($toner - $parametros['numero' . $extra]) <= 0) {//verificamos si toner es valido
                    $boolean = false;
                }
            }

            //if ($boolean) {
            $idventadirecta = "";
            $query = $catalogo->obtenerLista("SELECT IdVentaDirecta FROM c_ventadirecta WHERE id_solicitud='" . $_POST['solicitud'] . "'");
            $id_ventadirectadet = "";
            if (isset($_POST['partida'])) {
                $query = $catalogo->obtenerLista("SELECT IdDetalleVD FROM k_solicitud WHERE id_solicitud='" . $_POST['solicitud'] . "' AND id_partida='" . $_POST['partida'] . "'");

                $ubicacion = "";
                if (isset($parametros['ubicacion' . $_POST['partida']]) && $parametros['ubicacion' . $_POST['partida']] != "") {
                    $ubicacion = $parametros['ubicacion' . $_POST['partida']];
                }

                $retiro = "NULL";
                if (isset($parametros['retiro' . $_POST['partida']]) && $parametros['retiro' . $_POST['partida']] != "") {
                    $retiro = $parametros['retiro' . $_POST['partida']];
                    if ($retiro != "" && $retiro != "NULL" && !$reporte_historico->getRegistroById($retiro)) {
                        echo "Error: El retiro con folio $retiro no existe";
                        return false;
                    }
                }

                while ($rs = mysql_fetch_array($query)) {
                    $precio = 0;
                    if ($parametros['costo' . $_POST['partida']] == "none" || $parametros['costo' . $_POST['partida']] == "") {
                        $precio = $parametros['costotro' . $_POST['partida']];
                    } else {
                        $precio = $parametros['costo' . $_POST['partida']];
                    }
                    $consulta = "UPDATE k_solicitud SET cantidad='" . $parametros['numero' . $_POST['partida']] . "',cantidad_autorizada='" . $parametros['numero' . $_POST['partida']] . "',
                        Modelo='" . $parametros['modelo' . $_POST['partida']] . "',tipo='1',IdAnexoClienteCC=" . $anexo . ",
                        IdServicio=" . $datos_servicio[0] . ",IdKServicio=" . $datos_servicio[1] . ",TipoInventario=" . $parametros['tipo_inventario' . $_POST['partida']] . ",
                        NoSerie=$serie,UsuarioUltimaModificacion='" . $_SESSION['user'] . "',FechaUltimaModificacion=NOW(),
                        Pantalla='PHP edicion_solicitud.php',ReporteRetiro=$retiro,Ubicacion='$ubicacion'
                        WHERE id_solicitud=" . $_POST['solicitud'] . " AND id_partida='" . $_POST['partida'] . "'";

                    $query2 = $catalogo->obtenerLista($consulta);
                    $catalogo->obtenerLista("UPDATE k_ventadirectadet SET Cantidad='" . $parametros['numero' . $_POST['partida']] . "',IdProduto='" . $parametros['modelo' . $_POST['partida']] . "',Costo='$precio',UsuarioUltimaModificacion='" . $_SESSION['user'] . "',FechaUltimaModificacion=NOW(),Pantalla='PHP Nueva_Venta_Directa'
                    WHERE IdVentaDirectaDet='" . $rs['IdDetalleVD'] . "';");
                }
            } else {
                $ubicacion = "";
                if (isset($parametros['ubicacion']) && $parametros['ubicacion'] != "") {
                    $ubicacion = $parametros['ubicacion'];
                }

                $retiro = "NULL";
                if (isset($parametros['retiro']) && $parametros['retiro'] != "") {
                    $retiro = $parametros['retiro'];
                    if ($retiro != "" && $retiro != "NULL" && !$reporte_historico->getRegistroById($retiro)) {
                        echo "Error: El retiro con folio $retiro no existe";
                        return false;
                    }
                }
                if ($rs = mysql_fetch_array($query)) {
                    $precio = 0;
                    if ($parametros['costo'] == "none" || $parametros['costo'] == "") {
                        $precio = $parametros['costotro'];
                    } else {
                        $precio = $parametros['costo'];
                    }
                    $id_ventadirectadet = $catalogo->insertarRegistro("INSERT INTO k_ventadirectadet(Cantidad,TipoProducto,IdProduto,Costo,IdVentaDirecta,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                        VALUES('" . $parametros['numero'] . "','" . $parametros['tipo'] . "','" . $parametros['modelo'] . "','" . $precio . "','" . $rs['IdVentaDirecta'] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Nueva_Venta_Directa')");

                    $consulta = "INSERT INTO k_solicitud(id_solicitud,id_partida,cantidad,cantidad_autorizada,Modelo,ClaveCentroCosto,
                      tipo,IdAnexoClienteCC,IdServicio,IdKServicio,TipoInventario,NoSerie,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,
                      FechaUltimaModificacion,Pantalla,IdDetalleVD,ReporteRetiro,Ubicacion)
                      VALUES('" . $id_solicitud . "','" . $maximo . "','" . $parametros['numero'] . "','" . $parametros['numero'] . "',
                      '" . $parametros['modelo'] . "','" . $parametros['localidad_vd'] . "','1'," . $anexo . "," . $datos_servicio[0] . "," . $datos_servicio[1] . ",
                      " . $parametros['tipo_inventario'] . ",$serie,'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),
                      'PHP edicion_solicitud.php',$id_ventadirectadet,$retiro,'$ubicacion');";
                    $query2 = $catalogo->obtenerLista($consulta);
                    //echo $consulta;
                }
            }
            //}
        }
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
        k_solicitud.TipoInventario,
        k_solicitud.id_partida as id_partida,
        k_solicitud.IdDetalleVD AS IdDetalleVD,
        k_ventadirectadet.Costo AS Costo,
        vd.Clave_Localidad AS ClaveLocalidad
        FROM c_solicitud
        INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
        INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
        INNER JOIN k_ventadirectadet ON k_ventadirectadet.IdVentaDirectaDet =k_solicitud.IdDetalleVD
        INNER JOIN c_ventadirecta AS vd ON vd.IdVentaDirecta = k_ventadirectadet.IdVentaDirecta
        WHERE c_solicitud.id_solicitud =" . $_POST['solicitud'] . "
        ORDER BY k_solicitud.id_partida");
    ?>
    <table id="tsolformtabla">
        <?php
        $contador = 1;
        while ($rss = mysql_fetch_array($query)) {
            ?>
            <tr>
                <td>
                    <label for="numero<?php echo $rss['id_partida'] ?>">
                        Cantidad
                    </label>
                </td>
                <td>
                    <input type="text" id="numero<?php echo $rss['id_partida'] ?>" name="numero<?php echo $rss['id_partida'] ?>" value ="<?php echo $rss['Cantidad'] ?>" maxlength="5" style="width: 50px;" disabled="disabled"/>
                </td>
                <td>
                    <label for="tipo<?php echo $rss['id_partida'] ?>">
                        Tipo
                    </label>
                </td>
                <td>
                    <select id="tipo<?php echo $rss['id_partida'] ?>" name="tipo<?php echo $rss['id_partida'] ?>" 
                            onchange="cambiarselectmodelo('tipo<?php echo $rss['id_partida']; ?>', 'modelo<?php echo $rss['id_partida']; ?>');
                                            mostrarTipoInventario('tipo<?php echo $rss['id_partida']; ?>', 'tipo_inventario<?php echo $rss['id_partida'] ?>',
                                                    'div_serie_cliente<?php echo $rss['id_partida'] ?>');" style="width: 100px;" disabled="disabled">
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
                            $id_tipocompo = $rst['ID'];
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
                    <select id="modelo<?php echo $rss['id_partida']; ?>" name="modelo<?php echo $rss['id_partida']; ?>" class="size" style="width: 150px;" disabled="disabled">
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
                    <label for="costo<?php echo $rss['id_partida']; ?>">
                        Costo
                    </label>
                </td>
                <td>
                    <select id="costo<?php echo $rss['id_partida']; ?>" name="costo<?php echo $rss['id_partida']; ?>" class="size" onchange="calcularcostop('numero<?php echo $rss['id_partida']; ?>', 'costo<?php echo $rss['id_partida']; ?>', 'total<?php echo $rss['id_partida']; ?>', 'costotro<?php echo $rss['id_partida']; ?>', 'otrolabel<?php echo $rss['id_partida']; ?>', 'otroinput<?php echo $rss['id_partida']; ?>');" style="width: 120px;" disabled="disabled">
                        <?php
                        $precio = 0;
                        if ($rss['Tipo'] == 0) {
                            $query3 = $catalogo->obtenerLista("SELECT pabc.Precio_A,pabc.Precio_B,pabc.Precio_C  
                                FROM c_precios_abc AS pabc
                                LEFT JOIN c_equipo AS e ON e.NoParte = pabc.NoParteEquipo
                                WHERE e.NoParte = '".$rss['Modelo']."';");
                            if ($rsp = mysql_fetch_array($query3)) {
                                echo "<option value=\"\">Selecciona el precio</option>";
                                if ($rsp['Precio_A'] == $rss['Costo']) {
                                    echo "<option value=\"" . $rsp['Precio_A'] . "\" selected>Precio A: " . $rsp['Precio_A'] . "</option>";
                                    $precio = $rsp['Precio_A'];
                                } else {
                                    echo "<option value=\"" . $rsp['Precio_A'] . "\" >Precio A: " . $rsp['Precio_A'] . "</option>";
                                }
                                if ($rsp['Precio_B'] != "") {
                                    if ($rsp['Precio_B'] == $rss['Costo']) {
                                        echo "<option value=\"" . $rsp['Precio_B'] . "\" selected>Precio B: " . $rsp['Precio_B'] . "</option>";
                                        $precio = $rsp['Precio_B'];
                                    } else {
                                        echo "<option value=\"" . $rsp['Precio_B'] . "\" >Precio B: " . $rsp['Precio_B'] . "</option>";
                                    }
                                }
                                if ($rsp['Precio_C'] != "") {
                                    if ($rsp['Precio_B'] == $rss['Costo']) {
                                        echo "<option value=\"" . $rsp['Precio_C'] . "\" selected>Precio C: " . $rsp['Precio_C'] . "</option>";
                                        $precio = $rsp['Precio_C'];
                                    } else {
                                        echo "<option value=\"" . $rsp['Precio_C'] . "\" >Precio C: " . $rsp['Precio_C'] . "</option>";
                                    }
                                }
                                if ($precio == 0) {
                                    echo "<option value=\"none\" selected >Otro</option>";
                                } else {
                                    echo "<option value=\"none\" >Otro</option>";
                                }
                            } else {
                                echo "<option value=\"\">Selecciona otro</option>";
                                echo "<option value=\"none\" selected>Otro</option>";
                            }
                        } else {
                            $query3 = $catalogo->obtenerLista("SELECT pabc.Precio_A,pabc.Precio_B,pabc.Precio_C  
                                FROM c_precios_abc AS pabc
                                LEFT JOIN c_componente AS c ON c.NoParte = pabc.NoParteEquipo
                                WHERE c.NoParte = '".$rss['Modelo']."';");
                            if ($rsp = mysql_fetch_array($query3)) {
                                echo "<option value=\"\">Selecciona el precio</option>";
                                if ($rsp['Precio_A'] == $rss['Costo']) {
                                    echo "<option value=\"" . $rsp['Precio_A'] . "\" selected>Precio A: " . $rsp['Precio_A'] . "</option>";
                                    $precio = $rsp['Precio_A'];
                                } else {
                                    echo "<option value=\"" . $rsp['Precio_A'] . "\" >Precio A: " . $rsp['Precio_A'] . "</option>";
                                }
                                if ($rsp['Precio_B'] != "") {
                                    if ($rsp['Precio_B'] == $rss['Costo']) {
                                        echo "<option value=\"" . $rsp['Precio_B'] . "\" selected>Precio B: " . $rsp['Precio_B'] . "</option>";
                                        $precio = $rsp['Precio_B'];
                                    } else {
                                        echo "<option value=\"" . $rsp['Precio_B'] . "\" >Precio B: " . $rsp['Precio_B'] . "</option>";
                                    }
                                }
                                if ($rsp['Precio_C'] != "") {
                                    if ($rsp['Precio_B'] == $rss['Costo']) {
                                        echo "<option value=\"" . $rsp['Precio_C'] . "\" selected>Precio C: " . $rsp['Precio_C'] . "</option>";
                                        $precio = $rsp['Precio_C'];
                                    } else {
                                        echo "<option value=\"" . $rsp['Precio_C'] . "\" >Precio C: " . $rsp['Precio_C'] . "</option>";
                                    }
                                }
                                if ($precio == 0) {
                                    echo "<option value=\"none\" selected >Otro</option>";
                                } else {
                                    echo "<option value=\"none\" >Otro</option>";
                                }
                            } else {
                                echo "<option value=\"\">Selecciona otro</option>";
                                echo "<option value=\"none\" selected>Otro</option>";
                            }
                        }
                        ?>
                    </select>
                </td>
                <td id="otrolabel">
                    <label for="costotro<?php echo $rss['id_partida']; ?>">
                        Otro
                    </label>
                </td>
                <td id="otroinput<?php echo $rss['id_partida']; ?>">
                    <input type="text" style="width: 150px;" id="costotro<?php echo $rss['id_partida']; ?>" name="costotro<?php echo $rss['id_partida']; ?>" onkeyup="calcularcosto('numero<?php echo $rss['id_partida']; ?>', 'costotro<?php echo $rss['id_partida']; ?>', 'total<?php echo $rss['id_partida']; ?>');" <?php
                if ($precio == 0) {
                    echo "value=\"" . $rss['Costo'] . "\"";
                    $llamadas.="showcosto('" . $rss['id_partida'] . "');";
                } else {
                    echo "value=\"0\"";
                    $llamadas.="hidecosto('" . $rss['id_partida'] . "');";
                }
                        ?> disabled="disabled">
                </td>
                <td>
                    <label for="total<?php echo $rss['id_partida']; ?>">
                        Total
                    </label>
                </td>
                <td>
                    <input type="text" id="total<?php echo $rss['id_partida']; ?>" name="total<?php echo $rss['id_partida']; ?>" class="size" style="width: 50px;" readonly="readonly" value="<?php echo $rss['Costo'] * $rss['Cantidad'] ?>"/>
                </td>                
                <td>
                    <select id="tipo_inventario<?php echo $rss['id_partida']; ?>" name="tipo_inventario<?php echo $rss['id_partida']; ?>" style="display: none;">
                        <?php
                        $query2 = $catalogo->obtenerLista("SELECT idTipo AS ID, Nombre FROM `c_tipoinventario` WHERE idTipo IN(1,9) AND Activo = 1;");
                        while ($rsp = mysql_fetch_array($query2)) {
                            $s = "";
                            if ($rsp['ID'] == $rss['TipoInventario']) {
                                $s = "selected = 'selected'";
                            }
                            echo "<option value=\"" . $rsp['ID'] . "\" $s>" . $rsp['Nombre'] . "</option>";
                        }
                        ?>
                    </select>
                    <div id="div_serie_cliente<?php echo $rss['id_partida']; ?>" style="display: none;">
                        <label for="serie_con_cliente<?php echo $rss['id_partida']; ?>">Equipo en localidad</label>
                        <select id="serie_con_cliente<?php echo $rss['id_partida']; ?>" name="serie_con_cliente<?php echo $rss['id_partida']; ?>">
                            <option value="">Selecciona un equipo</option>
                        </select>
                    </div>
                    <input type="hidden" id="serie_asociada<?php echo $rss['id_partida']; ?>" name="serie_asociada<?php echo $rss['id_partida']; ?>" 
                           value="<?php echo $rss['NoSerie']; ?>"/>
                </td>
                <td>
                    <label for="ubicacion<?php echo $rss['id_partida']; ?>">Ubicación</label>
                </td>
                <td>
                    <input type="text" id="ubicacion<?php echo $rss['id_partida']; ?>" name="ubicacion<?php echo $rss['id_partida']; ?>" disabled="disabled"/>
                </td>
                <td>
                    <label for="retiro<?php echo $rss['id_partida']; ?>"># retiro</label>
                </td>
                <td>
                    <input type="number" id="retiro<?php echo $rss['id_partida']; ?>" name="retiro<?php echo $rss['id_partida']; ?>" maxlength="5" style="width: 50px;" disabled="disabled"  maxlength="6"/>
                </td>
                <td id="editarsolrow<?php echo $rss['id_partida']; ?>">
                    <a onclick="editarfilasol(<?php echo $rss['id_partida']; ?>);"><img src="resources/images/Modify.png" title="Editar Fila"/></a>
                </td>
                <td>
                    <a onclick="eliminarfilasol(<?php echo $rss['id_partida']; ?>);"><img src="resources/images/Erase.png" title="Editar Fila"/></a>
                </td>
            </tr>
            <?php
            $contador++;
        }
        if ($contador > 1) {
            echo "<script>setfilas(1)</script>";
        } else {
            echo "<script>setfilas(0)</script>";
        }
        echo "<script>setcontador($contador);$llamadas</script>"
        ?>
    </table>
    <br/>
    <?php
}
?>
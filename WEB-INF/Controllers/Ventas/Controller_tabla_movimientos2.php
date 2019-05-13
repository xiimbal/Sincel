<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/PermisosSubMenu.class.php");
include_once("../../Classes/Usuario.class.php");
include_once("../../Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes_arbol.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$catalogo = new Catalogo();
$id_noserie = "";
if (isset($_POST['id'])) {
    $id_noserie = $_POST['id'];
} else {
    $id_noserie = $_SESSION['idUsuario'];
}
$nseries;
$llamadas = ""; //script llamando funciones para que se valide el minimo que debe ingresar
if (isset($_POST['nserie'])) {
    $nseries = explode("&&", $_POST['nserie']);
}
$selects = Array("SELECT c_serviciofa.IdServicioFA AS ID,
				c_serviciofa.Nombre AS Nombre
 FROM c_serviciofa;", "SELECT c_serviciogfa.IdServicioGFA AS ID,
			c_serviciogfa.Nombre
 FROM c_serviciogfa;", "SELECT c_serviciogim.IdServicioGIM AS ID,
			c_serviciogim.Nombre AS Nombre
 FROM c_serviciogim;", "SELECT c_servicioim.IdServicioIM AS ID,
			c_servicioim.Nombre
 FROM c_servicioim;");
$servicio = "";
foreach ($selects as $select) {
    $query = $catalogo->obtenerLista($select);
    while ($rs = mysql_fetch_array($query)) {
        $servicio = $servicio . "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
    }
}
if ($permisos_grid->getAlta()) {
    ?>
    <script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/MovimientoEquipo2.js"></script>
    <script>
        $(document).ready(function() {
            $('.boton').button().css('margin-top', '20px');
        });
    </script>
    <style type="text/css">
        .tamanoinput {width: 95px;}
    </style>
    <form id="formmovimientos">
        <?php
        echo "Los equipos que moverás son los siguientes:<br/>";
        $cadena = "";
        foreach ($nseries as $value) {
            $cadena .=$value . ",";
        }
        echo substr($cadena, 0, strlen($cadena) - 1);
        echo "<br/>";
        $_SESSION['nseries'] = $nseries;
        ?>
        <br/>
        <table>
            <tr>
                <td><label for="tipomovimiento">Tipo de movimiento:</label></td>
                <td><select id="tipomovimiento" name="tipomovimiento"  width="140" style="width: 140px" ><option value="">Selecciona el movimiento</option><?php
                        $query = $catalogo->obtenerLista("SELECT c_tipomovimiento.Nombre AS Nombre,
		c_tipomovimiento.IdTipoMovimiento AS ID
 FROM c_tipomovimiento
ORDER BY Nombre");
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                        }
                        ?></select></td>
                <td>Fecha de movimiento</td>
                <td><input type="text" name="fecha_mov" id="fecha_mov"/></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php
            $permiso = new PermisosSubMenu();
            $cadena = "";
            foreach ($nseries as $value) {
                $cadena .="'" . $value . "',";
            }
            $cadena = substr($cadena, 0, strlen($cadena) - 1); //verificamos con el query si son del mismo cliente
            $qq = $catalogo->obtenerLista("SELECT
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCli, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCC, 
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
        COUNT(cinv.NoSerie) AS SUMA
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
WHERE cinv.NoSerie IN($cadena)
GROUP BY ClaveCli
ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, NoSerie DESC;");
            $resultados = mysql_num_rows($qq); //si son dos o mas resultados son de diferentes clientes
            $qq = $catalogo->obtenerLista("SELECT * FROM k_almacenequipo WHERE NoSerie IN($cadena)");
            $alm_equipo = FALSE;
            if (mysql_num_rows($qq) == count($nseries)) {
                $alm_equipo = TRUE;
            }
            if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 10) || $resultados == 1 || $alm_equipo) {
                $disabled = "";
                $usuario = new Usuario();
                if ($resultados == 1 && !$permiso->tienePermisoEspecial($_SESSION['idUsuario'], 10)) {
                    $disabled = "visibility:hidden;";
                }
                ?>
                <tr>
                    <td><input type="radio" id="movloc2" name="movloc" value="2" checked/></td>
                    <td>Mover a otro cliente</td>
                    <td><select id="selectvendedor2" name="selectvendedor2" width="140" style="<?php
                        if ($alm_equipo) {
                            echo "visibility:hidden;";
                        } else {
                            echo $disabled;
                        }
                        ?>width: 140px" onchange="cargarclientes('selectvendedor2', 'selectcli2');" >
                            <option value="">Selecciona vendedor</option>
                            <?php
                            $aux = "";
                            $vendedor = "";
                            if (isset($_POST['cliente'])) {
                                $query2 = $catalogo->obtenerLista("SELECT
            c_cliente.EjecutivoCuenta AS EjecutivoCuenta
            FROM
                    c_cliente
            WHERE
                    c_cliente.ClaveCliente='" . $_POST['cliente'] . "'");
                                if ($rs = mysql_fetch_array($query2)) {
                                    $aux = $rs['EjecutivoCuenta'];
                                    $vendedor = $aux;
                                }
                            }
                            $query = $catalogo->obtenerLista("SELECT c_usuario.IdUsuario,CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoPaterno,\" \",c_usuario.ApellidoMaterno) AS Nombre FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_puesto.IdPuesto=11 ORDER BY Nombre");
                            while ($rs = mysql_fetch_array($query)) {
                                if ($aux == $rs['IdUsuario']) {
                                    echo "<option value=\"" . $rs['IdUsuario'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                } else {
                                    echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                                }
                            }
                            ?>
                        </select></td>
                    <td><select id="selectcli2" name="selectcli2" width="140" style="<?php echo $disabled ?>width: 140px" onchange="cargarlocalidades('selectcli2', 'selectcliloc2');" >
                            <option value="">Selecciona Cliente</option>
                            <?php
                            if ($alm_equipo) {
                                $query = $catalogo->obtenerLista("SELECT c_cliente.NombreRazonSocial AS Nombre, c_cliente.ClaveCliente AS ID FROM c_cliente  WHERE IdTipoCliente=7 AND c_cliente.Activo=1 ORDER BY Nombre");
                            } else {
                                if ($disabled == "" && $usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)) {
                                    $query = $catalogo->obtenerLista("SELECT c_cliente.NombreRazonSocial AS Nombre, c_cliente.ClaveCliente AS ID
        FROM c_usuario INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
        WHERE c_usuario.IdUsuario = " . $id_noserie . " AND c_cliente.Activo=1 ORDER BY Nombre");
                                } else {
                                    if ($vendedor != "") {
                                        $query = $catalogo->obtenerLista("SELECT c_cliente.NombreRazonSocial AS Nombre, c_cliente.ClaveCliente AS ID FROM c_cliente WHERE EjecutivoCuenta='$vendedor' AND Activo=1 ORDER BY Nombre");
                                    } else {
                                        $query = $catalogo->obtenerLista("SELECT c_cliente.NombreRazonSocial AS Nombre, c_cliente.ClaveCliente AS ID FROM c_cliente ORDER BY Nombre");
                                    }
                                }
                            }
                            if (isset($_POST['cliente'])) {
                                while ($rs = mysql_fetch_array($query)) {
                                    if ($_POST['cliente'] == $rs['ID']) {
                                        echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                    } else {
                                        echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                                    }
                                }
                            } else {
                                while ($rs = mysql_fetch_array($query)) {
                                    echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                                }
                            }
                            ?>
                        </select></td>
                    <td><select id="selectcliloc2" name="selectcliloc2" width="140" style="width: 140px" onchange="cargaranexo('selectcliloc2', 'selectanexocli2');">
                            <option value="">Selecciona localidad</option><?php
                            if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
                                $query = $catalogo->obtenerLista("SELECT
            c_centrocosto.Nombre AS CentroCostoNombre,c_centrocosto.ClaveCentroCosto AS ID
            FROM
                    c_usuario
            INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
            INNER JOIN c_centrocosto ON c_centrocosto.ClaveCliente = c_cliente.ClaveCliente
            WHERE
                    c_cliente.ClaveCliente='" . $_POST['cliente'] . "'
            ORDER BY
                        CentroCostoNombre");
                            }
                            while ($rs = mysql_fetch_array($query)) {
                                echo "<option value=\"" . $rs['ID'] . "\">" . $rs['CentroCostoNombre'] . "</option>";
                            }
                            ?></select></td>
                    <td><select id="selectanexocli2" name="selectanexocli2" width="140" style="width: 140px" onchange="cargarServicios('selectanexocli2', 'selectlocserv2');">
                            <option value="">Selecciona Anexo</option>
                        </select></td>
                    <td><select id="selectlocserv2" name="selectcliserv2" width="140" style="width: 140px">
                            <option value="">Selecciona servicio</option>
                        </select></td>
                </tr>
            <?php }
            ?>
            <tr>
                <td><input type="radio" id="movloc3" name="movloc" value="3"/></td>
                <td>Mover a Almacén </td>
                <td><select id="selectalm" name="selectalm" width="140" style="width: 140px" disabled="disabled">
                        <option value="">Selecciona almacen</option>
                        <?php
                        $query = $catalogo->obtenerLista("SELECT
                        c_almacen.nombre_almacen AS Nombre,
                        c_almacen.id_almacen AS ID
                        FROM c_almacen WHERE Activo = 1 AND TipoAlmacen = 1 OR Surtir = 1;");
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                        }
                        ?>
                    </select></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>
        <label for="comentario"><h3>Comentario</h3></label><br/>
        <textarea id="comentario" name="comentario" cols="100" rows="4"></textarea>
        <br/>
        <br/>

        <?php
        $contador = 1;
        foreach ($nseries as $value) {
            $query = $catalogo->obtenerLista("SELECT NoSerie,c_equipo.NoParte AS NoParte,c_equipo.Modelo AS Modelo FROM c_bitacora
INNER JOIN c_equipo on c_equipo.NoParte=c_bitacora.NoParte
WHERE c_bitacora.NoSerie='" . $value . "'");
            $rs = mysql_fetch_array($query);
            echo "<h3>Equipo:" . $value . " Modelo:" . $rs['Modelo'] . "</h3><br/>";
            $query2 = $catalogo->obtenerLista("SELECT ts.Nombre AS servicio, ts.IdTipoServicio AS ID FROM `k_equipocaracteristicaformatoservicio` AS ke
INNER JOIN c_tiposervicio AS ts ON ke.NoParte = '" . $rs['NoParte'] . "' AND ts.IdTipoServicio = ke.IdTipoServicio;");
            $val = false;
            while ($rs = mysql_fetch_array($query2)) {
                if ($rs['ID'] == 1) {
                    $val = true;
                }
            }
            $query = $catalogo->obtenerLista("SELECT 
c_bitacora.NoSerie AS NoSerie,
DATE((SELECT CASE WHEN ISNULL(lt.Fecha) THEN l.Fecha WHEN ISNULL(l.Fecha) THEN lt.Fecha WHEN l.Fecha > lt.Fecha THEN DATE(l.Fecha) ELSE DATE(lt.Fecha) END)) AS Fecha,
(SELECT CASE WHEN ISNULL(lt.Fecha) THEN l.ContadorBNPaginas WHEN ISNULL(l.Fecha) THEN lt.ContadorBN WHEN l.Fecha > lt.Fecha THEN l.ContadorBNPaginas ELSE lt.ContadorBN END) AS ContadorBN,
(SELECT CASE WHEN ISNULL(lt.Fecha) THEN l.ContadorColorPaginas WHEN ISNULL(l.Fecha) THEN lt.ContadorCL WHEN l.Fecha > lt.Fecha THEN l.ContadorColorPaginas ELSE lt.ContadorCL END)AS ContadorCL,
(SELECT CASE WHEN ISNULL(lt.Fecha) THEN l.ContadorBNML WHEN ISNULL(l.Fecha) THEN lt.ContadorBNA WHEN l.Fecha > lt.Fecha THEN l.ContadorBNML ELSE lt.ContadorBNA END) AS ContadorBNML,
(SELECT CASE WHEN ISNULL(lt.Fecha) THEN l.ContadorColorML WHEN ISNULL(l.Fecha) THEN lt.ContadorCLA WHEN l.Fecha > lt.Fecha THEN l.ContadorColorML ELSE lt.ContadorCLA END) AS ContadorCLML,
(SELECT CASE WHEN ISNULL(lt.Fecha) THEN l.UsuarioCreacion WHEN ISNULL(l.Fecha) THEN lt.UsuarioCreacion WHEN l.Fecha > lt.Fecha THEN l.UsuarioCreacion ELSE lt.UsuarioCreacion END) AS Usuario,
(SELECT CASE WHEN ISNULL(lt.Fecha) THEN l.FechaCreacion WHEN ISNULL(l.Fecha) THEN lt.FechaCreacion WHEN l.Fecha > lt.Fecha THEN l.FechaCreacion ELSE lt.FechaCreacion END) AS FechaCreacion,
(SELECT CASE WHEN ISNULL(lt.Fecha) THEN l.UsuarioUltimaModificacion WHEN ISNULL(l.Fecha) THEN lt.UsuarioUltimaModificacion WHEN l.Fecha > lt.Fecha THEN l.UsuarioUltimaModificacion ELSE lt.UsuarioUltimaModificacion END) AS UsuarioUltimaModificacion,
(SELECT CASE WHEN ISNULL(lt.Fecha) THEN l.NivelTonNegro WHEN ISNULL(l.Fecha) THEN lt.NivelTonNegro WHEN l.Fecha > lt.Fecha THEN l.NivelTonNegro ELSE lt.NivelTonNegro END) AS NivelTonNegro,
(SELECT CASE WHEN ISNULL(lt.Fecha) THEN l.NivelTonCian WHEN ISNULL(l.Fecha) THEN lt.NivelTonCian WHEN l.Fecha > lt.Fecha THEN l.NivelTonCian ELSE lt.NivelTonCian END) AS NivelTonCian,
(SELECT CASE WHEN ISNULL(lt.Fecha) THEN l.NivelTonMagenta WHEN ISNULL(l.Fecha) THEN lt.NivelTonMagenta WHEN l.Fecha > lt.Fecha THEN l.NivelTonMagenta ELSE lt.NivelTonMagenta END) AS NivelTonMagenta,
(SELECT CASE WHEN ISNULL(lt.Fecha) THEN l.NivelTonAmarillo WHEN ISNULL(l.Fecha) THEN lt.NivelTonAmarillo WHEN l.Fecha > lt.Fecha THEN l.NivelTonAmarillo ELSE lt.NivelTonAmarillo END) AS NivelTonAmarillo
FROM
c_bitacora
LEFT JOIN c_lectura AS l ON l.NoSerie = c_bitacora.NoSerie AND l.Fecha = (SELECT MAX(Fecha) FROM c_lectura WHERE c_lectura.NoSerie = c_bitacora.NoSerie)
LEFT JOIN c_lecturasticket AS lt ON lt.ClvEsp_Equipo = c_bitacora.NoSerie AND lt.Fecha = (SELECT MAX(Fecha) FROM c_lecturasticket WHERE c_lecturasticket.ClvEsp_Equipo = c_bitacora.NoSerie)
WHERE c_bitacora.NoSerie='" . $value . "'
ORDER BY Fecha DESC");
            $impresora = true;
            $query2 = $catalogo->obtenerLista("SELECT c_caracteristicaequipo.IdCaracteristicaEquipo AS ID FROM c_inventarioequipo
INNER JOIN k_equipocaracteristicaformatoservicio ON k_equipocaracteristicaformatoservicio.NoParte=c_inventarioequipo.NoParteEquipo
INNER JOIN c_caracteristicaequipo ON c_caracteristicaequipo.IdCaracteristicaEquipo=k_equipocaracteristicaformatoservicio.IdCaracteristicaEquipo
WHERE c_inventarioequipo.NoSerie='" . $value . "';");
            while ($rs = mysql_fetch_array($query2)) {
                if ($rs['ID'] == 2) {
                    $impresora = false;
                }
            }
            if ($rs = mysql_fetch_array($query)) {
                ?>
                <table>
                    <tr>
                        <?php if ($val == 1) { ?>
                            <td>Contador B/N</td>
                            <td>Contador Color</td>
                            <?php if (!$impresora) { ?>
                                <td>Contador B/N ML</td>
                                <td>Contador Color ML</td>
                            <?php } ?>
                            <td>Nivel Toner Negro</td>
                            <td>Nivel Toner Cyan</td>
                            <td>Nivel Toner Magenta</td>
                            <td>Nivel Toner Amarillo</td>
                        <?php } else { ?>
                            <td>Contador B/N</td>
                            <?php if (!$impresora) { ?>
                                <td>Contador B/N ML</td>
                            <?php } ?>
                            <td>Nivel Toner Negro</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if ($val == 1) { ?>
                            <td>
                                <?php echo $rs['ContadorBN'] ?>
                            </td>
                            <td>
                                <?php echo $rs['ContadorCL'] ?>
                            </td>
                            <?php if (!$impresora) { ?>
                                <td>
                                    <?php echo $rs['ContadorBNML'] ?>
                                </td>
                                <td>
                                    <?php echo $rs['ContadorCLML'] ?>
                                </td>
                            <?php } ?>
                            <td>       
                                <?php echo $rs['NivelTonNegro'] ?>
                            </td>
                            <td>
                                <?php echo $rs['NivelTonCian'] ?>
                            </td>
                            <td>
                                <?php echo $rs['NivelTonMagenta'] ?>
                            </td>
                            <td>
                                <?php echo $rs['NivelTonAmarillo'] ?>
                            </td>
                        <?php } else { ?>
                            <td>
                                <?php echo $rs['ContadorBN'] ?>
                            </td>
                            <?php if (!$impresora) { ?>
                                <td>
                                    <?php echo $rs['ContadorBNML'] ?>
                                </td>
                            <?php } ?>
                            <td>       
                                <?php echo $rs['NivelTonNegro'] ?>
                            </td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if ($val == 1) { ?>
                        <input type="hidden" id="fecha<?php echo $contador ?>" name="fecha<?php echo $contador ?>" class="tamanoinput fecha"/>
                        <td>
                            <input type="text" id="contadorbn<?php echo $contador ?>" name="contadorbn<?php
                            echo $contador;
                            $llamadas.="contadorbn('contadorbn" . $contador . "','" . $rs['ContadorBN'] . "');";
                            ?>"  class="tamanoinput"/>
                        </td>
                        <td>
                            <input type="text" id="contadorcl<?php echo $contador ?>" name="contadorcl<?php
                            echo $contador;
                            $llamadas.="contadorcl('contadorcl" . $contador . "','" . $rs['ContadorCL'] . "');";
                            ?>" class="tamanoinput"/>
                        </td>
                        <?php if (!$impresora) { ?>
                            <td>
                                <input type="text" id="contadorbnml<?php echo $contador ?>" name="contadorbnml<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                            <td>
                                <input type="text" id="contadorclml<?php echo $contador ?>" name="contadorclml<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                        <?php } ?>
                        <td>
                            <input type="text" id="NivelTN<?php echo $contador ?>" name="NivelTN<?php echo $contador ?>" class="tamanoinput"/>
                        </td>
                        <td>
                            <input type="text" id="NivelTC<?php echo $contador ?>" name="NivelTC<?php echo $contador ?>" class="tamanoinput"/>
                        </td>
                        <td>
                            <input type="text" id="NivelTM<?php echo $contador ?>" name="NivelTM<?php echo $contador ?>" class="tamanoinput"/>
                        </td>
                        <td>
                            <input type="text" id="NivelTA<?php echo $contador ?>" name="NivelTA<?php echo $contador ?>" class="tamanoinput"/>
                        </td>
                    <?php } else { ?>

                        <input type="hidden" id="fecha<?php echo $contador ?>" name="fecha<?php echo $contador ?>" class="tamanoinput fecha"/>

                        <td>
                            <input type="text" id="contadorbn<?php echo $contador ?>" name="contadorbn<?php
                            echo $contador;
                            $llamadas.="contadorbn('contadorbn" . $contador . "','" . $rs['ContadorBN'] . "');";
                            ?>"  class="tamanoinput"/>
                        </td>
                        <?php if (!$impresora) { ?>
                            <td>
                                <input type="text" id="contadorbnml<?php echo $contador ?>" name="contadorbnml<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                        <?php } ?>
                        <td>
                            <input type="text" id="NivelTN<?php echo $contador ?>" name="NivelTN<?php echo $contador ?>" class="tamanoinput"/>
                        </td>
                    <?php } ?>

                    </tr>
                </table>
                <?php
            } else {
                ?>
                <table>
                    <tr>
                        <?php if ($val == 1) { ?>
                            <td>Contador B/N</td>
                            <td>Contador Color</td>
                            <?php if (!$impresora) { ?>
                                <td>Contador B/N ML</td>
                                <td>Contador Color ML</td>
                            <?php } ?>
                            <td>Nivel Toner Negro</td>
                            <td>Nivel Toner Cyan</td>
                            <td>Nivel Toner Magenta</td>
                            <td>Nivel Toner Amarillo</td>
                        <?php } else { ?>
                            <td>Contador B/N</td>
                            <?php if (!$impresora) { ?>
                                <td>Contador B/N ML</td>
                            <?php } ?>
                            <td>Nivel Toner Negro</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if ($val == 1) { ?>

                        <input type="hidden" id="fecha<?php echo $contador ?>" name="fecha<?php echo $contador ?>" class="tamanoinput fecha"/>

                        <td>
                            <input type="text" id="contadorbn<?php echo $contador ?>" name="contadorbn<?php echo $contador ?>"  class="tamanoinput"/>
                        </td>
                        <td>
                            <input type="text" id="contadorcl<?php echo $contador ?>" name="contadorcl<?php echo $contador ?>" class="tamanoinput"/>
                        </td>
                        <?php if (!$impresora) { ?>
                            <td>
                                <input type="text" id="contadorbnml<?php echo $contador ?>" name="contadorbnml<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                            <td>
                                <input type="text" id="contadorclml<?php echo $contador ?>" name="contadorclml<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                        <?php } ?>
                        <td>
                            <input type="text" id="NivelTN<?php echo $contador ?>" name="NivelTN<?php echo $contador ?>" class="tamanoinput"/>
                        </td>
                        <td>
                            <input type="text" id="NivelTC<?php echo $contador ?>" name="NivelTC<?php echo $contador ?>" class="tamanoinput"/>
                        </td>
                        <td>
                            <input type="text" id="NivelTM<?php echo $contador ?>" name="NivelTM<?php echo $contador ?>" class="tamanoinput"/>
                        </td>
                        <td>
                            <input type="text" id="NivelTA<?php echo $contador ?>" name="NivelTA<?php echo $contador ?>" class="tamanoinput"/>
                        </td>
                    <?php } else { ?>

                        <input type="hidden" id="fecha<?php echo $contador ?>" name="fecha<?php echo $contador ?>" class="tamanoinput fecha"/>

                        <td>
                            <input type="text" id="contadorbn<?php echo $contador ?>" name="contadorbn<?php echo $contador ?>"  class="tamanoinput"/>
                        </td>
                        <?php if (!$impresora) { ?>
                            <td>
                                <input type="text" id="contadorbnml<?php echo $contador ?>" name="contadorbnml<?php echo $contador ?>" class="tamanoinput"/>
                            </td>
                        <?php } ?>
                        <td>
                            <input type="text" id="NivelTN<?php echo $contador ?>" name="NivelTN<?php echo $contador ?>" class="tamanoinput"/>
                        </td>
                    <?php } ?>
                    </tr>
                </table>
                <?php
            }
            $contador++;
            echo "<br/>";
        }
        ?>
        <input type="submit" id="aceptar" class="boton" value="Aceptar"/>
    </form>

    <script type="text/javascript" language="javascript">
        validarextra(<?php echo $contador ?>);
    <?php echo $llamadas; ?>
        //cargarlocalidades('selectcli2', 'selectcliloc2');
    </script>
    <?php
}?>
<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
if (isset($_POST['id'])) {
    $id_solicitud = $_POST['id'];
    include_once("../WEB-INF/Classes/Catalogo.class.php");
    include_once("../WEB-INF/Classes/Usuario.class.php");
    $catalogo = new Catalogo();
    $query = $catalogo->obtenerLista("SELECT
	c_solicitud.fecha_solicitud AS Fecha,
	c_cliente.ClaveCliente AS ClaveCliente,
	c_cliente.NombreRazonSocial AS Cliente,
	c_solicitud.id_solicitud AS ID,
        c_solicitud.comentario AS comentario,
	k_solicitud.cantidad AS Cantidad,
	k_solicitud.tipo AS Tipo,
        k_solicitud.Modelo AS Modelo,
        k_solicitud.ClaveCentroCosto AS Localidad,
        k_solicitud.cantidad_autorizada AS Cantidad_Autorizada,
        k_solicitud.id_partida AS id_partida
        FROM c_solicitud
        INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
        INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
        WHERE c_solicitud.id_solicitud =" . $id_solicitud . "
        ORDER BY k_solicitud.id_partida");
    $rss = mysql_fetch_array($query);
    $usuario = new Usuario();
    $idUsuario = $_SESSION['idUsuario'];
    if(isset($rss['comentario'])){
        $comentario = $rss['comentario'];
    }
    ?>
    <style>
        .size{width: 200px;}
    </style>
    <script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/autorizar_sol_equipo.js"></script>
    <!--link responsivo-->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/fontawesome/css/all.min.css" rel="stylesheet">
<br/><br/>
   
        <form id="solform">
          <div class="container-fluid">

            <div class="form-row">
                <div class="form-group col-12">Fecha&nbsp;: <?php echo $rss['Fecha']; ?></div>
                <div class="form-group  col-md-6 col-lg-4">
           <!--Clientes-->
                    <label for="cliente">Cliente</label>
                    <select class="form-control" id="cliente" name="cliente" onchange="cambiarccosto('cliente', 'localidad');" width="600"   class="size" disabled="disabled" required>
                        <option value="">Selecciona el cliente</option>
                        <?php
                            $query2 = $catalogo->obtenerLista("SELECT c_usuario.IdPuesto AS Puesto FROM c_usuario WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario']);
                            $rs = mysql_fetch_array($query2);

                            $query2 = ($rs['Puesto'] == 6 
                                ? $catalogo->obtenerLista("SELECT c_cliente.NombreRazonSocial AS Nombre, c_cliente.ClaveCliente AS ID FROM c_cliente INNER JOIN c_usuario ON c_usuario.IdUsuario=c_cliente.EjecutivoCuenta WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " ORDER BY Nombre ASC")
                                : $catalogo->obtenerLista("SELECT c_cliente.NombreRazonSocial AS Nombre, c_cliente.ClaveCliente AS ID FROM c_cliente ORDER BY Nombre ASC"));
                            
                            while ($rs = mysql_fetch_array($query2)) {
                                echo ( $rs['ID'] != $rss['ClaveCliente'] 
                                    ? "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>" 
                                    : "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>");
                            }
                        ?>
                    </select>
                </div>

            <!--Cantidad-->
                <div class="form-group  col-md-6 col-lg-4">
                    <label for="numero1">Cantidad</label>
                    <input class="form-control" type="hidden" id="partida1" name="partida1" value="<?php echo $rss['id_partida']; ?>"/>
                    <input class="form-control" type="text" id="numero1" name="numero1" value ="<?php echo $rss['Cantidad']; ?>" disabled="disabled" size="3"/>
                </div>

            <!--Tipo-->
                <div class="form-group  col-md-6 col-lg-4">
                    <label for="tipo1">Tipo</label>
                    <select class="form-control" id="tipo1" name="tipo1" onchange="cambiarselectmodelo('tipo1', 'modelo1');" class="size" disabled="disabled" required>
                        <option value="">Selecciona el tipo</option>
                        <option value="0" <?php if ($rss['Tipo'] == 0) echo "selected"; ?>>Equipo</option>
                        <option value="1" <?php if ($rss['Tipo'] == 1) echo "selected"; ?>>Componente</option>
                    </select>
                </div>

            <!--Modelo-->
                <div class="form-group  col-md-6 col-lg-4">
                    <label for="modelo1">Modelo</label>
                    <select class="form-control" id="modelo1" name="modelo1" disabled="disabled" class="size" >
                        <option value="">Selecciona el modelo</option>
                        <?php
                        if ($rss['Tipo'] == 0) {
                            $query3 = $catalogo->obtenerLista("SELECT DISTINCT c_equipo.Modelo AS Modelo, c_equipo.NoParte AS Parte FROM c_equipo ORDER BY Modelo");
                            while ($rsp = mysql_fetch_array($query3)) {
                                echo ( $rsp['Parte'] == $rss['Modelo'] 
                                    ? "<option value=\"" . $rsp['Parte'] . "\" selected>" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>" 
                                    : "<option value=\"" . $rsp['Parte'] . "\" >" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>");
                            }
                        } else {
                            $query3 = $catalogo->obtenerLista("SELECT DISTINCT c_componente.Modelo AS Modelo, c_componente.NoParte AS Parte FROM c_componente ORDER BY Modelo");
                            while ($rsp = mysql_fetch_array($query3)) {
                                echo ( $rsp['Parte'] == $rss['Modelo'] 
                                    ? "<option value=\"" . $rsp['Parte'] . "\" selected>" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>" 
                                    : "<option value=\"" . $rsp['Parte'] . "\" >" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>" );
                            }
                        }
                        ?>
                    </select>
                </div>

            <!--Localidad-->
                <div class="form-group  col-md-6 col-lg-4">
                    <label for="localidad1">Localidad</label>
                    <select id="localidad1" name="localidad1" class="form-control" disabled="disabled" >
                        <?php
                            $query2 = $catalogo->obtenerLista("SELECT c_centrocosto.ClaveCentroCosto AS ID, c_centrocosto.Nombre AS Nombre FROM c_cliente INNER JOIN c_centrocosto ON c_centrocosto.ClaveCliente=c_cliente.ClaveCliente WHERE c_cliente.ClaveCliente='" . $rss['ClaveCliente'] . "'");
                            while ($rs = mysql_fetch_array($query2)) {
                                echo ( $rs['ID'] == $rss['Localidad'] 
                                    ? "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>" 
                                    : "<option value=\"" . $rs['ID'] . "\" >" . $rs['Nombre'] . "</option>" );
                            }
                        ?>
                    </select>
                </div>

            <!--Cantidad aceptada-->
                <div class="form-group  col-md-6 col-lg-4">
                    <label for="cantidadA1">Cantidad aceptada</label>
                    <input type="text" id="cantidadA1" name="cantidadA1" size="3" class="form-control"
                        value ="<?php echo ( $rss['Cantidad_Autorizada'] != null ? $rss['Cantidad_Autorizada'] : $rss['Cantidad'] ); ?>"  
                        <?php
                            if ($usuario->isUsuarioPuesto($idUsuario, 24) && $rss['Tipo'] == 0) {
                                $queryauxiliar = $catalogo->obtenerLista("SELECT * FROM c_componente WHERE NoParte='" . $rsp['Parte'] . "'");
                                if ($rspp = mysql_fetch_array($queryauxiliar)) {
                                    if ($rspp['IdTipoComponente'] == 2) {
                                        echo " readonly='readonly' ";
                                    }
                                }
                            }
                        ?> required/>
                </div>

                <?php
                    $contador = 2;
                    while ($rss = mysql_fetch_array($query)) {
                        ?>
                        <div>

                        <!--Cantidad-->
                            <div class="form-group  col-md-4">
                                <label  for="numero<?php echo $contador ?>">Cantidad</label>
                                <input class="form-control" type="hidden" id="partida<?php echo $contador ?>" name="partida<?php echo $contador ?>" value="<?php echo $rss['id_partida']; ?>"/>
                                <input type="text" id="numero<?php echo $contador ?>" name="numero<?php echo $contador ?>" value ="<?php echo $rss['Cantidad'] ?>" disabled="disabled" size="3"/>
                            </div>

                        <!--Tipo-->
                            <div class="form-group  col-md-4">
                                <label  for="tipo<?php echo $contador ?>">Tipo</label>
                                <select class="form-control" id="tipo<?php echo $contador ?>" name="tipo<?php echo $contador ?>" class="size" onchange="cambiarselectmodelo('tipo<?php echo $contador ?>', 'modelo<?php echo $contador ?>');" disabled="disabled"required>
                                    <option value="">Selecciona el tipo</option>
                                    <option value="0" <?php
                                    if ($rss['Tipo'] == 0) {
                                        echo "selected";
                                    }
                                    ?>>Equipo</option>
                                    <option value="1" <?php
                                    if ($rss['Tipo'] == 1) {
                                        echo "selected";
                                    }
                                    ?>>Componente</option>
                                </select>
                            </div>
                            
                                
                            
                        <!--Modelo-->
                            <div class="form-group col-md-4">
                                <label for="modelo<?php echo $contador ?>">Modelo</label>
                                <select id="modelo<?php echo $contador ?>" name="modelo<?php echo $contador ?>" class="size" disabled="disabled" required>
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
                            </div>
                            <td>
                                <select id="localidad<?php echo $contador ?>" name="localidad<?php echo $contador ?>" class="size" disabled="disabled" required>
                                    <?php
                                    $query4 = $catalogo->obtenerLista("SELECT c_centrocosto.ClaveCentroCosto AS ID, c_centrocosto.Nombre AS Nombre FROM c_cliente INNER JOIN c_centrocosto ON c_centrocosto.ClaveCliente=c_cliente.ClaveCliente WHERE c_cliente.ClaveCliente='" . $rss['ClaveCliente'] . "'");
                                    while ($rs = mysql_fetch_array($query4)) {
                                        if ($rs['ID'] == $rss['Localidad']) {
                                            echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                        } else {
                                            echo "<option value=\"" . $rs['ID'] . "\" >" . $rs['Nombre'] . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                            
                        <!--Cantidad aceptada-->
                            <div class="form-group col-md-4">
                                <label for="cantidadA<?php echo $contador ?>/">Cantidad aceptada</label>
                                <input type="text" id="cantidadA<?php echo $contador ?>" name="cantidadA<?php echo $contador ?>" size="3" value ="<?php
                                if ($rss['Cantidad_Autorizada'] != null) {
                                    echo $rss['Cantidad_Autorizada'];
                                } else {
                                    echo $rss['Cantidad'];
                                }
                                ?>"  <?php
                                    if ($usuario->isUsuarioPuesto($idUsuario, 24) && $rss['Tipo'] == 0) {
                                        $queryauxiliar = $catalogo->obtenerLista("SELECT * FROM c_componente WHERE NoParte='" . $rsp['Parte'] . "'");
                                        if ($rspp = mysql_fetch_array($queryauxiliar)) {
                                            if ($rspp['IdTipoComponente'] == 2) {
                                                echo " readonly='readonly' ";
                                            }
                                        }
                                    }
                                    ?> required/>
                            </div>
                        </div>
                        <?php
                        $contador++;
                    }
                ?>

            <!--Comentario-->    
                <div class="form-group col-md-6">
                    <label for="comentarios">Comentario</label>
                    <textarea id="comentarios" name="comentarios" class="form-control rounded-0"><?php echo $comentario; ?></textarea><br>
                </div>

            <!--Boton de seleccion-->    
                <div class="form-group col-12">
                    <label for="autorizar">Autorizar</label>
                    <input type="radio" id="autorizar1" name="autorizar" value="1" checked="checked"/>
                    <label for="autorizar">Rechazar</label>
                    <input type="radio" id="autorizar2" name="autorizar" value="3"/>
                    <label for="autorizar">Cancelada</label>
                    <input type="radio" id="autorizar3" name="autorizar" value="4"/>
                </div>

            <!--Botones-->
               
                    <input type="submit" id="aceptar" class="button btn btn-lang btn-block btn-outline-success mt-3 mb-3" name="aceptar" value="Aceptar"/>
                    <input type="button" id="cancelar" class="button btn btn-lang btn-block btn-outline-danger mt-3 mb-3" name="cancelar" value="Cancelar" onclick="cambiarContenidos('ventas/Autorizaciones_Solicitud.php', 'Autorizaciones');"/>
                    <input type="hidden" id="solicitud" name="solicitud" value="<?php echo $id_solicitud ?>"/>
                
            </div> 
      </div>
   </form>
 <script>
        $(document).ready(function() {
            $('.boton').button().css('margin-top', '20px');
        });
        <?php echo "establecercontador(" . $contador . ");"; ?>
    </script>
<?php } ?>


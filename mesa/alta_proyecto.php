<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Ticket.class.php");

$catalogo = new Catalogo();
$permisos_grid = new PermisosSubMenu();
$same_page = "mesa/alta_proyecto.php";
$page_alta = "mesa/lista_proyectos.php";

$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $page_alta);

$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();
$nombre_nota = $permisos_grid2->getTitulo(7); //Nombre de las notas en la empresa actual
$nombre_areaAtencion = $permisos_grid2->getTitulo(8);
$nombre_UsuarioOrigen = $permisos_grid2->getTitulo(9);
$ticket = new Ticket();
$monitoreo = "mesa/monitoreo_actividades.php";

if (isset($_GET['regresar']) && $_GET['regresar']!="") {
    $pagina_listaRegresar = $_GET['regresar'];
} else {
    $pagina_listaRegresar = "mesa/lista_proyectos.php";
}
$idTicket = "";
$nombre = "";
$fechaInicio = "";
$fechaFinPrevisto = "";
$subtipo = "";
$tecnico = "";
$areaAtencion = "";
$ClaveCliente = "";
$presupuestoInicial = "";
$progreso = "";
$descripcion = "";
$observacion = "";
$contacto = "";
$display = "display:none";
$ckContacto = "";
$usuarioOrigen = "";
//Estos no se modifican... por ahora
$estadoTicket = 3;
$params ="";
$lista_actividades = "mesa/lista_actividades.php";
//Para poder editar
if(isset($_POST['idTicket']) && $_POST['idTicket'] != ""){
    $idTicket = $_POST['idTicket'];
    $params = "?id=$idTicket";
    $ticket->getRegistroProyecto($idTicket);
    $nombre = $ticket->getNombre();
    $fechaInicio = $ticket->getFechaHora();
    $fechaFinPrevisto = $ticket->getFechaFinPrevisto();
    $subtipo = $ticket->getIdSubtipo();
    $fechaFinReal = $ticket->getFechaFinReal();
    $tecnico = $ticket->getTecnico();
    $areaAtencion = $ticket->getAreaAtencion();
    $ClaveCliente = $ticket->getClaveCliente();
    $presupuestoInicial = $ticket->getPresupuesto();
    $progreso = $ticket->getProgreso();
    $descripcion = $ticket->getDescripcionReporte();
    $observacion = $ticket->getObservacionAdicional();
    $contacto = $ticket->getContacto();
    $usuarioOrigen = $ticket->getUsuarioOrigen();
    if($contacto != ""){
        $display = "";
        $ckContacto = "checked";
    }
}

?>
<html>
    <head>
        <script type="text/javascript" src="resources/js/paginas/alta_proyecto.js"></script>
        <link href="resources/css/buttons.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div id="mySidenav" class="sidenav">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
            <a href="#" onclick="cambiarContenidos('<?php echo $page_alta; ?>', '<?php echo $nombre_objeto; ?>'); return false;">Lista de <?php echo $nombre_objeto; ?></a>
            <a href="#" onclick="cambiarContenidos('<?php echo $lista_actividades.$params; ?>','<?php echo $nombre_nota; ?>'); return false;">Lista de <?php echo $nombre_nota; ?></a>            
            <a href="#" onclick="monitorActividades('<?php echo $monitoreo; ?>'); return false;">Monitor de <?php echo $nombre_nota; ?></a>    
        </div>
        <div id="main_panel" >            
            <span style="font-size:30px;cursor:pointer" onclick="openNav()" id="open">&#9776;</span>
            <form id="frmAltaTicket" name="frmAltaTicket" action="/" method="POST">

                <div class="tabs">
                        <label><a href="#tabs-1">Información del <?php echo $nombre_objeto; ?></a></label>
                    <div id = "tabs-1" style = "background-color: #FFFFFF">
                        <table style="width:  100%">
                         <div class="form-row">
                          <div class="form-group col-md-4">
                                <label>Nombre del <?php echo $nombre_objeto; ?><span class="obligatorio"> *</span></label>
                                <input class="form-control" type="text" id="nombre" name="nombre" value="<?php echo $nombre?>"/>
                            </div> 
                          <div class="form-group col-md-4">
                                <label>Fecha inicio</label>
                                <input class="form-control" type="text" id="fecha" name="fecha" class="fecha" value="<?php echo $fechaInicio?>"/>
                             </div> 
                           <div class="form-group col-md-4">
                                <label>Tipo de <?php echo $nombre_objeto; ?></label>
                                    <select class="form-control" id="tipo" name="tipo" class="select" >
                                        <?php
                                        echo "<option value=''>Seleccione el tipo</option>";                                       
                                        $result = $catalogo->getListaAlta("c_subtipoticket", "Subtipo");
                                        while ($rs = mysql_fetch_array($result)) {
                                            $s = "";
                                            if ($subtipo != "" && $subtipo == $rs['IdSubtipo']) {
                                                $s = "selected";
                                            }
                                            echo "<option value='" . $rs['IdSubtipo'] . "' $s>" . $rs['Subtipo'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            <div class="form-group col-md-4">
                                <label>Fecha fin previsto</label>
                                <input class="form-control" type="text" id="fecha_fp" name="fecha_fp" class="fechaFin" value="<?php echo $fechaFinPrevisto?>"/>
                                </div>
                            <div class="form-group col-md-4">
                                <label>Fecha fin real</label>
                                <input class="form-control" type="text" id="fecha_fr" name="fecha_fr" class="fechaFin" value="<?php echo $fechaFinReal?>"/>
                                </div>
                            <div class="form-group col-md-4">
                                <label><?php echo $nombre_puesto; ?> asignado <span class="obligatorio"> *</span></label>
                                    <select class="form-control" id="tecnico" name="tecnico" class="select" >
                                        <?php
                                        echo "<option value=''>Seleccione el $nombre_puesto</option>";
                                        $consulta = "SELECT u.IdUsuario, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Usuario
                                                    FROM c_usuario AS u
                                                    LEFT JOIN permisos_especiales_puesto AS pep ON pep.IdPuesto = u.IdPuesto
                                                    WHERE pep.IdPermisoEspecial = 38 AND u.Activo = 1 ORDER BY Usuario;";
                                        $result = $catalogo->obtenerLista($consulta);
                                        while ($rs = mysql_fetch_array($result)) {
                                            $s = "";
                                            if ($tecnico != "" && $tecnico == $rs['IdUsuario']) {
                                                $s = "selected";
                                            }
                                            echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['Usuario'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            <div class="form-group col-md-4">    
                                <label><?php echo $nombre_areaAtencion; ?><span class="obligatorio"> *</span></label>
                                <select class="form-control" id="areaAtencionGral" name="areaAtencionGral" class="select" >
                                        <?php
                                        echo "<option value=''>Seleccione el area de atención</option>";
                                        $queryArea = "SELECT  e.IdEstado,e.Nombre  FROM c_estado e,c_flujo f,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND f.IdFlujo=fe.IdFlujo AND f.IdFlujo=2";

                                        $query = $catalogo->obtenerLista($queryArea);
                                        while ($rs = mysql_fetch_array($query)) {
                                            $s = "";
                                            if ($areaAtencion != "" && $areaAtencion == $rs['IdEstado']) {
                                                $s = "selected";
                                            }
                                            echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            <div class="form-group col-md-4">  
                                <label><?php echo $nombre_UsuarioOrigen?></label>
                                    <select class="form-control" id="usuarioOrigen" name="usuarioOrigen" class="select" >
                                        <option value="">Seleccione el <?php echo $nombre_UsuarioOrigen?></option>
                                        <?php 
                                        $queryUsuarioOrigen = "SELECT u.IdUsuario, CONCAT(u.Nombre, ' ', u.ApellidoPaterno, ' ', u.ApellidoMaterno, ' (',p.Nombre,')') AS usuario FROM c_usuario u INNER JOIN c_puesto p ON p.IdPuesto = u.IdPuesto";
                                        $resultUsuarioOrigen = $catalogo->obtenerLista($queryUsuarioOrigen);
                                        while($rs = mysql_fetch_array($resultUsuarioOrigen)){
                                            $s = "";
                                            if(!empty($usuarioOrigen) && $usuarioOrigen == $rs['IdUsuario']){
                                                $s = "selected";
                                            }
                                            echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['usuario'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">  
                                <label>Estado</label>
                                    <select class="form-control" id="sltEstadoTicket" name="sltEstadoTicket" class="select" disabled>                                         
                                        <?php
                                        $consulta = "SELECT * FROM c_estadoticket et WHERE et.Activo=1 ORDER BY et.Nombre ASC";
                                        $query = $catalogo->obtenerLista($consulta);
                                        while ($rs = mysql_fetch_array($query)) {
                                            $s = "";
                                            if ($estadoTicket == $rs['IdEstadoTicket']) {
                                                $s = "selected";
                                            }
                                            echo "<option value='" . $rs['IdEstadoTicket'] . "' $s>" . $rs['Nombre'] . "</option>";
                                        }
                                        ?> 
                                    </select>
                                    <div style='font-size:9px;font-style: italic;color:white;'>Este estado depende del sub-estado del proyecto</div>
                                </div> 
                                <div class="form-group col-md-4">                              
                                <label>SubEstado</label>
                                    <select class="form-control" id="sltSubEstadoTicket" name="sltSubEstadoTicket" class="select" disabled>                                         
                                        <?php
                                        $consulta = "SELECT e.IdEstado, e.Nombre FROM c_estado AS e
                                                INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND kfe.IdFlujo = 6 ORDER BY Nombre;";
                                        $query = $catalogo->obtenerLista($consulta);
                                        echo "<option value=''>Sin sub-estado</option>";
                                        while ($rs = mysql_fetch_array($query)) {
                                            $s = "";
                                            if ($estadoTicket == $rs['IdEstado']) {
                                                $s = "selected";
                                            }
                                            echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                        }
                                        ?> 
                                    </select>
                                    <div style='font-size:9px;font-style: italic;color:white;'>Este sub-estado depende de las <?php echo $nombre_nota; ?> del proyecto</div>
                                </div>
                                <div class="form-group col-md-4">
                                <label>Cliente <span class="obligatorio"> *</span></label>
                                    <select class="form-control" id="cliente_ticket" name="cliente_ticket" class="select"  onchange="cargarContactos('cliente_ticket','contacto_cliente');">
                                        <?php
                                        if (empty($clientes_permitidos)) {
                                            $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                                        } else {
                                            $query = $catalogo->obtenerLista("SELECT NombreRazonSocial, ClaveCliente FROM `c_cliente` WHERE ClaveCliente IN($array_clientes) AND Activo = 1 ORDER BY cliente;");
                                        }
                                        echo "<option value=''>Todos los clientes</option>";
                                        while ($rs = mysql_fetch_array($query)) {
                                            $s = "";
                                            if ($rs['ClaveCliente'] == $ClaveCliente) {
                                                $s = "selected='selected'";
                                            }
                                            echo "<option value='" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                                        }
                                        ?> 
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                <label>Contacto</label><br>
                                    <input  type="radio" name="tipoContacto" value="1" <?php echo $ckContacto?>> Contactos en el catálogo<br>
                                    <input type="radio" name="tipoContacto" value="2"> Ingresar nombre<br>
                                    <div style='font-size:9px;font-style: italic;color:white;'>Seleccione un método para seleccionar el contacto</div>
                               
                                <div id="DDLContacto" style="<?php echo $display?>">
                                    <select class="form-control" id="contacto_cliente" name="contacto_cliente" class="select" >
                                    <?php
                                        if(!empty($contacto)){
                                            include_once("../WEB-INF/Classes/Contacto.class.php");
                                            $obj = new Contacto();
                                            $result2 = $obj->getTodosContactosCliente($ClaveCliente);
                                            echo "<option value=\"null\">Selecciona el contacto</option>";
                                            while ($rsContacto = mysql_fetch_array($result2)) {
                                                $s = "";
                                                if($rsContacto['IdContacto'] == $contacto){
                                                    $s = "selected";
                                                }
                                                echo "<option value='".$rsContacto['IdContacto']."' $s>".$rsContacto['Nombre']." (".$rsContacto['TipoContacto'].")</option>";        
                                            } 
                                        }
                                    ?>
                                    </select>                                    
                                </div>

                                <div id="inputContacto" style="display: none">
                                    <input class="form-control" type="text" id="contacto_nuevo" name="contacto_nuevo">
                                </div> </div>

                         <div>
                        </table>
                     </div> 
                 </div> 
            <br>
                <div class="tabs">
                    <label><a href="#tabs-3">Especificación <?php echo $nombre_objeto; ?></a></label>
                         <div id = "tabs-3" style = "background-color: #FFFFFF">
                            <table style="width: 100%;">
                                 <div class="form-row">
                          <div class="form-group col-md-4">
                                <label>Presupuesto Inicial</label>
                                <input class="form-control" type="text" id="presupuesto" name="presupuesto" value="<?php echo $presupuestoInicial?>"/>
                             </div>   
                          <div class="form-group col-md-4">
                                        <label for="amount">Progreso (%)</label>
                                        <input class="form-control" type="text" value="<?php echo $progreso?>" id="amount" name="amount" readonly style="border:0; color:#f6931f; font-weight:bold;">
                                         <div id="slider"></div>
                                </div>
                          <div class="form-group col-md-4">
                                <label>Prioridad</label>
                                    <?php
                                    $result2 = $catalogo->obtenerLista("SELECT pt.IdPrioridad, pt.Prioridad, tp.TipoPrioridad,  c.Hexadecimal
                                            FROM `c_prioridadticket` AS pt
                                            LEFT JOIN c_color AS c ON c.IdColor = pt.IdColor
                                            LEFT JOIN c_tipoprioridad AS tp ON tp.IdTipoPrioridad = pt.IdTipoPrioridad WHERE pt.Activo = 1;");
                                    echo "<select class= 'form-control' id='prioridad' name='prioridad'>";
                                    echo "<option value = 0 >Seleccione una prioridad</option>";
                                    while ($rs2 = mysql_fetch_array($result2)) {
                                        echo "<option value='" . $rs2['IdPrioridad'] . "' style='background: #" . $rs2['Hexadecimal'] . ";'>" . $rs2['Prioridad'] . " (" . $rs2['TipoPrioridad'] . ")</option>";
                                    }
                                    echo "</select>";
                                    ?>                
                            </div>
                            <?php 
                            if(!empty($idTicket)){
                                $queryVerificar = "SELECT COUNT(*) AS conteo FROM c_notaticket WHERE IdTicket = $idTicket AND Progreso != 100;";
                                $resultVerificar = $catalogo->obtenerLista($queryVerificar);
                                if($rs = mysql_fetch_array($resultVerificar)){
                                    if($rs['conteo'] == "0"){
                            ?>
                            <input type="checkbox" id="cerrarProyecto" name="cerrarProyecto"> Cerrar <?php echo $nombre_objeto?>
                                
                                                    
                            <?php             
                                    }
                                }                                
                            }
                            ?>
                            </div>
                        </table>
                    </div>
                 </div>
            <br>

            <div class="tabs">
                        <label><a href="#tabs-2">Descripción del <?php echo $nombre_objeto; ?></a></label>
                    <div id = "tabs-2" style = "background-color: #FFFFFF">
                        <table>
                            <div class="form-row">
                             <div class="form-group col-md-4">
                                <label>Descripción del <?php echo $nombre_objeto; ?>:</label>
                                <textarea class="form-control" id='descripcion' name='descripcion' <?php echo $read; ?>><?php echo $descripcion; ?></textarea>
                            </div>
                             <div class="form-group col-md-4">
                                <label>Observaciones adicionales:</label>
                                <textarea  class="form-control" id='observacion' name='observacion' <?php echo $read; ?>><?php echo $observacion; ?></textarea>
                            </div> 
                            </div>
                        </table>
                    </div>
                </div>

                <br/>
                 <div class="form-row">
                <div class="form-group col-md-6">
                <?php if ($permisos_grid->getModificar()) { ?>              
                    <input type = "submit" id = "botonGuardar" name = "botonGuardar" class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" value = "Guardar"/> 
                       </div>
                    <div class="form-group col-md-6">                  
                    <input type = "submit" class="button btn btn-lg btn-block btn-outline-danger mt-3 mb-3" value = "Cancelar" onclick = "cambiarContenidos('<?php echo $pagina_listaRegresar; ?>');
                                return false;"/>
                           <?php
                       }
                       ?>
                         </div> </div>
                         
                <input type = "hidden" name = "idTicket" id = "idTicket" value = "<?php echo $idTicket; ?>" />
                <input type = "hidden" name = "txtPermisoRendimiento" id = "txtPermisoRendimiento" value = "<?php echo $permisoEspecialRendimiento; ?>" />
                <input type = "hidden" name = "nombreCC" id = "idTicket" value = "<?php echo $nombreLocalidad; ?>" />
                <input type = "hidden" name = "nombreCliente" id = "idTicket" value = "<?php echo $nombreCliente; ?>" />
                <input type = "hidden" name = "filaSeleccionada" id = "filaSeleccionada" value = ""/>
                <input type = "hidden" name = "tipoUsuario" id = "tipoUsuario" value = "<?php echo $idPuesto; ?>"/>
                <input type = "hidden" name = "rdContacto" id = "rdContacto" value ="-1" />
                <input type = "hidden" name = "txtNombre" id = "txtNombre" value = "<?php echo $nombreContacto . " / " . $telefono . " / " . $celular . " / " . $correoE . " / " . $idContacto . " / " . "8" . " / " . $claveEspecialContacto; ?>" />
                <input type = "hidden" name = "txtTelefono1" id = "txtTelefono1" value = "<?php echo $telefono; ?>"/>
                <input type = "hidden" name = "txtCelular" id = "txtCelular" value = "<?php echo $celular; ?>"/>
                <input type = "hidden" name = "correoElectronico" id = "correoElectronico" value = "<?php echo $correoE; ?>"/>                
                <input type = "hidden" name = "tipoReporte" id = "tipoReporte" value ="300" /><!-- Si va a aceptar mas tipos de tickets, esto se cambia -->
                <input type="hidden" id="nombreNota" value="<?php echo $nombre_nota?>">
                <input type="hidden" id="nombreProyecto" value="<?php echo $nombre_objeto?>">
                <?php if(!empty($contacto)){?>
                <input type = "hidden" id="contactoTemp" value="<?php echo $contacto?>">
                <?php }?>
                <?php
                if ($permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], "mesa/lista_ticket_new.php")) {
                    echo '<input type = "hidden" name = "permiso_tickets2" id = "permiso_tickets2" value ="1" />';
                }
                ?>
                <input type="hidden" id="paginaExito" value="<?php echo $pagina_listaRegresar?>">
            </form>
        </div>
    </body>
</html>
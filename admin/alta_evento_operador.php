<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
date_default_timezone_set('America/Mexico_City');
include_once("../WEB-INF/Classes/EventoOperador.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_evento_operador.php";
$pagina_alta = "admin/alta_evento_operador.php";

$id = "";
$idLN = "";
$idEvento = "";
$idOperador = "";
$Fecha = date('Y') . "-" . date('m') . "-" . date('d');
$Hora = "";
$Comentario = "";
$Dato = "";
$Servicio = "";
$activo = "checked='checked'";
$idPuesto = "";
$catalogo = new Catalogo();
$Hora = date('H') . ":" . date('i') . ":" . date('s');
$pathImagen = "";

if (isset($_POST['idLN'])) {
    $idLN = $_POST['idLN'];
    $id = $_POST['idBitacora'];
    $Fecha = $_POST['fecha'];
    $Hora = $_POST['hora'];
    $Comentario = $_POST['Comentario'];
    $Dato = $_POST['Dato'];
    $Servicio = $_POST['Servicio'];
}
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_evento_operador.js"></script> 
         <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">   
        <!-- FontAwesome para iconos -->
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">       
    </head>
    <body>
        <div class="principal">
            <div class="container-fluid">   
                <?php
             if (isset($_POST['id']) && $_POST['id'] != "") {
                $obj = new EventoOperador();
                $obj->getRegistroById($_POST['id']);
                $id = $obj->getIdBitacora();
                $idEvento = $obj->getEvento();
                $idLN = $obj->getLineaNegocio();
                $idOperador = $obj->getOperador();
                $Fecha = $obj->getFecha();
                $Hora = $obj->getHora();
                $Comentario = $obj->getComentario();
                $Dato = $obj->getDato();
                $Servicio = $obj->getServicio();
                $pathImagen = $obj->getPathImagen();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
            }
            if ($idLN == 1) { //TE
                $idPuesto = 101;
                $idArea = 101;
            } else {
                if ($idLN == 9) { //TV
                    $idPuesto = 108;
                    $idArea = 102;
                } else {
                    if ($idLN == 10) { //TC
                        $idPuesto = 109;
                        $idArea = 103;
                    } else {
                        if ($idLN == 11) { //CC
                            $idPuesto = 110;
                            $idArea = 104;
                        }
                    }
                }
            }
            ?>
            <form id="formBitacora" name="formBitacora" action="/" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="fecha">Fecha</label><span class="obligatorio"> *</span>
                            <input class="form-control" type="text" id="fecha" name="fecha" value="<?php echo $Fecha; ?>"/>
                           </div>
                        <div class="form-group col-md-4">
                            <label for="orden">Hora<span class="obligatorio"> *</span></label>
                            <input class="form-control" type="text" id="hora" name="hora" value="<?php echo $Hora; ?>"/>
                          </div>      
                        <div class="form-group col-md-4">
                            <label for="ln">Linea de Negocio</label><span class="obligatorio"> *</span>
                            <select class="form-control" id="ln" name="ln"  onchange="verPorEvento('admin/alta_evento_operador.php');" onkeydown="checkKey(event);">
                                <?php
                                $query = $catalogo->getListaAlta("c_tipocliente", "Orden");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($idLN != "" && $idLN == $rs['IdTipoCliente']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdTipoCliente'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </div> 
                        <div class="form-group col-md-4">
                        <label>Evento<span class="obligatorio"> *</span></label>
                            <select class="form-control" id="evento" name="evento" s onkeydown="checkKey(event);">
                                <?php
                                /* Inicializamos la clase */
                                $query = $catalogo->obtenerLista("SELECT e.IdEstado, e.Nombre FROM c_estado AS e
                                INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND kfe.IdFlujo = 6 WHERE e.IdArea=" . $idArea . " ORDER BY e.Nombre;");
                                echo "<option value=0>Selecciona un evento</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    if ($rs['IdEstado'] == "2") {
                                        continue;
                                    }
                                    $s = "";
                                    if ($idEvento != "" && $idEvento == $rs['IdEstado']) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?> 
                            </select>
                        </div>
                        <div class="form-group col-md-4"> 
                            <label>Operador<span class="obligatorio"> *</span></label>
                            <select class="form-control" id="operador" name="operador" onkeydown="checkKey(event);"> <!--class="filtro"-->
                                <?php
                                $query = $catalogo->getListaAlta("c_usuario", "Loggin");
                                echo "<option value='0' >Selecciona un operador</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($idOperador != "" && $idOperador == $rs['IdUsuario']) {
                                        $s = "selected='selected'";
                                    }
                                    if ($rs['IdPuesto'] == $idPuesto) {
                                        echo "<option value=" . $rs['IdUsuario'] . " " . $s . ">" . $rs['Loggin'] . "-" . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . " " . $rs['ApellidoMaterno'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                       <div class="form-group col-md-4"> 
                            <label>Dato</label>
                            <input class="form-control" type="text" id="dato" name="dato" value="<?php echo $Dato ?>" onkeydown="checkKey(event);">
                        </div>
                        <div class="form-group col-md-4"> 
                            <label>Servicio</label>
                            <input class="form-control" type="text" id="servicio" name="servicio" value="<?php echo $Servicio ?>" onkeydown="checkKey(event);">
                          </div>
                        <div class="form-group col-md-4"> 
                            <label>Comentario</label>
                            <textarea class="form-control" id='comentario' name='comentario' cols='50' onkeydown="checkKey(event);"><?php echo $Comentario; ?></textarea>
                             </div>
                        <div class="form-group col-md-4">
                         <?php if ($pathImagen == "") { ?>
                            <label>Subir imagen:</label>
                            <input class="form-control" type='file' name='file' id='file'>
                            <?php
                        } else {
                            echo "<td align = 'center'>Imagen <br/><img src='" . $pathImagen . "'></td>";
                            //echo $pathImagen;
                        }
                        ?>
                        </div>
                        <div class="form-group col-md-4">
                            <input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo
                            </div>
                        <div class="form-group col-md-4">
                            </div>
                         <div class="form-group col-md-4">
                            </div>
                        <div class="form-group col-md-4">
                             <center><input type="submit" id="guardar" class="btn btn-success" value="Guardar" /></center>
                              </div>
                        <div class="form-group col-md-4">
                             <center><input type="submit" class="btn btn-warning" value="Borrar" onclick="cambiarContenidos('<?php echo $pagina_alta; ?>');</center>
                                 return false;"/>
                            </div>
                        <div class="form-group col-md-4">  
                            <center><input type="submit" class="btn btn-primary" value="Consulta" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                            return false;"/>
                            <?php
                            echo "<input type='hidden' id='id' name='id' value='" . $id . "'/> ";
                            ?></center>
                        </div>
            </form>
        </div>
    </body>
</html>
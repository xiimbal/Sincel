<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Addenda.class.php");
include_once("../WEB-INF/Classes/AddendaDetalle.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$usuario = new Usuario();
$catalogo = new Catalogo(); 
$permisos_grid = new PermisosSubMenu();

$pagina_lista = "admin/lista_addenda.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $pagina_lista);

$nombre = "";
$id = "";
$obj = new Addenda();
if(isset($_POST['id']) && $obj->getRegistroById($_POST['id'])){
    $id = $obj->getId_addenda();
    $nombre = $obj->getNombre_addenda();
}

?>
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_addenda.js"></script>        
    </head>
    <body>
       <form id="form_addenda">        
           <table>
               <tr>
                   <td><label for="nombre_addenda">Nombre de addenda:</label></td>
                   <td><input type="text" id="nombre_addenda" name="nombre_addenda" value="<?php echo $nombre; ?>"/></td>
               </tr>
           </table>
        <fieldset>
            <legend>Addendas</legend>
            <table style="width: 80%;" id="t_datos_addenda">
                <tr>
                    <td>Campo</td>
                    <td>Valor</td>
                </tr>
                <?php
                    if(!empty($id)){
                        $detalle = new AddendaDetalle();
                        $result = $detalle->getRegistrosByAdenda($id);
                    }else{
                        $result = NULL;
                    }
                    
                    $numero = 1;
                        
                    if (empty($id) || mysql_num_rows($result) == 0) {//Si no hay detalles de addenda
                        echo "<tr id='row_$numero'>";                        
                        echo "<td><input type='text' id='nombre_$numero' name='nombre_$numero'/></td>";                        
                        echo "<td><input type='text' id='valor_$numero' name='valor_$numero'/></td>";
                        echo "<td><input type='checkbox' id='dinamico_$numero' name='dinamico_$numero' value='on'/>Variable por factura</td>";
                        echo '<td><input type="image" src="resources/images/add.png" title="Agregar otro concepto" onclick="agregarConcepto(); return false;" /></td>';                                                
                        echo "<tr>";
                    }else{
                        while ($rs = mysql_fetch_array($result)) {
                            echo "<tr id='row_$numero'>";                            
                            echo "<td><input type='text' id='nombre_$numero' name='nombre_$numero' value='".$rs['campo']."'/></td>";                        
                            echo "<td><input type='text' id='valor_$numero' name='valor_$numero' value='".$rs['valor']."'/></td>";                        
                            $s = "";
                            if($rs['dinamico'] == "1"){
                                $s = "checked='checked'";
                            }
                            echo "<td><input type='checkbox' id='dinamico_$numero' name='dinamico_$numero' value='on' $s/>Variable por factura</td>";
                            echo '<td><input type="image" src="resources/images/add.png" title="Agregar otra concepto" onclick="agregarConcepto(); return false;" /></td>';                                                
                            if ($numero > 1) {
                                echo "<td><input type='image' src='resources/images/Erase.png' title='Eliminar este concepto' onclick='borrarConcepto(" . $numero . "); return false;'/></td>";
                            }
                            $numero++; 
                            echo "</tr>";
                        }
                        $numero--;
                    }
                ?>                
            </table>
        </fieldset>      
        <input type="submit" class="boton" value="Guardar" />
        <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');return false;"/>
        <?php
            echo "<input type='hidden' id='id' name='id' value='" . $id . "'/> ";
        ?>        
        <input type="hidden" id="numero_conceptos" name="numero_conceptos" value="<?php echo $numero; ?>"/>
    </form> 
    </body>
</html>
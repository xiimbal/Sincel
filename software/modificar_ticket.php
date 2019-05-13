<?php
    session_start();
    
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../index.php");
    }
    
    if(!isset($_POST['id'])){
        header("Location: ../index.php");
    }
    
    include_once("../WEB-INF/Classes/Catalogo.class.php");
        
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/modificar_ticket_sw.js"></script>
    </head>
    <body>
        <form id="formTicketSw" name="formTicketSw" action="/" method="POST">
            <input type="hidden" id="ticket_sw" name="ticket_sw" value="<?php echo $_POST['id']; ?>"/>
            <input type="hidden" id="ticket_tipo" name="ticket_tipo" value="2"/>
            <table>
                <tr>
                    <td><label for="tecnico_xw">T&eacutecnico asignado:</label></td>
                    <td>
                        <select id="tecnico_xw" name="tecnico_xw" style="max-width: 180px;">
                            <?php
                            $catalogo = new Catalogo();
                            $query = $catalogo->obtenerLista("SELECT IdUsuario, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS tecnico FROM `c_usuario` WHERE IdPuesto = 20;");
                            echo "<option value='0' >Selecciona una opción</option>";
                            while ($rs = mysql_fetch_array($query)) {                                
                                echo "<option value=" . $rs['IdUsuario'] . ">" . $rs['tecnico'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <input type="submit" class="boton" id="submit_sw" name="submit_sw" value="Guardar"/>                        
                    </td>
                    <td>
                        <input type="button" class="boton" value="Cancelar" onclick="cambiarContenidos('software/mis_tickets.php','Asigna Técnico SW');  return false;"/>
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>
<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Turno.class.php");

$turno = new Turno();
$proveedor = null;

if (isset($_GET['id']) && $_GET['id']) {
    $turno->getRegistroById($_GET['id']);
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/catalogos/alta_turno.js"></script>
<form id="formTurno">
    <table style="width: 70%;">
        <tr>
            <td>Hora Entrada</td>
            <td>
                <div id="horaEntrada" name="horaEntrada">
                <select id="horaE" name="horaE" style="max-width: 250px;">
                    <?php
                    echo "<option value='00' >00</option>";
                    $aux = 0;
                    for($i = 1; $i < 24; $i++) {
                        if($i < 10)
                        {    $aux = "0".$i; }
                        else
                        {   $aux = "".$i; }
                        if(strcmp($aux, substr($turno->getHoraEntrada(), 0, 2)) == 0)
                        {
                            echo "<option value=\"" . $aux . "\" selected=\"selected\" >" . $aux ."</option>";
                        }else
                        {
                            echo "<option value=\"" . $aux . "\" >" . $aux ."</option>";                     
                        }
                    }
                    ?>
                </select>
                <select id="minutosE" name="minutosE" style="max-width: 250px;">
                    <?php
                    echo "<option value='00' >00</option>";
                    $aux = 0;
                    for($i = 15; $i < 60; $i = $i + 15) {
                        $aux = "".$i;
                        if(strcmp($aux, substr($turno->getHoraEntrada(), 3, 2)) == 0)
                        {
                            echo "<option value=\"" . $aux . "\" selected=\"selected\" >" . $aux ."</option>";
                        }else
                        {
                            echo "<option value=\"" . $aux . "\" >" . $aux ."</option>";                     
                        }
                    }
                    ?>
                </select>
                </div>
            </td>
            <td>Hora Salida</td>
            <td>
                <div id="horaSalida" name="horaSalida">
                <select id="horaS" name="horaS" style="max-width: 250px;">
                    <?php
                    echo "<option value='00' >00</option>";
                    $aux = 0;
                    for($i = 1; $i < 24; $i++) {
                        if($i < 10)
                        {    $aux = "0".$i; }
                        else
                        {   $aux = "".$i; }
                        if(strcmp($aux, substr($turno->getHoraSalida(), 0, 2)) == 0)
                        {
                            echo "<option value=\"" . $aux . "\" selected=\"selected\" >" . $aux ."</option>";
                        }else
                        {
                            echo "<option value=\"" . $aux . "\" >" . $aux ."</option>";                     
                        }
                    }
                    ?>
                </select>
                <select id="minutosS" name="minutosS" style="max-width: 250px;">
                    <?php
                    echo "<option value='00' >00</option>";
                    $aux = 0;
                    for($i = 15; $i < 60; $i = $i + 15) {
                        $aux = "".$i;
                        if(strcmp($aux, substr($turno->getHoraSalida(), 3, 2)) == 0)
                        {
                            echo "<option value=\"" . $aux . "\" selected=\"selected\" >" . $aux ."</option>";
                        }else
                        {
                            echo "<option value=\"" . $aux . "\" >" . $aux ."</option>";                     
                        }
                    }
                    ?>
                </select>
                </div>
            </td>
            <td>Descripcion<span class="obligatorio"> *</span></td>
            <td><input type="text" name="descripcion" id="descripcion" value="<?php echo $turno->getDescripcion(); ?>"/></td>
            <td>Activo</td>
            <td><input type="checkbox" value="1" name="activo" id="activo" 
            <?php
                if (isset($_GET['id']) && $_GET['id']) {
                    if ($turno->getActivo() != "" && $turno->getActivo() == 1) {
                        echo "checked";
                    }
                }else{
                    echo "checked";
                }
            ?>/></td>
        </tr>     
    </table>
    <?php
    if (isset($_GET['id']) && $_GET['id']) {
        ?>
        <input type="hidden" name="id" id="id" value="<?php echo $_GET['id'] ?>"/>
    <?php } ?>
    <br/><br/>
    <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
    <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('catalogos/lista_turnos.php', 'Turnos');"/>
</form>



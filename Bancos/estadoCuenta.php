<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
header('Content-Type: text/html; charset=utf-8');
include_once("../WEB-INF/Classes/Catalogo.class.php");
$meses = array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio',
               'Agosto','Septiembre','Octubre','Noviembre','Diciembre');
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/banco/estadoCuenta.js"></script>

<div id="div1"></div>
<form id = "formEstado" name="formEstado" ENCTYPE="multipart/form-data">
    <table width="60%" border="0" cellspacing="5" cellpadding="1" class='tb_add_cat' align='center' >
      <tr>
        <td colspan='2'>Seleccione los parámetros para realizar la carga del estado de cuenta:</td>
      </tr>
      <tr>
        <td>Período*:</td>
        <td>
          <select name="id_periodo" id="id_periodo">		      
            <?php
                $cont = 0;
                echo "<option value='0' >Selecciona una opción</option>";
                for($cont = -5; $cont <= 5; $cont++){
                  $anio = ((int)date("Y")) + $cont;
                  $sel="";
                  if($cont == 0)
                  {
                      $sel = "selected = 'selected'";
                  }
                  echo("<option value='".$anio."' ".$sel." >".$anio."</option>");
                }
            ?>
          </select>
        </td>
      </tr>
      <tr>
        <td>Mes*:</td>
        <td>
          <select name="mes" id="mes">		      
            <?php
                $cont = 0;
                echo "<option value='0' >Selecciona una opción</option>";
                for($cont = 1; $cont <= 12; $cont++){
                  $sel="";
                  if($cont == date("n"))
                  {
                      $sel = "selected = 'selected'";
                  }
                  echo("<option value='".$cont."' ".$sel." >".$meses[$cont]."</option>");
                }
            ?>
          </select>
        </td>
      </tr>
      <tr>
        <td width="">Número de Cuenta*:</td>
        <td width="">
            <div id="copiarComponente" name="copiarComponente">
                <select id="id_cuenta" name="id_cuenta" style="max-width: 250px;">
                    <?php
                    $equipos = new Catalogo();
                    $query = $equipos->getListaAlta("c_cuentaBancaria", "noCuenta");
                    echo "<option value='0' >Selecciona una opción</option>";
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value=\"" . $rs['idCuentaBancaria'] . "\" >" . $rs['noCuenta'] ."</option>";                     
                    }
                    ?>
                </select></div>
        </td>
      </tr>

      <tr>
        <td width="">Archivo con el  estado de cuenta*:</td>
        <td width="">
            <input type='file' name='file' id='file'>
        </td>
      </tr>

      <tr>
        <td align="center" colspan="4"><input id="upload" name ="upload" type="button" value="Upload" class="boton" /></td>
      </tr>
    </table>
</form>


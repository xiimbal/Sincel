<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../index.php");
    }
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
        <meta http-equiv="X-UA-Compatible" content="IE=8" />
        <meta http-equiv="X-UA-Compatible" content="IE=7" />
        <meta name="description" content="SMUC Sistema de Monitoreo Ubicacion y Control"/>
        <meta name="keywords" content="CAOMI,Rackspace,Romas Informatica, MAGG, Mexico, ESCOM"/>
        <meta name="author" content="CAOMI"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>        
        <script type="text/javascript" src="resources/js/paginas/reporteUsoDiario.js"></script>                           
    </head>    
    <body>
         <div id="loader" class="loader" style="margin: 40px 0 0 25px;">                
            <form action="/" method="post" id="fupload" name="fupload" enctype="multipart/form-data">
                <fieldset>
                    <legend>Carga tu archivo</legend>
                    <table>
                        <tr>
                            <td><input type="file" class="button" name="file" id="file" required/></td>
                            <td><input type="submit" class="button" name="upload" id="upload" value="Subir archivo" /></td>
                        </tr>
                    </table>                                        
                    <!--<input type="submit" class="buton" name="upload" id="upload" value="Cancelar" onclick="changeContenidos('logistica/lista_PuntosInteres.php', 'P u n t o s &nbsp; d e &nbsp; I n t e r &eacute; s', false); return false;"/>-->
                </fieldset>
            </form>
          </div>
    </body>
</html>
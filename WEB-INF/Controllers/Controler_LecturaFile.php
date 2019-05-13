<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
set_time_limit (0);
include_once("../Classes/Inventario.class.php");
include_once("../Classes/Catalogo.class.php");
include_once("../Classes/Lectura.class.php");
include_once("../Classes/EquipoCaracteristicasFormatoServicio.class.php");

$tipoArchivo = $_POST['tipo_archivo'];

$catalogo = new Catalogo();
$inventario = new Inventario();
$lectura = new Lectura();
$caracteristica = new EquipoCaracteristicasFormatoServicio();

$iniciarCeldas = false;
$filaValida = true;
$archivoError = false;
$tabla = ""; //Se guardaran todos los datos de la tabla
$rutaArchivo = "../../cargalectura/".$_FILES['file']['name'];

if($tipoArchivo==1){
if(!move_uploaded_file($_FILES['file']['tmp_name'], $rutaArchivo)){
    echo "Error: el archivo ".$_FILES['file']['name']." no pudo subirse al sistema, notifiquelo al administrador por favor.";
    return;
}
$cabecera = array("Comentario","No. Serie", "Periodo" ,"Contador B/N", "Contador CL");
$cliente = $_POST['cliente'];
$periodo = $_POST['anio_lectura']."-".$_POST['mes_lectura']."-01";
$bitacoras = array();

$handle = fopen($rutaArchivo, "r");
echo "<script type='text/javascript' language='javascript' src='../resources/js/paginas/lecturas/mostrarListaCarga.js'></script>";
echo "<form id='formLista'>";
echo "<input type='button' id='cargarBase2' name='cargarBase2' onclick='cargarLista();' value='Registrar lecturas' class='boton' style='display:none;'/><br/>";
echo "<div id='mensaje_faltantes2'></div>";
echo "<table id='mostrarLista'>";
echo "<thead>";
echo "<tr>";
for ($i = 0; $i < (count($cabecera)); $i++) {
    echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabecera[$i] . "</th>";
}
echo "</tr>";
echo "</thead>";
echo "<tbody>";
$i = 0;
$hay_error = false;
$filas_procesadas = false;
$comentarioErrores = "";
$filasCorrectas = 0;

while (!feof($handle)) {
    $data = fgetcsv($handle);
    $filaValida = true;
    if (empty($data[0]) && empty($data[1]) ) {
        $filaValida = false;
    }
    if (strtolower(trim($data[0]) ) == "serie") {
        //Buscaremos en el excel las celdas de inicio de la asistencia
        $iniciarCeldas = true;
        continue;
    } else if($iniciarCeldas) {
        $comentario = "";
        if($filas_procesadas && empty($data[0]) && empty($data[1]) && empty($data[2])){
            continue;
        }
        if (!isset($data[0]) || $data[0] == "" || !isset($data[1]) || $data[1] == "" ) {
            $archivoError = true;   //Si hace falta algún dato indicamos que hay error y terminamos el análisis del archivo
            $comentario.= "Error: Fila mal formada ($serie)<br/>";            
        }
        $serie = $data[0];

        $fecha = $periodo;
        $contadorBN = str_replace(",", "", $data[1]);
        $contadorCL = "";        
        if(isset($data[2]) && $data[2]!=""){
            $contadorCL = str_replace(",", "", $data[2]);
        }
        $color = "";
        if((int)$contadorBN < 0){
            $comentario.="<b>Error</b>: No puede haber contadores negativos ($serie)<br/>";
            $color = "red";
            $hay_error = true;
        }
        if($contadorCl != "" && (int)$contadorCL < 0){
            $comentario.="<b>Error</b>: No puede haber contadores negativos ($serie)<br/>";
            $color = "red";
            $hay_error = true;
        }
        
        $result = $inventario->getDatosDeInventario($serie);
        if(mysql_num_rows($result) > 0){
            while($rs = mysql_fetch_array($result)){
                array_push($bitacoras, $rs['id_bitacora']);
                if($rs['ClaveCliente'] != $cliente){
                    $comentario .= "<b>Error:</b> El equipo se encuentra actualmente en el cliente <b>".$rs['NombreCliente']."</b> ($serie)<br/>";
                    $color = "red";
                    $hay_error = true;
                }
            }
        }else{
            $comentario = "<b>Error:</b> este equipo no se encuenta con ningún cliente ($serie)<br/>";
            $color = "red";
            $hay_error = true;
        }
        
        $inventario->getRegistroById($serie);        
        if($contadorCL == "" && $caracteristica->isColor($inventario->getNoParteEquipo())){
            $comentario .= "<b>Error:</b> No se registó el contador de color del equipo ($serie)<br/>";
            $color = "red";
            $hay_error = true;
        }     
        
        $result = $lectura->getMaximaLecturaCorteNoSerie($serie, $periodo);
        while($rs = mysql_fetch_array($result)){
            //echo "<br/>$serie: ".((int)$rs['MaxContadorBN'])." vs ".((int)$contadorBN);
            if( ((int)$rs['MaxContadorBN']) > ((int)$contadorBN) ){//Si ya hay un contador BN
                $comentario .= "<b>Error</b>: Actualmente ya hay una lectura de corte BN más grande (".  number_format($rs['MaxContadorBN']).") de este equipo a la registrada en el archivo ($serie)<br/>";
                if(empty($color)){
                    $color = "red";
                }
                $hay_error = true;
            }
            if( ((int)$rs['MaxContadorCL']) > ((int)$contadorCL) ){//Si ya hay un contador BN
                $comentario .= "<b>Error</b>: Actualmente ya hay una lectura de corte de color más grande (".  number_format($rs['MaxContadorCL']).") de este equipo a la registrada en el archivo ($serie)<br/>";
                if(empty($color)){
                    $color = "red";
                }
                $hay_error = true;
            }
        }                           
        
        $filas_procesadas=true;
        if($hay_error){
            $comentarioErrores .= $comentario;
            $hay_error = false;
        }else{
        echo "<tr>";
        echo "<td style='color:$color;'>$comentario</td>"; //Comentario
        echo "<td>$serie</td>"; //Serie
        echo "<td>".substr($catalogo->formatoFechaReportes($fecha), 5)."</td>"; //Fecha
        echo "<td>$contadorBN</td>"; //Contador BN
        echo "<td>$contadorCL</td>"; //Contador color                
        echo "</tr>";                
        $i++;
        $filasCorrectas++;
        }
        
    }
}
$hay_error = false;
echo "</tbody>";
echo "</table>";
echo "<br/>";
echo $comentarioErrores;
echo "<br/>";
if(!empty($bitacoras)){
    $consulta = "SELECT id_bitacora, id_solicitud, (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.NombreRazonSocial ELSE c.NombreRazonSocial END) AS NombreRazonSocial, (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.ClaveCliente ELSE c.ClaveCliente END) AS ClaveCliente, (CASE WHEN !ISNULL(cc2.ClaveCentroCosto) THEN cc2.Nombre ELSE cc.Nombre END) AS localidad, a.nombre_almacen, b.NoSerie, CONCAT(e.Modelo,' / ',b.NoParte) AS NoParteCompuesta 
        FROM `c_bitacora` AS b 
        LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte 
        LEFT JOIN c_inventarioequipo AS cinv ON cinv.NoSerie = b.NoSerie 
        LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC 
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC 
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente 
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa 
        LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto 
        LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente 
        LEFT JOIN k_almacenequipo AS kae ON kae.NoSerie = b.NoSerie 
        LEFT JOIN c_almacen AS a ON a.id_almacen = kae.id_almacen 
        WHERE b.id_bitacora NOT IN(".  implode(",", $bitacoras).")
        GROUP BY id_bitacora HAVING ClaveCliente = '$cliente';";
    
    $result = $catalogo->obtenerLista($consulta);
    if(mysql_num_rows($result) > 0){
        echo "<div id='mensaje_faltantes'>No se cargaron en el archivo las lecturas de los siguientes equipos del cliente:";
        while($rs = mysql_fetch_array($result)){
            echo "* <b>".$rs['NoSerie']."</b> - ".$rs['localidad'].", ";
        }
        echo "</div><br/>";
    }
}

if ($archivoError) {
    echo "<font color='red'>Verifique su archivo en Excel ya que no cumple con el formato que se requiere</font>";
    echo "<br/><br/>";
}else if(!$iniciarCeldas){ 
    echo "<font color='red'>Verifique su archivo en Excel ya que no se encuentran los titulos de columnas necesarios</font>";
    echo "<br/><br/>";
}else {
    if (!$hay_error) {        
        echo "<input type='hidden' name='cliente' id='cliente' value='$cliente' />";
            $b=1;
            echo "<input type='hidden' name='ban' id='ban' value='$b' />";
        if($filasCorrectas > 0){
        echo "<input type='button' id='cargarBase' name='cargarBase' onclick='cargarLista();' value='Registrar lecturas' class='boton'/>";
        }
    }
}
echo "<input type='hidden' id='periodo' name='periodo' value='$periodo'/>";
//echo "<input type='button' id='cancelar' name='cancelar' onclick='cambiarContenidos(\"contrato/alta_lecturafile.php\", \"Carga lectura\");' value='Cancelar' class='boton'/>";

echo "</form>";
}else{
//****************************************************  Archivo de tipo Print Fleet
    if($tipoArchivo==2)     
    {
        $listaSeries = array();
        $listaStatus = array();
        $listaClientes = array();
        $listaUbicacion = array();


        if(!move_uploaded_file($_FILES['file']['tmp_name'], $rutaArchivo)){
        echo "Error: el archivo ".$_FILES['file']['name']." no pudo subirse al sistema, notifiquelo al administrador por favor.";
        return;
        }
//        $cabecera = array("Comentario","Cliente","Ultimo informe","Nom dispositivo","No. Serie", "Periodo" ,"Contador B/N", "Contador CL","IP","Ubicacion","Equipo en respaldo","Equipo conectado por USB");
        $cabecera = array("Comentario","Cliente","Ultimo informe","Nom dispositivo","No. Serie", "Periodo" ,"Contador B/N", "Contador CL","IP","Ubicacion");
        $cliente = $_POST['cliente'];
        $periodo = $_POST['anio_lectura']."-".$_POST['mes_lectura']."-01";
        $bitacoras = array();
        $handle = fopen($rutaArchivo, "r");
        
        echo "<script type='text/javascript' language='javascript' src='../resources/js/paginas/lecturas/mostrarListaCarga.js'></script>";
        echo "<form id='formLista'>";
        echo "<input type='button' id='cargarBase2' name='cargarBase2' onclick='cargarLista();' value='Registrar lecturas' class='boton' style='display:none;'/><br/>";
        echo "<div id='mensaje_faltantes2'></div>";
        echo "<table id='mostrarLista' >";
        echo "<thead>";
        echo "<tr>";
        for ($i = 0; $i < (count($cabecera)); $i++) {
            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabecera[$i] . "</th>";
        }
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        $hay_error = false;
        $hay_error2 = false;
        $filas_procesadas = false;
        $comentarioErrores = "";
        $filasCorrectas = 0;
        $i=0;
        $con = 0;
        $clientesPrueba = array();
        $clientes = array();
        while (!feof($handle)) {
            
            $data = fgetcsv($handle);
            $filaValida = true;
            
            $clientesPrueba[$con] = $data[0];
            
            if (strtolower(trim($data[0]) ) != "grupo") {
                if(!strcasecmp($clientesPrueba[$con] , $clientesPrueba[$con-1])==0){ 
                    $clientes[$con]=$clientesPrueba[$con];
                    //echo "<br>".$clientes[$con];
                }
            }
            if (empty($data[0]) && empty($data[1]) ) {
                $filaValida = false;
            }
            if (strtolower(trim($data[0]) ) == "grupo") {
                //Buscaremos en el excel las celdas de inicio de la asistencia
                $iniciarCeldas = true;
                continue;
            } else if($iniciarCeldas) {
                $comentario = "";
                if($filas_procesadas && empty($data[0]) && empty($data[1]) && empty($data[2])){
                    continue;
                }
                if (!isset($data[3]) || $data[3] == "" || !isset($data[4]) || $data[4] == "" ) {
                    $archivoError = true;   //Si hace falta alg�n dato indicamos que hay error y terminamos el an�lisis del archivo
                    $comentario.= "Error: Fila mal formada ($serie)<br/>";          
                }
                $serie = $data[3];

                $listaClientes[$con] = $data[0];    //Lista de clientes
                $listaSeries[$con] = $data[3];      //Lista de series
                $listaUbicacion[$con] = $data[7];   //Lista de Ubicaciones

                $fecha = $periodo;

                $contadorBN = str_replace(",", "", $data[4]);
                $contadorCL = "";        
                if(isset($data[5]) && $data[5]!=""){
                    $contadorCL = str_replace(",", "", $data[5]);
                }
                $color = "";
                if((int)$contadorBN < 0){
                    $comentario.="<b>Error</b>: No puede haber contadores negativos ($serie)<br/>";
                    $color = "red";
                    $hay_error2 = true;
                }
                if($contadorCl != "" && (int)$contadorCL < 0){
                    $comentario.="<b>Error</b>: No puede haber contadores negativos ($serie)<br/>";
                    $color = "red";
                    $hay_error2 = true;
                }
                $result = $inventario->getDatosDeInventario($serie);
                if(mysql_num_rows($result) > 0){
                    while($rs = mysql_fetch_array($result)){
                        array_push($bitacoras, $rs['id_bitacora']);
                        
                        if(!strcasecmp($rs['NombreCliente'], $data[0])==0){
                            /*$comentario .= "<b>Error:</b> El equipo se encuentra actualmente en el cliente <b>".$rs['NombreCliente']."</b> ($serie)<br/>";
                            $color = "red";
                            $hay_error = true;*/
                            $listaStatus[$con] = "Error: El equipo se encuentra actualmente en el cliente".$rs['NombreCliente']." ";
                        }
                    }
                }else{
                    /*$comentario = "<b>Error:</b> este equipo no se encuenta con ningún cliente ($serie)<br/>";
                    $color = "red";
                    $hay_error = true;*/
                    $listaStatus[$con] = "Error: Este equipo no se encuenta con ningun cliente";
                }
                
                $inventario->getRegistroById($serie);        
                if($contadorCL == "" && $caracteristica->isColor($inventario->getNoParteEquipo())){
                    $comentario .= "<b>Error:</b> No se registó el contador de color del equipo ($serie)<br/>";
                    $color = "red";
                    $hay_error2 = true;
                }     

                $result = $lectura->getMaximaLecturaCorteNoSerie($serie, $periodo);
                while($rs = mysql_fetch_array($result)){
                    //echo "<br/>$serie: ".((int)$rs['MaxContadorBN'])." vs ".((int)$contadorBN);
                    if( ((int)$rs['MaxContadorBN']) > ((int)$contadorBN) ){//Si ya hay un contador BN
                        $comentario .= "<b>Error</b>: Actualmente ya hay una lectura de corte BN más grande (".  number_format($rs['MaxContadorBN']).") de este equipo a la registrada en el archivo ($serie)<br/>";
                        if(empty($color)){
                            $color = "red";
                        }
                        $hay_error2 = true;
                    }
                    if( ((int)$rs['MaxContadorCL']) > ((int)$contadorCL) ){//Si ya hay un contador BN
                        $comentario .= "<b>Error</b>: Actualmente ya hay una lectura de corte de color más grande (".  number_format($rs['MaxContadorCL']).") de este equipo a la registrada en el archivo ($serie)<br/>";
                        if(empty($color)){
                            $color = "red";
                        }
                        $hay_error2 = true;
                    }
                }                           

                $filas_procesadas=true;

                if($hay_error2){
                    $comentarioErrores .= $comentario;
                    $hay_error2 = false;
                }else{
                    $listaStatus[$con] = "OK";
                    echo "<tr>";
                    echo "<td style='color:$color;'>$comentario</td>"; //Comentario
                    echo "<td>$data[0]</td>"; //Cliente
                    echo "<td>$data[1]</td>"; //Ultimo informe  
                    echo "<td>$data[2]</td>"; //Nombre de dispositivo
                    echo "<td>$serie</td>"; //Serie
                    echo "<td>".substr($catalogo->formatoFechaReportes($fecha), 5)."</td>"; //Fecha
                    echo "<td>$contadorBN</td>"; //Contador BN
                    echo "<td>$contadorCL</td>"; //Contador color                
                    echo "<td>$data[6]</td>"; //Direccion IP
                    echo "<td>$data[7]</td>"; //Direccion IP
    //                echo "<td>$data[8]</td>"; //Direccion IP
    //                echo "<td>$data[9]</td>"; //Direccion IP
                    echo "</tr>";                
                    $i++;
                    $filasCorrectas++;
                }

            }
         $con++;
        }        

        $hay_error = false;
        echo "</tbody>";
        echo "</table>";
        echo "<br/>";
        echo $comentarioErrores;
        echo "<br/>";
        if(!empty($bitacoras)){
            $consulta = "SELECT id_bitacora, id_solicitud, (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.NombreRazonSocial ELSE c.NombreRazonSocial END) AS NombreRazonSocial, (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.ClaveCliente ELSE c.ClaveCliente END) AS ClaveCliente, (CASE WHEN !ISNULL(cc2.ClaveCentroCosto) THEN cc2.Nombre ELSE cc.Nombre END) AS localidad, a.nombre_almacen, b.NoSerie, CONCAT(e.Modelo,' / ',b.NoParte) AS NoParteCompuesta 
                FROM `c_bitacora` AS b 
                LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte 
                LEFT JOIN c_inventarioequipo AS cinv ON cinv.NoSerie = b.NoSerie 
                LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC 
                LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC 
                LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente 
                LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa 
                LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto 
                LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente 
                LEFT JOIN k_almacenequipo AS kae ON kae.NoSerie = b.NoSerie 
                LEFT JOIN c_almacen AS a ON a.id_almacen = kae.id_almacen 
                WHERE b.id_bitacora NOT IN(".  implode(",", $bitacoras).")
                GROUP BY id_bitacora HAVING ClaveCliente = '$cliente';";
                //echo "<br>".$consulta;
            $result = $catalogo->obtenerLista($consulta);
            if(mysql_num_rows($result) > 0){
                echo "<div id='mensaje_faltantes'>No se cargaron en el archivo las lecturas de los siguientes equipos del cliente:";
                while($rs = mysql_fetch_array($result)){
                    echo "* <b>".$rs['NoSerie']."</b> - ".$rs['localidad'].", ";
                }
                echo "</div><br/>";
            }
        }

        if ($archivoError) {
            echo "<font color='red'>Verifique su archivo en Excel ya que no cumple con el formato que se requiere</font>";
            echo "<br/><br/>";
        }else if(!$iniciarCeldas){ 
            echo "<font color='red'>Verifique su archivo en Excel, ya que no se encuentran los titulos de columnas necesarios</font>";
            echo "<br/><br/>";
        }else {
            if (!$hay_error) {        
                echo "<input type='hidden' name='cliente' id='cliente' value='$cliente' />";
                $b=0;
                echo "<input type='hidden' name='ban' id='ban' value='$b' />";
                echo "<input type='hidden' name='clientes' id='clientes' value='$clientes' />";
                if($filasCorrectas > 0){
                    echo "<input type='button' id='cargarBase' name='cargarBase' onclick='cargarLista();' value='Registrar lecturas' class='boton'/>";
                }
            }
        }

        echo "<input type='hidden' id='periodo' name='periodo' value='$periodo'/>";

        echo "<input type='hidden' id='listaSeries' name='listaSeries' value=' ".implode("|",$listaSeries)."'/>";
        echo "<input type='hidden' id='listaClientes' name='listaClientes' value=' ".implode("|",$listaClientes)." '/>";
        echo "<input type='hidden' id='listaUbicacion' name='listaUbicacion' value=' ".implode("|",$listaUbicacion)." '/>";
        echo "<input type='hidden' id='listaStatus' name='listaStatus' value=' ".implode("|",$listaStatus)." '/>";

        //echo "<input type='button' id='cancelar' name='cancelar' onclick='cambiarContenidos(\"contrato/alta_lecturafile.php\", \"Carga lectura\");' value='Cancelar' class='boton'/>";

        echo "</form>";
    }
}

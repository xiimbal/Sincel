<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/ResurtidoToner.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$permiso = new PermisosSubMenu();
$resurtido = new ResurtidoToner();
$catalogo = new Catalogo();
$where = "";
$ticketAnterior = 0;
$fechaAnterior = "";
$primeraFila1 = "";
$primeraFila2 = "";
$varEncabezadoAlmacen = "";
$varEncabezadoCliente = "";
$varEncabezadoLocalidad = "";
$tabla1 = "";
$tabla = "";
$val = false;
$nota = false;
$rowspan = 1;
$IdTicket = "";
$fecha = "";
$almacen = "";
$cliente = "";
$localidad = "";
$claveLocalidad = "";
$idAlmacen = "";

if (isset($_GET['idTicket']) && $_GET['idTicket'] != "") {
    $resurtido->setIdTicket($_GET['idTicket']);
}
if (isset($_GET['almacen']) && $_GET['almacen'] != "") {
    $resurtido->setAlmacen($_GET['almacen']);
    $where = " AND a.id_almacen = ".$_GET['almacen'];
}
if (isset($_GET['fecha1']) && $_GET['fecha1'] != "") {
    $resurtido->setFecha1($_GET['fecha1'] . " 00:00:00");
    if (isset($_GET['fecha2']) && $_GET['fecha2'] != "") {
        $resurtido->setFecha2($_GET['fecha2'] . " 23:59:59");
    } else {
        $hoy = date("Y-n-j");
        $resurtido->setFecha2($hoy);
    }
}
if (isset($_GET['cliente']) && $_GET['cliente'] != "") {
    $resurtido->setCliente($_GET['cliente']);
    $where.= " AND t.ClaveCliente = '".$_GET['cliente']."'";
}
if (isset($_GET['localidad']) && $_GET['localidad'] != "") {
    $resurtido->setLocalidad($_GET['localidad']);
    $where.= " AND t.ClaveCentroCosto = '".$_GET['localidad']."'";
}
if (isset($_GET['equipo']) && $_GET['equipo'] != "") {
    $resurtido->setEquipo($_GET['equipo']);
    $where.= " AND lt.ClvEsp_Equipo = '".$_GET['equipo']."'";
}
$query = $resurtido->getTabla();
$resurtido->ponerNombre();

if ($resurtido->getAlmacenN() != "") {
    $tabla1.= "<tr><td class='mediano obscuro'>Almacén: </td><td style='width: 75%;'>" . $resurtido->getAlmacenN() . "</td></tr>";
} else {
    $varEncabezadoAlmacen = "Almacén";
    $tabla1.= "<tr><td class='mediano obscuro'>Almacén: </td><td style='width: 75%;'>Todos los almacenes</td></tr>";
}
if ($resurtido->getFecha1() != "") {
    $tabla1.= "<tr><td class='mediano obscuro'>Fecha: </td><td style='width: 75%;'>" . $resurtido->getFecha1() . " hasta " . $resurtido->getFecha2() . "</td></tr>";
} else {
    $tabla1.= "<tr><td class='mediano obscuro'>Fecha: </td><td style='width: 75%;'>Todas las fechas</td></tr>";
}if ($resurtido->getClienteN() != "") {
    $tabla1.= "<tr><td class='mediano obscuro'>Cliente: </td><td style='width: 75%;'>" . $resurtido->getClienteN() . "</td></tr>";
} else {
    $varEncabezadoCliente = "Cliente";
    $tabla1.= "<tr><td class='mediano obscuro'>Cliente: </td><td style='width: 75%;'>Todos los cliente</td></tr>";
}if ($resurtido->getLocalidadN() != "") {
    $tabla1.= "<tr><td class='mediano obscuro'>Localidad: </td><td style='width: 75%;'>" . $resurtido->getLocalidadN() . "</td></tr>";
} else {
    $varEncabezadoLocalidad = "Localidad";
    $tabla1.= "<tr><td class='mediano obscuro'>Localidad: </td><td style='width: 75%;'>Todas las localidades</td></tr>";
}if ($resurtido->getEquipo() != "") {
    $tabla1.= "<tr><td class='mediano obscuro'>Equipo: </td><td style='width: 75%;'>".$resurtido->getEquipo()."</td></tr>";
}else {
    $tabla1.= "<tr><td class='mediano obscuro'>Cliente: </td><td style='width: 75%;'>Todos los equipo</td></tr>";
}

while ($resultSet = mysql_fetch_array($query)) {
    if($ticketAnterior == 0){
        $ticketAnterior = $resultSet['IdTicket'];
    }
    
    if($ticketAnterior != (int)$resultSet['IdTicket']){
        $tabla.= "<tr>";
        $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $ticketAnterior
        . "<a href='reporte_toner_ticket.php?idTicket=$ticketAnterior'  target='_blank'><img src='../resources/images/icono_impresora.png' width='20' height='20'></a></td>";
        $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $fechaAnterior . "</td>";

        $tabla.= $primeraFila1;
        if($contestada != 1){
            $tabla.= "<td class='borde centrado' rowspan='$rowspan'>Sin autorizar</td>";
        }
        $tabla.=$primeraFila2;
        
        if ($varEncabezadoAlmacen != "") {
            $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $almacen . "</td>";
        }
        if ($varEncabezadoCliente != "") {
            $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $cliente . "</td>";
        }
        if ($varEncabezadoLocalidad != "") {
            $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $localidad . "</td>";
        }
        $tabla.= "</tr>";       
        $tabla.= $filas;
        
        $rowspan = 1;
        $filas = "";
        
        $primeraFila1 = "";
        $primeraFila2 = "";
        $primeraFila1.= "<td class='borde centrado'>" . $resultSet['ModeloT'] . "</td>";
        $primeraFila1.= "<td class='borde centrado'>" . $resultSet['precio'] . "</td>";
        $primeraFila1.= "<td class='borde centrado'>" . $resultSet['CantidadSolicitada'] . "</td>";
        if((int)$resultSet['mail'] == 1){
            if(isset($resultSet['Cantidad']) && $resultSet['Cantidad'] != ""){
                $primeraFila1.= "<td class='borde centrado'>" . $resultSet['Cantidad'] . "</td>";
            }else{
                $primeraFila1.= "<td class='borde centrado'>" . 0 . "</td>";
            }      
            if(isset($resultSet['existencia'])){
                $primeraFila2.= "<td class='borde centrado'>" . $resultSet['existencia'] . "</td>";
            }else{
                $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                $nota = true;
            }
            if(isset($resultSet['minimo'])){
                $primeraFila2.= "<td class='borde centrado'>" . $resultSet['minimo'] . "</td>";
            }else{
                $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                $nota = true;
            }
            if(isset($resultSet['maximo'])){
                $primeraFila2.= "<td class='borde centrado'>" . $resultSet['maximo'] . "</td>";
            }else{
                $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                $nota = true;
            }
        }else{
            if(isset($resultSet['existenciaA'])){
                $primeraFila2.= "<td class='borde centrado'>" . $resultSet['existenciaA'] . "</td>";
            }else{
                $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                $nota = true;
            }
            if(isset($resultSet['minimoA'])){
                $primeraFila2.= "<td class='borde centrado'>" . $resultSet['minimoA'] . "</td>";
            }else{
                $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                $nota = true;
            }
            if(isset($resultSet['maximoA'])){
                $primeraFila2.= "<td class='borde centrado'>" . $resultSet['maximoA'] . "</td>";
            }else{
                $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                $nota = true;
            }
        }  
    }else{
        if($primeraFila1 == ""){
            $primeraFila1.= "<td class='borde centrado'>" . $resultSet['ModeloT'] . "</td>";
            $primeraFila1.= "<td class='borde centrado'>" . $resultSet['precio'] . "</td>";
            $primeraFila1.= "<td class='borde centrado'>" . $resultSet['CantidadSolicitada'] . "</td>";
            if((int)$resultSet['mail'] == 1){
                if(isset($resultSet['Cantidad']) && $resultSet['Cantidad'] != ""){
                    $primeraFila1.= "<td class='borde centrado'>" . $resultSet['Cantidad'] . "</td>";
                }else{
                    $primeraFila1.= "<td class='borde centrado'>" . 0 . "</td>";
                }  
                if(isset($resultSet['existencia'])){
                    $primeraFila2.= "<td class='borde centrado'>" . $resultSet['existencia'] . "</td>";
                }else{
                    $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['minimo'])){
                    $primeraFila2.= "<td class='borde centrado'>" . $resultSet['minimo'] . "</td>";
                }else{
                    $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['maximo'])){
                    $primeraFila2.= "<td class='borde centrado'>" . $resultSet['maximo'] . "</td>";
                }else{
                    $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
            }else{
                if(isset($resultSet['existenciaA'])){
                    $primeraFila2.= "<td class='borde centrado'>" . $resultSet['existenciaA'] . "</td>";
                }else{
                    $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['minimoA'])){
                    $primeraFila2.= "<td class='borde centrado'>" . $resultSet['minimoA'] . "</td>";
                }else{
                    $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['maximoA'])){
                    $primeraFila2.= "<td class='borde centrado'>" . $resultSet['maximoA'] . "</td>";
                }else{
                    $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
            }  
            $rowspan = 0;
        }else{
            $filas.= "<tr>";
            $filas.= "<td class='borde centrado'>" . $resultSet['ModeloT'] . "</td>";
            $filas.= "<td class='borde centrado'>" . $resultSet['precio'] . "</td>";
            $filas.= "<td class='borde centrado'>" . $resultSet['CantidadSolicitada'] . "</td>";
            if((int)$resultSet['mail'] == 1){
                if(isset($resultSet['Cantidad']) && $resultSet['Cantidad'] != ""){
                    $filas.= "<td class='borde centrado'>" . $resultSet['Cantidad'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>" . 0 . "</td>";
                }
                if(isset($resultSet['existencia'])){
                    $filas.= "<td class='borde centrado'>" . $resultSet['existencia'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['minimo'])){
                    $filas.= "<td class='borde centrado'>" . $resultSet['minimo'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['maximo'])){
                    $filas.= "<td class='borde centrado'>" . $resultSet['maximo'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
            }else{
                if(isset($resultSet['existenciaA'])){
                    $filas.= "<td class='borde centrado'>" . $resultSet['existenciaA'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['minimoA'])){
                    $filas.= "<td class='borde centrado'>" . $resultSet['minimoA'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['maximoA'])){
                    $filas.= "<td class='borde centrado'>" . $resultSet['maximoA'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
            }
            $filas.= "</tr>";
        }
        $rowspan++;
        $IdTicket = $resultSet['IdTicket'];
        $fecha = $resultSet['Fecha'];
        $almacen = $resultSet['almacen'];
        $idAlmacen = $resultSet['IdAlmacen'];
        $cliente = $resultSet['cliente'];
        $localidad = $resultSet['localidad'];
        $claveLocalidad = $resultSet['ClaveCentroCosto'];
    }
    $ticketAnterior = (int)$resultSet['IdTicket'];
    $fechaAnterior = $resultSet['Fecha'];
    $val = true;
    $claveCliente = $resultSet['ClaveCliente'];
    $contestada = (int)$resultSet['mail'];
}
if ($val == true){
    $tabla.= "<tr>";
    $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $ticketAnterior
    . "<a href='reporte_toner_ticket.php?idTicket=$ticketAnterior' target='_blank'><img src='../resources/images/icono_impresora.png' width='20' height='20'></a></td>";
    $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $fechaAnterior . "</td>";

    $tabla.= $primeraFila1;
        if($contestada != 1){
            $tabla.= "<td class='borde centrado' rowspan='$rowspan'>Sin autorizar</td>";
        }
    $tabla.=$primeraFila2;

    if ($varEncabezadoAlmacen != "") {
        $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $almacen . "</td>";
    }
    if ($varEncabezadoCliente != "") {
        $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $cliente . "</td>";
    }
    if ($varEncabezadoLocalidad != "") {
        $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $localidad . "</td>";
    }
    $tabla.= "</tr>";       
    $tabla.= $filas;
}
if ($val == false) {
    $tabla.= "<tr>";
    $tabla.= "<td class='borde centrado' colspan='13'>No se encontraron datos que coincidieran con su búsqueda</td>";
    $tabla.= "</tr>";
}

$logo = "";
//Query para obtener el logo
$consultaLogo = "SELECT ImagenPHP FROM c_datosfacturacionempresa dfe "
        . "INNER JOIN c_cliente AS c ON c.IdDatosFacturacionEmpresa = dfe.IdDatosFacturacionEmpresa"
        . " WHERE c.ClaveCliente = '$claveCliente'";
$resultLogo = $catalogo->obtenerLista($consultaLogo);
if(mysql_num_rows($resultLogo) > 0){
    if($rsLogo = mysql_fetch_array($resultLogo)){
        $logo = "../".$rsLogo['ImagenPHP'];
    }
}

if($logo == ""){
    $parametroGlobal = new ParametroGlobal();
    $parametroGlobal->getRegistroById(5);
    $logo = "../".$parametroGlobal->getValor();
}
?>
<!DOCTYPE HTML>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style>
            body{font-family: Arial; font-size: 15px;}
            .titulo{font-weight: bold; font-size: 18px;}
            table{
                border-collapse:collapse;
            }            
            .borde{border: 1px solid #000;}
            .mediano{width: 30%;}
            .gigantes{width: 600px;}
            /*.pagebreak { page-break-after: always; page-break-before: always; }*/
            .espacio{min-height: 100px;}
            /*.obscuro{background-color: #404040; color: white; text-align: center;  font-style: italic; -webkit-print-color-adjust:exact;}
            .gris{background-color: #C0C0C0; font-weight: bold;  -webkit-print-color-adjust:exact;}
            .color{background-color: #3333CC; color: white; -webkit-print-color-adjust:exact; }
            .bn{background-color: #000; color: white; }*/
            .obscuro{color: black; text-align: center;  font-style: italic;}
            .gris{font-weight: bold; }
            .color{color: black;}
            .bn{color: black; }

            .pie{font-size: 10px; color: #800000;}
            .centrado {text-align: center;}
            .completeSize{width: 97%;}
        </style>
        <title>Reporte</title>
        <link rel="shortcut icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
    </head>
    <body>
        <a href=javascript:window.print(); style="margin: 85%;">Imprimir</a>
        <div class="principal">            
            <img src="<?php echo $logo; ?>" style="float:right; margin: 0% 20% 5% 0%; height: 45px;"/>            
            <div class="titulo">Reporte de resurtido de toner</div>           
            <br/><br/>
            <table>
                <?php
                    echo $tabla1;
                ?>
            </table>          
        </div>
        <br/>
        <table class="completeSize">
            <tr>
                <th class="borde centrado">Ticket</th> 
                <th class="borde centrado">Fecha</th>
                <th class="borde centrado">Modelo</th>
                <th class="borde centrado">Precio USD</th>               
                <th class="borde centrado">Cantidad Solicitada</th>
                <th class="borde centrado">Cantidad Surtida</th>
                <th class="borde centrado">Existencia</th>
                <th class="borde centrado">Mínimo</th>
                <th class="borde centrado">Máximo</th>
                <?php
                if ($varEncabezadoAlmacen != "") {
                    echo "<td class='borde centrado'>" . $varEncabezadoAlmacen . "</td>";
                }
                if ($varEncabezadoCliente != "") {
                    echo "<td class='borde centrado'>" . $varEncabezadoCliente . "</td>";
                }
                if ($varEncabezadoLocalidad != "") {
                    echo "<td class='borde centrado'>" . $varEncabezadoLocalidad . "</td>";
                }
                ?>
            </tr>
            <?php
                echo $tabla;
            ?>
        </table>
        <br/>
        <?php 
            if($nota){
                echo "<h5>Los datos de mínimos,máximos y existencias están disponibles para tickets creados despues del día 7/03/2016 </h5>";
            }
        ?>
        <br/><br/>
        <div style="page-break-after: always;"></div>
    </body>
</html>


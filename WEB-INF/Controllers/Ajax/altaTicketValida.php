<?php


session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
include_once("../../Classes/Catalogo.class.php");
$catalogo = new Catalogo();

if(isset($_POST['valida_toner']) && isset($_POST['form'])){    
    include_once("../../Classes/Componente.class.php");
    include_once("../../Classes/Parametros.class.php");
    include_once("../../Classes/PermisosSubMenu.class.php");    
    
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    
    $cantidad_tabla = $_POST['tabla'];          
    
    $componente = new Componente();
    $parametros_obj = new Parametros();
    $permiso = new PermisosSubMenu();
    
    $rendimiento_general = 0;
    if($parametros_obj->getRegistroById(5)){
        $rendimiento_general = $parametros_obj->getValor();
    }else{
        echo "<br/>Error: no se pudo obtener el valor de rendimientos del sistema";
        return;
    }
    
    if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 12)) {
        $permisoEspecialRendimiento = "1";
    } else {
        $permisoEspecialRendimiento = "0";
    }        
        
    if(isset($parametros['idTicket']) && !empty($parametros['idTicket'])){
        $idTicket = $parametros['idTicket'];
    }else{
        $idTicket = "";
    }
    
    for($i = 0; $i < $cantidad_tabla; $i++){
        if(isset($parametros["activar_$i"]) && $parametros["activar_$i"] == "on"){//Si se selecciono el equipo
            $serie = $parametros["txtNoSerieE_$i"];
            $modelo = $parametros["txtModeloE_$i"];
            if( (isset($parametros["txtContadorNegro_$i"]) && empty($parametros["txtContadorNegro_$i"])) ){
                echo "Error: no se detectaron el contador negro actual del equipo $serie";
                continue;
            }
            if( (isset($parametros["txtContadorColor_$i"]) && empty($parametros["txtContadorColor_$i"])) ){
                echo "Error: no se detectaron el contador de color actual del equipo $serie";
                continue;
            }
            if( (isset($parametros["txtContadorNegroAnterior_$i"]) && empty($parametros["txtContadorNegroAnterior_$i"])) ){                
                continue;
            }
            if( (isset($parametros["txtContadorColorAnterior_$i"]) && empty($parametros["txtContadorColorAnterior_$i"])) ){                
                continue;
            }
            
            $noParte = $parametros["txtNoParteE_$i"];
           
            
            //Toner negro            
            if( isset($parametros['ckbNegro_' . $i]) && $parametros['ckbNegro_' . $i] == "on" &&
                isset($parametros["txtTonerNegro$i"]) && !empty($parametros["txtTonerNegro$i"])){
                $validar = true;
                if(!empty($idTicket)){
                    $consulta = "SELECT IdDetalleNotaRefaccion 
                        FROM k_detalle_notarefaccion AS kdn
                        LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = kdn.IdNota
                        WHERE nt.IdTicket = $idTicket AND kdn.Componente = '".$parametros["txtTonerNegro$i"]."' AND kdn.NoSerieEquipo = '$serie';";
                    $result = $catalogo->obtenerLista($consulta);
                    if(mysql_num_rows($result) > 0){
                        $validar = false;
                    }
                }
                    
                if($validar){
                    if($componente->getRegistroById($parametros["txtTonerNegro$i"])){                                        
                        if($componente->getRendimiento() != ""){
                            $totalContadores = ((int)$parametros["txtContadorNegro_$i"] - (int)$parametros["txtContadorNegroAnterior_$i"]);
                            $porcentaje = ($totalContadores * 100) / $componente->getRendimiento();                        
                            if($rendimiento_general > 0 && $rendimiento_general > $porcentaje){
                                echo "El consumo de tóner negro del equipo <b>$serie / $modelo</b> fue de " .number_format($totalContadores). "(".  number_format($porcentaje)."%) impresiones. "
                                        . "El rendimiento del tóner <b>".$componente->getModelo()." / ".$componente->getNumero()."</b> es de " .number_format($componente->getRendimiento()). " impresiones<br/><br/>";
                            }
                        }                                        
                    }else{
                        echo "<br/>Error, no se encuentra el número de parte: ".$parametros["txtTonerNegro$i"];
                    }
                }
            }
            
            //Toner cian
            if(isset($parametros['ckbCian_' . $i]) && $parametros['ckbCian_' . $i] == "on" &&
                isset($parametros["txtTonerCian$i"]) && !empty($parametros["txtTonerCian$i"])){
                $validar = true;
                if(!empty($idTicket)){
                    $consulta = "SELECT IdDetalleNotaRefaccion 
                        FROM k_detalle_notarefaccion AS kdn
                        LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = kdn.IdNota
                        WHERE nt.IdTicket = $idTicket AND kdn.Componente = '".$parametros["txtTonerCian$i"]."' AND kdn.NoSerieEquipo = '$serie';";
                    $result = $catalogo->obtenerLista($consulta);
                    if(mysql_num_rows($result) > 0){
                        $validar = false;
                    }
                }
                
                if($validar){
                    if($componente->getRegistroById($parametros["txtTonerCian$i"])){                                        
                        if($componente->getRendimiento() != ""){
                            $totalContadores = ((int)$parametros["txtContadorColor_$i"] - (int)$parametros["txtContadorColorAnterior_$i"]);
                            $porcentaje = ($totalContadores * 100) / $componente->getRendimiento();
                            if($rendimiento_general > 0 && $rendimiento_general > $porcentaje){
                                echo "El consumo de tóner de color del equipo <b>$serie / $modelo</b> fue de " .number_format($totalContadores). "(".  number_format($porcentaje)."%) impresiones. "
                                        . "El rendimiento del tóner <b>".$componente->getModelo()."</b> es de " .number_format($componente->getRendimiento()). " impresiones<br/><br/>";
                            }
                        }                                        
                    }else{
                        echo "<br/>Error, no se encuentra el número de parte: ".$parametros["txtTonerCian$i"];
                    }
                }
            }
            
             //Toner magenta
            if(isset($parametros['ckbMagenta_' . $i]) && $parametros['ckbMagenta_' . $i] == "on" &&
                    isset($parametros["txtTonerMagenta$i"]) && !empty($parametros["txtTonerMagenta$i"])){
                $validar = true;
                if(!empty($idTicket)){
                    $consulta = "SELECT IdDetalleNotaRefaccion 
                        FROM k_detalle_notarefaccion AS kdn
                        LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = kdn.IdNota
                        WHERE nt.IdTicket = $idTicket AND kdn.Componente = '".$parametros["txtTonerMagenta$i"]."' AND kdn.NoSerieEquipo = '$serie';";
                    $result = $catalogo->obtenerLista($consulta);
                    if(mysql_num_rows($result) > 0){
                        $validar = false;
                    }
                }
                
                if($validar){
                    if($componente->getRegistroById($parametros["txtTonerMagenta$i"])){
                        if($componente->getRendimiento() != ""){
                            $totalContadores = ((int)$parametros["txtContadorColor_$i"] - (int)$parametros["txtContadorColorAnterior_$i"]);
                            $porcentaje = ($totalContadores * 100) / $componente->getRendimiento();
                            if($rendimiento_general > 0 && $rendimiento_general > $porcentaje){
                                echo "El consumo de tóner de color del equipo <b>$serie / $modelo</b> fue de " .number_format($totalContadores). "(".  number_format($porcentaje)."%) impresiones. "
                                        . "El rendimiento del tóner <b>".$componente->getModelo()."</b> es de " .number_format($componente->getRendimiento()). " impresiones<br/><br/>";
                            }
                        }                                        
                    }else{
                        echo "<br/>Error, no se encuentra el número de parte: ".$parametros["txtTonerMagenta$i"];
                    }
                }
            }
            
             //Toner yellow
            if(isset($parametros['ckbAmarillo_' . $i]) && $parametros['ckbAmarillo_' . $i] == "on" &&
                    isset($parametros["txtTonerAmarillo$i"]) && !empty($parametros["txtTonerAmarillo$i"])){
                $validar = true;
                if(!empty($idTicket)){
                    $consulta = "SELECT IdDetalleNotaRefaccion 
                        FROM k_detalle_notarefaccion AS kdn
                        LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = kdn.IdNota
                        WHERE nt.IdTicket = $idTicket AND kdn.Componente = '".$parametros["txtTonerAmarillo$i"]."' AND kdn.NoSerieEquipo = '$serie';";
                    $result = $catalogo->obtenerLista($consulta);
                    if(mysql_num_rows($result) > 0){
                        $validar = false;
                    }
                }
                
                if($validar){
                    if($componente->getRegistroById($parametros["txtTonerAmarillo$i"])){
                        if($componente->getRendimiento() != ""){
                            $totalContadores = ((int)$parametros["txtContadorColor_$i"] - (int)$parametros["txtContadorColorAnterior_$i"]);
                            $porcentaje = ($totalContadores * 100) / $componente->getRendimiento();
                            if($rendimiento_general > 0 && $rendimiento_general > $porcentaje){
                                echo "El consumo de tóner de color del equipo <b>$serie / $modelo</b> fue de " .number_format($totalContadores). "(".  number_format($porcentaje)."%) impresiones. "
                                        . "El rendimiento del tóner <b>".$componente->getModelo()."</b> es de " .number_format($componente->getRendimiento()). " impresiones<br/><br/>";
                            }
                        }                                        
                    }else{
                        echo "<br/>Error, no se encuentra el número de parte: ".$parametros["txtTonerAmarillo$i"];
                    }
                }
            }
        }
    }
}else if(isset ($_POST['imprimir']) && $_POST['imprimir']=="1" && isset ($_POST['ticket'])){
    include_once("../../Classes/TicketImpreso.class.php");
    
    $ticket = new TicketImpreso();
    $ticket->setIdTicket($_POST['ticket']);
    $result = $ticket->getRegistrosPorTicket();    
    if(mysql_num_rows($result) > 0){
        echo "El ticket <b>".$ticket->getIdTicket()."</b> ya ha sido impreso por:";
        while($rs = mysql_fetch_array($result)){
            echo "<br/>".$rs['Usuario']." el día ".$catalogo->formatoFechaReportes($rs['Fecha'])." con hora ".$rs['Hora'];
        }
    }
    
}else if(isset ($_POST['confirmar_imprimir']) && $_POST['confirmar_imprimir']=="1" && isset ($_POST['ticket'])){
    include_once("../../Classes/TicketImpreso.class.php");
    
    $ticket = new TicketImpreso();
    $ticket->setIdTicket($_POST['ticket']);
    $ticket->setIdUsuario($_SESSION['idUsuario']);
    $ticket->setPantalla("detalleReporte JS");
    if(!$ticket->newRegistro()){
        echo "<br/>Error: no se pudo registrar la impresión del ticket";
    }
}

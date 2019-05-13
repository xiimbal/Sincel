<?php

include_once("Detalle_Orden_Compra.class.php");
include_once("Componente.class.php");
include_once("Equipo.class.php");
include_once("Parametros.class.php");
include_once("Catalogo.class.php");

/**
 * Description of LeerCSV_OC
 *
 * @author MAGG
 */
class LeerCSV_OC {
    
    public function cargarCSVEntrada($archivo, $id_compra, $folio_factura, $usuario, $pantalla, $almacen, $estatus, $estadoOC, $empresa){
        $usuario = $_SESSION['user'];
        $pantalla = "LeerSCV_OC";        
        if (($gestor = fopen($archivo, "r")) !== FALSE) {
            $catalogo = new Catalogo();
            $equipo = new Equipo();
            $componente = new Componente();
            $ids_componentes = array();
            $arrayCantidad = array();
            $arrayUbicacion = array();
            $arraySerie = array();
            $componentes_no_existentes_almacen = array();
            $componentes_no_existentes = array();
            while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                $numero = count($datos);
                $procesar_fila = false;                
                $noParte = "";
                echo "<br>";
                for ($c = 0; $c < $numero; $c++) {
                    if($c == 0 && is_numeric($datos[$c])){
                        $procesar_fila = true;
                    }
                    if($procesar_fila && $c == 2){
                        array_push($arrayCantidad, urlencode(((int)$datos[$c])) );
                    }
                    if($procesar_fila && $c == 6){
                        /*Verificamos si el registro es de un equipo o de un componente*/
                        if($equipo->getRegistroById($noParte)){
                            $serie = $datos[$c];
                            $where = "  AND (koc.NoParteEquipo = '$noParte') ";                            
                        }else if($componente->getRegistroById($noParte)){
                            $serie = "++";
                            $where = "  AND (koc.NoParteComponente = '$noParte') ";
                        }else{
                            if($noParte != ""){
                                array_push($componentes_no_existentes_almacen, $noParte);
                                $where = " AND (koc.NoParteComponente = '$noParte' OR koc.NoParteEquipo = '$noParte') ";
                            }
                        }
                        
                        $consulta = "SELECT koc.IdDetalleOC,
                            (SELECT CASE WHEN koc.NoParteComponente IS NOT NULL THEN 'C' ELSE 'E' END) AS tipo 
                            FROM k_orden_compra koc 
                            LEFT JOIN c_equipo e ON koc.NoParteEquipo=e.NoParte 
                            LEFT JOIN c_componente c ON koc.NoParteComponente=c.NoParte WHERE koc.IdOrdenCompra = '$id_compra' 
                            $where;";                        
                        $result = $catalogo->obtenerLista($consulta);
                        if(mysql_num_rows($result) > 0){
                            while($rs = mysql_fetch_array($result)){
                                $idDetalle = $rs['IdDetalleOC'];
                            }                            
                            array_push($ids_componentes, urlencode(($idDetalle)));
                            array_push($arrayUbicacion, urlencode(("")));                             
                            array_push($arraySerie, urlencode($serie)); 
                        }else{
                            if($noParte != ""){
                                array_push($componentes_no_existentes, $noParte);
                            }
                        }                        
                        $procesar_fila = false;   
                    }
                    if($procesar_fila && $c == 3){
                        $noParte = $datos[$c];                                                                                             
                    }                    
                }
            }
            fclose($gestor);
            
            if(!empty($componentes_no_existentes)){
                echo "<br/>Error: los siguientes No. de partes no se encuentran registrados en el pedido: ";
                foreach ($componentes_no_existentes as $value) {
                    echo "<br/>$value";
                }
                return false;
            }else if(!empty ($componentes_no_existentes_almacen)){
                echo "<br/>Error: los siguientes No. de partes no se encuentran registrados en el sistema: ";
                foreach ($componentes_no_existentes_almacen as $value) {
                    echo "<br/>$value";
                }
                return false;
            }
            
            $parametros = new Parametros();
            if($parametros->getRegistroById("8")){
                $liga = $parametros->getDescripcion()."WEB-INF/Controllers/compras/Controler_Entrada_Orden_Compra.php";
            }else{
                $liga = "http://genesis2.techra.com.mx/genesis2/WEB-INF/Controllers/compras/Controler_Entrada_Orden_Compra.php";
            }
            
            //extract data from the post
            extract($_POST);
            //set POST variables
            $url = $liga;
            $fields = array(
                'arrayIdDetalle' => ($ids_componentes),
                'arrayCantidad' => ($arrayCantidad),                                        
                'arrayUbicacion' => ($arrayUbicacion),
                'arrayNoSerie' => ($arraySerie),
                'almacen' => urlencode($almacen),
                'estatus' => urlencode($estatus) ,                
                'estadoOC' => urlencode($estadoOC),
                'folio' => urlencode($folio_factura),
                'sistema_autorizado' => urlencode("true"),
                'user' => $usuario,
                'empresa' => $empresa
            );
            /*foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            rtrim($fields_string, '&');*/
            $fields_string = http_build_query($fields);
            //open connection
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

            //execute post
            if(!curl_exec($ch)){
                return false;
            }else{
                return true;
            }
            //echo $result_post;
            //close connection
            curl_close($ch);
        }
        return false;
    }

    public function cargarCSVOC($archivo, $id_compra, $usuario, $pantalla) {
        $usuario = $_SESSION['user'];
        $pantalla = "LeerSCV_OC";
        $fila = 1;
        $detalles = array();
        $partes_no_existentes = array();
        if (($gestor = fopen($archivo, "r")) !== FALSE) {
            while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                $tiene_registro = false;
                $numero_orden = false;
                $numero_oc = false;
                $numero = count($datos);
                
                if($datos[0] != "" && strpos($datos[0],' ') === false){
                    $no_parte = "";
                    $tiene_registro = true;
                    $detalle_oc = new Detalle_Orden_Compra();
                    $equipo = new Equipo();
                    $componente = new Componente();
                    $detalle_oc->setIdOrdenCompra($id_compra);
                    $detalle_oc->setDolar(0);
                    $detalle_oc->setUsuarioCreacion($usuario);
                    $detalle_oc->setUsuarioModificacion($usuario);
                    $detalle_oc->setPantalla($pantalla);
                    $tiene_promocion = true; //Cuando tiene promocion, los datos de cantidad y precio vienen 2 campos adelante
                }$fila++;
                
                for ($c = 0; $c < $numero; $c++) {                    
                    if($tiene_registro){//Si es una fila con no. parte                        
                        switch ($c){
                            case 0:
                                $no_parte = $datos[$c];
                                break;
                            case 3:
                                if($datos[$c] != ""){
                                    $detalle_oc->setCantidad($datos[$c]);
                                    $tiene_promocion = false;
                                }
                                break;
                            case 5:
                                if($tiene_promocion){
                                    $detalle_oc->setCantidad($datos[$c]);                                
                                }else{
                                    $detalle_oc->setPrecioUnitario(str_replace(",", "", substr($datos[$c], 1)));
                                }
                                break;
                            case 6:
                                if($tiene_promocion){
                                    $detalle_oc->setCostoTotal(str_replace(",", "", substr($datos[$c], 1)));
                                }
                                break;
                            case 7:
                                if($tiene_promocion){
                                    $detalle_oc->setPrecioUnitario(str_replace(",", "", substr($datos[$c], 1)));
                                }
                                break;
                            case 8:
                                if($tiene_promocion){
                                    $detalle_oc->setCostoTotal(str_replace(",", "", substr($datos[$c], 1)));
                                }
                                break;
                            default:
                                break;
                        }                        
                    }else if($numero_orden){                     
                        $detalle_oc1 = new Detalle_Orden_Compra();
                        $detalle_oc1->actualizarProforma($id_compra, $datos[$c], $usuario, $pantalla);                        
                        $numero_orden = false;
                    }else if($numero_oc){
                        $detalle_oc1 = new Detalle_Orden_Compra();
                        $detalle_oc1->actualizarNumeroOC($id_compra, $datos[$c], $usuario, $pantalla);
                        $numero_oc = false;
                    }else{                        
                        if($datos[$c] != "" && strpos($datos[$c],'mer de orden') !== false){                            
                            $numero_orden = true;
                        }
                        if($datos[$c] != "" && strpos($datos[$c],'mero de O/C:') !== false){                            
                            $numero_oc = true;
                        }
                    }
                }//Fin for, recorre columna de cada fila
                
                if($tiene_registro){
                    /*Verificamos si el registro es de un equipo o de un componente*/
                    if($equipo->getRegistroById($no_parte)){
                        $detalle_oc->setNoParteEquipo($no_parte);
                        $detalle_oc->setTipo(0);
                        //array_push($detalles, $detalle_oc);                        
                        if(isset($detalles[$no_parte])){
                            $detalle_aux = $detalles[$no_parte];
                            $detalle_oc->setCantidad($detalle_aux->getCantidad() + $detalle_oc->getCantidad());
                            $detalle_oc->setCostoTotal($detalle_aux->getCostoTotal() + $detalle_oc->getCostoTotal());
                        }                        
                        $detalles[$no_parte] = $detalle_oc;
                    }else if($componente->getRegistroById($no_parte)){//Buscamos si es un componente
                        $detalle_oc->setNoParteComponente($no_parte);
                        $detalle_oc->setTipo(1);
                        //array_push($detalles, $detalle_oc);
                        if(isset($detalles[$no_parte])){
                            $detalle_aux = $detalles[$no_parte];
                            $detalle_oc->setCantidad($detalle_aux->getCantidad() + $detalle_oc->getCantidad());
                            $detalle_oc->setCostoTotal($detalle_aux->getCostoTotal() + $detalle_oc->getCostoTotal());
                        }                        
                        $detalles[$no_parte] = $detalle_oc;
                    }else if($componente->getRegistroByParteAnterior($no_parte)){//Buscamos si hay componentes con no de partes anteriores con esta parte
                        $no_parte = $componente->getNumero();
                        $detalle_oc->setNoParteComponente($no_parte);
                        $detalle_oc->setTipo(1);
                        //array_push($detalles, $detalle_oc);
                        if(isset($detalles[$no_parte])){
                            $detalle_aux = $detalles[$no_parte];
                            $detalle_oc->setCantidad($detalle_aux->getCantidad() + $detalle_oc->getCantidad());
                            $detalle_oc->setCostoTotal($detalle_aux->getCostoTotal() + $detalle_oc->getCostoTotal());
                        }                        
                        $detalles[$no_parte] = $detalle_oc;
                    }else{                        
                        array_push($partes_no_existentes, $no_parte);                        
                    }
                }                
            }//Fin while, recorre las filas del documento            
            fclose($gestor);
            
            if(empty($partes_no_existentes) && !empty($detalles)){//Si todos los No. Partes están registrados
                $detalle_oc = new Detalle_Orden_Compra();
                $todo_exito = true;
                foreach ($detalles as $value) {
                    $detalle_oc = $value;
                    
                    if($detalle_oc->getTipo() == 0){
                        if(!$detalle_oc->verificarExistenciaEquipo()){                        
                            if(!$detalle_oc->newRegistroEquipo()){
                                $todo_exito = false;
                            }
                        }else{
                            $todo_exito = false;
                        }
                    }else{
                        if(!$detalle_oc->verificarExistencia()){                        
                            if(!$detalle_oc->newRegistroCompnente()){                                
                                $todo_exito = false;
                            }
                        }else{
                            $todo_exito = false;
                        }
                    }
                }
                
                if($todo_exito){
                    return true;
                }
                return false;                
            }else if(empty ($detalles)){
                echo "<br/><br/>Error: no se encontraron No. de partes en el archivo csv para registrar (guarde y vuelva a editar esta orden de compra [# $id_compra] para importar)";
                return false;
            }else{
                echo "<br/>Error: los siguientes No. de partes no se encuentran registrados (registrelos, guarde y vuelva a editar esta orden de compra [# $id_compra] para importar): ";
                foreach ($partes_no_existentes as $value) {
                    echo "<br/>$value";
                }
                return false;
            }
        }//Fin if, si se lee el archivo correctamente.
        return false;
    }
}

?>

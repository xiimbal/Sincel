<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Log.class.php");
include_once("../../Classes/AlmacenConmponente.class.php");

if (isset($_POST['num'])) {
    $numero = $_POST['num'];
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
        $id_solicitud = $parametros['solicitud'];
        $catalogo = new Catalogo();
        $pantalla = "Controller_Autorizar_Solicitud";
        if($parametros['autorizar'] == "3" || $parametros['autorizar']=="4"){            
            /*Desasociamos las series de la bitacora y quitamos lo apartado*/
            $catalogo->obtenerLista("UPDATE k_almacenequipo SET Apartado = 0 WHERE NoSerie IN (SELECT NoSerie FROM c_bitacora WHERE id_solicitud = $id_solicitud);");
            $catalogo->obtenerLista("UPDATE `c_bitacora` SET id_solicitud = NULL WHERE id_solicitud = $id_solicitud;");
            /*Descontamos componentes apartados y lo eliminaos de la solicitud*/                        
            $result = $catalogo->obtenerLista("SELECT (CASE WHEN !ISNULL(ks.cantidad_autorizada) THEN ks.cantidad_autorizada ELSE 0 END) AS Cantidad, 
                ksa.IdAlmacen, ksa.NoParte
                FROM `k_solicitud_asignado` AS ksa 
                INNER JOIN k_solicitud AS ks ON ks.id_solicitud = ksa.id_solicitud AND ks.id_partida = ksa.id_partida AND ks.NoSurtir = 0
                WHERE ksa.id_solicitud = $id_solicitud;");
            while($rs = mysql_fetch_array($result)){                
                $cantidad = $rs['Cantidad'];                
                /*Verificamos que no entren existencias negativas*/
                
                $almacenComponente = new AlmacenComponente();
                if($almacenComponente->getRegistroById($rs['NoParte'], $rs['IdAlmacen'])){
                    if($cantidad > $almacenComponente->getApartados()){
                        $log = new Log();
                        $log->setConsulta("Intento de registrar existencias negativas ($cantidad)");
                        $log->setSeccion($pantalla);
                        $log->setIdUsuario($_SESSION['idUsuario']);
                        $log->setTipo("Incidencia sistema");
                        $log->newRegistro();
                        $cantidad = $almacenComponente->getApartados();
                    }
                }
                $catalogo->obtenerLista("UPDATE k_almacencomponente SET cantidad_apartados = cantidad_apartados - ".$cantidad.", 
                    cantidad_existencia = cantidad_existencia + ".$rs['Cantidad'].", FechaUltimaModificacion = NOW(), 
                    UsuarioUltimaModificacion = '".$_SESSION['user']."',Pantalla = '$pantalla' 
                    WHERE NoParte = '".$rs['NoParte']."' AND id_almacen = ".$rs['IdAlmacen'].";");
            }
            $catalogo->obtenerLista("DELETE FROM `k_solicitud_asignado` WHERE id_solicitud = $id_solicitud;");            
            $msj = "La solicitud $id_solicitud fue marcada como cancelada o rechazada (si ya había equipos enviados, es necesario crear una solicitud de retiro).";
        }else{
            for ($i = 1; $i < $numero; $i++) {
                    $query2 = $catalogo->obtenerLista("UPDATE k_solicitud SET cantidad_autorizada='".$parametros['cantidadA'.$i]."',
                        UsuarioUltimaModificacion='".$_SESSION['user']."',FechaUltimaModificacion=NOW(), Pantalla = '$pantalla' 
                        WHERE k_solicitud.id_solicitud='".$id_solicitud."' AND k_solicitud.id_partida='".$parametros['partida'.$i]."'");
            }            
            $msj = "La solicitud $id_solicitud fue aceptada correctamente";
        }
        $query2 = $catalogo->insertarRegistro("UPDATE c_solicitud SET estatus='".$parametros['autorizar']."',id_autoriza='".$_SESSION['idUsuario']."',comentario='".$parametros['comentarios']."' WHERE c_solicitud.id_solicitud='".$id_solicitud."'");
        if($query2==null){
            echo $msj;
        }else{
            echo "<br/>Ocurrió un error al cambiar el estatus de la solicitud $id_solicitud";
        }
    }
}
?>

<?php

include_once ("Log.class.php");
include_once ("Catalogo.class.php");

class AlmacenComponente {

    private $noParte;
    private $idAlmacen;
    private $existencia;
    private $apartados;
    private $minimo;
    private $maximo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $ubicacion;
    private $IdOrdenCompra;
    private $tipoAlmacen;
    private $arreglo_php = array();
    private $arreglo_php2 = array();
    private $arreglo_php3 = array();
    private $modeloComp;
    private $cantidadSalida;
    private $arrayNoParte = array();
    private $arrayExistente = array();
    private $arrayMaxima = array();
    private $arrayMinima = array();
    private $arrayApartados = array();
    private $arrayModelo = array();
    private $arrayDescripcion = array();
    private $nombreAlamcen;
    private $tipoComponente;
    
    private $empresa;

    public function getRegistroById($id, $id2) {
        $consulta = ("SELECT * FROM k_almacencomponente ac,
            c_componente c WHERE ac.NoParte='" . $id . "' AND ac.id_almacen='" . $id2 . "' 
            AND ac.NoParte=c.NoParte");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->noParte = $rs['NoParte'];
            $this->idAlmacen = $rs['id_almacen'];
            $this->existencia = $rs['cantidad_existencia'];
            $this->apartados = $rs['cantidad_apartados'];
            $this->minimo = $rs['CantidadMinima'];
            $this->maximo = $rs['CantidadMaxima'];
            $this->tipoComponente = $rs['IdTipoComponente'];
            $this->ubicacion = $rs['Ubicacion'];      
            $this->IdOrdenCompra = $rs['IdOrdenCompra'];
            return true;
        }
        return false;
    }

    public function verificarExistenciaAlmacen($id, $id2) {
        $consulta = ("SELECT cantidad_existencia FROM k_almacencomponente WHERE NoParte='" . $id . "' AND id_almacen='" . $id2 . "' AND cantidad_existencia>=0");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            if($rs = mysql_fetch_array($query)){
                $this->existencia = $rs['cantidad_existencia'];
            }
            return TRUE;
        }
        return FALSE;
    }

    public function newRegistro() {
        if ($this->ubicacion == "") {
            $ubicacionAlmacen = "NULL";
        } else {
            $ubicacionAlmacen = "'$this->ubicacion'";
        }
        
        if(!isset($this->IdOrdenCompra) || empty($this->IdOrdenCompra)){
            $this->IdOrdenCompra = "NULL";
        }
        /* Verificamos que no entren existencias negativas */
        if ($this->existencia < 0) {
            $log = new Log();
            $log->setConsulta("Intento de registrar existencias negativas ($this->existencia)");
            $log->setSeccion($this->pantalla);
            $log->setIdUsuario($_SESSION['idUsuario']);
            $log->setTipo("Incidencia sistema");
            $log->newRegistro();
            $this->existencia = 0;
        }

        /* Verificamos que no entren apartados negativos */
        if ($this->apartados < 0) {
            $log = new Log();
            $log->setConsulta("Intento de registrar apartados negativos ($this->apartados)");
            $log->setSeccion($this->pantalla);
            $log->setIdUsuario($_SESSION['idUsuario']);
            $log->setTipo("Incidencia sistema");
            $log->newRegistro();
            $this->apartados = 0;
        }                
        
        $consulta = ("INSERT INTO k_almacencomponente(NoParte,id_almacen,cantidad_existencia,cantidad_apartados,CantidadMinima,CantidadMaxima,
            Ubicacion,IdOrdenCompra,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->noParte . "','" . $this->idAlmacen . "','" . $this->existencia . "','" . $this->apartados . "','" . $this->minimo . "',"
                . "'" . $this->maximo . "',$ubicacionAlmacen,$this->IdOrdenCompra,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" 
                . $this->pantalla . "')");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        if ($this->ubicacion == "") {
            $ubicacionAlmacen = "NULL";
        } else {
            $ubicacionAlmacen = "'$this->ubicacion'";
        }
        /* Verificamos que no entren existencias negativas */
        if ($this->existencia < 0) {
            $log = new Log();
            $log->setConsulta("Intento de registrar existencias negativas ($this->existencia)");
            $log->setSeccion($this->pantalla);
            $log->setIdUsuario($_SESSION['idUsuario']);
            $log->setTipo("Incidencia sistema");
            $log->newRegistro();
            $this->existencia = 0;
        }
        
        /* Verificamos que no entren apartados negativos */
        if ($this->apartados < 0) {
            $log = new Log();
            $log->setConsulta("Intento de registrar apartados negativos ($this->apartados)");
            $log->setSeccion($this->pantalla);
            $log->setIdUsuario($_SESSION['idUsuario']);
            $log->setTipo("Incidencia sistema");
            $log->newRegistro();
            $this->apartados = 0;
        }        
        
        $consulta = ("UPDATE k_almacencomponente SET cantidad_existencia = '" . $this->existencia . "',cantidad_apartados = '" . $this->apartados . "',"
                . "CantidadMinima='" . $this->minimo . "',CantidadMaxima='" . $this->maximo . "',Ubicacion=$ubicacionAlmacen,"
                . "UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' "
                . "WHERE NoParte='" . $this->noParte . "' AND id_almacen='" . $this->idAlmacen . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM k_almacencomponente WHERE NoParte = '" . $this->noParte . "' AND id_almacen='" . $this->idAlmacen . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getTipoAlmacenById() {
        $consulta = ("SELECT * FROM c_almacen a WHERE a.id_almacen='" . $this->idAlmacen . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->tipoAlmacen = $rs['TipoAlmacen'];
        }
        return $query;
    }

    public function serchNoSerie($consulta) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) == 0)
            array_push($this->arreglo_php, "No hay datos");
        else {
            $countador = 0;
            while ($palabras = mysql_fetch_array($query)) {
                $this->arreglo_php[$countador] = $palabras["Modelo"];
                $this->arreglo_php2[$countador] = $palabras["NoParte"];
                $this->arreglo_php3[$countador] = $palabras["Descripcion"];
                $countador++;
            }
        }
    }

    function GetModeloComponente($noParte) {
        $consulta = ("SELECT * FROM c_componente c WHERE c.NoParte='" . $noParte . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->modeloComp = $rs['Modelo'];
        }
        return $query;
    }

    public function editarCantidadAlmacen() {
        /* Verificamos que no entren existencias negativas */
        if ($this->getRegistroById($this->noParte, $this->idAlmacen)) {
            if ($this->cantidadSalida > $this->existencia) {
                $log = new Log();
                $log->setConsulta("Intento de registrar existencias negativas ($this->cantidadSalida)");
                $log->setSeccion($this->pantalla);
                $log->setIdUsuario($_SESSION['idUsuario']);
                $log->setTipo("Incidencia sistema");
                $log->newRegistro();
                $this->cantidadSalida = $this->existencia;
            }
        }

        $consulta = ("UPDATE k_almacencomponente SET cantidad_existencia = cantidad_existencia - $this->cantidadSalida, 
            UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' 
            WHERE id_almacen='" . $this->idAlmacen . "' AND NoParte='" . $this->noParte . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function TonerExistentesAlamcen() {
        $consulta = ("SELECT cantidad_existencia FROM  k_almacencomponente WHERE id_almacen='" . $this->idAlmacen . "' AND NoParte='" . $this->noParte . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            return $rs['cantidad_existencia'];
        }
    }

    public function editarCantidadAlmacenReusrtir() {
        $consulta = ("UPDATE k_almacencomponente SET cantidad_existencia = cantidad_existencia + $this->cantidadSalida,
            UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = NOW(),
                Pantalla = '" . $this->pantalla . "' WHERE id_almacen='" . $this->idAlmacen . "' AND NoParte='" . $this->noParte . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editarResurtidoReporte($almacen, $noParte) {
        $consulta = ("UPDATE k_resurtidotoner SET Surtido =1,UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdAlmacen='" . $almacen . "' AND NoComponenteToner='$noParte' AND Surtido=0;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getComponentesAlmacen($id) {
        $consulta = ("SELECT ka.NoParte,ka.id_almacen,ka.cantidad_apartados,ka.cantidad_existencia,ka.CantidadMinima,ka.CantidadMaxima,c.Modelo,c.Descripcion,a.nombre_almacen,c.IdColor 
                        FROM k_almacencomponente ka 
                        INNER JOIN c_componente c ON c.NoParte=ka.NoParte
                        INNER JOIN c_almacen a ON ka.id_almacen=a.id_almacen
                        WHERE ka.id_almacen='$id' AND ka.cantidad_existencia < ka.CantidadMaxima");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            $this->arrayNoParte[$contador] = $rs['NoParte'];
            $this->arrayExistente[$contador] = $rs['cantidad_existencia'];
            $this->arrayApartados[$contador] = $rs['cantidad_apartados'];
            $this->arrayMaxima[$contador] = $rs['CantidadMaxima'];
            $this->arrayMinima[$contador] = $rs['CantidadMinima'];
            $this->arrayModelo[$contador] = $rs['Modelo'];
            $this->arrayDescripcion[$contador] = $rs['IdColor'];
            $this->nombreAlamcen = $rs['nombre_almacen'];
            $contador++;
        }
        return $query;
    }

    public function verificarComponenteAlmacen() {
        $consulta = ("SELECT * FROM k_almacencomponente WHERE NoParte='" . $this->noParte . "' AND id_almacen='" . $this->idAlmacen . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            return TRUE;
        }
        return FALSE;
    }    
    
    public function verificarResurtirAlmacenBySolicitud(){
        /* Checamos que no haya solicitudes de esté mismo almacén que no estén completadas,
        *  en caso de que haya alguna, vamos a obtener de cantidad o cantidad_autorizada los que se supone deben
        *  de surtirse para este componente, los sumaremos a la cantidad existente de esté almacen
        */  
        if(!empty($this->idAlmacen) && !empty($this->noParte) && !empty($this->idAlmacen)){
            $pedidosAnteriores = 0;
            $consulta = "SELECT
                        (CASE WHEN !ISNULL(ks.cantidad_autorizada) THEN ks.cantidad_autorizada ELSE ks.cantidad END) AS cantidadT,
                        cantidad_surtida
                        FROM c_solicitud s
                        LEFT JOIN k_solicitud AS ks ON ks.id_solicitud = c.id_solicitud
                        WHERE c.id_almacen = $this->idAlmacen AND ks.Modelo = '$this->noParte' AND c.estatus IN (0,1,2)";
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            $result = $catalogo->obtenerLista($consulta);
            while($rs = mysql_fetch_array($result)){
                $pedidosAnteriores += (int)$rs['cantidadT'] - (int)$rs['cantidad_surtida'];
            }

            $consulta = ("SELECT * FROM k_almacencomponente ac,
                c_componente c WHERE ac.NoParte='" . $this->noParte . "' AND ac.id_almacen='" . $this->idAlmacen . "' 
                AND ac.NoParte=c.NoParte");
            $query = $catalogo->obtenerLista($consulta);
            if ($rs = mysql_fetch_array($query)) {
                $this->noParte = $rs['NoParte'];
                $this->idAlmacen = $rs['id_almacen'];
                $this->existencia = $rs['cantidad_existencia'];
                $this->apartados = $rs['cantidad_apartados'];
                $this->minimo = $rs['CantidadMinima'];
                $this->maximo = $rs['CantidadMaxima'];
                $this->tipoComponente = $rs['IdTipoComponente'];
                $this->ubicacion = $rs['Ubicacion'];            
            }

            $cantidadExistente = $this->existencia + $pedidosAnteriores;
            $cantidadMinima = $this->minimo;
            $cantidadMaxima = $this->maximo;

            if ((int) $cantidadExistente < (int) $cantidadMinima) {
                return true;
            }
            return false;
        }else{
            return false;
        }
    }
    
    public function getNoParte() {
        return $this->noParte;
    }

    public function setNoParte($noParte) {
        $this->noParte = $noParte;
    }

    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    public function getExistencia() {
        return $this->existencia;
    }

    public function setExistencia($existencia) {
        $this->existencia = $existencia;
    }

    public function getApartados() {
        return $this->apartados;
    }

    public function setApartados($apartados) {
        $this->apartados = $apartados;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    public function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    public function getMinimo() {
        return $this->minimo;
    }

    public function setMinimo($minimo) {
        $this->minimo = $minimo;
    }

    public function getMaximo() {
        return $this->maximo;
    }

    public function setMaximo($maximo) {
        $this->maximo = $maximo;
    }

    public function getTipoAlmacen() {
        return $this->tipoAlmacen;
    }

    public function setTipoAlmacen($tipoAlmacen) {
        $this->tipoAlmacen = $tipoAlmacen;
    }

    public function getArreglo_php() {
        return $this->arreglo_php;
    }

    public function setArreglo_php($arreglo_php) {
        $this->arreglo_php = $arreglo_php;
    }

    public function getArreglo_php2() {
        return $this->arreglo_php2;
    }

    public function setArreglo_php2($arreglo_php2) {
        $this->arreglo_php2 = $arreglo_php2;
    }

    public function getModeloComp() {
        return $this->modeloComp;
    }

    public function setModeloComp($modeloComp) {
        $this->modeloComp = $modeloComp;
    }

    public function getCantidadSalida() {
        return $this->cantidadSalida;
    }

    public function setCantidadSalida($cantidadSalida) {
        $this->cantidadSalida = $cantidadSalida;
    }

    public function getArrayNoParte() {
        return $this->arrayNoParte;
    }

    public function getArrayExistente() {
        return $this->arrayExistente;
    }

    public function getArrayMaxima() {
        return $this->arrayMaxima;
    }

    public function setArrayNoParte($arrayNoParte) {
        $this->arrayNoParte = $arrayNoParte;
    }

    public function setArrayExistente($arrayExistente) {
        $this->arrayExistente = $arrayExistente;
    }

    public function setArrayMaxima($arrayMaxima) {
        $this->arrayMaxima = $arrayMaxima;
    }

    public function getArrayModelo() {
        return $this->arrayModelo;
    }

    public function setArrayModelo($arrayModelo) {
        $this->arrayModelo = $arrayModelo;
    }

    public function getArrayDescripcion() {
        return $this->arrayDescripcion;
    }

    public function setArrayDescripcion($arrayDescripcion) {
        $this->arrayDescripcion = $arrayDescripcion;
    }

    public function getNombreAlamcen() {
        return $this->nombreAlamcen;
    }

    public function setNombreAlamcen($nombreAlamcen) {
        $this->nombreAlamcen = $nombreAlamcen;
    }

    public function getArreglo_php3() {
        return $this->arreglo_php3;
    }

    public function setArreglo_php3($arreglo_php3) {
        $this->arreglo_php3 = $arreglo_php3;
    }

    public function getTipoComponente() {
        return $this->tipoComponente;
    }

    public function setTipoComponente($tipoComponente) {
        $this->tipoComponente = $tipoComponente;
    }

    public function getUbicacion() {
        return $this->ubicacion;
    }

    public function setUbicacion($ubicacion) {
        $this->ubicacion = $ubicacion;
    }
        
    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    function getArrayMinima() {
        return $this->arrayMinima;
    }

    function getArrayApartados() {
        return $this->arrayApartados;
    }

    function setArrayMinima($arrayMinima) {
        $this->arrayMinima = $arrayMinima;
    }

    function setArrayApartados($arrayApartados) {
        $this->arrayApartados = $arrayApartados;
    }
    
    function getIdOrdenCompra() {
        return $this->IdOrdenCompra;
    }

    function setIdOrdenCompra($IdOrdenCompra) {
        $this->IdOrdenCompra = $IdOrdenCompra;
    }

}

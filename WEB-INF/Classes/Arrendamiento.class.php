<?php

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");
include_once("ServicioIM.class.php");
include_once("ServicioGIM.class.php");
include_once("ServicioFA.class.php");

class Arrendamiento {

    private $idArrendamiento;
    private $idModalidad;
    private $nombre;
    private $tipo;
    private $renta;
    private $incluidoBN;
    private $incluidoColor;
    private $excedenteBN;
    private $excedenteColor;
    private $costoBN;
    private $costoColor;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getRegistroById($id, $id2) {
        $consulta = ("SELECT * FROM c_arrendamiento WHERE IdArrendamiento='" . $id . "' AND IdModalidad='" . $id2 . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->idArrendamiento = $rs['IdArrendamiento'];
            $this->idModalidad = $rs['IdModalidad'];
            $this->nombre = $rs['Nombre'];
            $this->tipo = $rs['Tipo'];
            $this->renta = $rs['rentaMensual'];
            $this->incluidoBN = $rs['IncluidoBN'];
            $this->incluidoColor = $rs['IncluidoColor'];
            $this->excedenteBN = $rs['ExcedentesBN'];
            $this->excedenteColor = $rs['ExcedentesColor'];
            $this->costoBN = $rs['CostoProcesadaBN'];
            $this->costoColor = $rs['CostoProcesadaColor'];
            $this->activo = $rs['Activo'];
        }
        return $query;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO c_arrendamiento(IdArrendamiento,IdModalidad,
            Nombre,Tipo,rentaMensual,IncluidoBN,IncluidoColor,ExcedentesBN,ExcedentesColor,CostoProcesadaBN,
            CostoProcesadaColor,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,
            FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->idModalidad . "','" . $this->nombre . "','" . $this->tipo . "','" . $this->renta . "',
                    '" . $this->incluidoBN . "','" . $this->incluidoColor . "','" . $this->excedenteBN . "','" . $this->excedenteColor . "',
                    '" . $this->costoBN . "','" . $this->costoColor . "','" . $this->activo . "','" . $this->usuarioCreacion . "',
                    now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "');");        
        $catalogo = new Catalogo(); $this->idArrendamiento = $catalogo->insertarRegistro($consulta);
        if ($this->idArrendamiento!= NULL && $this->idArrendamiento != 0) {                        
            return true;
        }
        
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_arrendamiento SET Nombre= '" . $this->nombre . "',Tipo= '" . $this->tipo . "',rentaMensual= '" . $this->renta . "',
            IncluidoBN= '" . $this->incluidoBN . "',IncluidoColor= '" . $this->incluidoColor . "',ExcedentesBN= '" . $this->excedenteBN . "',
            ExcedentesColor= '" . $this->excedenteColor . "',CostoProcesadaBN= '" . $this->costoBN . "',CostoProcesadaColor= '" . $this->costoColor . "',Activo= '" . $this->activo . "',
            UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'  
            WHERE IdArrendamiento='" . $this->idArrendamiento . "' AND IdModalidad ='" . $this->idModalidad . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM c_arrendamiento WHERE IdArrendamiento='" . $this->idArrendamiento . "' AND IdModalidad ='" . $this->idModalidad . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    /*
     * Obtenemos los servicios ligados al idArrendamiento de esta clase.
     * @return Regresa un arreglo con los id de servicios asociados a este arrendamiento.
     */

    public function getServiciosByArrendamiento() {
        $consulta = "SELECT IdServicioIM AS IdServicio FROM c_servicioim WHERE IdArrendamiento = $this->idArrendamiento AND Activo = 1
        UNION
        SELECT IdServicioGIM AS IdServicio FROM c_serviciogim WHERE IdArrendamiento = $this->idArrendamiento AND Activo = 1
        UNION
        SELECT IdServicioFA AS IdServicio FROM c_serviciofa WHERE IdArrendamiento = $this->idArrendamiento AND Activo = 1
        UNION 
        SELECT IdServicioGFA AS IdServicio FROM c_serviciogfa WHERE IdArrendamiento = $this->idArrendamiento AND Activo = 1;";
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        $servicios = array();
        while ($rs = mysql_fetch_array($query)) {
            array_push($servicios, $rs['IdServicio']);
        }
        
        return $servicios;
    }        
    
    /**
     * Actualiza los servicios recibidos con el arrendamiento actual de la clase.
     * @param type $servicios Array con el id de los servicios a actualizar.
     */
    public function actualizarServicios($servicios) {
        /* Una vez registrado el arrendamiento, asociamos los servicios para los que se ocupara este esquema */
        if (isset($servicios)) {
            foreach ($servicios as $selectedOption) {/* recorremos todos los servicios seleccionados */
                if (intval($selectedOption) < 100) { /* Si es el id de un servicio FA */
                    $servicio = new ServicioFA();
                    $servicio->actualizarArrendamiento($this->getIdArrendamiento(), $selectedOption);
                } else if (intval($selectedOption) < 1001) {/* si es el id de un servicio IM */
                    $servicio = new ServicioIM();
                    $servicio->actualizarArrendamiento($this->getIdArrendamiento(), $selectedOption);
                } else if (intval($selectedOption) < 1050) {/* Si es el id de un servicio FA */
                    $servicio = new ServicioFA();
                    $servicio->actualizarArrendamientoGFA($this->getIdArrendamiento(), $selectedOption);
                } else {/* Si es el id de un servicio GIM */
                    $servicio = new KServicioGIM();
                    $servicio->actualizarArrendamiento($this->getIdArrendamiento(), $selectedOption);
                }
            }
        }
    }

    public function getIdArrendamiento() {
        return $this->idArrendamiento;
    }

    public function setIdArrendamiento($idArrendamiento) {
        $this->idArrendamiento = $idArrendamiento;
    }

    public function getIdModalidad() {
        return $this->idModalidad;
    }

    public function setIdModalidad($idModalidad) {
        $this->idModalidad = $idModalidad;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function getRenta() {
        return $this->renta;
    }

    public function setRenta($renta) {
        $this->renta = $renta;
    }

    public function getIncluidoBN() {
        return $this->incluidoBN;
    }

    public function setIncluidoBN($incluidoBN) {
        $this->incluidoBN = $incluidoBN;
    }

    public function getIncluidoColor() {
        return $this->incluidoColor;
    }

    public function setIncluidoColor($incluidoColor) {
        $this->incluidoColor = $incluidoColor;
    }

    public function getExcedenteBN() {
        return $this->excedenteBN;
    }

    public function setExcedenteBN($excedenteBN) {
        $this->excedenteBN = $excedenteBN;
    }

    public function getExcedenteColor() {
        return $this->excedenteColor;
    }

    public function setExcedenteColor($excedenteColor) {
        $this->excedenteColor = $excedenteColor;
    }

    public function getCostoBN() {
        return $this->costoBN;
    }

    public function setCostoBN($costoBN) {
        $this->costoBN = $costoBN;
    }

    public function getCostoColor() {
        return $this->costoColor;
    }

    public function setCostoColor($costoColor) {
        $this->costoColor = $costoColor;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
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

}

?>

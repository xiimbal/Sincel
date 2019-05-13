<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");

class EquipoCaracteristicasFormatoServicio {

    private $nombreServicio;
    private $nombreFormato;
    private $idServicio;
    private $idFormato;
    private $caracteristica;
    private $noParte;
    private $formatoEquipo;
    private $servicioColor;
    private $servicioFax;
    private $idCaracteristica;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $idCaract;
    private $idServ;
    private $idForm;
    private $bn;
    private $empresa;

    public function getNombreFormato() {
        return $this->nombreFormato;
    }

    public function setNombreFormato($nombreFormato) {
        $this->nombreFormato = $nombreFormato;
    }

    /**
     * True en caso de que el equipo sea tipo Formato Amplio.
     * @param type $NoParte
     * @return boolean
     */
    public function isFormatoAmplio($NoParte) {
        $result2 = $this->getCaracteristicasByParte($NoParte);
        while ($rs_aux = mysql_fetch_array($result2)) {
            if ($rs_aux['IdCaracteristicaEquipo'] == "2") {
                return true;
            }
        }
        return false;
    }

    /**
     * True en caso de que el equipo sea de color.
     * @param type $NoParte
     * @return boolean
     */
    public function isColor($NoParte) {
        $result2 = $this->getTiposDeServicios($NoParte);
        while ($rs_aux = mysql_fetch_array($result2)) {
            if ($rs_aux['IdTipoServicio'] == "1") {
                return true;
            }
        }
        return false;
    }

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM k_equipocaracteristicaformatoservicio efs WHERE efs.NoParte='" . $id . "' ORDER BY efs.IdTipoServicio");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            $this->noParte = $rs['NoParte'];
            $this->formatoEquipo = $this->getNombre($rs['IdFormatoEquipo']);
            $this->idCaracteristica = $rs['IdCaracteristicaEquipo'];
            $contador++;
            if ($contador == 1) {
                if ($rs['IdTipoServicio'] == "2") {
                    $this->servicioFax = $this->getNombreServicio($rs['IdTipoServicio']);
                } else if ($rs['IdTipoServicio'] == "1") {
                    $this->servicioColor = $this->getNombreServicio($rs['IdTipoServicio']);
                } else if ($rs['IdTipoServicio'] == "3") {
                    $this->bn = $this->getNombreServicio($rs['IdTipoServicio']);
                }
            } else if ($contador == 2) {
                if ($rs['IdTipoServicio'] == "2") {
                    $this->servicioFax = $this->getNombreServicio($rs['IdTipoServicio']);
                } else if ($rs['IdTipoServicio'] == "3") {
                    $this->bn = $this->getNombreServicio($rs['IdTipoServicio']);
                } else if ($rs['IdTipoServicio'] == "1") {
                    $this->servicioColor = $this->getNombreServicio($rs['IdTipoServicio']);
                }
            } else if ($contador == 3) {
                $this->servicioFax = $this->getNombreServicio(2);
                $this->servicioColor = $this->getNombreServicio(1);
                $this->bn = "";
            }
        }
        return $query;
    }

    /**
     * Obtiene los tipos de servicio del No. de parte especificado
     * @param type $NoParte
     * @return type ResultSet con los Tipo de servicios
     */
    public function getTiposDeServicios($NoParte) {
        $consulta = "SELECT ts.Nombre AS servicio, ke.IdTipoServicio FROM `k_equipocaracteristicaformatoservicio` AS ke
        INNER JOIN c_tiposervicio AS ts ON ke.NoParte = '$NoParte' AND ts.IdTipoServicio = ke.IdTipoServicio;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    /**
     * Obtiene las caracteristicas del No. de parte especificado
     * @param type $NoParte
     * @return type ResultSet con las caracteristicas
     */
    public function getCaracteristicasByParte($NoParte) {
        $consulta = "SELECT * FROM `k_equipocaracteristicaformatoservicio` WHERE NoParte = '$NoParte';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getNombre($id) {
        $consulta = ("SELECT Nombre FROM c_formatoequipo WHERE IdFormatoEquipo='" . $id . "' ");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return $this->nombreFormato = $rs['Nombre'];
        }
        return false;
    }

    public function getNombreServicio($id) {
        $consulta = ("SELECT Nombre FROM c_tiposervicio WHERE IdTipoServicio='" . $id . "' ");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return $this->nombreServicio = $rs['Nombre'];
        }
        return false;
    }

    public function getServicio($tipo) {
        $consulta = ("SELECT IdTipoServicio FROM c_tiposervicio WHERE Nombre='" . $tipo . "' ");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return $this->idServicio = $rs['IdTipoServicio'];
        }
        return false;
    }

    public function getTipoFormato($tipo) {
        $consulta = ("SELECT IdFormatoEquipo FROM c_formatoequipo WHERE Nombre='" . $tipo . "' ");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return $this->idFormato = $rs['IdFormatoEquipo'];
        }
        return false;
    }

    public function getCaract($tipo) {
        $consulta = ("SELECT IdCaracteristicaEquipo FROM c_caracteristicaequipo WHERE Nombre='" . $tipo . "' ");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return $this->caracteristica = $rs['IdCaracteristicaEquipo'];
        }
        return false;
    }

    public function newRegistro() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        if ($this->servicioColor != "") {
            $query = $catalogo->obtenerLista("INSERT INTO k_equipocaracteristicaformatoservicio (NoParte,IdFormatoEquipo,IdTipoServicio,IdCaracteristicaEquipo,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                  VALUES('" . $this->noParte . "','" . $this->formatoEquipo . "','" . $this->getServicioColor() . "',
                                  '" . $this->idCaracteristica . "','" . $this->usuarioCreacion . "',now(),'$this->usuarioModificacion',now(),'$this->pantalla')");
        }
        if ($this->servicioFax != "") {
            $query = $catalogo->obtenerLista("INSERT INTO k_equipocaracteristicaformatoservicio (NoParte,IdFormatoEquipo,IdTipoServicio,IdCaracteristicaEquipo,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                  VALUES('" . $this->noParte . "','" . $this->formatoEquipo . "','" . $this->getServicioFax() . "',
                                  '" . $this->idCaracteristica . "','" . $this->usuarioCreacion . "',now(),'$this->usuarioModificacion',now(),'$this->pantalla')");
        }
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getBn() {
        return $this->bn;
    }

    public function setBn($bn) {
        $this->bn = $bn;
    }

    public function deleteRegistro() {
        $consulta = ("DELETE FROM k_equipocaracteristicaformatoservicio WHERE NoParte = '" . $this->noParte . "';");
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
        $this->deleteRegistro();
        $this->newRegistro();
        return True;
    }

    public function getIdCaract() {
        return $this->idCaract;
    }

    public function setIdCaract($idCaract) {
        $this->idCaract = $idCaract;
    }

    public function getIdServ() {
        return $this->idServ;
    }

    public function setIdServ($idServ) {
        $this->idServ = $idServ;
    }

    public function getIdForm() {
        return $this->idForm;
    }

    public function setIdForm($idForm) {
        $this->idForm = $idForm;
    }

    public function getIdServicio() {
        return $this->idServicio;
    }

    public function setIdServicio($idServicio) {
        $this->idServicio = $idServicio;
    }

    public function getIdFormato() {
        return $this->idFormato;
    }

    public function setIdFormato($idFormato) {
        $this->idFormato = $idFormato;
    }

    public function getCaracteristica() {
        return $this->caracteristica;
    }

    public function setCaracteristica($caracteristica) {
        $this->caracteristica = $caracteristica;
    }

    public function getNoParte() {
        return $this->noParte;
    }

    public function setNoParte($noParte) {
        $this->noParte = $noParte;
    }

    public function getFormatoEquipo() {
        return $this->formatoEquipo;
    }

    public function setFormatoEquipo($formatoEquipo) {
        $this->formatoEquipo = $formatoEquipo;
    }

    public function getServicioColor() {
        return $this->servicioColor;
    }

    public function setServicioColor($servicioColor) {
        $this->servicioColor = $servicioColor;
    }

    public function getServicioFax() {
        return $this->servicioFax;
    }

    public function setServicioFax($servicioFax) {
        $this->servicioFax = $servicioFax;
    }

    public function getIdCaracteristica() {
        return $this->idCaracteristica;
    }

    public function setIdCaracteristica($idCaracteristica) {
        $this->idCaracteristica = $idCaracteristica;
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

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>

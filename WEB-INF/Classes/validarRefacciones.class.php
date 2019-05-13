<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");

class ValidarRefaccion {

    private $idNotaTicket;
    private $IdTicket;
    private $DiagnosticoSol;
    private $IdEstatusAtencion;
    private $Activo;
    private $FechaHora;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getId($estatus) {
        $consulta = ("SELECT * FROM c_estado WHERE Nombre='" . $estatus . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdEstatusAtencion = $rs['IdEstado'];
        }
        return $this->IdEstatusAtencion;
    }

    public function CambiarEstatus() {
        $consulta = ("UPDATE c_notaticket SET IdEstatusAtencion = '" . $this->getId('Validar refacción') . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdNotaTicket='" . $this->idNotaTicket . "';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    /**
     * Obtiene un resultset con las refacciones que están marcadas como pendientes para proximo servicio que no han sido atendidas
     * @param type $noSerie
     * @return type
     */
    public function getRefaccionesPendientesServicio($noSerie){
        $consulta = "SELECT t.IdTicket, t.NoSerieEquipo, nt.IdEstatusAtencion, nr.IdNotaTicket ,nr.NoParteComponente, 
            CONCAT(c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS Modelo, nr.UsuarioCreacion  
            FROM c_ticket AS t
            LEFT JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
            LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
            LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
            WHERE t.NoSerieEquipo = '$noSerie' AND nt.IdEstatusAtencion = 81 AND !ISNULL(nr.IdNotaTicket) AND nr.Validada = 0 AND c.Activo = 1;";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    public function getIdNotaTicket() {
        return $this->idNotaTicket;
    }

    public function setIdNotaTicket($idNotaTicket) {
        $this->idNotaTicket = $idNotaTicket;
    }

    public function getIdTicket() {
        return $this->IdTicket;
    }

    public function setIdTicket($IdTicket) {
        $this->IdTicket = $IdTicket;
    }

    public function getDiagnosticoSol() {
        return $this->DiagnosticoSol;
    }

    public function setDiagnosticoSol($DiagnosticoSol) {
        $this->DiagnosticoSol = $DiagnosticoSol;
    }

    public function getIdEstatusAtencion() {
        return $this->IdEstatusAtencion;
    }

    public function setIdEstatusAtencion($IdEstatusAtencion) {
        $this->IdEstatusAtencion = $IdEstatusAtencion;
    }

    public function getActivo() {
        return $this->Activo;
    }

    public function setActivo($Activo) {
        $this->Activo = $Activo;
    }

    public function getFechaHora() {
        return $this->FechaHora;
    }

    public function setFechaHora($FechaHora) {
        $this->FechaHora = $FechaHora;
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

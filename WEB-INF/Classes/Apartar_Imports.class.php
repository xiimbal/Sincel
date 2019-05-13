<?php

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");

class Apartar_Imports {

    private $idApartado;
    private $idNota;
    private $noParte;
    private $idOrdenCompra;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function newRegistro() {
        $consulta = ("INSERT INTO k_importacion_orden_compra(IdOrden,IdNotaTicket,noParte,Solicitada,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                SELECT 0,nr.IdNotaTicket,nr.NoParteComponente,0,'$this->usuarioCreacion',now(),'$this->usuarioModificacion',now(),'$this->pantalla'
                                FROM c_componente c INNER JOIN k_nota_refaccion nr ON c.NoParte=nr.NoParteComponente INNER JOIN c_notaticket nt ON nr.IdNotaTicket=nt.IdNotaTicket
                                WHERE nt.IdEstatusAtencion=20 AND nr.Cantidad>0 AND nr.IdNotaTicket NOT IN (SELECT ki.IdNotaTicket FROM k_importacion_orden_compra ki  WHERE ki.IdOrdenCompra<>'' OR ki.IdOrdenCompra IS NOT NULL)
                                ORDER BY nr.IdNotaTicket DESC");        
        $catalogo = new Catalogo(); $this->idApartado = $catalogo->insertarRegistro($consulta);
        if ($this->idApartado!= NULL && $this->idApartado!=0) {
            return true;
        }
        return false;
    }

    public function deleteRegistro($idNota) {
        $consulta = ("DELETE FROM k_importacion_orden_compra WHERE IdNotaTicket IN ($idNota)");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editImportados($idNota) {
        $consulta = ("UPDATE k_importacion_orden_compra SET IdOrdenCompra='" . $this->idOrdenCompra . "'  WHERE IdNotaTicket IN ($idNota);");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function verificarInsert($idNota) {
        $consulta = "SELECT * FROM k_importacion_orden_compra ki WHERE ki.IdNotaTicket IN ($idNota) AND (ki.IdOrdenCompra='' OR ki.IdOrdenCompra IS NULL)";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            return true;
        }
        return false;
    }

    public function getIdApartado() {
        return $this->idApartado;
    }

    public function getIdNota() {
        return $this->idNota;
    }

    public function getNoParte() {
        return $this->noParte;
    }

    public function setIdApartado($idApartado) {
        $this->idApartado = $idApartado;
    }

    public function setIdNota($idNota) {
        $this->idNota = $idNota;
    }

    public function setNoParte($noParte) {
        $this->noParte = $noParte;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
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

    public function getIdOrdenCompra() {
        return $this->idOrdenCompra;
    }

    public function setIdOrdenCompra($idOrdenCompra) {
        $this->idOrdenCompra = $idOrdenCompra;
    }

}

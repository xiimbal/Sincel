<?php

include_once ("Catalogo.class.php");

/**
 * Description of AddendaDetalle
 *
 * @author MAGG
 */
class AddendaDetalle {

    private $id_kaddenda;
    private $id_addenda;
    private $campo;
    private $valor;
    private $dinamicos;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `k_addenda` WHERE id_addenda = $id;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id_kaddenda = $rs['id_kaddenda'];
            $this->id_addenda = $rs['id_addenda'];
            $this->campo = $rs['campo'];
            $this->valor = $rs['valor'];
            $this->dinamicos = $rs['dinamico'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }
    
    public function getRegistrosByAdenda($id){
        $consulta = ("SELECT * FROM `k_addenda` WHERE id_addenda = $id;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getRegistrosByCliente($claveCliente, $solo_dinamico){
        $where = ($solo_dinamico)?"AND ka.dinamico = 1":"";        
        $consulta = ("SELECT c.ClaveCliente, c.NombreRazonSocial, ka.id_kaddenda, ka.id_addenda, ka.campo, ka.valor, ka.dinamico
            FROM c_cliente AS c
            LEFT JOIN c_addenda AS ca ON ca.id_addenda = c.IdAddenda
            LEFT JOIN k_addenda AS ka ON ka.id_addenda = ca.id_addenda
            WHERE c.ClaveCliente = '$claveCliente' $where;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function newRegistro() {        
        $consulta = ("INSERT INTO k_addenda(id_kaddenda, id_addenda, campo, valor, dinamico, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) "
                . "VALUES(0, $this->id_addenda, '$this->campo', '$this->valor' ,$this->dinamicos,'$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla');");
        $catalogo = new Catalogo();
        $this->id_kaddenda = $catalogo->insertarRegistro($consulta);
        if ($this->id_kaddenda != NULL && $this->id_kaddenda != 0) {
            return true;
        }
        return false;
    }

    public function editRegistro() {        
        $consulta = ("UPDATE k_addenda SET id_addenda = $this->id_addenda, campo = '$this->campo', valor = '$this->valor',dinamico = $this->dinamicos,"
                . "UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla' "
                . "WHERE id_kaddenda = " . $this->id_kaddenda . ";");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistros() {
        $consulta = "DELETE FROM `k_addenda` WHERE id_addenda = $this->id_addenda;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function getId_kaddenda() {
        return $this->id_kaddenda;
    }

    public function getId_addenda() {
        return $this->id_addenda;
    }

    public function getCampo() {
        return $this->campo;
    }

    public function getValor() {
        return $this->valor;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setId_kaddenda($id_kaddenda) {
        $this->id_kaddenda = $id_kaddenda;
    }

    public function setId_addenda($id_addenda) {
        $this->id_addenda = $id_addenda;
    }

    public function setCampo($campo) {
        $this->campo = $campo;
    }

    public function setValor($valor) {
        $this->valor = $valor;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    function getDinamicos() {
        return $this->dinamicos;
    }

    function setDinamicos($dinamicos) {
        $this->dinamicos = $dinamicos;
    }


}

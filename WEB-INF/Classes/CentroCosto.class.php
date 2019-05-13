<?php

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");
include_once("ParametroGlobal.class.php");

class CentroCosto {

    private $ClaveCentroCosto;
    private $ClaveZona;
    private $ClaveCliente;
    private $Nombre;
    private $TipoDomicilioFiscal;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $Moroso = "0";
    private $empresa;

    public function getRegistroValidacion($claveCliente) {
        $consulta = ("SELECT * FROM `c_centrocosto` WHERE ClaveCliente = '$claveCliente' AND Activo = 1;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `c_centrocosto` WHERE ClaveCentroCosto = '$id';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->ClaveCentroCosto = $rs['ClaveCentroCosto'];
            $this->ClaveZona = $rs['ClaveZona'];
            $this->ClaveCliente = $rs['ClaveCliente'];
            $this->Nombre = $rs['Nombre'];
            $this->TipoDomicilioFiscal = $rs['TipoDomicilioFiscal'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->Moroso = $rs['Moroso'];
            return true;
        }
        return false;
    }

    /**
     * Obtiene la localidad con el nombre especificado de la clave del cliente recibida.
     * @param type $ClaveCliente Clave del cliente.
     * @param type $Nombre Nombre de la localidad
     * @return type ResultSet con la clave del centro de costo.
     */
    public function getCentroCostoByClienteYNombre($ClaveCliente, $Nombre) {
        $consulta = ("SELECT ClaveCentroCosto FROM c_centrocosto WHERE ClaveCliente = '$ClaveCliente' AND Nombre = TRIM('$Nombre');");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function newRegistro() {
        $consulta = "SELECT ClaveCentroCosto FROM `c_centrocosto` WHERE ClaveCliente = '$this->ClaveCliente' AND TRIM(Nombre) = '$this->Nombre';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query3 = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query3) == 0) {
            $query2 = $catalogo->obtenerLista("SELECT MAX(CAST(ClaveCentroCosto AS UNSIGNED)) AS maximo FROM `c_centrocosto`;");
            if ($rs = mysql_fetch_array($query2)) {
                $maximo = (int) $rs['maximo'];
                if ($maximo == "" || $maximo == 0) {
                    $maximo = 1;
                }
                $maximo++;
                if (isset($this->TipoDomicilioFiscal) && $this->TipoDomicilioFiscal != "") {
                    $tipoDomicilio = $this->TipoDomicilioFiscal;
                } else {
                    $tipoDomicilio = "null";
                }
                /* Obtenemos la zona que se pone por default segun los parametros globales */
                $parametro = new ParametroGlobal();
                if (isset($this->empresa)) {
                    $parametro->setEmpresa($this->empresa);
                }
                if ($parametro->getRegistroById("2")) {
                    $this->ClaveZona = $parametro->getValor();
                } else {
                    $this->ClaveZona = "Z06";
                }
                $consulta = "INSERT INTO c_centrocosto(ClaveCentroCosto, ClaveCliente, Nombre, Activo,UsuarioCreacion,FechaCreacion,
                    UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,ClaveZona,Moroso,TipoDomicilioFiscal)
                VALUES('$maximo','$this->ClaveCliente','$this->Nombre',$this->Activo,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),
                    '$this->Pantalla','$this->ClaveZona','" . $this->Moroso . "',$tipoDomicilio)";
                $query = $catalogo->obtenerLista($consulta);
                if ($query == 1) {
                    $this->ClaveCentroCosto = $maximo;
                    return true;
                }
            }
        } else {
            echo "Error: Este cliente ya tiene una localidad con el nombre $this->Nombre<br/>";
        }
        return false;
    }

    public function editRegistro() {
        if (isset($this->TipoDomicilioFiscal) && $this->TipoDomicilioFiscal != "") {
            $tipoDomicilio = $this->TipoDomicilioFiscal;
        } else {
            $tipoDomicilio = "null";
        }
        $consulta = ("UPDATE c_centrocosto SET ClaveCliente = '$this->ClaveCliente', Nombre = '$this->Nombre', Activo = $this->Activo, TipoDomicilioFiscal = $tipoDomicilio,
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla',Moroso='" . $this->Moroso . "'
            WHERE ClaveCentroCosto = '$this->ClaveCentroCosto';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getClaveCentroCosto() {
        return $this->ClaveCentroCosto;
    }

    public function setClaveCentroCosto($ClaveCentroCosto) {
        $this->ClaveCentroCosto = $ClaveCentroCosto;
    }

    public function getClaveZona() {
        return $this->ClaveZona;
    }

    public function setClaveZona($ClaveZona) {
        $this->ClaveZona = $ClaveZona;
    }

    public function getClaveCliente() {
        return $this->ClaveCliente;
    }

    public function setClaveCliente($ClaveCliente) {
        $this->ClaveCliente = $ClaveCliente;
    }

    public function getNombre() {
        return $this->Nombre;
    }

    public function setNombre($Nombre) {
        $this->Nombre = $Nombre;
    }

    public function getActivo() {
        return $this->Activo;
    }

    public function setActivo($Activo) {
        $this->Activo = $Activo;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    public function getMoroso() {
        return $this->Moroso;
    }

    public function setMoroso($Moroso) {
        $this->Moroso = $Moroso;
    }

    public function getTipoDomicilioFiscal() {
        return $this->TipoDomicilioFiscal;
    }

    public function setTipoDomicilioFiscal($TipoDomicilioFiscal) {
        $this->TipoDomicilioFiscal = $TipoDomicilioFiscal;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>

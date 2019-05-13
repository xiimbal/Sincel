<?php

include_once ("Catalogo.class.php");

class AlmacenComponenteTicket {
    
    private $IdTicket;
    private $noParte;
    private $idAlmacen;
    private $existencia;
    private $apartados;
    private $minimo;
    private $maximo;
    private $precio;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $empresa;
    private $NoEquiposBeneficiados;
    private $arrayNoParte = array();
    private $arrayExistente = array();
    private $arrayMaxima = array();
    private $arrayModelo = array();
    private $arrayDescripcion = array();
    private $arrayMinima = array();
    private $arrayApartados = array();
    
    function existeImagenConTicket(){
        $query = "SELECT * FROM k_almacencomponenteticket act WHERE act.IdTicket = $this->IdTicket";
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
    
    function newRegistros(){
        if(count($this->arrayNoParte) > 0){
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            
            for($i= 0; $i < count($this->arrayNoParte); $i++)
            {
                $equiposBeneficiados = 0;
                $obtenerEquiposBeneficiados = "SELECT COUNT(id_bitacora) AS NumEquiposCompatible FROM(
                    SELECT b.id_bitacora,
                    (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.NombreRazonSocial ELSE c.NombreRazonSocial END) AS NombreRazonSocial, 
                    (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.ClaveCliente ELSE c.ClaveCliente END) AS ClaveCliente, 
                    (CASE WHEN !ISNULL(cc2.ClaveCentroCosto) THEN cc2.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto,
                    b.NoSerie, CONCAT(e.Modelo,' / ',b.NoParte) AS NoParteCompuesta,
                    cmp.NoParte AS ParteComponente, cmp.Modelo AS ModeloComponente
                    FROM `c_bitacora` AS b
                    INNER JOIN c_equipo AS e ON b.NoParte = e.NoParte
                    INNER JOIN c_inventarioequipo AS cinv ON cinv.NoSerie = b.NoSerie
                    LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
                    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
                    LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
                    LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
                    LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
                    LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente
                    LEFT JOIN k_equipocomponentecompatible AS kecc ON kecc.NoParteEquipo = b.NoParte
                    LEFT JOIN c_componente AS cmp ON cmp.NoParte = kecc.NoParteComponente
                    WHERE cmp.NoParte = '".$this->arrayNoParte[$i]."'
                    GROUP BY b.id_bitacora HAVING ClaveCentroCosto IN(SELECT ClaveCentroCosto FROM k_minialmacenlocalidad WHERE IdAlmacen = $this->idAlmacen)
                    ) AS t_1";
                $resultEquipos = $catalogo->obtenerLista($obtenerEquiposBeneficiados);
                if($rsEquipos = mysql_fetch_array($resultEquipos)){
                    $equiposBeneficiados = $rsEquipos['NumEquiposCompatible'];
                }
                //Consultamos si es que ya tenemos registros de este NoParte, Ticket, Almacen
                $buscarRegistro = "SELECT * FROM k_almacencomponenteticket WHERE IdTicket = $this->IdTicket AND NoParte = '".$this->arrayNoParte[$i]."' AND id_almacen = $this->idAlmacen";
                $result = $catalogo->obtenerLista($buscarRegistro);
                if(mysql_num_rows($result) > 0){
                    $consulta = "UPDATE k_almacencomponenteticket SET cantidad_existente = ".$this->arrayExistente[$i]
                            .", cantidad_apartados = ".$this->arrayApartados[$i].", CantidadMinima = ".$this->arrayMinima[$i]
                            .", CantidadMaxima = ".$this->arrayMaxima[$i]." WHERE IdTicket = $this->IdTicket AND NoParte = '".$this->arrayNoParte[$i]."' AND id_almacen = $this->idAlmacen";
                }else{
                    $consulta = ("INSERT INTO k_almacencomponenteticket(IdTicket,NoParte,id_almacen,cantidad_existente,cantidad_apartados,CantidadMinima,CantidadMaxima,
                        NoEquiposBeneficiados, UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                        VALUES($this->IdTicket,'" . $this->arrayNoParte[$i] . "'," . $this->idAlmacen . "," . $this->arrayExistente[$i] . "," . $this->arrayApartados[$i] . "," . $this->arrayMinima[$i] . ","
                        . $this->arrayMaxima[$i] . ",$equiposBeneficiados ,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" 
                        . $this->pantalla . "')");
                }
                $query = $catalogo->obtenerLista($consulta);
                if ($query == 0) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
    
    function getEmpresa() {
        return $this->empresa;
    }

    function getArrayNoParte() {
        return $this->arrayNoParte;
    }

    function getArrayExistente() {
        return $this->arrayExistente;
    }

    function getArrayMaxima() {
        return $this->arrayMaxima;
    }

    function getArrayModelo() {
        return $this->arrayModelo;
    }

    function getArrayDescripcion() {
        return $this->arrayDescripcion;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    function setArrayNoParte($arrayNoParte) {
        $this->arrayNoParte = $arrayNoParte;
    }

    function setArrayExistente($arrayExistente) {
        $this->arrayExistente = $arrayExistente;
    }

    function setArrayMaxima($arrayMaxima) {
        $this->arrayMaxima = $arrayMaxima;
    }

    function setArrayModelo($arrayModelo) {
        $this->arrayModelo = $arrayModelo;
    }

    function setArrayDescripcion($arrayDescripcion) {
        $this->arrayDescripcion = $arrayDescripcion;
    }
    
    function getIdTicket() {
        return $this->IdTicket;
    }

    function getNoParte() {
        return $this->noParte;
    }

    function getIdAlmacen() {
        return $this->idAlmacen;
    }

    function getExistencia() {
        return $this->existencia;
    }

    function getApartados() {
        return $this->apartados;
    }

    function getMinimo() {
        return $this->minimo;
    }

    function getMaximo() {
        return $this->maximo;
    }

    function getPrecio() {
        return $this->precio;
    }

    function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    function getPantalla() {
        return $this->pantalla;
    }

    function setIdTicket($IdTicket) {
        $this->IdTicket = $IdTicket;
    }

    function setNoParte($noParte) {
        $this->noParte = $noParte;
    }

    function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    function setExistencia($existencia) {
        $this->existencia = $existencia;
    }

    function setApartados($apartados) {
        $this->apartados = $apartados;
    }

    function setMinimo($minimo) {
        $this->minimo = $minimo;
    }

    function setMaximo($maximo) {
        $this->maximo = $maximo;
    }

    function setPrecio($precio) {
        $this->precio = $precio;
    }

    function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;
    }

    function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    function getNoEquiposBeneficiados() {
        return $this->NoEquiposBeneficiados;
    }

    function setNoEquiposBeneficiados($NoEquiposBeneficiados) {
        $this->NoEquiposBeneficiados = $NoEquiposBeneficiados;
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
    
}

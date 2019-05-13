<?php

include_once("Catalogo.class.php");
include_once("Cliente.class.php");

/**
 * Description of Localidad
 *
 * @author MAGG
 */
class Localidad {

    private $IdDomicilio;
    private $ClaveEspecialDomicilio;
    private $IdTipoDomicilio;
    private $Calle;
    private $NoExterior;
    private $NoInterior;
    private $Colonia;
    private $Ciudad;
    private $Estado;
    private $Delegacion;
    private $Pais;
    private $CodigoPostal;
    private $ClaveZona;
    private $Latitud;
    private $longitud;
    private $Activo;
    private $Localidad;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;

    public function getRegistoValidacion($clave, $nombre) {
        $consulta = ("SELECT * FROM `c_domicilio` WHERE ClaveEspecialDomicilio = '$clave' AND Activo = 1;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getLocalidadById($id) {
        $consulta = ("SELECT * FROM `c_domicilio` WHERE IdDomicilio = '$id';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdDomicilio = $rs['IdDomicilio'];
            $this->ClaveEspecialDomicilio = $rs['ClaveEspecialDomicilio'];
            $this->IdTipoDomicilio = $rs['IdTipoDomicilio'];
            $this->Calle = $rs['Calle'];
            $this->NoExterior = $rs['NoExterior'];
            $this->NoInterior = $rs['NoInterior'];
            $this->Colonia = $rs['Colonia'];
            $this->Ciudad = $rs['Ciudad'];
            $this->Estado = $rs['Estado'];
            $this->Delegacion = $rs['Delegacion'];
            $this->Pais = $rs['Pais'];
            $this->CodigoPostal = $rs['CodigoPostal'];
            $this->ClaveZona = $rs['ClaveZona'];
            $this->Latitud = $rs['Latitud'];
            $this->longitud = $rs['Longitud'];
            $this->Activo = $rs['Activo'];
            $this->Localidad = $rs['Localidad'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    public function getLocalidadTicket($idTicket) {
        $consulta = ("SELECT * FROM `c_domicilioticket` WHERE IdTicket = $idTicket;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->ClaveZona = $rs['ClaveZona'];
            $this->Calle = $rs['Calle'];
            $this->NoExterior = $rs['NoExterior'];
            $this->NoInterior = $rs['NoInterior'];
            $this->Colonia = $rs['Colonia'];
            $this->Ciudad = $rs['Ciudad'];
            $this->Estado = $rs['Estado'];
            $this->Delegacion = $rs['Delegacion'];
            $this->Pais = $rs['Pais'];
            $this->CodigoPostal = $rs['CodigoPostal'];
            $this->Id_gzona = $rs['Id_gzona'];
            $this->Latitud = $rs['Latitud'];
            $this->longitud = $rs['Longitud'];
            $this->Activo = $rs['Activo'];
            $this->Localidad = $rs['Localidad'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            return true;
        }
        return false;
    }

    public function getLocalidadByClave($clave) {
        $consulta = ("SELECT * FROM `c_domicilio` WHERE ClaveEspecialDomicilio = '$clave' ORDER BY IdDomicilio;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdDomicilio = $rs['IdDomicilio'];
            $this->ClaveEspecialDomicilio = $rs['ClaveEspecialDomicilio'];
            $this->IdTipoDomicilio = $rs['IdTipoDomicilio'];
            $this->Calle = $rs['Calle'];
            $this->NoExterior = $rs['NoExterior'];
            $this->NoInterior = $rs['NoInterior'];
            $this->Colonia = $rs['Colonia'];
            $this->Ciudad = $rs['Ciudad'];
            $this->Estado = $rs['Estado'];
            $this->Delegacion = $rs['Delegacion'];
            $this->Pais = $rs['Pais'];
            $this->CodigoPostal = $rs['CodigoPostal'];
            $this->ClaveZona = $rs['ClaveZona'];
            $this->Latitud = $rs['Latitud'];
            $this->longitud = $rs['Longitud'];
            $this->Activo = $rs['Activo'];
            $this->Localidad = $rs['Localidad'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    public function getLocalidadByClaveTipo($clave, $tipo) {
        $consulta = ("SELECT * FROM `c_domicilio` WHERE ClaveEspecialDomicilio = '$clave' AND IdTipoDomicilio='$tipo';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdDomicilio = $rs['IdDomicilio'];
            $this->ClaveEspecialDomicilio = $rs['ClaveEspecialDomicilio'];
            $this->IdTipoDomicilio = $rs['IdTipoDomicilio'];
            $this->Calle = $rs['Calle'];
            $this->NoExterior = $rs['NoExterior'];
            $this->NoInterior = $rs['NoInterior'];
            $this->Colonia = $rs['Colonia'];
            $this->Ciudad = $rs['Ciudad'];
            $this->Estado = $rs['Estado'];
            $this->Delegacion = $rs['Delegacion'];
            $this->Pais = $rs['Pais'];
            $this->CodigoPostal = $rs['CodigoPostal'];
            $this->ClaveZona = $rs['ClaveZona'];
            $this->Latitud = $rs['Latitud'];
            $this->longitud = $rs['Longitud'];
            $this->Activo = $rs['Activo'];
            $this->Localidad = $rs['Localidad'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    /**
     * Nuevo registro de localidad.
     * @param type $tipo tipo de domicilio
     * @return boolean true si se inserto de manera correcta, false en caso contrario.
     */
    public function newRegistro($tipo) {
        if (isset($this->Localidad) && $this->Localidad != "") {
            $localidad = "'$this->Localidad'";
        } else {
            $localidad = "null";
        }

        if (!isset($this->Latitud) || $this->Latitud == "") {
            $this->Latitud = "null";
        }

        if (!isset($this->longitud) || $this->longitud == "") {
            $this->longitud = "null";
        }
        
        if(!isset($this->NoInterior) || $this->NoInterior == ""){
            $this->NoInterior = "0";
        }
        
        $zona = "NULL";
        if(isset($this->ClaveZona) && !empty($this->ClaveZona)){
            $zona = "'$this->ClaveZona'";
        }

        $consulta = "INSERT INTO c_domicilio(ClaveEspecialDomicilio,Calle,NoExterior,NoInterior,Colonia,Ciudad,
            Estado,ClaveZona,Delegacion,Pais,CodigoPostal,Localidad,Activo,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,
            FechaUltimaModificacion,Pantalla,IdTipoDomicilio,Latitud,Longitud) 
            VALUES('$this->ClaveEspecialDomicilio','$this->Calle','$this->NoExterior',
            '$this->NoInterior','$this->Colonia','$this->Ciudad','$this->Estado',$zona,'$this->Delegacion','$this->Pais',
            '$this->CodigoPostal',$localidad,$this->Activo,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),
            '$this->Pantalla',$tipo, $this->Latitud, $this->longitud)";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $this->IdDomicilio = $catalogo->insertarRegistro($consulta);
        if ($this->IdDomicilio != NULL && $this->IdDomicilio != 0) {            
            return true;
        }
        return false;
    }

    public function editRegistro() {
        if (isset($this->Localidad) && $this->Localidad != "") {
            $localidad = "'$this->Localidad'";
        } else {
            $localidad = "null";
        }

        $latitud = "";
        if (isset($this->Latitud) && !empty($this->Latitud)) {
            $latitud = "Latitud = $this->Latitud,";
        }

        $longitud = "";
        if (isset($this->longitud) && !empty($this->longitud)) {
            $longitud = "Longitud = $this->longitud,";
        }        
        
        if(!isset($this->NoInterior) || $this->NoInterior == ""){
            $this->NoInterior = "0";
        }
        
        $zona = "NULL";
        if(isset($this->ClaveZona) && !empty($this->ClaveZona)){
            $zona = "'$this->ClaveZona'";
        }
        
        $consulta = ("UPDATE c_domicilio SET ClaveEspecialDomicilio = '$this->ClaveEspecialDomicilio',ClaveZona=$zona, 
            Calle = '$this->Calle', NoExterior = '$this->NoExterior',NoInterior = '$this->NoInterior',Colonia = '$this->Colonia',            
            Ciudad = '$this->Ciudad', Estado = '$this->Estado', Delegacion = '$this->Delegacion', Pais = '$this->Pais',CodigoPostal= '$this->CodigoPostal',
            Localidad = $localidad, $latitud $longitud
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla'
            WHERE IdDomicilio = $this->IdDomicilio;");
        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
        $theta = floatval($lon1) - floatval($lon2);
        $dist = sin(deg2rad(floatval($lat1))) * sin(deg2rad(floatval($lat2))) + cos(deg2rad(floatval($lat1))) * cos(deg2rad(floatval($lat2))) * cos(deg2rad(floatval($theta)));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $km = ($miles * 1.609344);
        return $km;
    }
    
    public function getTodosDomiciliosCliente($categoria){
        if($categoria != ""){
            $where = "AND (c.IdGiro = $categoria OR kcc.IdGiro = $categoria)";
        }
        $consulta = "SELECT d.IdDomicilio, d.Latitud, d.Longitud 
            FROM `c_domicilio` AS d
            INNER JOIN c_cliente AS c ON d.ClaveEspecialDomicilio = c.ClaveCliente
            LEFT JOIN k_clientecategoria AS kcc ON kcc.ClaveCliente = c.ClaveCliente
            WHERE d.Activo = 1 $where AND !ISNULL(Latitud) AND !ISNULL(Longitud)
            GROUP BY c.ClaveCliente;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
        
    public function obtenerDomiciliosCercanos($latitud, $longitud, $categoria, $tipo_cliente, $indice, $registros_por_pagina, $radio, $id_ejecutivos, $tipo_usuarios, $id_usuario){
        $catalogo = new Catalogo();
        $cliente = new Cliente();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
            $cliente->setEmpresa($this->empresa);
        } 
        
        if(!empty($id_usuario)){
            $result = $cliente->getClientesByEjecutivoCuenta($id_usuario);
            if(mysql_num_rows($result) > 0){
                $tipo_usuarios = "1";
                $id_ejecutivos = $id_usuario;
            }else{
                $result = $cliente->getClientesByEjecutivoAtencion($id_usuario);
                if(mysql_num_rows($result) > 0){
                    $tipo_usuarios = "2";
                    $id_ejecutivos = $id_usuario;
                }
            }
        }
        
        $domicilios = array();
        if($categoria != ""){
            $where = " AND (c.IdGiro = $categoria OR kcc.IdGiro = $categoria) ";
        }
        
        if($tipo_cliente != ""){
            $where = " AND c.IdTipoCliente = $tipo_cliente ";
        }
        
        if(!empty($tipo_usuarios) && !empty($id_ejecutivos)){
            if($tipo_usuarios == "1"){
                $where = " AND c.EjecutivoCuenta IN ($id_ejecutivos) ";
            }else if($tipo_usuarios == "2"){
                $where = " AND c.EjecutivoAtencionCliente IN ($id_ejecutivos) ";
            }
        }
                        
        if(!empty($latitud) && !empty($longitud)){
            $distancia_km = "111.1111 *
            DEGREES(ACOS(COS(RADIANS(d.Latitud))
                * COS(RADIANS($latitud))
                * COS(RADIANS(d.Longitud - $longitud))
                + SIN(RADIANS(d.Latitud))
                * SIN(RADIANS($latitud)))) AS distance_in_km";
            if(empty($radio)){
                $having = " HAVING distance_in_km <= Radio_calculado ";
            }else{
                $having = " HAVING distance_in_km <= $radio ";
            }
            $where_latitud = " AND !ISNULL(Latitud) AND !ISNULL(Longitud) ";
        }else{
            $distancia_km = "0 AS distance_in_km";
            $having = "";
            $where_latitud = "";
        }
        
        $consulta = "SELECT d.IdDomicilio, d.Latitud, d.Longitud,c.NombreRazonSocial,tc.Nombre, 
            (CASE WHEN c.IdEstatusCobranza = 1 THEN tc.Radio ELSE (SELECT Radio FROM c_tipocliente WHERE IdTipoCliente = 1) END) AS Radio_calculado,
            $distancia_km 
            FROM `c_domicilio` AS d
            INNER JOIN c_cliente AS c ON d.ClaveEspecialDomicilio = c.ClaveCliente
            LEFT JOIN k_clientecategoria AS kcc ON kcc.ClaveCliente = c.ClaveCliente
            LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = c.IdTipoCliente
            WHERE d.Activo = 1 $where $where AND c.Activo = 1 
            GROUP BY c.ClaveCliente
            $having 
            ORDER BY distance_in_km, c.NombreRazonSocial
            LIMIT $indice,$registros_por_pagina;";
        
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $domicilios[$rs['IdDomicilio']] = $rs['distance_in_km'];            
        }
        
        return $domicilios;
    }
    
    public function obtenerDomiciliosCercanos2($latitud, $longitud, $categoria, $tipo_cliente, $indice, $registros_por_pagina, $radio, $id_ejecutivos, $tipo_usuarios, $id_usuario){
        $catalogo = new Catalogo();
        $cliente = new Cliente();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
            $cliente->setEmpresa($this->empresa);
        } 
        
        if(!empty($id_usuario)){
            $result = $cliente->getClientesByEjecutivoCuenta($id_usuario);
            if(mysql_num_rows($result) > 0){
                $tipo_usuarios = "1";
                $id_ejecutivos = $id_usuario;
            }else{
                $result = $cliente->getClientesByEjecutivoAtencion($id_usuario);
                if(mysql_num_rows($result) > 0){
                    $tipo_usuarios = "2";
                    $id_ejecutivos = $id_usuario;
                }
            }
        }
        
        $domicilios = array();
        if($categoria != ""){
            $where = " AND (c.IdGiro = $categoria OR kcc.IdGiro = $categoria) ";
        }
        
        if($tipo_cliente != ""){
            $where = " AND c.Modalidad = $tipo_cliente ";
        }
        
        if(!empty($tipo_usuarios) && !empty($id_ejecutivos)){
            if($tipo_usuarios == "1"){
                $where = " AND c.EjecutivoCuenta IN ($id_ejecutivos) ";
            }else if($tipo_usuarios == "2"){
                $where = " AND c.EjecutivoAtencionCliente IN ($id_ejecutivos) ";
            }
        }
                        
        if(!empty($latitud) && !empty($longitud)){
            $distancia_km = "111.1111 *
            DEGREES(ACOS(COS(RADIANS(d.Latitud))
                * COS(RADIANS($latitud))
                * COS(RADIANS(d.Longitud - $longitud))
                + SIN(RADIANS(d.Latitud))
                * SIN(RADIANS($latitud)))) AS distance_in_km";
            if(empty($radio)){
                $having = " HAVING distance_in_km <= Radio_calculado ";
            }else{
                $having = " HAVING distance_in_km <= $radio ";
            }
            $where_latitud = " AND !ISNULL(Latitud) AND !ISNULL(Longitud) ";
        }else{
            $distancia_km = "0 AS distance_in_km";
            $having = "";
            $where_latitud = "";
        }
        
        $consulta = "SELECT d.IdDomicilio, d.Latitud, d.Longitud,c.NombreRazonSocial,tc.Nombre, 
            (CASE WHEN c.IdEstatusCobranza = 1 THEN tc.Radio ELSE (SELECT Radio FROM c_tipocliente WHERE IdTipoCliente = 1) END) AS Radio_calculado,
            $distancia_km 
            FROM `c_domicilio` AS d
            INNER JOIN c_cliente AS c ON d.ClaveEspecialDomicilio = c.ClaveCliente
            LEFT JOIN k_clientecategoria AS kcc ON kcc.ClaveCliente = c.ClaveCliente
            LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = c.IdTipoCliente
            WHERE d.Activo = 1 $where $where AND c.Activo = 1 
            GROUP BY c.ClaveCliente
            $having 
            ORDER BY distance_in_km, c.NombreRazonSocial
            LIMIT $indice,$registros_por_pagina;";
        
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $domicilios[$rs['IdDomicilio']] = $rs['distance_in_km'];            
        }
        
        return $domicilios;
    }
    
    public function getTodosContactos($tipo_contacto){
        if(!empty($tipo_contacto)){
            $where = "AND ctt.IdTipoContacto = $tipo_contacto";
        }
        $consulta = "SELECT ctt.IdContacto,
            (CASE WHEN !ISNULL(d.IdDomicilio) THEN d.Latitud ELSE d2.Latitud END) AS Latitud,
            (CASE WHEN !ISNULL(d.IdDomicilio) THEN d.Longitud ELSE d2.Longitud END) AS Longitud
            FROM `c_contacto` AS ctt
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = ctt.ClaveEspecialContacto
            LEFT JOIN c_domicilio AS d ON d.ClaveEspecialDomicilio = c.ClaveCliente
            LEFT JOIN c_centrocosto AS cc ON c.ClaveCliente = ctt.ClaveEspecialContacto
            LEFT JOIN c_domicilio AS d2 ON d2.ClaveEspecialDomicilio = cc.ClaveCentroCosto
            WHERE ctt.Activo = 1 $where AND c.Activo = 1 AND (!ISNULL(d.IdDomicilio) OR !ISNULL(d2.IdDomicilio));";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function obtenerContactosCercanos($latitud, $longitud, $tipo_contacto, $indice, $registros_por_pagina, $radio){
        $contactos = array();
        
        $where = "";
        if(!empty($tipo_contacto)){
            $where = "AND ctt.IdTipoContacto = $tipo_contacto";
        }
        
        if(empty($radio)){
            $having = " HAVING distance_in_km <= Radio_calculado ";
        }else{
            $having = " HAVING distance_in_km <= $radio ";
        }
        
        $consulta = "
            SELECT ctt.IdContacto,c.NombreRazonSocial,
            (CASE WHEN c.IdEstatusCobranza = 1 THEN tc.Radio ELSE (SELECT Radio FROM c_tipocliente WHERE IdTipoCliente = 1) END) AS Radio_calculado,
            (CASE WHEN !ISNULL(d.IdDomicilio) THEN d.Latitud ELSE d2.Latitud END) AS Latitud,
            (CASE WHEN !ISNULL(d.IdDomicilio) THEN d.Longitud ELSE d2.Longitud END) AS Longitud,
            (CASE WHEN !ISNULL(d.IdDomicilio) THEN
            (111.1111 *
            DEGREES(ACOS(COS(RADIANS(d.Latitud))
                    * COS(RADIANS($latitud))
                    * COS(RADIANS(d.Longitud - $longitud))
                    + SIN(RADIANS(d.Latitud))
                    * SIN(RADIANS($latitud)))) )
            ELSE
            (
            111.1111 *
            DEGREES(ACOS(COS(RADIANS(d2.Latitud))
                    * COS(RADIANS($latitud))
                    * COS(RADIANS(d2.Longitud - $longitud))
                    + SIN(RADIANS(d2.Latitud))
                    * SIN(RADIANS($latitud)))) )
            END) AS distance_in_km
            FROM `c_contacto` AS ctt
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = ctt.ClaveEspecialContacto
            LEFT JOIN c_domicilio AS d ON d.ClaveEspecialDomicilio = c.ClaveCliente
            LEFT JOIN c_centrocosto AS cc ON c.ClaveCliente = ctt.ClaveEspecialContacto
            LEFT JOIN c_domicilio AS d2 ON d2.ClaveEspecialDomicilio = cc.ClaveCentroCosto
            LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = c.IdTipoCliente
            WHERE ctt.Activo = 1 $where AND c.Activo = 1 AND (!ISNULL(d.IdDomicilio) OR !ISNULL(d2.IdDomicilio))
            GROUP BY IdContacto
            $having AND !ISNULL(Latitud) AND !ISNULL(Longitud)
            ORDER BY distance_in_km
            LIMIT $indice,$registros_por_pagina;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $contactos[$rs['IdContacto']] = $rs['distance_in_km'];            
        }
        
        return $contactos;
    }

    public function obtenerContactoClaveCliente($ClaveCliente, $ClaveLocalidad, $tipos_contactos, $indice, $registros_por_pagina){
        $contactos = array();
        
        $ClaveEspecial = $ClaveCliente;
        if(isset($ClaveLocalidad) && $ClaveLocalidad != ""){
            $ClaveEspecial = $ClaveLocalidad;
        }
        
        $where = "";
        if(!empty($tipos_contactos)){
            $tipos = explode(",", $tipos_contactos);
            for($i = 0; $i < count($tipos); $i++){
                if($i == 0){
                    $where = "AND (ctt.IdTipoContacto = $tipos[$i]";
                }else{
                    $where .= " OR ctt.IdTipoContacto = $tipos[$i]";
                }
            }
            $where .= ")";
        }
        
        $consulta = "
            SELECT ctt.IdContacto,c.NombreRazonSocial 
            FROM `c_contacto` AS ctt
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = ctt.ClaveEspecialContacto
            LEFT JOIN c_centrocosto AS cc ON c.ClaveCliente = ctt.ClaveEspecialContacto
            WHERE ctt.Activo = 1 $where AND ctt.Activo = 1
            AND ctt.ClaveEspecialContacto = '$ClaveEspecial' 
            GROUP BY IdContacto
            LIMIT $indice,$registros_por_pagina;";        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $result = $catalogo->obtenerLista($consulta);
        
        while($rs = mysql_fetch_array($result)){
            $contactos[$rs['IdContacto']] = $rs['NombreRazonSocial'];            
        }
        
        return $contactos;
    }
    
    public function getLocalidad() {
        return $this->Localidad;
    }

    public function setLocalidad($Localidad) {
        $this->Localidad = $Localidad;
    }

    public function getIdDomicilio() {
        return $this->IdDomicilio;
    }

    public function setIdDomicilio($IdDomicilio) {
        $this->IdDomicilio = $IdDomicilio;
    }

    public function getClaveEspecialDomicilio() {
        return $this->ClaveEspecialDomicilio;
    }

    public function setClaveEspecialDomicilio($ClaveEspecialDomicilio) {
        $this->ClaveEspecialDomicilio = $ClaveEspecialDomicilio;
    }

    public function getIdTipoDomicilio() {
        return $this->IdTipoDomicilio;
    }

    public function setIdTipoDomicilio($IdTipoDomicilio) {
        $this->IdTipoDomicilio = $IdTipoDomicilio;
    }

    public function getCalle() {
        return $this->Calle;
    }

    public function setCalle($Calle) {
        $this->Calle = $Calle;
    }

    public function getNoExterior() {
        return $this->NoExterior;
    }

    public function setNoExterior($NoExterior) {
        $this->NoExterior = $NoExterior;
    }

    public function getNoInterior() {
        return $this->NoInterior;
    }

    public function setNoInterior($NoInterior) {
        $this->NoInterior = $NoInterior;
    }

    public function getColonia() {
        return $this->Colonia;
    }

    public function setColonia($Colonia) {
        $this->Colonia = $Colonia;
    }

    public function getCiudad() {
        return $this->Ciudad;
    }

    public function setCiudad($Ciudad) {
        $this->Ciudad = $Ciudad;
    }

    public function getEstado() {
        return $this->Estado;
    }

    public function setEstado($Estado) {
        $this->Estado = $Estado;
    }

    public function getDelegacion() {
        return $this->Delegacion;
    }

    public function setDelegacion($Delegacion) {
        $this->Delegacion = $Delegacion;
    }

    public function getPais() {
        return $this->Pais;
    }

    public function setPais($Pais) {
        $this->Pais = $Pais;
    }

    public function getCodigoPostal() {
        return $this->CodigoPostal;
    }

    public function setCodigoPostal($CodigoPostal) {
        $this->CodigoPostal = $CodigoPostal;
    }

    public function getClaveZona() {
        return $this->ClaveZona;
    }

    public function setClaveZona($ClaveZona) {
        $this->ClaveZona = $ClaveZona;
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

    public function getLatitud() {
        return $this->Latitud;
    }

    public function setLatitud($Latitud) {
        $this->Latitud = $Latitud;
    }

    public function getLongitud() {
        return $this->longitud;
    }

    public function setLongitud($longitud) {
        $this->longitud = $longitud;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>

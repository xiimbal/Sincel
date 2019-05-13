<?php

include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");
include_once("ParametroGlobal.class.php");
include_once("Usuario.class.php");
include_once("Cliente.class.php");
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ccliente {

    private $EstatusCobranza;
    private $claveCliente;
    private $RazonSocial;
    private $tipoCliente;
    private $giro;
    private $TipoDomicilio;
    private $CalleF;
    private $NoExtF;
    private $NoIntF;
    private $ColoniaF;
    private $CiudadF;
    private $EstadoF;
    private $DelegacionF;
    private $CPF;
    private $Pais;
    private $RFCD;
    private $CorreoE1D;
    private $CorreoE2D;
    private $CorreoE3D;
    private $CorreoE4D;
    private $ClaveZona;
    private $ClaveGrupo;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $fechaCreacion;
    private $fechaMoficacion;
    private $EjecutivoCuenta;
    private $empresa;
    private $idCliente;
    private $idDomicilio;
    private $idTipoDomicilio;
    private $Modalidad;
    private $TipoFacturacion;
    private $IdDatosFacturacionEmpresa;
    private $Localidad;
    private $Latitud;
    private $Longitud;
    private $Calificacion;
    private $Comentario;
    private $Imagen;
    private $Telefono;
    private $TamanoPerro;
    private $Email;
    private $Sitioweb;
    private $Facebook;
    private $Twitter;
    private $Horario;
    private $categorias;
    private $EjecutivoAtencionCliente;
    private $NivelCliente;
    private $IdTipoMorosidad;
    private $regresar_boleano;
    private $IdAddenda;
    private $MostarCondicionesPago;
    private $MostrarAddenda;
    private $idCuentaBancaria;
    private $referenciaNumerica;
    private $verCClientePDF;
    /* Datos para CFDI33 de clientes de venta*/
    private $FormaPago = "";
    private $MetodoPago = "";
    private $numero_cuenta = "";
    private $idBanco = "";
    private $idFormaComprobante = "";
    private $idUsoCFDI = "";
    private $idCuentaBancariaCFDI33 = "";
    private $dias_credito = "";

    public function nuevoRegistro() {
        if (!isset($this->ClaveZona) || $this->ClaveZona == "") {
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
        }
        
        $usuario_obj = new Usuario();
        if (isset($this->empresa)) {
            $usuario_obj->setEmpresa($this->empresa);
        }

        /* Obtenemos el vendedor que se pone por default segun los parametros globales */
        $parametro = new ParametroGlobal();
        if (isset($this->empresa)) {
            $parametro->setEmpresa($this->empresa);
        }
        
        if(isset($this->EjecutivoCuenta) && !empty($this->EjecutivoCuenta)){
            $usuario = $this->EjecutivoCuenta;
        }else{            
            if (isset($_SESSION['idUsuario']) && $usuario_obj->isUsuarioPuesto($_SESSION['idUsuario'], "11")) {
                $usuario = $_SESSION['idUsuario'];
            } else {                                
                if ($parametro->getRegistroById("3")) {
                    $usuario = $parametro->getValor();
                } else {
                    $usuario = "4"; /* Si no existe sesion o el usuario no es vendedor, el cliente se agrega al usuario 4 (Gerardo beltran en genesis) */
                }
            }
        }
        
        $ClaveGrupo = "null";
        if (isset($this->ClaveGrupo) && $this->ClaveGrupo != "") {
            $ClaveGrupo = "'$this->ClaveGrupo'";
        }

        $comentario = "null";
        if (isset($this->Comentario) && $this->Comentario != "") {
            $comentario = "'$this->Comentario'";
        }

        $calificacion = "null";
        if (isset($this->Calificacion) && $this->Calificacion != "") {
            $calificacion = "'$this->Calificacion'";
        }

        $imagen = "null";
        if (isset($this->Imagen) && $this->Imagen != "") {
            $imagen = "'$this->Imagen'";
        }
        
        if(!isset($this->EjecutivoAtencionCliente) || empty($this->EjecutivoAtencionCliente) || !is_numeric($this->EjecutivoAtencionCliente)){
            $this->EjecutivoAtencionCliente = "NULL";
        }

        $values = "'" . $this->EstatusCobranza . "','" . $this->giro . "','" . $this->IdDatosFacturacionEmpresa . "',
            '" . $this->tipoCliente . "','".$usuario."',$this->EjecutivoAtencionCliente,'" . $this->RazonSocial . "','" . $this->RFCD . "'";
        if (isset($this->CorreoE1D) && $this->CorreoE1D != "") {
            $values.=",'" . $this->CorreoE1D . "'";
        } else {
            $values.=",''";
        }
        if (isset($this->CorreoE2D) && $this->CorreoE2D != "") {
            $values.=",'" . $this->CorreoE2D . "'";
        } else {
            $values.=",''";
        }
        if (isset($this->CorreoE3D) && $this->CorreoE3D != "") {
            $values.=",'" . $this->CorreoE3D . "'";
        } else {
            $values.=",''";
        }
        if (isset($this->CorreoE4D) && $this->CorreoE4D != "") {
            $values.=",'" . $this->CorreoE4D . "'";
        } else {
            $values.=",''";
        }
        $values.="," . $this->activo . ",'" . $this->usuarioCreacion . "',NOW(),'" . $this->usuarioModificacion . "',NOW(),'" . $this->pantalla . "'";
        if (isset($this->Modalidad) && $this->Modalidad != "") {
            $values .= ",$this->Modalidad";
        } else {
            $values .= ",1";
        }
        if (isset($this->TipoFacturacion) && $this->TipoFacturacion != "") {
            $values .= ",$this->TipoFacturacion";
        } else {
            $values .= ",1";
        }

        $values .= ",'$this->ClaveZona', $ClaveGrupo, $calificacion, $comentario, $imagen";

        if (isset($this->Telefono) && $this->Telefono != "") {
            $values .= ",'$this->Telefono'";
        } else {
            $values .= ",NULL";
        }

        if (isset($this->Email) && $this->Email != "") {
            $values .= ",'$this->Email'";
        } else {
            $values .= ",NULL";
        }

        if (isset($this->Sitioweb) && $this->Sitioweb != "") {
            $values .= ",'$this->Sitioweb'";
        } else {
            $values .= ",NULL";
        }

        if (isset($this->Facebook) && $this->Facebook != "") {
            $values .= ",'$this->Facebook'";
        } else {
            $values .= ",NULL";
        }

        if (isset($this->Twitter) && $this->Twitter != "") {
            $values .= ",'$this->Twitter'";
        } else {
            $values .= ",NULL";
        }

        if (isset($this->Horario) && $this->Horario != "") {
            $values .= ",'$this->Horario'";
        } else {
            $values .= ",NULL";
        }
        
        if (isset($this->TamanoPerro) && $this->TamanoPerro != "") {
            $values .= ",'$this->TamanoPerro'";
        } else {
            $values .= ",NULL";
        }

        if (isset($this->NivelCliente) && $this->NivelCliente != "") {
            $values .= ",'$this->NivelCliente'";
        } else {
            $values .= ",NULL";
        }
        
        if (isset($this->IdTipoMorosidad) && $this->IdTipoMorosidad != "") {
            $values .= ",'$this->IdTipoMorosidad'";
        } else {
            $values .= ",NULL";
        }
        
        if (isset($this->IdAddenda) && $this->IdAddenda != "") {
            $values .= ",'$this->IdAddenda'";
        } else {
            $values .= ",NULL";
        }
        
        if (isset($this->MostarCondicionesPago) && $this->MostarCondicionesPago != "") {
            $values .= ",$this->MostarCondicionesPago";
        } else {
            $values .= ",0";
        }
        
        if (isset($this->MostrarAddenda) && $this->MostrarAddenda != "") {
            $values .= ",$this->MostrarAddenda";
        } else {
            $values .= ",0";
        }
        
        if (isset($this->idCuentaBancaria) && $this->idCuentaBancaria != "") {
            $values .= ",$this->idCuentaBancaria";
        } else {
            $values .= ",null";
        }
        if (isset($this->referenciaNumerica) && $this->referenciaNumerica != "") {
            $values .= ",'$this->referenciaNumerica'";
        } else {
            $values .= ",''";
        }
        
        if(isset($this->verCClientePDF) && $this->verCClientePDF!=""){
            $values .= ",".$this->verCClientePDF;
        }else{
            $values .= ",0";
        }
        
        $consulta = ("SELECT MAX(CAST(ClaveCliente AS UNSIGNED)) AS maximo FROM `c_cliente`;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query2 = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query2)) {
            $maximo = (int) $rs['maximo'];
            if ($maximo == "" || $maximo == 0) {
                $maximo = 10001;
            }
            $maximo++;
            $consulta = "INSERT INTO c_cliente(ClaveCliente, IdEstatusCobranza,IdGiro,IdDatosFacturacionEmpresa,IdTipoCliente,EjecutivoCuenta,
                EjecutivoAtencionCliente,NombreRazonSocial,RFC,
                CorreoElectronicoEnvioFact1,CorreoElectronicoEnvioFact2,CorreoElectronicoEnvioFact3,CorreoElectronicoEnvioFact4,Activo,UsuarioCreacion,
                FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Modalidad,IdTipoFacturacion,ClaveZona, ClaveGrupo, 
                Calificacion, Comentario, Foto, Telefono, Email, Sitioweb, Facebook, Twitter, Horario, TamanoPerro, NivelCliente, IdTipoMorosidad, 
                IdAddenda, MostarCondicionesPago,MostrarAddenda, idCuentaBancaria, referencia, verCClientePDF) 
                VALUES('$maximo', " . $values . ");";
            
            $query = $catalogo->obtenerLista($consulta);
            if ($query == "1") {
                $this->idCliente = $maximo;
                
                $latitud = "null";
                if (isset($this->Latitud) && $this->Latitud != "") {
                    $latitud = "$this->Latitud";
                }

                $longitud = "null";
                if (isset($this->Longitud) && $this->Longitud != "") {
                    $longitud = "$this->Longitud";
                }

                if(!isset($this->NoIntF) || $this->NoIntF == ""){
                    $this->NoIntF = "0";
                }
                
                $consulta = ("INSERT INTO c_domicilio(IdTipoDomicilio,ClaveEspecialDomicilio,Calle,NoExterior,NoInterior,Colonia,Ciudad,Estado,Delegacion,Pais,CodigoPostal,Activo,
                UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Localidad, Latitud, Longitud)
                VALUES(" . $this->getTipoDomicilio() . ",$maximo,'" . $this->CalleF . "','" . $this->NoExtF . "','" . $this->NoIntF . "','" . $this->ColoniaF . "',
                    '" . $this->CiudadF . "','" . $this->EstadoF . "','" . $this->DelegacionF . "','México','" . $this->CPF . "',1,'" . $this->usuarioCreacion . "',
                    NOW(),'" . $this->usuarioModificacion . "',NOW(),'" . $this->pantalla . "',
                    '$this->Localidad', $latitud, $longitud);");
                $catalogo->insertarRegistro($consulta);
                
                if(isset($this->categorias)){
                    $this->insertarMultiCategorias($this->categorias);
                }
                
                if(isset($this->regresar_boleano) && $this->regresar_boleano){
                    return true;
                }else{
                    return "El cliente <b>$this->RazonSocial</b> se insertó correctamente";
                }
            } else {
                if(isset($this->regresar_boleano) && $this->regresar_boleano){
                    return false;
                }else{
                    $cliente_aux = new Cliente();
                    if($cliente_aux->getRegistroByRFCValidacion($this->RFCD)){
                        return "Error: el RFC <b>".$this->RFCD."</b> ya está registrado en el sistema";
                    }else{
                        return "Error: no se pudo ingresar, favor de intentar más tarde.";
                    }
                }
            }
        }
    }

    public function getregistrobyID($id) {
        $consulta = "SELECT c.ClaveCliente AS idCliente,
            c.IdEstatusCobranza AS IdEstatusCobranza,
            c.IdGiro AS Giro,
            c.referencia,
            c.verCClientePDF,
            c.IdTipoCliente AS TipoCliente,
            c.EjecutivoCuenta AS EjecutivoCuenta,
            c.NombreRazonSocial AS NombreRazonSocial,
            c.RFC AS RFC,
            c.CorreoElectronicoEnvioFact1 AS Correo1,
            c.CorreoElectronicoEnvioFact2 AS Correo2,
            c.CorreoElectronicoEnvioFact3 AS Correo3,
            c.CorreoElectronicoEnvioFact4 AS Correo4,
            c.Modalidad,
            c.idCuentaBancaria,
            c.IdTipoFacturacion,
            c.IdDatosFacturacionEmpresa,
            c.ClaveZona, c.ClaveGrupo,
            c.Calificacion, c.Comentario, c.Foto,
            c.EjecutivoAtencionCliente,
            c.Activo AS Activo,       
            c.Telefono,
            c.Email, c.Sitioweb, c.Facebook, c.Twitter, c.Horario,
            c.IdAddenda, c.MostarCondicionesPago, c.MostrarAddenda,
            d.IdDomicilio AS IdDomicilio,
            d.IdTipoDomicilio AS IdTipoDomicilio,
            d.Calle AS Calle,
            d.NoExterior AS NoExterior,
            d.NoInterior AS NoInterior,
            d.Colonia AS Colonia,
            d.Ciudad AS Ciudad,
            d.Estado AS Estado,
            d.Delegacion AS Delegacion,
            d.Pais AS Pais,
            d.CodigoPostal AS CodigoPostal ,
            d.Localidad,
            d.Latitud, d.Longitud
            FROM c_cliente AS c
            LEFT JOIN c_domicilio AS d ON d.ClaveEspecialDomicilio=c.ClaveCliente AND d.IdTipoDomicilio = 3
            WHERE c.ClaveCliente='$id';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->EstatusCobranza = $rs['IdEstatusCobranza'];
            $this->idCliente = $rs['idCliente'];
            $this->claveCliente = $rs['idCliente'];
            $this->giro = $rs['Giro'];
            $this->tipoCliente = $rs['TipoCliente'];
            $this->EjecutivoCuenta = $rs['EjecutivoCuenta'];
            $this->RazonSocial = $rs['NombreRazonSocial'];
            $this->RFCD = $rs['RFC'];
            $this->CorreoE1D = $rs['Correo1'];
            $this->CorreoE2D = $rs['Correo2'];
            $this->CorreoE3D = $rs['Correo3'];
            $this->CorreoE4D = $rs['Correo4'];
            $this->Modalidad = $rs['Modalidad'];
            $this->TipoFacturacion = $rs['IdTipoFacturacion'];
            $this->activo = $rs['Activo'];
            $this->idDomicilio = $rs['IdDomicilio'];
            $this->idTipoDomicilio = $rs['IdTipoDomicilio'];
            $this->CalleF = $rs['Calle'];
            $this->NoExtF = $rs['NoExterior'];
            $this->NoIntF = $rs['NoInterior'];
            $this->ColoniaF = $rs['Colonia'];
            $this->CiudadF = $rs['Ciudad'];
            $this->EstadoF = $rs['Estado'];
            $this->DelegacionF = $rs['Delegacion'];
            $this->Pais = $rs['Pais'];
            $this->CPF = $rs['CodigoPostal'];
            //$this->empresa = $rs['idEmpresa'];
            $this->IdDatosFacturacionEmpresa = $rs['IdDatosFacturacionEmpresa'];
            $this->ClaveZona = $rs['ClaveZona'];
            $this->ClaveGrupo = $rs['ClaveGrupo'];
            $this->Localidad = $rs['Localidad'];
            $this->Latitud = $rs['Latitud'];
            $this->Longitud = $rs['Longitud'];
            $this->Comentario = $rs['Comentario'];
            $this->Calificacion = $rs['Calificacion'];
            $this->Imagen = $rs['Foto'];
            $this->Telefono = $rs['Telefono'];
            $this->TamanoPerro = $rs['TamanoPerro'];
            $this->Email = $rs['Email'];
            $this->Sitioweb = $rs['Sitioweb'];
            $this->Facebook = $rs['Facebook'];
            $this->Twitter = $rs['Twitter'];
            $this->Horario = $rs['Horario'];
            $this->EjecutivoAtencionCliente = $rs['EjecutivoAtencionCliente'];
            $this->IdAddenda = $rs['IdAddenda'];
            $this->MostarCondicionesPago = $rs['MostarCondicionesPago'];
            $this->MostrarAddenda = $rs['MostrarAddenda'];
            $this->idCuentaBancaria = $rs['idCuentaBancaria'];
            $this->referenciaNumerica = $rs['referencia'];
            $this->verCClientePDF = $rs['verCClientePDF'];
            return true;
        }
        return false;
    }        
    
    public function getregistrobyRFC($rfc) {
        $consulta = "SELECT c.ClaveCliente AS idCliente,
            c.IdEstatusCobranza AS IdEstatusCobranza,
            c.IdGiro AS Giro,
            c.referencia,
            c.verCClientePDF,
            c.IdTipoCliente AS TipoCliente,
            c.EjecutivoCuenta AS EjecutivoCuenta,
            c.NombreRazonSocial AS NombreRazonSocial,
            c.RFC AS RFC,
            c.CorreoElectronicoEnvioFact1 AS Correo1,
            c.CorreoElectronicoEnvioFact2 AS Correo2,
            c.CorreoElectronicoEnvioFact3 AS Correo3,
            c.CorreoElectronicoEnvioFact4 AS Correo4,
            c.Modalidad,
            c.idCuentaBancaria,
            c.IdTipoFacturacion,
            c.IdDatosFacturacionEmpresa,
            c.ClaveZona, c.ClaveGrupo,
            c.Calificacion, c.Comentario, c.Foto,
            c.EjecutivoAtencionCliente,
            c.Activo AS Activo,       
            c.Telefono,
            c.Email, c.Sitioweb, c.Facebook, c.Twitter, c.Horario,
            c.IdAddenda, c.MostarCondicionesPago, c.MostrarAddenda,
            d.IdDomicilio AS IdDomicilio,
            d.IdTipoDomicilio AS IdTipoDomicilio,
            d.Calle AS Calle,
            d.NoExterior AS NoExterior,
            d.NoInterior AS NoInterior,
            d.Colonia AS Colonia,
            d.Ciudad AS Ciudad,
            d.Estado AS Estado,
            d.Delegacion AS Delegacion,
            d.Pais AS Pais,
            d.CodigoPostal AS CodigoPostal ,
            d.Localidad,
            d.Latitud, d.Longitud
            FROM c_cliente AS c
            LEFT JOIN c_domicilio AS d ON d.ClaveEspecialDomicilio=c.ClaveCliente AND d.IdTipoDomicilio = 3
            WHERE c.RFC='$rfc';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->EstatusCobranza = $rs['IdEstatusCobranza'];
            $this->idCliente = $rs['idCliente'];
            $this->claveCliente = $rs['idCliente'];
            $this->giro = $rs['Giro'];
            $this->tipoCliente = $rs['TipoCliente'];
            $this->EjecutivoCuenta = $rs['EjecutivoCuenta'];
            $this->RazonSocial = $rs['NombreRazonSocial'];
            $this->RFCD = $rs['RFC'];
            $this->CorreoE1D = $rs['Correo1'];
            $this->CorreoE2D = $rs['Correo2'];
            $this->CorreoE3D = $rs['Correo3'];
            $this->CorreoE4D = $rs['Correo4'];
            $this->Modalidad = $rs['Modalidad'];
            $this->TipoFacturacion = $rs['IdTipoFacturacion'];
            $this->activo = $rs['Activo'];
            $this->idDomicilio = $rs['IdDomicilio'];
            $this->idTipoDomicilio = $rs['IdTipoDomicilio'];
            $this->CalleF = $rs['Calle'];
            $this->NoExtF = $rs['NoExterior'];
            $this->NoIntF = $rs['NoInterior'];
            $this->ColoniaF = $rs['Colonia'];
            $this->CiudadF = $rs['Ciudad'];
            $this->EstadoF = $rs['Estado'];
            $this->DelegacionF = $rs['Delegacion'];
            $this->Pais = $rs['Pais'];
            $this->CPF = $rs['CodigoPostal'];
            //$this->empresa = $rs['idEmpresa'];
            $this->IdDatosFacturacionEmpresa = $rs['IdDatosFacturacionEmpresa'];
            $this->ClaveZona = $rs['ClaveZona'];
            $this->ClaveGrupo = $rs['ClaveGrupo'];
            $this->Localidad = $rs['Localidad'];
            $this->Latitud = $rs['Latitud'];
            $this->Longitud = $rs['Longitud'];
            $this->Comentario = $rs['Comentario'];
            $this->Calificacion = $rs['Calificacion'];
            $this->Imagen = $rs['Foto'];
            $this->Telefono = $rs['Telefono'];
            $this->TamanoPerro = $rs['TamanoPerro'];
            $this->Email = $rs['Email'];
            $this->Sitioweb = $rs['Sitioweb'];
            $this->Facebook = $rs['Facebook'];
            $this->Twitter = $rs['Twitter'];
            $this->Horario = $rs['Horario'];
            $this->EjecutivoAtencionCliente = $rs['EjecutivoAtencionCliente'];
            $this->IdAddenda = $rs['IdAddenda'];
            $this->MostarCondicionesPago = $rs['MostarCondicionesPago'];
            $this->MostrarAddenda = $rs['MostrarAddenda'];
            $this->idCuentaBancaria = $rs['idCuentaBancaria'];
            $this->referenciaNumerica = $rs['referencia'];
            $this->verCClientePDF = $rs['verCClientePDF'];
            return true;
        }
        return false;
    }

    public function update() {
        if($this->activo == "0"){
            include_once("ReporteFacturacion_net.class.php");        
            $facturas = new ReporteFacturacion();
            $facturas->setRfccliente($this->RFCD);
            $estatus = array(1);
            $facturas->setStatus($estatus);/*Para que muestre solo las facturas no pagadas*/
            $result = $facturas->getTabla(false); 
            if(mysql_num_rows($result) > 0){
                echo "Error: el cliente no puede ser marcado como inactivo porque tiene las siguientes facturas pendientes de pago:";
                while($rs = mysql_fetch_array($result)){
                    echo "<br/> * ".$rs['Folio'];
                }
                return;
            }
        }
        
        if (!isset($this->ClaveZona) || $this->ClaveZona == "") {
            /* Obtenemos la zona que se pone por default segun los parametros globales */
            $parametro = new ParametroGlobal();
            if ($parametro->getRegistroById("2")) {
                $this->ClaveZona = $parametro->getValor();
            } else {
                $this->ClaveZona = "Z06";
            }
        }
        $ClaveGrupo = "null";
        if (isset($this->ClaveGrupo) && $this->ClaveGrupo != "") {
            $ClaveGrupo = "'$this->ClaveGrupo'";
        }

        $comentario = "null";
        if (isset($this->Comentario) && $this->Comentario != "") {
            $comentario = "'$this->Comentario'";
        }

        $calificacion = "null";
        if (isset($this->Calificacion) && $this->Calificacion != "") {
            $calificacion = "'$this->Calificacion'";
        }

        $imagen = "null";
        if (isset($this->Imagen) && $this->Imagen != "") {
            $imagen = "'$this->Imagen'";
        }

        $latitud = "null";
        if (isset($this->Latitud) && $this->Latitud != "") {
            $latitud = "$this->Latitud";
        }

        $longitud = "null";
        if (isset($this->Longitud) && $this->Longitud != "") {
            $longitud = "$this->Longitud";
        }
        
        if(!isset($this->EjecutivoAtencionCliente) || empty($this->EjecutivoAtencionCliente) || !is_numeric($this->EjecutivoAtencionCliente)){
            $this->EjecutivoAtencionCliente = "NULL";
        }

        $catalogo = new Catalogo();

        if(!isset($this->NoIntF) || $this->NoIntF == ""){
            $this->NoIntF = "0";
        }
        
        if ($this->idDomicilio != "") {            
            $consulta = ("UPDATE c_domicilio SET IdTipoDomicilio='" . $this->getTipoDomicilio() . "',
                Calle='" . $this->CalleF . "',NoExterior='" . $this->NoExtF . "',NoInterior='" . $this->NoIntF . "',
                Colonia='" . $this->ColoniaF . "',Ciudad='" . $this->CiudadF . "',Estado='" . $this->EstadoF . "',Delegacion='" . $this->DelegacionF . "',
                Pais='México',CodigoPostal='" . $this->CPF . "',Activo=1,Localidad='$this->Localidad',Latitud = $latitud, Longitud = $longitud,
                UsuarioUltimaModificacion='" . $this->usuarioModificacion . "',FechaUltimaModificacion=" . $this->fechaMoficacion . ",Pantalla='" . $this->pantalla . "'
                WHERE IdDomicilio='" . $this->idDomicilio . "'");
            $query = $catalogo->obtenerLista($consulta);
        } else {
            $consulta = ("INSERT INTO c_domicilio(IdTipoDomicilio,ClaveEspecialDomicilio,Calle,NoExterior,NoInterior,Colonia,Ciudad,Estado,Delegacion,Pais,CodigoPostal,Activo,
                UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Localidad,Latitud,Longitud)
                VALUES(" . $this->getTipoDomicilio() . ",$this->idCliente,'" . $this->CalleF . "','" . $this->NoExtF . "','" . $this->NoIntF . "',
                    '" . $this->ColoniaF . "','" . $this->CiudadF . "','" . $this->EstadoF . "','" . $this->DelegacionF . "','México',
                    '" . $this->CPF . "',1,'" . $this->usuarioCreacion . "',NOW(),'" . $this->usuarioModificacion . "',NOW(),"
                    . "'" . $this->pantalla . "','$this->Localidad',$latitud,$longitud);");
            $query = $catalogo->insertarRegistro($consulta);
        }

        if ($query != "0") {
            $values = "UPDATE c_cliente SET EjecutivoCuenta='" . $this->EjecutivoCuenta . "',EjecutivoAtencionCliente=$this->EjecutivoAtencionCliente,"
                    . "IdEstatusCobranza='" . $this->EstatusCobranza . "',IdGiro='" . $this->giro . "',IdTipoCliente='" . $this->tipoCliente . "',"
                    . "NombreRazonSocial='" . $this->RazonSocial . "',RFC='" . $this->RFCD . "'";
            if (isset($this->CorreoE1D) && $this->CorreoE1D != "") {
                $values.=",CorreoElectronicoEnvioFact1='" . $this->CorreoE1D . "'";
            } else {
                $values.=",CorreoElectronicoEnvioFact1=''";
            }
            if (isset($this->CorreoE2D) && $this->CorreoE2D != "") {
                $values.=",CorreoElectronicoEnvioFact2='" . $this->CorreoE2D . "'";
            } else {
                $values.=",CorreoElectronicoEnvioFact2=''";
            }
            if (isset($this->CorreoE3D) && $this->CorreoE3D != "") {
                $values.=",CorreoElectronicoEnvioFact3='" . $this->CorreoE3D . "'";
            } else {
                $values.=",CorreoElectronicoEnvioFact3=''";
            }
            if (isset($this->CorreoE4D) && $this->CorreoE4D != "") {
                $values.=",CorreoElectronicoEnvioFact4='" . $this->CorreoE4D . "'";
            } else {
                $values.=",CorreoElectronicoEnvioFact4=''";
            }
            if (isset($this->Modalidad) && $this->Modalidad != "") {
                $values .= ",Modalidad=$this->Modalidad";
            }
            if (isset($this->TipoFacturacion) && $this->TipoFacturacion != "") {
                $values .= ",IdTipoFacturacion=$this->TipoFacturacion";
            }

            if (isset($this->Telefono) && $this->Telefono != "") {
                $values .= ",Telefono = '$this->Telefono'";
            } else {
                $values .= ",Telefono = NULL";
            }

            if (isset($this->Email) && $this->Email != "") {
                $values .= ",Email = '$this->Email'";
            } else {
                $values .= ",Email = NULL";
            }

            if (isset($this->Sitioweb) && $this->Sitioweb != "") {
                $values .= ",Sitioweb = '$this->Sitioweb'";
            } else {
                $values .= ",Sitioweb = NULL";
            }

            if (isset($this->Facebook) && $this->Facebook != "") {
                $values .= ",Facebook = '$this->Facebook'";
            } else {
                $values .= ",Facebook = NULL";
            }

            if (isset($this->Twitter) && $this->Twitter != "") {
                $values .= ",Twitter = '$this->Twitter'";
            } else {
                $values .= ",Twitter = NULL";
            }

            if (isset($this->Horario) && $this->Horario != "") {
                $values .= ",Horario = '$this->Horario'";
            } else {
                $values .= ",Horario = NULL";
            }
            
            if (isset($this->IdAddenda) && $this->IdAddenda != "") {
                $values .= ",IdAddenda='$this->IdAddenda'";
            } else {
                $values .= ",IdAddenda=NULL";
            }

            if (isset($this->MostarCondicionesPago) && $this->MostarCondicionesPago != "") {
                $values .= ",MostarCondicionesPago=$this->MostarCondicionesPago";
            } else {
                $values .= ",MostarCondicionesPago=0";
            }
            
            if (isset($this->MostrarAddenda) && $this->MostrarAddenda != "") {
                $values .= ",MostrarAddenda=$this->MostrarAddenda";
            } else {
                $values .= ",MostrarAddenda=0";
            }
            
            if (isset($this->idCuentaBancaria) && $this->idCuentaBancaria != "") {
                $values .= ",idCuentaBancaria=$this->idCuentaBancaria";
            } else {
                $values .= ",idCuentaBancaria=null";
            }
            
            if (isset($this->referenciaNumerica) && $this->referenciaNumerica != "") {
                $values .= ",referencia='$this->referenciaNumerica'";
            } else {
                $values .= ",referencia=''";
            }
            
            if(!isset($this->verCClientePDF) || $this->verCClientePDF == ""){
                $this->verCClientePDF = 0;
            }
            
            $values.=",ClaveZona = '$this->ClaveZona', ClaveGrupo = $ClaveGrupo, Comentario = $comentario, Calificacion = $calificacion,
                Foto = $imagen, verCClientePDF = " .$this->verCClientePDF. " ,Activo=" . $this->activo . ",UsuarioUltimaModificacion='" . $this->usuarioModificacion . "',"
                    . "FechaUltimaModificacion=NOW(),Pantalla='" . $this->pantalla . "', IdDatosFacturacionEmpresa = '$this->IdDatosFacturacionEmpresa' "
                    . "WHERE ClaveCliente='" . $this->idCliente . "'";
            $query = $catalogo->obtenerLista($values);            
            if ($query == 1) {
                $consulta = "UPDATE c_contrato SET RazonSocial = $this->IdDatosFacturacionEmpresa WHERE ClaveCliente = '$this->idCliente';";
                $catalogo->obtenerLista($consulta);
                $this->insertarMultiCategorias($this->categorias);
                return "El cliente <b>$this->RazonSocial</b> se actualizó correctamente";
            } else {
                return "Error: no se pudo actualizar, favor de intentar más tarde.";
            }
        } else {
            return "Error: no se pudo actualizar la dirección, favor de intentar más tarde.";
        }
    }

    public function deletebyID() {
        $consulta = ("DELETE FROM c_cliente WHERE ClaveCliente='" . $this->idCliente . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function obtieneMultiCategoria(){
        $consulta = "SELECT IdGiro FROM `k_clientecategoria` WHERE ClaveCliente = '$this->idCliente';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function insertarMultiCategorias($categorias){
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "DELETE FROM k_clientecategoria WHERE ClaveCliente = '$this->idCliente'";
        $catalogo->obtenerLista($consulta);
        if(isset($categorias) && !empty($categorias)){
            foreach ($categorias as $value) {
                $consulta = "INSERT INTO k_clientecategoria(ClaveCliente, IdGiro, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
                    VALUES('$this->idCliente', $value, '$this->usuarioCreacion', NOW(),'$this->usuarioModificacion',NOW(),'$this->pantalla');";            
                $catalogo->obtenerLista($consulta);            
            }
        }
    }
    
    public function getDatosFiscalesVenta(){
        $consulta = "SELECT * FROM `k_ventaconfiguracion` WHERE ClaveCliente = '".$this->claveCliente."';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){            
            $this->FormaPago = $rs['FormaPago'];
            $this->MetodoPago = $rs['IdMetodoPago'];
            $this->idFormaComprobante = $rs['IdFormaComprobantePago'];
            $this->idUsoCFDI = $rs['IdUsoCFDI'];
            $this->numero_cuenta = $rs['NumeroCuenta'];
            $this->idBanco = $rs['IdBanco'];
            $this->idCuentaBancaria = $rs['IdCuentaBancaria'];
            $this->dias_credito = $rs['DiasCredito'];
            return true;
        }
        return false;        
    }
    
    public function newDatosFiscalesVentas(){
        if(!isset($this->dias_credito) || ($this->dias_credito == "")){
            $this->dias_credito = "null";
        }
        if(!isset($this->idBanco) || ($this->idBanco == "")){
            $this->idBanco = "null";
        }
        if(!isset($this->idCuentaBancariaCFDI33) || ($this->idCuentaBancariaCFDI33 == "")){
            $this->idCuentaBancariaCFDI33 = "null";
        }
        if(!isset($this->idFormaComprobante) || ($this->idFormaComprobante == "")){
            $this->idFormaComprobante = "null";
        }
        
        $consulta = "INSERT INTO k_ventaconfiguracion(ClaveCliente,FormaPago,IdMetodoPago,IdFormaComprobantePago,IdUsoCFDI,NumeroCuenta,"
                . "IdBanco,IdCuentaBancaria,DiasCredito,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) "
                . "VALUES('$this->claveCliente',$this->FormaPago,$this->MetodoPago,$this->idFormaComprobante,$this->idUsoCFDI,'$this->numero_cuenta',"
                . "$this->idBanco,$this->idCuentaBancariaCFDI33,$this->dias_credito,'$this->usuarioCreacion',NOW(),'$this->usuarioModificacion',NOW(),'$this->pantalla')";
        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $id = $catalogo->insertarRegistro($consulta);
        if($id != null && $id != 0){
            return true;
        }
        return false;
    }
    
    public function editDatosFiscalesVentas(){
        if(!isset($this->dias_credito) || ($this->dias_credito == "")){
            $this->dias_credito = "null";
        }
        if(!isset($this->idBanco) || ($this->idBanco == "")){
            $this->idBanco = "null";
        }
        if(!isset($this->idCuentaBancariaCFDI33) || ($this->idCuentaBancariaCFDI33 == "")){
            $this->idCuentaBancariaCFDI33 = "null";
        }
        if(!isset($this->idFormaComprobante) || ($this->idFormaComprobante == "")){
            $this->idFormaComprobante = "null";
        }
        $consulta = "UPDATE k_ventaconfiguracion SET FormaPago = '$this->FormaPago',IdMetodoPago = $this->MetodoPago,"
                . "IdFormaComprobantePago = '$this->idFormaComprobante',IdUsoCFDI = $this->idUsoCFDI,NumeroCuenta = '$this->numero_cuenta',"
                . "IdBanco = '$this->idBanco',IdCuentaBancaria = '$this->idCuentaBancariaCFDI33',DiasCredito = $this->dias_credito,"
                . "UsuarioUltimaModificacion = '$this->usuarioModificacion',FechaUltimaModificacion = NOW(),Pantalla = '$this->pantalla' "
                . "WHERE ClaveCliente = '$this->claveCliente';";
        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if($query > 0){
            return true;
        }
        return false;
    }
    
    public function deleteDatosFiscalesVentas(){
        $consulta = "DELETE FROM k_ventaconfiguracion WHERE ClaveCliente = '$this->claveCliente';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        
        $query = $catalogo->obtenerLista($consulta);
        if($query >= 0){
            return true;
        }
        return false;        
    }

    public function getIdCliente() {
        return $this->idCliente;
    }

    public function setIdCliente($idCliente) {
        $this->idCliente = $idCliente;
    }

    public function getIdDomicilio() {
        return $this->idDomicilio;
    }

    public function setIdDomicilio($idDomicilio) {
        $this->idDomicilio = $idDomicilio;
    }

    public function getIdTipoDomicilio() {
        return $this->idTipoDomicilio;
    }

    public function setIdTipoDomicilio($idTipoDomicilio) {
        $this->idTipoDomicilio = $idTipoDomicilio;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function getEjecutivoCuenta() {
        return $this->EjecutivoCuenta;
    }

    public function setEjecutivoCuenta($EjecutivoCuenta) {
        $this->EjecutivoCuenta = $EjecutivoCuenta;
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

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function getFechaMoficacion() {
        return $this->fechaMoficacion;
    }

    public function setFechaMoficacion($fechaMoficacion) {
        $this->fechaMoficacion = $fechaMoficacion;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

    public function getTipoDomicilio() {
        return $this->TipoDomicilio;
    }

    public function setTipoDomicilio($TipoDomicilio) {
        $this->TipoDomicilio = $TipoDomicilio;
    }

    public function getEstatusCobranza() {
        return $this->EstatusCobranza;
    }

    public function setEstatusCobranza($EstatusCobranza) {
        $this->EstatusCobranza = $EstatusCobranza;
    }

    public function getClaveCliente() {
        return $this->claveCliente;
    }

    public function setClaveCliente($claveCliente) {
        $this->claveCliente = $claveCliente;
    }

    public function getRazonSocial() {
        return $this->RazonSocial;
    }

    public function setRazonSocial($RazonSocial) {
        $this->RazonSocial = $RazonSocial;
    }

    public function getTipoCliente() {
        return $this->tipoCliente;
    }

    public function setTipoCliente($tipoCliente) {
        $this->tipoCliente = $tipoCliente;
    }

    public function getGiro() {
        return $this->giro;
    }

    public function setGiro($giro) {
        $this->giro = $giro;
    }

    public function getCalleF() {
        return $this->CalleF;
    }

    public function setCalleF($CalleF) {
        $this->CalleF = $CalleF;
    }

    public function getNoExtF() {
        return $this->NoExtF;
    }

    public function setNoExtF($NoExtF) {
        $this->NoExtF = $NoExtF;
    }

    public function getNoIntF() {
        return $this->NoIntF;
    }

    public function setNoIntF($NoIntF) {
        $this->NoIntF = $NoIntF;
    }

    public function getColoniaF() {
        return $this->ColoniaF;
    }

    public function setColoniaF($ColoniaF) {
        $this->ColoniaF = $ColoniaF;
    }

    public function getCiudadF() {
        return $this->CiudadF;
    }

    public function setCiudadF($CiudadF) {
        $this->CiudadF = $CiudadF;
    }

    public function getEstadoF() {
        return $this->EstadoF;
    }

    public function setEstadoF($EstadoF) {
        $this->EstadoF = $EstadoF;
    }

    public function getDelegacionF() {
        return $this->DelegacionF;
    }

    public function setDelegacionF($DelegacionF) {
        $this->DelegacionF = $DelegacionF;
    }

    public function getCPF() {
        return $this->CPF;
    }

    public function setCPF($CPF) {
        $this->CPF = $CPF;
    }

    public function getRFCD() {
        return $this->RFCD;
    }

    public function setRFCD($RFCD) {
        $this->RFCD = $RFCD;
    }

    public function getCorreoE1D() {
        return $this->CorreoE1D;
    }

    public function setCorreoE1D($CorreoE1D) {
        $this->CorreoE1D = $CorreoE1D;
    }

    public function getCorreoE2D() {
        return $this->CorreoE2D;
    }

    public function setCorreoE2D($CorreoE2D) {
        $this->CorreoE2D = $CorreoE2D;
    }

    public function getCorreoE3D() {
        return $this->CorreoE3D;
    }

    public function setCorreoE3D($CorreoE3D) {
        $this->CorreoE3D = $CorreoE3D;
    }

    public function getCorreoE4D() {
        return $this->CorreoE4D;
    }

    public function setCorreoE4D($CorreoE4D) {
        $this->CorreoE4D = $CorreoE4D;
    }

    public function getModalidad() {
        return $this->Modalidad;
    }

    public function getTipoFacturacion() {
        return $this->TipoFacturacion;
    }

    public function setModalidad($Modalidad) {
        $this->Modalidad = $Modalidad;
    }

    public function setTipoFacturacion($TipoFacturacion) {
        $this->TipoFacturacion = $TipoFacturacion;
    }

    public function getIdDatosFacturacionEmpresa() {
        return $this->IdDatosFacturacionEmpresa;
    }

    public function setIdDatosFacturacionEmpresa($IdDatosFacturacionEmpresa) {
        $this->IdDatosFacturacionEmpresa = $IdDatosFacturacionEmpresa;
    }

    public function getClaveZona() {
        return $this->ClaveZona;
    }

    public function setClaveZona($ClaveZona) {
        $this->ClaveZona = $ClaveZona;
    }

    public function getClaveGrupo() {
        return $this->ClaveGrupo;
    }

    public function setClaveGrupo($ClaveGrupo) {
        $this->ClaveGrupo = $ClaveGrupo;
    }

    public function getLocalidad() {
        return $this->Localidad;
    }

    public function setLocalidad($Localidad) {
        $this->Localidad = $Localidad;
    }

    public function getLatitud() {
        return $this->Latitud;
    }

    public function setLatitud($Latitud) {
        $this->Latitud = $Latitud;
    }

    public function getLongitud() {
        return $this->Longitud;
    }

    public function setLongitud($Longitud) {
        $this->Longitud = $Longitud;
    }

    public function getCalificacion() {
        return $this->Calificacion;
    }

    public function setCalificacion($Calificacion) {
        $this->Calificacion = $Calificacion;
    }

    public function getComentario() {
        return $this->Comentario;
    }

    public function setComentario($Comentario) {
        $this->Comentario = $Comentario;
    }

    public function getImagen() {
        return $this->Imagen;
    }

    public function setImagen($Imagen) {
        $this->Imagen = $Imagen;
    }

    public function getPais() {
        return $this->Pais;
    }

    public function setPais($Pais) {
        $this->Pais = $Pais;
    }

    public function getTelefono() {
        return $this->Telefono;
    }

    public function setTelefono($Telefono) {
        $this->Telefono = $Telefono;
    }

    public function getTamanoPerro() {
        return $this->TamanoPerro;
    }

    public function setTamanoPerro($TamanoPerro) {
        $this->TamanoPerro = $TamanoPerro;
    }

    public function getEmail() {
        return $this->Email;
    }

    public function setEmail($Email) {
        $this->Email = $Email;
    }

    public function getSitioweb() {
        return $this->Sitioweb;
    }

    public function setSitioweb($Sitioweb) {
        $this->Sitioweb = $Sitioweb;
    }

    public function getFacebook() {
        return $this->Facebook;
    }

    public function setFacebook($Facebook) {
        $this->Facebook = $Facebook;
    }

    public function getTwitter() {
        return $this->Twitter;
    }

    public function setTwitter($Twitter) {
        $this->Twitter = $Twitter;
    }

    public function getHorario() {
        return $this->Horario;
    }

    public function setHorario($Horario) {
        $this->Horario = $Horario;
    }

    function getCategorias() {
        return $this->categorias;
    }

    function setCategorias($categorias) {
        $this->categorias = $categorias;
    }
    
    function getEjecutivoAtencionCliente() {
        return $this->EjecutivoAtencionCliente;
    }

    function setEjecutivoAtencionCliente($EjecutivoAtencionCliente) {
        $this->EjecutivoAtencionCliente = $EjecutivoAtencionCliente;
    }
    
    function getNivelCliente() {
        return $this->NivelCliente;
    }

    function setNivelCliente($NivelCliente) {
        $this->NivelCliente = $NivelCliente;
    }
    
    function getIdTipoMorosidad() {
        return $this->IdTipoMorosidad;
    }

    function setIdTipoMorosidad($IdTipoMorosidad) {
        $this->IdTipoMorosidad = $IdTipoMorosidad;
    }
    
    function getRegresar_boleano() {
        return $this->regresar_boleano;
    }

    function setRegresar_boleano($regresar_boleano) {
        $this->regresar_boleano = $regresar_boleano;
    }
    
    function getIdAddenda() {
        return $this->IdAddenda;
    }

    function getMostarCondicionesPago() {
        return $this->MostarCondicionesPago;
    }

    function setIdAddenda($IdAddenda) {
        $this->IdAddenda = $IdAddenda;
    }

    function setMostarCondicionesPago($MostarCondicionesPago) {
        $this->MostarCondicionesPago = $MostarCondicionesPago;
    }
    
    function getMostrarAddenda() {
        return $this->MostrarAddenda;
    }

    function setMostrarAddenda($MostrarAddenda) {
        $this->MostrarAddenda = $MostrarAddenda;
    }
    
    function getIdCuentaBancaria() {
        return $this->idCuentaBancaria;
    }

    function setIdCuentaBancaria($idCuentaBancaria) {
        $this->idCuentaBancaria = $idCuentaBancaria;
    }

    function getReferenciaNumerica() {
        return $this->referenciaNumerica;
    }

    function setReferenciaNumerica($referenciaNumerica) {
        $this->referenciaNumerica = $referenciaNumerica;
    }

    function getVerCClientePDF() {
        return $this->verCClientePDF;
    }

    function setVerCClientePDF($verCClientePDF) {
        $this->verCClientePDF = $verCClientePDF;
    }

    
    function getFormaPago() {
        return $this->FormaPago;
    }

    function getMetodoPago() {
        return $this->MetodoPago;
    }

    function getNumero_cuenta() {
        return $this->numero_cuenta;
    }

    function getIdBanco() {
        return $this->idBanco;
    }

    function getIdFormaComprobante() {
        return $this->idFormaComprobante;
    }

    function getIdUsoCFDI() {
        return $this->idUsoCFDI;
    }

    function getIdCuentaBancariaCFDI33() {
        return $this->idCuentaBancariaCFDI33;
    }

    function getDias_credito() {
        return $this->dias_credito;
    }

    function setFormaPago($FormaPago) {
        $this->FormaPago = $FormaPago;
    }

    function setMetodoPago($MetodoPago) {
        $this->MetodoPago = $MetodoPago;
    }

    function setNumero_cuenta($numero_cuenta) {
        $this->numero_cuenta = $numero_cuenta;
    }

    function setIdBanco($idBanco) {
        $this->idBanco = $idBanco;
    }

    function setIdFormaComprobante($idFormaComprobante) {
        $this->idFormaComprobante = $idFormaComprobante;
    }

    function setIdUsoCFDI($idUsoCFDI) {
        $this->idUsoCFDI = $idUsoCFDI;
    }

    function setIdCuentaBancariaCFDI33($idCuentaBancariaCFDI33) {
        $this->idCuentaBancariaCFDI33 = $idCuentaBancariaCFDI33;
    }

    function setDias_credito($dias_credito) {
        $this->dias_credito = $dias_credito;
    }
}

?>

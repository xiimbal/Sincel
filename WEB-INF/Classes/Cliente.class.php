<?php

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");
include_once("Usuario.class.php");
include_once("Catalogo.class.php");
include_once("TFSCliente.class.php");
include_once("ParametroGlobal.class.php");

/**
 * Description of Cliente
 *
 * @author MAGG
 */
class Cliente {

    private $ClaveCliente;
    private $IdEstatusCobranza;
    private $IdGiro;
    private $ClaveZona;
    private $IdDatosFacturacionEmpresa;
    private $IdTipoCliente;
    private $ClaveGrupo;
    private $EjecutivoCuenta;
    private $NombreRazonSocial;
    private $RFC;
    private $CorreoElectronicoEnvioFact1;
    private $CorreoElectronicoEnvioFact2;
    private $CorreoElectronicoEnvioFact3;
    private $CorreoElectronicoEnvioFact4;
    private $ImprimirFactura;
    private $HorarioEntrega;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $correoEjecutivo;
    private $nombreEjecutivo;
    private $calle;
    private $idTipoDomicilio;
    private $noExterior;
    private $noInterior;
    private $colonia;
    private $ciudad;
    private $estado;
    private $delegacion;
    private $pais;
    private $cp;
    private $Suspendido;
    private $Modalidad;
    private $IdTipoFacturacion;
    private $IdTipoMorosidad;
    private $GeneraFactura;
    private $DiasCredito;
    private $NoVolverMoroso;
    private $NivelCliente;
    private $empresa;
    private $Calificacion;
    private $Mensaje;
    private $Titulo;
    private $Telefono;
    private $TamanoPerro;
    private $Email;
    private $Sitioweb;
    private $comentario;
    private $Foto;
    private $Id_calificacion;
    private $IdUsuario;
    private $MostarMesContrato;
    private $EsperaContrato;

    /**
     * Obtener todos los clientes activos registrados y ordenarlos por nombre descendentemente
     * @return type ResultSet con resultado de queries.
     */
    public function getTodosRegistros() {
        $consulta = "SELECT * FROM c_cliente WHERE Activo = 1 ORDER BY NombreRazonSocial";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getRegistoValidacion($nombre, $clave) {
        $consulta = ("SELECT c.ClaveCliente,c.NombreRazonSocial,c.RFC, tc.Nombre AS tipoCliente FROM `c_cliente` AS c 
            LEFT JOIN c_clientemodalidad AS tc ON tc.IdTipoCliente = c.Modalidad
            WHERE c.NombreRazonSocial LIKE '%".  trim($nombre)."%' OR ClaveCliente LIKE '%".trim($clave)."%';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getRegistrosByClave($clave) {
        if ($clave == "") {
            $activo = "AND c.Activo = 1";
        } else {
            $activo = "";
        }
        $usuario = new Usuario();
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 21)) {
            $consulta = ("SELECT
                c.ClaveCliente,c.NombreRazonSocial,c.RFC, tc.Nombre AS tipoCliente, tf.TipoFacturacion
                FROM c_usuario
                INNER JOIN k_tfscliente ON k_tfscliente.IdUsuario=c_usuario.IdUsuario
                INNER JOIN c_cliente AS c ON c.ClaveCliente = k_tfscliente.ClaveCliente
                LEFT JOIN c_clientemodalidad AS tc ON tc.IdTipoCliente = c.Modalidad
                LEFT JOIN c_tipofacturacion AS tf ON tf.IdTipoFacturacion = c.IdTipoFacturacion
                WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c.ClaveCliente LIKE '%$clave%' $activo
                ORDER BY NombreRazonSocial ASC");
        } else if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)) {
            $consulta = ("SELECT
                c.ClaveCliente,c.NombreRazonSocial,c.RFC, tc.Nombre AS tipoCliente, tf.TipoFacturacion
                FROM c_usuario
                INNER JOIN c_cliente AS c ON c.EjecutivoCuenta = c_usuario.IdUsuario
                LEFT JOIN c_clientemodalidad AS tc ON tc.IdTipoCliente = c.Modalidad
                LEFT JOIN c_tipofacturacion AS tf ON tf.IdTipoFacturacion = c.IdTipoFacturacion
                WHERE c.EjecutivoCuenta=" . $_SESSION['idUsuario'] . " AND c.ClaveCliente LIKE '%$clave%' $activo
                ORDER BY NombreRazonSocial ASC;");
        } else {
            if(!empty($clave)){
                $consulta = ("SELECT c.ClaveCliente,c.NombreRazonSocial,c.RFC, tc.Nombre AS tipoCliente, tf.TipoFacturacion 
                FROM `c_cliente` AS c 
                LEFT JOIN c_clientemodalidad AS tc ON tc.IdTipoCliente = c.Modalidad
                LEFT JOIN c_tipofacturacion AS tf ON tf.IdTipoFacturacion = c.IdTipoFacturacion
                WHERE c.ClaveCliente LIKE '%$clave%' $activo;");
            }else{
                $consulta = ("SELECT c.ClaveCliente,c.NombreRazonSocial,c.RFC, tc.Nombre AS tipoCliente, tf.TipoFacturacion 
                FROM `c_cliente` AS c 
                LEFT JOIN c_clientemodalidad AS tc ON tc.IdTipoCliente = c.Modalidad
                LEFT JOIN c_tipofacturacion AS tf ON tf.IdTipoFacturacion = c.IdTipoFacturacion
                WHERE c.Activo = 1;");
            }
        }
        
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getRegistroByRFCValidacion($rfc) {
        $consulta = ("SELECT * FROM c_cliente WHERE RFC ='" . $rfc . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->ClaveCliente = $rs['ClaveCliente'];
            $this->IdEstatusCobranza = $rs['IdEstatusCobranza'];
            $this->IdGiro = $rs['IdGiro'];
            $this->ClaveZona = $rs['ClaveZona'];
            $this->IdDatosFacturacionEmpresa = $rs['IdDatosFacturacionEmpresa'];
            $this->IdTipoCliente = $rs['IdTipoCliente'];
            $this->ClaveGrupo = $rs['ClaveGrupo'];
            $this->EjecutivoCuenta = $rs['EjecutivoCuenta'];
            $this->NombreRazonSocial = $rs['NombreRazonSocial'];
            $this->RFC = $rs['RFC'];
            $this->Modalidad = $rs['Modalidad'];
            $this->CorreoElectronicoEnvioFact1 = $rs['CorreoElectronicoEnvioFact1'];
            $this->CorreoElectronicoEnvioFact2 = $rs['CorreoElectronicoEnvioFact2'];
            $this->CorreoElectronicoEnvioFact3 = $rs['CorreoElectronicoEnvioFact3'];
            $this->CorreoElectronicoEnvioFact4 = $rs['CorreoElectronicoEnvioFact4'];
            $this->ImprimirFactura = $rs['ImprimirFactura'];
            $this->HorarioEntrega = $rs['HorarioEntrega'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->Suspendido = $rs['Suspendido'];
            $this->IdTipoFacturacion = $rs['IdTipoFacturacion'];
            $this->IdTipoMorosidad = $rs['IdTipoMorosidad'];
            $this->GeneraFactura = $rs['GeneraFactura'];
            $this->DiasCredito = $rs['DiasCredito'];
            $this->NoVolverMoroso = $rs['NoVolverMoroso'];
            $this->MostarMesContrato = $rs['MostrarMesContrato'];
            $this->EsperaContrato = $rs['EsperaContrato'];
            return true;
        }
        return false;
    }

    public function getRegistroByRFC($rfc) {
        $consulta = ("SELECT * FROM c_cliente WHERE RFC ='" . $rfc . "' AND Activo = 1");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->ClaveCliente = $rs['ClaveCliente'];
            $this->IdEstatusCobranza = $rs['IdEstatusCobranza'];
            $this->IdGiro = $rs['IdGiro'];
            $this->ClaveZona = $rs['ClaveZona'];
            $this->IdDatosFacturacionEmpresa = $rs['IdDatosFacturacionEmpresa'];
            $this->IdTipoCliente = $rs['IdTipoCliente'];
            $this->ClaveGrupo = $rs['ClaveGrupo'];
            $this->EjecutivoCuenta = $rs['EjecutivoCuenta'];
            $this->NombreRazonSocial = $rs['NombreRazonSocial'];
            $this->RFC = $rs['RFC'];
            $this->Modalidad = $rs['Modalidad'];
            $this->CorreoElectronicoEnvioFact1 = $rs['CorreoElectronicoEnvioFact1'];
            $this->CorreoElectronicoEnvioFact2 = $rs['CorreoElectronicoEnvioFact2'];
            $this->CorreoElectronicoEnvioFact3 = $rs['CorreoElectronicoEnvioFact3'];
            $this->CorreoElectronicoEnvioFact4 = $rs['CorreoElectronicoEnvioFact4'];
            $this->ImprimirFactura = $rs['ImprimirFactura'];
            $this->HorarioEntrega = $rs['HorarioEntrega'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->Suspendido = $rs['Suspendido'];
            $this->IdTipoFacturacion = $rs['IdTipoFacturacion'];
            $this->IdTipoMorosidad = $rs['IdTipoMorosidad'];
            $this->GeneraFactura = $rs['GeneraFactura'];
            $this->DiasCredito = $rs['DiasCredito'];
            $this->NoVolverMoroso = $rs['NoVolverMoroso'];
            $this->MostarMesContrato = $rs['MostrarMesContrato'];
            $this->EsperaContrato = $rs['EsperaContrato'];
            return true;
        }
        return false;
    }

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_cliente WHERE ClaveCliente ='" . $id . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->ClaveCliente = $rs['ClaveCliente'];
            $this->IdEstatusCobranza = $rs['IdEstatusCobranza'];
            $this->IdGiro = $rs['IdGiro'];
            $this->ClaveZona = $rs['ClaveZona'];
            $this->IdDatosFacturacionEmpresa = $rs['IdDatosFacturacionEmpresa'];
            $this->IdTipoCliente = $rs['IdTipoCliente'];
            $this->ClaveGrupo = $rs['ClaveGrupo'];
            $this->EjecutivoCuenta = $rs['EjecutivoCuenta'];
            $this->NombreRazonSocial = $rs['NombreRazonSocial'];
            $this->RFC = $rs['RFC'];
            $this->Modalidad = $rs['Modalidad'];
            $this->CorreoElectronicoEnvioFact1 = $rs['CorreoElectronicoEnvioFact1'];
            $this->CorreoElectronicoEnvioFact2 = $rs['CorreoElectronicoEnvioFact2'];
            $this->CorreoElectronicoEnvioFact3 = $rs['CorreoElectronicoEnvioFact3'];
            $this->CorreoElectronicoEnvioFact4 = $rs['CorreoElectronicoEnvioFact4'];
            $this->ImprimirFactura = $rs['ImprimirFactura'];
            $this->HorarioEntrega = $rs['HorarioEntrega'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->Suspendido = $rs['Suspendido'];
            $this->IdTipoFacturacion = $rs['IdTipoFacturacion'];
            $this->IdTipoMorosidad = $rs['IdTipoMorosidad'];
            $this->GeneraFactura = $rs['GeneraFactura'];
            $this->DiasCredito = $rs['DiasCredito'];
            $this->NoVolverMoroso = $rs['NoVolverMoroso'];
            $this->MostarMesContrato = $rs['MostrarMesContrato'];
            $this->EsperaContrato = $rs['EsperaContrato'];
            if (isset($rs['Telefono'])) {
                $this->Telefono = $rs['Telefono'];
            }
            if (isset($rs['TamanoPerro'])) {
                $this->TamanoPerro = $rs['TamanoPerro'];
            }
            if (isset($rs['Foto'])) {
                $this->Foto = $rs['Foto'];
            }
            if (isset($rs['Email'])) {
                $this->Email = $rs['Email'];
            }
            if (isset($rs['Sitioweb'])) {
                $this->Sitioweb = $rs['Sitioweb'];
            }
            return true;
        }
        return false;
    }

    public function getNombreTipoCliente() {
        $consulta = ("SELECT Nombre FROM `c_tipocliente` WHERE IdTipoCliente = $this->IdTipoCliente;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query != null && $rs = mysql_fetch_array($query)) {
            return $rs['Nombre'];
        }
        return null;
    }

    public function newRegistroGuau() {
        $usuario_obj = new Usuario();
        if (isset($this->empresa)) {
            $usuario_obj->setEmpresa($this->empresa);
        }

        /* Obtenemos el vendedor que se pone por default segun los parametros globales */
        $parametro = new ParametroGlobal();
        if (isset($this->empresa)) {
            $parametro->setEmpresa($this->empresa);
        }

        if (isset($this->EjecutivoCuenta) && !empty($this->EjecutivoCuenta)) {
            $usuario = $this->EjecutivoCuenta;
        } else {

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

        /* Obtenemos la zona que se pone por default segun los parametros globales */
        if ($parametro->getRegistroById("2")) {
            $zona = $parametro->getValor();
        } else {
            $zona = "Z06";
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
            $consulta = "INSERT INTO c_cliente(ClaveCliente, IdDatosFacturacionEmpresa, NombreRazonSocial, RFC, Activo,UsuarioCreacion,
                    FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,IdEstatusCobranza,IdGiro,ClaveZona,IdTipoCliente,
                    ClaveGrupo,EjecutivoCuenta,CorreoElectronicoEnvioFact1,HorarioEntrega,ImprimirFactura, Modalidad, IdTipoFacturacion,IdTipoMorosidad, 
                    GeneraFactura,DiasCredito,NivelCliente,Telefono,TamanoPerro,Email,Sitioweb,Comentario,Foto,Calificacion) 
                    VALUES ('$maximo','$this->IdDatosFacturacionEmpresa','$this->NombreRazonSocial','$this->RFC'," . $this->Activo . ",
                    '" . $this->UsuarioCreacion . "',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla','" . $this->IdEstatusCobranza . "',
                    $this->IdGiro,'$zona',$this->IdTipoCliente,null," . $usuario . ",'',now(),0,$this->Modalidad, $this->IdTipoFacturacion,'" . $this->IdTipoMorosidad . "',
                    $this->GeneraFactura, $this->DiasCredito,$this->NivelCliente,'$this->Telefono',$this->TamanoPerro,'$this->Email','$this->Sitioweb',
                    '$this->comentario','$this->Foto',$this->Calificacion);";

            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                $this->ClaveCliente = $maximo;
                $catalogo->obtenerLista($consulta);
                $usuario_obj = new Usuario();
                if (isset($this->empresa)) {
                    $usuario_obj->setEmpresa($this->empresa);
                }
                if ($usuario_obj->isUsuarioPuesto($_SESSION['idUsuario'], 21)) {//Si el usuario que da de alta es un tfs, entonces se asocia el cliente al tfs
                    $tfs_cliente = new TFSCliente();
                    $tfs_cliente->setIdUsuario($_SESSION['idUsuario']);
                    $tfs_cliente->setClaveCliente($this->ClaveCliente);
                    $tfs_cliente->setTipo(1);
                    $tfs_cliente->setUsuarioCreacion($this->UsuarioCreacion);
                    $tfs_cliente->setUsuarioModificacion($this->UsuarioUltimaModificacion);
                    $tfs_cliente->setPantalla($this->Pantalla);
                    $tfs_cliente->newRegistro();
                }
                $this->newRegistroContacto();
                return true;
            }
        }
        return false;
    }

    public function newRegistro() {
        $usuario = new Usuario();
        
        $parametro = new ParametroGlobal();
        if (isset($this->empresa)) {
            $parametro->setEmpresa($this->empresa);
        }
        if (isset($_SESSION['idUsuario']) && $usuario->isUsuarioPuesto($_SESSION['idUsuario'], "11")) {
            $usuario = $_SESSION['idUsuario'];
        } else {
            /* Obtenemos el vendedor que se pone por default segun los parametros globales */
                        
            if ($parametro->getRegistroById("3")) {
                $usuario = $parametro->getValor();
            } else {
                $usuario = "4"; /* Si no existe sesion o el usuario no es vendedor, el cliente se agrega al usuario 4 (Gerardo beltran en genesis) */
            }
        }
        
        /* Obtenemos la zona que se pone por default segun los parametros globales */
        if ($parametro->getRegistroById("2")) {
            $zona = $parametro->getValor();
        } else {
            $zona = "Z06";
        }

        $modalidad = "1"; //Por default es de modalidad 1 (Arrendamiento)
        if (isset($this->Modalidad) && $this->Modalidad != "" && $this->Modalidad != "0") {
            $modalidad = $this->Modalidad;
        }

        $tipoFacturacion = "1";
        if (isset($this->IdTipoFacturacion) && $this->IdTipoFacturacion != "") {
            $tipoFacturacion = $this->IdTipoFacturacion;
        }

        $tipoCliente = "2";
        if (isset($this->IdTipoCliente) && $this->IdTipoCliente != "") {
            $tipoCliente = $this->IdTipoCliente;
        }

        if (!isset($this->GeneraFactura) || $this->GeneraFactura == "") {
            $this->GeneraFactura = 0;
        }

        if (!isset($this->DiasCredito) || $this->DiasCredito == "") {
            $this->DiasCredito = "null";
        }

        if (!isset($this->NivelCliente) || $this->NivelCliente == "") {
            $this->NivelCliente = "null";
        }

        if (!isset($this->IdGiro) || $this->IdGiro == "") {
            $this->IdGiro = "null";
        }

        if (!isset($this->MostarMesContrato) || empty($this->MostarMesContrato) || !is_numeric($this->MostarMesContrato)) {
            $this->MostarMesContrato = "0";
        }

        $consulta = ("SELECT MAX(CAST(ClaveCliente AS UNSIGNED)) AS maximo FROM `c_cliente`;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query2 = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query2)) {
            $maximo = (int) $rs['maximo'];
            if ($maximo == "" || $maximo == 0) {
                $maximo = 10001;
            }
            $maximo++;
            $consulta = "INSERT INTO c_cliente(ClaveCliente, IdDatosFacturacionEmpresa, NombreRazonSocial, RFC, Activo,UsuarioCreacion,
                    FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,IdEstatusCobranza,IdGiro,ClaveZona,IdTipoCliente,
                    ClaveGrupo,EjecutivoCuenta,CorreoElectronicoEnvioFact1,HorarioEntrega,ImprimirFactura, Modalidad, IdTipoFacturacion,IdTipoMorosidad, 
                    GeneraFactura,DiasCredito,NivelCliente,MostrarMesContrato) 
                    VALUES ('$maximo','$this->IdDatosFacturacionEmpresa','$this->NombreRazonSocial','$this->RFC'," . $this->Activo . ",
                    '" . $this->UsuarioCreacion . "',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla','" . $this->IdEstatusCobranza . "',
                    $this->IdGiro,'$zona',$tipoCliente,null," . $usuario . ",'',now(),0,$modalidad, $tipoFacturacion,'" . $this->IdTipoMorosidad . "',
                    $this->GeneraFactura, $this->DiasCredito,$this->NivelCliente,$this->MostarMesContrato);";

            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                $this->ClaveCliente = $maximo;
                $catalogo->obtenerLista($consulta);
                $usuario = new Usuario();
                if (isset($this->empresa)) {
                    $usuario->setEmpresa($this->empresa);
                }
                if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 21)) {//Si el usuario que da de alta es un tfs, entonces se asocia el cliente al tfs
                    $tfs_cliente = new TFSCliente();
                    $tfs_cliente->setIdUsuario($_SESSION['idUsuario']);
                    $tfs_cliente->setClaveCliente($this->ClaveCliente);
                    $tfs_cliente->setTipo(1);
                    $tfs_cliente->setUsuarioCreacion($this->UsuarioCreacion);
                    $tfs_cliente->setUsuarioModificacion($this->UsuarioUltimaModificacion);
                    $tfs_cliente->setPantalla($this->Pantalla);
                    $tfs_cliente->newRegistro();
                }
                $this->newRegistroContacto();
                return true;
            }
        }
        return false;
    }

    public function editRegistro() {
        $modalidad = "1"; //Por default es de modalidad 1 (Arrendamiento)
        if (isset($this->Modalidad) && $this->Modalidad != "" && $this->Modalidad != "0") {
            $modalidad = $this->Modalidad;
        }

        $tipoFacturacion = "1";
        if (isset($this->IdTipoFacturacion) && $this->IdTipoFacturacion != "") {
            $tipoFacturacion = $this->IdTipoFacturacion;
        }

        $tipoCliente = "2";
        if (isset($this->IdTipoCliente) && $this->IdTipoCliente != "") {
            $tipoCliente = $this->IdTipoCliente;
        }

        if (!isset($this->GeneraFactura) || $this->GeneraFactura == "") {
            $this->GeneraFactura = 0;
        }

        if (!isset($this->DiasCredito) || $this->DiasCredito == "") {
            $this->DiasCredito = "null";
        }

        if (!isset($this->NivelCliente) || $this->NivelCliente == "") {
            $this->NivelCliente = "null";
        }

        if (!isset($this->MostarMesContrato) || empty($this->MostarMesContrato) || !is_numeric($this->MostarMesContrato)) {
            $this->MostarMesContrato = "0";
        }

        $consulta = ("UPDATE c_cliente SET IdDatosFacturacionEmpresa = $this->IdDatosFacturacionEmpresa, Modalidad = $modalidad,
            NombreRazonSocial = '$this->NombreRazonSocial', RFC = '$this->RFC', IdTipoCliente = $tipoCliente, 
            IdTipoFacturacion = $tipoFacturacion, Activo = $this->Activo, GeneraFactura = $this->GeneraFactura, DiasCredito = $this->DiasCredito,
            NivelCliente = $this->NivelCliente,MostrarMesContrato = $this->MostarMesContrato,
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla', IdTipoMorosidad='" . $this->IdTipoMorosidad . "',IdEstatusCobranza='" . $this->IdEstatusCobranza . "'
            WHERE ClaveCliente = '$this->ClaveCliente';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getCalificacionById($id) {
        $consulta = ("SELECT * FROM `k_calificacioncliente` WHERE Id_calificacion = $id;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->Id_calificacion = $rs['Id_calificacion'];
            $this->ClaveCliente = $rs['ClaveCliente'];
            $this->Calificacion = $rs['Calificacion'];
            $this->Titulo = $rs['Titulo'];
            $this->Mensaje = $rs['Mensaje'];
            $this->Foto = $rs['Foto'];
            $this->IdUsuario = $rs['IdUsuario'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    public function agregarCalificacion($idUsuario) {
        $consulta = "INSERT INTO k_calificacioncliente(ClaveCliente,Calificacion,Titulo,Mensaje,Foto,IdUsuario,UsuarioCreacion,FechaCreacion,
            UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES('$this->ClaveCliente',$this->Calificacion,'$this->Titulo','$this->Mensaje','$this->Foto',$idUsuario,'$this->UsuarioCreacion',NOW(),
                '$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->Id_calificacion = $catalogo->insertarRegistro($consulta);
        if ($this->Id_calificacion != NULL && $this->Id_calificacion != "") {
            return true;
        }
        return false;
    }

    public function editCalificacion() {
        $consulta = ("UPDATE k_calificacioncliente SET ClaveCliente = '$this->ClaveCliente', Calificacion = $this->Calificacion, Titulo = '$this->Titulo',
            Mensaje = '$this->Mensaje',IdUsuario=$this->IdUsuario,
            UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion = NOW(),
            Pantalla = '" . $this->Pantalla . "' WHERE Id_calificacion=" . $this->Id_calificacion . ";");

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

    public function editJustCalificacion() {
        $consulta = ("UPDATE k_calificacioncliente SET Foto = '$this->Foto' WHERE Id_calificacion=" . $this->Id_calificacion . ";");

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

    function deleteCalificacion() {
        $consulta = ("DELETE FROM k_calificacioncliente WHERE Id_calificacion = " . $this->Id_calificacion . ";");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getCalificacionesCliente($limite) {
        if ($limite == NULL || $limite == 0) {
            $consulta = "select * from k_calificacioncliente where ClaveCliente = '$this->ClaveCliente' ORDER BY FechaCreacion;";
        } else {//Esta llamada se hace para pedir las imagenes
            $consulta = "select * from k_calificacioncliente where ClaveCliente = '$this->ClaveCliente' AND Foto <> '' ORDER BY FechaCreacion LIMIT 0,$limite;";
        }

        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    public function getCalificacionClientePorUsuario($idUsuario) {
        $consulta = "select Calificacion from k_calificacioncliente where ClaveCliente = '$this->ClaveCliente' AND IdUsuario = $idUsuario;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    public function cambiarEstatusCobranza() {
        $consulta = ("UPDATE c_cliente SET IdEstatusCobranza = $this->IdEstatusCobranza,
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla' 
            WHERE ClaveCliente = '$this->ClaveCliente';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function obtenerDatosClientesCorreo($cliente) {
        $consulta = ("SELECT c.ClaveCliente,c.NombreRazonSocial,u.IdUsuario,CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS ejecutivo,u.correo FROM c_cliente c,c_usuario u WHERE c.EjecutivoCuenta=u.IdUsuario AND  c.ClaveCliente='" . $cliente . "' ");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query != null && $rs = mysql_fetch_array($query)) {
            $this->ClaveCliente = $rs['ClaveCliente'];
            $this->NombreRazonSocial = $rs['NombreRazonSocial'];
            $this->nombreEjecutivo = $rs['ejecutivo'];
            $this->correoEjecutivo = $rs['correo'];
            return true;
        }
        return false;
    }

    public function deleteRegistro($id) {
        $consulta = "DELETE FROM `c_cliente` WHERE ClaveCliente = '$id';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getNombreClienteById() {
        $consulta = ("SELECT * FROM c_cliente c WHERE c.ClaveCliente='" . $this->ClaveCliente . "' ");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query != null && $rs = mysql_fetch_array($query)) {
            $this->NombreRazonSocial = $rs['NombreRazonSocial'];
            return true;
        }
        return false;
    }

    public function newRegistroContacto() {
        $consulta = ("INSERT INTO c_contacto (ClaveEspecialContacto,Nombre, Activo,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,
            FechaUltimaModificacion,Pantalla,CorreoElectronico,IdTipoContacto,Telefono,Celular) 
            VALUES('$this->ClaveCliente','Falta contacto',1,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla','falta@correo.com',8,'55555555','0');");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newDomicilioCliente() {
        if(!isset($this->noInterior) || $this->noInterior == ""){
            $this->noInterior = "0";
        }
        
        $consulta = ("INSERT INTO c_domicilio (ClaveEspecialDomicilio,IdTipoDomicilio,Calle,NoExterior, NoInterior,Colonia,Ciudad,Estado,Delegacion,Pais,CodigoPostal,ClaveZona,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES('$this->ClaveCliente','" . $this->idTipoDomicilio . "','" . $this->calle . "','" . $this->noExterior . "','" . $this->noInterior . "','" . $this->colonia . "','" . $this->ciudad . "','" . $this->estado . "','" . $this->delegacion . "','" . $this->pais . "','" . $this->cp . "','" . $this->ClaveZona . "',1,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla');");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    /**
     * Cambia el estado de Suspendido del cliente, false: No suspendido, true:Suspendido
     * @param type $estado true o false
     * @return boolean truen en caso de haber cambiado el estatus correctamente, false en caso contrario.
     */
    public function marcarComoSuspendidoRFC($estado) {
        if ($estado != true && $estado != false) {
            return false;
        }
        $consulta = "UPDATE `c_cliente` SET Suspendido = $estado WHERE RFC = '$this->RFC';";
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

    public function marcarFavorito() {
        $consulta = "INSERT INTO k_favoritocliente"
                . "(ClaveCliente, IdUsuario, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion, Pantalla) "
                . "VALUES('$this->ClaveCliente',$this->IdUsuario,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');";
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

    public function desmarcarFavorito() {
        $consulta = "DELETE FROM k_favoritocliente WHERE ClaveCliente = '$this->ClaveCliente' AND IdUsuario = $this->IdUsuario;";
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

    public function obtieneFavoritos() {
        $consulta = "SELECT ClaveCliente FROM k_favoritocliente WHERE IdUsuario = $this->IdUsuario;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function isFavorito($ClaveCliente) {
        $consulta = "SELECT ClaveCliente FROM k_favoritocliente WHERE IdUsuario = $this->IdUsuario AND ClaveCliente = '$ClaveCliente';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        $result = $catalogo->obtenerLista($consulta);

        if (mysql_num_rows($result) > 0) {
            return true;
        }
        return false;
    }
    
    public function getClientesByEjecutivoCuenta($IdEjecutivo) {
        $consulta = "SELECT ClaveCliente FROM `c_cliente` WHERE EjecutivoCuenta = $IdEjecutivo;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getClientesByEjecutivoAtencion($IdEjecutivo) {
        $consulta = "SELECT ClaveCliente FROM `c_cliente` WHERE EjecutivoAtencionCliente = $IdEjecutivo;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getClaveCliente() {
        return $this->ClaveCliente;
    }

    public function setClaveCliente($ClaveCliente) {
        $this->ClaveCliente = $ClaveCliente;
    }

    public function getIdEstatusCobranza() {
        return $this->IdEstatusCobranza;
    }

    public function setIdEstatusCobranza($IdEstatusCobranza) {
        $this->IdEstatusCobranza = $IdEstatusCobranza;
    }

    public function getIdGiro() {
        return $this->IdGiro;
    }

    public function setIdGiro($IdGiro) {
        $this->IdGiro = $IdGiro;
    }

    public function getClaveZona() {
        return $this->ClaveZona;
    }

    public function setClaveZona($ClaveZona) {
        $this->ClaveZona = $ClaveZona;
    }

    public function getIdDatosFacturacionEmpresa() {
        return $this->IdDatosFacturacionEmpresa;
    }

    public function setIdDatosFacturacionEmpresa($IdDatosFacturacionEmpresa) {
        $this->IdDatosFacturacionEmpresa = $IdDatosFacturacionEmpresa;
    }

    public function getIdTipoCliente() {
        return $this->IdTipoCliente;
    }

    public function setIdTipoCliente($IdTipoCliente) {
        $this->IdTipoCliente = $IdTipoCliente;
    }

    public function getClaveGrupo() {
        return $this->ClaveGrupo;
    }

    public function setClaveGrupo($ClaveGrupo) {
        $this->ClaveGrupo = $ClaveGrupo;
    }

    public function getEjecutivoCuenta() {
        return $this->EjecutivoCuenta;
    }

    public function setEjecutivoCuenta($EjecutivoCuenta) {
        $this->EjecutivoCuenta = $EjecutivoCuenta;
    }

    public function getNombreRazonSocial() {
        return $this->NombreRazonSocial;
    }

    public function setNombreRazonSocial($NombreRazonSocial) {
        $this->NombreRazonSocial = $NombreRazonSocial;
    }

    public function getRFC() {
        return $this->RFC;
    }

    public function setRFC($RFC) {
        $this->RFC = $RFC;
    }

    public function getCorreoElectronicoEnvioFact1() {
        return $this->CorreoElectronicoEnvioFact1;
    }

    public function setCorreoElectronicoEnvioFact1($CorreoElectronicoEnvioFact1) {
        $this->CorreoElectronicoEnvioFact1 = $CorreoElectronicoEnvioFact1;
    }

    public function getCorreoElectronicoEnvioFact2() {
        return $this->CorreoElectronicoEnvioFact2;
    }

    public function setCorreoElectronicoEnvioFact2($CorreoElectronicoEnvioFact2) {
        $this->CorreoElectronicoEnvioFact2 = $CorreoElectronicoEnvioFact2;
    }

    public function getCorreoElectronicoEnvioFact3() {
        return $this->CorreoElectronicoEnvioFact3;
    }

    public function setCorreoElectronicoEnvioFact3($CorreoElectronicoEnvioFact3) {
        $this->CorreoElectronicoEnvioFact3 = $CorreoElectronicoEnvioFact3;
    }

    public function getCorreoElectronicoEnvioFact4() {
        return $this->CorreoElectronicoEnvioFact4;
    }

    public function setCorreoElectronicoEnvioFact4($CorreoElectronicoEnvioFact4) {
        $this->CorreoElectronicoEnvioFact4 = $CorreoElectronicoEnvioFact4;
    }

    public function getImprimirFactura() {
        return $this->ImprimirFactura;
    }

    public function setImprimirFactura($ImprimirFactura) {
        $this->ImprimirFactura = $ImprimirFactura;
    }

    public function getHorarioEntrega() {
        return $this->HorarioEntrega;
    }

    public function setHorarioEntrega($HorarioEntrega) {
        $this->HorarioEntrega = $HorarioEntrega;
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

    public function getCorreoEjecutivo() {
        return $this->correoEjecutivo;
    }

    public function setCorreoEjecutivo($correoEjecutivo) {
        $this->correoEjecutivo = $correoEjecutivo;
    }

    public function getNombreEjecutivo() {
        return $this->nombreEjecutivo;
    }

    public function setNombreEjecutivo($nombreEjecutivo) {
        $this->nombreEjecutivo = $nombreEjecutivo;
    }

    public function getCalle() {
        return $this->calle;
    }

    public function setCalle($calle) {
        $this->calle = $calle;
    }

    public function getIdTipoDomicilio() {
        return $this->idTipoDomicilio;
    }

    public function setIdTipoDomicilio($idTipoDomicilio) {
        $this->idTipoDomicilio = $idTipoDomicilio;
    }

    public function getNoExterior() {
        return $this->noExterior;
    }

    public function setNoExterior($noExterior) {
        $this->noExterior = $noExterior;
    }

    public function getNoInterior() {
        return $this->noInterior;
    }

    public function setNoInterior($noInterior) {
        $this->noInterior = $noInterior;
    }

    public function getColonia() {
        return $this->colonia;
    }

    public function setColonia($colonia) {
        $this->colonia = $colonia;
    }

    public function getCiudad() {
        return $this->ciudad;
    }

    public function setCiudad($ciudad) {
        $this->ciudad = $ciudad;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function getDelegacion() {
        return $this->delegacion;
    }

    public function setDelegacion($delegacion) {
        $this->delegacion = $delegacion;
    }

    public function getPais() {
        return $this->pais;
    }

    public function setPais($pais) {
        $this->pais = $pais;
    }

    public function getCp() {
        return $this->cp;
    }

    public function setCp($cp) {
        $this->cp = $cp;
    }

    public function getSuspendido() {
        return $this->Suspendido;
    }

    public function setSuspendido($Suspendido) {
        $this->Suspendido = $Suspendido;
    }

    public function getModalidad() {
        return $this->Modalidad;
    }

    public function setModalidad($Modalidad) {
        $this->Modalidad = $Modalidad;
    }

    public function getIdTipoFacturacion() {
        return $this->IdTipoFacturacion;
    }

    public function setIdTipoFacturacion($IdTipoFacturacion) {
        $this->IdTipoFacturacion = $IdTipoFacturacion;
    }

    public function getIdTipoMorosidad() {
        return $this->IdTipoMorosidad;
    }

    public function setIdTipoMorosidad($IdTipoMorosidad) {
        $this->IdTipoMorosidad = $IdTipoMorosidad;
    }

    public function getGeneraFactura() {
        return $this->GeneraFactura;
    }

    public function setGeneraFactura($GeneraFactura) {
        $this->GeneraFactura = $GeneraFactura;
    }

    public function getDiasCredito() {
        return $this->DiasCredito;
    }

    public function setDiasCredito($DiasCredito) {
        $this->DiasCredito = $DiasCredito;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function getNoVolverMoroso() {
        return $this->NoVolverMoroso;
    }

    public function setNoVolverMoroso($NoVolverMoroso) {
        $this->NoVolverMoroso = $NoVolverMoroso;
    }

    public function getNivelCliente() {
        return $this->NivelCliente;
    }

    public function setNivelCliente($NivelCliente) {
        $this->NivelCliente = $NivelCliente;
    }

    public function getCalificacion() {
        return $this->Calificacion;
    }

    public function setCalificacion($Calificacion) {
        $this->Calificacion = $Calificacion;
    }

    public function getMensaje() {
        return $this->Mensaje;
    }

    public function setMensaje($Mensaje) {
        $this->Mensaje = $Mensaje;
    }

    public function getTitulo() {
        return $this->Titulo;
    }

    public function setTitulo($Titulo) {
        $this->Titulo = $Titulo;
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

    public function getComentario() {
        return $this->comentario;
    }

    public function setComentario($comentario) {
        $this->comentario = $comentario;
    }

    public function getFoto() {
        return $this->Foto;
    }

    public function setFoto($Foto) {
        $this->Foto = $Foto;
    }

    public function getId_calificacion() {
        return $this->Id_calificacion;
    }

    public function setId_calificacion($Id_calificacion) {
        $this->Id_calificacion = $Id_calificacion;
    }

    public function getIdUsuario() {
        return $this->IdUsuario;
    }

    public function setIdUsuario($IdUsuario) {
        $this->IdUsuario = $IdUsuario;
    }

    function getMostarMesContrato() {
        return $this->MostarMesContrato;
    }

    function setMostarMesContrato($MostarMesContrato) {
        $this->MostarMesContrato = $MostarMesContrato;
    }

    function getEsperaContrato() {
        return $this->EsperaContrato;
    }

    function setEsperaContrato($EsperaContrato) {
        $this->EsperaContrato = $EsperaContrato;
    }

}

?>

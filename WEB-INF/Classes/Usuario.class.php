<?php

include_once("Catalogo.class.php");
include_once("ConexionMultiBD.class.php");

/**
 * Description of Usuario
 *
 * @author MAGG
 */
class Usuario {

    private $id;
    private $usuario;
    private $nombre;
    private $paterno;
    private $materno;
    private $email;
    private $password;
    private $puesto;
    private $activo;
    private $usuarioCreacion;
    private $fechaCreacion;
    private $UsuarioModificacion;
    private $fechaModificacion;
    private $pantalla;
    private $idAlmacen;
    private $idUsuarioMultiBD;
    private $Telefono;
    private $Sexo;
    private $RFC;
    private $FechaNacimiento;
    private $CostoFijo;
    private $IdFormaPago;
    private $PorcentajeDesc;
    private $empresa;
    private $ProveedorFactura;

    public function getUsuarios() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("SELECT * FROM `c_usuario` ORDER BY Loggin");
        return $query;
    }

    /**
     * Obtiene el usuario por la llave user-password
     * @param type $user
     * @param type $password
     * @return boolean
     */
    public function getUsuarioByUserPassWord($user, $password) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("SELECT * FROM `c_usuario` WHERE Loggin = '$user' AND Password2 = '$password';");
        while ($rs = mysql_fetch_array($query)) {
            $this->id = $rs['IdUsuario'];
            $this->usuario = $rs['Loggin'];
            $this->nombre = $rs['Nombre'];
            $this->paterno = $rs['ApellidoPaterno'];
            $this->materno = $rs['ApellidoMaterno'];
            $this->email = $rs['correo'];
            $this->password = $rs['Password2'];
            $this->puesto = $rs['IdPuesto'];
            $this->activo = $rs['Activo'];
            $this->idAlmacen = $rs['IdAlmacen'];
            $this->CostoFijo = $rs['CostoFijo'];
            $this->IdFormaPago = $rs['IdFormaPago'];
            $this->PorcentajeDesc = $rs['PorcentajeDesc'];
            $this->ProveedorFactura = $rs['ProveedorFactura'];
            /* Se agrega primero el usuario en la base multiBD */
            $conexionMulti = new ConexionMultiBD();
            $consulta = "SELECT id_usuario FROM `c_usuario` WHERE Loggin = '$this->usuario' AND `Password` = '$this->password';";
            $result = mysql_query($consulta);
            $conexionMulti->Desconectar();
            while ($rs2 = mysql_fetch_array($result)) {
                $this->idUsuarioMultiBD = $rs2['id_usuario'];
            }
            return true;
        }
        return false;
    }

    public function getRegistroById($id) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("SELECT * FROM `c_usuario` WHERE IdUsuario = " . $id . ";");
        while ($rs = mysql_fetch_array($query)) {
            $this->id = $id;
            $this->usuario = $rs['Loggin'];
            $this->nombre = $rs['Nombre'];
            $this->paterno = $rs['ApellidoPaterno'];
            $this->materno = $rs['ApellidoMaterno'];
            $this->email = $rs['correo'];
            $this->Telefono = $rs['Telefono'];
            $this->password = $rs['Password2'];
            $this->puesto = $rs['IdPuesto'];
            $this->activo = $rs['Activo'];
            $this->ProveedorFactura = $rs['ProveedorFactura'];
            $this->idAlmacen = $rs['IdAlmacen'];
            $this->RFC = $rs['RFC'];
            $this->CostoFijo = $rs['CostoFijo'];
            $this->IdFormaPago = $rs['IdFormaPago'];
            $this->PorcentajeDesc = $rs['PorcentajeDesc'];
            /* Se selecciona el usuario en la base multiBD */
            $conexionMulti = new ConexionMultiBD();
            $consulta = "SELECT id_usuario FROM `c_usuario` WHERE Loggin = '$this->usuario' AND `Password` = '$this->password';";
            $result = mysql_query($consulta);
            $conexionMulti->Desconectar();
            while ($rs2 = mysql_fetch_array($result)) {
                $this->idUsuarioMultiBD = $rs2['id_usuario'];
            }
            return true;
        }
        return false;
    }

    /**
     * Obtiene el usuario por la llave user-password
     * @param type $user
     * @param type $password
     * @return boolean
     */
    public function getUsuarioByUser($user) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("SELECT * FROM `c_usuario` WHERE Loggin = '$user';");

        while ($rs = mysql_fetch_array($query)) {
            $this->id = $rs['IdUsuario'];
            $this->usuario = $rs['Loggin'];
            $this->nombre = $rs['Nombre'];
            $this->paterno = $rs['ApellidoPaterno'];
            $this->materno = $rs['ApellidoMaterno'];
            $this->email = $rs['correo'];
            $this->password = $rs['Password2'];
            $this->puesto = $rs['IdPuesto'];
            $this->activo = $rs['Activo'];
            $this->ProveedorFactura = $rs['ProveedorFactura'];
            $this->idAlmacen = $rs['IdAlmacen'];
            $this->CostoFijo = $rs['CostoFijo'];
            $this->IdFormaPago = $rs['IdFormaPago'];
            $this->PorcentajeDesc = $rs['PorcentajeDesc'];
            /* Se agrega primero el usuario en la base multiBD */
            $conexionMulti = new ConexionMultiBD();
            $consulta = "SELECT id_usuario FROM `c_usuario` WHERE Loggin = '$this->usuario' AND `Password` = '$this->password';";
            $result = mysql_query($consulta);
            $conexionMulti->Desconectar();
            while ($rs2 = mysql_fetch_array($result)) {
                $this->idUsuarioMultiBD = $rs2['id_usuario'];
            }
            return true;
        }
        return false;
    }
    
    public function getRegistroByEmail($correo) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("SELECT * FROM `c_usuario` WHERE correo = TRIM('$correo');");
        while ($rs = mysql_fetch_array($query)) {
            $this->id = $rs['IdUsuario'];
            $this->usuario = $rs['Loggin'];
            $this->nombre = $rs['Nombre'];
            $this->paterno = $rs['ApellidoPaterno'];
            $this->materno = $rs['ApellidoMaterno'];
            $this->email = $rs['correo'];
            $this->password = $rs['Password2'];
            $this->puesto = $rs['IdPuesto'];
            $this->activo = $rs['Activo'];
            $this->ProveedorFactura = $rs['ProveedorFactura'];
            $this->idAlmacen = $rs['IdAlmacen'];
            $this->CostoFijo = $rs['CostoFijo'];
            $this->IdFormaPago = $rs['IdFormaPago'];
            $this->PorcentajeDesc = $rs['PorcentajeDesc'];
            /* Se agrega primero el usuario en la base multiBD */
            $conexionMulti = new ConexionMultiBD();
            $consulta = "SELECT id_usuario FROM `c_usuario` WHERE Loggin = '$this->usuario' AND `Password` = '$this->password';";
            $result = mysql_query($consulta);
            $conexionMulti->Desconectar();
            while ($rs2 = mysql_fetch_array($result)) {
                $this->idUsuarioMultiBD = $rs2['id_usuario'];
            }
            return true;
        }
        return false;
    }

    public function getRegistroByRFC($rfc) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("SELECT * FROM `c_usuario` WHERE RFC = TRIM('$rfc');");
        while ($rs = mysql_fetch_array($query)) {
            $this->id = $rs['IdUsuario'];
            $this->usuario = $rs['Loggin'];
            $this->nombre = $rs['Nombre'];
            $this->paterno = $rs['ApellidoPaterno'];
            $this->materno = $rs['ApellidoMaterno'];
            $this->email = $rs['correo'];
            $this->password = $rs['Password2'];
            $this->puesto = $rs['IdPuesto'];
            $this->ProveedorFactura = $rs['ProveedorFactura'];
            $this->activo = $rs['Activo'];
            $this->idAlmacen = $rs['IdAlmacen'];
            $this->CostoFijo = $rs['CostoFijo'];
            $this->IdFormaPago = $rs['IdFormaPago'];
            $this->PorcentajeDesc = $rs['PorcentajeDesc'];
            $this->RFC = $rs['RFC'];
            /* Se agrega primero el usuario en la base multiBD */
            $conexionMulti = new ConexionMultiBD();
            $consulta = "SELECT id_usuario FROM `c_usuario` WHERE Loggin = '$this->usuario' AND `Password` = '$this->password';";
            $result = mysql_query($consulta);
            $conexionMulti->Desconectar();
            while ($rs2 = mysql_fetch_array($result)) {
                $this->idUsuarioMultiBD = $rs2['id_usuario'];
            }
            return true;
        }
        return false;
    }

    public function newRegistro() {
        /* Se agrega primero el usuario en la base multiBD */
        $conexionMulti = new ConexionMultiBD();
        if (isset($this->empresa)) {
            $empresa = $this->empresa;
        } else {
            $empresa = $_SESSION['idEmpresa'];
        }
        $consulta = "INSERT INTO c_usuario(id_usuario, id_empresa, Loggin, `Password`, Activo, FechaCreacion, FechaModificacion) 
            VALUES(0,$empresa,'$this->usuario',MD5('$this->password'),$this->activo,NOW(),NOW());";
        $result = mysql_query($consulta);
        $conexionMulti->Desconectar();

        if ($result == "1") {//Si se inserta correctamente el usuario en la MultiBD
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            if (!isset($this->Telefono) || $this->Telefono == "") {
                $this->Telefono = "NULL";
            }
            if(!isset($this->CostoFijo) || $this->CostoFijo == ""){
                $this->CostoFijo = "NULL";
            }
            if(!isset($this->IdFormaPago) || $this->IdFormaPago == ""){
                $this->IdFormaPago = "NULL";
            }
            if(!isset($this->PorcentajeDesc) || $this->PorcentajeDesc == ""){
                $this->PorcentajeDesc = "0";
            }
            if(!isset($this->ProveedorFactura) || $this->ProveedorFactura == ""){
                $this->ProveedorFactura = "NULL";
            }else{
                $this->ProveedorFactura = "'$this->ProveedorFactura'";
            }
            if ($this->idAlmacen == "0" || $this->idAlmacen == "") {
                $consulta = ("INSERT INTO c_usuario(IdUsuario,IdPuesto,Nombre,ApellidoPaterno,ApellidoMaterno,
                Loggin,Password2,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,
                Pantalla,correo,Telefono,Password,RFC,CostoFijo,IdFormaPago,PorcentajeDesc, ProveedorFactura) 
                VALUES(0,$this->puesto,'$this->nombre','$this->paterno','$this->materno','$this->usuario',
                MD5('$this->password'),$this->activo,'$this->usuarioCreacion',now(),'$this->UsuarioModificacion',now(),'$this->pantalla',
                '$this->email','$this->Telefono',MD5('$this->password'),'$this->RFC',$this->CostoFijo,$this->IdFormaPago,$this->PorcentajeDesc, $this->ProveedorFactura)");
            } else {
                $consulta = ("INSERT INTO c_usuario(IdUsuario,IdPuesto,Nombre,ApellidoPaterno,ApellidoMaterno,
                Loggin,Password2,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,
                Pantalla,correo,Telefono,Password,IdAlmacen,RFC,CostoFijo,IdFormaPago,PorcentajeDesc, ProveedorFactura) 
                VALUES(0,$this->puesto,'$this->nombre','$this->paterno','$this->materno','$this->usuario',
                MD5('$this->password'),$this->activo,'$this->usuarioCreacion',now(),'$this->UsuarioModificacion',now(),'$this->pantalla','$this->email',
                '$this->Telefono',MD5('$this->password'),'" . $this->idAlmacen . "','$this->RFC',$this->CostoFijo,$this->IdFormaPago,$this->PorcentajeDesc, $this->ProveedorFactura)");
            }
            $this->id = $catalogo->insertarRegistro($consulta);
            if ($this->id != null && $this->id != 0) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function newRegistroSinEcriptar() {
        /* Se agrega primero el usuario en la base multiBD */
        $conexionMulti = new ConexionMultiBD();
        if (isset($this->empresa)) {
            $empresa = $this->empresa;
        } else {
            $empresa = $_SESSION['idEmpresa'];
        }
        $consulta = "INSERT INTO c_usuario(id_usuario, id_empresa, Loggin, `Password`, Activo, FechaCreacion, FechaModificacion) 
            VALUES(0,$empresa,'$this->usuario',TRIM('$this->password'),$this->activo,NOW(),NOW());";

        $result = mysql_query($consulta);
        $conexionMulti->Desconectar();

        if ($result == "1") {//Si se inserta correctamente el usuario en la MultiBD
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }

            if (!isset($this->Sexo) || $this->Sexo == "") {
                $this->Sexo = "NULL";
            }
            
            if(!isset($this->CostoFijo) || $this->CostoFijo == ""){
                $this->CostoFijo = "NULL";
            }
            
            if(!isset($this->IdFormaPago) || $this->IdFormaPago == ""){
                $this->IdFormaPago = "NULL";
            }
            
            if(!isset($this->PorcentajeDesc) || $this->PorcentajeDesc == ""){
                $this->PorcentajeDesc = "0";
            }

            if ($this->idAlmacen == "0" || $this->idAlmacen == "") {
                $consulta = ("INSERT INTO c_usuario(IdUsuario,IdPuesto,Nombre,ApellidoPaterno,ApellidoMaterno,
                    Telefono, Sexo, FechaNacimiento,
                    Loggin,Password2,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,
                    Pantalla,correo,Password,CostoFijo,IdFormaPago,PorcentajeDesc) 
                    VALUES(0,$this->puesto,'$this->nombre','$this->paterno','$this->materno',"
                        . "'$this->Telefono',$this->Sexo,'$this->FechaNacimiento',"
                        . "'$this->usuario',
                    TRIM('$this->password'),$this->activo,'$this->usuarioCreacion',now(),'$this->UsuarioModificacion',now(),'$this->pantalla',
                    '$this->email',TRIM('$this->password'),$this->CostoFijo,$this->IdFormaPago,$this->PorcentajeDesc)");
            } else {
                $consulta = ("INSERT INTO c_usuario(IdUsuario,IdPuesto,Nombre,ApellidoPaterno,ApellidoMaterno,
                    Telefono, Sexo, FechaNacimiento,
                    Loggin,Password2,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,
                    Pantalla,correo,Password,IdAlmacen,CostoFijo,IdFormaPago,PorcentajeDesc) 
                    VALUES(0,$this->puesto,'$this->nombre','$this->paterno','$this->materno',"
                        . "'$this->Telefono',$this->Sexo,'$this->FechaNacimiento',"
                        . "'$this->usuario',
                    TRIM('$this->password'),$this->activo,'$this->usuarioCreacion',now(),'$this->UsuarioModificacion',now(),'$this->pantalla',
                    '$this->email',TRIM('$this->password'),'" . $this->idAlmacen . "',$this->CostoFijo, $this->IdFormaPago, $this->PorcentajeDesc)");
            }

            $this->id = $catalogo->insertarRegistro($consulta);
            if ($this->id != null && $this->id != 0) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function editarRegistroConPasswordSinEcriptar() {
        /* Se agrega primero el usuario en la base multiBD */
        $conexionMulti = new ConexionMultiBD();
        $consulta = "UPDATE c_usuario SET Loggin = '$this->usuario', `Password` = TRIM('" . $this->password . "'), Activo = $this->activo, FechaModificacion = NOW()
            WHERE id_usuario = $this->idUsuarioMultiBD";
        $result = mysql_query($consulta);
        $conexionMulti->Desconectar();
        if ($result == "1") {//Si se inserta correctamente el usuario en la MultiBD
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }

            if (!isset($this->Sexo) || $this->Sexo == "") {
                $this->Sexo = "NULL";
            }
            
            if(!isset($this->CostoFijo) || $this->CostoFijo == ""){
                $this->CostoFijo = "NULL";
            }
            
            if(!isset($this->IdFormaPago) || $this->IdFormaPago == ""){
                $this->IdFormaPago = "NULL";
            }
            
            if(!isset($this->PorcentajeDesc) || $this->PorcentajeDesc == ""){
                $this->PorcentajeDesc = "0";
            }

            if ($this->idAlmacen == "0" || $this->idAlmacen == "") {
                $consulta = "UPDATE c_usuario SET Loggin = '" . $this->usuario . "', nombre = '" . $this->nombre . "',
                            ApellidoPaterno = '" . $this->paterno . "',ApellidoMaterno = '" . $this->materno . "',PorcentajeDesc=$this->PorcentajeDesc,
                            correo = '" . $this->email . "', IdPuesto = $this->puesto,CostoFijo=$this->CostoFijo,IdFormaPago=$this->IdFormaPago,
                            Activo = $this->activo, UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), 
                            Telefono = '$this->Telefono', Sexo = $this->Sexo, FechaNacimiento = '$this->FechaNacimiento',
                            Password2 = TRIM('" . $this->password . "'),Password = TRIM('" . $this->password . "'),Pantalla = '$this->pantalla',IdAlmacen=NULL WHERE IdUsuario = " . $this->id . ";";                
                $query = $catalogo->obtenerLista($consulta);
            } else {
                $consulta = "UPDATE c_usuario SET Loggin = '" . $this->usuario . "', nombre = '" . $this->nombre . "',
                            ApellidoPaterno = '" . $this->paterno . "',ApellidoMaterno = '" . $this->materno . "',PorcentajeDesc=$this->PorcentajeDesc,
                            correo = '" . $this->email . "', IdPuesto = $this->puesto, CostoFijo=$this->CostoFijo,IdFormaPago=$this->IdFormaPago,
                            Telefono = '$this->Telefono', Sexo = $this->Sexo, FechaNacimiento = '$this->FechaNacimiento',
                            Activo = $this->activo, UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), 
                            Password2 = TRIM('" . $this->password . "'),Password = TRIM('" . $this->password . "'),Pantalla = '$this->pantalla',IdAlmacen='" . $this->idAlmacen . "' WHERE IdUsuario = " . $this->id . ";";                
                $query = $catalogo->obtenerLista($consulta);
            }

            if ($query == 1) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function editRegistro() {
        /* Se agrega primero el usuario en la base multiBD */
        $conexionMulti = new ConexionMultiBD();
        $consulta = "UPDATE c_usuario SET Loggin = '$this->usuario', Activo = $this->activo, FechaModificacion = NOW()
            WHERE id_usuario = $this->idUsuarioMultiBD";
        $result = mysql_query($consulta);
        $conexionMulti->Desconectar();
        if ($result == "1") {//Si se inserta correctamente el usuario en la MultiBD
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            if (!isset($this->Telefono) || $this->Telefono == "") {
                $this->Telefono = "NULL";
            }
            if(!isset($this->CostoFijo) || $this->CostoFijo == ""){
                $this->CostoFijo = "NULL";
            }
            if(!isset($this->IdFormaPago) || $this->IdFormaPago == ""){
                $this->IdFormaPago = "NULL";
            }
            if(!isset($this->PorcentajeDesc) || $this->PorcentajeDesc == ""){
                $this->PorcentajeDesc = "0";
            }
            if(!isset($this->ProveedorFactura) || $this->ProveedorFactura == ""){
                $this->ProveedorFactura = "NULL";
            }else{
                $this->ProveedorFactura = "'$this->ProveedorFactura'";
            }
            
            $rfc = "";
            if(isset($this->RFC)){
                $rfc = " RFC = '$this->RFC', ";
            }
            if ($this->idAlmacen == "0" || $this->idAlmacen == "") {
                $consulta = "UPDATE c_usuario SET Loggin = '" . $this->usuario . "', nombre = '" . $this->nombre . "', $rfc
                            ApellidoPaterno = '" . $this->paterno . "',ApellidoMaterno = '" . $this->materno . "',IdFormaPago=$this->IdFormaPago,
                            PorcentajeDesc=$this->PorcentajeDesc,
                            correo = '" . $this->email . "',Telefono = '" . $this->Telefono . "', IdPuesto = $this->puesto,CostoFijo=$this->CostoFijo, 
                            Activo = $this->activo, UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), 
                            Pantalla = '$this->pantalla',IdAlmacen=NULL, ProveedorFactura = $this->ProveedorFactura WHERE IdUsuario = " . $this->id . ";";                
                $query = $catalogo->obtenerLista($consulta);
            } else {
                $consulta = "UPDATE c_usuario SET Loggin = '" . $this->usuario . "', nombre = '" . $this->nombre . "', $rfc
                            ApellidoPaterno = '" . $this->paterno . "',ApellidoMaterno = '" . $this->materno . "',IdFormaPago=$this->IdFormaPago,PorcentajeDesc=$this->PorcentajeDesc,
                            correo = '" . $this->email . "', Telefono = '" . $this->Telefono . "', IdPuesto = $this->puesto, CostoFijo=$this->CostoFijo,
                            Activo = $this->activo, UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), 
                            Pantalla = '$this->pantalla',IdAlmacen='" . $this->idAlmacen . "', ProveedorFactura = $this->ProveedorFactura WHERE IdUsuario = " . $this->id . ";";                
                $query = $catalogo->obtenerLista($consulta);
            }

            if ($query == 1) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function editarRegistroConPassword() {
        /* Se agrega primero el usuario en la base multiBD */
        $conexionMulti = new ConexionMultiBD();
        $consulta = "UPDATE c_usuario SET Loggin = '$this->usuario', `Password` = MD5('" . $this->password . "'), Activo = $this->activo, FechaModificacion = NOW()
            WHERE id_usuario = $this->idUsuarioMultiBD";
        $result = mysql_query($consulta);
        $conexionMulti->Desconectar();
        if ($result == "1") {//Si se inserta correctamente el usuario en la MultiBD
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            if(!isset($this->CostoFijo) || $this->CostoFijo == ""){
                $this->CostoFijo = "NULL";
            }
            if(!isset($this->IdFormaPago) || $this->IdFormaPago == ""){
                $this->IdFormaPago = "NULL";
            }
            if(!isset($this->PorcentajeDesc) || $this->PorcentajeDesc == ""){
                $this->PorcentajeDesc = "0";
            }
            $rfc = "";
            if(isset($this->RFC)){
                $rfc = " RFC = '$this->RFC', ";
            }
            if ($this->idAlmacen == "0" || $this->idAlmacen == "") {
                $query = $catalogo->obtenerLista("UPDATE c_usuario SET Loggin = '" . $this->usuario . "', nombre = '" . $this->nombre . "',$rfc
                            ApellidoPaterno = '" . $this->paterno . "',ApellidoMaterno = '" . $this->materno . "',PorcentajeDesc=$this->PorcentajeDesc, 
                            correo = '" . $this->email . "', IdPuesto = $this->puesto,CostoFijo=$this->CostoFijo,IdFormaPago=$this->IdFormaPago,
                            Activo = $this->activo, UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), 
                            Password2 = MD5('" . $this->password . "'),Password = MD5('" . $this->password . "'),Pantalla = '$this->pantalla',IdAlmacen=NULL WHERE IdUsuario = " . $this->id . ";");
            } else {
                $query = $catalogo->obtenerLista("UPDATE c_usuario SET Loggin = '" . $this->usuario . "', nombre = '" . $this->nombre . "',$rfc
                            ApellidoPaterno = '" . $this->paterno . "',ApellidoMaterno = '" . $this->materno . "',PorcentajeDesc=$this->PorcentajeDesc, 
                            correo = '" . $this->email . "', IdPuesto = $this->puesto, CostoFijo=$this->CostoFijo,IdFormaPago=$this->IdFormaPago,
                            Activo = $this->activo, UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), 
                            Password2 = MD5('" . $this->password . "'),Password = MD5('" . $this->password . "'),Pantalla = '$this->pantalla',IdAlmacen='" . $this->idAlmacen . "' WHERE IdUsuario = " . $this->id . ";");
            }

            if ($query == 1) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function editarRegistroSimple() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        if ($this->idAlmacen == "0" || $this->idAlmacen == "") {
            $consulta = ("UPDATE c_usuario SET Loggin = '" . $this->usuario . "', nombre = '" . $this->nombre . "',
			ApellidoPaterno = '" . $this->paterno . "',ApellidoMaterno = '" . $this->materno . "', 
			correo = TRIM('" . $this->email . "'),UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), 
                        Pantalla = '$this->pantalla',IdAlmacen=NULL WHERE IdUsuario = " . $this->id . ";");
        } else {
            $consulta = ("UPDATE c_usuario SET Loggin = '" . $this->usuario . "', nombre = '" . $this->nombre . "',
			ApellidoPaterno = '" . $this->paterno . "',ApellidoMaterno = '" . $this->materno . "', 
			correo = TRIM('" . $this->email . "'),UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), 
                        Pantalla = '$this->pantalla',IdAlmacen='" . $this->idAlmacen . "' WHERE IdUsuario = " . $this->id . ";");
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editarRegistroSimplePassword() {
        $user = new Usuario(); //Usuario MultiBD
        $user->getRegistroById($this->id);
        /* Se agrega primero el usuario en la base multiBD */
        $conexionMulti = new ConexionMultiBD();
        $consulta = "UPDATE c_usuario SET Loggin = '$this->usuario', `Password` = MD5('" . $this->password . "'), FechaModificacion = NOW()
            WHERE id_usuario = " . $user->getIdUsuarioMultiBD();
        $result = mysql_query($consulta);
        $conexionMulti->Desconectar();
        if ($result == "1") {//Si se inserta correctamente el usuario en la MultiBD
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            if ($this->idAlmacen == "0" || $this->idAlmacen == "") {
                $consulta = "UPDATE c_usuario SET Loggin = '" . $this->usuario . "', nombre = '" . $this->nombre . "',
                            ApellidoPaterno = '" . $this->paterno . "',ApellidoMaterno = '" . $this->materno . "', 
                            correo = '" . $this->email . "', UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), 
                            Password2 = MD5('" . $this->password . "'),Password = MD5('" . $this->password . "'),Pantalla = '$this->pantalla',IdAlmacen=NULL  WHERE IdUsuario = " . $this->id . ";";
            } else {
                $consulta = ("UPDATE c_usuario SET Loggin = '" . $this->usuario . "', nombre = '" . $this->nombre . "',
                            ApellidoPaterno = '" . $this->paterno . "',ApellidoMaterno = '" . $this->materno . "', 
                            correo = '" . $this->email . "', UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), 
                            Password2 = MD5('" . $this->password . "'),Password = MD5('" . $this->password . "'),Pantalla = '$this->pantalla',IdAlmacen='" . $this->idAlmacen . "' WHERE IdUsuario = " . $this->id . ";");
            }
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteRegistro() {
        $this->getRegistroById($this->id);
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("DELETE FROM c_usuario WHERE IdUsuario = " . $this->id . ";");
        /* Se elimina también el usuario en la base multiBD */
        $conexionMulti = new ConexionMultiBD();
        $consulta = "DELETE FROM c_usuario WHERE id_usuario = $this->idUsuarioMultiBD";
        mysql_query($consulta);
        $conexionMulti->Desconectar();
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getUsuariosByPuesto($idPuesto) {
        $consulta = "SELECT * FROM c_usuario WHERE IdPuesto = $idPuesto AND Activo = 1 ORDER BY Nombre, ApellidoPaterno, ApellidoMaterno;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    /**
     * True n caso de que el usuario pueda reabrir tickets, false en caso contrario
     * @param type $idUsuario
     * @return boolean
     */
    public function puedeReabrir($idUsuario) {
        $consulta = "SELECT p.ReAbrirTicket FROM c_usuario AS u
        INNER JOIN c_puesto AS p ON u.IdUsuario = $idUsuario AND u.IdPuesto = p.IdPuesto";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            if ($rs['ReAbrirTicket'] == "1") {
                return true;
            }
        }
        return false;
    }

    /**
     * True en caso de que el usuario este asociado como conductor, false en caso contrario.
     * @return boolean
     */
    function isMensajeroConductor() {
        $consulta = "SELECT * FROM `c_conductor` WHERE IdUsuario = $this->id;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $num_rows = mysql_num_rows($query);
        if ($num_rows > 0) {
            return true;
        }
        return false;
    }

    /**
     * Verifica si el usuario pertenece al puesto especificado
     * @param type $idUsuario
     * @param type $idPuesto
     * @return boolean true en caso de que el usuario tenga el puesto de idPuesto
     */
    public function isUsuarioPuesto($idUsuario, $idPuesto) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        if (!isset($idUsuario) || !isset($idPuesto)) {
            return false;
        }
        $query = $catalogo->obtenerLista("SELECT IdPuesto FROM c_usuario WHERE IdUsuario = $idUsuario AND IdPuesto = $idPuesto;");
        $resultados = mysql_num_rows($query);
        if ($resultados > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function registrarNegociosDeUsuario($ClavesNegocios) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "DELETE FROM k_usuarionegocio WHERE IdUsuario = $this->id;";
        $catalogo->obtenerLista($consulta);
        if (isset($ClavesNegocios) && !empty($ClavesNegocios) && is_array($ClavesNegocios)) {
            foreach ($ClavesNegocios as $value) {
                $consulta = "INSERT INTO k_usuarionegocio"
                        . "(IdUsuario, ClaveCliente, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) "
                        . "VALUES($this->id, '$value','$this->usuarioCreacion', NOW(), '$this->UsuarioModificacion', NOW(), '$this->pantalla');";
                $catalogo->obtenerLista($consulta);
            }
        }
    }

    public function obtenerNegociosDeUsuario() {
        $consulta = "SELECT ClaveCliente FROM `k_usuarionegocio` WHERE IdUsuario = $this->id;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        $negocios = array();

        while ($rs = mysql_fetch_array($result)) {
            array_push($negocios, $rs['ClaveCliente']);
        }
        return $negocios;
    }

    public function obtenerRFCNegociosDEUsuario() {
        $consulta = "SELECT c.RFC 
            FROM `k_usuarionegocio` AS kun
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = kun.ClaveCliente
            WHERE kun.IdUsuario = $this->id;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        $negocios = array();

        while ($rs = mysql_fetch_array($result)) {
            array_push($negocios, $rs['RFC']);
        }
        return $negocios;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function getPaterno() {
        return $this->paterno;
    }

    public function setPaterno($paterno) {
        $this->paterno = $paterno;
    }

    public function getMaterno() {
        return $this->materno;
    }

    public function setMaterno($materno) {
        $this->materno = $materno;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getPuesto() {
        return $this->puesto;
    }

    public function setPuesto($puesto) {
        $this->puesto = $puesto;
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

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->UsuarioModificacion;
    }

    public function setUsuarioModificacion($UsuarioModificacion) {
        $this->UsuarioModificacion = $UsuarioModificacion;
    }

    public function getFechaModificacion() {
        return $this->fechaModificacion;
    }

    public function setFechaModificacion($fechaModificacion) {
        $this->fechaModificacion = $fechaModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    public function getIdUsuarioMultiBD() {
        return $this->idUsuarioMultiBD;
    }

    public function setIdUsuarioMultiBD($idUsuarioMultiBD) {
        $this->idUsuarioMultiBD = $idUsuarioMultiBD;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    function getTelefono() {
        return $this->Telefono;
    }

    function getSexo() {
        return $this->Sexo;
    }

    function getFechaNacimiento() {
        return $this->FechaNacimiento;
    }

    function setTelefono($Telefono) {
        $this->Telefono = $Telefono;
    }

    function setSexo($Sexo) {
        $this->Sexo = $Sexo;
    }

    function setFechaNacimiento($FechaNacimiento) {
        $this->FechaNacimiento = $FechaNacimiento;
    }

    function getRFC() {
        return $this->RFC;
    }

    function setRFC($RFC) {
        $this->RFC = $RFC;
    }

    function getCostoFijo() {
        return $this->CostoFijo;
    }

    function setCostoFijo($CostoFijo) {
        $this->CostoFijo = $CostoFijo;
    }
    
    function getIdFormaPago() {
        return $this->IdFormaPago;
    }

    function setIdFormaPago($IdFormaPago) {
        $this->IdFormaPago = $IdFormaPago;
    }
    
    function getPorcentajeDesc() {
        return $this->PorcentajeDesc;
    }

    function setPorcentajeDesc($PorcentajeDesc) {
        $this->PorcentajeDesc = $PorcentajeDesc;
    }
    
    function getProveedorFactura() {
        return $this->ProveedorFactura;
    }

    function setProveedorFactura($ProveedorFactura) {
        $this->ProveedorFactura = $ProveedorFactura;
    }


}
?>

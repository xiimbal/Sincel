<?php

include_once("Catalogo.class.php");
include_once("ConexionMultiBD.class.php");

/**
 * Description of UsuarioPendiente
 *
 * @author MAGG
 */
class UsuarioPendiente {
    
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
    private $tiempoDiferencia;
    private $idAlmacen;
    private $Telefono;
    private $Sexo;
    private $FechaNacimiento;
    private $idUsuarioMultiBD;
    private $empresa;
    
    public function getUsuarioById($id) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("SELECT *, TIMESTAMPDIFF(MINUTE,FechaCreacion,NOW()) AS MinutosDiferencia FROM `c_usuario_pendiente` WHERE IdUsuario = '$id';");
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
            $this->Telefono = $rs['Telefono'];
            $this->Sexo = $rs['Sexo'];
            $this->FechaNacimiento = $rs['FechaNacimiento'];
            $this->tiempoDiferencia = $rs['MinutosDiferencia'];
            return true;
        }
        return false;
    }
    
    public function marcarProcesado($id, $idUsuario){
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("UPDATE `c_usuario_pendiente` SET Activo = 0, IdUsuarioReal = $idUsuario WHERE IdUsuario = '$id';");
        if($query == "1"){
            return true;
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
        $consulta = "SELECT * FROM c_usuario WHERE Loggin = '$this->usuario' AND `Password` = '$this->password';";        
        $result = mysql_query($consulta);
        $conexionMulti->Desconectar();

        if (mysql_num_rows($result) == 0) {//Si se inserta correctamente el usuario en la MultiBD
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            if ($this->idAlmacen == "0" || $this->idAlmacen == "") {
                $consulta = ("INSERT INTO c_usuario_pendiente(IdUsuario,IdPuesto,Nombre,ApellidoPaterno,ApellidoMaterno,
                Loggin,Password2,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,
                Pantalla,correo,Password, Telefono, Sexo, FechaNacimiento) VALUES(0,$this->puesto,'$this->nombre','$this->paterno','$this->materno','$this->usuario',
                    TRIM('$this->password'),1,'$this->usuarioCreacion',now(),'$this->UsuarioModificacion',now(),'$this->pantalla','$this->email',"
                        . "TRIM('$this->password'), '$this->Telefono', $this->Sexo, '$this->FechaNacimiento')");
            } else {
                $consulta = ("INSERT INTO c_usuario_pendiente(IdUsuario,IdPuesto,Nombre,ApellidoPaterno,ApellidoMaterno,
                Loggin,Password2,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,
                Pantalla,correo,Password,IdAlmacen, Telefono, Sexo, FechaNacimiento) VALUES(0,$this->puesto,'$this->nombre','$this->paterno','$this->materno','$this->usuario',
                    TRIM('$this->password'),$this->activo,'$this->usuarioCreacion',now(),'$this->UsuarioModificacion',now(),'$this->pantalla',"
                    . "'$this->email',TRIM('$this->password'),'" . $this->idAlmacen . "', '$this->Telefono', $this->Sexo, '$this->FechaNacimiento')");
            }
            
            $this->id = $catalogo->insertarRegistro($consulta);
            if ($this->id != null && $this->id != 0) {
                return true;
            }
            return false;
        }
        return false;
    }
    
    public function getUsuariosPendientes(){
        $consulta = "SELECT CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS Nombre, correo, IdUsuario, Loggin
            FROM `c_usuario_pendiente` WHERE Activo = 1 AND ISNULL(IdUsuarioReal) AND TIMESTAMPDIFF(MINUTE,FechaCreacion,NOW()) <= 30;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }
    
    function getId() {
        return $this->id;
    }

    function getUsuario() {
        return $this->usuario;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getPaterno() {
        return $this->paterno;
    }

    function getMaterno() {
        return $this->materno;
    }

    function getEmail() {
        return $this->email;
    }

    function getPassword() {
        return $this->password;
    }

    function getPuesto() {
        return $this->puesto;
    }

    function getActivo() {
        return $this->activo;
    }

    function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    function getUsuarioModificacion() {
        return $this->UsuarioModificacion;
    }

    function getFechaModificacion() {
        return $this->fechaModificacion;
    }

    function getPantalla() {
        return $this->pantalla;
    }

    function getIdAlmacen() {
        return $this->idAlmacen;
    }

    function getIdUsuarioMultiBD() {
        return $this->idUsuarioMultiBD;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setPaterno($paterno) {
        $this->paterno = $paterno;
    }

    function setMaterno($materno) {
        $this->materno = $materno;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setPuesto($puesto) {
        $this->puesto = $puesto;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

    function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    function setUsuarioModificacion($UsuarioModificacion) {
        $this->UsuarioModificacion = $UsuarioModificacion;
    }

    function setFechaModificacion($fechaModificacion) {
        $this->fechaModificacion = $fechaModificacion;
    }

    function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    function setIdUsuarioMultiBD($idUsuarioMultiBD) {
        $this->idUsuarioMultiBD = $idUsuarioMultiBD;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    function getTiempoDiferencia() {
        return $this->tiempoDiferencia;
    }

    function setTiempoDiferencia($tiempoDiferencia) {
        $this->tiempoDiferencia = $tiempoDiferencia;
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
}

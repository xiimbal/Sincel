<?php

date_default_timezone_set("America/Mexico_City");

include_once("ConexionMultiBD.class.php");
include_once("Catalogo.class.php");
include_once("Parametros.class.php");
include_once("Incidencia.class.php");

class Session {

    private $id_usu;
    private $usuario;
    private $password;
    private $id_empresa;
    private $nombre_empresa;
    private $empresa;
    // ** PCM 30/01/2019
    private $user_activo;
    private $empresa_activa;
    // PCM **

    function getLogin($usuario, $password) {
        $consulta = ("SELECT IdUsuario FROM `c_usuario` WHERE Password2='" . $password . "' AND Loggin='" . $usuario . "' AND Activo = 1;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id_usu = $rs['IdUsuario'];
            $this->usuario = $usuario;
            $this->password = $password;
            return $this->id_usu;
        }
        return "";
    }
    
    function getObtenerUsuarioCreacion($IdSession){
        $con_session = mysql_query("SELECT c.Loggin from c_usuario as c where IdSession =" . $IdSession) . "";
        $catalogo = new Catalogo();
        while ($rs = mysql_fetch_array($con_session)){
            $this->usuario = $rs["Loggin"];
            return $this->usuario;
        }
        return "";
    }

    // ** PCM 30/01/2019
    function getLogginMultiBD($usuario, $password) {
        $this->conn = new ConexionMultiBD();
        $query = mysql_query("SELECT u.id_usuario, u.id_empresa, e.nombre_empresa, e.Activo as e_activo, u.Activo as u_activo FROM `c_usuario` AS u 
            LEFT JOIN c_empresa AS e ON e.id_empresa = u.id_empresa 
            WHERE u.Password='" . $password . "' AND u.Loggin='" . $usuario . "';");
        while ($rs = mysql_fetch_array($query)) {
            $this->id_usu = $rs['id_usuario'];
            $this->id_empresa = $rs['id_empresa'];
            $this->empresa = $rs['id_empresa'];
            $this->nombre_empresa = $rs['nombre_empresa'];
            $this->usuario = $usuario;
            $this->password = $password;
            $this->user_activo = $rs['u_activo'];
            $this->empresa_activa = $rs['e_activo'];
            $this->conn->Desconectar();
            return true;
        }
        $this->conn->Desconectar();
        return false;
    }

    public function obtenerEmpresaBySesion($IdSession) {
        $this->conn = new ConexionMultiBD();
        $result = mysql_query("SELECT id_empresa FROM `c_usuario` WHERE IdSession = '$IdSession';");
        $this->conn->Desconectar();
        if (mysql_num_rows($result) > 0) {
            while ($rs = mysql_fetch_array($result)) {
                return $rs['id_empresa'];
            }
        } else {
            return 0;
        }
    }

    public function logginWithSession($clave) {
        $consulta = "SELECT IdSession, FechaCreacion, IdUsuario FROM c_session WHERE ClaveSession = '$clave' AND Activo = 1;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);

        if (mysql_num_rows($result) > 0) {//Si existe una session activa para esa clave            
            $parametros = new Parametros();
            if (isset($this->empresa)) {
                $parametros->setEmpresa($this->empresa);
            }
            //Obtenemos el maximo de minutos que una session puede estar activa
            $max_minute = 10; //Calor por default
            if ($parametros->getRegistroById(31)) {//Valor configurado en la bd
                $max_minute = (int) $parametros->getValor();
            }

            while ($rs = mysql_fetch_array($result)) {//Recorremos la session activa
                $consulta = "SELECT TIMESTAMPDIFF(MINUTE,'" . $rs['FechaCreacion'] . "',NOW()) AS Time;";
                $resultTime = $catalogo->obtenerLista($consulta);
                while ($rs2 = mysql_fetch_array($resultTime)) {
                    $tiempo_transcurrido = (int) $rs2['Time'];
                    if ($tiempo_transcurrido > $max_minute) {//Si la session ha estado activa mas del tiempo permitido
                        $consulta = "UPDATE c_session SET Activo = 0 WHERE IdUsuario = " . $rs['IdUsuario'] . ";";
                        $catalogo->obtenerLista($consulta);
                        return -2;
                    } else {//Si la session sigue activa correctamente
                        return $rs['IdUsuario'];
                    }
                }
                return -3;
            }
        } else {//Esta clave ya no esta activa
            return -1;
        }
    }

    /**
     * Regresa una clave unica activa para el usuario actual
     * @param type $sizeClave tamanio de la clave
     * @param type $IdSessionAnterior Id de la sesión anterior a la nueva que se piensa crear
     * @return type
     */
    public function generarClaveSession($sizeClave, $IdSessionAnterior) {
        $clave = "";
        $clave_existe = false;
        $respuesta = array();
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        $parametros = new Parametros();
        if (isset($this->empresa)) {
            $parametros->setEmpresa($this->empresa);
        }
        //Obtenemos el maximo de minutos que una session puede estar activa
        $max_minute = 10; //Calor por default
        if ($parametros->getRegistroById(31)) {//Valor configurado en la bd
            $max_minute = (int) $parametros->getValor();
        }


        $consulta = "SELECT IdSession, ClaveSession, FechaCreacion FROM c_session 
                WHERE IdUsuario = $this->id_usu AND Activo = 1 AND TIMESTAMPDIFF(MINUTE,FechaCreacion,NOW()) < $max_minute 
                ORDER BY IdSession LIMIT 0,1;";
        $result = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($result) > 0) {//Si el usuario actual ya tiene una clave activa, se regresa esa clave
            while ($rs = mysql_fetch_array($result)) {
                $respuesta['FechaCreacion'] = $rs['FechaCreacion'];
                $respuesta['IdSession'] = $rs['ClaveSession'];
                $respuesta['DuracionMinutos'] = $max_minute;
            }
            return $respuesta;
        } else {//Si el usuario no tiene clave activa se genera una nueva  
            ////Buscamos si la clave de sesión anterior recibida es efectivamente la última creada
            $consulta = "SELECT IdSession FROM `c_session` WHERE ClaveSession = '$IdSessionAnterior' AND IdUsuario = $this->id_usu AND Activo = 1;";
            $result_incidencia = $catalogo->obtenerLista($consulta);
            if (mysql_num_rows($result_incidencia) == 0) {//Si el id de sesion anterior es incorrecto
                $incidencia = new Incidencia();
                if (isset($this->empresa)) {
                    $incidencia->setEmpresa($this->empresa);
                }
                $incidencia->setNoSerie("");
                $incidencia->setClaveCentroCosto("");
                $incidencia->setId_Ticket("NULL");
                $incidencia->setFecha(date('Y') . "-" . date('m') . "-" . date('d'));
                $incidencia->setFechaFin($incidencia->getFecha());
                $incidencia->setDescripcion("Inicio de sesión del usuario " . $this->id_usu . " con id de sesión anterior incorrecto: $IdSessionAnterior " . date('l jS \of F Y h:i:s A'));
                $incidencia->setStatus(1);
                $incidencia->setActivo(1);
                $incidencia->setUsuarioCreacion($this->usuario);
                $incidencia->setUsuarioUltimaModificacion($this->usuario);
                $incidencia->setPantalla("Generar nueva sesión");
                $incidencia->setIdTipoIncidencia(100);
                if (!$incidencia->newRegistro()) {
                    //return $incidencia->getConsulta();
                }
            }
            //Desactivamos cualquier Session activa
            $consulta = "UPDATE c_session SET Activo = 0 WHERE IdUsuario = $this->id_usu;";
            $catalogo->obtenerLista($consulta);
            do {//Se repite el proceso hasta que se encuentra una clave valida
                $clave = $this->generarClavealeatoria($sizeClave); //Generamos la clave aleatoria 
                //verificamos que la clave no existe en la multi-base
                $this->conn = new ConexionMultiBD();
                $result = mysql_query("SELECT id_usuario FROM `c_usuario` WHERE IdSession = '$clave';");
                $this->conn->Desconectar();
                if (mysql_num_rows($result) > 0) {
                    $clave_existe = true;
                } else {
                    //verificamos que la clave no exista en la bd de la empresa del usuario
                    $consulta = "SELECT IdSession FROM c_session WHERE ClaveSession = '$clave' AND Activo = 1;";
                    $result = $catalogo->obtenerLista($consulta);
                    if (mysql_num_rows($result) > 0) { //Si la clave ya existes y está vigente, se tiene que volver a generar otra
                        $clave_existe = true;
                    } else {//Si la clave no existe, la insertamos en la BD y la devolvemos como resultado del método
                        $this->conn = new ConexionMultiBD();
                        $result = mysql_query("UPDATE `c_usuario` SET IdSession = '$clave',FechaModificacion=NOW() WHERE Loggin = '$this->usuario' AND `Password` = '$this->password';");
                        $this->conn->Desconectar();

                        $hoy = getdate();
                        $fechaCreacion = $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'] . " " . $hoy['hours'] . ":" . $hoy['minutes'] . ":" . $hoy['seconds'];
                        $consulta = "INSERT INTO c_session(IdSession, ClaveSession, IdUsuario, Activo, FechaCreacion) VALUES(0,'$clave',$this->id_usu,1,'$fechaCreacion');";
                        $idSession = $catalogo->insertarRegistro($consulta);
                        if ($idSession != NULL && $idSession != 0) {
                            $clave_existe = false;
                            $respuesta['FechaCreacion'] = $fechaCreacion;
                            $respuesta['IdSession'] = $clave;
                            $respuesta['DuracionMinutos'] = $max_minute;
                        }
                    }
                }
            } while ($clave_existe);
        }
        return $respuesta;
    }

    /**
     * Genera una clave alfanumerica de tamanio "n"
     * @param type $sizeClave tamanio de la clave
     * @return type
     */
    function generarClavealeatoria($sizeClave) {
        //Se define una cadena de caractares. Te recomiendo que uses esta.
        $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890?[]=*-+.";
        //Obtenemos la longitud de la cadena de caracteres
        $longitudCadena = strlen($cadena);

        //Se define la variable que va a contener la contraseña
        $pass = "";
        //Se define la longitud de la contraseña, en mi caso 10, pero puedes poner la longitud que quieras        
        $longitudPass = $sizeClave;

        //Creamos la contraseña
        for ($i = 1; $i <= $longitudPass; $i++) {
            //Definimos numero aleatorio entre 0 y la longitud de la cadena de caracteres-1
            $pos = rand(0, $longitudCadena - 1);

            //Vamos formando la contraseña en cada iteraccion del bucle, añadiendo a la cadena $pass la letra correspondiente a la posicion $pos en la cadena de caracteres definida.
            $pass .= substr($cadena, $pos, 1);
        }
        return $pass;
    }

    function revalidarSesion($IdSession) {
        $consulta = "SELECT s.IdUsuario, u.Loggin, u.Password2 
            FROM `c_session` AS s
            LEFT JOIN c_usuario AS u ON u.IdUsuario = s.IdUsuario
            WHERE s.ClaveSession = '$IdSession' AND s.Activo = 1;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($result) > 0) {
            while ($rs = mysql_fetch_array($result)) {
                $this->id_usu = $rs['IdUsuario'];
                $this->usuario = $rs['Loggin'];
                $this->password = $rs['Password2'];
                $respuesta = $this->generarClaveSession(15, $IdSession);
                $respuesta['IdUsuario'] = $this->getId_usu();
                return $respuesta;
            }
        } else {
            return -1;
        }
    }

    public function marcarIntentoCorrecto($IP, $Proxy, $Loggin) {
        $this->conn = new ConexionMultiBD();
        $consulta = "UPDATE c_usuario SET IntentosConsecutivos = 0, IP = '$IP', Proxy = '$Proxy', FechaHoraIntento = NOW(), FechaModificacion = NOW() "
                . "WHERE Loggin = '$Loggin';";
        mysql_query($consulta);
        $this->conn->Desconectar();
    }

    // ** PCM 30/01/2019
    public function marcarIntentoFallido($IP, $Proxy, $Loggin) {
        $this->conn = new ConexionMultiBD();        
        $consulta = "SELECT IntentosConsecutivos, id_empresa FROM c_usuario WHERE Loggin = '$Loggin';";
        $result = mysql_query($consulta);
        $Intentos = 0;
        while ($rs = mysql_fetch_array($result)) {
            $Intentos = $rs['IntentosConsecutivos'];
            $IdEmpresa = $rs['id_empresa'];        
        }
        $consulta = "UPDATE c_usuario SET IntentosConsecutivos = IntentosConsecutivos + 1"
                . " WHERE Loggin = '$Loggin';";
        if ($Intentos == 2){            
            $consulta = "CREATE DEFINER = root@localhost EVENT IF NOT EXISTS ".$Loggin."
                ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 3 MINUTE
                do                             
                UPDATE c_usuario c SET c.IntentosConsecutivos = 0, c.Activo = 1 WHERE c.Loggin = '".$Loggin."';";
            mysql_query($consulta);  
            $consulta = "UPDATE c_usuario SET IntentosConsecutivos = IntentosConsecutivos + 1, IP = '$IP', Proxy = '$Proxy', FechaHoraIntento = NOW()"
                    . " WHERE Loggin = '$Loggin';";
        }
        mysql_query($consulta);

        if ($Intentos >= 2) {//Si han sido más de tres intentos, bloquear al usuario
            $consulta = "UPDATE c_usuario SET Activo = 0 "
                    . "WHERE Loggin = '$Loggin';";
            mysql_query($consulta);            
            $this->conn->Desconectar();
            return true;
        }

        $this->conn->Desconectar();
        return false;
    }

    public function getId_usu() {
        return $this->id_usu;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getId_empresa() {
        return $this->id_empresa;
    }

    public function getNombre_empresa() {
        return $this->nombre_empresa;
    }

    // ** PCM 30/01/2019
    public function getUser_activo() {
        return $this->user_activo;
    }

    public function getEmpresa_activa() {
        return $this->empresa_activa;
    }
    // ** PCM

    public function setNombre_empresa($nombre_empresa) {
        $this->nombre_empresa = $nombre_empresa;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    function setId_usu($id_usu) {
        $this->id_usu = $id_usu;
    }

    function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setId_empresa($id_empresa) {
        $this->id_empresa = $id_empresa;
    }

}

?>

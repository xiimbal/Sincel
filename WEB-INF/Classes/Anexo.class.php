<?php

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");
include_once ("Contrato.class.php");
include_once ("CentroCosto.class.php");

/**
 * Description of Anexo
 *
 * @author samsung
 */
class Anexo {

    private $ClaveAnexoTecnico;
    private $FechaElaboracion;
    private $NoContrato;
    private $DiaCorte;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $IdAnexoClienteCC;
    private $ClaveCC;
    private $empresa;

    /**
     * Obtener los id anexos clientes dentro de un array
     * @param type $ClaveAnexoTecnico clave del anexo tecnico
     * @param type $CC clave del centro de costo
     * @return type arreglo con los idAnexos de la clave del anexo y localidad especificada
     */
    public function getIdAnexosDeAnexoLocalidad($ClaveAnexoTecnico, $CC) {
        $idAnexos = array();

        $consulta = "SELECT IdAnexoClienteCC FROM k_anexoclientecc AS kacc
        WHERE kacc.ClaveAnexoTecnico = '$ClaveAnexoTecnico' AND CveEspClienteCC = '$CC';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            array_push($idAnexos, $rs['IdAnexoClienteCC']);
        }

        return $idAnexos;
    }

    public function getAnexosDeContratoLocalidad($contrato, $cc) {

        $consulta = "SELECT kacc.IdAnexoClienteCC, kacc.ClaveAnexoTecnico, kacc.CveEspClienteCC, DATE(cat.FechaElaboracion) AS FechaElaboracion, 
        cat.NoContrato FROM k_anexoclientecc AS kacc 
        INNER JOIN c_anexotecnico AS cat ON kacc.CveEspClienteCC = '$cc' AND kacc.ClaveAnexoTecnico = cat.ClaveAnexoTecnico
        AND cat.NoContrato = '$contrato' AND cat.Activo= 1 GROUP BY ClaveAnexoTecnico;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);

        return $query;
    }

    public function getAnexosDeLocalidad($cc) {

        $consulta = "SELECT kacc.IdAnexoClienteCC, kacc.ClaveAnexoTecnico, kacc.CveEspClienteCC, DATE(cat.FechaElaboracion) AS FechaElaboracion, 
        cat.NoContrato FROM k_anexoclientecc AS kacc 
        INNER JOIN c_anexotecnico AS cat ON kacc.CveEspClienteCC = '$cc' AND kacc.ClaveAnexoTecnico = cat.ClaveAnexoTecnico
        AND cat.Activo= 1;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);

        return $query;
    }

    public function getAnexosDeContrato($contrato) {

        $consulta = "SELECT * FROM c_anexotecnico WHERE NoContrato = '$contrato';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);

        return $query;
    }

    public function getAnexosDeCliente($ClaveCliente) {
        $consulta = "SELECT ctt.NoContrato, cat.ClaveAnexoTecnico, cat.FechaElaboracion, ctt.ClaveCliente 
        FROM c_contrato AS ctt
        INNER JOIN c_anexotecnico AS cat ON ctt.ClaveCliente = '$ClaveCliente' AND ctt.NoContrato = cat.NoContrato;";

        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);

        return $query;
    }

    public function getRegistroValidacion($contrato) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("SELECT k.IdAnexoClienteCC as id,k.CveEspClienteCC, 
            (SELECT CASE WHEN !ISNULL(cc.ClaveCentroCosto) THEN '1' ELSE '0' END) AS Tipo,
            (SELECT CASE WHEN !ISNULL(cc.ClaveCentroCosto) THEN cc.Nombre ELSE c.NombreRazonSocial END) AS Nombre, a.* 
            FROM `c_anexotecnico` AS a 
        INNER JOIN k_anexoclientecc AS k ON a.NoContrato = '$contrato' AND k.ClaveAnexoTecnico = a.ClaveAnexoTecnico
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = k.CveEspClienteCC
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = k.CveEspClienteCC WHERE a.Activo = 1;");
        return $query;
    }

    public function getRegistroValidacionSoloAnexo($contrato) {
        $consulta = "SELECT cat.*, DAY(kacc.Fecha) AS DiaCorte 
            FROM `c_anexotecnico` AS cat 
            LEFT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = (SELECT MIN(IdAnexoClienteCC) FROM k_anexoclientecc WHERE ClaveAnexoTecnico = cat.ClaveAnexoTecnico)
            WHERE cat.NoContrato = '$contrato' AND cat.Activo = 1;";

        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);

        return $query;
    }

    public function getRegistroById($clave) {

        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("SELECT * FROM `c_anexotecnico` WHERE ClaveAnexoTecnico = '$clave';");

        if ($rs = mysql_fetch_array($query)) {
            $this->ClaveAnexoTecnico = $rs['ClaveAnexoTecnico'];
            $this->FechaElaboracion = $rs['FechaElaboracion'];
            $this->NoContrato = $rs['NoContrato'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->DiaCorte = $this->getDiaCorteByAnexo($this->ClaveAnexoTecnico);
            return true;
        }
        return false;
    }

    public function hayKAnexoClienteByClave($ClaveAnexoTecnico, $CC) {

        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("SELECT * FROM `k_anexoclientecc` where ClaveAnexoTecnico = '$ClaveAnexoTecnico' AND CveEspClienteCC = '$CC';");

        if (mysql_num_rows($query) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Nuevo registro con los parametros recibidos.
     * @param type $fechaElaboracion
     * @param type $NoContrato
     * @param type $claveCC
     * @param type $pantalla
     * @return type true en caso de hacerlo correctamente, false en caso contrario.
     */
    public function newRegistroDefault($fechaElaboracion, $NoContrato, $claveCC, $pantalla) {
        $this->FechaElaboracion = $fechaElaboracion;
        $this->NoContrato = $NoContrato;
        $this->Activo = 1;
        if (isset($_SESSION['user'])) {
            $usuario = $_SESSION['user'];
        } else {
            $usuario = "kyocera";
        }
        $this->UsuarioCreacion = $usuario;
        $this->UsuarioUltimaModificacion = $usuario;
        $this->Pantalla = $pantalla;
        $this->ClaveCC = $claveCC;
        return $this->newRegistro();
    }

    public function newRegistro() {

        $consulta = ("SELECT MAX(CAST(ClaveAnexoTecnico AS UNSIGNED)) AS maximo FROM `c_anexotecnico`;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query2 = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query2)) {
            $maximo = (int) $rs['maximo'];
            if ($maximo == "" || $maximo == 0) {
                $maximo = 1000;
            }
            $maximo++;
            $consulta = "INSERT INTO c_anexotecnico(ClaveAnexoTecnico, FechaElaboracion,NoContrato, Activo,UsuarioCreacion,
                FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                VALUES('$maximo','$this->FechaElaboracion','$this->NoContrato',$this->Activo,
                '$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla');";
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                $this->ClaveAnexoTecnico = $maximo;
                return $this->newK_anexoClienteCC();
            }
        }

        return false;
    }

    public function newK_anexoClienteCC() {
        $m = date("m");
        $y = date("Y");
        if (isset($this->DiaCorte) && $this->DiaCorte != "") {
            $Fecha = "'" . $y . "-" . $m . "-$this->DiaCorte'";
        } else if (isset($this->FechaElaboracion) && $this->FechaElaboracion != "") {
            $Fecha = "'$this->FechaElaboracion'";
        } else {
            $Fecha = "NOW()";
        }
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        if (isset($this->ClaveCC) && $this->ClaveCC != "") {
            $consulta = "INSERT INTO k_anexoclientecc(IdAnexoClienteCC, ClaveAnexoTecnico,CveEspClienteCC,Fecha,UsuarioCreacion,
                    FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                    VALUES(0,'$this->ClaveAnexoTecnico','$this->ClaveCC',$Fecha,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla')";

            $this->IdAnexoClienteCC = $catalogo->insertarRegistro($consulta);
            if ($this->IdAnexoClienteCC != NULL && $this->IdAnexoClienteCC != 0) {
                return true;
            } else {
                return false;
            }
        } else {
            $contrato = new Contrato();            
            $cc = new CentroCosto();
            if(isset($this->empresa)){
                $contrato->setEmpresa($this->empresa);
                $cc->setEmpresa($this->empresa);
            }
            if ($contrato->getRegistroById($this->NoContrato)) {
                $result = $cc->getRegistroValidacion($contrato->getClaveCliente());
                while ($rs = mysql_fetch_array($result)) {
                    $consulta = "INSERT INTO k_anexoclientecc(IdAnexoClienteCC, ClaveAnexoTecnico,CveEspClienteCC,Fecha,UsuarioCreacion,
                        FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                        VALUES(0,'$this->ClaveAnexoTecnico','" . $rs['ClaveCentroCosto'] . "',$Fecha,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla')";

                    $this->IdAnexoClienteCC = $catalogo->insertarRegistro($consulta);
                    if ($this->IdAnexoClienteCC == NULL || $this->IdAnexoClienteCC == 0) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function editRegistro() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("UPDATE c_anexotecnico SET FechaElaboracion = '$this->FechaElaboracion', NoContrato = '$this->NoContrato',
           UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla'
           WHERE ClaveAnexoTecnico = '$this->ClaveAnexoTecnico';");
        $m = date("m");
        $y = date("Y");
        if (isset($this->DiaCorte) && $this->DiaCorte != "") {
            $Fecha = "'" . $y . "-01-$this->DiaCorte'";
        } else if (isset($this->FechaElaboracion) && $this->FechaElaboracion != "") {
            $Fecha = "'" . $y . "-01-$this->FechaElaboracion'";
        } else {
            $Fecha = "NOW()";
        }
        /* Actualizamos la fecha de corte */
        $consulta = "SELECT MIN(IdAnexoClienteCC) AS IdAnexoClienteCC FROM k_anexoclientecc WHERE ClaveAnexoTecnico = '$this->ClaveAnexoTecnico';";
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            $consulta = "UPDATE k_anexoclientecc SET Fecha = $Fecha, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla' WHERE IdAnexoClienteCC = " . $rs['IdAnexoClienteCC'] . ";";
            $catalogo->obtenerLista($consulta);
        }

        if ($query == 1) {
            return true;
        }
        return false;
    }

    /**
     * Obtiene el dia de corte del anexo.
     * @param type $anexo clave del anexo tecnico
     * @return type dia de corte del anexo tecnico, null en caso de no existir.
     */
    public function getDiaCorteByAnexo($anexo) {
        $consulta = "SELECT cat.*, DAY(kacc.Fecha) AS DiaCorte 
            FROM `c_anexotecnico` AS cat 
            LEFT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = (SELECT MIN(IdAnexoClienteCC) FROM k_anexoclientecc WHERE ClaveAnexoTecnico = cat.ClaveAnexoTecnico)
            WHERE cat.ClaveAnexoTecnico = '$anexo' AND cat.Activo = 1;";

        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $dia = null;
        while ($rs = mysql_fetch_array($query)) {
            $dia = $rs['DiaCorte'];
        }

        return $dia;
    }

    public function getClaveAnexoTecnico() {
        return $this->ClaveAnexoTecnico;
    }

    public function setClaveAnexoTecnico($ClaveAnexoTecnico) {
        $this->ClaveAnexoTecnico = $ClaveAnexoTecnico;
    }

    public function getFechaElaboracion() {
        return $this->FechaElaboracion;
    }

    public function setFechaElaboracion($FechaElaboracion) {
        $this->FechaElaboracion = $FechaElaboracion;
    }

    public function getNoContrato() {
        return $this->NoContrato;
    }

    public function setNoContrato($NoContrato) {
        $this->NoContrato = $NoContrato;
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

    public function getClaveCC() {
        return $this->ClaveCC;
    }

    public function setClaveCC($ClaveCC) {
        $this->ClaveCC = $ClaveCC;
    }

    public function getIdAnexoClienteCC() {
        return $this->IdAnexoClienteCC;
    }

    public function setIdAnexoClienteCC($IdAnexoClienteCC) {
        $this->IdAnexoClienteCC = $IdAnexoClienteCC;
    }

    public function getDiaCorte() {
        return $this->DiaCorte;
    }

    public function setDiaCorte($DiaCorte) {
        $this->DiaCorte = $DiaCorte;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>

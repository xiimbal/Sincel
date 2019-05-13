<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

/**
 * Description of ReporteUso
 *
 * @author samsung
 */
class ReporteUso {

    private $IdReporteUso;
    private $IdReporte;
    private $NombreDeDominio;
    private $NombreDocumento;
    private $Impresora;
    private $Fecha;
    private $Hora;
    private $Computadora;
    private $Tamano;
    private $Valores;
    private $Tipo;
    private $BN;
    private $Color;
    private $Bytes;
    private $PageCount;
    private $Costo;
    private $Balance;
    private $ClaveCentroCosto;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    /**
     * Obtiene el nombre de todos los archivos Log dentro del directorio(No revisa dentro de los subdirectorios)
     * @param type $directory $path del directorio
     * @return type
     */
    public function getFilesLOGFromDirectory($directory) {
        $files = array();
        /* Leemos recursivamente los directorios y sus archivos */
        $i = 0;
        $di = new RecursiveDirectoryIterator($directory);
        foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
            //echo $filename . ' - ' . $file->getSize() . ' bytes <br/>';
            if ($this->endsWith($filename, ".LOG") || $this->endsWith($filename, ".log")) {/* Verificamos que tengan extension .LOG */
                $lastModified = date("Y-m-d H:i:s", filemtime($filename)); /* Obtenemos la ultima modificacion del archivo */
                if (!$this->existFile($filename, $lastModified)) { /* Si el archivo no esta registrado en la bd, lo consideramos para procesarlo */
                    $files[$filename] = $lastModified;
                }
            }
        }
        return $files;
    }

    /**
     * Verifica si el archivo con su ultima modificacion ya esta registrado
     * @param type $file nombre del archivo
     * @param type $lastModified ultima modificacion
     * @return boolean true en caso de que exista, false en caso contrario
     */
    public function existFile($file, $lastModified) {
        $file = str_replace("\\", "/", $file);
        $consulta = ("SELECT id_reporte FROM `c_reporteuso` WHERE archivo = '$file' AND ultima_modificacion = '$lastModified';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return true;
        }
        return false;
    }

    public function procesarArchivosNuevos($archivosParaProcesar) {
        $catalogo = new Catalogo();
        /* Procesamos todos los archivos nuevos */
        foreach ($archivosParaProcesar as $key => $value) {
            $filename = str_replace("\\", "/", $key);
            //echo "Trabajando: ".$filename;
            /* Recorremos todos los nuevo archivos a procesar */
            $idReporte = $catalogo->insertarRegistro("INSERT INTO c_reporteuso(fecha,archivo,ultima_modificacion,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,"
                    . "FechaUltimaModificacion,Pantalla) VALUES(NOW(),'$filename','$value',1,'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'upload_file.php');");
            /* Leemos el excel guardado y lo vamos insertando en la base de datos */
            $fila = 1;
            if (($gestor = fopen($filename, "r")) !== FALSE) {
                while (($datos = fgetcsv($gestor, 0, ",")) !== FALSE) {
                    $numero = count($datos);
                    $obj = new ReporteUso();
                    $obj->setIdReporte($idReporte);
                    //echo "<p> $numero de campos en la línea $fila: <br /></p>\n";
                    $fila++;
                    for ($c = 0; $c < $numero; $c++) {
                        //echo $datos[$c] . "<br />\n";                        
                        switch ($c) {
                            case 9:
                                $obj->setValores($datos[$c]);
                                $pos = strpos($datos[$c], "/Jt="); //Tipo (101: copia, 102: scan, 103: impresion)
                                if ($pos !== false) { // nota: tres signos de igual
                                    $pos_final = strpos($datos[$c], "/", $pos + 1);
                                    $tipo = substr($datos[$c], $pos + 4, $pos_final - ($pos + 4));
                                    if ($tipo != "101" && $tipo != "102" && $tipo != "103") {
                                        continue 3;
                                    }
                                    $obj->setTipo($tipo);

                                    $pos = strpos($datos[$c], "/Cp="); //Hojas B/N
                                    if ($pos !== false) { // nota: tres signos de igual
                                        $pos_final = strpos($datos[$c], "/", $pos + 1);
                                        $bn = substr($datos[$c], $pos + 4, $pos_final - ($pos + 4));
                                        $obj->setBN($bn);
                                    } else {
                                        $obj->setBN(0);
                                    }

                                    $pos = strpos($datos[$c], "/Cg="); //Hojas B/N
                                    if ($pos !== false) { // nota: tres signos de igual
                                        $pos_final = strpos($datos[$c], "/", $pos + 1);
                                        $color = substr($datos[$c], $pos + 4, $pos_final - ($pos + 4));
                                        $obj->setColor($color);
                                    } else {
                                        $obj->setColor(0);
                                    }
                                } else {/* Si no hay tipo (101: copia, 102: scan, 103: impresion) */
                                    continue 3;
                                }
                                break;
                            case 0:
                                $obj->setNombreDeDominio($datos[$c]);
                                break;
                            case 1:
                                $obj->setNombreDocumento($datos[$c]);
                                break;
                            case 2:
                                $obj->setImpresora($datos[$c]);
                                break;
                            case 3:
                                $fecha = explode("/", $datos[$c]);
                                $obj->setFecha($fecha[2] . "-" . $fecha[1] . "-" . $fecha[0]);
                                break;
                            case 4:
                                $obj->setHora("00:00");
                                break;
                            case 5:
                                $obj->setComputadora($datos[$c]);
                                break;
                            case 6:
                                break;
                            case 7:
                                $obj->setClaveCentroCosto($datos[$c]);
                                break;
                            case 8:
                                $obj->setTamano($datos[$c]);
                                break;
                            case 10:
                                $obj->setBytes($datos[$c]);
                                break;
                            case 11:
                                $obj->setPageCount($datos[$c]);
                                break;
                            case 12:
                                $obj->setCosto($datos[$c]);
                                break;
                            case 13:
                                $obj->setBalance($datos[$c]);
                                break;
                            default:
                                break;
                        }
                    }
                    $obj->setActivo(1);
                    $obj->setUsuarioCreacion($_SESSION['user']);
                    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
                    $obj->setPantalla("ReporteUso.class.php");
                    $obj->newRegistro();
                }
                fclose($gestor);
            }
        }
    }

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `k_reporteuso` WHERE IdReporteUso = $id;");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdReporteUso = $rs['IdReporteUso'];
            $this->IdReporte = $rs['IdReporte'];
            $this->NombreDeDominio = $rs['NombreDeDominio'];
            $this->NombreDocumento = $rs['NombreDocumento'];
            $this->Impresora = $rs['Impresora'];
            $this->Fecha = $rs['Fecha'];
            $this->Hora = $rs['Hora'];
            $this->Computadora = $rs['Computadora'];
            $this->Tamano = $rs['Tamano'];
            $this->Tipo = $rs['Tipo'];
            $this->BN = $rs['BN'];
            $this->Color = $rs['Color'];
            $this->Valores = $rs['Valores'];
            $this->Bytes = $rs['Bytes'];
            $this->PageCount = $rs['PageCount'];
            $this->Costo = $rs['Costo'];
            $this->Balance = $rs['Balance'];
            $this->ClaveCentroCosto = $rs['ClaveCentroCosto'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    public function newRegistro() {        
        $consulta = "INSERT INTO k_reporteuso(IdReporte,NombreDeDominio,NombreDocumento,Impresora,Fecha,Hora,Computadora,Tamano,Valores,"
                . "Tipo, BN, Color, Bytes,PageCount,Costo,Balance,"
                . "ClaveCentroCosto,Activo,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) "
                . "VALUES($this->IdReporte,'$this->NombreDeDominio','$this->NombreDocumento','$this->Impresora','$this->Fecha','$this->Hora','$this->Computadora',"
                . "'$this->Tamano','$this->Valores',$this->Tipo,$this->BN,$this->Color,'$this->Bytes','$this->PageCount','$this->Costo','$this->Balance','$this->ClaveCentroCosto',$this->Activo,"
                . "'" . $this->UsuarioCreacion . "',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla');";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdReporteUso() {
        return $this->IdReporteUso;
    }

    public function getNombreDeDominio() {
        return $this->NombreDeDominio;
    }

    public function getNombreDocumento() {
        return $this->NombreDocumento;
    }

    public function getImpresora() {
        return $this->Impresora;
    }

    public function getFecha() {
        return $this->Fecha;
    }

    public function getHora() {
        return $this->Hora;
    }

    public function getComputadora() {
        return $this->Computadora;
    }

    public function getTamano() {
        return $this->Tamano;
    }

    public function getValores() {
        return $this->Valores;
    }

    public function getBytes() {
        return $this->Bytes;
    }

    public function getPageCount() {
        return $this->PageCount;
    }

    public function getCosto() {
        return $this->Costo;
    }

    public function getBalance() {
        return $this->Balance;
    }

    public function getClaveCentroCosto() {
        return $this->ClaveCentroCosto;
    }

    public function getActivo() {
        return $this->Activo;
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

    public function setIdReporteUso($IdReporteUso) {
        $this->IdReporteUso = $IdReporteUso;
    }

    public function setNombreDeDominio($NombreDeDominio) {
        $this->NombreDeDominio = $NombreDeDominio;
    }

    public function setNombreDocumento($NombreDocumento) {
        $this->NombreDocumento = $NombreDocumento;
    }

    public function setImpresora($Impresora) {
        $this->Impresora = $Impresora;
    }

    public function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }

    public function setHora($Hora) {
        $this->Hora = $Hora;
    }

    public function setComputadora($Computadora) {
        $this->Computadora = $Computadora;
    }

    public function setTamano($Tamano) {
        $this->Tamano = $Tamano;
    }

    public function setValores($Valores) {
        $this->Valores = $Valores;
    }

    public function setBytes($Bytes) {
        $this->Bytes = $Bytes;
    }

    public function setPageCount($PageCount) {
        $this->PageCount = $PageCount;
    }

    public function setCosto($Costo) {
        $this->Costo = $Costo;
    }

    public function setBalance($Balance) {
        $this->Balance = $Balance;
    }

    public function setClaveCentroCosto($ClaveCentroCosto) {
        $this->ClaveCentroCosto = $ClaveCentroCosto;
    }

    public function setActivo($Activo) {
        $this->Activo = $Activo;
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

    public function getIdReporte() {
        return $this->IdReporte;
    }

    public function setIdReporte($IdReporte) {
        $this->IdReporte = $IdReporte;
    }

    public function getTipo() {
        return $this->Tipo;
    }

    public function getBN() {
        return $this->BN;
    }

    public function getColor() {
        return $this->Color;
    }

    public function setTipo($Tipo) {
        $this->Tipo = $Tipo;
    }

    public function setBN($BN) {
        $this->BN = $BN;
    }

    public function setColor($Color) {
        $this->Color = $Color;
    }

    function startsWith($haystack, $needle) {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    function endsWith($haystack, $needle) {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

}

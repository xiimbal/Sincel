<?php

include_once ("Conexion.class.php");
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class FechasMantenimiento {

    private $Fechas = Array();
    private $tabla = Array();
    private $FechaInicio;
    private $FechaFin;
    private $Nserie;
    private $periocidad;
    private $dias;
    private $CentroCosto;
    private $estatus = 0;
    private $idEstado;
    private $userCreacion;
    private $conf = 0;

    public function crearFechas() {
        $contador = 0;
        unset($this->Fechas);
        $this->Fechas = Array();
        $this->tabla = Array($this->Nserie, $this->FechaInicio, $this->FechaFin);
        if ($this->FechaInicio < $this->FechaFin) {
            array_push($this->Fechas, $this->FechaInicio);
            $contador++;
            for (;;) {
                $fecha = $this->calculaFecha($this->periocidad, $this->dias, $this->FechaInicio);
                if ($fecha < $this->FechaFin) {
                    array_push($this->Fechas, $fecha);
                    $this->FechaInicio = $fecha;
                } else {
                    break;
                }
                $contador++;
            }
            
            if ($this->conf == 1) {
                $catalogo = new Catalogo();
                //$catalogo->insertarRegistro("DELETE FROM c_mantenimiento WHERE c_mantenimiento.NoSerie='" . $this->Nserie . "'");
                foreach ($this->Fechas as $value) {
                    $catalogo->insertarRegistro("INSERT INTO c_mantenimiento(ClaveCentroCosto,NoSerie,Fecha,Estatus,IdEstado,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                VALUES('" . $this->CentroCosto . "','" . $this->Nserie . "','" . $value . "','" . $this->estatus . "',$this->idEstado,'" . $this->userCreacion . "',NOW(),'" . $this->userCreacion . "',NOW(),'PHP AltaMantenimiento');");
                }
            }
        }
        array_push($this->tabla, $contador);
    }

    private function dia($calculo) {
        switch (date('w', strtotime($calculo))) {
            case 0: return 7;
                break;
            case 1: return 1;
                break;
            case 2: return 2;
                break;
            case 3: return 3;
                break;
            case 4: return 4;
                break;
            case 5: return 5;
                break;
            case 6: return 6;
                break;
        }
    }

    private function calculaFecha($modo, $valor, $fecha_inicio) {
        $fecha_base = strtotime($fecha_inicio);
        $calculo = strtotime("$valor $modo", "$fecha_base");
        /*if ($this->dia(date("Y-m-d", $calculo)) > 5) {
            $calculo = date("Y-m-d", strtotime("next Monday", $calculo));
            return $calculo;
        }*/
        return date("Y-m-d", $calculo);
    }

    /* private function dias_transcurridos($fecha_i, $fecha_f) {
      $dias = (strtotime($fecha_i) - strtotime($fecha_f)) / 86400;
      $dias = abs($dias);
      $dias = floor($dias);
      return $dias;
      } */

    public function setFechaInicio($FechaInicio) {
        $this->FechaInicio = $FechaInicio;
    }

    public function setNserie($Nserie) {
        $this->Nserie = $Nserie;
    }

    public function setFechaFin($FechaFin) {
        $this->FechaFin = $FechaFin;
    }

    public function setPeriocidad($periocidad) {
        if ($periocidad == 1) {
            $this->periocidad = "days";
        } elseif ($periocidad == 2) {
            $this->periocidad = "weeks";
        } else {
            $this->periocidad = "months";
        }
    }

    public function setDias($dias) {
        $this->dias = $dias;
    }

    public function setCentroCosto($CentroCosto) {
        $this->CentroCosto = $CentroCosto;
    }

    public function setUserCreacion($userCreacion) {
        $this->userCreacion = $userCreacion;
    }

    public function getTabla() {
        return $this->tabla;
    }

    public function getConf() {
        return $this->conf;
    }

    public function setConf($conf) {
        $this->conf = $conf;
    }

    function getIdEstado() {
        return $this->idEstado;
    }

    function setIdEstado($idEstado) {
        $this->idEstado = $idEstado;
    }

}

?>

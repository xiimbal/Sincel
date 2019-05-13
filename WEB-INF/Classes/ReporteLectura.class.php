<?php

include_once("Catalogo.class.php");
include_once("Lectura.class.php");
include_once("ServicioGeneral.class.php");
include_once("Cliente.class.php");
include_once("Zona.class.php");
include_once("CentroCosto.class.php");
include_once("CentroCostoReal.class.php");
include_once("Parametros.class.php");

/**
 * Description of ReporteLectura
 *
 * @author MAGG
 */
class ReporteLectura {

    private $fecha;
    private $periodo;
    private $contadorBNPagina;
    private $contadorColorPagina;
    private $contadorBNML;
    private $contadorColorML;
    private $fechaAnterior;
    private $contadorBNPaginaAnterior;
    private $contadorColorPaginaAnterior;
    private $contadorBNMLAnterior;
    private $contadorColorMLAnterior;
    private $TotalConceptosAdicionales;
    private $hayConceptosSeparados;
    private $NumeroConceptosColor;
    private $IdProductoSATRenta;
    private $IdProductoSATImpresion;
    private $atm_post;
    private $fields;
    private $empresa;

    /**
     * Regresa el costo total del servicio especificado
     * @param type $renta costo de la renta
     * @param type $impresasBN numero de impresiones BN
     * @param type $impresasColor numero de impresiones a color
     * @param type $incluidasBN paginas incluidas bn
     * @param type $incluidasColor paginas incluidas color
     * @param type $costoExcedenteBN costo por pagina excedente bn
     * @param type $costoExcedenteColor costo por pagina excedente color
     * @param type $costoProcesadasBN costo por pagina prodcesada bn
     * @param type $costoProcesadasColor costo por pagina procesada color
     * @param type $cobraRenta true en caso de que se cobre renta, false en caso contrario
     * @param type $cobraExcedentesBN true en caso de que se cobre excedentes bn, false en caso contrario
     * @param type $cobraExcedentesColor true en caso de que se cobre excedentes color, false en caso contrario
     * @param type $cobraProcesadasBN true en caso de que se cobre procesados bn, false en caso contrario
     * @param type $cobraProcesadasColor true en caso de que se cobre procesados color, false en caso contrario
     * @return type costo calculados del servicio segun parametros recibidos
     */
    public function calcularCostoServicio($renta, $impresasBN, $impresasColor, $incluidasBN, $incluidasColor, $costoExcedenteBN, $costoExcedenteColor, $costoProcesadasBN, $costoProcesadasColor, $cobraRenta, $cobraExcedentesBN, $cobraExcedentesColor, $cobraProcesadasBN, $cobraProcesadasColor) {
        $costo = 0;

        if ($cobraRenta && $renta != NULL && $renta > 0) {//Si se cobra la renta y el costo de la misma es mayor a cero se suma al costo final
            $costo += $renta;
        }

        if ($cobraExcedentesBN && $impresasBN > $incluidasBN) {
            $diferencia = $impresasBN - $incluidasBN;
            $costo += (int) $diferencia * (float) $costoExcedenteBN;
        }

        if ($cobraExcedentesColor && $impresasColor > $incluidasColor) {
            $diferencia = $impresasColor - $incluidasColor;
            $costo += (int) $diferencia * (float) $costoExcedenteColor;
        }

        if ($cobraProcesadasBN && $impresasBN > 0) {
            $costo += (int) $impresasBN * (float) $costoProcesadasBN;
        }

        if ($cobraProcesadasColor && $impresasColor > 0) {
            $costo += (int) $impresasColor * (float) $costoProcesadasColor;
        }

        return $costo;
    }

    /**
     * Obtiene la lectura de corte para el equipo y mes especificado.
     * @param type $NoSerie NoSerie
     * @param type $month numero de mes que se tomara como actual (indice 1)
     * @param type $year anio a 4 digitos (p.e. 2014)
     * @return boolean true en caso de encontrar la lectura de corte, false en cas contrario.
     */
    public function getLecturaMesActualCorte($NoSerie, $month, $year) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $back_up = false;
        $consulta = "SELECT IdTipoInventario FROM `c_bitacora` WHERE NoSerie = '$NoSerie';";        
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {            
            $back_up = ($rs['IdTipoInventario'] == "8") ? true : false;
        }        
        
        if (!$back_up) {
            $consulta = "SELECT * FROM `c_lectura` WHERE NoSerie = '$NoSerie' AND LecturaCorte = 1 AND MONTH(Fecha) = $month AND YEAR(Fecha)= $year ORDER BY IdLectura DESC;";
            $result = $catalogo->obtenerLista($consulta);
            while ($rs = mysql_fetch_array($result)) {
                $this->fecha = $rs['Fecha'];
                $this->contadorBNPagina = $rs['ContadorBNPaginas'];
                $this->contadorColorPagina = $rs['ContadorColorPaginas'];
                $this->contadorBNML = $rs['ContadorBNML'];
                $this->contadorColorML = $rs['ContadorColorML'];
                return true;
            }
        } else {            
            $this->fecha = "$year-$month-1";
            $this->contadorBNPagina = 0;
            $this->contadorColorPagina = 0;
            $this->contadorBNML = 0;
            $this->contadorColorML = 0;
            return true;
        }
        return false;
    }

    /**
     * Obtiene los contadores de la lectura mas cercana a la fecha de corte.
     * @param type $NoSerie
     * @param type $month numero de mes que se tomara como actual (indice 1)
     * @param type $year anio a 4 digitos (p.e. 2014)
     * @return int 0 c_lectura - 1 c_lecturaticket, null en caso de no encontar lecturas
     */
    public function getLecturaMesActual($NoSerie, $month, $year, $ClaveCliente) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $lectura = new Lectura();
        $diaCorte = $lectura->getDiaDeCorteByCliente($ClaveCliente);
        //$diaCorte = "24";//Fija provisionalmente  *********** Recordar cambiar ***********
        $intervalo = 10;
        $tipo = null; // 0 es c_lectura y 1 es c_lecturasticket        
        /* Obtenemos los datos de c_lectura */
        $consulta = "SELECT IdLectura, Fecha, ContadorBNPaginas, ContadorColorPaginas, ContadorBNML, ContadorColorML, ABS((SELECT DATEDIFF('$year-$month-$diaCorte', Fecha))) AS diferencia FROM `c_lectura` WHERE NoSerie = '$NoSerie' AND (Fecha BETWEEN DATE_SUB('$year-$month-$diaCorte',INTERVAL $intervalo DAY) AND DATE_ADD('$year-$month-$diaCorte',INTERVAL $intervalo DAY)) ORDER BY diferencia ASC LIMIT 0,1;";
        $result = $catalogo->obtenerLista($consulta);
        $diferencia = $intervalo + 1;
        while ($rs = mysql_fetch_array($result)) {
            $this->fecha = $rs['Fecha'];
            $diferencia = intval($rs['diferencia']);
            $this->contadorBNPagina = $rs['ContadorBNPaginas'];
            $this->contadorColorPagina = $rs['ContadorColorPaginas'];
            $this->contadorBNML = $rs['ContadorBNML'];
            $this->contadorColorML = $rs['ContadorColorML'];
            $tipo = 0;
        }
        /* Obtenemos los datos de c_lecturaticket */
        $consulta = "SELECT id_lecturaticket, Fecha, ContadorBN, ContadorCL, ABS((SELECT DATEDIFF('$year-$month-$diaCorte', Fecha))) AS diferencia FROM `c_lecturasticket` WHERE ClvEsp_Equipo = '$NoSerie' AND (Fecha BETWEEN DATE_SUB('$year-$month-$diaCorte',INTERVAL $intervalo DAY) AND DATE_ADD('$year-$month-$diaCorte',INTERVAL $intervalo DAY)) ORDER BY diferencia ASC LIMIT 0,1;";
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            /* Si la diferencia de dias de lecturas tickets es menor, tomamos esta lectura como la mas cercana a la fecha de corte */
            if (intval($rs['diferencia']) < $diferencia) {
                $this->fecha = $rs['Fecha'];
                $this->contadorBNPagina = $rs['ContadorBN'];
                $this->contadorColorPagina = $rs['ContadorCL'];
                $this->contadorBNML = $rs['ContadorBN'];
                $this->contadorColorML = $rs['ContadorCL'];
                $tipo = 1;
            }
        }
        return $tipo;
    }

    /**
     * Obtiene la lectura de corte para el equipo y mes especificado. Guarda los datos en los atributos de contadores anteriores.
     * @param type $NoSerie Numero de serie.
     * @param type $month numero de mes que se tomara como actual (indice 1)
     * @param type $year anio a 4 digitos (p.e. 2014)
     * @return boolean true en caso de encontrar la lectura de corte, false en cas contrario.
     */
    public function getLecturaMesAnteriorCorte($NoSerie, $month, $year) {        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        
        $back_up = false;
        $consulta = "SELECT IdTipoInventario FROM `c_bitacora` WHERE NoSerie = '$NoSerie';";
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            $back_up = ($rs['IdTipoInventario'] == "8") ? true : false;
        }
        
        if(!$back_up){
            $consulta = "SELECT * FROM `c_lectura` WHERE NoSerie = '$NoSerie' AND LecturaCorte = 1 AND MONTH(Fecha) = $month AND YEAR(Fecha)= $year ORDER BY IdLectura DESC;";
            $result = $catalogo->obtenerLista($consulta);
            while ($rs = mysql_fetch_array($result)) {
                $this->fechaAnterior = $rs['Fecha'];
                $this->contadorBNPaginaAnterior = $rs['ContadorBNPaginas'];
                $this->contadorColorPaginaAnterior = $rs['ContadorColorPaginas'];
                $this->contadorBNMLAnterior = $rs['ContadorBNML'];
                $this->contadorColorMLAnterior = $rs['ContadorColorML'];
                return true;
            }
        }else{
            $this->fechaAnterior = "$year-$month-1";
            $this->contadorBNPaginaAnterior = 0;
            $this->contadorColorPaginaAnterior = 0;
            $this->contadorBNMLAnterior = 0;
            $this->contadorColorMLAnterior = 0;
            return true;
        }
        return false;
    }

    /**
     * Obtiene los contadores de la lectura mas cercana a la fecha de corte.
     * @param type $NoSerie
     * @param type $month numero de mes que se tomara como actual (indice 1)
     * @param type $year anio a 4 digitos (p.e. 2014)
     * @return int 0 c_lectura - 1 c_lecturaticket, null en caso de no encontar lecturas
     */
    public function getLecturasSinMarcaMesAnterior($NoSerie, $month, $year, $ClaveCliente) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $lectura = new Lectura();
        $diaCorte = $lectura->getDiaDeCorteByCliente($ClaveCliente);
        $intervalo = 10;
        $tipo = null; // 0 es c_lectura y 1 es c_lecturasticket        
        /* Obtenemos los datos de c_lectura */
        $consulta = "SELECT IdLectura, Fecha, ContadorBNPaginas, ContadorColorPaginas, ContadorBNML, ContadorColorML, ABS((SELECT DATEDIFF('$year-$month-$diaCorte', Fecha))) AS diferencia FROM `c_lectura` WHERE NoSerie = '$NoSerie' AND (Fecha BETWEEN DATE_SUB('$year-$month-$diaCorte',INTERVAL $intervalo DAY) AND DATE_ADD('$year-$month-$diaCorte',INTERVAL $intervalo DAY)) AND LecturaCorte = 0 ORDER BY diferencia ASC LIMIT 0,1;";
        $result = $catalogo->obtenerLista($consulta);
        $diferencia = $intervalo + 1;
        while ($rs = mysql_fetch_array($result)) {
            $this->fechaAnterior = $rs['Fecha'];
            $diferencia = intval($rs['diferencia']);
            $this->contadorBNPaginaAnterior = $rs['ContadorBNPaginas'];
            $this->contadorColorPaginaAnterior = $rs['ContadorColorPaginas'];
            $this->contadorBNMLAnterior = $rs['ContadorBNML'];
            $this->contadorColorMLAnterior = $rs['ContadorColorML'];
            $tipo = 0;
        }
        /* Obtenemos los datos de c_lecturaticket */
        $consulta = "SELECT id_lecturaticket, Fecha, ContadorBN, ContadorCL, ABS((SELECT DATEDIFF('$year-$month-$diaCorte', Fecha))) AS diferencia FROM `c_lecturasticket` WHERE ClvEsp_Equipo = '$NoSerie' AND (Fecha BETWEEN DATE_SUB('$year-$month-$diaCorte',INTERVAL $intervalo DAY) AND DATE_ADD('$year-$month-$diaCorte',INTERVAL $intervalo DAY)) ORDER BY diferencia ASC LIMIT 0,1;";
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            /* Si la diferencia de dias de lecturas tickets es menor, tomamos esta lectura como la mas cercana a la fecha de corte */
            if (intval($rs['diferencia']) < $diferencia) {
                $this->fechaAnterior = $rs['Fecha'];
                $this->contadorBNPaginaAnterior = $rs['ContadorBN'];
                $this->contadorColorPaginaAnterior = $rs['ContadorCL'];
                $this->contadorBNMLAnterior = $rs['ContadorBN'];
                $this->contadorColorMLAnterior = $rs['ContadorCL'];
                $tipo = 1;
            }
        }
        return $tipo;
    }

    public function imprimirExcedentesPorFila($diferencia_bn, $diferencia_color) {
        $parametros = new Parametros();
        $mostrarContadores = true;
        if ($parametros->getRegistroById("13") && $parametros->getValor() == "0") {
            $mostrarContadores = false;
        }

        if ($mostrarContadores) {
            if ($diferencia_bn >= 0) {
                echo "<td class='borde excedente'>" . number_format($diferencia_bn, 0, '.', ',') . "</td>";
            } else {
                echo "<td class='borde excedente' style='color:red;'>" . number_format($diferencia_bn, 0, '.', ',') . "</td>";
            }

            if ($diferencia_color >= 0) {
                echo "<td class='borde excedente'>" . number_format($diferencia_color, 0, '.', ',') . "</td>";
            } else {
                echo "<td class='borde excedente' style='color:red;'>" . number_format($diferencia_color, 0, '.', ',') . "</td>";
            }
        }
    }

    /**
     * Imprimir contadores por fila.
     * @param type $contadorBNAnterior
     * @param type $contadorColorAnterior
     * @param type $contadorBN
     * @param type $contadorColor
     */
    public function imprimirContadores($contadorBNAnterior, $contadorColorAnterior, $contadorBN, $contadorColor) {
        $parametros = new Parametros();
        $mostrarContadores = true;
        if ($parametros->getRegistroById("13") && $parametros->getValor() == "0") {
            $mostrarContadores = false;
        }

        if ($mostrarContadores) {
            if (isset($contadorBNAnterior) && $contadorBNAnterior != null) {
                echo "<td class='borde'>$contadorBNAnterior</td>";
            } else {
                echo "<td class='borde'>S/L</td>";
            }

            if (isset($contadorColorAnterior) && $contadorColorAnterior != null) {
                echo "<td class='borde'>$contadorColorAnterior</td>";
            } else {
                echo "<td class='borde'>S/L</td>";
            }

            if (isset($contadorBN) && $contadorBN != null) {
                echo "<td class='borde'>" . $contadorBN . "</td>";
            } else {
                echo "<td class='borde'>S/L</td>";
            }

            if (isset($contadorColor) && $contadorColor != null) {
                echo "<td class='borde'>" . $contadorColor . "</td>";
            } else {
                echo "<td class='borde'>S/L</td>";
            }
        }
    }

    /**
     * Imprime la fila total de cada agrupación.
     * @param type $Total
     * @param type $contadorBNServicio
     * @param type $contadorColorServicio
     * @param type $costoRentaServicio
     * @param type $costoExcedentesBN
     * @param type $costoExcedentesColor
     * @param type $costoProBN
     * @param type $costoProColor
     * @param type $excedenteBNServicio
     * @param type $excedenteColorServicio
     * @param type $iva
     */
    public function imprimirTablaTotalAgrupacion($Total, $contadorBNServicio, $contadorColorServicio, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, $particularServicio, $excedenteBNServicio, $excedenteColorServicio, $iva, $idServicioByKServicio, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $mostrarDetalleServicio, $incluidosBNServicio, $incluidosColorServicio, $MostrarEquipos, $imprimir_cero, $agrupar_color, $NumeroConceptosColor) {
        $totalContadorBN = 0;
        $totalContadorColor = 0;
        /* Precios globales de paginas blanco y negro */
        foreach ($contadorBNServicio as $key => $value) {
            if ($value > 0) {
                $totalContadorBN += $value;
            }
            if ($particularServicio[$key] == 0) {//Solo se procesan los costos de servicios globales                
                if (isset($costoExcedentesBN[$key]) && isset($excedenteBNServicio[$key])) {
                    $excedentes = ($excedenteBNServicio[$key] - $incluidosBNServicio[$key]);
                    if ($excedentes < 0) {
                        $excedentes = 0;
                    }
                    $Total += ((float) $costoExcedentesBN[$key] * $excedentes);
                    if ($MostrarEquipos && ($excedentes > 0 || $imprimir_cero)) {
                        /* Concepto para la factura de paginas excedentes */
                        $um = $this->getUnidadMedida($idServicioByKServicio[$key], "Excedentes", $unidad_servicio);
                        $this->crearConceptoFactura($excedentes, $um, " PAGINAS IMPRESAS NEGRO: $value INCLUYE(" . $incluidosBNServicio[$key] . ")", $costoExcedentesBN[$key], $costoExcedentesBN[$key] * $excedentes, $NumeroFacturas, $NumeroConcepto, $this->IdProductoSATImpresion);
                        $NumeroConcepto++;
                    }
                }

                if (isset($costoProBN[$key])) {
                    $Total += ((float) $costoProBN[$key] * $value);
                    if ($MostrarEquipos && ($value > 0 || $imprimir_cero)) {
                        /* Concepto para la factura de paginas Procesadas */
                        $um = $this->getUnidadMedida($idServicioByKServicio[$key], "Impresiones", $unidad_servicio);
                        $this->crearConceptoFactura($value, $um, "PAGINAS IMPRESAS NEGRO: $value", $costoProBN[$key], $costoProBN[$key] * $value, $NumeroFacturas, $NumeroConcepto, $this->IdProductoSATImpresion);
                        $NumeroConcepto++;
                    }
                }
            }
        }
        /* Precios globales de paginas color */
        foreach ($contadorColorServicio as $key => $value) {
            if ($value > 0) {
                $totalContadorColor += $value;
            }
            if ($particularServicio[$key] == 0) {//Solo se procesan los costos de servicios globales                
                if (isset($costoExcedentesColor[$key]) && isset($excedenteColorServicio[$key])) {
                    $excedentes = $excedenteColorServicio[$key] - $incluidosColorServicio[$key];
                    if ($excedentes < 0) {
                        $excedentes = 0;
                    }
                    $Total += ((float) $costoExcedentesColor[$key] * $excedentes);
                    if ($MostrarEquipos && ($excedentes > 0 || $imprimir_cero)) {
                        /* Concepto para la factura de paginas excedentes */
                        $um = $this->getUnidadMedida($idServicioByKServicio[$key], "Excedentes", $unidad_servicio);
                        if ($agrupar_color) {
                            $auxFactura = $NumeroFacturas + 1;
                            $auxConcepto = $NumeroConceptosColor++;
                        } else {
                            $auxFactura = $NumeroFacturas;
                            $auxConcepto = $NumeroConcepto++;
                        }
                        $this->crearConceptoFactura($excedentes, $um, " PAGINAS IMPRESAS COLOR: $value INCLUYE(" . $incluidosColorServicio[$key] . ")", $costoExcedentesColor[$key], $costoExcedentesColor[$key] * $excedentes, $auxFactura, $auxConcepto, $this->IdProductoSATImpresion);
                        $NumeroConcepto++;
                    }
                }
                if (isset($costoProColor[$key])) {
                    $Total += ((float) $costoProColor[$key] * $value);
                    if ($MostrarEquipos && ($value > 0 || $imprimir_cero)) {
                        /* Concepto para la factura de paginas Procesadas */
                        $um = $this->getUnidadMedida($idServicioByKServicio[$key], "Impresiones", $unidad_servicio);

                        if ($agrupar_color) {
                            $auxFactura = $NumeroFacturas + 1;
                            $auxConcepto = $NumeroConceptosColor++;
                        } else {
                            $auxFactura = $NumeroFacturas;
                            $auxConcepto = $NumeroConcepto++;
                        }
                        $this->crearConceptoFactura($value, $um, "PAGINAS IMPRESAS COLOR: $value", $costoProColor[$key], $costoProColor[$key] * $value, $auxFactura, $auxConcepto, $this->IdProductoSATImpresion);
                        $NumeroConcepto++;
                    }
                }
            }
        }

        foreach ($costoRentaServicio as $key => $value) {
            if ($particularServicio[$key] == 0) {//Solo se procesan los costos de servicios globales
                $Total += (float) $value;
            }
        }

        $parametros = new Parametros();
        $mostrarContadores = true;
        if ($parametros->getRegistroById("13") && $parametros->getValor() == "0") {
            $mostrarContadores = false;
        }

        $subtotal = $Total;
        $Total = $subtotal * (1 + $iva);

        if ($mostrarContadores) {
            echo "<tr>
                <td colspan='8'></td>
                <td class='borde'>" . number_format($totalContadorBN, 0, '.', ',') . "</td>
                <td class='borde'>" . number_format($totalContadorColor, 0, '.', ',') . "</td>
                <td colspan='2'></td>";

            echo "<td class='borde' style='text-align:right;'>$" . number_format($subtotal, 2, '.', ',') . "</td>
                <td class='borde' style='text-align:right;'>$" . number_format($subtotal * $iva, 2, '.', ',') . "</td>
                <td class='borde' style='text-align:right;'>$" . number_format($Total, 2, '.', ',') . "</td>
                </tr>";
        } else {
            echo "<tr>
                <td colspan='4'></td>";

            echo "<td class='borde' style='text-align:right;'>$" . number_format($subtotal, 2, '.', ',') . "</td>
                <td class='borde' style='text-align:right;'>$" . number_format($subtotal * $iva, 2, '.', ',') . "</td>
                <td class='borde' style='text-align:right;'>$" . number_format($Total, 2, '.', ',') . "</td>
                </tr>";
        }
        $this->NumeroConceptosColor = $NumeroConceptosColor;
        return $NumeroConcepto++;
    }

    /**
     * Calcula el costo por equipo en servicio particular.
     * @param type $cobrarRenta
     * @param type $costoRenta
     * @param type $incluidosBN
     * @param type $cobrarExcedenteBN
     * @param type $excedentesBN
     * @param type $procesadosBN
     * @param type $diferencia_bn
     * @param type $diferencia_color
     * @param type $incluidosColor
     * @param type $cobrarExcedenteColor
     * @param type $excedentesColor
     * @param type $procesadosColor
     * @return int
     */
    public function calcularCostoParticularPorEquipo($cobrarRenta, $costoRenta, $incluidosBN, $cobrarExcedenteBN, $excedentesBN, $procesadosBN, $diferencia_bn, $diferencia_color, $incluidosColor, $cobrarExcedenteColor, $excedentesColor, $procesadosColor) {
        $totalParticular = 0;
        if ($cobrarRenta) {
            $totalParticular += (float) $costoRenta;
        }
        $aux = $diferencia_bn - $incluidosBN;
        if ($aux < 0) {
            $aux = 0;
        }

        if ($cobrarExcedenteBN) {//Para obtener el total por equipo
            $totalParticular += ($aux * $excedentesBN);
        } else if ($procesadosBN) {
            $totalParticular += ($diferencia_bn * $procesadosBN);
        }

        $aux1 = $diferencia_color - $incluidosColor;
        if ($aux1 < 0) {
            $aux1 = 0;
        }

        if ($cobrarExcedenteColor) {//Para obtener el total por equipo
            $totalParticular += ($aux1 * $excedentesColor);
        } else if ($procesadosColor) {
            $totalParticular += ($diferencia_color * $procesadosColor);
        }

        if ($totalParticular < 0) {
            $totalParticular = 0;
        }
        return $totalParticular;
    }

    public function imprimirDetalle($nombreAgrupaciones, $esParticularAgrupacion, $idServicioByKServicio, $incluidosBNAgrupacion, $incluidosColorAgrupacion, $equiposPorAgrupacion, $impresionesBNAgrupacion, $impresionesColorAgrupacion, $excedenteBNAgrupacion, $excedenteColorAgrupacion, $rentaAgrupacion, $costoExcedentesBNAgrupacion, $costoExcedentesColorAgrupacion, $costoProcesadasBNAgrupacion, $costoProcesadasColorAgrupacion, $iva, $mostrarRenta, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $imprimir_cero, $dividir_lecturas, $MostrarSeries, $seriesPorAgrupacion, $MostrarModelo, $agrupar_color, $NumeroConceptosColor) {
        $concepto_renta = 0;
        $rentas_globales_procesadas = array();
        $imprimio_renta = false;

        $parametros = new Parametros();
        $mostrarContadores = true;
        if ($parametros->getRegistroById("13") && $parametros->getValor() == "0") {
            $mostrarContadores = false;
        }

        //print_r($nombreAgrupaciones);
        foreach ($esParticularAgrupacion as $fila => $array) {
            foreach ($array as $columna => $value) {
                $totalServicio = 0;
                /* Se imprimen las filas de rentas */
                if (isset($rentaAgrupacion[$fila][$columna]) && !isset($rentas_globales_procesadas[$columna])) {
                    if ($esParticularAgrupacion[$fila][$columna] == 1) {//Si el servicios es particular
                        $rentas = $equiposPorAgrupacion[$fila][$columna];
                    } else {
                        $rentas = 1;
                    }

                    $subtotal = $rentaAgrupacion[$fila][$columna] * $rentas;
                    $iva_servico = $subtotal * $iva;
                    $totalServicio += ($subtotal);
                    if ($mostrarRenta && ($subtotal > 0 || $imprimir_cero)) {
                        $numeroColspan = "12";
                        if (!$mostrarContadores) {
                            $numeroColspan = "4";
                        }
                        echo "<tr>"
                        . "<td colspan='$numeroColspan' class='borde'>(" . $idServicioByKServicio[$fila][$columna] . ")[$columna] $nombreAgrupaciones[$fila] Renta ($rentas)</td>
                            <td class='borde' style='text-align:right;'>$" . number_format($subtotal, 2, '.', ',') . "</td>
                            <td class='borde' style='text-align:right;'>$" . number_format($iva_servico, 2, '.', ',') . "</td>
                            <td class='borde' style='text-align:right;'>$" . number_format($subtotal + $iva_servico, 2, '.', ',') . "</td>
                            </tr>";
                        if ($dividir_lecturas) {
                            $auxFactura = $NumeroFacturas + 1;
                            $auxConcepto = $concepto_renta++;
                        } else {
                            $auxFactura = $NumeroFacturas;
                            $auxConcepto = $NumeroConcepto++;
                        }
                        $unidad_medida = $this->getUnidadMedida($idServicioByKServicio[$fila][$columna], "Renta", $unidad_servicio);
                        if (($MostrarSeries || $MostrarModelo) && !$imprimio_renta) {
                            $concepto = "RENTA " . $equiposPorAgrupacion[$fila][$columna] . " EQUIPOS " . $nombreAgrupaciones[$fila] . "   " . $seriesPorAgrupacion[$fila][$columna];
                        } else {
                            $concepto = "RENTA " . $equiposPorAgrupacion[$fila][$columna] . " EQUIPOS " . $nombreAgrupaciones[$fila];
                        }
                        $this->crearConceptoFactura($rentas, $unidad_medida, $concepto, $rentaAgrupacion[$fila][$columna], $subtotal, $auxFactura, $auxConcepto, $this->IdProductoSATRenta);
                    }
                }
                /* Imprimir los excedentes */
                if ($mostrarContadores && isset($costoExcedentesBNAgrupacion[$fila][$columna])) {
                    if ($esParticularAgrupacion[$fila][$columna] == 0 && !isset($rentas_globales_procesadas[$columna])) {//Si el servicio es global, quitamos las paginas incluidas total
                        $excedenteBNAgrupacion[$fila][$columna] = $excedenteBNAgrupacion[$fila][$columna] - $incluidosBNAgrupacion[$fila][$columna];
                        $rentas_globales_procesadas[$columna] = 1;
                    }
                    if ($excedenteBNAgrupacion[$fila][$columna] < 0) {
                        $excedenteBNAgrupacion[$fila][$columna] = 0;
                    }
                    $subtotal = $costoExcedentesBNAgrupacion[$fila][$columna] * $excedenteBNAgrupacion[$fila][$columna];
                    $iva_servico = $subtotal * $iva;
                    $totalServicio += ($subtotal);
                    if ($mostrarRenta && ($subtotal > 0 || $imprimir_cero)) {
                        echo "<tr><td colspan='10' class='borde'>(" . $idServicioByKServicio[$fila][$columna] . ")[$columna] $nombreAgrupaciones[$fila] Excedentes B/N</td>
                            <td class='borde'>" . number_format($excedenteBNAgrupacion[$fila][$columna], 0, '.', ',') . " ($ " . $costoExcedentesBNAgrupacion[$fila][$columna] . " c/u)</td>
                            <td class='borde'></td>
                            <td class='borde' style='text-align:right;'>$" . number_format($subtotal, 2, '.', ',') . "</td>
                            <td class='borde' style='text-align:right;'>$" . number_format($iva_servico, 2, '.', ',') . "</td>
                            <td class='borde' style='text-align:right;'>$" . number_format($subtotal + $iva_servico, 2, '.', ',') . "</td>
                            </tr>";
                        $unidad_medida = $this->getUnidadMedida($idServicioByKServicio[$fila][$columna], "Excedentes", $unidad_servicio);
                        if (($MostrarSeries || $MostrarModelo) && !$imprimio_renta) {
                            $concepto = "PAGINAS IMPRESAS NEGRO: " . $impresionesBNAgrupacion[$fila][$columna] . " INCLUYE (" . $incluidosBNAgrupacion[$fila][$columna] . ") " . $nombreAgrupaciones[$fila] . "   " . $seriesPorAgrupacion[$fila][$columna];
                        } else {
                            $concepto = "PAGINAS IMPRESAS NEGRO: " . $impresionesBNAgrupacion[$fila][$columna] . " INCLUYE (" . $incluidosBNAgrupacion[$fila][$columna] . ") " . $nombreAgrupaciones[$fila];
                        }
                        $this->crearConceptoFactura($excedenteBNAgrupacion[$fila][$columna], $unidad_medida, $concepto, $costoExcedentesBNAgrupacion[$fila][$columna], $subtotal, $NumeroFacturas, $NumeroConcepto, $this->IdProductoSATImpresion);
                        $NumeroConcepto++;
                    }
                }
                /* Imprimir los excedentes colores */
                if ($mostrarContadores && isset($costoExcedentesColorAgrupacion[$fila][$columna])) {
                    if ($esParticularAgrupacion[$fila][$columna] == 0) {//Si el servicio es global, quitamos las paginas incluidas total
                        $excedenteColorAgrupacion[$fila][$columna] = $excedenteColorAgrupacion[$fila][$columna] - $incluidosColorAgrupacion[$fila][$columna];
                    }
                    if ($excedenteColorAgrupacion[$fila][$columna] < 0) {
                        $excedenteColorAgrupacion[$fila][$columna] = 0;
                    }
                    $subtotal = $costoExcedentesColorAgrupacion[$fila][$columna] * $excedenteColorAgrupacion[$fila][$columna];
                    $iva_servico = $subtotal * $iva;
                    $totalServicio += ($subtotal );
                    if ($mostrarRenta && ($subtotal > 0 || $imprimir_cero)) {
                        echo "<tr><td colspan='10' class='borde'>(" . $idServicioByKServicio[$fila][$columna] . ")[$columna] $nombreAgrupaciones[$fila] Excedentes Color</td>
                            <td class='borde'>" . number_format($excedenteColorAgrupacion[$fila][$columna], 0, '.', ',') . " ($ " . $costoExcedentesColorAgrupacion[$fila][$columna] . " c/u)</td>
                            <td class='borde'></td>
                            <td class='borde' style='text-align:right;'>$" . number_format($subtotal, 2, '.', ',') . "</td>
                            <td class='borde' style='text-align:right;'>$" . number_format($iva_servico, 2, '.', ',') . "</td>
                            <td class='borde' style='text-align:right;'>$" . number_format($subtotal + $iva_servico, 2, '.', ',') . "</td>
                            </tr>";
                        $unidad_medida = $this->getUnidadMedida($idServicioByKServicio[$fila][$columna], "Excedentes", $unidad_servicio);
                        if (($MostrarSeries || $MostrarModelo) && !$imprimio_renta) {
                            $concepto = "PAGINAS IMPRESAS COLOR: " . $impresionesBNAgrupacion[$fila][$columna] . " INCLUYE (" . $incluidosColorAgrupacion[$fila][$columna] . ") " . $nombreAgrupaciones[$fila] . "   " . $seriesPorAgrupacion[$fila][$columna];
                        } else {
                            $concepto = "PAGINAS IMPRESAS COLOR: " . $impresionesBNAgrupacion[$fila][$columna] . " INCLUYE (" . $incluidosColorAgrupacion[$fila][$columna] . ") " . $nombreAgrupaciones[$fila];
                        }
                        if ($agrupar_color) {
                            $auxFactura = $NumeroFacturas + 1;
                            $auxConcepto = $NumeroConceptosColor++;
                        } else {
                            $auxFactura = $NumeroFacturas;
                            $auxConcepto = $NumeroConcepto++;
                        }
                        $this->crearConceptoFactura($excedenteColorAgrupacion[$fila][$columna], $unidad_medida, $concepto, $costoExcedentesColorAgrupacion[$fila][$columna], $subtotal, $auxFactura, $auxConcepto, $this->IdProductoSATImpresion);
                    }
                }
                /* Imprimir los procesados */
                if ($mostrarContadores && isset($costoProcesadasBNAgrupacion[$fila][$columna])) {
                    $subtotal = $costoProcesadasBNAgrupacion[$fila][$columna] * $impresionesBNAgrupacion[$fila][$columna];
                    $iva_servico = $subtotal * $iva;
                    $totalServicio += ($subtotal);
                    if ($mostrarRenta && ($subtotal > 0 || $imprimir_cero)) {
                        echo "<tr><td colspan='10' class='borde'>(" . $idServicioByKServicio[$fila][$columna] . ")[$columna] $nombreAgrupaciones[$fila] Procesados B/N</td>
                            <td class='borde'>" . number_format($impresionesBNAgrupacion[$fila][$columna], 0, '.', ',') . " ($ " . $costoProcesadasBNAgrupacion[$fila][$columna] . " c/u)</td>
                            <td class='borde'></td>
                            <td class='borde' style='text-align:right;'>$" . number_format($subtotal, 2, '.', ',') . "</td>
                            <td class='borde' style='text-align:right;'>$" . number_format($iva_servico, 2, '.', ',') . "</td>
                            <td class='borde' style='text-align:right;'>$" . number_format($subtotal + $iva_servico, 2, '.', ',') . "</td>
                            </tr>";
                        $unidad_medida = $this->getUnidadMedida($idServicioByKServicio[$fila][$columna], "Excedentes", $unidad_servicio);
                        if (($MostrarSeries || $MostrarModelo) && !$imprimio_renta) {
                            $concepto = "PAGINAS IMPRESAS NEGRO: " . $impresionesBNAgrupacion[$fila][$columna] . " " . $nombreAgrupaciones[$fila] . "   " . $seriesPorAgrupacion[$fila][$columna];
                        } else {
                            $concepto = "PAGINAS IMPRESAS NEGRO: " . $impresionesBNAgrupacion[$fila][$columna] . " " . $nombreAgrupaciones[$fila];
                        }
                        $this->crearConceptoFactura($impresionesBNAgrupacion[$fila][$columna], $unidad_medida, $concepto, $costoProcesadasBNAgrupacion[$fila][$columna], $subtotal, $NumeroFacturas, $NumeroConcepto, $this->IdProductoSATImpresion);
                        $NumeroConcepto++;
                    }
                }

                /* Imprimir los procesados colores */
                if ($mostrarContadores && isset($costoProcesadasColorAgrupacion[$fila][$columna])) {
                    $subtotal = $costoProcesadasColorAgrupacion[$fila][$columna] * $impresionesColorAgrupacion[$fila][$columna];
                    $iva_servico = $subtotal * $iva;
                    $totalServicio += ($subtotal);
                    if ($mostrarRenta && ($subtotal > 0 || $imprimir_cero)) {
                        echo "<tr><td colspan='10' class='borde'>(" . $idServicioByKServicio[$fila][$columna] . ")[$columna] $nombreAgrupaciones[$fila] Procesados Color</td>
                            <td class='borde'>" . number_format($impresionesColorAgrupacion[$fila][$columna], 0, '.', ',') . " ($ " . $costoProcesadasColorAgrupacion[$fila][$columna] . " c/u)</td>
                            <td class='borde'></td>
                            <td class='borde' style='text-align:right;'>$" . number_format($subtotal, 2, '.', ',') . "</td>
                            <td class='borde' style='text-align:right;'>$" . number_format($iva_servico, 2, '.', ',') . "</td>
                            <td class='borde' style='text-align:right;'>$" . number_format($subtotal + $iva_servico, 2, '.', ',') . "</td>
                            </tr>";
                        $unidad_medida = $this->getUnidadMedida($idServicioByKServicio[$fila][$columna], "Excedentes", $unidad_servicio);
                        if (($MostrarSeries || $MostrarModelo) && !$imprimio_renta) {
                            $concepto = "PAGINAS IMPRESAS COLOR: " . $impresionesBNAgrupacion[$fila][$columna] . " " . $nombreAgrupaciones[$fila] . "   " . $seriesPorAgrupacion[$fila][$columna];
                        } else {
                            $concepto = "PAGINAS IMPRESAS COLOR: " . $impresionesBNAgrupacion[$fila][$columna] . " " . $nombreAgrupaciones[$fila];
                        }
                        if ($agrupar_color) {
                            $auxFactura = $NumeroFacturas + 1;
                            $auxConcepto = $NumeroConceptosColor++;
                        } else {
                            $auxFactura = $NumeroFacturas;
                            $auxConcepto = $NumeroConcepto++;
                        }
                        $this->crearConceptoFactura($impresionesColorAgrupacion[$fila][$columna], $unidad_medida, $concepto, $costoProcesadasColorAgrupacion[$fila][$columna], $subtotal, $auxFactura, $auxConcepto, $this->IdProductoSATImpresion);
                    }
                }

                if (!$mostrarRenta && ($totalServicio > 0 || $imprimir_cero)) {
                    $numeroColspan = "12";
                    if (!$mostrarContadores) {
                        $numeroColspan = "4";
                    }

                    $concepto = $nombreAgrupaciones[$fila];
                    $iva_servico = $totalServicio * $iva;
                    echo "<tr><td colspan='$numeroColspan' class='borde'>(" . $idServicioByKServicio[$fila][$columna] . ")[$columna] $concepto</td>                    
                           <td class='borde' style='text-align:right;'>$" . number_format($totalServicio, 2, '.', ',') . "</td>
                           <td class='borde' style='text-align:right;'>$" . number_format($iva_servico, 2, '.', ',') . "</td>
                           <td class='borde' style='text-align:right;'>$" . number_format($totalServicio + $iva_servico, 2, '.', ',') . "</td>
                           </tr>";
                    $this->crearConceptoFactura(1, "Servicio", $concepto, $totalServicio, $totalServicio, $NumeroFacturas, $NumeroConcepto, $this->IdProductoSATImpresion);
                    $NumeroConcepto++;
                }
            }
        }
        if ($dividir_lecturas) {
            echo "<input type='hidden' id='conceptos_factura_" . ($NumeroFacturas + 1) . "' name='conceptos_factura_" . ($NumeroFacturas + 1) . "' 
                value='$concepto_renta'/>";
            if ($this->getAtm_post()) {
                $this->fields['conceptos_factura_' . ($NumeroFacturas + 1)] = $concepto_renta;
            }
        } else if ($agrupar_color && $mostrarRenta) {
            echo "<input type='hidden' id='conceptos_factura_" . ($NumeroFacturas + 1) . "' name='conceptos_factura_" . ($NumeroFacturas + 1) . "' 
                value='$NumeroConceptosColor'/>";
            if ($this->getAtm_post()) {
                $this->fields['conceptos_factura_' . ($NumeroFacturas + 1)] = $concepto_renta;
            }
        }
        return $NumeroConcepto;
    }

    public function imprimirDetalleServicio($idKServicio, $idServicio, $prefijo, $impresionesBN, $impresionesColor, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProcesadosBN, $costoProcesadosColor, $particularServicio, $iva, $excedenteBNServicio, $excedenteColorServicio, $equiposPorServicio, $NumeroFacturas, $NumeroConcepto, $unidad_servicio, $prefijos, $incluidosBNServicio, $incluidosColorServicio, $mostrarRenta, $encabezado_variable, $incluidosColor, $incluidosBN, $imprimir_cero, $dividir_lecturas, $MostrarSeries, $seriesServicio, $MostrarModelo, $agrupar_color, $NumeroConceptosColor) {
        $totalServicio = 0;
        $concepto_renta = 0;
        $imprimio_renta = false;

        $parametros = new Parametros();
        $mostrarContadores = true;
        if ($parametros->getRegistroById("13") && $parametros->getValor() == "0") {
            $mostrarContadores = false;
        }

        if (isset($costoRentaServicio[$idKServicio])) {
            if ($particularServicio[$idKServicio] == 1) {
                $rentas = $equiposPorServicio;
            } else {
                $rentas = 1;
            }

            $subtotal = $costoRentaServicio[$idKServicio] * $rentas;
            $iva_servico = $subtotal * $iva;
            $totalServicio += ($subtotal);
            if ($mostrarRenta && ($subtotal > 0 || $imprimir_cero)) {
                $concepto_aux = $this->obtenerEncabezadoPrecios($encabezado_variable, $idServicio, $costoRentaServicio, $idKServicio, $incluidosColor, $incluidosBN, $costoExcedentesColor, $costoExcedentesBN, $costoProcesadosColor, $costoProcesadosBN);
                if ($this->contains("__0", $encabezado_variable[$idServicio])) {
                    $concepto_aux = "Renta";
                } else {
                    $concepto_aux = str_replace("__0", "", $concepto_aux);
                }

                $numeroColspan = "12";
                if (!$mostrarContadores) {
                    $numeroColspan = "4";
                }

                echo "<tr><td colspan='$numeroColspan' class='borde'>($idServicio)[$idKServicio] $concepto_aux ($rentas)</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($subtotal, 2, '.', ',') . "</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($iva_servico, 2, '.', ',') . "</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($subtotal + $iva_servico, 2, '.', ',') . "</td>
                    </tr>";
                if ($dividir_lecturas) {
                    $auxFactura = $NumeroFacturas + 1;
                    $auxConcepto = $concepto_renta++;
                } else {
                    $auxFactura = $NumeroFacturas;
                    $auxConcepto = $NumeroConcepto++;
                }
                $unidad_medida = $this->getUnidadMedida($idServicio, "Renta", $unidad_servicio);
                if (($MostrarSeries || $MostrarModelo) && !$imprimio_renta) {
                    if ($this->contains("__0", $encabezado_variable[$idServicio])) {
                        $concepto = "RENTA $equiposPorServicio EQUIPOS " . $seriesServicio[$idKServicio];
                    } else {
                        $concepto = str_replace("__0", "", $concepto_aux) . " " . $seriesServicio[$idKServicio];
                    }
                    $imprimio_renta = true;
                } else {
                    if ($this->contains("__0", $encabezado_variable[$idServicio])) {
                        $concepto = "RENTA $equiposPorServicio EQUIPOS $concepto_aux";
                    } else {
                        $concepto = str_replace("__0", "", $concepto_aux);
                    }
                }
                $this->crearConceptoFactura($rentas, $unidad_medida, $concepto, $costoRentaServicio[$idKServicio], $subtotal, $auxFactura, $auxConcepto, $this->IdProductoSATRenta);
            }
        }


        if ($particularServicio[$idKServicio] == 0) {
            $excedenteBNServicio[$idKServicio] = $excedenteBNServicio[$idKServicio] - $incluidosBNServicio[$idKServicio];
        }
        if ($mostrarContadores && isset($costoExcedentesBN[$idKServicio]) /* && $costoExcedentesBN[$idKServicio]!=0 && $excedenteBNServicio[$idKServicio] > 0 */) {
            if ($excedenteBNServicio[$idKServicio] < 0) {
                $excedenteBNServicio[$idKServicio] = 0;
            }
            $subtotal = $costoExcedentesBN[$idKServicio] * $excedenteBNServicio[$idKServicio];
            $iva_servico = $subtotal * $iva;
            $totalServicio += ($subtotal );
            if ($mostrarRenta && ($subtotal > 0 || $imprimir_cero)) {
                echo "<tr><td colspan='10' class='borde'>($idServicio)[$idKServicio] Excedentes B/N</td>
                    <td class='borde'>" . number_format($excedenteBNServicio[$idKServicio], 0, '.', ',') . " ($ " . $costoExcedentesBN[$idKServicio] . " c/u)</td>
                    <td class='borde'></td>
                    <td class='borde' style='text-align:right;'>$" . number_format($subtotal, 2, '.', ',') . "</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($iva_servico, 2, '.', ',') . "</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($subtotal + $iva_servico, 2, '.', ',') . "</td>
                    </tr>";
                $unidad_medida = $this->getUnidadMedida($idServicio, "Excedentes", $unidad_servicio);
                if (($MostrarSeries || $MostrarModelo) && !$imprimio_renta) {
                    $concepto = "PAGINAS IMPRESAS NEGRO: $impresionesBN INCLUYE (" . $incluidosBNServicio[$idKServicio] . ") " . $seriesServicio[$idKServicio];
                    $imprimio_renta = true;
                } else {
                    $concepto = "PAGINAS IMPRESAS NEGRO: $impresionesBN INCLUYE (" . $incluidosBNServicio[$idKServicio] . ") ";
                }
                $this->crearConceptoFactura($excedenteBNServicio[$idKServicio], $unidad_medida, $concepto, $costoExcedentesBN[$idKServicio], $subtotal, $NumeroFacturas, $NumeroConcepto, $this->IdProductoSATImpresion);
                $NumeroConcepto++;
            }
        }

        if ($particularServicio[$idKServicio] == 0) {
            $excedenteColorServicio[$idKServicio] = $excedenteColorServicio[$idKServicio] - $incluidosColorServicio[$idKServicio];
        }
        if ($mostrarContadores && isset($costoExcedentesColor[$idKServicio]) /* && $costoExcedentesColor[$idKServicio]!=0 && $excedenteColorServicio[$idKServicio] > 0 */) {
            if ($excedenteColorServicio[$idKServicio] < 0) {
                $excedenteColorServicio[$idKServicio] = 0;
            }

            $subtotal = $costoExcedentesColor[$idKServicio] * $excedenteColorServicio[$idKServicio];
            $iva_servico = $subtotal * $iva;
            $totalServicio += ($subtotal);
            if ($mostrarRenta && ($subtotal > 0 || $imprimir_cero)) {
                echo "<tr><td colspan='10' class='borde'>($idServicio)[$idKServicio] Excedentes Color</td>                
                    <td class='borde'></td>
                    <td class='borde'>" . number_format($excedenteColorServicio[$idKServicio], 0, '.', ',') . " ($" . $costoExcedentesColor[$idKServicio] . " c/u)</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($subtotal, 2, '.', ',') . "</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($iva_servico, 2, '.', ',') . "</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($subtotal + $iva_servico, 2, '.', ',') . "</td>
                    </tr>";
                $unidad_medida = $this->getUnidadMedida($idServicio, "Excedentes", $unidad_servicio);
                if (($MostrarSeries || $MostrarModelo) && !$imprimio_renta) {
                    $concepto = "PAGINAS IMPRESAS COLOR: $impresionesColor INCLUYE (" . $incluidosColorServicio[$idKServicio] . ") " . $seriesServicio[$idKServicio];
                    $imprimio_renta = true;
                } else {
                    $concepto = "PAGINAS IMPRESAS COLOR: $impresionesColor INCLUYE (" . $incluidosColorServicio[$idKServicio] . ") ";
                }
                if ($agrupar_color) {
                    $auxFactura = $NumeroFacturas + 1;
                    $auxConcepto = $NumeroConceptosColor++;
                } else {
                    $auxFactura = $NumeroFacturas;
                    $auxConcepto = $NumeroConcepto++;
                }
                $this->crearConceptoFactura($excedenteColorServicio[$idKServicio], $unidad_medida, $concepto, $costoExcedentesColor[$idKServicio], $subtotal, $auxFactura, $auxConcepto, $this->IdProductoSATImpresion);
            }
        }

        if ($mostrarContadores && isset($costoProcesadosBN[$idKServicio]) /* && $costoProcesadosBN[$idKServicio]!=0 && $impresionesBN > 0 */) {
            if ($impresionesBN < 0) {
                $impresionesBN = 0;
            }
            $subtotal = $costoProcesadosBN[$idKServicio] * $impresionesBN;
            $iva_servico = $subtotal * $iva;
            $totalServicio += ($subtotal);
            if ($mostrarRenta && ($subtotal > 0 || $imprimir_cero)) {
                echo "<tr><td colspan='8' class='borde'>($idServicio)[$idKServicio] Procesados B/N</td>
                    <td class='borde'>" . number_format($impresionesBN, 0, '.', ',') . " ($" . $costoProcesadosBN[$idKServicio] . " c/u)</td>
                    <td class='borde'></td>
                    <td class='borde'></td>
                    <td class='borde'></td>
                    <td class='borde' style='text-align:right;'>$" . number_format($subtotal, 2, '.', ',') . "</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($iva_servico, 2, '.', ',') . "</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($subtotal + $iva_servico, 2, '.', ',') . "</td>
                    </tr>";
                $unidad_medida = $this->getUnidadMedida($idServicio, "Impresiones", $unidad_servicio);
                if (($MostrarSeries || $MostrarModelo) && !$imprimio_renta) {
                    $concepto = "PAGINAS IMPRESAS PROCESADAS NEGRO: $impresionesBN " . $seriesServicio[$idKServicio];
                    $imprimio_renta = true;
                } else {
                    $concepto = "PAGINAS IMPRESAS PROCESADAS NEGRO: $impresionesBN";
                }
                $this->crearConceptoFactura($impresionesBN, $unidad_medida, $concepto, $costoProcesadosBN[$idKServicio], $subtotal, $NumeroFacturas, $NumeroConcepto, $this->IdProductoSATImpresion);
                $NumeroConcepto++;
            }
        }

        if ($mostrarContadores && isset($costoProcesadosColor[$idKServicio]) /* && $costoProcesadosColor[$idKServicio]!=0 && $impresionesColor > 0 */) {
            if ($impresionesColor < 0) {
                $impresionesColor = 0;
            }
            $subtotal = $costoProcesadosColor[$idKServicio] * $impresionesColor;
            $iva_servico = $subtotal * $iva;
            $totalServicio += ($subtotal);
            if ($mostrarRenta && ($subtotal > 0 || $imprimir_cero)) {
                echo "<tr><td colspan='8' class='borde'>($idServicio)[$idKServicio] Procesados Color</td>                
                    <td class='borde'></td>
                    <td class='borde'>" . number_format($impresionesColor, 0, '.', ',') . " ($" . $costoProcesadosColor[$idKServicio] . " c/u)</td>
                    <td class='borde'></td>
                    <td class='borde'></td>
                    <td class='borde' style='text-align:right;'>$" . number_format($subtotal, 2, '.', ',') . "</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($iva_servico, 2, '.', ',') . "</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($subtotal + $iva_servico, 2, '.', ',') . "</td>
                    </tr>";
                $unidad_medida = $this->getUnidadMedida($idServicio, "Impresiones", $unidad_servicio);
                if (($MostrarSeries || $MostrarModelo) && !$imprimio_renta) {
                    $concepto = "PAGINAS IMPRESAS PROCESADAS COLOR: $impresionesColor " . $seriesServicio[$idKServicio];
                    $imprimio_renta = true;
                } else {
                    $concepto = "PAGINAS IMPRESAS PROCESADAS COLOR: $impresionesColor";
                }
                if ($agrupar_color) {
                    $auxFactura = $NumeroFacturas + 1;
                    $auxConcepto = $NumeroConceptosColor++;
                } else {
                    $auxFactura = $NumeroFacturas;
                    $auxConcepto = $NumeroConcepto++;
                }
                $this->crearConceptoFactura($impresionesColor, $unidad_medida, $concepto, $costoProcesadosColor[$idKServicio], $subtotal, $auxFactura, $auxConcepto, $this->IdProductoSATImpresion);
            }
        }

        if (!$mostrarRenta && ($subtotal > 0 || $imprimir_cero)) {//Sino se muestra renta, quiere decir que solo se muestra una fila por servicio            
            $concepto = $this->obtenerEncabezadoPrecios($encabezado_variable, $idServicio, $costoRentaServicio, $idKServicio, $incluidosColor, $incluidosBN, $costoExcedentesColor, $costoExcedentesBN, $costoProcesadosColor, $costoProcesadosBN);
            $iva_servico = $totalServicio * $iva;
            $numeroColspan = "12";
            if (!$mostrarContadores) {
                $numeroColspan = "4";
            }
            echo "<tr><td colspan='$numeroColspan' class='borde'>($idServicio)[$idKServicio] $concepto</td>                    
                    <td class='borde' style='text-align:right;'>$" . number_format($totalServicio, 2, '.', ',') . "</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($iva_servico, 2, '.', ',') . "</td>
                    <td class='borde' style='text-align:right;'>$" . number_format($totalServicio + $iva_servico, 2, '.', ',') . "</td>
                    </tr>";
            $this->crearConceptoFactura(1, "Servicio", $concepto, $totalServicio, $totalServicio, $NumeroFacturas, $NumeroConcepto, $this->IdProductoSATImpresion);
            $NumeroConcepto++;
        }
        if ($dividir_lecturas) {
            echo "<input type='hidden' id='conceptos_factura_" . ($NumeroFacturas + 1) . "' name='conceptos_factura_" . ($NumeroFacturas + 1) . "' 
                value='$concepto_renta'/>";
            if ($this->getAtm_post()) {
                $this->fields['conceptos_factura_' . ($NumeroFacturas + 1)] = $concepto_renta;
            }
        } else if ($agrupar_color && $mostrarRenta) {
            echo "<input type='hidden' id='conceptos_factura_" . ($NumeroFacturas + 1) . "' name='conceptos_factura_" . ($NumeroFacturas + 1) . "' 
                value='$NumeroConceptosColor'/>";
            if ($this->getAtm_post()) {
                $this->fields['conceptos_factura_' . ($NumeroFacturas + 1)] = $concepto_renta;
            }
        }
        return $NumeroConcepto;
    }

    public function crearConceptosAdicionales($conceptos_adicionales, $cliente_por_factura, $anexos_por_factura, $zonas_por_factura, $cc_por_factura, $localidad_por_factura, $iva, $numFactura, $numConcepto, $procesadosSeparados, $dividir_lecturas) {
        $this->TotalConceptosAdicionales = 0;
        $separados = 0;
        $parametros = new Parametros();
        $mostrarContadores = true;
        if ($parametros->getRegistroById("13") && $parametros->getValor() == "0") {
            $mostrarContadores = false;
        }

        foreach ($conceptos_adicionales as $value) {
            $separado = false;
            $array_aux = $value;
            $array_seleccionado = array();
            if ($array_aux[0] == 1) {//Si el concepto adiciona es por cliente
                $array_seleccionado = $cliente_por_factura;
            } else if ($array_aux[0] == 2) {//Si el concepto es por anexo
                $array_seleccionado = $anexos_por_factura;
            } else if ($array_aux[0] == 3) {//Si el concepto es por localidad
                $array_seleccionado = $localidad_por_factura;
            } else if ($array_aux[0] == 4) {//Si el concepto es por zona
                $array_seleccionado = $zonas_por_factura;
            } else if ($array_aux[0] == 5) {//Si el concepto es por centro de costo
                $array_seleccionado = $cc_por_factura;
            } else {
                if (!$procesadosSeparados) {
                    $array_seleccionado = array(0 => 0);
                } else {
                    $array_seleccionado = array(0 => 1);
                }
            }

            foreach ($array_seleccionado as $key => $value2) {
                if ($value2 == 0) {
                    $descripcion_nombre = "";
                    if ($array_aux[0] == 1) {//Si el concepto adicional es por cliente
                        $obj = new Cliente();
                        if ($obj->getRegistroById($key)) {
                            $descripcion_nombre = "para cliente " . $obj->getNombreRazonSocial();
                        }
                    } else if ($array_aux[0] == 2) {//Si el concepto es por ClaveAnexoTecnico
                        $descripcion_nombre = "para anexo $key";
                    } else if ($array_aux[0] == 3) {//Si el concepto es por localidad
                        $obj = new CentroCosto();
                        if ($obj->getRegistroById($key)) {
                            $descripcion_nombre = "para localidad " . $obj->getNombre();
                        }
                    } else if ($array_aux[0] == 4) {//Si el concepto es por zona
                        $obj = new Zona();
                        if ($obj->getRegistroById($key)) {
                            $descripcion_nombre = "para zona " . $obj->getNombre();
                        }
                    } else if ($array_aux[0] == 5) {//Si el concepto es por centro de costo
                        $obj = new CentroCostoReal();
                        $obj->setId_cc($key);
                        if ($obj->getRegistrobyID()) {
                            $descripcion_nombre = "para centro de costo " . $obj->getNombre();
                        }
                    } else {
                        $descripcion_nombre = "(Concepto separado)";
                        $separado = true;
                        $this->hayConceptosSeparados = true;
                    }

                    if ($descripcion_nombre == "") {
                        $descripcion_nombre = " para $key";
                    }
                    $descripcion = "Servicios $descripcion_nombre";
                    $subtotal = (float) ($array_aux[2]) * (float) ($array_aux[3]);
                    //$subtotal = (float)($array_aux[2]);
                    $this->TotalConceptosAdicionales += $subtotal;
                    if ($separado) {
                        $auxFactura = $numFactura + 1;
                        if ($dividir_lecturas) {
                            $auxFactura++;
                        }
                        $auxConcepto = $separados++;
                    } else {
                        $auxFactura = $numFactura;
                        $auxConcepto = $numConcepto++;
                    }

                    $this->crearConceptoFactura($array_aux[2], "", $array_aux[1], $array_aux[3], $subtotal, $auxFactura, $auxConcepto, $array_aux[4]);
                    $numeroColspan = "12";
                    if (!$mostrarContadores) {
                        $numeroColspan = "4";
                    }

                    echo "<tr>";
                    echo "<td colspan='$numeroColspan' class='borde'>" . $array_aux[1] . "</td>";
                    echo "<td class='borde' style='text-align:right;'>$" . number_format($subtotal, 2, '.', ',') . "</td>";
                    echo "<td class='borde' style='text-align:right;'>$" . number_format($subtotal * $iva, 2, '.', ',') . "</td>";
                    echo "<td class='borde' style='text-align:right;'>$" . number_format($subtotal * (1 + $iva), 2, '.', ',') . "</td>";
                    echo "</tr>";
                }
            }
        }

        if ($this->hayConceptosSeparados) {
            echo "<input type='hidden' id='conceptos_factura_" . ($numFactura + 1) . "' name='conceptos_factura_" . ($numFactura + 1) . "' 
                value='$separados'/>";
            if ($this->getAtm_post()) {
                $this->fields['conceptos_factura_' . ($numFactura + 1)] = $separados;
            }
        }
        return $numConcepto;
    }

    public function ponerValorUno($array) {
        foreach ($array as $key => $value) {
            $array[$key] = 1;
        }
        return $array;
    }

    public function crearConceptoRentaFactura($particular, $cantidad, $unidad_medida, $descripcion, $IdKServicio, $costoRentaServicio, $idServicioByKServicio, $numFactura, $numConcepto) {
        $ServioGeneral = new ServicioGeneral();
        if ($ServioGeneral->getCobranzasByTipoServicio($idServicioByKServicio[$IdKServicio])) {
            $cobrarRenta = $ServioGeneral->getCobrarRenta();
        } else {
            $cobrarRenta = true;
        }
        if ($cobrarRenta) {
            $precio_unitario = $costoRentaServicio[$IdKServicio];
            if (!$particular) {
                //$descripcion = str_replace("RENTA EQUIPO", "RENTA $cantidad EQUIPOS", $descripcion);
                $from = "RENTA EQUIPO";
                $to = "RENTA $cantidad EQUIPOS EN GENERAL.";
                $subject = $descripcion;

                $from = '/' . preg_quote($from, '/') . '/';
                $descripcion = preg_replace($from, $to, $subject, 1);
                $cantidad = 1;
            }
            $importe = $cantidad * $precio_unitario;
            $this->crearConceptoFactura($cantidad, $unidad_medida, $descripcion, $precio_unitario, $importe, $numFactura, $numConcepto, $this->IdProductoSATRenta);
        }
    }

    public function crearConceptoFactura($cantidad, $unidad_medida, $descripcion, $precio_unitario, $importe, $numFactura, $numConcepto, $idProductoSAT = 51334) {
        if (isset($this->periodo) && !empty($this->periodo)) {
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            $descripcion .= ". Período " . substr($catalogo->formatoFechaReportes($this->periodo), 6);
        }
        echo "<input type='hidden' id='cantidad_" . $numFactura . "_" . $numConcepto . "' name='cantidad_" . $numFactura . "_" . $numConcepto . "' value='$cantidad'/>";
        echo "<input type='hidden' id='um_" . $numFactura . "_" . $numConcepto . "' name='um_" . $numFactura . "_" . $numConcepto . "' value='$unidad_medida'/>";
        echo "<input type='hidden' id='descripcion_" . $numFactura . "_" . $numConcepto . "' name='descripcion_" . $numFactura . "_" . $numConcepto . "' value='$descripcion'/>";
        echo "<input type='hidden' id='pu_" . $numFactura . "_" . $numConcepto . "' name='pu_" . $numFactura . "_" . $numConcepto . "' value='$precio_unitario'/>";
        echo "<input type='hidden' id='importe_" . $numFactura . "_" . $numConcepto . "' name='importe_" . $numFactura . "_" . $numConcepto . "' value='$importe'/>";
        echo "<input type='hidden' id='sat_" . $numFactura . "_" . $numConcepto . "' name='sat_" . $numFactura . "_" . $numConcepto . "' value='$idProductoSAT'/>";

        if ($this->getAtm_post()) {
            $this->fields['cantidad_' . $numFactura . "_" . $numConcepto] = $cantidad;
            $this->fields['um_' . $numFactura . "_" . $numConcepto] = $unidad_medida;
            $this->fields['descripcion_' . $numFactura . "_" . $numConcepto] = $descripcion;
            $this->fields['pu_' . $numFactura . "_" . $numConcepto] = $precio_unitario;
            $this->fields['importe_' . $numFactura . "_" . $numConcepto] = $importe;
            $this->fields['sat_' . $numFactura . "_" . $numConcepto] = $idProductoSAT;
        }
    }

    public function agregarValorNumericoArrayBidimensional($array, $fila, $columna, $valor) {
        if (isset($array[$fila][$columna])) {
            $array[$fila][$columna] += $valor;
        } else {
            $array[$fila][$columna] = $valor;
        }
        return $array;
    }

    public function obtenerMovimientoPorFecha($Cliente, $NoSerie, $mes, $anio) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT 
            meq.*,
            kacc.Fecha,
            (CASE WHEN (!ISNULL(meq.lectura) AND meq.lectura = 0) THEN l3.ContadorBNML WHEN (!ISNULL(meq.lectura) AND meq.lectura = 1) THEN l4.ContadorBNML WHEN !ISNULL(l.IdLectura) THEN l.ContadorBNML WHEN !ISNULL(l2.IdLectura) THEN l2.ContadorBNML ELSE 0 END) AS ContadorBNML, 
            (CASE WHEN (!ISNULL(meq.lectura) AND meq.lectura = 0) THEN l3.ContadorBNPaginas WHEN (!ISNULL(meq.lectura) AND meq.lectura = 1) THEN l4.ContadorBNPaginas WHEN !ISNULL(l.IdLectura) THEN l.ContadorBNPaginas WHEN !ISNULL(l2.IdLectura) THEN l2.ContadorBNPaginas ELSE 0 END) AS ContadorBNPaginas, 
            (CASE WHEN (!ISNULL(meq.lectura) AND meq.lectura = 0) THEN l3.ContadorColorML WHEN (!ISNULL(meq.lectura) AND meq.lectura = 1) THEN l4.ContadorColorML WHEN !ISNULL(l.IdLectura) THEN l.ContadorColorML WHEN !ISNULL(l2.IdLectura) THEN l2.ContadorColorML ELSE 0 END) AS ContadorColorML, 
            (CASE WHEN (!ISNULL(meq.lectura) AND meq.lectura = 0) THEN l3.ContadorColorPaginas WHEN (!ISNULL(meq.lectura) AND meq.lectura = 1) THEN l4.ContadorColorPaginas WHEN !ISNULL(l.IdLectura) THEN l.ContadorColorPaginas WHEN !ISNULL(l2.IdLectura) THEN l2.ContadorColorPaginas ELSE 0 END) AS ContadorColorPaginas 
            FROM movimientos_equipo AS meq  
            LEFT JOIN c_solicitud AS sol ON meq.id_solicitud = sol.id_solicitud
            LEFT JOIN c_cliente AS c ON (c.ClaveCliente = meq.clave_cliente_anterior OR c.ClaveCliente = meq.clave_cliente_nuevo)
            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = meq.clave_centro_costo_anterior
            LEFT JOIN c_cen_costo AS ccc ON cc.id_cr = ccc.id_cc 
            LEFT JOIN c_bitacora AS b ON b.NoSerie = meq.NoSerie
            LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
            LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = (SELECT MIN(NoContrato) FROM c_contrato WHERE ClaveCliente = c.ClaveCliente AND ctt.Activo = 1 )
            LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = (SELECT MIN(ClaveAnexoTecnico) FROM c_anexotecnico WHERE NoContrato = ctt.NoContrato)
            LEFT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = (SELECT MIN(IdAnexoClienteCC) FROM k_anexoclientecc WHERE ClaveAnexoTecnico = cat.ClaveAnexoTecnico)
            LEFT JOIN c_lectura AS l ON l.IdLectura = meq.id_lectura
            LEFT JOIN c_lectura AS l3 ON l3.IdLectura = meq.id_lectura2
            LEFT JOIN c_lectura AS l4 ON l4.IdSolicitud = meq.id_solicitud 
            LEFT JOIN c_lectura AS l2 ON l2.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE NoSerie = meq.NoSerie AND DATE(Fecha) = DATE(meq.Fecha))
            WHERE  meq.NoSerie = '$NoSerie'
            AND 
            (
            (meq.clave_cliente_anterior = '$Cliente' AND !ISNULL(meq.almacen_nuevo))
            OR
            (meq.clave_cliente_nuevo = '$Cliente' AND !ISNULL(meq.almacen_anterior))
            )
            AND meq.Fecha >= DATE_ADD(DATE_ADD(MAKEDATE(YEAR(DATE_SUB('$anio-$mes-01',INTERVAL 1 MONTH)), 1), INTERVAL (MONTH(DATE_SUB('$anio-$mes-01',INTERVAL 1 MONTH)))-1 MONTH), INTERVAL (DAY(kacc.Fecha))-1 DAY) 
            AND meq.Fecha < DATE_ADD(DATE_ADD(MAKEDATE(YEAR('$anio-$mes-01'), 1), INTERVAL (MONTH('$anio-$mes-01'))-1 MONTH), INTERVAL (DAY(kacc.Fecha))-1 DAY) ORDER BY meq.id_movimientos";
        
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    public function generarConsultaConRetiros($anexo, $cc, $cen_costo, $cliente, $contrato, $zona, $agrupar_equipo, $agrupar_localidad, $agrupar_cc, $agrupar_servicio, $agrupar_zona, $agrupar_tipo_servicio, $tipoDomicilioFiscal, $anio, $mes, $MostrarLocalidad, $HistoricoFacturacion, $FechaInstalacion) {
        //En la consulta, la primer parte antes del union son los equipos que actualmente estan en el cliente con sus datos para facturar, la segunda parte despues del union son los equipos que salieron durante el periodo.
        $select = "SELECT * FROM (SELECT 
            'TodoJunto' AS Junto,";
        if ($HistoricoFacturacion) {
            $select .= "(SELECT (CASE WHEN meq2.clave_cliente_nuevo = '$cliente' THEN 1 ELSE 0 END)
                FROM movimientos_equipo AS meq2 
                WHERE meq2.NoSerie = b.NoSerie 
                AND meq2.Fecha <= 
                DATE_ADD(DATE_ADD(MAKEDATE(YEAR(DATE_SUB('$anio-$mes-01',INTERVAL 1 MONTH)), 1), 
                INTERVAL (MONTH(DATE_SUB('$anio-$mes-01',INTERVAL 1 MONTH)))-1 MONTH), 
                INTERVAL (DAY(kacc.Fecha))-1 DAY) ORDER BY meq2.Fecha DESC, id_movimientos DESC LIMIT 0,1) AS EstabaConElCliente,";
        }
        $select .= "sl.PeriodoFac, 0 AS esMovimiento,
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN c.ClaveCliente ELSE (SELECT ClaveCliente FROM c_cliente WHERE c_cliente.ClaveCliente = cc2.ClaveCliente) END) AS ClaveCliente, 	
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.Nombre ELSE cc2.Nombre END) AS CentroCostoNombre, 
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.TipoDomicilioFiscal ELSE cc2.TipoDomicilioFiscal END) AS CentroCostoDomicilioFiscal,
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.ClaveCentroCosto ELSE ks.ClaveCentroCosto END) AS ClaveCentroCosto,
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.ClaveZona ELSE cc2.ClaveZona END) AS ClaveZona,
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN ccc1.nombre ELSE ccc2.nombre END) AS CentroCostoLocalidad,
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN ccc1.id_cc ELSE ccc2.id_cc END) AS idCen_Costo,
            (CASE WHEN !ISNULL(kecs.Id) THEN 1 ELSE 0 END) AS isColor,
            cinv.NoSerie AS NoSerie, cinv.NoParteEquipo, cinv.Ubicacion,
            b.VentaDirecta, b.id_bitacora,
            b.IdTipoInventario,
            ctt.NoContrato,
            c_equipo.Modelo AS Modelo,
            c.RFC,
            kacc.IdAnexoClienteCC,
            kacc.CveEspClienteCC,
            kacc.ClaveAnexoTecnico,     
            fp.Nombre AS FormaPago,
            (CASE WHEN !ISNULL(fe2.IdDatosFacturacionEmpresa) THEN fe2.ImagenPHP ELSE fe.ImagenPHP END) AS ImagenPHP,
            (CASE WHEN !ISNULL(fe2.IdDatosFacturacionEmpresa) THEN fe2.RFC ELSE fe.RFC END) AS RFCFacturacion,
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.EjecutivoCuenta FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.EjecutivoCuenta END) AS Usuario,
            ks.IdKserviciogimgfa,
            cim.Nombre AS Nombreim,
            im.IdKServicioIM AS IdKServicioim,
            im.IdServicioIM AS IdServicioim,
            im.RentaMensual AS imRenta,
            im.PaginasIncluidasBN AS imincluidosBN,
            im.PaginasIncluidasColor AS imincluidosColor,
            im.CostoPaginasExcedentesBN AS imExcedentesBN,
            im.CostoPaginasExcedentesColor AS imExcedentesColor,
            im.CostoPaginaProcesadaBN AS imProcesadasBN,
            im.CostoPaginaProcesadaColor AS imProcesadosColor,
            cfa.Nombre AS Nombrefa,
            fa.IdKServicioFA AS IdKServiciofa,
            fa.IdServicioFA AS IdServiciofa,
            fa.RentaMensual AS faRenta,
            fa.MLIncluidosBN AS faincluidosBN,
            fa.MLIncluidosColor AS faincluidosColor,
            fa.CostoMLExcedentesBN AS faExcedentesBN,
            fa.CostoMLExcedentesColor AS faExcedentesColor,
            fa.CostoMLProcesadosBN AS faProcesadasBN,
            fa.CostoMLProcesadosColor AS faProcesadosColor,
            cgim.Nombre AS Nombregim,
            gim.IdKServicioGIM AS IdKServiciogim,
            gim.IdServicioGIM AS IdServiciogim,
            gim.RentaMensual AS gimRenta,
            gim.PaginasIncluidasBN AS gimincluidosBN,
            gim.PaginasIncluidasColor AS gimincluidosColor,
            gim.CostoPaginasExcedentesBN AS gimExcedentesBN,
            gim.CostoPaginasExcedentesColor AS gimExcedentesColor,
            gim.CostoPaginaProcesadaBN AS gimProcesadasBN,
            gim.CostoPaginaProcesadaColor AS gimProcesadosColor,
            cgfa.Nombre AS Nombregfa,
            gfa.IdKServicioGFA AS IdKServiciogfa,
            gfa.IdServicioGFA AS IdServiciogfa,
            gfa.RentaMensual AS gfaRenta,
            gfa.MLIncluidosBN AS gfaincluidosBN,
            gfa.MLIncluidosColor AS gfaincluidosColor,
            gfa.CostoMLExcedentesBN AS gfaExcedentesBN,
            gfa.CostoMLExcedentesColor AS gfaExcedentesColor,
            gfa.CostoMLProcesadosBN AS gfaProcesadasBN,
            gfa.CostoMLProcesadosColor AS gfaProcesadosColor  
            FROM `c_inventarioequipo` AS cinv
            LEFT JOIN c_bitacora AS b ON b.NoSerie = cinv.NoSerie
            LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
            RIGHT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = cinv.IdAnexoClienteCC
            RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC
            LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
            LEFT JOIN c_cen_costo AS ccc1 ON cc.id_cr = ccc1.id_cc
            LEFT JOIN c_cen_costo AS ccc2 ON ks.ClaveCentroCosto = ccc2.id_cc
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
            LEFT JOIN c_datosfacturacionempresa AS fe ON fe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa
            LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
            LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
            LEFT JOIN k_equipocaracteristicaformatoservicio AS kecs ON kecs.Id = (SELECT MAX(ID) FROM k_equipocaracteristicaformatoservicio WHERE NoParte = c_equipo.NoParte AND IdTipoServicio = 1)
            LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = kacc.ClaveAnexoTecnico
            LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = cat.NoContrato
            LEFT JOIN c_datosfacturacionempresa AS fe2 ON fe2.IdDatosFacturacionEmpresa = ctt.RazonSocial 
            LEFT JOIN c_formapago AS fp ON fp.IdFormaPago = ctt.FormaPago
            LEFT JOIN c_solicitud AS sl ON sl.id_solicitud = b.id_solicitud
            LEFT JOIN k_serviciogim AS gim ON gim.IdKServicioGIM = (SELECT MIN(IdKServicioGIM) FROM k_serviciogim WHERE (cinv.IdKServicio = IdKServicioGIM OR (ISNULL(cinv.IdKServicio) AND cinv.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioGIM = cinv.ClaveEspKServicioFAIM)
            LEFT JOIN c_serviciogim AS cgim ON cgim.IdServicioGIM = gim.IdServicioGIM
            LEFT JOIN k_serviciogfa AS gfa ON gfa.IdKServicioGFA = (SELECT MIN(IdKServicioGFA) FROM k_serviciogfa WHERE (cinv.IdKServicio = IdKServicioGFA OR (ISNULL(cinv.IdKServicio) AND cinv.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioGFA = cinv.ClaveEspKServicioFAIM)
            LEFT JOIN c_serviciogfa AS cgfa ON cgfa.IdServicioGFA = gfa.IdServicioGFA
            LEFT JOIN k_servicioim AS im ON im.IdKServicioIM = (SELECT MIN(IdKServicioIM) FROM k_servicioim WHERE (cinv.IdKServicio = IdKServicioIM OR (ISNULL(cinv.IdKServicio) AND cinv.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioIM = cinv.ClaveEspKServicioFAIM)
            LEFT JOIN c_servicioim AS cim ON cim.IdServicioIM = im.IdServicioIM
            LEFT JOIN k_serviciofa AS fa ON fa.IdKServicioFA = (SELECT MIN(IdKServicioFA) FROM k_serviciofa WHERE (cinv.IdKServicio = IdKServicioFA OR (ISNULL(cinv.IdKServicio) AND cinv.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioFA = cinv.ClaveEspKServicioFAIM)
            LEFT JOIN c_serviciofa AS cfa ON cfa.IdServicioFA = fa.IdServicioFA ";
        $having = "";
        $where = " WHERE b.Activo = 1 ";
        if (!empty($anexo)) {
            if ($having == "") {
                $having = " HAVING ClaveAnexoTecnico = '$anexo' ";
            } else {
                $having .= " AND ClaveAnexoTecnico = '$anexo' ";
            }
        }

        if (!empty($zona)) {
            if ($having == "") {
                $having = " HAVING ClaveZona = '$zona' ";
            } else {
                $having .= " AND ClaveZona = '$zona' ";
            }
        }

        if (!empty($cc)) {
            if ($having == "") {
                $having = " HAVING ClaveCentroCosto = '$cc' ";
            } else {
                $having .= " AND ClaveCentroCosto = '$cc' ";
            }
        }

        if (!empty($cen_costo)) {
            if ($having == "") {
                $having = " HAVING idCen_Costo = '$cen_costo' ";
            } else {
                $having .= " AND idCen_Costo = '$cen_costo' ";
            }
        }

        if ($tipoDomicilioFiscal == 0) {
            if ($having == "") {
                $having = " HAVING (ISNULL(CentroCostoDomicilioFiscal) OR CentroCostoDomicilioFiscal = 0 OR CentroCostoDomicilioFiscal = 2) AND (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            } else {
                $having .= " AND (ISNULL(CentroCostoDomicilioFiscal) OR CentroCostoDomicilioFiscal = 0 OR CentroCostoDomicilioFiscal = 2) AND (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            }
        } else {
            if ($having == "") {
                $having = " HAVING CentroCostoDomicilioFiscal = 1 AND (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            } else {
                $having .= " AND CentroCostoDomicilioFiscal = 1 AND (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            }
        }

        if (!empty($cliente)) {
            if ($where == "") {
                $where = " WHERE c.ClaveCliente = '$cliente' ";
            } else {
                $where .= " AND c.ClaveCliente = '$cliente' ";
            }
        }

        if (!empty($contrato)) {
            if ($where == "") {
                $where = " WHERE ctt.NoContrato = '$contrato' ";
            } else {
                $where .= " AND ctt.NoContrato = '$contrato' ";
            }
        }

        if ($FechaInstalacion) {
            if ($where == "") {
                $where = " WHERE (ISNULL(sl.PeriodoFac) OR sl.PeriodoFac = '0000-00-00' OR DATE(sl.PeriodoFac) <= '$anio-$mes-01') ";
            } else {
                $where .= " AND (ISNULL(sl.PeriodoFac) OR sl.PeriodoFac = '0000-00-00' OR DATE(sl.PeriodoFac) <= '$anio-$mes-01') ";
            }
        }

        if ($where == "") {
            $where = " WHERE cinv.Demo = 0 ";
        } else {
            $where .= " AND cinv.Demo = 0 ";
        }

        //Vemos como se va a ordenar los datos de la consulta
        if ($agrupar_equipo) {
            $order_by = "CentroCostoNombre ASC, cinv.NoSerie ASC, ";
        } else if ($agrupar_tipo_servicio) {
            $order_by = "IdServiciogim DESC, IdServiciogfa DESC, IdServicioim DESC, IdServiciofa DESC, ";
        } else if ($agrupar_localidad) {
            $order_by = "CentroCostoNombre ASC, ";
        } else if ($agrupar_cc) {
            $order_by = "CentroCostoLocalidad ASC, CentroCostoNombre ASC, ";
        } else if ($agrupar_zona) {
            $order_by = "ClaveZona ASC, CentroCostoNombre ASC, ";
        } else if ($MostrarLocalidad) {
            $order_by = "CentroCostoNombre ASC, ";
        } else {
            $order_by = "";
        }

        $order_by .= " IdKServicioim DESC, IdKServiciofa DESC, IdKServiciogim DESC, IdKServiciogfa DESC ";
        //$consulta = "$select $where AND !ISNULL(cinv.NoSerie) $having ORDER BY $order_by;";
        $consulta = "$select $where AND !ISNULL(cinv.NoSerie) $having ";

        $having = "";
        $where = "";
        if (!empty($anexo)) {
            if ($having == "") {
                $having = " HAVING ClaveAnexoTecnico = '$anexo' ";
            } else {
                $having .= " AND ClaveAnexoTecnico = '$anexo' ";
            }
        }

        if (!empty($zona)) {
            if ($having == "") {
                $having = " HAVING ClaveZona = '$zona' ";
            } else {
                $having .= " AND ClaveZona = '$zona' ";
            }
        }

        if (!empty($cc)) {
            if ($having == "") {
                $having = " HAVING ClaveCentroCosto = '$cc' ";
            } else {
                $having .= " AND ClaveCentroCosto = '$cc' ";
            }
        }

        if (!empty($cen_costo)) {
            if ($having == "") {
                $having = " HAVING idCen_Costo = '$cen_costo' ";
            } else {
                $having .= " AND idCen_Costo = '$cen_costo' ";
            }
        }

        if ($tipoDomicilioFiscal == 0) {
            if ($having == "") {
                $having = " HAVING (ISNULL(CentroCostoDomicilioFiscal) OR CentroCostoDomicilioFiscal = 0 OR CentroCostoDomicilioFiscal = 2) AND (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            } else {
                $having .= " AND (ISNULL(CentroCostoDomicilioFiscal) OR CentroCostoDomicilioFiscal = 0 OR CentroCostoDomicilioFiscal = 2) AND (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            }
        } else {
            if ($having == "") {
                $having = " HAVING CentroCostoDomicilioFiscal = 1 AND (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            } else {
                $having .= " AND CentroCostoDomicilioFiscal = 1 AND (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            }
        }

        if (!empty($cliente)) {
            $where .= " AND c.ClaveCliente = '$cliente' ";
        }

        if (!empty($contrato)) {
            $where .= " AND ctt.NoContrato = '$contrato' ";
        }

        $consulta .= "UNION
            SELECT 'TodoJunto' AS Junto,";
        if ($HistoricoFacturacion) {
            $consulta .= "1 AS EstabaConElCliente,";
        }
        $consulta .= "NULL, 1 AS esMovimiento,
            c.ClaveCliente,cc.Nombre AS CentroCostoNombre,cc.TipoDomicilioFiscal AS CentroCostoDomicilioFiscal,
            cc.ClaveCentroCosto,
            cc.ClaveZona,
            ccc.nombre AS CentroCostoLocalidad, ccc.id_cc AS idCen_Costo,
            (CASE WHEN !ISNULL(kecs.Id) THEN 1 ELSE 0 END) AS isColor,
            b.NoSerie AS NoSerie, b.NoParte, 'Equipo salió del cliente' AS Ubicacion,
            0 AS VentaDirecta, b.id_bitacora,NULL,
            ctt.NoContrato,
            e.Modelo AS Modelo,
            c.RFC,
            kacc.IdAnexoClienteCC,
            kacc.CveEspClienteCC,
            kacc.ClaveAnexoTecnico,     
            fp.Nombre AS FormaPago,
            fe.ImagenPHP, fe.RFC AS RFCFacturacion,
            c.EjecutivoCuenta AS Usuario,
            meq.IdKserviciogimgfaAnterior,
            cim.Nombre AS Nombreim,
            im.IdKServicioIM AS IdKServicioim,
            im.IdServicioIM AS IdServicioim,
            im.RentaMensual AS imRenta,
            im.PaginasIncluidasBN AS imincluidosBN,
            im.PaginasIncluidasColor AS imincluidosColor,
            im.CostoPaginasExcedentesBN AS imExcedentesBN,
            im.CostoPaginasExcedentesColor AS imExcedentesColor,
            im.CostoPaginaProcesadaBN AS imProcesadasBN,
            im.CostoPaginaProcesadaColor AS imProcesadosColor,
            cfa.Nombre AS Nombrefa,
            fa.IdKServicioFA AS IdKServiciofa,
            fa.IdServicioFA AS IdServiciofa,
            fa.RentaMensual AS faRenta,
            fa.MLIncluidosBN AS faincluidosBN,
            fa.MLIncluidosColor AS faincluidosColor,
            fa.CostoMLExcedentesBN AS faExcedentesBN,
            fa.CostoMLExcedentesColor AS faExcedentesColor,
            fa.CostoMLProcesadosBN AS faProcesadasBN,
            fa.CostoMLProcesadosColor AS faProcesadosColor,
            cgim.Nombre AS Nombregim,
            gim.IdKServicioGIM AS IdKServiciogim,
            gim.IdServicioGIM AS IdServiciogim,
            gim.RentaMensual AS gimRenta,
            gim.PaginasIncluidasBN AS gimincluidosBN,
            gim.PaginasIncluidasColor AS gimincluidosColor,
            gim.CostoPaginasExcedentesBN AS gimExcedentesBN,
            gim.CostoPaginasExcedentesColor AS gimExcedentesColor,
            gim.CostoPaginaProcesadaBN AS gimProcesadasBN,
            gim.CostoPaginaProcesadaColor AS gimProcesadosColor,
            cgfa.Nombre AS Nombregfa,
            gfa.IdKServicioGFA AS IdKServiciogfa,
            gfa.IdServicioGFA AS IdServiciogfa,
            gfa.RentaMensual AS gfaRenta,
            gfa.MLIncluidosBN AS gfaincluidosBN,
            gfa.MLIncluidosColor AS gfaincluidosColor,
            gfa.CostoMLExcedentesBN AS gfaExcedentesBN,
            gfa.CostoMLExcedentesColor AS gfaExcedentesColor,
            gfa.CostoMLProcesadosBN AS gfaProcesadasBN,
            gfa.CostoMLProcesadosColor AS gfaProcesadosColor  
            FROM movimientos_equipo AS meq
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = meq.clave_cliente_anterior
            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = meq.clave_centro_costo_anterior
            LEFT JOIN c_cen_costo AS ccc ON cc.id_cr = ccc.id_cc 
            LEFT JOIN c_bitacora AS b ON b.NoSerie = meq.NoSerie
            LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
            LEFT JOIN k_equipocaracteristicaformatoservicio AS kecs ON kecs.Id = (SELECT MAX(ID) FROM k_equipocaracteristicaformatoservicio WHERE NoParte = e.NoParte AND IdTipoServicio = 1) 
            LEFT JOIN c_contrato AS ctt ON ctt.ClaveCliente = c.ClaveCliente            
            LEFT JOIN c_formapago AS fp ON fp.IdFormaPago = ctt.FormaPago 
            LEFT JOIN c_datosfacturacionempresa AS fe ON fe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa 
            LEFT JOIN c_lectura AS l ON l.IdLectura = meq.id_lectura 
            LEFT JOIN k_serviciogim AS gim ON gim.IdKServicioGIM = (SELECT MIN(IdKServicioGIM) FROM k_serviciogim WHERE (meq.IdKServicioAnterior = IdKServicioGIM OR (ISNULL(meq.IdKServicioAnterior) AND meq.IdAnexoClienteCCAnterior = IdAnexoClienteCC)) AND IdServicioGIM = meq.IdServicioAnterior)
            LEFT JOIN c_serviciogim AS cgim ON cgim.IdServicioGIM = gim.IdServicioGIM 
            LEFT JOIN k_serviciogfa AS gfa ON gfa.IdKServicioGFA = (SELECT MIN(IdKServicioGFA) FROM k_serviciogfa WHERE (meq.IdKServicioAnterior = IdKServicioGFA OR (ISNULL(meq.IdKServicioAnterior) AND meq.IdAnexoClienteCCAnterior = IdAnexoClienteCC)) AND IdServicioGFA = meq.IdServicioAnterior)
            LEFT JOIN c_serviciogfa AS cgfa ON cgfa.IdServicioGFA = gfa.IdServicioGFA 
            LEFT JOIN k_servicioim AS im ON im.IdKServicioIM = (SELECT MIN(IdKServicioIM) FROM k_servicioim WHERE (meq.IdKServicioAnterior = IdKServicioIM OR (ISNULL(meq.IdKServicioAnterior) AND meq.IdAnexoClienteCCAnterior = IdAnexoClienteCC)) AND IdServicioIM = meq.IdServicioAnterior)
            LEFT JOIN c_servicioim AS cim ON cim.IdServicioIM = im.IdServicioIM 
            LEFT JOIN k_serviciofa AS fa ON fa.IdKServicioFA = (SELECT MIN(IdKServicioFA) FROM k_serviciofa WHERE (meq.IdKServicioAnterior = IdKServicioFA OR (ISNULL(meq.IdKServicioAnterior) AND meq.IdAnexoClienteCCAnterior = IdAnexoClienteCC)) AND IdServicioFA = meq.IdServicioAnterior)
            LEFT JOIN c_serviciofa AS cfa ON cfa.IdServicioFA = fa.IdServicioFA 
            LEFT JOIN k_anexoclientecc AS kacc ON (kacc.IdAnexoClienteCC = gim.IdAnexoClienteCC OR kacc.IdAnexoClienteCC = gfa.IdAnexoClienteCC OR kacc.IdAnexoClienteCC = im.IdAnexoClienteCC OR kacc.IdAnexoClienteCC = fa.IdAnexoClienteCC)
            LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = kacc.ClaveAnexoTecnico
            LEFT JOIN movimientos_equipo AS meq2 ON meq2.id_movimientos =
            (SELECT MAX(id_movimientos)
            FROM movimientos_equipo
            WHERE NoSerie = meq.NoSerie AND meq.clave_cliente_anterior = clave_cliente_nuevo AND id_movimientos <> meq.id_movimientos
            )
            LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = meq2.id_solicitud
            WHERE b.Activo = 1 AND meq.FacturarMovimiento = 1 AND meq.clave_cliente_anterior = '$cliente' AND (ISNULL(meq.clave_cliente_nuevo) OR meq.clave_cliente_nuevo <> '$cliente')
            AND meq.Fecha >= DATE_ADD(DATE_ADD(MAKEDATE(YEAR(DATE_SUB('$anio-$mes-01',INTERVAL 1 MONTH)), 1), INTERVAL (MONTH(DATE_SUB('$anio-$mes-01',INTERVAL 1 MONTH)))-1 MONTH), INTERVAL (DAY(kacc.Fecha))-1 DAY) 
            AND (ISNULL(cs.id_solicitud) OR cs.id_tiposolicitud <> 4)            
            /*AND meq.Fecha < DATE_ADD(DATE_ADD(MAKEDATE(YEAR('$anio-$mes-01'), 1), INTERVAL (MONTH('$anio-$mes-01'))-1 MONTH), INTERVAL (DAY(kacc.Fecha))-1 DAY) */
            AND meq.Fecha <= NOW()
            $where $having)";
        if ($HistoricoFacturacion) {
            $consulta .= "AS t WHERE ISNULL(EstabaConElCliente) OR EstabaConElCliente = 1 GROUP BY NoSerie ORDER BY $order_by;";
        } else {
            $consulta .= "AS t GROUP BY NoSerie ORDER BY $order_by;";
        }
        //echo $consulta;
        return $consulta;
    }

    public function generarConsulta($anexo, $cc, $cen_costo, $cliente, $contrato, $zona, $agrupar_equipo, $agrupar_localidad, $agrupar_cc, $agrupar_servicio, $agrupar_zona, $agrupar_tipo_servicio, $tipoDomicilioFiscal) {
        $select = "SELECT 
            'TodoJunto' AS Junto,
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN c.ClaveCliente ELSE (SELECT ClaveCliente FROM c_cliente WHERE c_cliente.ClaveCliente = cc2.ClaveCliente) END) AS ClaveCliente, 	
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.Nombre ELSE cc2.Nombre END) AS CentroCostoNombre, 
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.TipoDomicilioFiscal ELSE cc2.TipoDomicilioFiscal END) AS CentroCostoDomicilioFiscal,
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.ClaveCentroCosto ELSE ks.ClaveCentroCosto END) AS ClaveCentroCosto,
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.ClaveZona ELSE cc2.ClaveZona END) AS ClaveZona,
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN ccc1.nombre ELSE ccc2.nombre END) AS CentroCostoLocalidad,
            (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN ccc1.id_cc ELSE ccc2.id_cc END) AS idCen_Costo,
            (CASE WHEN !ISNULL(kecs.Id) THEN 1 ELSE 0 END) AS isColor,
            cinv.NoSerie AS NoSerie, cinv.NoParteEquipo, cinv.Ubicacion,
            b.VentaDirecta, b.id_bitacora,
            ctt.NoContrato,
            c_equipo.Modelo AS Modelo,
            c.RFC,
            kacc.IdAnexoClienteCC,
            kacc.CveEspClienteCC,
            kacc.ClaveAnexoTecnico,     
            fp.Nombre AS FormaPago,
            (CASE WHEN !ISNULL(fe2.IdDatosFacturacionEmpresa) THEN fe2.ImagenPHP ELSE fe.ImagenPHP END) AS ImagenPHP,
            (CASE WHEN !ISNULL(fe2.IdDatosFacturacionEmpresa) THEN fe2.RFC ELSE fe.RFC END) AS RFCFacturacion,
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.EjecutivoCuenta FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.EjecutivoCuenta END) AS Usuario,
            ks.IdKserviciogimgfa,
            cim.Nombre AS Nombreim,
            im.IdKServicioIM AS IdKServicioim,
            im.IdServicioIM AS IdServicioim,
            im.RentaMensual AS imRenta,
            im.PaginasIncluidasBN AS imincluidosBN,
            im.PaginasIncluidasColor AS imincluidosColor,
            im.CostoPaginasExcedentesBN AS imExcedentesBN,
            im.CostoPaginasExcedentesColor AS imExcedentesColor,
            im.CostoPaginaProcesadaBN AS imProcesadasBN,
            im.CostoPaginaProcesadaColor AS imProcesadosColor,
            cfa.Nombre AS Nombrefa,
            fa.IdKServicioFA AS IdKServiciofa,
            fa.IdServicioFA AS IdServiciofa,
            fa.RentaMensual AS faRenta,
            fa.MLIncluidosBN AS faincluidosBN,
            fa.MLIncluidosColor AS faincluidosColor,
            fa.CostoMLExcedentesBN AS faExcedentesBN,
            fa.CostoMLExcedentesColor AS faExcedentesColor,
            fa.CostoMLProcesadosBN AS faProcesadasBN,
            fa.CostoMLProcesadosColor AS faProcesadosColor,
            cgim.Nombre AS Nombregim,
            gim.IdKServicioGIM AS IdKServiciogim,
            gim.IdServicioGIM AS IdServiciogim,
            gim.RentaMensual AS gimRenta,
            gim.PaginasIncluidasBN AS gimincluidosBN,
            gim.PaginasIncluidasColor AS gimincluidosColor,
            gim.CostoPaginasExcedentesBN AS gimExcedentesBN,
            gim.CostoPaginasExcedentesColor AS gimExcedentesColor,
            gim.CostoPaginaProcesadaBN AS gimProcesadasBN,
            gim.CostoPaginaProcesadaColor AS gimProcesadosColor,
            cgfa.Nombre AS Nombregfa,
            gfa.IdKServicioGFA AS IdKServiciogfa,
            gfa.IdServicioGFA AS IdServiciogfa,
            gfa.RentaMensual AS gfaRenta,
            gfa.MLIncluidosBN AS gfaincluidosBN,
            gfa.MLIncluidosColor AS gfaincluidosColor,
            gfa.CostoMLExcedentesBN AS gfaExcedentesBN,
            gfa.CostoMLExcedentesColor AS gfaExcedentesColor,
            gfa.CostoMLProcesadosBN AS gfaProcesadasBN,
            gfa.CostoMLProcesadosColor AS gfaProcesadosColor  
            FROM `c_inventarioequipo` AS cinv
            LEFT JOIN c_bitacora AS b ON b.NoSerie = cinv.NoSerie
            LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
            RIGHT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = cinv.IdAnexoClienteCC
            RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC
            LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
            LEFT JOIN c_cen_costo AS ccc1 ON cc.id_cr = ccc1.id_cc
            LEFT JOIN c_cen_costo AS ccc2 ON ks.ClaveCentroCosto = ccc2.id_cc
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
            LEFT JOIN c_datosfacturacionempresa AS fe ON fe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa
            LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
            LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
            LEFT JOIN k_equipocaracteristicaformatoservicio AS kecs ON kecs.Id = (SELECT MAX(ID) FROM k_equipocaracteristicaformatoservicio WHERE NoParte = c_equipo.NoParte AND IdTipoServicio = 1)
            LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = kacc.ClaveAnexoTecnico
            LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = cat.NoContrato
            LEFT JOIN c_datosfacturacionempresa AS fe2 ON fe2.IdDatosFacturacionEmpresa = ctt.RazonSocial 
            LEFT JOIN c_formapago AS fp ON fp.IdFormaPago = ctt.FormaPago
            LEFT JOIN k_serviciogim AS gim ON gim.IdKServicioGIM = (SELECT MIN(IdKServicioGIM) FROM k_serviciogim WHERE (cinv.IdKServicio = IdKServicioGIM OR (ISNULL(cinv.IdKServicio) AND cinv.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioGIM = cinv.ClaveEspKServicioFAIM)
            LEFT JOIN c_serviciogim AS cgim ON cgim.IdServicioGIM = gim.IdServicioGIM
            LEFT JOIN k_serviciogfa AS gfa ON gfa.IdKServicioGFA = (SELECT MIN(IdKServicioGFA) FROM k_serviciogfa WHERE (cinv.IdKServicio = IdKServicioGFA OR (ISNULL(cinv.IdKServicio) AND cinv.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioGFA = cinv.ClaveEspKServicioFAIM)
            LEFT JOIN c_serviciogfa AS cgfa ON cgfa.IdServicioGFA = gfa.IdServicioGFA
            LEFT JOIN k_servicioim AS im ON im.IdKServicioIM = (SELECT MIN(IdKServicioIM) FROM k_servicioim WHERE (cinv.IdKServicio = IdKServicioIM OR (ISNULL(cinv.IdKServicio) AND cinv.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioIM = cinv.ClaveEspKServicioFAIM)
            LEFT JOIN c_servicioim AS cim ON cim.IdServicioIM = im.IdServicioIM
            LEFT JOIN k_serviciofa AS fa ON fa.IdKServicioFA = (SELECT MIN(IdKServicioFA) FROM k_serviciofa WHERE (cinv.IdKServicio = IdKServicioFA OR (ISNULL(cinv.IdKServicio) AND cinv.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioFA = cinv.ClaveEspKServicioFAIM)
            LEFT JOIN c_serviciofa AS cfa ON cfa.IdServicioFA = fa.IdServicioFA ";
        $having = "";
        $where = "";
        if (!empty($anexo)) {
            if ($having == "") {
                $having = " HAVING ClaveAnexoTecnico = '$anexo' ";
            } else {
                $having .= " AND ClaveAnexoTecnico = '$anexo' ";
            }
        }

        if (!empty($zona)) {
            if ($having == "") {
                $having = " HAVING ClaveZona = '$zona' ";
            } else {
                $having .= " AND ClaveZona = '$zona' ";
            }
        }

        if (!empty($cc)) {
            if ($having == "") {
                $having = " HAVING ClaveCentroCosto = '$cc' ";
            } else {
                $having .= " AND ClaveCentroCosto = '$cc' ";
            }
        }

        if (!empty($cen_costo)) {
            if ($having == "") {
                $having = " HAVING idCen_Costo = '$cen_costo' ";
            } else {
                $having .= " AND idCen_Costo = '$cen_costo' ";
            }
        }

        if ($tipoDomicilioFiscal == 0) {
            if ($having == "") {
                $having = " HAVING (ISNULL(CentroCostoDomicilioFiscal) OR CentroCostoDomicilioFiscal = 0 OR CentroCostoDomicilioFiscal = 2) AND (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            } else {
                $having .= " AND (ISNULL(CentroCostoDomicilioFiscal) OR CentroCostoDomicilioFiscal = 0 OR CentroCostoDomicilioFiscal = 2) AND (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            }
        } else if ($tipoDomicilioFiscal == 1) {
            if ($having == "") {
                $having = " HAVING CentroCostoDomicilioFiscal = 1 AND (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            } else {
                $having .= " AND CentroCostoDomicilioFiscal = 1 AND (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            }
        } else if (empty($tipoDomicilioFiscal)) {
            if ($having == "") {
                $having = " HAVING (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            } else {
                $having .= " AND (ISNULL(VentaDirecta) OR VentaDirecta = 0)";
            }
        }

        if (!empty($cliente)) {
            if ($where == "") {
                $where = " WHERE c.ClaveCliente = '$cliente' ";
            } else {
                $where .= " AND c.ClaveCliente = '$cliente' ";
            }
        }

        if (!empty($contrato)) {
            if ($where == "") {
                $where = " WHERE ctt.NoContrato = '$contrato' ";
            } else {
                $where .= " AND ctt.NoContrato = '$contrato' ";
            }
        }

        //Vemos como se va a ordenar los datos de la consulta
        if ($agrupar_equipo) {
            $order_by = "CentroCostoNombre ASC, cinv.NoSerie ASC, ";
        } else if ($agrupar_tipo_servicio) {
            $order_by = "gim.IdServicioGIM DESC, gfa.IdServicioGFA DESC, im.IdServicioIM DESC, fa.IdServicioFA DESC, ";
        } else if ($agrupar_localidad) {
            $order_by = "CentroCostoNombre ASC, ";
        } else if ($agrupar_cc) {
            $order_by = "CentroCostoLocalidad ASC, CentroCostoNombre ASC, ";
        } else if ($agrupar_zona) {
            $order_by = "ClaveZona ASC, CentroCostoNombre ASC, ";
        } else {
            $order_by = "";
        }

        $order_by .= "gim.IdKServicioGIM DESC, gfa.IdKServicioGFA DESC, im.IdKServicioIM DESC, fa.IdKServicioFA DESC";
        $consulta = "$select $where AND !ISNULL(cinv.NoSerie) $having ORDER BY $order_by;";
        //echo $consulta;
        return $consulta;
    }

    public function getUnidadMedida($idServicio, $um, $unidad_servicio) {
        $encontrado = false;
        if (is_array($unidad_servicio)) {
            foreach ($unidad_servicio as $key => $value) {
                if ($value[0] == $idServicio && $value[1] == $um) {
                    $um = $value[2];
                    $encontrado = true;
                    break;
                }
            }
        }
        if (!$encontrado) {
            $um = "Servicio";
        }
        return $um;
    }

    public function getUMServicio($POST) {
        $unidad_servicio = array();
        foreach ($POST as $key => $value) {
            if ($value != null & $value != "") {
                if ($this->startsWith($key, "text_serv_renta_")) {//Obtenemos todos los parametros que inicien con el texto especificado.
                    $last_index = strrpos($key, "_"); //Obtiene el ultimo indice del substring indicado (guion bajo)
                    $servicio = substr($key, $last_index + 1, strlen($key) - $last_index); //Obtenemos el id del servicios que viene concatenado en el nombre del post                
                    $aux = array($servicio, "Renta", $value);
                    array_push($unidad_servicio, $aux);
                } else if ($this->startsWith($key, "text_serv_excedente_")) {
                    $last_index = strrpos($key, "_"); //Obtiene el ultimo indice del substring indicado (guion bajo)
                    $servicio = substr($key, $last_index + 1, strlen($key) - $last_index); //Obtenemos el id del servicios que viene concatenado en el nombre del post                
                    $aux = array($servicio, "Excedentes", $value);
                    array_push($unidad_servicio, $aux);
                } else if ($this->startsWith($key, "text_serv_impresiones_")) {
                    $last_index = strrpos($key, "_"); //Obtiene el ultimo indice del substring indicado (guion bajo)
                    $servicio = substr($key, $last_index + 1, strlen($key) - $last_index); //Obtenemos el id del servicios que viene concatenado en el nombre del post                
                    $aux = array($servicio, "Impresiones", $value);
                    array_push($unidad_servicio, $aux);
                }
            }
        }
        return $unidad_servicio;
    }

    /**
     * Crea el arreglo con los encabezados personalizados por servicio.
     * @param type $POST arreglo post recibido en la pagina
     * @return type
     */
    public function getEncabezadoServicio($POST, $prefijos) {
        $encabezado_variable = array();
        foreach ($POST as $key => $value) {
            if ($value != null & $value != "") {
                if ($this->startsWith($key, "text_serv_nom_")) {//Obtenemos todos los parametros que inicien con el texto especificado.
                    $last_index = strrpos($key, "_"); //Obtiene el ultimo indice del substring indicado (guion bajo)
                    $servicio = substr($key, $last_index + 1, strlen($key) - $last_index); //Obtenemos el id del servicios que viene concatenado en el nombre del post
                    $encabezado_variable[$servicio] = $value; //Guardamos en el array con posicion Id_Servicio el valor ingresdo por el usuario.
                }
            }
        }
        //Se completan los servicios que no esten personalizados por lo que esta guardado en la base de datos
        return $this->completarServiciosDefault($encabezado_variable, $prefijos);
    }

    public function completarServiciosDefault($array, $prefijos) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        foreach ($prefijos as $value) {
            $value_mayus = strtoupper($value);
            $value_minus = strtolower($value);
            $consulta = "SELECT IdServicio$value_mayus, Nombre FROM `c_servicio$value_minus` WHERE Activo = 1;";
            $result = $catalogo->obtenerLista($consulta);
            while ($rs = mysql_fetch_array($result)) {
                if (!isset($array[$rs['IdServicio' . $value_mayus]])) {
                    $array[$rs['IdServicio' . $value_mayus]] = $rs['Nombre'] . "__0";
                }
            }
        }
        return $array;
    }

    private function obtenerEncabezadoPrecios($encabezados, $idServicio, $costoRentaServicio, $idKServicio, $incluidosColor, $incluidosBN, $costoExcedentesColor, $costoExcedentesBN, $costoProcesadosColor, $costoProcesadosBN) {
        $encabezado = $encabezados[$idServicio];
        $encabezado = str_replace("[]", "$" . number_format($costoRentaServicio[$idKServicio], 2, '.', ','), $encabezado);
        $encabezado = str_replace("***", number_format($incluidosColor, 0, '.', ','), $encabezado);
        $encabezado = str_replace("**", number_format($incluidosBN, 0, '.', ','), $encabezado);
        $encabezado = str_replace("+++", "$" . number_format($costoExcedentesColor[$idKServicio], 2, '.', ','), $encabezado);
        $encabezado = str_replace("++", "$" . number_format($costoExcedentesBN[$idKServicio], 2, '.', ','), $encabezado);
        $encabezado = str_replace("---", "$" . number_format($costoProcesadosColor[$idKServicio], 2, '.', ','), $encabezado);
        $encabezado = str_replace("--", "$" . number_format($costoProcesadosBN[$idKServicio], 2, '.', ','), $encabezado);
        return $encabezado;
    }

    public function imprimirEncabezadoServicio($encabezados, $incluidosBN, $incluidosColor, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProcesadosBN, $costoProcesadosColor, $idKServicio, $idServicio, $agrupar_color, $numFactura, $numConcepto) {
        $parametros = new Parametros();
        $mostrarContadores = true;
        if ($parametros->getRegistroById("13") && $parametros->getValor() == "0") {
            $mostrarContadores = false;
        }

        $numeroColspan = "15";
        if (!$mostrarContadores) {
            $numeroColspan = "7";
        }

        $encabezado = $this->obtenerEncabezadoPrecios($encabezados, $idServicio, $costoRentaServicio, $idKServicio, $incluidosColor, $incluidosBN, $costoExcedentesColor, $costoExcedentesBN, $costoProcesadosColor, $costoProcesadosBN);
        $encabezado = str_replace("__0", "", $encabezado);
        echo "<tr>";
        echo "<td colspan='$numeroColspan' class='borde' style='background: yellow'>$idServicio. $encabezado</td>";
        echo "</tr>";
        $this->crearConceptoFactura("", "", $encabezado, "", "", $numFactura, $numConcepto, $this->IdProductoSATImpresion);
        echo "<input type='hidden' id='encabezado_" . $numFactura . "_" . $numConcepto . "' name='encabezado_" . $numFactura . "_" . $numConcepto . "' value='1'/>";
        if ($this->getAtm_post()) {
            $this->fields['encabezado_' . $numFactura . "_" . $numConcepto] = 1;
        }
        return $numConcepto + 1;
    }

    public function getConceptosAdicionales($POST) {
        $conceptos_adicionales = array();
        $numero_conceptos = $POST['filas_conceptos'];
        for ($i = 0; $i < $numero_conceptos; $i++) {
            if (isset($POST['select_con_adic_' . $i]) && $POST['select_con_adic_' . $i] != "") {
                $aux = array($POST['select_con_adic_' . $i], $POST['text_con_adic_' . $i], $POST['cantidad_con_adic_' . $i], $POST['preciounitario_con_adic_' . $i],
                    $POST['producto_adic_' . $i]);
                array_push($conceptos_adicionales, $aux);
            }
        }
        return $conceptos_adicionales;
    }

    public function obtenerIdPrefijo($prefijo, $prefijos) {
        $i = 0;
        for ($i = 0; $i < count($prefijos); $i++) {
            if ($prefijos[$i] == $prefijo) {
                return $i;
            }
        }
        return $i;
    }

    public function isParticularByPrefijo($prefijo) {
        if ($prefijo <= 1) {//Si son los primeros dos prefijos (gim, gfa), entonces el equipo está en renta global.
            return false;
        } else {//Renta global.
            return true;
        }
    }

    function startsWith($haystack, $needle) {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    function endsWith($haystack, $needle) {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    // returns true if $needle is a substring of $haystack
    function contains($needle, $haystack) {
        return strpos($haystack, $needle) !== false;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function getContadorBNPagina() {
        return $this->contadorBNPagina;
    }

    public function setContadorBNPagina($contadorBNPagina) {
        $this->contadorBNPagina = $contadorBNPagina;
    }

    public function getContadorColorPagina() {
        return $this->contadorColorPagina;
    }

    public function setContadorColorPagina($contadorColorPagina) {
        $this->contadorColorPagina = $contadorColorPagina;
    }

    public function getContadorBNML() {
        return $this->contadorBNML;
    }

    public function setContadorBNML($contadorBNML) {
        $this->contadorBNML = $contadorBNML;
    }

    public function getContadorColorML() {
        return $this->contadorColorML;
    }

    public function setContadorColorML($contadorColorML) {
        $this->contadorColorML = $contadorColorML;
    }

    public function getFechaAnterior() {
        return $this->fechaAnterior;
    }

    public function setFechaAnterior($fechaAnterior) {
        $this->fechaAnterior = $fechaAnterior;
    }

    public function getContadorBNPaginaAnterior() {
        return $this->contadorBNPaginaAnterior;
    }

    public function setContadorBNPaginaAnterior($contadorBNPaginaAnterior) {
        $this->contadorBNPaginaAnterior = $contadorBNPaginaAnterior;
    }

    public function getContadorColorPaginaAnterior() {
        return $this->contadorColorPaginaAnterior;
    }

    public function setContadorColorPaginaAnterior($contadorColorPaginaAnterior) {
        $this->contadorColorPaginaAnterior = $contadorColorPaginaAnterior;
    }

    public function getContadorBNMLAnterior() {
        return $this->contadorBNMLAnterior;
    }

    public function setContadorBNMLAnterior($contadorBNMLAnterior) {
        $this->contadorBNMLAnterior = $contadorBNMLAnterior;
    }

    public function getContadorColorMLAnterior() {
        return $this->contadorColorMLAnterior;
    }

    public function setContadorColorMLAnterior($contadorColorMLAnterior) {
        $this->contadorColorMLAnterior = $contadorColorMLAnterior;
    }

    public function getTotalConceptosAdicionales() {
        return $this->TotalConceptosAdicionales;
    }

    public function setTotalConceptosAdicionales($TotalConceptosAdicionales) {
        $this->TotalConceptosAdicionales = $TotalConceptosAdicionales;
    }

    public function getHayConceptosSeparados() {
        return $this->hayConceptosSeparados;
    }

    public function setHayConceptosSeparados($hayConceptosSeparados) {
        $this->hayConceptosSeparados = $hayConceptosSeparados;
    }

    public function getNumeroConceptosColor() {
        return $this->NumeroConceptosColor;
    }

    public function setNumeroConceptosColor($NumeroConceptosColor) {
        $this->NumeroConceptosColor = $NumeroConceptosColor;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function getFields() {
        return $this->fields;
    }

    public function setFields($fields) {
        $this->fields = $fields;
    }

    public function getAtm_post() {
        return $this->atm_post;
    }

    public function setAtm_post($atm_post) {
        $this->atm_post = $atm_post;
    }

    function getPeriodo() {
        return $this->periodo;
    }

    function setPeriodo($periodo) {
        $this->periodo = $periodo;
    }

    function getIdProductoSATRenta() {
        return $this->IdProductoSATRenta;
    }

    function getIdProductoSATImpresion() {
        return $this->IdProductoSATImpresion;
    }

    function setIdProductoSATRenta($IdProductoSATRenta) {
        $this->IdProductoSATRenta = $IdProductoSATRenta;
    }

    function setIdProductoSATImpresion($IdProductoSATImpresion) {
        $this->IdProductoSATImpresion = $IdProductoSATImpresion;
    }

}

?>

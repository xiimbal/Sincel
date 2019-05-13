<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['cliente']) || !isset($_POST['localidad'])) {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/ReporteLectura.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Contacto.class.php");
include_once("../WEB-INF/Classes/Zona.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");

$catalogo = new Catalogo();
$obj_cliente = new Cliente();
$obj_direccion = new Localidad();
$obj_contacto = new Contacto();
$obj_zona = new Zona();
$caracteristicas = new EquipoCaracteristicasFormatoServicio();

$cliente = $_POST['cliente'];
$cc = $_POST['localidad'];
$anexo = $_POST['anexo'];

$obj_cliente->getRegistroById($cliente);
$obj_direccion->getLocalidadByClave($cliente);
$obj_contacto->getContactoByClaveEspecial($cliente);
$obj_zona->getRegistroById($obj_cliente->getClaveZona());

$select = "SELECT cie.NoSerie,
	cie.NoParteEquipo,
	cie.ClaveEspKServicioFAIM,
	e.Modelo,
	kacc.IdAnexoClienteCC,
	kacc.CveEspClienteCC,
	cc.Nombre,
	im.IdKServicioIM AS IdKServicioim,
	im.IdServicioIM,
	im.RentaMensual AS imRenta,
	im.PaginasIncluidasBN AS imincluidosBN,
	im.PaginasIncluidasColor AS imincluidosColor,
	im.CostoPaginasExcedentesBN AS imExcedentesBN,
	im.CostoPaginasExcedentesColor AS imExcedentesColor,
	im.CostoPaginaProcesadaBN AS imProcesadasBN,
	im.CostoPaginaProcesadaColor AS imProcesadosColor,
	fa.IdKServicioFA AS IdKServiciofa,
	fa.RentaMensual AS faRenta,
	fa.MLIncluidosBN AS faincluidosBN,
	fa.MLIncluidosColor AS faincluidosColor,
	fa.CostoMLExcedentesBN AS faExcedentesBN,
	fa.CostoMLExcedentesColor AS faExcedentesColor,
	fa.CostoMLProcesadosBN AS faProcesadasBN,
	fa.CostoMLProcesadosColor AS faProcesadosColor,
	gim.IdKServicioGIM AS IdKServiciogim,
	gim.IdServicioGIM,
	gim.RentaMensual AS gimRenta,
	gim.PaginasIncluidasBN AS gimincluidosBN,
	gim.PaginasIncluidasColor AS gimincluidosColor,
	gim.CostoPaginasExcedentesBN AS gimExcedentesBN,
	gim.CostoPaginasExcedentesColor AS gimExcedentesColor,
	gim.CostoPaginaProcesadaBN AS gimProcesadasBN,
	gim.CostoPaginaProcesadaColor AS gimProcesadosColor,
	gfa.IdKServicioGFA AS IdKServiciogfa,
	gfa.RentaMensual AS gfaRenta,
	gfa.MLIncluidosBN AS gfaincluidosBN,
	gfa.MLIncluidosColor AS gfaincluidosColor,
	gfa.CostoMLExcedentesBN AS gfaExcedentesBN,
	gfa.CostoMLExcedentesColor AS gfaExcedentesColor,
	gfa.CostoMLProcesadosBN AS gfaProcesadasBN,
	gfa.CostoMLProcesadosColor AS gfaProcesadosColor ";
if(!empty($anexo)){//La consulta debe de ser por anexo
    $filtro = "";
    if(!empty($cc)){ /*Si viene por anexo pero tambien se selecciono una localidad*/
        $filtro = "AND kacc.CveEspClienteCC = '$cc'";
    }
    $consulta = $select."
    FROM k_anexoclientecc AS kacc
    LEFT JOIN k_serviciogim AS gim ON gim.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN k_serviciogfa AS gfa ON gfa.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN k_servicioim AS im ON im.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN k_serviciofa AS fa ON fa.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN c_inventarioequipo AS cie ON cie.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC
    LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo
    WHERE kacc.ClaveAnexoTecnico = '$anexo' $filtro";
}else if(!empty ($cc)){//la consulta debe de ser por localidad
    $consulta = $select."
    FROM k_anexoclientecc AS kacc
    LEFT JOIN k_serviciogim AS gim ON gim.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN k_serviciogfa AS gfa ON gfa.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN k_servicioim AS im ON im.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN k_serviciofa AS fa ON fa.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN c_inventarioequipo AS cie ON cie.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC
    LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo
    WHERE kacc.CveEspClienteCC = '$cc'";
}else{//la consulta debe de ser por cliente
    $consulta =$select."
    FROM `c_contrato` AS ctt
    INNER JOIN c_anexotecnico AS cat ON ctt.Activo = 1 AND ctt.FechaTermino >= NOW() AND ctt.NoContrato = cat.NoContrato AND cat.Activo = 1
    INNER JOIN k_anexoclientecc AS kacc ON kacc.ClaveAnexoTecnico = cat.ClaveAnexoTecnico
    LEFT JOIN k_serviciogim AS gim ON gim.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN k_serviciogfa AS gfa ON gfa.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN k_servicioim AS im ON im.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN k_serviciofa AS fa ON fa.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN c_inventarioequipo AS cie ON cie.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC
    LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo
    WHERE ctt.ClaveCliente = '$cliente'";
}
$consulta.=" AND !ISNULL(cie.NoSerie) ORDER BY im.IdKServicioIM DESC, fa.IdKServicioFA DESC, gim.IdKServicioGIM DESC,  gfa.IdKServicioGFA DESC";
//echo $consulta."<br/>";
$result = $catalogo->obtenerLista($consulta);
?>
<!DOCTYPE>
<html lang="es">
    <head>
        <title>Reporte de facturación</title>
        <style>
            table{
                border-collapse:collapse;
            }            
            .borde{border: 1px solid #000;}
        </style>
    </head>
    <body>
        <a href=javascript:window.print(); style="margin: 85%;">Imprimir</a>
        <?php
            $prefijos = array("im","fa","gim","gfa");//Prefijos a revisar (siempre se toman las prioridades: im, fa, gim, gfa)
            $prefijo_pagml = array("PAGINAS","ML","PAGINAS","ML"); //Este array debe de ir asociado a los prefijos del array prefijos
            $prefijo = 0;
            $no_equipo = 0;
            $nueva_hoja = true;
            if(mysql_num_rows ($result) > 0){
                $contadorBNTotal = 0;
                $contadorColorTotal = 0;
                while($rs = mysql_fetch_array($result)){   
                    $reporte = new ReporteLectura();
                    while(!isset($rs['IdKServicio'.$prefijos[$prefijo]])){
                        if(!$nueva_hoja){/*Cerramos la ultima hoja abierta*/
                            echo "</table><br/><br/><br/>";
                            ?>
                            <div style="page-break-after: always;"></div>
                            <?php
                            echo "<table class='borde'>";
                            echo "<tr><td>Total Consumos</td><td class='borde'>$contadorBNTotal</td><td class='borde'>$contadorColorTotal</td></tr>";
                            echo "<table>";
                        }
                        $prefijo++;
                        $no_equipo = 0;
                        $nueva_hoja = true;
                        if($prefijo >= count($prefijos)){
                            echo "fin";
                            break 2;
                        }
                    }
                    
                    if($prefijo <= 1){
                        $renta = "Renta por equipo";
                    }else{
                        $renta = "Renta";
                    }
                    
                    /*Datos del equipo*/
                    $reporte->getLecturaMesActual($rs['NoSerie']);
                    $reporte->getLecturasSinMarcaMesAnterior($rs['NoSerie']);
                    $result2 = $caracteristicas->getTiposDeServicios($rs['NoParteEquipo']);
                    $color = false;
                    while($rs_aux = mysql_fetch_array($result2)){
                        if($rs_aux['IdTipoServicio'] == "1"){
                            $color = true;
                            break;
                        }
                    }
                    
                    $result2 = $caracteristicas->getCaracteristicasByParte($rs['NoParteEquipo']);
                    $fa = false;
                    while($rs_aux = mysql_fetch_array($result2)){
                        if($rs_aux['IdCaracteristicaEquipo'] == "2"){
                            $fa = true;
                            break;
                        }
                    }
                    
                    if($nueva_hoja){ /*Iniciamos nueva hoja*/
                        $contadorBNTotal = 0;
                        $contadorColorTotal = 0;
                        echo "<table style='min-width: 95%;'><tr><td>";
                        echo "<table>";
                        echo "<tr><td>CLIENTE: </td><td>".$obj_cliente->getNombreRazonSocial()."</td></tr>";
                        echo "<tr><td colspan='2'>DIRECCION: ".$obj_direccion->getCalle()." No. Ext: ".$obj_direccion->getNoExterior()." 
                            No. Int: ".$obj_direccion->getNoInterior()."<br/>".$obj_direccion->getCodigoPostal()." ".
                                $obj_direccion->getCiudad()." Delegación ".$obj_direccion->getDelegacion()."</td></tr>";
                        echo "<tr><td style='vertical-align:top;'>CONTACTO: </td><td>".$obj_contacto->getNombre()."<br/>Tel. ".$obj_contacto->getTelefono()."<br/>".$obj_contacto->getCorreoElectronico()."</td></tr>";
                        echo "</table></td>";
                        echo "<td><table>";
                        echo "<tr><td>ZONA CLIENTE: ".$obj_zona->getNombre()."</td></tr>";
                        echo "<tr><td>Renta por XXX equipos</td></tr>";
                        echo "<tr><td>Incluye ".$rs[$prefijos[$prefijo].'incluidosBN']." ".$prefijo_pagml[$prefijo]." BN</td></tr>";                     
                        echo "<tr><td>Incluye ".$rs[$prefijos[$prefijo].'incluidosColor']." ".$prefijo_pagml[$prefijo]." de color</td></tr>";                        
                        echo "<tr><td>Costo por ".$prefijo_pagml[$prefijo]." BN excedente ".$rs[$prefijos[$prefijo].'ExcedentesBN']."</td></tr>";                        
                        echo "<tr><td>Costo por ".$prefijo_pagml[$prefijo]." Color excedente ".$rs[$prefijos[$prefijo].'ExcedentesColor']."</td></tr>";                                                
                        echo "</table></td>";                       
                        echo "</tr></table>";
                        echo "<br/><br/>";
                        echo "<table class='borde'>";
                        echo "<tr><td class='borde'>No.</td><td class='borde'>Concepto</td><td class='borde'>Modelo</td><td class='borde'>No. Serie</td>
                            <td class='borde'>B&N</td><td class='borde'>Color</td><td class='borde'></td><td class='borde'></td><td class='borde'></td></tr>";
                        
                    }                                                                                
                    
                    $contadorBN = 0;
                    $contadorColor = 0;
                    $contadorBNAnterior = 0;
                    $contadorColorAnterior = 0;
                    
                    if(!$color){/*Si el equipo es blanco y negro*/
                        if(!$fa){/*Si el equipo no es de formato amplio (es decir que es impresora)*/
                            $contadorBN = $reporte->getContadorBNPagina(); 
                            $contadorBNAnterior = $reporte->getContadorBNPaginaAnterior(); 
                        }else{
                            $contadorBN = $reporte->getContadorBNML();
                            $contadorBNAnterior = $reporte->getContadorBNMLAnterior();
                        }
                    }else{/*Si el equipo es color*/
                        if(!$fa){/*Si el equipo no es de formato amplio (es decir que es impresora)*/
                            $contadorBN = $reporte->getContadorBNPagina();
                            $contadorColor = $reporte->getContadorColorPagina();
                            $contadorBNAnterior = $reporte->getContadorBNPaginaAnterior();
                            $contadorColorAnterior = $reporte->getContadorColorPaginaAnterior();
                        }else{
                            $contadorBN = $reporte->getContadorBNML();
                            $contadorColor = $reporte->getContadorColorML();
                            $contadorBNAnterior = $reporte->getContadorBNMLAnterior();
                            $contadorColorAnterior = $reporte->getContadorColorMLAnterior();
                        }
                    }
                    
                    echo "<tr>";
                    echo "<td class='borde'>$no_equipo</td>";
                    echo "<td class='borde'>$renta</td>";
                    echo "<td class='borde'>".$rs['Modelo']."</td>";
                    echo "<td class='borde'>".$rs['NoSerie']."</td>";
                    echo "<td class='borde'>".$contadorBN."</td>";
                    echo "<td class='borde'>".$contadorColor."</td>";                    
                    echo "<td class='borde'>".$contadorBNAnterior." - $contadorColorAnterior</td>";
                    echo "<td class='borde'>".(intval($contadorBN) - intval($contadorBNAnterior))."</td>";
                    echo "<td class='borde'>".(intval($contadorColor) - intval($contadorColorAnterior))."</td>";
                    echo "</tr>";                    
                    $contadorBNTotal+=(intval($contadorBN) - intval($contadorBNAnterior));
                    $contadorColorTotal+=(intval($contadorColor) - intval($contadorColorAnterior));
                    $no_equipo++;
                    $nueva_hoja = false;
                }                
            }else{
                echo "No se pudieron encontrar anexos activos y/o servicios de esta búsqueda";
            }
        ?>
    </body>
</html>

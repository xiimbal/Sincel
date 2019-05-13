<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Catalogo.class.php");

if($_POST['tipo'] == 0){
    $catalogo = new Catalogo();
    
    $claveCliente = $_POST['cliente'];
    $NoContrato = $_POST['contrato0'];
    $NoParte = $_POST['modelo'];
    $idServicio = $_POST['servicio0'];
    
    $query = "SELECT 'TodoJunto' AS Junto, 
        (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN c.ClaveCliente ELSE (SELECT ClaveCliente FROM c_cliente WHERE c_cliente.ClaveCliente = cc2.ClaveCliente) END) AS ClaveCliente, (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.Nombre ELSE cc2.Nombre END) AS CentroCostoNombre, 
        (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.TipoDomicilioFiscal ELSE cc2.TipoDomicilioFiscal END) AS CentroCostoDomicilioFiscal, 
        (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.ClaveCentroCosto ELSE ks.ClaveCentroCosto END) AS ClaveCentroCosto, (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.ClaveZona ELSE cc2.ClaveZona END) AS ClaveZona, 
        (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN ccc1.nombre ELSE ccc2.nombre END) AS CentroCostoLocalidad, 
        (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN ccc1.id_cc ELSE ccc2.id_cc END) AS idCen_Costo, 
        (CASE WHEN !ISNULL(kecs.Id) THEN 1 ELSE 0 END) AS isColor,
        (CASE WHEN !ISNULL(cgim.IdServicioGIM) THEN cgim.IdServicioGIM 
        WHEN !ISNULL(cgfa.IdServicioGFA) THEN cgfa.IdServicioGFA 
        WHEN !ISNULL(cim.IdServicioIM) THEN cim.IdServicioIM
        WHEN !ISNULL(cfa.IdServicioFA) THEN cfa.IdServicioFA ELSE NULL END) AS IdServicio,
        (CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.IdKServicioGIM 
        WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.IdKServicioGFA
        WHEN !ISNULL(im.IdKServicioIM) THEN im.IdKServicioIM
        WHEN !ISNULL(fa.IdKServicioFA) THEN fa.IdKServicioFA ELSE NULL END) AS IdKServicio,
        (CASE WHEN !ISNULL(im.RentaMensual) THEN im.RentaMensual
        WHEN !ISNULL(fa.RentaMensual) THEN fa.RentaMensual
        WHEN !ISNULL(gim.RentaMensual) THEN gim.RentaMensual
        WHEN !ISNULL(gfa.RentaMensual) THEN gfa.RentaMensual ELSE NULL END) AS RentaMensual,
        (CASE WHEN !ISNULL(im.PaginasIncluidasBN) THEN im.PaginasIncluidasBN
        WHEN !ISNULL(fa.MLIncluidosBN) THEN fa.MLIncluidosBN
        WHEN !ISNULL(gfa.MLIncluidosBN) THEN gfa.MLIncluidosBN
        WHEN !ISNULL(gim.PaginasIncluidasBN) THEN gim.PaginasIncluidasBN ELSE NULL END) AS PaginasIncluidasBN,
        (CASE WHEN !ISNULL(im.PaginasIncluidasColor) THEN im.PaginasIncluidasColor 
        WHEN !ISNULL(fa.MLIncluidosColor) THEN fa.MLIncluidosColor
        WHEN !ISNULL(gfa.MLIncluidosColor) THEN gfa.MLIncluidosColor
        WHEN !ISNULL(gim.PaginasIncluidasColor) THEN gim.PaginasIncluidasColor ELSE NULL END) AS PaginasIncluidasColor,
        (CASE WHEN !ISNULL(im.CostoPaginasExcedentesBN) THEN im.CostoPaginasExcedentesBN
        WHEN !ISNULL(fa.CostoMLExcedentesBN) THEN fa.CostoMLExcedentesBN
        WHEN !ISNULL(gim.CostoPaginasExcedentesBN) THEN gim.CostoPaginasExcedentesBN
        WHEN !ISNULL(gfa.CostoMLExcedentesBN) THEN gfa.CostoMLExcedentesBN ELSE NULL END) AS costoExcedentesBN,
        (CASE WHEN !ISNULL(im.CostoPaginasExcedentesColor) THEN im.CostoPaginasExcedentesColor
        WHEN !ISNULL(fa.CostoMLExcedentesColor) THEN fa.CostoMLExcedentesColor
        WHEN !ISNULL(gim.CostoPaginasExcedentesColor) THEN gim.CostoPaginasExcedentesColor
        WHEN !ISNULL(gfa.CostoMLExcedentesColor) THEN gfa.CostoMLExcedentesColor ELSE NULL END) AS costoExcedentesColor,
        
        (CASE WHEN !ISNULL(im.CostoPaginaProcesadaBN) THEN im.CostoPaginaProcesadaBN
        WHEN !ISNULL(fa.CostoMLProcesadosBN) THEN fa.CostoMLProcesadosBN
        WHEN !ISNULL(gim.CostoPaginaProcesadaBN) THEN gim.CostoPaginaProcesadaBN
        WHEN !ISNULL(gfa.CostoMLProcesadosBN) THEN gfa.CostoMLProcesadosBN ELSE NULL END) AS procesadaBN,
        
        (CASE WHEN !ISNULL(im.CostoPaginaProcesadaColor) THEN im.CostoPaginaProcesadaColor
        WHEN !ISNULL(fa.CostoMLProcesadosColor) THEN fa.CostoMLProcesadosColor
        WHEN !ISNULL(gim.CostoPaginaProcesadaColor) THEN gim.CostoPaginaProcesadaColor
        WHEN !ISNULL(gfa.CostoMLProcesadosColor) THEN gfa.CostoMLProcesadosColor ELSE NULL END) AS procesadaColor,
        cinv.NoSerie AS NoSerie, 
        cinv.NoParteEquipo, 
        cinv.Ubicacion, 
        b.VentaDirecta, 
        b.id_bitacora, 
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
        cim.Nombre AS Nombreim, 
        cfa.Nombre AS Nombrefa, 
        cgim.Nombre AS Nombregim, 
        cgfa.Nombre AS Nombregfa  
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
        LEFT JOIN c_serviciofa AS cfa ON cfa.IdServicioFA = fa.IdServicioFA 
        WHERE c.ClaveCliente = '$claveCliente' AND ctt.NoContrato = '$NoContrato' AND cinv.Demo = 0 
        AND b.NoParte = '$NoParte'
        AND !ISNULL(cinv.NoSerie) AND (ISNULL(VentaDirecta) OR VentaDirecta = 0);";
    $result = $catalogo->obtenerLista($query);
    $auxS = "1";
    $auxK = "1";
    $mismoServicio = true;
    $servicio = "No";
    while($rs = mysql_fetch_array($result)){
        if(strcmp($auxS, "1") == 0){
            $auxS = $rs['IdServicio'];
            $auxK = $rs['IdKServicio'];
            $servicio = "\n [".$auxK."]";
            if(isset($rs['RentaMensual']) && $rs['RentaMensual'] != 0){
                setlocale(LC_MONETARY, 'en_US.UTF-8');
                $servicio .= "Renta base con paginas incluidas BN o Color - RM: ".money_format('%.2n', $rs['RentaMensual']);
            }
            if(isset($rs['PaginasIncluidasBN']) && $rs['PaginasIncluidasBN'] != 0){
                $servicio .= " - I B/N: ".$rs['PaginasIncluidasBN'];
            }
            if(isset($rs['PaginasIncluidasColor']) && $rs['PaginasIncluidasColor'] != 0){
                $servicio .= " - I Color: ".$rs['PaginasIncluidasColor'];
            }
            if(isset($rs['costoExcedentesBN']) && $rs['costoExcedentesBN'] != 0){
                $servicio .= " - E B/N: ".money_format('%.2n', $rs['costoExcedentesBN']);
            }
            if(isset($rs['costoExcedentesColor']) && $rs['costoExcedentesColor'] != 0){
                $servicio .= " - E Color: ".money_format('%.2n', $rs['costoExcedentesColor']);
            }
            if(isset($rs['procesadaBN']) && $rs['procesadaBN'] != 0){
                $servicio .= " Costo por pagina procesada BN o Color: - P B/N: ".money_format('%.2n', $rs['procesadaBN']);
            }
            if(isset($rs['procesadaColor']) && $rs['procesadaColor'] != 0){
                $servicio .= " - P Color: ".money_format('%.2n', $rs['procesadaColor']);
            }
        }
        if(strcmp($auxS, $rs['IdServicio']) != 0 || strcmp($auxK, $rs['IdKServicio']) != 0){
            $servicio = "No";
            $mismoServicio = false;
            break;
        }
    }
    if($mismoServicio && strcmp($auxS."-".$auxK,$idServicio) == 0){
        $servicio = "No";
    }
    echo $servicio;
}else{
    echo "No";
}


<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Equipo.class.php");
include_once("../WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "ReporteBaseInstalada.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$writer = new XLSXWriter();
$writer->setAuthor('Techra');

$cabeceras = array('Serie' => 'string', 'Modelo' => "string", 'Cliente' => 'string', 'Grupo' => 'string', 'Localidad' => 'string', 
    'Calle' => 'string', 'No Exterior' => 'string','No Interior' => 'string','Colonia' => 'string','Delegacion' => 'string','Estado' => 'string','Zona'=>'string',
    'ContadorBnIni' => 'number', 'ContadorBnFin' => 'number', 'ContadorCLIni' => 'number', 'ContadorCLFin' => 'number', 'ImpresasBN' => 'number', 'ImpresasCL' => 'number');

$hoja = "Reporte";

$catalogo = new Catalogo();

$having = "";
$where = "";
$fechaInicio = "";
$fechaFinal = "";
$titulo = "";

if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_fin'])) {
    if (isset($_GET['cliente']) && $_GET['cliente'] != "") {
        $titulo .= " Cliente(s): ";
        $cliente_obj = new Cliente();
        $idC = "";        
        $array_clientes = split(",", $_GET['cliente']);
        foreach ($array_clientes as $value) {
            if($cliente_obj->getRegistroById($value)){
                $titulo .= ("{".$cliente_obj->getNombreRazonSocial()."}");
            }
            $idC.= "$value,";
            if ($having == "") {
                $having = "HAVING (ClaveCliente = '" . $value . "'";
            } else {
                $having.= " OR ClaveCliente = '" . $value . "'";
            }
        }
        
        if ($idC != "") {
            $idC = substr($idC, 0, strlen($idC) - 1);
        }
        $having .= ")";
    }

    if (isset($_GET['modelo']) && $_GET['modelo'] != "") {
        $idT = "";
        $titulo .= " Modelo(s): ";
        $modelo_obj = new Equipo();        
        $array_modelos = split(",",$_GET['modelo']);
        foreach ($array_modelos as $value) {
            if($modelo_obj->getRegistroById($value)){
                $titulo .= ("{".$modelo_obj->getModelo()."}");
            }
            
            $idT .= "$value,";
            if ($where == "") {
                $where = "WHERE (e.NoParte = '" . $value . "'";
            } else {
                $where.= " OR e.NoParte = '" . $value . "'";
            }
        }
        if ($idT != "") {
            $idT = substr($idT, 0, strlen($idT) - 1);
        }


        $where .= ")";
    }

    if (isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] != "") {
        $fechaInicio = $_GET['fecha_inicio'];
        $titulo .= " de ".$catalogo->formatoFechaReportes($fechaInicio);
    }

    if (isset($_GET['fecha_fin']) && $_GET['fecha_fin'] != "") {
        $fechaFinal = $_GET['fecha_fin'];
        $titulo .= " al ".$catalogo->formatoFechaReportes($fechaFinal);
    }
} else {
    echo "Accede al reporte desde el sistema";
    return;
}

$query = "SELECT id_bitacora, id_solicitud,
    (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.NombreRazonSocial ELSE c.NombreRazonSocial END) AS NombreRazonSocial,
    (CASE WHEN !ISNULL(c2.ClaveCliente) THEN cg2.Nombre ELSE cg.Nombre END) AS GrupoCliente,
    (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.ClaveCliente ELSE c.ClaveCliente END) AS ClaveCliente,
    (CASE WHEN !ISNULL(cc2.ClaveCentroCosto) THEN cc2.Nombre ELSE cc.Nombre END) AS localidad,    
    (CASE WHEN !ISNULL(cc2.ClaveCentroCosto) THEN 
        (SELECT z.NombreZona FROM c_zona AS z LEFT JOIN c_domicilio AS d_aux ON d_aux.ClaveZona = z.ClaveZona WHERE d_aux.IdDomicilio = d2.IdDomicilio)
    ELSE 
        (SELECT z.NombreZona FROM c_zona AS z LEFT JOIN c_domicilio AS d_aux ON d_aux.ClaveZona = z.ClaveZona WHERE d_aux.IdDomicilio = d.IdDomicilio)
    END) AS zona,    
    b.NoSerie, CONCAT(e.Modelo,' / ',b.NoParte) AS NoParteCompuesta,
    (CASE WHEN !ISNULL(kecs2.Id) THEN l1.ContadorBNML ELSE l1.ContadorBNPaginas END) AS ContadorBNIni,
    (CASE WHEN ISNULL(kecs.Id) THEN NULL WHEN !ISNULL(kecs2.Id) THEN l1.ContadorColorML ELSE l1.ContadorColorPaginas END) AS ContadorColorIni,
    (CASE WHEN !ISNULL(kecs2.Id) THEN l2.ContadorBNML ELSE l2.ContadorBNPaginas END) AS ContadorBNFin,
    (CASE WHEN ISNULL(kecs.Id) THEN NULL WHEN !ISNULL(kecs2.Id) THEN l2.ContadorColorML ELSE l2.ContadorColorPaginas END) AS ContadorColorFin,
    (
      CASE
      WHEN (`d2`.`IdDomicilio` IS NOT NULL) THEN
       `d2`.`Calle`
      WHEN (
       `d`.`IdDomicilio` IS NOT NULL
      ) THEN
       `d`.`Calle`
      ELSE
       ''
      END
     ) AS `Calle`,
     (
      CASE
      WHEN (`d2`.`IdDomicilio` IS NOT NULL) THEN
       `d2`.`NoInterior`
      WHEN (
       `d`.`IdDomicilio` IS NOT NULL
      ) THEN
       `d`.`NoInterior`
      ELSE
       ''
      END
     ) AS `NoInterior`,
     (
      CASE
      WHEN (`d2`.`IdDomicilio` IS NOT NULL) THEN
       `d2`.`NoExterior`
      WHEN (
       `d`.`IdDomicilio` IS NOT NULL
      ) THEN
       `d`.`NoExterior`
      ELSE
       ''
      END
     ) AS `NoExterior`,
     (
      CASE
      WHEN (`d2`.`IdDomicilio` IS NOT NULL) THEN
       `d2`.`Colonia`
      WHEN (
       `d`.`IdDomicilio` IS NOT NULL
      ) THEN
       `d`.`Colonia`
      ELSE
       ''
      END
     ) AS `Colonia`,
     (
      CASE
      WHEN (`d2`.`IdDomicilio` IS NOT NULL) THEN
       `d2`.`Delegacion`
      WHEN (
       `d`.`IdDomicilio` IS NOT NULL
      ) THEN
       `d`.`Delegacion`
      ELSE
       ''
      END
     ) AS `Delegacion`,
     (
      CASE
      WHEN (`d2`.`IdDomicilio` IS NOT NULL) THEN
       `d2`.`Ciudad`
      WHEN (
       `d`.`IdDomicilio` IS NOT NULL
      ) THEN
       `d`.`Ciudad`
      ELSE
       ''
      END
     ) AS `Ciudad`,
     (
      CASE
      WHEN (`d2`.`IdDomicilio` IS NOT NULL) THEN
       `d2`.`Estado`
      WHEN (
       `d`.`IdDomicilio` IS NOT NULL
      ) THEN
       `d`.`Estado`
      ELSE
       ''
      END
     ) AS `Estado`,
     (
      CASE
      WHEN (`d2`.`IdDomicilio` IS NOT NULL) THEN
       `d2`.`Pais`
      WHEN (
       `d`.`IdDomicilio` IS NOT NULL
      ) THEN
       `d`.`Pais`
      ELSE
       ''
      END
     ) AS `Pais`
    FROM `c_inventarioequipo` AS cinv
    LEFT JOIN c_bitacora AS b ON cinv.NoSerie = b.NoSerie
    LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte    
    LEFT JOIN k_equipocaracteristicaformatoservicio AS kecs ON kecs.Id = (SELECT MAX(ID) FROM k_equipocaracteristicaformatoservicio WHERE NoParte = e.NoParte AND IdTipoServicio = 1)
    LEFT JOIN k_equipocaracteristicaformatoservicio AS kecs2 ON kecs2.Id = (SELECT MAX(ID) FROM k_equipocaracteristicaformatoservicio WHERE NoParte = e.NoParte AND IdCaracteristicaEquipo = 2)  
    LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
    LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
    LEFT JOIN c_clientegrupo AS cg ON cg.ClaveGrupo = c.ClaveGrupo
    LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
    LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto       
    LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente
    LEFT JOIN c_clientegrupo AS cg2 ON cg2.ClaveGrupo = c2.ClaveGrupo    
    LEFT JOIN c_lectura AS l1 ON l1.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE NoSerie = b.NoSerie AND LecturaCorte = 1 AND MONTH('$fechaInicio') = MONTH(Fecha) AND YEAR('$fechaInicio') = YEAR(Fecha))
    LEFT JOIN c_lectura AS l2 ON l2.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE NoSerie = b.NoSerie AND LecturaCorte = 1 AND MONTH('$fechaFinal') = MONTH(Fecha) AND YEAR('$fechaFinal') = YEAR(Fecha))
    LEFT JOIN c_domicilio AS d ON d.IdDomicilio = (SELECT MIN(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = cc.ClaveCentroCosto)
    LEFT JOIN c_domicilio AS d2 ON d2.IdDomicilio = (SELECT MIN(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = ks.ClaveCentroCosto)
    $where GROUP BY id_bitacora $having ORDER BY NombreRazonSocial;";
$result = $catalogo->obtenerLista($query);
$writer->writeSheetHeader($hoja, $cabeceras);
while ($rs = mysql_fetch_array($result)) {
    $array_valores = array();
    array_push($array_valores, $rs['NoSerie']);
    array_push($array_valores, $rs['NoParteCompuesta']);
    array_push($array_valores, $rs['NombreRazonSocial']);
    array_push($array_valores, $rs['GrupoCliente']);
    array_push($array_valores, $rs['localidad']);
    //array_push($array_valores, "C." . $rs['Calle'] . " No. I " . $rs['NoInterior'] . " No. E " . $rs['NoExterior'] . " Col." . $rs['Colonia'] . "," . $rs['Estado'] . " " . $rs['Pais']);
    array_push($array_valores, $rs['Calle']);
    array_push($array_valores, $rs['NoExterior']);
    array_push($array_valores, $rs['NoInterior']);
    array_push($array_valores, $rs['Colonia']);
    array_push($array_valores, $rs['Delegacion']);    
    array_push($array_valores, $rs['Estado']);    
    array_push($array_valores, $rs['zona']);
    array_push($array_valores, (int)$rs['ContadorBNIni']);
    array_push($array_valores, (int)$rs['ContadorBNFin']);
    array_push($array_valores, (int)$rs['ContadorColorIni']);
    array_push($array_valores, (int)$rs['ContadorColorFin']);
    if (isset($rs['ContadorBNFin']) && !empty($rs['ContadorBNFin'])) {
        $bnF = $rs['ContadorBNFin'];
    } else {
        $bnF = "";
    }
    if (isset($rs['ContadorBNIni']) && !empty($rs['ContadorBNIni'])) {
        $bnI = $rs['ContadorBNIni'];
    } else {
        $bnI = "";
    }
    if (isset($rs['ContadorColorFin']) && !empty($rs['ContadorColorFin'])) {
        $colorF = $rs['ContadorColorFin'];
    } else {
        $colorF = 0;
    }
    if (isset($rs['ContadorColorIni']) && !empty($rs['ContadorColorIni'])) {
        $colorI = $rs['ContadorColorIni'];
    } else {
        $colorI = "";
    }

    $diferencia_bn = $bnF - $bnI;
    $diferencia_color = $colorF - $colorI;

    if ($diferencia_bn < 0) {
        $diferencia_bn = 0;
    }
    if ($diferencia_color < 0) {
        $diferencia_color = 0;
    }
    array_push($array_valores, (int)$diferencia_bn);
    array_push($array_valores, (int)$diferencia_color);
    $writer->writeSheetRow($hoja, $array_valores);
}
$writer->writeSheetRow($hoja, array("Reporte de impresiones por fecha $titulo"));
$writer->writeToStdOut();
/*$writer->writeToFile('example.xlsx');
echo $writer->writeToString();*/
exit(0);

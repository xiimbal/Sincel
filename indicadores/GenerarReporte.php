<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Equipo.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");

$urlReporteExcel = "";

$catalogo = new Catalogo();

$having = "";
$where = "";
$fechaInicio = "";
$fechaFinal = "";
$nombreLogo = "";
$titulo = "";

if (isset($_POST['fecha_inicio']) && isset($_POST['fecha_fin'])) {
    if (isset($_POST['cliente']) && $_POST['cliente'] != "" && $_POST['cliente'] != 0) {
        $cliente_obj = new Cliente();
        $idC = "";
        $titulo .= " Cliente(s): ";
        
        foreach ($_POST['cliente'] as $value) {
            if($cliente_obj->getRegistroById($value)){
                $titulo .= ("{".$cliente_obj->getNombreRazonSocial()."}");
            }
            $idC.= "$value,";
            if ($having == "") {
                $having = "HAVING (ClaveCliente = '" . $value . "'";
                $queryFacturacion = $catalogo->obtenerLista("SELECT fe.ImagenPHP FROM `c_cliente` AS c LEFT JOIN c_datosfacturacionempresa AS fe ON fe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa WHERE c.ClaveCliente = '$value';");
                if ($rs = mysql_fetch_array($queryFacturacion)) {
                    $nombreLogo = "../" . $rs['ImagenPHP'];
                }
            } else {
                $having.= " OR ClaveCliente = '" . $value . "'";
            }
        }
        if ($idC != "") {
            $idC = substr($idC, 0, strlen($idC) - 1);
        }
        $urlReporteExcel.="?cliente=$idC";
        $having .= ")";
    } else {
        $queryFacturacion = $catalogo->obtenerLista("SELECT df.ImagenPHP FROM c_datosfacturacionempresa df WHERE df.IdDatosFacturacionEmpresa = 3;");
        if (mysql_num_rows($queryFacturacion) > 0) {
            while ($rs = mysql_fetch_array($queryFacturacion)) {
                $nombreLogo = "../" . $rs['ImagenPHP'];
            }
        } else {
            $queryFacturacion = $catalogo->obtenerLista("SELECT df.Telefono,df.ImagenPHP,df.IdDatosFacturacionEmpresa,df.Calle,df.NoExterior,df.Colonia,df.Delegacion,df.Estado,df.CP FROM c_datosfacturacionempresa df");
            if ($rs = mysql_fetch_array($queryFacturacion)) {
                $nombreLogo = "../" . $rs['ImagenPHP'];
            }
        }
    }

    if (isset($_POST['modelo']) && $_POST['modelo'] != "" && $_POST['modelo'] != 0) {
        $idT = "";
        $modelo_obj = new Equipo();
        $titulo .= "<br/>Modelo(s): ";
        
        foreach ($_POST['modelo'] as $value) {
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
        if ($urlReporteExcel == "") {
            $urlReporteExcel.="?modelo=$idT";
        } else {
            $urlReporteExcel.="&modelo=$idT";
        }

        $where .= ")";
    }

    if (isset($_POST['fecha_inicio']) && $_POST['fecha_inicio'] != "") {
        if ($urlReporteExcel == "") {
            $urlReporteExcel.="?fecha_inicio=" . $_POST['fecha_inicio'] . "";
        } else {
            $urlReporteExcel.="&fecha_inicio=" . $_POST['fecha_inicio'] . "";
        }
        $fechaInicio = $_POST['fecha_inicio'];
        $titulo .= "<br/>de ".$catalogo->formatoFechaReportes($fechaInicio);
    }

    if (isset($_POST['fecha_fin']) && $_POST['fecha_fin'] != "") {
        if ($urlReporteExcel == "") {
            $urlReporteExcel.="?fecha_fin=" . $_POST['fecha_fin'] . "";
        } else {
            $urlReporteExcel.="&fecha_fin=" . $_POST['fecha_fin'] . "";
        }
        $fechaFinal = $_POST['fecha_fin'];
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
?>

<!DOCTYPE html>
<html lang="es">
    <head>     
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Reporte Administracion</title>
        <link rel="icon" href="../../resources/images/logos/ra4.png" type="image/x-icon"/>
        <style>
            .BorderTabla td,th {
                border: solid black;
                border-width:1px;
                border-spacing: 0px;
                border-collapse: collapse
            }
            img.imagen{width:150px; height:70px;}
            img.imagens{width:150px; height:70px;}
            @media print {
                * { margin: 0 !important; padding: 0 !important; }
                #controls, .footer, .footerarea{ display: none; }
                html, body {
                    /*changing width to 100% causes huge overflow and wrap*/
                    height:80%; 
                    background: #FFF; 
                    font-size: 9.5pt;
                }
                img.imagen{width:75px; height:30px;}
                img.imagens{width:75px; height:30px;}
                template { width: auto; left:0; top:0; }
                li { margin: 0 0 10px 20px !important;}
            }
        </style>
        <style type="text/css">
            .fieldset-auto-width {
                display: inline-block;
            }
        </style>
    </head>
    <body style="font-size:12px;font-family:Arial;height:50%;">
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
        <script src="../resources/js/jquery/jquery-ui.min.js"></script>
        <a href=javascript:window.print(); style="margin-left: 85%;"><img src="../resources/images/icono_impresora.png" height="30" width="30"></a>
        <a href="GenerarReporteExcel.php<?php echo $urlReporteExcel ?>" style="margin-left: 5%;" target="_blank"><img height="30" width="30" src="../resources/images/excel.png"></a>
        <br/><br/>
        <table style="width: 100%">
            <tr>                
                <td style="width: 50%"><img src="<?php echo $nombreLogo; ?>"/></td>
                <td>
                    <fieldset class="fieldset-auto-width">
                        <legend>Par&aacute;metros del reporte</legend>                        
                        <?php echo "<h2>$titulo</h2>"; ?>                                                        
                    </fieldset>
                </td>
            </tr>
        </table>

    <center><h2>Reporte de impresiones por fecha </h2></center>
    <br/><br/>
    <fieldset>
        <legend><b>Datos del reporte</b></legend>
        <table style="width: 100%;height: 40px;" class="BorderTabla">
            <thead style="background-color: grey;">
                <tr></tr>
                <tr>
                    <th align="center" style="width: 7%;">Serie</th>
                    <th align="center" style="width: 7%;">Modelo</th>
                    <th align="center" style="width: 15%;">Cliente</th>
                    <th align="center" style="width: 15%;">Grupo</th>
                    <th align="center" style="width: 18%;">Localidad</th>
                    <th align="center" style="width: 18%;">Direcci&oacute;n</th>
                    <th align="center" style="width: 10%;">Zona</th>
                    <th align="center" style="width: 5%;">Contador Inicial B/N</th>
                    <th align="center" style="width: 5%;">Contador Final B/N</th>
                    <th align="center" style="width: 5%;">Contador Inicial Color</th>
                    <th align="center" style="width: 5%;">Contador Final Color</th>
                    <th align="center" style="width: 5%;">Paginas Impresas B/N</th>
                    <th align="center" style="width: 5%;">Paginas Impresas Color</th>
                </tr>
            </thead>
            <tbody style="background-color: #D3D6FF;">
                <?php
                while ($rs = mysql_fetch_array($result)) {
                    ?>
                    <tr>
                        <td align="center"><?php echo $rs['NoSerie']; ?></td>
                        <td align="center"><?php echo $rs['NoParteCompuesta']; ?></td>
                        <td align="center"><?php echo $rs['NombreRazonSocial']; ?></td>
                        <td align="center"><?php echo $rs['GrupoCliente']; ?></td>                        
                        <td align="center"><?php echo $rs['localidad']; ?></td>
                        <td align="center"><?php echo "C." . $rs['Calle'] . " No. I " . $rs['NoInterior'] . " No. E " . $rs['NoExterior'] . " Col." . $rs['Colonia'] . ",". $rs['Delegacion'] . "," . $rs['Estado'] . " ".$rs['Pais']; ?></td>
                        <td align="center"><?php echo $rs['zona'] ?></td>
                        <td align="center"><?php echo number_format($rs['ContadorBNIni']);  ?></td>
                        <td align="center"><?php echo number_format($rs['ContadorBNFin']);  ?></td>
                        <td align="center"><?php echo number_format($rs['ContadorColorIni']); ?></td>
                        <td align="center"><?php echo number_format($rs['ContadorColorFin']); ?></td>
                        <?php
                        if (isset($rs['ContadorBNFin']) && !empty($rs['ContadorBNFin'])) {
                            $bnF = $rs['ContadorBNFin'];
                        } else {
                            $bnF = "";
                        }
                        if (isset($rs['ContadorBNIni']) && !empty($rs['ContadorBNIni']) ) {
                            $bnI = $rs['ContadorBNIni'];
                        } else {
                            $bnI = "";
                        }
                        if (isset($rs['ContadorColorFin']) && !empty($rs['ContadorColorFin']) ) {
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
                        
                        if($diferencia_bn < 0){ $diferencia_bn = 0;}
                        if($diferencia_color < 0){ $diferencia_color = 0;}
                        ?>
                        <td align="center"><?php echo number_format($diferencia_bn); ?></td>
                        <td align="center"><?php echo number_format($diferencia_color); ?></td>
                    </tr>
                    <?php }
                ?>
            </tbody>
        </table>
    </fieldset>
</body>
</html>

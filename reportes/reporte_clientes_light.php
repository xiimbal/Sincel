<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

ini_set("memory_limit","512M");
set_time_limit (0);

include_once("../WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "Cliente.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$writer = new XLSXWriter();
$writer->setAuthor('Techra');
$cabeceras = array('ClaveCliente' => 'clave', 'FechaCreacion' => "FechaCreacion", 'NombreRazonSocial' => "NombreRazonSocial", 'RFC' => "RFC",'Vendedor' => "vendedor",'Ejecutivo Atencion' => "ejecutivo_atencion" ,'Tipo' => "tipoCliente",
    'Estatus' => "estatus", 'RFCEmisor' => "RFCEmisor", 'ClaveCentroCosto' => "ClaveCentroCosto", 'NombreCentroCosto' => "Nombre",
    'Calle' => "Calle", 'NoExterior' => "NoExterior", 'NoInterior' => "NoInterior", 
    'Colonia' => "Colonia", 'Ciudad' => "Ciudad", 'Estado' => "Estado",
    'Delegación' => "Delegacion", 'País' => "Pais", 'CodigoPostal' => "CodigoPostal", 'Contacto' => "contacto", 'Telefono' => "Telefono",
    'Celular' => "Celular", 'CorreoElectronico' => "CorreoElectronico");

$hoja = "Reporte";
$writer->writeSheetHeader($hoja, $cabeceras);

$catalogo = new Catalogo();
$permiso_facturar = new PermisosSubMenu();
$usuario = new Usuario();
$where = "";
$permiso_inctivos = $permiso_facturar->tienePermisoEspecial($_SESSION['idUsuario'], 23);
$id_cliente = "";
$id_vendedor = "";
$rfc = "";
$estatus = "";
$tipo = "";

if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)) {
    $where = " AND c.EjecutivoCuenta = '" . $_SESSION['idUsuario'] . "'";
}
if (isset($_POST['sl_cliente']) && $_POST['sl_cliente'] != "0") {
    $id_cliente = $_POST['sl_cliente'];
    if ($where == "") {
        $where = " WHERE c.ClaveCliente = '$id_cliente'";
    } else {
        $where .= " AND c.ClaveCliente = '$id_cliente'";
    }
}
if (isset($_POST['txt_rfc']) && $_POST['txt_rfc'] != "") {
    $rfc = $_POST['txt_rfc'];
    if ($where == "") {
        $where = " WHERE c.RFC = '$rfc'";
    } else {
        $where .= " AND c.RFC = '$rfc'";
    }
}
if (isset($_POST['sl_vendedor']) && $_POST['sl_vendedor'] != "0") {
    $id_vendedor = $_POST['sl_vendedor'];
    if ($where == "") {
        $where = " WHERE u.IdUsuario = '$id_vendedor'";
    } else {
        $where .= " AND u.IdUsuario = '$id_vendedor'";
    }
}
if ($permiso_inctivos) {
    if (isset($_POST['sl_estatus']) && $_POST['sl_estatus'] != "") {
        $estatus = $_POST['sl_estatus'];
        if ($where == "") {
            $where = " WHERE c.Activo = '$estatus'";
        } else {
            $where .= " AND c.Activo = '$estatus'";
        }
    }
} else {
    if ($where == "") {
        $where = " WHERE c.Activo = '1'";
    } else {
        $where .= " AND c.Activo = '1'";
    }
}
if (isset($_POST['sl_tipo']) && $_POST['sl_tipo'] != "0") {
    $tipo = $_POST['sl_tipo'];
    if ($where == "") {
        $where = " WHERE c.Modalidad = '$tipo'";
    } else {
        $where .= "  AND c.Modalidad= '$tipo'";
    }
}

$consulta = "SELECT c.ClaveCliente AS clave,DATE(c.FechaCreacion) AS FechaCreacion,NombreRazonSocial,c.RFC,tc.Nombre AS tipoCliente,cg.Nombre AS NombreGrupo,
    CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS vendedor,
    CONCAT(u2.Nombre,' ',u2.ApellidoPaterno,' ',u2.ApellidoMaterno) AS ejecutivo_atencion,
    IF(c.Activo=1,'Activo','Inactivo') AS estatus,
    cc.ClaveCentroCosto,cc.Nombre AS Nombre,fe.RFC AS RFCEmisor,ctt.Nombre AS contacto,ctt.Telefono AS Telefono,ctt.Celular AS Celular,ctt.CorreoElectronico AS CorreoElectronico,
    d.Calle AS Calle,d.NoExterior AS NoExterior,d.NoInterior AS NoInterior,d.Colonia AS Colonia,d.Ciudad AS Ciudad,d.Estado AS Estado,d.Delegacion AS Delegacion,d.Pais AS Pais,d.CodigoPostal AS CodigoPostal
    FROM c_cliente c 
    LEFT JOIN c_usuario u ON c.EjecutivoCuenta = u.IdUsuario 
    LEFT JOIN c_usuario u2 ON c.EjecutivoAtencionCliente = u2.IdUsuario 
    LEFT JOIN c_clientegrupo  cg ON c.ClaveGrupo = cg.ClaveGrupo 
    LEFT JOIN c_clientemodalidad tc ON tc.IdTipoCliente = c.Modalidad 
    LEFT JOIN c_centrocosto cc ON c.ClaveCliente=cc.ClaveCliente 
    LEFT JOIN c_datosfacturacionempresa AS fe ON fe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa 
    LEFT JOIN c_contacto ctt ON ctt.ClaveEspecialContacto = cc.ClaveCentroCosto 
    LEFT JOIN c_domicilio d ON d.ClaveEspecialDomicilio = cc.ClaveCentroCosto $where
    GROUP BY clave";

$result = $catalogo->obtenerLista($consulta);
while ($rs = mysql_fetch_array($result)) {
    $array_valores = array();
    foreach ($cabeceras as $key => $value) {        
        
        array_push($array_valores, $rs[$value]);
    }        
    $writer->writeSheetRow($hoja, $array_valores);
}

$writer->writeToStdOut();
/*$writer->writeToFile('example.xlsx');
echo $writer->writeToString();*/
exit(0);



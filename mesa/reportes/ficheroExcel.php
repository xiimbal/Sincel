<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
date_default_timezone_set('America/Mexico_City');

$str = $_POST['datos_a_enviar'];
if (mb_detect_encoding($str ) == 'UTF-8') {
   $str = mb_convert_encoding($str , "HTML-ENTITIES", "UTF-8");
}

header('Content-type: application/x-msdownload; charset=utf-16');
header('Content-Disposition: attachment; filename=Genesis.xls');
header('Pragma: no-cache');
header('Expires: 0');

echo $str ;
?>
<table aling="center" width="90%" >
    <tr><td>Reporte: Genesis <?php echo date("Y")."-".date("m")."-".date("d") ?></td></tr>
</table> 
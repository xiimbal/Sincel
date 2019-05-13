<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/MovimientoBancario.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/CatalogoFacturacion.class.php");
//Con esto se mueve el archivo temporal a la carpeta especificada, con el nombre dado por el usuario a través
//de $_FILES['file']['name']
$rutaArchivo = "../../../CSV/".$_POST['id_periodo'].$_POST['mes']."_".$_POST['id_cuenta']."EstadoCuenta.csv"; 
move_uploaded_file($_FILES['file']['tmp_name'],$rutaArchivo);

$iniciarCeldas = false;
$impar = false;
$handle = fopen ($rutaArchivo,"r");
$cont = 0;
$celdas = 0;

while(!feof($handle))
{
    $celdas++;
    $data = fgetcsv($handle);
    if($data[0] == "Fecha" || $data[4] == "Retiros"){
        //Buscaremos en el excel las celdas de inicio de los movimientos ya que las primeras celdas
       //contienen solo información de la cuenta y no de los movimientos 
        $iniciarCeldas = true;
    continue;
    }
    else{
        if($iniciarCeldas){
            $cont++;
            //Comenzaremos a obtener los datos para ingresarlos a la BD
            $movimiento = new MovimientoBancario();
            $fecha = $data[0];
            $fecha = substr($fecha, 6)."-".substr($fecha, 3, 5)."-".  substr($fecha, 0, 2);
            $descripcion = $data[1];
            $depositos = $data[2];
            $retiros = $data[3];
            $saldo = $data[4];
            $referencia = explode("merica:       ",$descripcion);
            $referencia = substr($referencia[1], 0 , 10);
            if(!isset($referencia[1]) || $referencia[1] == ""){
                $referencia = explode("merica: ",$descripcion);
                $referencia = substr($referencia[1], 0 , 13);
            }
            
            $movimiento->setId_cuentaBancaria($_POST['id_cuenta']);
            $movimiento->setFecha($fecha);
            $movimiento->setDescripcion(utf8_encode($descripcion));
            if(!isset($retiros) || $retiros == "")
            {
                $movimiento->setTipo("Deposito");
                $depositos = str_replace(",", "", $depositos);
                $movimiento->setMonto($depositos);
                $queryPago = "SELECT IdFacturaProveedor FROM c_factura_proveedor fp
                        LEFT JOIN c_proveedor as p ON fp.IdEmisor = p.ClaveProveedor
                        WHERE p.referencia LIKE '%".$referencia."%'and fp.Total = ".$depositos." AND fp.Fecha >= '".$_POST['id_periodo']."-".$_POST['mes']."-01' "
                        . "AND fp.Fecha <= '".$_POST['id_periodo']."-".$_POST['mes']."-31'";
                $catalogo = new Catalogo();
                $result = $catalogo->obtenerLista($queryPago);
                if(mysql_num_rows($result) == 1){
                    if($rs = mysql_fetch_array($result)){
                        $verificar = "SELECT * from c_movimientoBancario WHERE id_pago = " .$rs['IdFacturaProveedor'];
                        $result2 = $catalogo->obtenerLista($verificar);
                        if(mysql_num_rows($result) == 0){
                            $movimiento->setPago($rs['IdFacturaProveedor']);
                        }
                    }
                }
            }else{
                $movimiento->setTipo("Retiro");
                $retiros = str_replace(",", "", $retiros);
                $movimiento->setMonto($retiros);
                $queryPago = "SELECT IdFactura FROM c_factura f
                        LEFT JOIN c_cliente as c ON f.RFCReceptor = c.RFC
                        WHERE f.FacturaPagada = 1 AND c.referencia LIKE '%".$referencia."%'and f.Total = ".$retiros." AND f.FechaFacturacion >= '".$_POST['id_periodo']."-".$_POST['mes']."-01' "
                        . "AND f.FechaFacturacion <= '".$_POST['id_periodo']."-".$_POST['mes']."-31'";
                $catalogo = new Catalogo();
                $result = $catalogo->obtenerLista($queryPago);
                if(mysql_num_rows($result) == 1){
                    if($rs = mysql_fetch_array($result)){
                        $verificar = "SELECT * from c_movimientoBancario WHERE id_pago = " .$rs['IdFactura'];
                        $result2 = $catalogo->obtenerLista($verificar);
                        if(mysql_num_rows($result) == 0){
                            $movimiento->setPago($rs['IdFactura']);
                        }
                    }
                }
            }
            $movimiento->setReferencia($referencia);
            $movimiento->setUsuarioCreacion($_SESSION['user']);
            $movimiento->setUsuarioUltimaModificacion($_SESSION['user']);
            $movimiento->setPantalla("Carga Estado de Cuenta");
            $movimiento->newRegistro();

        }
    }
}

fclose($handle);
echo "Se ha subido el archivo: ". $_POST['id_periodo'].$_POST['mes']."_".$_POST['id_cuenta']."EstadoCuenta.csv";
echo "Numero de registros insertados: ".$cont." Numero de celdas leidas: ".$celdas;
?>
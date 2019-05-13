<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../../Classes/LeerXML.class.php");
include_once("../../Classes/Factura_Proveedor.class.php");
include_once("../../Classes/Factura_Proveedor_Detalle.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Orden_Compra.class.php");
$xml = new LeerXML();
$facturaProv = new Factura_Proveedor();
$facturaDetalle = new Factura_Proveedor_Detalle();
$catalogo = new Catalogo();
$ordenCompra = new Orden_Compra();
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $idOC = $_GET['idOrden'];
    $ordenCompra->setIdOrdenCompra($idOC);
    $ordenCompra->setPathFactura("");
    $ordenCompra->setUsuarioModificacion($_SESSION['user']);
    $ordenCompra->setPantalla("Eliminar factura");
    $facturaProv->setIdFacturaProveedor($id);
    $facturaDetalle->setIdFacturaProveedor($id);
    if ($facturaDetalle->deleteRegistro()) {
        if ($facturaProv->deleteRegistro()) {
            if ($ordenCompra->editRegistroPathFactura()) {
                echo "La factura se eliminó correctamente.";
            } else {
                echo "Error: La factura no se eliminó correctamente.";
            }
        } else {
            echo "Error: La factura no se eliminó correctamente.";
        }
    } else {
        echo "Error: La factura no se eliminó correctamente.";
    }
} else {
    if (isset($_POST['tipo'])) {
        if ($_POST['tipo'] == "factura") {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                //obtenemos el archivo a subir
                $file = $_FILES['factura']['name'];
                //comprobamos si existe un directorio para subir el archivo
                //si no es así, lo creamos            
                if (!is_dir("../documentos/facturasProveedor/factura/"))
                    if(!mkdir("../documentos/facturasProveedor/factura/", 0755)){
                        echo "Error: no se pudo crear la carpeta";
                    }
                $existe = false;
                do {/* renombramos el archivo si ya existe ese nombre */
                    if (file_exists("../documentos/facturasProveedor/factura/" . $file)) {
                        $existe = true;
                        $file = "(1)" . $file;
                    } else {
                        $existe = false;
                    }
                } while ($existe);
                //comprobamos si el archivo ha subido
                if ($file && move_uploaded_file($_FILES['factura']['tmp_name'], "../documentos/facturasProveedor/factura/" . $file)) {
                    $ordenCompra->setIdOrdenCompra($_POST['idOC']);
                    $ordenCompra->setPathFactura($file);
                    $ordenCompra->setUsuarioModificacion($_SESSION['user']);
                    $ordenCompra->setPantalla("Alta factura");
                    $facturaProv->setIdFacturaProveedor($_POST['idFactura']);
                    $facturaProv->setPathFactura($file);
                    if ($ordenCompra->editRegistroPathFactura()) {
                        if ($facturaProv->editPathFactura()) {
                            
                        } else {
                            
                        }
                    } else {
                        
                    }
                    echo "La factura se subio correctamente";
                }
            } else {
                throw new Exception("Error Processing Request", 1);
            }
        } else if ($_POST['tipo'] == "xml") {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                //obtenemos el archivo a subir
                $file = $_FILES['xml']['name'];
                //comprobamos si existe un directorio para subir el archivo
                //si no es así, lo creamos          
                                
                if(!file_exists("../documentos/facturasProveedor/factura") && !mkdir("../documentos/facturasProveedor/factura/", 0755)){
                    echo "Error: no se pudo crear la carpeta";
                }
                
                $existe = false;
                do {/* renombramos el archivo si ya existe ese nombre */
                    if (file_exists("../documentos/facturasProveedor/xml/" . $file)) {
                        $existe = true;
                        $file = "(1)" . $file;
                    } else {
                        $existe = false;
                    }
                } while ($existe);
                //comprobamos si el archivo ha subido
                
                //echo "Error: ".move_uploaded_file($_FILES['xml']['tmp_name'], "../documentos/facturasProveedor/xml/" . $file);
                if ($file != "" && move_uploaded_file($_FILES['xml']['tmp_name'], "../documentos/facturasProveedor/xml/" . $file)) {
                    $xml->setXml("../documentos/facturasProveedor/xml/" . $file);
                    $xml->getDatosXML();
                    $facturaProv->setIdOrdenCompra($_POST['idOC']);
                    $facturaProv->setFolio($xml->getFolio());
                    $facturaProv->setPathFactura("");
                    $rfcEmisor = $xml->getRfcEmisor();
                    $rfcReceptor = $xml->getRfcReceptor();
                    $queryProv = $catalogo->obtenerLista("SELECT p.ClaveProveedor FROM c_proveedor p WHERE p.RFC='$rfcEmisor'");
                    $queryFac = $catalogo->obtenerLista("SELECT df.IdDatosFacturacionEmpresa FROM c_datosfacturacionempresa df WHERE df.RFC='$rfcReceptor'");
                    $idProveedor = "";
                    $idFacturacion = "";
                    if(mysql_num_rows($queryProv) == 0){
                        echo "Error: el proveedor con RFC $rfcEmisor no existe en el sistema";
                        return;
                    }
                    
                    if(mysql_num_rows($queryFac) == 0){
                        echo "Error: el receptor con RFC $rfcReceptor no existe en el sistema";
                        return;
                    }
                    
                    while ($rs = mysql_fetch_array($queryProv)) {
                        $idProveedor = $rs['ClaveProveedor'];
                    }
                    
                    while ($rs = mysql_fetch_array($queryFac)) {
                        $idFacturacion = $rs['IdDatosFacturacionEmpresa'];
                    }
                    $fechaFactura = str_replace("T", " ", $xml->getFecha());
                    $facturaProv->setFecha($fechaFactura);
                    $facturaProv->setIdEmisor($idProveedor);
                    $facturaProv->setIdReceptor($idFacturacion);
                    $facturaProv->setSubTotal($xml->getSubTotal());
                    $facturaProv->setIva($xml->getIva());
                    $facturaProv->setTotal($xml->getTotal());
                    $facturaProv->setUsuarioCreacion($_SESSION['user']);
                    $facturaProv->setUsuarioUltimaModificacion($_SESSION['user']);
                    $facturaProv->setPantalla("Alta de xml");
                    $arrayCantidad = $xml->getArrayCantidad();
                    $arrayUnidad = $xml->getArrayUnidad();
                    $arrayDescripcion = $xml->getArrayDescripcion();
                    $arrayValorUnitario = $xml->getArrayValorUnitario();
                    $arrayImporte = $xml->getArrayImporte();
                    $facturaDetalle->setUsuarioCreacion($_SESSION['user']);
                    $facturaDetalle->setUsuarioUltimaModificacion($_SESSION['user']);
                    $facturaDetalle->setPantalla("Alta de xml");
                    $facturaProv->setPathXml($file);
                    if ($facturaProv->newRegistro()) {//agregar datos de la fatura
                        $idFactura = $facturaProv->getIdFacturaProveedor();
                        $facturaDetalle->setIdFacturaProveedor($idFactura);
                        for ($x = 0; $x < count($xml->getArrayCantidad()); $x++) {//agregar los conceptos del xml
                            $facturaDetalle->setCantidad($arrayCantidad[$x]);
                            $facturaDetalle->setUnidad($arrayUnidad[$x]);
                            $facturaDetalle->setDescripcion($arrayDescripcion[$x]);
                            $facturaDetalle->setValorUnitario($arrayValorUnitario[$x]);
                            $facturaDetalle->setImporte($arrayImporte[$x]);
                            if ($facturaDetalle->newRegistro()) {
                                
                            } else {
                                
                            }
                        }
                        echo "El XML se regitro correctamente *--* $idFactura";
                    } else {
                        echo "El XML no se regitro correctamente";
                    }
                }else{
                    echo "Error: no se pudo copiar el archivo";
                }
            } else {
                throw new Exception("Error Processing Request", 1);
            }
        }
    }
}
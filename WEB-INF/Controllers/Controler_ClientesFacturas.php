<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/ClientesFacturas.class.php");
$cliente = new ClientesFacturas();
if (isset($_POST['CCliente']) && $_POST['CCliente'] == "CCliente") {
    $cliente->obtenerClientesFacturas();
    $cliente->ObtenerClienteEquipos();
    $cliente->ObtenerClienteFacturacionAdeudo();
//se obtinene los datos de las consultas
    $RFCCliente = $cliente->getClienteFactura();
    $RFCClienteAdeudo = $cliente->getClienteFacturaAdeudo();
    $RFC = $cliente->getClienteEquipo();
    $nombre = $cliente->getNombreCliente();
    $RFCEmisor = $cliente->getRFCEmisor();
    $fechaFacturacion = $cliente->getFechaFacturacion();
    $clientesAdeudo = array();
    $ClientesNormal = array();
    $clienteRFCEmisor = array();
    $clienteRFCEmisorAdeudo = array();
    $clienteNombre = array();
    $clienteNombreAdeudo = array();
    $fechaFacturacionAdeudo = array();
    $contador3 = 0;
    $adeudo = 0;
    $normal = 0;
    while ($contador3 < count($RFCCliente)) {
        if (in_array($RFCCliente[$contador3], $RFCClienteAdeudo)) {//verifica los morosos 
            $clientesAdeudo[$adeudo] = $RFCCliente[$contador3];
            $clienteNombreAdeudo[$adeudo] = $nombre[$contador3];
            $clienteRFCEmisorAdeudo[$adeudo] = $RFCEmisor[$contador3];
            $fechaFacturacionAdeudo[$adeudo] = $fechaFacturacion[$contador3];
            $adeudo++;
        } else {//si no son morosos no verificar nada
            $ClientesNormal[$normal] = $RFCCliente[$contador3];
            $clienteNombre[$normal] = $nombre[$contador3];
            $clienteRFCEmisor[$normal] = $RFCEmisor[$contador3];
            $normal++;
        }

        $contador3++;
    }
//    echo "TOTAL adeudo " . count($RFCClienteAdeudo) . "<br/>";
//    echo "TOTAL Normal " . count($RFCCliente) . "<br/>";
    $contador4 = 0;
    $contador4aux = 0;
    $insertarAdeudo = array();
    $insertarAdeudoNombre = array();
    $insertarAdeudoRfc = array();
    $insertarAdeudoFecha = array();
    $insertarAdeudoAUX = array();
    $contador5ExistenteAux = 0;
    while ($contador4 < count($clientesAdeudo)) {
        if (in_array($clientesAdeudo[$contador4], $RFC)) {
            $insertarAdeudoAUX[$contador5ExistenteAux] = $clientesAdeudo[$contador4];
            $contador5ExistenteAux++;
        } else {
            $insertarAdeudo[$contador4aux] = $clientesAdeudo[$contador4];
            $insertarAdeudoNombre[$contador4aux] = $clienteNombreAdeudo[$contador4];
            $insertarAdeudoRfc[$contador4aux] = $clienteRFCEmisorAdeudo[$contador4];
            $insertarAdeudoFecha[$contador4aux] = $fechaFacturacionAdeudo[$contador4];
            $contador4aux++;
        }
        $contador4++;
    }
    $contador5 = 0;
    $contador5aux = 0;
    $insertarNormal = array();
    $insertarNombre = array();
    $insertarRfc = array();
    $insertarNormalAUX = array();
    $contador5Existente = 0;
    while ($contador5 < count($ClientesNormal)) {
        if (in_array($ClientesNormal[$contador5], $RFC)) {
            $insertarNormalAUX[$contador5Existente] = $ClientesNormal[$contador5];
            $contador5Existente++;
        } else {
            $insertarNormal[$contador5aux] = $ClientesNormal[$contador5];
            $insertarNombre[$contador5aux] = $clienteNombre[$contador5];
            $insertarRfc[$contador5aux] = $clienteRFCEmisor[$contador5];
            $contador5aux++;
        }
        $contador5++;
    }
//    echo "TOTAL no existente Normal   " . count($insertarNormal) . "<br/>";
//    echo "TOTAL existente Normal    " . count($insertarNormalAUX) . "<br/>";
//    
//    echo "TOTAL no existente Adeudo   " . count($insertarAdeudo) . "<br/>";
//    echo "TOTAL existente Adeudo    " . count($insertarAdeudoAUX) . "<br/>";
    $contadorComprobacion = 0;
    $cliente->setActivo(1);
    $cliente->setUsuarioCreacion($_SESSION['user']);
    $cliente->setUsuarioModificacion($_SESSION['user']);

    $contador0 = 0;
    $contador0Aux = 0;
    $cliente->ClientesNoMarcados();
    $clientesNoMarcados = $cliente->getClienteNoMarcados();
//    echo "cantidad de clientes " . count($clientesNoMarcados) . "</br>";
//    echo "existentes para editar " . count($insertarAdeudoAUX) . "</br>";


    while ($contador0 < count($insertarAdeudoAUX)) {
        if (in_array($insertarAdeudoAUX[$contador0], $clientesNoMarcados)) {//comprobar si se puede editar                 
            $cliente->setUsuarioModificacion($_SESSION['user']);
            $cliente->setPantalla("Exportacion de clientes cambio de normal a moroso");
            if ($cliente->ComprobarTipoCliente($insertarAdeudoAUX[$contador0])) {//conculta cliente
                if ($cliente->getDiferenciaFechas() >= "3") {//verifica si es moroso o no
                    if ($cliente->editarTipoCliente($insertarAdeudoAUX[$contador0])) {
                        $contador0Aux++;
                    }
                    else
                        echo "Sin cambios " . $insertarAdeudoAUX[$contador0] . "</br>";
                }
            }
        }

//         $contador0Aux++;
        $contador0++;
    }
    echo "Se cambiaron " . $contador0Aux . "</b> clientes normales a morosos </br>";
    $c = 0;
    while ($contadorComprobacion < count($insertarAdeudo)) {
        if ($cliente->ComprobarTipoCliente($insertarAdeudo[$contadorComprobacion])) {
            if ($cliente->getDiferenciaFechas() >= "3") {
                $cliente->setIdEstatusCobranza(2);
                $cliente->datosFacturacion($insertarAdeudoRfc[$contadorComprobacion]); //hacer consulta
                $cliente->setNombreRazonSocial($insertarAdeudoNombre[$contadorComprobacion]);
                $cliente->setRfc($insertarAdeudo[$contadorComprobacion]);
                $cliente->setPantalla("Exportacion de clientes");
                if ($cliente->InsertarCliente()) {
                    $c++;
                }
                // echo "El cliente " . $insertarAdeudoNombre[$contadorComprobacion] . "se agrego exitosamente";
                else
                    echo "El cliente " . $insertarAdeudoNombre[$contadorComprobacion] . "no se agrego <br/>";
                // echo $insertarAdeudo[$contadorComprobacion] . "Moroso <br/>";
            } else {
                $cliente->setIdEstatusCobranza(1);
                $cliente->datosFacturacion($insertarAdeudoRfc[$contadorComprobacion]); //hacer consulta
                $cliente->setNombreRazonSocial($insertarAdeudoNombre[$contadorComprobacion]);
                $cliente->setRfc($insertarAdeudo[$contadorComprobacion]);
                $cliente->setPantalla("Exportacion de clientes");
                if ($cliente->InsertarCliente()) {
                    $c++;
                }
                //echo "El cliente " . $insertarAdeudoNombre[$contadorComprobacion] . "se agrego exitosamente";
                else
                    echo "El cliente " . $insertarAdeudoNombre[$contadorComprobacion] . "no se agrego <br/>";
//                echo $insertarAdeudo[$contadorComprobacion] . "normal <br/>";
            }
        }
        $contadorComprobacion++;
    }
    echo "Se exportaron <b>" . $c . "</b> clientes morosos correctamente </br>";

    //insertar clientes normales
    $contadornormales = 0;
    $b = 0;
    while ($contadornormales < count($insertarNormal)) {//count($insertarNormal)
        $cliente->setRfc($insertarNormal[$contadornormales]);
        $cliente->setIdEstatusCobranza(1);
        $cliente->datosFacturacion($insertarRfc[$contadornormales]); //hacer consulta
        $cliente->setNombreRazonSocial($insertarNombre[$contadornormales]);
        $cliente->setPantalla("Exportacion de clientes");
        if ($cliente->InsertarCliente()) {
            $b++;
        }
        //echo "El cliente " . $insertarAdeudoNombre[$contadorComprobacion] . "se agrego exitosamente";
        else
            echo "El cliente " . $insertarNombre[$contadornormales] . "no se agrego";
//                echo $insertarAdeudo[$contadorComprobacion] . "normal <br/>";
        $contadornormales++;
    }
    echo "Se exportaron <b>" . $b . "</b> clientes normales correctamente </br>";
}
else if (isset($_POST['claveCliente'])) {
    $claves = $_POST['claveCliente'];
    $cliente->setPonerMoroso(1);
    $cliente->setUsuarioCreacion($_SESSION['user']);
    $cliente->setUsuarioModificacion($_SESSION['user']);
    $cliente->setPantalla("No volver a poner como moroso");
    $contador = 0;
    while ($contador < count($claves)) {
        if ($cliente->NoVolverAPonerComoMoroso($claves[$contador]))
            echo "El cliente <b>" . $claves[$contador] . "</b> se modificó correctamente.<br/>";
        else
            echo "El cliente <b>" . $claves[$contador] . "</b> no se modificó.<br/>";
        $contador++;
    }
//    echo "entro";
//   
//    if ($cliente->NoVolverAPonerComoMoroso($_POST['claveCliente']))
//        echo "Se agrego correctamente";
//    else
//        echo "no se modifico";
}
?>

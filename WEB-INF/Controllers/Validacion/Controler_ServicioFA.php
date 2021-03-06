<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../Classes/ServicioFA.class.php");
include_once("../../Classes/ServicioIM.class.php");
$obj = new ServicioFA();

if (isset($_POST['eliminar']) && isset($_POST['IdKServicio'])) {
    $result = $obj->getEquiposByIdKAnexo($_POST['IdKServicio']);
    if (mysql_num_rows($result) > 0) {
        echo "Error: El servicio no puede ser eliminado, ya que contiene los siguientes equipos asociados: <br/>";
        while ($rs = mysql_fetch_array($result)) {
            echo $rs['NoSerie'] . ", ";
        }
    } else {
        if ($obj->getRegistroById($_POST['IdKServicio'])) {
            $obj_aux = new ServicioIM();
            $result = $obj_aux->getSolicitudesAbiertasAsignadas($_POST['IdKServicio'], $obj->getIdServicioFA());
            if (mysql_num_rows($result) > 0) {
                echo "Error: El servicio no puede ser eliminado, ya que fue asignado a equipos en las solicitudes abiertas: <br/>";
                while ($rs = mysql_fetch_array($result)) {
                    echo $rs['id_solicitud'] . ", ";
                }
            } else {
                if ($obj->deleteRegistro($_POST['IdKServicio'])) {
                    echo "El servicio " . $_POST['IdKServicio'] . " fue eliminado correctamente.";
                } else {
                    echo "Error: El servicio no pudo ser eliminado, intente de nuevo o reportelo por favor.";
                }
            }
        } else {
            if ($obj->deleteRegistro($_POST['IdKServicio'])) {
                echo "El servicio " . $_POST['IdKServicio'] . " fue eliminado correctamente.";
            } else {
                echo "Error: El servicio no pudo ser eliminado, intente de nuevo o reportelo por favor.";
            }
        }
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }

    if (isset($parametros['tipo_servicioIM'])) {
        $obj->setIdServicioFA($parametros['tipo_servicioIM']);
    } else {
        $obj->setIdServicioFA($_POST['servicio']);
    }
    if (isset($parametros['anexo_servicioIM'])) {
        $obj->setIdAnexoClienteCC($parametros['anexo_servicioIM']);
    } else {
        $obj->setIdAnexoClienteCC($_POST['IdAnexo']);
    }
    if (isset($parametros['renta_servicioIM']) && $parametros['renta_servicioIM'] != "") {
        $obj->setRentaMensual($parametros['renta_servicioIM']);
    } else {
        $obj->setRentaMensual("null");
    }
    if (isset($parametros['incluidasBN']) && $parametros['incluidasBN'] != "") {
        $obj->setMLIncluidosBN($parametros['incluidasBN']);
    } else {
        $obj->setMLIncluidosBN("null");
    }
    if (isset($parametros['incluidasColor']) && $parametros['incluidasColor'] != "") {
        $obj->setMLIncluidosColor($parametros['incluidasColor']);
    } else {
        $obj->setMLIncluidosColor("null");
    }
    if (isset($parametros['excedentesBN']) && $parametros['excedentesBN'] != "") {
        $obj->setCostoMLExcedentesBN($parametros['excedentesBN']);
    } else {
        $obj->setCostoMLExcedentesBN("null");
    }
    if (isset($parametros['excedentesColor']) && $parametros['excedentesColor'] != "") {
        $obj->setCostoMLExcedentesColor($parametros['excedentesColor']);
    } else {
        $obj->setCostoMLExcedentesColor("null");
    }
    if (isset($parametros['procesadasBN']) && $parametros['procesadasBN'] != "") {
        $obj->setCostoMLProcesadosBN($parametros['procesadasBN']);
    } else {
        $obj->setCostoMLProcesadosBN("null");
    }
    if (isset($parametros['procesadasColor']) && $parametros['procesadasColor'] != "") {
        $obj->setCostoMLProcesadosColor($parametros['procesadasColor']);
    } else {
        $obj->setCostoMLProcesadosColor("null");
    }

    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla('PHP ServicioFA');

    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo $obj->getIdKServicioFA();
        } else {
            echo "Error: El servicio no se pudo registrar.";
        }
    } else {/* Modificar */
        $id = $parametros['id'];
        $obj->setIdKServicioFA($parametros['id']);
        $array = $obj->getNoSerieUpdate($id); //traer los numeros de serie que se van a modificar de inventario equipo
        if ($obj->editRegistro()) {
            $idServico = $obj->getIdServicioFA();
            $strSerie = "'" . implode("','", $array) . "'"; //convertir el array en string
            if ($obj->editInvetarioEquipo($strSerie, $idServico)) {
                echo $obj->getIdKServicioFA();
            } else {
                echo "Error: El servicio no se pudo modificar, intenta más tarde por favor 12";
            }
        } else {
            echo "Error: El servicio no se pudo modificar, intenta más tarde por favor";
        }
    }
}
?>
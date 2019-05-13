<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
if (isset($_POST['id']) && $_POST['id'] != "" && isset($_POST['noparte']) && $_POST['noparte'] != "") {
    include_once ("../Classes/Catalogo.class.php");
    include_once("../Classes/ComponentesDetalle.class.php");
    include_once("../Classes/CompCompatiblesEq.class.php");
    include_once("../Classes/ComponentesNecesariosC.class.php");
    $catalogo = new Catalogo();
    $componente = new ComponentesDetalle();
    $query = "SELECT k_componentecomponenteinicial.NoParteComponentePadre AS Padre FROM k_componentecomponenteinicial WHERE NoParteComponente='" . $_POST['id'] . "'";
    $result = $catalogo->obtenerLista($query);
    $componente->setPantalla("PHP Controller_CopiadoCompo.php");
    $componente->setUsuarioCreacion($_SESSION['user']);
    $componente->setUsuarioModificacion($_SESSION['user']);
    $componente->setNoParteHijo($_POST['noparte']);
    while ($row = mysql_fetch_array($result)) {
        $componente->setNoPartePadre($row['Padre']);
        $componente->newRegistro();
    }
    //terminamos con los componentes
    //empezamos con los equipos
    $query ="SELECT NoParteEquipo,Soportado FROM k_equipocomponentecompatible WHERE NoParteComponente='".$_POST['id']."'";
    $result = $catalogo->obtenerLista($query);
    $equipo = new CompCompatiblesEq();
    $equipo->setPantalla("PHP Controller_CopiadoCompo.php");
    $equipo->setUsuarioCreacion($_SESSION['user']);
    $equipo->setUsuarioModificacion($_SESSION['user']);
    $equipo->setNoParteComponente($_POST['noparte']);
    while ($row = mysql_fetch_array($result)) {
        $equipo->setNoParteEquipo($row['NoParteEquipo']);
        $equipo->setSoportado($row['Soportado']);
        $equipo->newRegistro();
    }
    //terminamos con los equipos
    //comenzamos con componentes necesarios
    $query ="SELECT NoParteComponente FROM k_componentecomponentenecesario WHERE k_componentecomponentenecesario.NoParteComponentePadre='".$_POST['id']."'";
    $result = $catalogo->obtenerLista($query);
    $componece=new ComponentesNecesariosC();
    $componece->setNoPartePadre($_POST['noparte']);
    $componece->setPantalla("PHP Controller_CopiadoCompo.php");
    $componece->setUsuarioCreacion($_SESSION['user']);
    $componece->setUsuarioModificacion($_SESSION['user']);
    while ($row = mysql_fetch_array($result)) {
        $componece->setNoParteHijo($row['NoParteComponente']);
        $componece->newRegistro();
    }
    //terminamos con componentes necesarios
    //empezamos con la locura
    //terminamos la locura
    //empezamos con partes del componente
    $query ="SELECT NoParteComponente AS Hijo FROM k_componentecomponenteinicial WHERE NoParteComponentePadre='".$_POST['id']."'";
    $result = $catalogo->obtenerLista($query);
    $componente->setPantalla("PHP Controller_CopiadoCompo.php");
    $componente->setUsuarioCreacion($_SESSION['user']);
    $componente->setUsuarioModificacion($_SESSION['user']);
    $componente->setNoPartePadre($_POST['noparte']);
    while ($row = mysql_fetch_array($result)) {
        $componente->setNoParteHijo($row['Hijo']);
        $componente->newRegistro();
    }
} else {
    echo "No se recebieron los datos del copiado correctamente";
}
?>

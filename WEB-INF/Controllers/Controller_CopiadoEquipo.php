<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
if (isset($_POST['id']) && $_POST['id'] != "" && isset($_POST['noparte']) && $_POST['noparte'] != "") {
    include_once("../Classes/Catalogo.class.php");
    include_once("../Classes/PartesEquipo.class.php");
    include_once("../Classes/CompCompatiblesEq.class.php");
    include_once("../Classes/ComponentesEquipo.class.php");
    $catalogo = new Catalogo();
    $partesequipo = new PartesEquipo();
    //empezamos con las partes del equipo
    $query = "SELECT NoParteComponente,SoportadoMaximo  FROM k_parteequipo WHERE NoParteEquipo='".$_POST['id']."';";
    $result = $catalogo->obtenerLista($query);
    $partesequipo->setPantalla("PHP Controller_CopiadoEquipo.php");
    $partesequipo->setNoPartesEquipo($_POST['noparte']);
    $partesequipo->setUsuarioCreacion($_SESSION['user']);
    $partesequipo->setUsuarioModificacion($_SESSION['user']);
    while ($row = mysql_fetch_array($result)) {
        $partesequipo->setNoParteComponente($row['NoParteComponente']);
        $partesequipo->setSoportadoMax($row['SoportadoMaximo']);
        $partesequipo->newRegistro();
    }
    //terminamos con las partes del equipo
    //empezamos con los componentes compatibles
    $query ="SELECT NoParteComponente,Soportado FROM k_equipocomponentecompatible WHERE NoParteEquipo='".$_POST['id']."'";
    $result = $catalogo->obtenerLista($query);
    $equipo = new CompCompatiblesEq();
    $equipo->setPantalla("PHP Controller_CopiadoEquipo.php");
    $equipo->setUsuarioCreacion($_SESSION['user']);
    $equipo->setUsuarioModificacion($_SESSION['user']);
    $equipo->setNoParteEquipo($_POST['noparte']);
    while ($row = mysql_fetch_array($result)) {
        $equipo->setNoParteComponente($row['NoParteComponente']);
        $equipo->setSoportado($row['Soportado']);
        $equipo->newRegistro();
    }
    //terminamos con los componentes compatibles
    //comenzamos con componentes equipo
    $query ="SELECT NoParteComponente,Instalado FROM k_equipocomponenteinicial WHERE NoParteEquipo='".$_POST['id']."'";
    $result = $catalogo->obtenerLista($query);
    $componentesequipo=new ComponentesEquipo();
    $componentesequipo->setPantalla("PHP Controller_CopiadoEquipo.php");
    $componentesequipo->setNoPartesEquipo($_POST['noparte']);
    $componentesequipo->setUsuarioCreacion($_SESSION['user']);
    $componentesequipo->setUsuarioModificacion($_SESSION['user']);
    while ($row = mysql_fetch_array($result)) {
        $componentesequipo->setNoPartesComponentes($row['NoParteComponente']);
        $componentesequipo->setInstalado($row['Instalado']);
        $componentesequipo->newRegistro();
    }
    //terminamos con componentes equipo
} else {
    echo "No se recebieron los datos del copiado correctamente";
}
?>

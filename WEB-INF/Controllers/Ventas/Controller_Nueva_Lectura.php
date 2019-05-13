<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$parametros;
if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}
$NivelTN="null";
$NivelTC="null";
$NivelTM="null";
$NivelTA="null";
if(isset($parametros['NivelTA']) && $parametros['NivelTA']!=""){
    $NivelTA="'".$parametros['NivelTA']."'";
}
if(isset($parametros['NivelTM']) && $parametros['NivelTM']!=""){
    $NivelTM="'".$parametros['NivelTM']."'";
}
if(isset($parametros['NivelTC']) && $parametros['NivelTC']!=""){
    $NivelTC="'".$parametros['NivelTC']."'";
}
if(isset($parametros['NivelTN']) && $parametros['NivelTN']!=""){
    $NivelTN="'".$parametros['NivelTN']."'";
}
if (isset($parametros['contadorcl'])) {
    if (isset($parametros['contadorclml']) && isset($parametros['contadorbnml'])) {
        $catalogo->obtenerLista("INSERT INTO c_lectura(NoSerie,Fecha,ContadorBNPaginas,ContadorColorPaginas,ContadorBNML,ContadorColorML,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,Pantalla,NivelTonNegro,NivelTonCian,NivelTonMagenta,NivelTonAmarillo,Activo,FechaUltimaModificacion)
VALUES('" . $parametros['nserie'] . "','" . $parametros['fecha'] . "','" . $parametros['contadorbn'] . "','" . $parametros['contadorcl'] . "','" . $parametros['contadorbnml'] . "','" . $parametros['contadorclml'] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "','ASP.operacion_altalectura_aspx',".$NivelTN."," . $NivelTC . "," . $NivelTM . "," . $NivelTA . ",1,NOW());");
    } else {
        $catalogo->obtenerLista("INSERT INTO c_lectura(NoSerie,Fecha,ContadorBNPaginas,ContadorColorPaginas,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,Pantalla,NivelTonNegro,NivelTonCian,NivelTonMagenta,NivelTonAmarillo,Activo,FechaUltimaModificacion)
VALUES('" . $parametros['nserie'] . "','" . $parametros['fecha'] . "','" . $parametros['contadorbn'] . "','" . $parametros['contadorcl'] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "','ASP.operacion_altalectura_aspx'," . $NivelTN . "," . $NivelTC . "," . $NivelTM . "," . $NivelTA . ",1,NOW());");
    }
} else {
    if (isset($parametros['contadorbnml'])) {
        $catalogo->obtenerLista("INSERT INTO c_lectura(NoSerie,Fecha,ContadorBNPaginas,ContadorBNML,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,Pantalla,NivelTonNegro,Activo,FechaUltimaModificacion)
VALUES('" . $parametros['nserie'] . "','" . $parametros['fecha'] . "','" . $parametros['contadorbn'] . "','" . $parametros['contadorbnml'] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "','ASP.operacion_altalectura_aspx'," . $NivelTN . ",1,NOW());");
    } else {
        $catalogo->obtenerLista("INSERT INTO c_lectura(NoSerie,Fecha,ContadorBNPaginas,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,Pantalla,NivelTonNegro,Activo,FechaUltimaModificacion)
VALUES('" . $parametros['nserie'] . "','" . $parametros['fecha'] . "','" . $parametros['contadorbn'] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "','ASP.operacion_altalectura_aspx'," . $NivelTN . ",1,NOW());");
    }
}
echo "Guardado";
?>

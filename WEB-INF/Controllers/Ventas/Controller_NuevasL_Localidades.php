<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Mail.class.php");
$catalogo = new Catalogo();
$parametros;
if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}
$contador = 1;
for(;;) {
    if (isset($parametros['fecha' . $contador])) {
        $NivelTN = "null";
        $NivelTC = "null";
        $NivelTM = "null";
        $NivelTA = "null";
        if (isset($parametros['NivelTA' . $contador]) && $parametros['NivelTA' . $contador] != "") {
            $NivelTA = "'" . $parametros['NivelTA' . $contador] . "'";
        }
        if (isset($parametros['NivelTM' . $contador]) && $parametros['NivelTM' . $contador] != "") {
            $NivelTM = "'" . $parametros['NivelTM' . $contador] . "'";
        }
        if (isset($parametros['NivelTC' . $contador]) && $parametros['NivelTC' . $contador] != "") {
            $NivelTC = "'" . $parametros['NivelTC' . $contador] . "'";
        }
        if (isset($parametros['NivelTN' . $contador]) && $parametros['NivelTN' . $contador] != "") {
            $NivelTN = "'" . $parametros['NivelTN' . $contador] . "'";
        }
        if (isset($parametros['contadorcl' . $contador])) {
            if (isset($parametros['contadorclml' . $contador]) && isset($parametros['contadorbnml' . $contador])) {
                $catalogo->obtenerLista("INSERT INTO c_lectura(NoSerie,Fecha,ContadorBNPaginas,ContadorColorPaginas,ContadorBNML,ContadorColorML,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,Pantalla,NivelTonNegro,NivelTonCian,NivelTonMagenta,NivelTonAmarillo,Activo,FechaUltimaModificacion)
VALUES('" . $parametros['nserie' . $contador]  . "','" . $parametros['fecha' . $contador] . "','" . $parametros['contadorbn' . $contador] . "','" . $parametros['contadorcl' . $contador] . "','" . $parametros['contadorbnml' . $contador] . "','" . $parametros['contadorclml' . $contador] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "','ASP.operacion_altalectura_aspx'," . $NivelTN . "," . $NivelTC . "," . $NivelTM . "," . $NivelTA . ",1,NOW());");
            } else {
                $catalogo->obtenerLista("INSERT INTO c_lectura(NoSerie,Fecha,ContadorBNPaginas,ContadorColorPaginas,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,Pantalla,NivelTonNegro,NivelTonCian,NivelTonMagenta,NivelTonAmarillo,Activo,FechaUltimaModificacion)
VALUES('" . $parametros['nserie' . $contador]  . "','" . $parametros['fecha' . $contador] . "','" . $parametros['contadorbn' . $contador] . "','" . $parametros['contadorcl' . $contador] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "','ASP.operacion_altalectura_aspx'," . $NivelTN . "," . $NivelTC . "," . $NivelTM . "," . $NivelTA . ",1,NOW());");
            }
        } else {
            if (isset($parametros['contadorbnml' . $contador])) {
                $catalogo->obtenerLista("INSERT INTO c_lectura(NoSerie,Fecha,ContadorBNPaginas,ContadorBNML,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,Pantalla,NivelTonNegro,Activo,FechaUltimaModificacion)
VALUES('" . $parametros['nserie' . $contador]  . "','" . $parametros['fecha' . $contador] . "','" . $parametros['contadorbn' . $contador] . "','" . $parametros['contadorbnml' . $contador] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "','ASP.operacion_altalectura_aspx'," . $NivelTN . ",1,NOW());");
            } else {
                $catalogo->obtenerLista("INSERT INTO c_lectura(NoSerie,Fecha,ContadorBNPaginas,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,Pantalla,NivelTonNegro,Activo,FechaUltimaModificacion)
VALUES('" . $parametros['nserie' . $contador]  . "','" . $parametros['fecha' . $contador] . "','" . $parametros['contadorbn' . $contador] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "','ASP.operacion_altalectura_aspx'," . $NivelTN . ",1,NOW());");
            }
        }
        $contador++;
    } else {
        break;
    }
}
?>

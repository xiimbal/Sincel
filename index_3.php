<?php

include_once("WEB-INF/Classes/ConexionMultiBD.class.php"); // ** PCM 30/01/2019

$mensaje = "";

if (isset($_GET['session']) && $_GET['session'] == "false") {

    $mensaje = "El usuario y/o contraseñas incorrectos";
        

}else if (isset($_GET['session']) && $_GET['session'] == "false1") {
    
    // ** PCM 30/01/2019
    $user = $_GET['usr'];
    $segundos_max = 180;
    $segundos = 0;
    $conexion = new ConexionMultiBD();
    $conexion->ConexionMultiBD();
    $consulta = 'CALL totalSeconds("'.$user.'","fecha","fecha2");';
    $query = $conexion->Ejecutar($consulta);

    while ($row = mysql_fetch_assoc($query)){

        $segundos = $row["Segundos"];
        
    }        
    if ($segundos >= 180){
        $segundos = 0;
    }else{
        $segundos = $segundos_max - $segundos;    
    }    
    $mensaje = "Usuario ".$_GET['usr']." bloqueado por 3 o más intentos fallidos consecutivos <br>";
}else if (isset($_GET['session']) && $_GET['session'] == "false2") {
    $mensaje = "El usuario esta bloqueado";
}else if (isset($_GET['session']) && $_GET['session'] == "false3") {
    $mensaje = "Esta empresa se encuentra inactiva";
    // PCM **
}else if (isset($_GET['session']) && $_GET['session'] == "finished") {
    $mensaje = "La sessión ha caducado, ingrese de nuevo por favor";
}
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minmum-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="expires" content="-1">
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>        
        <script src="resources/js/md5.js"></script>
        <link rel="shortcut icon" href="resources/images/logos/ra4.png" type="image/x-icon"/>
        <title>ARA</title>
        <meta http-equiv="expires" content="-1">
        <link id="linkCSS" href="./css/Site.css" rel="stylesheet" type="text/css" media="all">
        <link href="./css/Site.css" rel="stylesheet" type="text/css">
        <style>
            .contenido{
                width: 800px;
                margin-left:auto;
                margin-right:auto;
            }
            .style1

            {
                width: 30%;
            }
        </style>

        <script language="javascript" type="text/javascript" src="/resources/js/countdown.js"></script> <!-- ** PCM 30/01/2019 -->

    </head>
    <!-- ** PCM 30/01/2019 -->
    <body onload="setup()">
    <!-- PCM ** -->
        <form method="post" action="sesion.php" onsubmit="javascript:Encriptar(); return WebForm_OnSubmit();" id="form1">
            
            <script type="text/javascript">
                //<![CDATA[
                var theForm = document.forms['form1'];
                if (!theForm) {
                    theForm = document.form1;
                }
                function __doPostBack(eventTarget, eventArgument) {
                    if (!theForm.onsubmit || (theForm.onsubmit() != false)) {
                        theForm.__EVENTTARGET.value = eventTarget;
                        theForm.__EVENTARGUMENT.value = eventArgument;
                        theForm.submit();
                    }
                }

                function Encriptar() {
                    $("#password").val($.md5($("#password").val()));
                }
                
                function mostrarRecuperarPassword(){
                    if($("#show").val() == "0"){
                        $("#forgot_password").show();
                        $("#show").val("1");
                        $('#linkrecordar').text('Recordaste tu contraseña');
                    }else{
                        $("#forgot_password").hide();
                        $("#show").val("0");
                        $('#linkrecordar').text('¿Olvidaste tu contraseña?');
                    }
                }
                
                
                //]]>
            </script>

            <script src="./Facturación_files/WebResource.axd" type="text/javascript"></script>
            <script src="./Facturación_files/WebResource(1).axd" type="text/javascript"></script>
            <script src="./Facturación_files/WebResource(2).axd" type="text/javascript"></script>
            
            <script type="text/javascript">
                //<![CDATA[
                function WebForm_OnSubmit() {
                    if (typeof (ValidatorOnSubmit) == "function" && ValidatorOnSubmit() == false) {
                        return false;
                    }
                    return true;
                }

                //]]>
            </script>

            <div class="page">

                <div class="header">
                    <!--<div style=" float:right;" class="title">
                        <h1>
                            FACTURACIÓN ELECTRÓNICA
                        </h1>
                    </div>-->
                    <div style=" float:left;width:960px;height:98px;overflow:hidden; ">
                        <!--<img src="./img/Genesis-Kiosera_r1_c2_site.jpg" id="imgLogo">-->
                        <img src="./img/baner_factury.jpg" id="imgLogo" style=" position: absolute;right: 0px;">
                    </div>

                </div>

                <div class="main">
                    <table style=" width:100%">
                        <tbody>
                            <tr>
                                <td class="style1"></td>
                                <td>
                                    <h2 class="titulos">
                                        INICIO SESIÓN
                                    </h2>
                                    <p>
                                        Ingresar usuario y password.
                                    </p>                                    
                                    <div id="MainContent_LoginUserValidationSummary" class="failureNotification" style="display:none;">
                                    </div>
                                    <div>
                                        <fieldset class="login">
                                            <legend>Información cuenta</legend>
                                            
                                            <p>
                                                <label for="username" id="MainContent_UserNameLabel">Nombre usuario:</label>
                                                <input name="username" type="text" id="username" class="textEntry">
                                                <span id="MainContent_UserNameRequired" title="Usuario requerido." class="failureNotification" style="visibility:hidden;">*</span>
                                            </p>

                                            <p>
                                                <label for="password" id="MainContent_PasswordLabel">Password:</label>
                                                <input  name="password" type="password" id="password" class="passwordEntry">
                                                <span id="MainContent_PasswordRequired" title="Password requerido." class="failureNotification" style="visibility:hidden;">*</span>
                                            </p>

                                            <!-- ** PCM 30/01/2019 -->
                                            <div style="color: red; display: inline;">
                                                <?php echo $mensaje; ?>
                                                <p hidden id='mensaje_time'>Esperar</p>
                                                <p hidden id="timer">______</p>
                                                <p hidden id='mensaje_time2'>para que se desbloqueé</p>
                                            </div>

                                            <input hidden type='datetime' name='tiempo' id='tiempo' value='<?php echo $segundos ?>'>
                                            <!-- PCM ** -->
                                            <!--<a href="#" id="linkrecordar" onclick="mostrarRecuperarPassword(); return false;" >¿Olvidaste tu contraseña?</a>-->
                                        </fieldset>
                                        <p class="submitButton">
                                            <input type="submit" name="ctl00$MainContent$LoginButton" value="Iniciar Sesión" onclick="javascript:WebForm_DoPostBackWithOptions(new WebForm_PostBackOptions( & quot; ctl00$MainContent$LoginButton & quot; , & quot; & quot; , true, & quot; LoginUserValidationGroup & quot; , & quot; & quot; , false, false))" id="MainContent_LoginButton">
                                        </p>
                                    </div>
                                </td>
                                <td style=" width:30%"></td>
                            </tr>
                        </tbody>
                    </table>   
                    <!--<div id="forgot_password" style="margin-left: 30%; width: 40%; display: none;">
                        <input type="hidden" id="show" name="show" value="0"/>
                        <fieldset class="login">
                            <legend>Recuperar contraseña</legend>
                            <p>
                                <label for="username2" id="MainContent_UserNameLabel">Nombre usuario:</label>
                                <input name="username2" type="text" id="username2" class="textEntry" required="required">
                                
                            </p>
                            <p>
                                <label for="correo" id="MainContent_PasswordLabel">Correo:</label>
                                <input  name="correo" type="email" id="correo" required="required">
                                
                                <button id="recuper" name="recuperar" onclick=" return false;">Recuperar contraseña</button>
                            </p>
                            <div style="color: red;"></div>
                        </fieldset>
                    </div>-->
                </div>
                <div class="clear">
                </div>
                <div class="DivEncabezado">
                    <table border="0" cellpadding="0" cellspacing="0">
                        <tbody><tr>
                                <td class="DTImagenEncabezado"></td>
                            </tr>
                        </tbody></table>
                </div>
                <div class="DivLineaEncabezado">
                </div>
            </div>

            <div class="footer">
            </div>

            <script type="text/javascript">
                //<![CDATA[
                var Page_ValidationSummaries = new Array(document.getElementById("MainContent_LoginUserValidationSummary"));
                var Page_Validators = new Array(document.getElementById("MainContent_UserNameRequired"), document.getElementById("MainContent_PasswordRequired"));
                //]]>
            </script>

            <script type="text/javascript">
                //<![CDATA[
                var MainContent_LoginUserValidationSummary = document.all ? document.all["MainContent_LoginUserValidationSummary"] : document.getElementById("MainContent_LoginUserValidationSummary");
                MainContent_LoginUserValidationSummary.validationGroup = "LoginUserValidationGroup";
                var MainContent_UserNameRequired = document.all ? document.all["MainContent_UserNameRequired"] : document.getElementById("MainContent_UserNameRequired");
                MainContent_UserNameRequired.controltovalidate = "username";
                MainContent_UserNameRequired.errormessage = "Usuario requerido.";
                MainContent_UserNameRequired.validationGroup = "LoginUserValidationGroup";
                MainContent_UserNameRequired.evaluationfunction = "RequiredFieldValidatorEvaluateIsValid";
                MainContent_UserNameRequired.initialvalue = "";
                var MainContent_PasswordRequired = document.all ? document.all["MainContent_PasswordRequired"] : document.getElementById("MainContent_PasswordRequired");
                MainContent_PasswordRequired.controltovalidate = "password";
                MainContent_PasswordRequired.errormessage = "Password requerido.";
                MainContent_PasswordRequired.validationGroup = "LoginUserValidationGroup";
                MainContent_PasswordRequired.evaluationfunction = "RequiredFieldValidatorEvaluateIsValid";
                MainContent_PasswordRequired.initialvalue = "";
                //]]>
            </script>


            <script type="text/javascript">
                //<![CDATA[

                var Page_ValidationActive = false;
                if (typeof (ValidatorOnLoad) == "function") {
                    ValidatorOnLoad();
                }

                function ValidatorOnSubmit() {
                    if (Page_ValidationActive) {
                        return ValidatorCommonOnSubmit();
                    }
                    else {
                        return true;
                    }
                }
                WebForm_AutoFocus('username'); //]]>
            </script>

        </form>       
    </body>
</html>
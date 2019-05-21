<?php

    header("Content-Type: text/html;charset=utf-8");

    include_once("WEB-INF/Classes/ConexionMultiBD.class.php"); // ** PCM 30/01/2019

    $mensaje = "";

    if (isset($_GET['session']) && $_GET['session'] == "false") {

        $mensaje = '<strong> <i class="far fa-user-shield"></i> El usuario y/o contraseñas </strong> son incorrectos.';

        //ALERTA (USUARIO O CONTRASEÑA INCORRECTOS)
        echo ('<div id="usuario_incorrecto" class="alert alert-dismissible alert-danger fixed-top" role="alert">
            
                <p class=" text-center m-0 p-0">'                                       
                    . $mensaje . 
                                   
                '</p>'.

                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                </div>
            ');

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

        $mensaje = '<strong> <i class="far fa-user-lock"></i> El usuario ' .$_GET['usr']. ' ha sido bloqueado</strong> por 3 o más intentos fallidos <strong>consecutivos.</strong>';

        //ALERTA (USUARIO O CONTRASEÑA INCORRECTOS)
        echo ('<div id="usuario_incorrecto" class="alert alert-dismissible alert-danger fixed-top" role="alert">
            
                <p class=" text-center m-0 p-0">'                                       
                    . $mensaje . 
                    "<center>
                        <p hidden id='mensaje_time'> Debe esperar </p>
                        <strong>
                            <p hidden id='timer'></p>
                        </strong>
                        <p hidden id='mensaje_time2'>para que se desbloqueé</p>      
                        <input hidden type='text' name='tiempo' id='tiempo' value='$segundos'>
                     </center>                 
                </p>".

                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                </div>
            ');

    }else if (isset($_GET['session']) && $_GET['session'] == "false2") {

        $mensaje = '<i class="far fa-user-lock"></i> El usuario <strong> ' .$_GET['usr']. ' </strong> se encuentra bloqueado.';

        //ALERTA (USUARIO O CONTRASEÑA INCORRECTOS)
        echo ('<div id="usuario_incorrecto" class="alert alert-dismissible alert-danger fixed-top" role="alert">
            
                <p class=" text-center m-0 p-0">'                                       
                    . $mensaje . 
                                   
                '</p>'.

                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                </div>
            ');

    }else if (isset($_GET['session']) && $_GET['session'] == "false3") {

        $mensaje = '<i class="far fa-lock"></i> Esta empresa se encuentra <strong> bloqueada.</strong>';
        
        //ALERTA (USUARIO O CONTRASEÑA INCORRECTOS)
        echo ('<div id="usuario_incorrecto" class="alert alert-dismissible alert-danger fixed-top" role="alert">
            
                <p class=" text-center m-0 p-0">'                                       
                    . $mensaje . 
                                   
                '</p>'.

                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                </div>
            ');

    }else if (isset($_GET['session']) && $_GET['session'] == "finished") {
        
        $mensaje = '<strong><i class="far fa-user-times"></i></strong> La sesión ha caducado, <strong>ingrese de nuevo por favor.</strong>';

        //ALERTA (USUARIO O CONTRASEÑA INCORRECTOS)
        echo ('<div id="usuario_incorrecto" class="alert alert-dismissible alert-danger fixed-top" role="alert">
            
                <p class=" text-center m-0 p-0">'                                       
                    . $mensaje . 
                                   
                '</p>'.

                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                </div>
            ');

    }
?>

<!DOCTYPE html>
<html lang="es">

    <head>

        <!-- METATAGS PARA EL CORRECTO FUNCIONAMIENTO DEL CONTENIDO RESPONSIVE -->
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minmum-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="expires" content="-1">
        <!-- FIN DE LOS METATAGS PARA EL CORRECTO FUNCIONAMIENTO DEL CONTENIDO RESPONSIVE -->
        
        <script src="resources/js/jquery/jquery-1.11.3.min.js"></script>        
        <script src="resources/js/md5.js"></script>
        
        <link rel="shortcut icon" href="resources/images/logos/ra4.png" type="image/x-icon"/>
        <title>ARA</title>
        
        <link id="linkCSS" href="./css/Site.css" rel="stylesheet" type="text/css" media="all">
        <link href="./css/Site.css" rel="stylesheet" type="text/css">

        <!-- ESTILOS DE BOOTSTARP 4 -->
        <link href="resources/css/Bootstrap 4/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        
        <!-- ESTILOS PARA ICONOS FONTAWESOME -->
        <link href="resources/css/Bootstrap 4/css/all.min.css" rel="stylesheet">

        <!-- ESTILOS PERSONALIZADOS DEL SITIO WEB -->
        <link href="resources/css/Bootstrap 4/css/login.css" rel="stylesheet" type="text/css">
        
        <script language="javascript" type="text/javascript" src="resources/js/countdown.js"></script>             <!-- PCM 30/01/2019 -->
    </head>

    <!-- PCM 30/01/2019 -->
    <body onload="setup()">
    <!-- PCM ** -->

        <div class="contenedor-principal row">

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

            <!-- FORMULARIO DE INICIO DE SESION -->
            <div class="contenedor-formulario col-lg-5">

                <!-- HEADER DEL FORMULARIO  -->
                <div class="titulo-ARA header">
                    
                    <img src="resources/images/logos/ARA.png" alt="ARA LOGO">

                    <p class="sesion">
                        Inicie sesión en <span class="ara-span-login">ARA</span>
                    </p>

                </div>
                <!-- FIN DEL HEADER DEL FORMULARIO  -->

                <form method="post" class="formulario-sesion mt-5 pt-4" action="sesion.php" onsubmit="javascript:Encriptar();return WebForm_OnSubmit();" id="form1">

                    <div class="form-group">

                        <label for="username" id="MainContent_UserNameLabel">Nombre usuario</label>
                        <input class="form-control" name="username" type="text" id="username" class="textEntry" autofocus required>
                        <!-- <span id="MainContent_UserNameRequired" title="Usuario requerido." class="failureNotification" style="visibility:hidden;">*</span> -->

                        <label class="mt-4" for="password" id="MainContent_PasswordLabel">Contraseña</label>
                        <input class="form-control" name="password" type="password" id="password" class="passwordEntry" required>
                        <!-- <span id="MainContent_PasswordRequired" title="Password requerido." class="failureNotification" style="visibility:hidden;">*</span> -->                                                                                                

                        <!--<a href="#" id="linkrecordar" onclick="mostrarRecuperarPassword(); return false;" >¿Olvidaste tu contraseña?</a>-->                                                                                                                            
                        <button class="btn btn-block btn-outline-secondary mt-5" type="submit" name="ctl00$MainContent$LoginButton" onclick="javascript:WebForm_DoPostBackWithOptions(new WebForm_PostBackOptions( & quot; ctl00$MainContent$LoginButton & quot; , & quot; & quot; , true, & quot; LoginUserValidationGroup & quot; , & quot; & quot; , false, false))" id="MainContent_LoginButton">
                            Iniciar Sesión
                            <i class="far fa-sign-in-alt"></i>
                        </button>

                    </div>

                </form>                                                                
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
                <div class="clear">
                </div>
                
            </div>
            <!-- FIN DEL FORMULARIO DE INICIO DE SESION -->

            <!-- CONTENEDOR DE LOS SLIDES -->
            <div class="contenedor-slides d-none d-lg-block col-lg-7">

                <!-- CONTENEDOR DEL CAROUSEL -->
                <div id="carousel-login" class="carousel slide carousel-fade" data-ride="carousel">

                    <!-- CONTENEDOR DE LAS IMAGENES DEL SLIDER -->
                    <div class="carousel-inner  d-flex align-items-center">

                        <div class="carousel-item active">
                            <img src="resources/images/1.png" class="d-block w-100" alt="...">
                        </div>

                        <div class="carousel-item">
                            <img src="resources/images/2.png" class="d-block w-100" alt="...">
                        </div>

                        <div class="carousel-item">
                            <img src="resources/images/3.png" class="d-block w-100" alt="...">
                        </div>

                    </div>
                    <!-- FIN DEL CONTENEDOR DE LAS IMAGENES DEL SLIDER -->

                </div>
                <!-- FIND DEL CONTENEDOR DEL CAROUSEL -->

            </div>
            <!-- CONTENEDOR DE LOS SLIDES -->

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

        </div> 

        <!-- SCRIPTS QUE CARGAN LAS FUNCIONALIDADES JS DE BOOTSTRAP -->
        <!-- <script src="resources/js/Bootstrap 4/jquery-3.3.1.min.js"></script> -->
        <script src="resources/js/Bootstrap 4/popper.min.js"></script>
        <script src="resources/js/Bootstrap 4/bootstrap.min.js"></script>

    </body>

</html>
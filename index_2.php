<?php
    $mensaje = "";

    if (isset($_GET['session']) && $_GET['session'] == "false") {

        $mensaje = '<strong> <i class="far fa-user-shield"></i> El usuario y/o contraseñas </strong> son incorrectos';

    }else if (isset($_GET['session']) && $_GET['session'] == "false1") {

        $mensaje =  '<strong> <i class="far fa-user-lock"></i> El usuario ' .$_GET['usr']. ' ha sido bloqueado</strong> por 3 o más intentos fallidos consecutivos';

    }else if (isset($_GET['session']) && $_GET['session'] == "finished") {

        $mensaje = "La sessión ha caducado, ingrese de nuevo por favor";

    }
?>
<!DOCTYPE html>
<html lang="es">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>        
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

    </head>

    <body>

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
                        $('# linkrecordar').text('Recordaste tu contraseña');
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
                <div class="titulo-ARA">

                    <img src="resources/images/logos/ARA.png" alt="ARA LOGO">

                    <p class="sesion">
                        Inicie sesión en <span class="ara-span-login">ARA</span>
                    </p>

                </div>
                <!-- FIN DEL HEADER DEL FORMULARIO  -->
                
                <!-- FORMULARIO DE INICIO DE SESION -->
                <form method="post" class="formulario-sesion mt-4" action="sesion.php" onsubmit="javascript:Encriptar();return WebForm_OnSubmit();" id="form1">
                    
                    <div class="form-group">

                        <label for="username" id="MainContent_UserNameLabel">Nombre usuario</label>
                        <input class="form-control" name="username" type="text" id="username" class="textEntry" required>
                        <!-- <span id="MainContent_UserNameRequired" title="Usuario requerido." class="failureNotification" style="visibility:hidden;">*</span> -->

                        <label class="mt-4" for="password" id="MainContent_PasswordLabel">Contraseña</label>
                        <input class="form-control" name="password" type="password" id="password" class="passwordEntry" required>
                        <!-- <span id="MainContent_PasswordRequired" title="Password requerido." class="failureNotification" style="visibility:hidden;">*</span> -->
                        
                        <!-- CONTENEDOR DE LA ALERTA (USUARIO O CONTRASEÑA INCORRECTOS) -->
                        <div id="usuario_incorrecto" class="alert alert-dismissible alert-danger fixed-top" role="alert">
                            <p class=" text-center m-0 p-0">                            
                                <?php echo $mensaje; ?>                                                                
                            </p>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <!-- FIN DEL CONTENEDOR DE LA ALERTA (USUARIO O CONTRASEÑA INCORRECTOS) -->

                        <button class="btn btn-block btn-outline-secondary mt-5" type="submit" name="ctl00$MainContent$LoginButton" onclick="javascript:WebForm_DoPostBackWithOptions(new WebForm_PostBackOptions( & quot; ctl00$MainContent$LoginButton & quot; , & quot; & quot; , true, & quot; LoginUserValidationGroup & quot; , & quot; & quot; , false, false))" id="MainContent_LoginButton">
                            Iniciar Sesión
                            <i class="far fa-sign-in-alt"></i>
                        </button>
                    </div>
                    
                </form>
                <!-- FIN DEL FORMULARIO DE INICIO DE SESION -->
                
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
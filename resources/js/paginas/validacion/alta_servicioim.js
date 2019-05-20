var div = "";
var pagina = "";
var form = "";
var controlador = "";
$(document).ready(function () {
    var prefijo = $("#prefijo").val();

    if (prefijo == "im") {
        controlador = "WEB-INF/Controllers/Validacion/Controler_ServicioIM.php";
        if ($("#independiente").length) {
            controlador = "../WEB-INF/Controllers/Validacion/Controler_ServicioIM.php";
        }
    } else if (prefijo == "fa") {
        controlador = "WEB-INF/Controllers/Validacion/Controler_ServicioFA.php";
        if ($("#independiente").length) {
            controlador = "../WEB-INF/Controllers/Validacion/Controler_ServicioFA.php";
        }
    } else if (prefijo == "gim") {
        controlador = "WEB-INF/Controllers/Validacion/Controler_ServicioGIM.php";
        if ($("#independiente").length) {
            controlador = "../WEB-INF/Controllers/Validacion/Controler_ServicioGIM.php";
        }
    } else if (prefijo == "gfa") {
        controlador = "WEB-INF/Controllers/Validacion/Controler_ServicioGFA.php";
        if ($("#independiente").length) {
            controlador = "../WEB-INF/Controllers/Validacion/Controler_ServicioGFA.php";
        }
    }

    form = "#formServicios" + prefijo.toUpperCase();

    jQuery.validator.addMethod('selectcheck', function (value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
        },
        messages: {
        }
    });

    if ($("#id").length && $("#id").val() != "") {
        cambiarTipoServicio("tipo_servicioIM");
    }

    /*Prevent form*/
    $(form).submit(function (event) {
        if ($(form).valid()) {
            /*Obtenemos los datos para después de que se haga el proceso de los datos*/
            if ($("#div_pagina").length) {
                div = $("#div_pagina").val();
            }

            if ($("#pagina").length) {
                pagina = $("#pagina").val();
            }

            var anexo = "";
            if ($("#claveAnexo").length) {
                anexo = $("#claveAnexo").val();
            }

            var cc = "";
            if ($("#cc").length) {
                cc = $("#cc").val();
            }
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function (data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                    $('#mensajes').html("El servicio se registro correctamente");
                    if (div != "") {
                        if ($("#independiente").length) {
                            cargarDependencia(div, "../cliente/validacion/" + pagina, anexo, null, cc);
                        } else {
                            cargarDependencia(div, "ventas/validacion/" + pagina, anexo, null, cc);
                        }
                    } else {
                        $("#cancelar_servicioim").trigger("click");/*llamamos el evento onclick del boton cancelar para que regrese a la pantalla de lista*/
                    }
                } else {
                    $('#mensajes').html(data);
                }
            });
        }
    });
});

function subimitForm() {
    if ($(form).valid()) {
        /*Obtenemos los datos para después de que se haga el proceso de los datos*/
        if ($("#div_pagina").length) {
            div = $("#div_pagina").val();
        }

        if ($("#pagina").length) {
            pagina = $("#pagina").val();
        }

        var anexo = "";
        if ($("#claveAnexo").length) {
            anexo = $("#claveAnexo").val();
        }

        var cc = "";
        if ($("#cc").length) {
            cc = $("#cc").val();
        }

        /* stop form from submitting normally */
        event.preventDefault();
        /*Serialize and post the form*/
        $.post(controlador, {form: $(form).serialize()}).done(function (data) {

            $('#mensajes').html(data);
            if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                $('#mensajes').html("El servicio se registro correctamente");
                if (div != "") {
                    if ($("#independiente").length) {
                        cargarDependencia(div, "../cliente/validacion/" + pagina, anexo, null, cc);
                    } else {
                        cargarDependencia(div, "ventas/validacion/" + pagina, anexo, null, cc);
                    }
                } else {
                    $("#cancelar_servicioim").trigger("click");/*llamamos el evento onclick del boton cancelar para que regrese a la pantalla de lista*/
                }
            } else {
                $('#mensajes').html(data);
            }
        });
    }
}

function cambiarTipoServicio(servicio) {
    var dir = "WEB-INF/Controllers/Ajax/cargaDivs.php";
    if ($("#independiente").length) {
        dir = "../" + dir;
    }

    var prefijo = $("#prefijo").val();
    $("#contenidos_invisibles").load(dir, {'servicio': $("#" + servicio).val(), 'arrendamiento': true, 'prefijo': prefijo}, function (data) {
        var permisos = data.split(",");
        if (permisos[0] != null && permisos[0] == "1") {//Renta mensual
            if (!$("#id").length || $("#id").val() == "") {
                $(".renta").val("");
            }
            $(".renta").show();
            $("#renta_servicioIM").rules('add', {
                required: true,
                number: true,
                messages: {
                    required: " * Ingresa la renta mensual",
                    number: " * Ingresa solo números"
                }
            });
        } else {
            if (!$("#id").length || $("#id").val() == "") {
                $(".renta").val("");
            }
            $(".renta").hide();
            $("#renta_servicioIM").val("");
            $("#renta_servicioIM").rules("remove");
        }

        var mostrar_lecturas = true;
        if ($("#mostrar_contadores").length && $("#mostrar_contadores").val() == "0") {
            mostrar_lecturas = false;
        }

        if (mostrar_lecturas) {
            if (permisos[1] != null && permisos[1] == "1") {//Incluidos BN
                if (!$("#id").length || $("#id").val() == "") {
                    $(".pibn").val("");
                }
                $(".pibn").show();
                $("#incluidasBN").rules('add', {
                    required: true,
                    number: true,
                    messages: {
                        required: " * Ingresa un valor",
                        number: " * Ingresa solo números"
                    }
                });
            } else {
                if (!$("#id").length || $("#id").val() == "") {
                    $(".pibn").val("");
                }
                $(".pibn").hide();
                $("#incluidasBN").val("");
                $("#incluidasBN").rules("remove");
            }

            if (permisos[2] != null && permisos[2] == "1") {//Incluidos color
                if (!$("#id").length || $("#id").val() == "") {
                    $(".picl").val("");
                }
                $(".picl").show();
                $("#incluidasColor").rules('add', {
                    required: true,
                    number: true,
                    messages: {
                        required: " * Ingresa un valor",
                        number: " * Ingresa solo números"
                    }
                });
            } else {
                if (!$("#id").length || $("#id").val() == "") {
                    $(".picl").val("");
                }
                $(".picl").hide();
                $("#incluidasColor").val("");
                $("#incluidasColor").rules("remove");
            }

            if (permisos[3] != null && permisos[3] == "1") {//Excedentes BN
                if (!$("#id").length || $("#id").val() == "") {
                    $(".pebn").val("");
                }
                $(".pebn").show();
                $("#excedentesBN").rules('add', {
                    required: true,
                    number: true,
                    messages: {
                        required: " * Ingresa un valor",
                        number: " * Ingresa solo números"
                    }
                });
            } else {
                if (!$("#id").length || $("#id").val() == "") {
                    $(".pebn").val("");
                }                
                $(".pebn").hide();
                $("#excedentesBN").val("");
                $("#excedentesBN").rules("remove");
            }

            if (permisos[4] != null && permisos[4] == "1") {//Excedentes color
                if (!$("#id").length || $("#id").val() == "") {
                    $(".pecl").val("");
                }
                $(".pecl").show();
                $("#excedentesColor").rules('add', {
                    required: true,
                    number: true,
                    messages: {
                        required: " * Ingresa un valor"
                    }
                });
            } else {
                if (!$("#id").length || $("#id").val() == "") {
                    $(".pecl").val("");
                }
                $(".pecl").hide();
                $("#excedentesColor").val("");
                $("#excedentesColor").rules("remove");
            }

            if (permisos[5] != null && permisos[5] == "1") {//Excedentes BN
                if (!$("#id").length || $("#id").val() == "") {
                    $(".ppbn").val("");
                }
                $(".ppbn").show();
                $("#procesadasBN").rules('add', {
                    required: true,
                    number: true,
                    messages: {
                        required: " * Ingresa un valor",
                        number: " * Ingresa solo números"
                    }
                });
            } else {
                if (!$("#id").length || $("#id").val() == "") {
                    $(".ppbn").val("");
                }
                $(".ppbn").hide();
                $("#procesadasBN").val("");
                $("#procesadasBN").rules("remove");
            }

            if (permisos[6] != null && permisos[6] == "1") {//Excedentes color
                if (!$("#id").length || $("#id").val() == "") {
                    $(".ppcl").val("");
                }
                $(".ppcl").show();
                $("#procesadasColor").rules('add', {
                    required: true,
                    number: true,
                    messages: {
                        required: " * Ingresa un valor",
                        number: " * Ingresa solo números"
                    }
                });
            } else {
                if (!$("#id").length || $("#id").val() == "") {
                    $(".ppcl").val("");
                }
                $(".ppcl").hide();
                $("#procesadasColor").val("");
                $("#procesadasColor").rules("remove");
            }
        }
    });
}
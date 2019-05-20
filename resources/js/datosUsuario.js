$(document).ready(function() {
    $('.boton').button().css('margin-top', '20px');
    
    $("#cancelar").click(function() {
        $('#titulo').empty();
        $('#mensajes').empty();
        $('#contenidos').empty();
    });
    
    $("#Checkc").change(function() {
        if ($("#Checkc").is(':checked')) {
            $("#Contra").removeAttr("disabled");
            $("#Contras").removeAttr("disabled");
        } else {
            $("#Contra").attr("disabled", true);
            $("#Contras").attr("disabled", true);
        }
    });

    var form = "#formDatosUsuario";
    var controlador = "WEB-INF/Controllers/Controler_DatosUsuario.php";

    jQuery.validator.addMethod('contra', function(value) {
        if ($("#Checkc").is(':checked')) {
            if ($("#Contra").val().length > 1) {
                return true;
            } else {
                return false;
            }
        }
    }, " * Ingresa la contraseña");

    jQuery.validator.addMethod('contras', function(value) {
        if ($("#Checkc").is(':checked')) {
            if ($("#Contras").val() === $("#Contra").val()) {
                return true;
            } else {
                return false;
            }
        }
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            Nombre: {required: true},
            Appat: {required: true},
            Apmat: {required: true},
            Correo: {required: true, email: true},
            Username: {required: true},
            Contra: {contra: true},
            Contras: {contras: true}

        },
        messages: {
            Nombre: {required: " * Ingrese su nombre"},
            Appat: {required: " * Ingrese su apellido paterno"},
            Apmat: {required: " * Ingrese su apellido materno"},
            Correo: {required: " * Ingrese su correo", email: " * Introduce un correo."},
            Username: {required: " * Ingrese su nombre de usuario"},
            Contra: {contra: " * Ingrese su contraseña"},
            Contras: {contras: " * Ingrese su contraseña"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#titulo').empty();        
                    $('#contenidos').empty();
                    finished();
                } else {
                    finished();
                }
            });
        }
    });


});
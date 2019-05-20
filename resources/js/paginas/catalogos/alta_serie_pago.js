$(document).ready(function() {
    var direccion = "catalogos/lista_series_pagos.php";
    var form = "#formSerie";
    var controlador = "WEB-INF/Controllers/Catalogos/Controller_Serie_Pago.php";
    $(".boton").button();/*Estilo de botones*/
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            prefijo: {required: true},
            folioInicio: {required: true}
        },
        messages: {
            prefijo: {required: " * Ingrese el prefijo"},
            folioInicio: {required: true}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    cambiarContenidos(direccion, "Serie");
                }
                finished();
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });

});




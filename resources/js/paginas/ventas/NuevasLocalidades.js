var form = "#lecturas";
var controlador = "WEB-INF/Controllers/Ventas/Controller_NuevasL_Localidades.php";
$(form).validate({
    errorClass: "my-error-class",
    rules: {
    },
    messages: {
    }
});
$(".fecha").mask("9999-99-99");
/*Prevent form*/
$(form).submit(function(event) {
    if ($(form).valid()) {
        /* stop form from submitting normally */
        event.preventDefault();
        /*Serialize and post the form*/
        $.post(controlador, {form: $(form).serialize()}).done(function(data) {
            finished();
            tglecturas(null, null);
            $("#divinfoup").html(data);
        });
        loading("Enviando...");
        $("#divinfo").empty();
    }
});


function validarextra(valor) {
    for (var i = 1; i < valor; i++) {
        $("#fecha" + i).datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true,
            changeMonth: true,
            maxDate: '+0D'
        });
        $("#fecha" + i).rules("add", {
            required: true,
            messages: {
                required: " * Seleccione la fecha"
            }
        });
        if ($("#contadorbn" + i).length > 0) {
            $("#contadorbn" + i).rules("add", {
                required: true,
                number: true,
                messages: {
                    required: " * Ingrese el contador blanco y negro",
                    number: " * Ingrese un número"
                }
            });
        }

        if ($("#contadorcl" + i).length > 0) {
            $("#contadorcl" + i).rules("add", {
                required: true,
                number: true,
                messages: {
                    required: " * Ingrese el contador color",
                    number: " * Ingrese un número"
                }
            });
        }

        if ($("#contadorbnml" + i).length > 0) {
            $("#contadorbnml" + i).rules("add", {
                required: true,
                number: true,
                messages: {
                    required: " * Ingrese el contador blanco y negro",
                    number: " * Ingrese un número"
                }
            });
        }

        if ($("#contadorclml" + i).length > 0) {
            $("#contadorclml" + i).rules("add", {
                required: true,
                number: true,
                messages: {
                    required: " * Ingrese el contador color",
                    number: " * Ingrese un número"
                }
            });
        }

        if ($("#NivelTN" + i).length > 0) {
            $("#NivelTN" + i).rules("add", {
                number: true,
                range: [0, 100],
                messages: {
                    number: " * Ingrese un número",
                    range: " * Ingrese un n\u00famero mayor a 0 y máximo hasta 100"
                }
            });
        }

        if ($("#NivelTC" + i).length > 0) {
            $("#NivelTC" + i).rules("add", {
                number: true,
                range: [0, 100],
                messages: {
                    number: " * Ingrese un número",
                    range: " * Ingrese un n\u00famero mayor a 0 y máximo hasta 100"
                }
            });
        }

        if ($("#NivelTM" + i).length > 0) {
            $("#NivelTM" + i).rules("add", {
                number: true,
                range: [0, 100],
                messages: {
                    number: " * Ingrese un número",
                    range: " * Ingrese un n\u00famero mayor a 0 y máximo hasta 100"
                }
            });
        }
        if ($("#NivelTA" + i).length > 0) {
            $("#NivelTA" + i).rules("add", {
                number: true,
                range: [0, 100],
                messages: {
                    number: " * Ingrese un número",
                    range: " * Ingrese un n\u00famero mayor a 0 y máximo hasta 100"
                }
            });
        }
    }
}

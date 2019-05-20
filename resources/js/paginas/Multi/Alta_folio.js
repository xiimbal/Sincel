var direccion = "Multi/list_empresas.php";
$(document).ready(function () {
    var form = "#formfolio";
    var controlador = "WEB-INF/Controllers/Multi/Controller_Folio.php";
    $(".boton").button();/*Estilo de botones*/
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            RFCemisor: {required: true},
            folioinicial: {required: true},
            foliofinal: {required: true},
            noAprobacion: {required: true},
            anioAprobacion: {required: true},
            ultimoFolio: {required: true}
        },
        messages: {
            RFCemisor: {required: " * Ingrese el RFC"},
            folioInicial: {required: " *  Ingrese el folio inicial"},
            folioFinal: {required: " *  Ingrese el folio final"},
            noAprobacion: {required: " *  Ingrese No aprobacion"},
            anioAprobacion: {required: " *  Ingrese el ano de aprobacion"},
            ultimoFolio: {required: " *  Ingrese el ultimo folio"}
        }
    });

    /*Prevent form*/
    $(form).submit(function (event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function (data) {
                if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                    cambiarContenidos(direccion, "Folio");
                    $('#mensajes').html(data);
                } else {
                    $('#mensajes').html(data);
                }
                finished();
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });
});
$(document).ready(function() {
    var form = "#formParametros";
    var paginaExito = "admin/lista_parametros.php";
    var controlador = "WEB-INF/Controllers/Controler_Parametros.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            descripcion: {required: true, maxlength: 1000, minlength: 4},
            valor: {required: true, number: true}

        },
        messages: {
            descripcion: {required: " * Ingrese el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            valor: {required: "* Ingrese el valor", number: "* Ingresa s\u00f3lo n\u00fameros"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()})
                    .done(function(data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(paginaExito, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
});
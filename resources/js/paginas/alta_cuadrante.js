$(document).ready(function () {
    var form = "#formCuadrante";
    var paginaExito = "catalogos/lista_cuadrante.php";
    var controlador = "WEB-INF/Controllers/Controller_Cuadrante.php";
    $('.boton').button().css('margin-top', '20px');

    $(form).validate({
        rules: {
            descripcion: {required: true, maxlength: 100, minlength: 2},
            Latitud: {required: true, maxlength: 100, minlength: 1},
            Longitud: {required: true, maxlength: 100, minlength: 1}
        },
        messages: {
            descripcion: {required: " * Ingrese un nombre para el cuadrante", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            Latitud: {required: " * Ingrese latitud", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            Longitud: {required: " * Ingrese longitud", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
        }
    });

    /*Prevent form*/
    $(form).submit(function (event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()})
                    .done(function (data) {
                        $('#mensajes').html(data);
                        if (data.toString().indexOf("Error:") === -1) {
                            $('#contenidos').load(paginaExito, function () {
                                finished();
                            });
                        } else {
                            finished();
                        }
                    });
        }
    });
});




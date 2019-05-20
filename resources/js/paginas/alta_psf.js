$(document).ready(function() {
    var form = "#formPSF";
    var paginaExito = "facturacion/lista_periodosinFacturar.php";
    var controlador = "WEB-INF/Controllers/facturacion/Controler_PeriodosSinFacturar.php";
    
    /*validate form*/
    $(form).validate({
        rules: {
            comentario: {required: true, minlength: 3}                       
        },
        messages: {            
            comentario: {required: " * Ingrese el nombre", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
        }
    });
    
    $(".boton").button();

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
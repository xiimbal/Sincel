var paginaExito = "admin/lista_Productos_Genesis.php";
$(document).ready(function() {
    var form = "#formProdGenesis";
    var controlador = "WEB-INF/Controllers/Controller_Producto_Genesis.php";
    $(".boton").button();/*Estilo de botones*/
    /*validate form*/
    $(form).validate({
        rules: {
            nombre: {required: true},
            precio: {required: true}
        },
        messages: {
            nombre: {required: " * Ingresa el nombre"},
            precio: {required: " * Selecciona el precio"}
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
                finished();
                cambiarContenidos(paginaExito, 'Productos ABC');
            });
            loading("Guardando...");
            $("#divinfo").empty();
        }
    });


});
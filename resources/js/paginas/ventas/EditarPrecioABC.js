var direccion = "ventas/lista_precios_abc.php";
$(document).ready(function() {
    var form = "#formprecioabc";
    var controlador = "WEB-INF/Controllers/Ventas/Controller_Editar_Precioabc.php";
    $(".boton").button();/*Estilo de botones*/
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            tipo: {required: true},
            modelo: {required: true},
            precioa: {required: true, number: true},
            preciob: {number: true},
            precioc: {number: true}
        },
        messages: {
            tipo: {required: " * Selecciona el tipo"},
            modelo: {required: " * Selecciona el modelo"},
            precioa: {required: " * Ingresa el precio A", number: " * Ingresa un número"},
            preciob: {number: " * Ingresa un número"},
            precioc: {number: " * Ingresa un número"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                finished();
                cambiarContenidos(direccion, "Precio ABC");
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });
    
    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
});

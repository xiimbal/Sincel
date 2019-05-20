var contador = 2;
var paginaExito = "ventas/Ventas_Directas.php";
$(document).ready(function() {
    $(".boton").button();/*Estilo de botones*/
    $("#Fecha").datepicker({
        dateFormat: 'yy-mm-dd',
        changeYear: true,
        changeMonth: true,
        maxDate: "+0D"
    });
    $(".fecha").mask("9999-99-99");
    /*validate form
    $(form).validate({
        rules: {
            cliente: {required: true},
            localidad: {required: true},
            Fecha: {required: true},
            status: {required: true},
            numero1: {required: true, number: true, min: 1},
            tipo1: {required: true},
            modelo1: {required: true},
            costo1: {required: true},
            costotro1: {required: true, number: true, min: 1}
        },
        messages: {
            cliente: {required: " * Selecciona el cliente"},
            localidad: {required: " * Selecciona la localidad"},
            Fecha: {required: " * Ingresa la fecha"},
            status: {required: " * Ingresa el estatus"},
            numero1: {required: "* Ingresa la cantidad", min: " * Ingresa el un valor mayor a {0}"},
            tipo1: {required: "* Selecciona el tipo"},
            modelo1: {required: "* Selecciona el modelo"},
            costo1: {required: "* Ingresa el costo", number: "* Ingresa un número"},
            costotro1: {required: "* Ingresa el costo", number: "* Ingresa un número", min: " * Ingresa el un valor mayor a {0}"}
        }
    });*/

    /*Prevent form
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally 
            event.preventDefault();
            /*Serialize and post the form
            $.post(controlador, {form: $(form).serialize(), numero: contador}).done(function(data) {
                finished();
                cambiarContenidos(paginaExito, 'Ventas directas');
            });
            loading("Guardando...");
            $("#divinfo").empty();
        }
    });*/

    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1,
        minWidth: 125
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $(".filtro").multiselect('widget').width(125);
    //$("#otrolabel1").hide();
    //$("#otroinput1").hide();
});

var direccion = "ventas/lista_precios_abc.php";
$(document).ready(function() {
    var form = "#formprecioabc";
    var controlador = "WEB-INF/Controllers/Ventas/Controller_Nuevo_Precioabc.php";
    $(".boton").button();/*Estilo de botones*/
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            tipo: {required: true},
            modelo: {required: true},
            noparte: {required: true},
            precioa: {required: true, number: true},
            preciob: {number: true},
            precioc: {number: true}
        },
        messages: {
            tipo: {required: " * Selecciona el tipo"},
            modelo: {required: " * Selecciona el modelo"},
            noparte: {required: " * Selecciona el No de parte"},
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
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(direccion, function() {
                        finished();
                    });
                } else {
                    finished();
                }
                
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

function cargarmodelo(origen, destino) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_modelo_precioabc.php";
    $("#" + destino).load(dir, {id: $("#" + origen).val()}, function(){
        /*Refrescamos las opciones*/
        var x = $('#'+destino).find('option');
        $('#'+destino).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $('#'+destino).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#"+destino).css('width', '250px');
    });
}

function cargarnoparte(origen, anterior, destino) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_noparte_abc.php";
    $("#" + destino).load(dir, {tipo: $("#" + anterior).val(), id: $("#" + origen).val()});
}
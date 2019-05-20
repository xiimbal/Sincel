$(document).ready(function() {
    var form = "#formDevoluciones";
    var paginaExito = "almacen/alta_devoluciones.php";
    var controlador = "WEB-INF/Controllers/Controler_Devoluciones.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");


    /*validate form*/
    $(form).validate({
        rules: {
            almacen: {selectcheck: true},
            parte: {selectcheck: true},
            cantidadExis: {required: true, number: true, min: 1},
            comentario: {required: true},
            ticket_devolucion: {number: true}
        },
        messages: {            
            cantidadExis: {required: " * Ingrese el n\u00famero de existencias ", number: "* Ingresa s\u00f3lo n\u00fameros", min: "* El valor mínimo es {0}"},
            comentario: {required: " * Ingrese el n\u00famero de apartados "},
            ticket_devolucion: {number: " * Ingrese el folio de ticket n\u00famerico"}
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

    $('.boton').button().css('margin-top', '20px');

    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
});

function cargarComponentesDeAlmacen(origen, destino){
    var controlador = "WEB-INF/Controllers/Ajax/CargaSelect.php";     
    loading("Cargando componentes ...");
    $("#"+destino).load(controlador, {'almacen':$("#"+origen).val(), 'inventario':true}, function(data){        
        /*Refrescamos las opciones*/
        var x = $('#' + destino).find('option');
        $('#' + destino).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $('#' + destino).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#" + destino).css('width', '250px');
        finished();
    });
}

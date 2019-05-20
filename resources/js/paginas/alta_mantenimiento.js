var form = "#formMantenimiento";
var paginaExito = "WEB-INF/Controllers/Ventas/Controller_tabla_mtto.php";
var controlador = "WEB-INF/Controllers/Ventas/Controller_Alta_Mtto.php";
$(document).ready(function() {
    $(".boton").button();/*Estilo de botones*/
    $('.fecha').each(function() {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true,
            changeMonth: true
        });
    });
    $(".fecha").mask("9999-99-99");
    
    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");
    
    /*validate form*/
    $(form).validate({
        rules: {
            fechaMtto: {required: true},
            periocidad: {required: true},
            numero: {required: true},
            area: {selectcheck: true}
        },
        messages: {
            fechaMtto: {required: " * Ingresa la fecha del mantenimiento"},
            periocidad: {required: " * Ingresa el tipo de periodos"},
            numero: {required: " * Ingresa el número"},
            area: {selectcheck: " * Selecciona un elemento de la lista"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            $.post(controlador, {form: $(form).serialize(), conf: 0}).done(function(data) {
                $('#tablamtto2').html(data);
                finished();
                $(".boton").button();/*Estilo de botones*/
            });
        }
    });
});

function enviarmtto() {
    loading("Cargando ...");
    /* stop form from submitting normally */
    //event.preventDefault();
    /*Serialize and post the form*/
    $.post(controlador, {form: $(form).serialize(), conf: 1}).done(function(data) {
        $('#divinfo').html(data);
        finished();
    });
    loading("Programando Mantenimientos...");
    $("#divinfo").empty();
}

function cancelarmtto() {
    $("#divinfo").empty();
}
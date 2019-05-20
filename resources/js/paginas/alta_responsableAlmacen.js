$(document).ready(function() {
    var form = "#formresponsableAlmacen";
    var paginaExito = "admin/lista_responsableAlmacen.php";
    var controlador = "WEB-INF/Controllers/Controler_ResponsableAlmacen.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            responsable: {selectcheck: true},
            almacen: {selectcheck: true},
        },
        messages: {
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            event.preventDefault();
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


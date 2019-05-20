$(document).ready(function() {
    var form = "#fromDetalleComponente";
    var paginaExito = $("#paginaLista").val();
    var controlador = "WEB-INF/Controllers/Controler_DetalleComponente.php";
    var div=$("#div").val();

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            componente:{selectcheck:true}
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
                    $('#'+div).load(paginaExito,{"idEquipo":$("#idE").val()}, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
    $('.boton').button().css('margin-top', '20px');
    $("#componente").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
});
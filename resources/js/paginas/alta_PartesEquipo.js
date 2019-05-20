$(document).ready(function() {
    var form = "#formParteEq";
    var paginaExito = "admin/lista_partesEquipo.php"
    var controlador = "WEB-INF/Controllers/Controler_PartesEquipo.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            soportado: {required: true,number:true},
            componenteInst: {selectcheck: true}
        },
        messages: {
            soportado: {required: " *Ingresa el n\u00famero de soportado maximo",number:"Ingresa s\u00f3lo n\u00fameros"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            event.preventDefault();
            $.post(controlador, {form: $(form).serialize()})
                    .done(function(data) {
                $('#mensajeEquipo').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#partesEquipo').load(paginaExito,{"idEquipo":$("#idE").val()}, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
    $('.boton').button().css('margin-top', '20px');
    $("#componenteInst").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
});


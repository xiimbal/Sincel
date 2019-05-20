$(document).ready(function() {
    var form = "#formCompoEq";
    var paginaExito = "admin/lista_componetesEquipo.php"
    var controlador = "WEB-INF/Controllers/Controler_ComponentesEquipo.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            instalado: {required: true,number:true},
            componentes: {selectcheck: true}
        },
        messages: {
            instalado: {required: " * Ingresa el n\u00famero de instalados",number:"Ingresa s\u00f3lo n\u00fameros"}
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
                    $('#ComponentesEquipo').load(paginaExito,{"idEquipo":$("#idE").val()}, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
    
    $('.boton').button().css('margin-top', '20px');
    $("#componentes").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
});


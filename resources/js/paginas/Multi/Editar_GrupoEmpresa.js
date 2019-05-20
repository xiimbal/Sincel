var direccion = "Multi/list_grupo_empresas.php";
$(document).ready(function() {
    var form = "#formcliente";
    var controlador = "WEB-INF/Controllers/Multi/Controller_GrupoEmpresa.php";
    $(".boton").button();/*Estilo de botones*/
    jQuery.validator.addMethod("needsSelection", function(value, element) {
        return $("#empresa").multiselect("getChecked").length > 0;
    }, 'Necesitas seleccionar una empresa.');

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            Descripcion: {required: true},
            empresa: {needsSelection: true}
        },
        messages: {
            Descripcion: {required: " * Ingrese el nombre del grupo"},
            empresa: {needsSelection: " * Seleccione una empresa"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            var x = document.getElementById("empresa");
            var vals = "";
            for (var i = 0; i < x.options.length; i++) {
                if (x.options[i].selected == true) {
                    vals += x.options[i].value + ",";
                }
            }
            vals = vals.substring(0, vals.length - 1);
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize(), empresa: vals}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                    cambiarContenidos(direccion, "Grupo - Empresas");
                    $('#mensajes').html(data);
                } else {
                    $('#mensajes').html(data);
                }
                finished();
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });
    $("#empresa").multiselect({
        multiple: true,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
});
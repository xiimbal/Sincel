$(document).ready(function() {
    var form = "#formequipoComp";
    var paginaExito = "admin/lista_equipoCompatible.php"
    var controlador = "WEB-INF/Controllers/Controler_EquipoCompatible.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            soportado1: {required: true, number: true},
            equipo1: {selectcheck: true}
        },
        messages: {
            soportado1: {required: " * Ingresa el n\u00famero de soportado", numbre: "Ingresa Ingresa s\u00f3lo n\u00fameros"}
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
                    $('#equipoCompatible').load(paginaExito, {"idEquipo": $("#idComponente").val()}, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
    $('.boton').button().css('margin-top', '20px');
    $("#equipo1").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
});
var tamanoEquipo = 2;
function otraEquipo()
{
    var newRow = "<tr><td>Equipo " + tamanoEquipo + "</td><td><select id='equipo" + tamanoEquipo + "' name='equipo" + tamanoEquipo + "' style='width: 170px'>" +
            "</select></td>" +
            "<td>Soportado  " + tamanoEquipo + "</td><td><input type='text' style='width: 80px' id='soportado" + tamanoEquipo + "' name='soportado" + tamanoEquipo + "' /></td></tr>";
    $('#equipoCompatible tr:last').after(newRow);//add the new row
    $('#equipo1 option').clone().appendTo('#equipo' + tamanoEquipo);
    var nombre = "#equipo" + tamanoEquipo;
    $(nombre).rules("add", {
        selectcheck: true,
        messages: {required: " * Seleccione el equipo"}
    });
    nombre = "#soportado" + tamanoEquipo;
    $(nombre).rules("add", {
        number: true,
        required: true,
        messages: {number: " * S\u00f3lo puedes ingresar n\u00fameros", required: "* Ingresa la cantidad"}
    });
    $("#equipo"+tamanoEquipo).multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    tamanoEquipo++;
}
function deleteEquipo()
{
    var trs = $("#equipoCompatible tr").length;
    if (trs > 1) {
        $("#equipoCompatible tr:last").remove();
        tamanoEquipo--;
    }

}


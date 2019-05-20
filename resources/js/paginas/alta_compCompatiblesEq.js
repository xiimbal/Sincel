$(document).ready(function() {
    var form = "#formCompatible";
    var paginaExito = "admin/lista_componentesCompatiblesEq.php"
    var controlador = "WEB-INF/Controllers/Controler_CompCompatiblesEq.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            soportado1: {required: true, number: true},
            componentesComp1: {selectcheck: true}
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
                    $('#ComponentesCompatibles').load(paginaExito, {"idEquipo": $("#idE").val()}, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
    
    $('.boton').button().css('margin-top', '20px');
    $("#componentesComp1").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
});
var tamano = 2;

function nuevoComponente()
{
    var newRow = "<tr><td>Componente " + tamano + "</td><td><select id='componentesComp" + tamano + "' name='componentesComp" + tamano + "' style='width: 170px'>" +
            "</select></td>" +
            "<td>Soportado  "+ tamano +"</td><td><input type='text' style='width: 80px' id='soportado" + tamano + "' name='soportado" + tamano + "' /></td></tr>";
    $('#componenteCompatible tr:last').after(newRow);//add the new row
    $('#componentesComp1 option').clone().appendTo('#componentesComp' + tamano);
    var nombre = "#componentesComp" + tamano;
    $(nombre).rules("add", {
        selectcheck: true,
        messages: {required: " * Seleccione el tipo de recurso"}
    });
    nombre = "#soportado" + tamano;
    $(nombre).rules("add", {
        number: true,
        required: true,
        messages: {number: " * S\u00f3lo puedes ingresar n\u00fameros", required: "* Ingresa la cantidad"}
    });
    $("#componentesComp"+tamano).multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    tamano++;
}
function eliminarComponente()
{
    var trs = $("#componenteCompatible tr").length;
    if (trs > 1) {
        $("#componenteCompatible tr:last").remove();
        tamano--;
    }

}


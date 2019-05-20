$(document).ready(function() {
    var form = "#formAlmacenZona";
    var paginaExito = "admin/lista_almacenZona.php";
    var controlador = "WEB-INF/Controllers/Controler_AlmacenZona.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            zona1: {selectcheck: true},
        },
        messages: {
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()})
                    .done(function(data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(paginaExito, {"id": $("#almacen").val()}, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
});
function verZonas(pagina)
{
    var gzona = $("#gzona").val();
    var almacen = $("#almacen").val();
    var accion = $("#accion").val();
    var zona = $("#zona").val();
    
    loading("Cargando ...");
    $('#contenidos').load(pagina, {"gzona": gzona, "almacen": almacen, "accion": accion,"zona":zona}, function() {
        finished();
    });
}
var numeroNota = 2;
function agregarNota()
{
    var newRow = "<tr><td>Zona " + numeroNota + "</td><td><select id='zona" + numeroNota + "' name='zona" + numeroNota + "' style='width:200px'>" +
            "</select></td></tr>";
    $('#almacenNota tr:last').after(newRow);//add the new row
    $('#zona1 option').clone().appendTo('#zona' + numeroNota);
    var nombre = "#zona" + numeroNota;
    $(nombre).rules("add", {
        selectcheck: true,
        messages: {required: " * Seleccione el tipo de recurso"}
    });
    numeroNota++;
}
function eliminarNota()
{
    var trs = $("#almacenNota tr").length;
    if (trs > 2) {
        $("#almacenNota tr:last").remove();
        numeroNota--;
    }

}
function regresarAlmacen(pagina)
{
    loading("Cargando ...");
    limpiarMensaje();
    $("#contenidos").load(pagina, function() {
        finished();
    });
}
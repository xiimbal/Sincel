var form = "#rfactura";
var controlador = "facturacion/TablaPDF.php";
$(document).ready(function() {
    $(".boton").button();/*Estilo de botones*/
    $("#fecha1").datepicker({
        dateFormat: 'yy-mm-dd'
    });
    $("#fecha2").datepicker({
        dateFormat: 'yy-mm-dd'
    });
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                finished();
                $("#tablainfo").html(data);
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });
});

function cargarclientes(origen, componente) {
    $("#" + componente).load("WEB-INF/Controllers/facturacion/Controller_select_clientes.php", {id: $("#" + origen).val()});
}

var form = "#rfactura";
var controlador = "facturacion/tabla_reporte_facturacion.php";
$(document).ready(function() {
    $(".boton").button();/*Estilo de botones*/
    $("#fecha1").datepicker({
        dateFormat: 'yy-mm-dd',
        maxDate: '+0D'
    });
    $("#fecha2").datepicker({
        dateFormat: 'yy-mm-dd',
        maxDate: '+0D'
    });
    $("#rfccliente").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter();
    $("#RFC").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter();
    $("#cliente").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter();
    $("#status").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter();
    $("#docto").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    });
    $('.fecha').mask("9999-99-99");

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
            loading("Cargando...");
            $("#divinfo").empty();
            $("#tablamensajeinfo").empty();
        }
    });
});

function marcarPagadas(){
    loading("Marcando facturas pagadas ...");
    var pagina = "WEB-INF/Controllers/facturacion/Controller_Pagar_Factura.php";
    $("#tablamensajeinfo").load(pagina,{"marcarPagadas":true}, function(){
        finished();
        $(form).submit();
    });
}

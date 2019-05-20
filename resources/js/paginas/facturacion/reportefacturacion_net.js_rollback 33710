var form = "#rfactura";
var controlador = "facturacion/tabla_reporte_facturacion_net.php";
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
    
    $('.periodo_facturacion').each(function() {
        $(this).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'mm-yy',
            onClose: function() {
                var iMonth = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var iYear = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                $(this).datepicker('setDate', new Date(iYear, iMonth, 1));
            }
        });

    });
    
    $(".filtroselect").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    $(".filtroselectmultiple").multiselect({
        multiple: true,
        noneSelectedText: "Todos los estados",
        selectedList: 3,        
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
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

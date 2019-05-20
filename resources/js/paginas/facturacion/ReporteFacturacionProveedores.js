$(document).ready(function() {
    var form = "#rfacturaProveedor";
    var controlador = "facturacion/tabla_reporte_facturacion_proveedores.php";

    $(".boton").button();/*Estilo de botones*/
    $("#fecha1").datepicker({
        dateFormat: 'yy-mm-dd',
        maxDate: '+0D'
    });
    $("#fecha2").datepicker({
        dateFormat: 'yy-mm-dd',
        maxDate: '+0D'
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
    /*$(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally *
            event.preventDefault();
            /*Serialize and post the form*
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                finished();
                $("#tablainfo").html(data);
            });
            loading("Cargando...");
            $("#tablamensajeinfo").empty();
        }
    });*/
});

function BuscarCxP(){
    var RFCProveedor = $("#RFCProveedor").val(),
    FechaInicio = $("#fecha1").val(),
    FechaFin = $("#fecha2").val(),
    Proveedor = $("#proveedor").val(),
    Estado = $("#status").val(),
    Folio = $("#folio").val();
    loading("Cargando ...");
    var datos = $("#rfacturaProveedor").serialize();
    var controlador = "facturacion/tabla_reporte_facturacion_proveedores.php";    
    $.post(controlador, {form: datos}).done(function(data) {
        finished();
        $("#tablainfo").html(data);
    });
    $("#tablamensajeinfo").empty();
}
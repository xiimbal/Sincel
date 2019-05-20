var form = "#rfactura";
var controlador = "remision/lista_busqueda_remisiones.php";

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


var form = "#rfactura";
var controlador = "remision/lista_busqueda_remisiones_pakal.php";

$(document).ready(function() {    
    
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

    $(form).submit(function(event) {
        if ($(form).valid()) {
            event.preventDefault();
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


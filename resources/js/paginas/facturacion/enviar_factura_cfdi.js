$(document).ready(function() {    
    $(".boton").button();/*Estilo de botones*/        
    
    $("#contactos").multiselect({
        noneSelectedText: "Todos los contactos",
        selectedList: 3,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
});

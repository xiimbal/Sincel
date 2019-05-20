$(document).ready(function() {
   $(".button").button();
   $(".filtroselect").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $(".multiselect").multiselect({
        multiple: true,
        noneSelectedText: "Todas los registros",
        selectedList: 4,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $(".fecha").datepicker({
        dateFormat: 'yy-mm-dd',
        maxDate: '+0D'
    });
    $('.fecha').mask("9999-99-99");
});
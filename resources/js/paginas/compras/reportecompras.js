$(document).ready(function() {
    $(".boton").button();/*Estilo de botones*/
    
    $(".fecha").datepicker({
        dateFormat: 'yy-mm-dd',
        maxDate: '+0D'
    });
    
    $(".multiple").multiselect({
        multiple: true,
        noneSelectedText: "Ningún elemento seleccionado",
        selectedList: 3,
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
                    label: 'Filtro',
                    placeholder: 'Escribe el filtro'
                }).css('max-width','200px');
    
    $('.fecha').mask("9999-99-99");    
});

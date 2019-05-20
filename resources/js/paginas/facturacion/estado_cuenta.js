var form = "#FormEdoCuenta";
$(document).ready(function () {
    jQuery.validator.addMethod('selectcheck', function (value) {
        alert(value);
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            cliente: {selectcheck: true}
        },
        messages: {
            
        }
    });

    $(".button").button();

    
    $('.ui-multiselect').css('width', '150px');
    jQuery(function ($) {
        $.datepicker.regional['es'] = {
            closeText: 'Cerrar',
            prevText: '<Ant',
            nextText: 'Sig>',
            currentText: 'Hoy',
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Juv', 'Vie', 'Sáb'],
            dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
            weekHeader: 'Sm',
            dateFormat: 'dd/mm/yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''};
        $.datepicker.setDefaults($.datepicker.regional['es']);
    });
    
    $(".fecha").mask("9999-99-99");
    $('.fecha').each(function () {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true,
            changeMonth: true,
            maxDate: "+0D"
        });
    });

    /*Prevent form*/
    $(form).submit(function (event) {
        if ($(form).valid()) {
            return true;
        } else {
            return false;
        }
    });    
    
    $(".multiselect").multiselect({
        multiple: true,
        noneSelectedText: "Ningún cliente seleccionado",
        selectedList: 3,        
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
});

function cargarclientes(origen, componente) {
    $("#parametros_lectura").empty();
    $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_clientes.php", {cliente: $("#" + origen).val(), 'modalidad': 'arrendamiento'}, function (data) {        
        refrescarMulti();
    });
}

function refrescarMulti() {
    $(".multiselect").multiselect({
        multiple: true,
        noneSelectedText: "Ningún cliente",
        selectedList: 3,        
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $('.ui-multiselect').css('width', '150px');
}
$(document).ready(function() {        
    jQuery(function($) {
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

    $('.fecha').each(function() {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',            
            changeMonth: true,
            changeYear: true,
            maxDate: '+0D'
        });
    });
    $(".fecha").mask("9999-99-99");
    
    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1,
        minWidth: "150"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });  
    
    $(".filtromultiple").multiselect({
        multiple: true,
        noneSelectedText: "No ha seleccionado",
        selectedList: 3,
        minWidth: "150",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });  
    
    $('.boton').button().css('margin-top', '20px');    
});

function cambiarselectmodelo(origen, destino) {
    dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $('#'+destino).load(dir, { 'tipo': $("#" + origen).val(), 'multiple':true}, function(){            
        /*Refrescamos las opciones*/
        var x = $('#'+destino).find('option');
        $('#'+destino).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $('#'+destino).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#"+destino).css('width', '230px');         
    });    
}

function cargarLocalidadByCliente(destino, origen){
    $("#" + destino).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {cliente: $("#" + origen).val()}, function(){
        /*Refrescamos las opciones*/
        var x = $('#'+destino).find('option');
        $('#'+destino).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $('#'+destino).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#"+destino).css('width', '230px');
    });
}


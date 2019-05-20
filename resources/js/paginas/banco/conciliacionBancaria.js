$(document).ready(function() {
    var form = "formconciliacion";
    $(".boton").button();/*Estilo de botones*/
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            fecha_inicio: {required: true},
            fecha_fin: {required: true}
        },
        messages: {
            fecha_inicio: {required: " * Ingrese la fecha inicial"},
            fecha_fin: {required: " * Ingrese la fecha final"}
        }
    });
    $('.fecha').mask("9999-99");
    $(".select").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $('.ui-multiselect').css('width', '150px');
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
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm',
            onClose: function() {
                var iMonth = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var iYear = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                $(this).datepicker('setDate', new Date(iYear, iMonth, 1));
            }
        });
    });
    
    $("#buscar").click(function(){
        if($("#formconciliacion").valid()){
            var direccion = "WEB-INF/Controllers/Bancos/Controller_tabla_conciliacion.php";
            var concil = 0, desconcil = 0;
            if($("#conciliados").is(':checked')) {  
                concil = 1; 
            }
            if($("#botonC").is(':checked')) {  
                desconcil = 1; 
            }
            $("#divinfo").load(direccion,{fecha_ini: $("#fecha_inicio").val(), fecha_f: $("#fecha_fin").val(), botconciliados: concil, botdesconciliar: desconcil});
            return true;
        }else{
            return false;
        }
    });
    $("#formconciliacion").validate();
});




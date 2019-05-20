var form = "#formReporteImpresiones";
$(document).ready(function() {

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $('.boton').button().css('margin-top', '20px');
    
    $(".multiselect").multiselect({
        multiple: true,
        noneSelectedText: "Todos los registros",
        selectedList: 3,selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
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

    $('.fecha').each(function () {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            maxDate: "+0D",
            changeMonth: true,
            changeYear: true
        });
    });
    $('.fecha').mask("9999-99-99");

    var espanol = {
        "sProcessing": "Procesando...",
        "sLengthMenu": "Mostrar _MENU_ registros",
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ning\u00fan dato disponible en esta tabla",
        "sInfo": "Mostrando de _START_ a _END_ de  _TOTAL_ registros",
        "sInfoEmpty": "Mostrando 0 registros",
        "sInfoFiltered": "(filtrado de _MAX_ registros)",
        "sInfoPostFix": "",
        "sSearch": "Buscar:",
        "sUrl": "",
        "sInfoThousands": ",",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst": "Primero",
            "sLast": "\u00daltimo",
            "sNext": "Siguiente",
            "sPrevious": "Anterior"
        },
        "oAria": {
            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }
    };
    
    /*$("#reporte").click(function(){
        $("#reporte").hide();
        loading("Generando reporte ...");
        if($(form).valid()){
            var direccion = "indicadores/GenerarReporte.php";
            $.post(direccion, {form: $(form).serialize()}).done(function(data) {
                finished();
                $("#reporte").show();
                var w = window.open('about:blank', 'Reporte Impresiones');
                w.document.write(data);
                w.document.close();
            });
            return true;
        }else{
            return false;
        }
    });*/
    
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
    
    $(form).validate();
});



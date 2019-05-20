$(document).ready(function() {
    var form = "#formbiblioteca";
    $(".boton").button();/*Estilo de botones*/
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            palabra: {required: true, minlength: 3}
        },
        messages: {
            palabra: {required: "* Ingrese la palabra a buscar", minlength: " * Escribe m\u00ednimo {3} caracteres"}
        }
    });
    $('#formbiblioteca').validate();
    $('.fecha').mask("9999-99-99");
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
            dateFormat: 'yy-mm-dd',
            onClose: function() {
                var iMonth = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var iYear = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                $(this).datepicker('setDate', new Date(iYear, iMonth, 1));
            }
        });
    });
    
    $("#buscar").click(function(){
        if($("#formbiblioteca").valid()){
            var pal = $("#palabra").val();
            var txt = pal.split(" ");
            $("#Stxt").val(txt[0]);
            loading("Cargando ...");
            var direccion = "WEB-INF/Controllers/mesa/Controller_tabla_busqueda.php";
            $("#divinfo").load(direccion,{fecha_ini: $("#fecha_inicio").val(), fecha_f: $("#fecha_fin").val(), palabra: $("#palabra").val()}, function() {
                $("#busq").show();
                $("#busq2").show();
                finished();
            });
            return true;
        }else{
            return false;
        }
    });
});

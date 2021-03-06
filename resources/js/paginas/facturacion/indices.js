$(document).ready(function(){
    
    $('.date-picker-year').datepicker({
        changeYear: true,
        showButtonPanel: true,
        dateFormat: 'yy',
        onClose: function(dateText, inst) { 
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).datepicker('setDate', new Date(year, 1));
        }
    });
    $(".date-picker-year").focus(function () {
        $(".ui-datepicker-month").hide();
    });
    
    $(".button").button();
});

function recargarIndices(){
    var form = "#formIndicadores";
    var direccion = "facturacion/indices.php"
    loading("Cargando...");
    /*Serialize and post the form*/
    $.post(direccion, {form: $(form).serialize()}).done(function(data) {
        $("#tabs-2").empty();
        $('#tabs-2').html(data);
        finished();
    });
}


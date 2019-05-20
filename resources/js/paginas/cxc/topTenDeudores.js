$(document).ready(function(){
    
    $(".button").button();
});

function recargarDeudores(){
    var form = "#formTopDeudores";
    var direccion = "cxc/topTenDeudores.php"
    loading("Cargando...");
    /*Serialize and post the form*/
    $.post(direccion, {form: $(form).serialize()}).done(function(data) {
        $("#tabs-4").empty();
        $('#tabs-4').html(data);
        finished();
    });
}





$(document).ready(function() {
    var form = "#formSimilar";
    var paginaExito = "admin/lista_SimilaresEquipos.php";
    var controlador = "WEB-INF/Controllers/Controler_SimilaresEquipos.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            equipoSimilar: {required: true}
        },
        messages: {
            
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            event.preventDefault();
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                
                $('#mensajeEquipo').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#EquiposSimiliares').load(paginaExito, {"idEquipo": $("#idE").val()}, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
    
    $('.boton').button().css('margin-top', '20px');
    
});
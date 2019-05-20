$(document).ready(function() {
    var form = "#formAutoPlantilla";
    var paginaExito = "viajes/lista_actualizar_plantilla.php";
    var controlador = "WEB-INF/Controllers/Viajes/Controller_Plantilla.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Seleccione un elemento de la lista");

      /*validate form*/
    $(form).validate({
        rules: {
            CampaniaFiltro: {selectcheck: true},
            TurnoFiltro: {selectcheck: true}

        },
        messages: {
        }
    });


$(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()})
                    .done(function(data) {
                var idCampania = $("#CampaniaFiltro").val();
                var idTurno = $("#TurnoFiltro").val();
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(paginaExito, {"CampaniaFiltro": idCampania, "TurnoFiltro": idTurno, 'mostrar': true}, function() {
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

function cancelar(){
    loading("Cargando ...");
    var paginaExito = "viajes/lista_actualizar_plantilla.php";
    var idCampania = $("#CampaniaFiltro").val();
    var idTurno = $("#TurnoFiltro").val();
    $('#contenidos').load(paginaExito, {"CampaniaFiltro": idCampania, "TurnoFiltro": idTurno, 'mostrar': true}, function() {
        $(".button").button();
        finished();
    });
}
    



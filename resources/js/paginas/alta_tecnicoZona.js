$(document).ready(function() {
    var form = "#formTecnicoZona";
    var paginaExito = "admin/lista_tecnicoZona.php";
    var controlador = "WEB-INF/Controllers/Controler_TecnicoZona.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            usuario: {selectcheck: true},
            gzona: {selectcheck: true},
            zona: {selectcheck: true}

        },
        messages: {
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()})
                    .done(function(data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(paginaExito, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
});
function verListaOpc(pagina)
{
    var usuario = $("#usuario").val();
    var gzona = $("#gzona").val();
    var accion=$("#accion").val();
    var zona=$("#id2").val();
//    alert(usuario + "     >   " + gzona);
    loading("Cargando ...");
    $('#contenidos').load(pagina, {"usuario": usuario, "gzona": gzona,"accion":accion,"zona":zona}, function() {
        finished();
    });
}
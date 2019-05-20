$(document).ready(function() {
    var form = "#formTecnicoCliente";
    var paginaExito = "admin/lista_tecnicoCliente.php";
    var controlador = "WEB-INF/Controllers/Controler_TFSCliente.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            usuario: {selectcheck: true},
            cliente: {selectcheck: true},
            localidad: {selectcheck: true}

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
    
    $('.boton').button().css('margin-top', '20px');
    
    $(".filtro").multiselect({        
        noneSelectedText: "Selecciona el cliente",
        selectedList: 1,
        selectedText: "# seleccionados",        
        multiple: false
    }).multiselectfilter();
});
function verLocalidad(pagina)
{
    var claveCliente = $("#cliente").val();
    var usuario = $("#usuario").val();
    var auxcliente = $("#idCliente").val();
    var auxlocalidad = $("#idLocalidad").val();
    var auxUsuario = $("#idUsuario").val();
    var visible = $("#visible").val();
//    alert(claveCliente+" "+usuario);
    //alert(claveCliente);
    loading("Cargando ...");
    $('#contenidos').load(pagina, {"claveCliente": claveCliente, "usuario": usuario,"auxcliente":auxcliente,"auxlocalidad":auxlocalidad,"auxUsuario":auxUsuario,"visible":visible}, function() {
        finished();
    });
}
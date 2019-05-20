$(document).ready(function() {
    var form = "#formCampania";
    var paginaExito = "catalogos/lista_campania.php";
    var controlador = "WEB-INF/Controllers/Controler_Campania.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Seleccione un elemento de la lista");

      /*validate form*/
    $(form).validate({
        rules: {
            descripcion: {required: true, maxlength: 100, minlength: 1},
            cliente: {selectcheck: true},
            localidad: {selectcheck: true},
            area: {selectcheck: true}

        },
        messages: {
            descripcion: {required: " * Ingrese descripción", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
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
        noneSelectedText: "Selecciona localidad",
        selectedList: 1,
        selectedText: "# seleccionados",        
        multiple: false
    }).multiselectfilter();
});
function verLocalidad(pagina)
{
    var descripcion=$("#descripcion1").val();
    var area=$("#area1").val();
    var claveCliente = $("#cliente").val();
    var auxcliente = $("#idCliente").val();
    var auxlocalidad = $("#idLocalidad").val();
    var visible = $("#visible").val();
    var idCampania= $("#idCampania").val();
//    alert(claveCliente+" "+usuario);
    //alert(claveCliente);
    loading("Cargando ...");
    $('#contenidos').load(pagina, {"idCampania":idCampania,"descripcion1":descripcion,"area1":area,"claveCliente": claveCliente,"auxcliente":auxcliente,"auxlocalidad":auxlocalidad,"visible":visible}, function() {
        finished();
    });
}
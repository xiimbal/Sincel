$(document).ready(function() {
    var form = "#formImprMult";
    var controlador = "WEB-INF/Controllers/Controler_Arrendamiento.php";
    var paginaExito = "admin/lista_Impresorasmultifuncionales.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");

    $.validator.addMethod('filesize', function(value, element, param) {
        return this.optional(element) || (element.files[0].size <= param)
    });

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            // orden: {required: true, maxlength: 10, minlength: 4},
            nombre: {required: true, maxlength: 50, minlength: 4},
            tipo: {required: true, maxlength: 300, minlength: 4}
           
        },
        messages: {
            nombre: {required: " * Ingrese el nombre", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            tipo: {required: " * Ingrese el tipo", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            
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
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#arrendamientos').load(paginaExito, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
    
    $('.boton').button().css('margin-top', '20px');
    
    $(".multiple").multiselect({
        noneSelectedText: "Selecciona el servicio",
        selectedList: 2,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"       
    }).multiselectfilter();        
});


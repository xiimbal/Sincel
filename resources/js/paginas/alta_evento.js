$(document).ready(function() {
    var form = "#formEvento";
    var paginaExito = "admin/lista_evento.php";
    var controlador = "WEB-INF/Controllers/Controler_Evento.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            nombre: {required:true, maxlength:100},
            cliente: {required: true},
            descripcion: {required: true},            
            fecha_inicio: {required: true},
            fecha_fin: {required: true},
            calle: {required:true, maxlength:100},
            no_exterior: {required:true, maxlength:20},
            ciudad: {required:true, maxlength:30},
            estado: {required:true},
            codigo_postal: {required: true, number:true},
            latitud: {number: true, maxlength: 12},
            longitud: {number: true, maxlength: 12}
        },
        messages: {
            nombre: {required:" * Ingresa el nombre", maxlength: " * Ingresa un máximo de {0} caracteres"},
            cliente: {required: " * Selecciona el cliente"},
            descripcion: {required: " * Ingresa la descripción"},            
            fecha_inicio: {required: " * Ingresa la fecha de inicio"},
            fecha_fin: {required: " * Ingresa la fecha fin"},
            calle: {required:" * Ingresa la calle", maxlength:" * Ingresa un máximo de {0} caracteres"},
            no_exterior: {required:" * Ingresa el número exterior", maxlength:" * Ingresa un máximo de {0} caracteres"},
            ciudad: {required:" * Ingresa la ciudad", maxlength:" * Ingresa un máximo de {0} caracteres"},
            estado: {required:" * Ingresa el estado"},
            codigo_postal: {required: " * Ingresa el código postal", number:" * Ingresa solamente números"},
            latitud: {number: " * Ingresa la latitud", maxlength: " * Ingresa un máximo de {0} caracteres"},
            longitud: {number: " * Ingresa la longitud", maxlength: " * Ingresa un máximo de {0} caracteres"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Procesando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            var inputs = $("input[type=file]"),
                    files = [];
            for (var i = 0; i < inputs.length; i++) {
                files.push(inputs.eq(i).prop("files")[0]);
            }

            var formData = new FormData();
            $.each(files, function(key, value)
            {
                formData.append(key, value);
            });
            formData.append('form', $(form).serialize());
            $.ajax({
                url: controlador,
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data, textStatus, jqXHR)
                {
                    if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                        cambiarContenidos(paginaExito);
                        $('#mensajes').html(data);
                    } else {
                        $('#mensajes').html(data);
                    }
                    finished();
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                },
                complete: function()
                {
                }
            });            
        }
    });
    
    $('.fecha').each(function() {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true,
            changeMonth: true
        });        
    });
    
    $('.boton').button().css('margin-top', '20px');
    
    $(".multiselect").multiselect({
        multiple: true,
        noneSelectedText: "Todos los registros",
        selectedList: 3,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    $(".uniselect").multiselect({
        multiple: false,
        noneSelectedText: "Todos los registros",
        selectedList: 3,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    $('#logo').fileValidator({
        onValidation: function(files) {            
            $(".error_file").text("");
            $(this).attr('class', '');
        },
        onInvalid: function(type, file) {                    
            $(".error_file").text("Debes de elegir una imagen menor de 200kb");
            var control = $("#logo");
            control.replaceWith( control = control.clone( true ) );            
            $(this).addClass('invalid ' + type);
            return false;
        },
        maxSize: '200kb',
        type: 'image'
    });        
});
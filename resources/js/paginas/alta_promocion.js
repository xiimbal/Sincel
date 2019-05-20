$(document).ready(function() {
    var form = "#formPromocion";
    var paginaExito = "admin/lista_promocion.php";
    var controlador = "WEB-INF/Controllers/Controler_Promocion.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            titulo: {required:true},
            localidad: {required: true},
            contacto: {required: true},
            cliente: {required: true},
            vigencia_inicio: {required: true},
            vigencia_fin: {required: true}
        },
        messages: {
            titulo: {required: " * Ingresa el título"},
            localidad: {required: " * Ingresa la localidad"},
            contacto: {required: " * Selecciona el contacto"},
            cliente: {required: " * Selecciona el negocio"},
            vigencia_inicio: {required: " * Ingresa el inicio de la vigencia"},
            vigencia_fin: {required: " * Ingresa el fin de la vigencia"}    
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
            $(".error_file").text("Debes de elegir una imagen menor de 17kb (200x200)");
            var control = $("#logo");
            control.replaceWith( control = control.clone( true ) );            
            $(this).addClass('invalid ' + type);
            return false;
        },
        maxSize: '17kb',
        type: 'image'
    });        
});

function cargarNegociosDeUsuario(origen, destino){
    var loc = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $('#' + destino).load(loc, {'usuario': $("#" + origen).val(), 'negocios_propios':true}, function() {
        /*Refrescamos las opciones*/
        var x = $('#' + destino).find('option');
        $('#' + destino).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $('#' + destino).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#" + destino).css('width', '250px');
    });
}
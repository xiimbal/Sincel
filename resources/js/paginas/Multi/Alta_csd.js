$(document).ready(function() {
    var form = "#formcsd";
    var controlador = "WEB-INF/Controllers/Multi/Controller_Alta_csd.php";
    $(".boton").button();/*Estilo de botones*/
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            nombre: {required: true},
            //Unidad: {required: true},
            pass: {required: true},
            csd: {required: true, accept: "cer"},
            key: {required: true, accept: "key"},
            pem: {required: true, accept: "pem"},
            certificado: {required: true},
            nosat: {required: true}
        },
        messages: {
            nombre: {required: " * Ingrese el nombre"},
            pass: {required: " * Ingrese la contraseña"},
            csd: {required: " * Seleccione el csd", accept: " * Archivo invalido debe seleccionar el csd"},
            key: {required: " * Seleccione el key", accept: " * Archivo invalido debe seleccionar el key"},
            pem: {required: " * Seleccione el pem", accept: " * Archivo invalido debe seleccionar el pem"},
            certificado: {required: " * Ingrese el número de certificado"},
            nosat: {required: " * Ingrese el número de SAT"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
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
            formData.append('pass', $("#pass").val());
            formData.append('nombre', $("#nombre").val());
            formData.append('certificado', $("#certificado").val());
            formData.append('nosat', $("#nosat").val());
            if ($("#id").length) {
                formData.append('id', $("#id").val());
            }
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
                        cambiarContenidos('Multi/lista_cfdi_archivos.php', 'CFDI');
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
});

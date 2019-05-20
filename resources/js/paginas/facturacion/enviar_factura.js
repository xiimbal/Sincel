$(document).ready(function() {
    var form = "#formeniar";
    var controlador = "WEB-INF/Controllers/facturacion/Controller_enviar_Factura.php";
    $(".boton").button();/*Estilo de botones*/
    var espanol = {
        "sProcessing": "Procesando...",
        "sLengthMenu": "Mostrar _MENU_ registros",
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ning\u00fan dato disponible en esta tabla",
        "sInfo": "Mostrando de _START_ a _END_ de  _TOTAL_ registros",
        "sInfoEmpty": "Mostrando 0 registros",
        "sInfoFiltered": "(filtrado de _MAX_ registros)",
        "sInfoPostFix": "",
        "sSearch": "Buscar:",
        "sUrl": "",
        "sInfoThousands": ",",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst": "Primero",
            "sLast": "\u00daltimo",
            "sNext": "Siguiente",
            "sPrevious": "Anterior"
        },
        "oAria": {
            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }
    };
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            correo1: {required: true,email:true},
            correo2: {email:true},
            correo3: {email:true},
            correo4: {email:true},
            comentario: {required: true}
        },
        messages: {
            correo1: {required: " * Ingrese el email",email: " * Ingrese un email válido"},
            correo2: {email: " * Ingrese un email válido"},
            correo3: {email: " * Ingrese un email válido"},
            correo4: {email: " * Ingrese un email válido"},
            comentario: {required: " * Ingrese un comentario"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            loading("Enviando");
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {
                    cambiarContenidos('facturacion/ReporteFacturacion.php', 'Facturas CFDI')
                }
            });
        }
    });
});

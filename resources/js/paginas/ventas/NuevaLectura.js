$(document).ready(function() {
    var form = "#formNuevaLectura";
    var controlador = "WEB-INF/Controllers/Ventas/Controller_Nueva_Lectura.php";
    //$("#fecha").datetimepicker();
    $("#fecha").datepicker({
        dateFormat: 'yy-mm-dd',
        changeYear: true,
        changeMonth: true,
        maxDate: '+0D'
    });
    $(".fecha").mask("9999-99-99");
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
            fecha: {required: true},
            hora: {required: true},
            contadorbn: {required: true, number: true, range: [0, 1000000]},
            contadorcl: {required: true, number: true, range: [0, 1000000]},
            contadorbnml: {required: true, number: true, range: [0, 1000000]},
            contadorclml: {required: true, number: true, range: [0, 1000000]},
            NivelTN: {number: true, range: [0, 100]},
            NivelTC: {number: true, range: [0, 100]},
            NivelTM: {number: true, range: [0, 100]},
            NivelTA: {number: true, range: [0, 100]}
        },
        messages: {
            fecha: {required: " * Ingrese la fecha"},
            hora: {required: " * Ingrese la hora"},
            contadorbn: {required: " * Ingrese el n\u00famero el contador B/N", number: " * Ingresa s\u00f3lo n\u00fameros", range: "* Ingrese un n\u00famero mayor a 0"},
            contadorcl: {required: " * Ingrese el n\u00famero el contador de color", number: " * Ingresa s\u00f3lo n\u00fameros", range: "* Ingrese un n\u00famero mayor a 0"},
            contadorbnml: {required: " * Ingrese el n\u00famero el contador B/N", number: " * Ingresa s\u00f3lo n\u00fameros", range: "* Ingrese un n\u00famero mayor a 0"},
            contadorclml: {required: " * Ingrese el n\u00famero el contador de color", number: " * Ingresa s\u00f3lo n\u00fameros", range: "* Ingrese un n\u00famero mayor a 0"},
            NivelTN: {number: " * Ingresa s\u00f3lo n\u00fameros", range: "* Ingrese un n\u00famero mayor a 0 y menor a 100"},
            NivelTC: {number: " * Ingresa s\u00f3lo n\u00fameros", range: "* Ingrese un n\u00famero mayor a 0 y menor a 100"},
            NivelTM: {number: " * Ingresa s\u00f3lo n\u00fameros", range: "* Ingrese un n\u00famero mayor a 0 y menor a 100"},
            NivelTA: {number: " * Ingresa s\u00f3lo n\u00fameros", range: "* Ingrese un n\u00famero mayor a 0 y menor a 100"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                var direccion = "WEB-INF/Controllers/Ventas/Controller_tabla_lecturas.php";
                $("#divinfo").load(direccion, {id: $("#nserie").val()}, function() {
                    finished();
                    oTable = $('#tinfo').dataTable({
                        "bJQueryUI": true,
                        "bRetrieve": true,
                        "bDestroy": true,
                        "oLanguage": espanol,
                        "sPaginationType": "full_numbers",
                        "bDeferRender": true,
                        "iDisplayLength": 25
                    });
                });
                $('#divinfo').html(data);
                loading("Enviando...");
                $("#divinfo").empty();
            });
        }
    });
});
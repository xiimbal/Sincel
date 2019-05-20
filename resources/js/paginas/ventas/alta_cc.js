$(document).ready(function() {
    var form = "#form_cc";
    var controlador = "../WEB-INF/Controllers/Ventas/Controller_CC.php";
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
    $.validator.addMethod("needsSelection", function() {
        if ($("#localidades").multiselect("getChecked").length > 0) {
            return true;
        } else {
            return false;
        }
    });
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            nombre: {required: true},
            localidades: {needsSelection: true}
        },
        messages: {
            nombre: {required: " * Ingrese el nombre"},
            localidades: {needsSelection: " * Seleccione al menos una localidad"}
        }
    });
    $("#localidades").multiselect({
        multiple: true,
        noneSelectedText: "No ha seleccionado",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo",
        selectedList: 3,
        selectedText: "# seleccionados",
        minWidth: 125
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                cambiarContenidos('tabla_cc.php?id=' + $("#clavecliente").val(),"Centro Costo");
            });
        }
    });
});
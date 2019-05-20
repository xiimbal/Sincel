$(function() {
    jQuery(function($) {
        $.datepicker.regional['es'] = {
            closeText: 'Cerrar',
            prevText: '<Ant',
            nextText: 'Sig>',
            currentText: 'Hoy',
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            dayNames: ['Domingo', 'Lunes', 'Martes', 'Mi\u00e9rcoles', 'Jueves', 'Viernes', 'S\u00e1bado'],
            dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mi\u00e9', 'Juv', 'Vie', 'S\u00e1b'],
            dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'S\u00e1'],
            weekHeader: 'Sm',
            dateFormat: 'dd/mm/yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''};
        $.datepicker.setDefaults($.datepicker.regional['es']);
    });
});

$(document).ready(function() {
    $('.boton').button().css('margin-top', '20px').css('font-size', '13px');

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

    oTable = $('.filtro').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 10,
        "bLengthChange": false
    });
    
    oTable = $('.filtro100').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 100,
        "bLengthChange": false
    });

    $('.fecha').each(function() {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true,
            changeMonth: true
        });
    });
});

function actualizarTicket(idTicket, idEquipo, tipo) {
    loading("Actualizando ...");
    $("#mensajes").load("WEB-INF/Controllers/Validacion/Controler_Actualizador.php",
            {"tipo": tipo, "idTicket": idTicket, "idComponente": idEquipo}, function() {
        finished();
    });
}

function buscarEquipo() {
    var modelo = $("#modelo1").val();
    modelo = modelo.replace(/\s/g, "__XX__");
    cambiarContenidoValidaciones('equipo2', 'ventas/validacion/lista_equipo.php?NoSerie=' + $('#no_serie1').val() + '&Modelo=' + modelo + '', $('#idTicket').val(), null, false);
}

function buscarCliente(data) {
    if ($('#empresa1').length) {
        var empresa = $('#empresa1').val();
        empresa = empresa.replace(/\s/g, "__XX__");
        var centro = $("#localidad1").val();
        centro = centro.replace(/\s/g, "__XX__");
        cambiarContenidoValidaciones('cliente2', 'ventas/validacion/lista_cliente.php?Nombre=' + empresa + '&Clave=' + $('#cliente_n1').val() + '&NombreCentro=' + centro + '&ClaveCentro=' + $('#clave_localidad1').val() + '', $('#idTicket').val(), null, false);
    } else {
        if ($("#idTicket").length) {
            cambiarContenidoValidaciones('cliente2', 'ventas/validacion/lista_cliente.php?Nombre=&Clave=' + data + '&NombreCentro=&ClaveCentro=', $('#idTicket').val(), null, false);
        } else {
            cambiarContenidoValidaciones('cliente2', 'validacion/lista_cliente.php?Nombre=&Clave=' + data + '&NombreCentro=&ClaveCentro=', null, null, false);
        }
    }
}

function validarTicket(idTicket, NoSerie) {
    $("#mensaje_validar").load("WEB-INF/Controllers/Validacion/Controler_Valida.php", {"idTicket": idTicket, "NoSerie": NoSerie}, function(data) {
        if (data.toString().indexOf("Error:") === -1) {
            setTimeout(function() {
                cambiarContenidos("ventas/lista_Validacion.php", 'Validación');
            }, 3000);
        }
    });
}

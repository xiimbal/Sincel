var oTable;
var form = "#rfactura";
var currentPage = 0;
var controlador = "remision/lista_busqueda_remisiones.php";
$(document).ready(function() {
    $.fn.dataTableExt.oApi.fnPagingInfo = function(oSettings)
    {
        return {
            "iStart": oSettings._iDisplayStart,
            "iEnd": oSettings.fnDisplayEnd(),
            "iLength": oSettings._iDisplayLength,
            "iTotal": oSettings.fnRecordsTotal(),
            "iFilteredTotal": oSettings.fnRecordsDisplay(),
            "iPage": oSettings._iDisplayLength === -1 ?
                    0 : Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength),
            "iTotalPages": oSettings._iDisplayLength === -1 ?
                    0 : Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength)
        };
    };
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
    $(".boton").button();/*Estilo de botones*/
    oTable = $('#treportfact').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 100,
        "aaSorting": [[1, "desc"]],
        "fnDrawCallback": function() {
            currentPage = this.fnPagingInfo().iPage;
        }
    });
    
    if($("#total_facturas").length && $("#resumen_por_pagar").length){        
        $("#resumen_por_pagar").html(
                "<br/><table style='margin-left: 7%;'>"+
                "<tr><td>Número de facturas: </td><td style='text-align: right;'><b>"+$("#total_facturas").val()+"</b></td></tr>"+
                "<tr><td>Pagado: </td>            <td style='text-align: right;'><b>"+$("#pagado_facturas").val()+"</b></td></tr>"+
                "<tr><td>Por pagar: </td>         <td style='text-align: right;'><b>"+$("#por_pagar_facturas").val()+"</b></td></tr>"+
                "<tr><td>Total: </td>             <td style='text-align: right;'><b>"+$("#total_costo_factura").val()+"</b></td></tr>"+
                "</table>");
    }
});

function PagarFactura(folio, tipo) {
    var estado = "pagado";
    if (tipo === "0") {
        estado = "no pagado";
    }

    if (confirm("¿Esta seguro que desea cambiar a " + estado + " el folio " + folio + "?")) {
        loading("Actualizando y cargando ...");
        $("#tablainfo").hide();
        $.post("WEB-INF/Controllers/facturacion/Controller_Pagar_Factura.php", {folio: folio, tipo: tipo}, function(data) {
            var page = controlador;
            if (currentPage !== 0) {
                page = controlador + "?page=" + currentPage;
            }
            //alert(data);
            $.post(page, {form: $(form).serialize()}).done(function(data) {
                $("#tablainfo").show();
                finished();
                $("#tablainfo").html(data);
                limpiarMensaje();
            });
        });
    }
}

function generarnota(info) {
    $("#tablainfo").load(info);
}

function ponerpagina(pagina) {
    oTable.fnPageChange(pagina);
}

function cancelarfactura(dir, factura) {
    if (confirm("¿Esta seguro que desea cancelar la factura No " + factura + "?")) {
        loading("Procesando ...");
        $.post(dir, function(data) {
            $("#tablamensajeinfo").html(data);
            var page = controlador;
            if (currentPage !== 0) {
                page = controlador + "?page=" + currentPage;
            }
            loading("Cargando ...");
            
            $.post(page, {form: $(form).serialize()}).done(function(data) {
                finished();
                $("#tablainfo").html(data);
                limpiarMensaje();
            });
        });
    }
}

function generarFacturaLectura(factura) {
    if (confirm("¿Esta seguro que desea copiar y generar la Pre-factura?")) {
        loading("Procesando ...");
        $.post("WEB-INF/Controllers/facturacion/Controller_Convert_Factura.php", {id: factura}, function(data) {
            $("#tablamensajeinfo").html(data);
            finished();
        });
    }
}
var oTable;
var form = "#rfactura";
var currentPage = 0;
var controlador = "facturacion/tabla_reporte_facturacion.php";
$(document).ready(function () {
    $("#dialog").hide();
    $.fn.dataTableExt.oApi.fnPagingInfo = function (oSettings)
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
        "iDisplayLength": 25,
        "aaSorting": [[0, "desc"]],
        "fnDrawCallback": function () {
            currentPage = this.fnPagingInfo().iPage;
        }
    });
});

function PagarFactura(folio, tipo, id) {
    var estado = "pagado";
    if (tipo === "0") {
        estado = "no pagado";
    }
    if (confirm("¿Esta seguro que desea cambiar a " + estado + " el folio " + id + "?")) {
        loading("Actualizando y cargando ...");
        $("#tablainfo").hide();
        $.post("WEB-INF/Controllers/facturacion/Controller_Pagar_Factura.php", {folio: folio, tipo: tipo}, function (data) {
            var page = controlador;
            if (currentPage !== 0) {
                page = controlador + "?page=" + currentPage;
            }
            $.post(page, {form: $(form).serialize()}).done(function (data) {
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

function cancelarfactura(dir, factura, id) {
    loading("Procesando ...");
    if (confirm("¿Esta seguro que desea cancelar la factura No " + id + "?")) {
        $.post(dir, function (data) {
            $("#tablamensajeinfo").html(data);
            var page = controlador;
            if (currentPage !== 0) {
                page = controlador + "?page=" + currentPage;
            }
            loading("Cargando ...");
            $.post(page, {form: $(form).serialize()}).done(function (data) {
                finished();
                $("#tablainfo").html(data);
                limpiarMensaje();
            });
        });
    }
}

function Eliminarfactura(dir, factura, id) {
    loading("Procesando ...");
    if (confirm("¿Esta seguro que desea eliminar la pre-factura No " + id + "?")) {
        $.post(dir + "?id=" + factura, function (data) {
            if ($("#same_page").length) {
                cambiarContenidos($("#same_page").val());
            } else {
                recargar();
            }
        });
    }
}

function modificarFactura(id) {
    cambiarContenidos("facturacion/alta_factura.php?id=" + id, "Facturación");
}

var id_gral;
var folio_gral;
var dialog;
function GenerarFactura(id, folio) {
    $("#text_factura").css('width', 565);
    $("#text_factura").css('height', 365);
    id_gral = id;
    folio_gral = folio;
    var leyenda = $("#text_factura").val();
    //alert(leyenda);
    $("#dialog").dialog({width: 600,
        height: 500,
        buttons: {
            "Aceptar": function () {
                if (confirm("¿Esta seguro que desea generar la factura No " + folio_gral + "?")) {
                    loading("Procesando ...");                    
                    $.post("WEB-INF/Controllers/facturacion/Controller_Generar_Factura.php?id=" + id_gral, {'leyenda': leyenda}, function (data) {
                        $("#tablamensajeinfo").html(data);
                        var page = controlador;                        
                        /*$.post(page, {form: $(form).serialize()}).done(function (data) {                            
                            $("#tablamensajeinfo").show();
                            finished();
                            $("#tablainfo").html(data);
                            limpiarMensaje();
                        });*/
                        $("#contenidos").load(controlador, {form: $(form).serialize()}, function(){
                            finished();
                        });
                    });
                    $(this).dialog("close");
                }
            },
            Cancelar: function () {
                $(this).dialog("close");
            }
        }
    });
}

function recargar() {
    var page = controlador;
    $.post(page, {form: $(form).serialize()}).done(function (data) {
        $("#tablamensajeinfo").show();
        finished();
        $("#tablainfo").html(data);
        limpiarMensaje();
    });
}
function generar_factura_final() {
    /*alert("HOL");
     $("#dialog").dialog("close");*/
    /*if (confirm("¿Esta seguro que desea generar la factura No " + folio_gral + "?")) {
     loading("Procesando ...");
     $.post("WEB-INF/Controllers/facturacion/Controller_Generar_Factura.php?id=" + id_gral, {leyenda: $("#text_factura").val()}, function(data) {
     $("#tablamensajeinfo").html(data);
     var page = controlador;
     $.post(page, {form: $(form).serialize()}).done(function(data) {
     $("#tablamensajeinfo").show();
     finished();
     $("#tablainfo").html(data);
     limpiarMensaje();
     });
     });
     }*/
}

function CopiaFactura(id, folio) {
    if (confirm("¿Esta seguro que desea copiar la factura No " + folio + "?")) {
        loading("Procesando ...");
        $.post("WEB-INF/Controllers/facturacion/Controller_Copiar_Factura.php?id=" + id, function (data) {
            if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                var res = data.split(",");
                alert("La factura " + res[1] + " se acaba de crear exitosamente a continuación se detallará.");
                cambiarContenidos("facturacion/alta_factura.php?id=" + res[0], "Facturación");
            } else {
                $('#mensajes').html(data);
            }
            finished();
        });
    }
}

function EnviarFactura(id, folio) {
    if (confirm("¿Esta seguro que desea enviar la factura No " + folio + "?")) {
        cambiarContenidos("facturacion/enviar_factura.php?id=" + id, "Enviar Factura");
    }
}
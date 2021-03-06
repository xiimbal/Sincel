var oTable;
var form = "#rfactura";
var currentPage = 0,  clickSeleccionarTodos = 0;
var controlador = "facturacion/tabla_reporte_facturacion_net.php";
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
    })
    oTable = $('#treportfact1').dataTable({
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
    })
    oTable = $('#treportfact2').dataTable({
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
    })
    oTable = $('#treportfact3').dataTable({
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
    })
    oTable = $('#treportfact4').dataTable({
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
    
    if($("#total_facturas").length /*&& $("#resumen_por_pagar").length*/){        
        /*$("#resumen_por_pagar").html(
                "<br/><table style='width:50%;''>"+
                "<tr><td>Saldos</td>"+
                "<tbody><tr><td>Número de facturas: </td><td style='text-align: right;'><b>"+$("#total_facturas").val()+"</b></td></tr>"+
                "<tr><td>Pagos Parciales: </td>            <td style='text-align: right;'><b>"+$("#pagado_facturas").val()+"</b></td></tr>"+
                "<tr><td>Por pagar: </td>         <td style='text-align: right;'><b>"+$("#por_pagar_facturas").val()+"</b></td></tr>"+
                "<tr><td>Total: </td>             <td style='text-align: right;'><b>"+$("#total_costo_factura").val()+"</b></td></tr>"+
                "</tbody> </table>");
        $("#resumen_prueba").html(
                "<br/><table style='width:50%;'>"+
                "<tr><td>Saldos</td><td>Vencidos</td>"+
                "<tr><td>Número de facturas: </td><td style='text-align: right;'><b>"+$("#total_facturas").val()+"</b></td></tr>"+
                "<tr><td>Pagos Parciales: </td>            <td style='text-align: right;'><b>"+$("#pagado_facturas").val()+"</b></td></tr>"+
                "<tr><td>Por pagar: </td>         <td style='text-align: right;'><b>"+$("#por_pagar_facturas").val()+"</b></td></tr>"+
                "<tr><td>Total: </td>             <td style='text-align: right;'><b>"+$("#total_costo_factura").val()+"</b></td></tr>"+
                "</table>");*/
        $("#num").html(
                "<b>"+$("#total_facturas").val()+"</b>"
                );
        $("#num_1").html(
                "<b>"+$("#total_facturas_vencidas").val()+"</b>"
                );
        $("#num_2").html(
                "<b>"+$("#total_facturas_vencidas_30").val()+"</b>"
                );
        $("#num_3").html(
                "<b>"+$("#total_facturas_vencidas_60").val()+"</b>"
                );
        $("#num_4").html(
                "<b>"+$("#total_facturas_vencidas_90").val()+"</b>"
                );
        $("#Saldo").html(
                "<table style='width:100%; border-collapse: collapse;' align='right'>"+
                "<tr>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Pagos Parciales: "+$("#pagado").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Por Pagar: "+$("#saldo").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Saldos: "+$("#total").val()+"</FONT></td>"+
                "<tr>"+
                "</table>"+
                "&#160;"+
                "<p></p>"
                );
        $("#Saldo_1").html(
                "&#160;"+
                "<p></p>"+
                "<table style='width:100%; border-collapse: collapse;' align='right'>"+
                "<tr>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Pagos Parciales: "+$("#pagado").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Por Pagar: "+$("#saldo").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Saldos: "+$("#total").val()+"</FONT></td>"+
                "<tr>"+
                "</table>"+
                "&#160;"+
                "<p></p>"
                );
        $("#Saldo1").html(
                "<table style='width:100%; border-collapse: collapse;' align='right'>"+
                "<tr>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Pagos Parciales: "+$("#pagado1").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Por Pagar: "+$("#saldo1").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Saldos: "+$("#total1").val()+"</FONT></td>"+
                "<tr>"+
                "</table>"+
                "&#160;"+
                "<p></p>"
                );
        $("#Saldo1_1").html(
                "&#160;"+
                "<p></p>"+
                "<table style='width:100%; border-collapse: collapse;' align='right'>"+
                "<tr>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Pagos Parciales: "+$("#pagado1").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Por Pagar: "+$("#saldo1").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Saldos: "+$("#total1").val()+"</FONT></td>"+
                "<tr>"+
                "</table>"+
                "&#160;"+
                "<p></p>"
                );
        $("#Saldo2").html(
                "<table style='width:100%; border-collapse: collapse;' align='right'>"+
                "<tr>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Pagos Parciales: "+$("#pagado2").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Por Pagar: "+$("#saldo2").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Saldos: "+$("#total2").val()+"</FONT></td>"+
                "<tr>"+
                "</table>"+
                "&#160;"+
                "<p></p>"
                );
        $("#Saldo2_1").html(
                "&#160;"+
                "<p></p>"+
                "<table style='width:100%; border-collapse: collapse;' align='right'>"+
                "<tr>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Pagos Parciales: "+$("#pagado2").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Por Pagar: "+$("#saldo2").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Saldos: "+$("#total2").val()+"</FONT></td>"+
                "<tr>"+
                "</table>"+
                "&#160;"+
                "<p></p>"
                );
        $("#Saldo3").html(
                "<table style='width:100%; border-collapse: collapse;' align='right'>"+
                "<tr>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Pagos Parciales: "+$("#pagado3").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Por Pagar: "+$("#saldo3").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Saldos: "+$("#total3").val()+"</FONT></td>"+
                "<tr>"+
                "</table>"+
                "&#160;"+
                "<p></p>"
                );
        $("#Saldo3_1").html(
                "&#160;"+
                "<p></p>"+
                "<table style='width:100%; border-collapse: collapse;' align='right'>"+
                "<tr>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Pagos Parciales: "+$("#pagado3").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Por Pagar: "+$("#saldo3").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Saldos: "+$("#total3").val()+"</FONT></td>"+
                "<tr>"+
                "</table>"+
                "&#160;"+
                "<p></p>"
                );
        $("#Saldo4").html(
                "<table style='width:100%; border-collapse: collapse;' align='right'>"+
                "<tr>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Pagos Parciales: "+$("#pagado4").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Por Pagar: "+$("#saldo4").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Saldos: "+$("#total4").val()+"</FONT></td>"+
                "<tr>"+
                "</table>"+
                "&#160;"+
                "<p></p>"
                );
        $("#Saldo4_1").html(
                "&#160;"+
                "<p></p>"+
                "<table style='width:100%; border-collapse: collapse;' align='right'>"+
                "<tr>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Pagos Parciales: "+$("#pagado4").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Por Pagar: "+$("#saldo4").val()+"</FONT></td>"+
                "<td><FONT size=4 width=\"33%\" align=\"center\" scope=\"col\">Saldos: "+$("#total4").val()+"</FONT></td>"+
                "<tr>"+
                "</table>"+
                "&#160;"+
                "<p></p>"
                );
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

function marcarTodos() {
    var numeroCheck = $("#numDocumentos").val();//  Numero de documentos    
    if (numeroCheck < 1){//    Si ay más de un documento
        if ($('#marcarTodo').is(':checked')) { //   Cuando esta seleccionado
            for (i = 0; i < numeroCheck; i++) {
                $('.check_' + i).prop('checked', true); //  Todos los Checkbox se seleccionan
            }
        } else {
            for (i = 0; i < numeroCheck; i++) {
                $('.check_' + i).prop('checked', false); //  Todos los Checkbox se desactivan
            }
        }
    } else{  //     Si no existe ningun documento
        $('#marcarTodo').prop('checked', false); // Se desactiva el checkbox del Label
        if (clickSeleccionarTodos == 1 || clickSeleccionarTodos == 3 || clickSeleccionarTodos == 6){//s es presionado 2, 4 o 6 veces el label Seleccionar Todos
            //mostrarMensaje("No existe ningun archivo en este directorio");
            alert("No hay");
        }
        clickSeleccionarTodos ++;// numero de veces que se pulsa el Label Seleccionar todos
    }       
}

function marcarTodos2(num) {
    var cantidad = '#numDocumentos'+num;
    var checbox = '#marcarTodo'+num;
    var checks = '.check'+num;
    console.log(cantidad);
    console.log(checbox);
    console.log(checks);
    var numeroCheck = $(cantidad).val();//  Numero de documentos    
    console.log(numeroCheck);
    if (numeroCheck >= 1){//    Si ay más de un documento
        if ($(checbox).is(':checked')) { //   Cuando esta seleccionado
            for (i = 0; i < numeroCheck; i++) {
                $(checks + i).prop('checked', true); //  Todos los Checkbox se seleccionan
            }
        } else {
            for (i = 0; i < numeroCheck; i++) {
                $(checks + i).prop('checked', false); //  Todos los Checkbox se desactivan
            }
        }
    } else{  //     Si no existe ningun documento
        $(checbox).prop('checked', false); // Se desactiva el checkbox del Label
        if (clickSeleccionarTodos == 1 || clickSeleccionarTodos == 3 || clickSeleccionarTodos == 6){//s es presionado 2, 4 o 6 veces el label Seleccionar Todos
            //mostrarMensaje("No existe ningun archivo en este directorio");
            alert("No hay");
        }
        clickSeleccionarTodos ++;// numero de veces que se pulsa el Label Seleccionar todos
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
//Se crea la funcion siguiente para enviar el id de la pre-factura y el tipo de relacion del SAT
function generarFacturaLecturaSustituir(factura) {
    var relacion = 4;
    if (confirm("¿Esta seguro que desea sustituir la Pre-factura?")) {
        loading("Procesando ...");
        $.post("WEB-INF/Controllers/facturacion/Controller_Convert_Factura.php", {id: factura, Tiporelacion: relacion}, function(data) {
            $("#tablamensajeinfo").html(data);
            finished();
        });
    }
}

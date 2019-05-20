$(document).ready(function() {
    $("#divConceptos").empty();
    var form = "#altaFacturaform";
    var controlador = "WEB-INF/Controllers/facturacion/Controller_nueva_Factura.php";
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
            LugarExpedicion: {required: true},
            MetodoPago: {required: true},
            FormaPago: {required: true},
            RFCEmisor: {required: true},
            RFCReceptor: {required: true}/*,
            Serie: {required: true}*/
        },
        messages: {
            LugarExpedicion: {required: " * Ingrese el lugar de expedición"},
            MetodoPago: {required: " * Seleccione el metodo de pago"},
            FormaPago: {required: " * Seleccione la forma de pago"},
            RFCEmisor: {required: " * Seleccione el emisor"},
            RFCReceptor: {required: " * Seleccione Receptor"}/*,
            Serie: {required: " * Seleccione serie"}*/
        }
    });
    
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {
                    var res = data.split(",");
                    $("#idFactura").val(res[0]);
                    //alert("La factura se creó correctamente con el folio " + res[1]);
                    $("#mensajes").html("La factura se creó correctamente con el folio " + res[1]);
                    /*var direccion = "facturacion/ConceptosFacturacion.php";
                    $("#botones").hide();
                    var ndc = 0;
                    if($("#ndc").length && $("#ndc").val() == "1"){
                        ndc = 1;
                    }
                    $("#divConceptos").load(direccion, {id: $("#idFactura").val(), 'ndc':ndc}, function() {
                        finished();
                    });*/
                    
                    if($("#ndc").length && $("#ndc").val() == "1"){
                        cambiarContenidos("facturacion/alta_factura.php?id=" + res[0]+"&param1=egreso&nuevo=true", "Facturación");
                    }else{
                        cambiarContenidos("facturacion/alta_factura.php?id=" + res[0], "Facturación");
                    }
                    
                } else {
                    $('#divinfo').html(data);
                }
            });
        }
    });

    var periodos = parseInt($("#numero_periodos").val());

    for (var i = 1; i <= periodos; i++) {        
        if ($('#periodo_facturacion_' + i).length) {  
            if($("#permiso_periodo").val() == "1" || (i == 1 && $('#periodo_facturacion_' + i).val() == "")){
                $('#periodo_facturacion_' + i).datepicker({
                    changeMonth: true,
                    changeYear: true,
                    changeDay: false,
                    showButtonPanel: true,
                    dateFormat: 'yy-mm-01',
                    onClose: function(dateText, inst) {
                        var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                        var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                        $(this).datepicker('setDate', new Date(year, month, 1));
                    }
                });
            }
            
            $("#periodo_facturacion_" + i).rules('add', {
                required: true,
                messages: {
                    required: " * Ingrese el periodo"
                }
            });            
        }
    }
    
    for(var i=0;;i++){
        if($("#kaddenda_"+i).length){
            var id=$("#kaddenda_"+i).val();
            $("#addenda_" + id).rules('add', {
                required: true,
                messages: {
                    required: " * Ingrese el valor del concepto"
                }
            });
        }else{
            break;
        }
    }
});

function CargarInfoReceptor() {
    $("#cargarRFCinfoReceptor").empty();
    $("#cargarRFCinfoReceptor").load("facturacion/Tabla_Receptor.php", {id: $("#RFCReceptorA").val()});
}

function limpiarConcepto() {
    $("#Cantidad").val("");
    $("#Unidad").val("");
    $("#Descripcion").val("");
    $("#PrecioUnitario").val("");
}

function AgregarConcepto() {
    if ($("#Cantidad").val() !== "" && $("#Unidad").val() !== "" && $("#Descripcion").val() !== "" && $("#PrecioUnitario").val() !== "") {

        $("#Cantidad").val("");
        $("#Unidad").val("");
        $("#Descripcion").val("");
        $("#PrecioUnitario").val("");
    } else {
        alert("Ingrese todos los campos para agregar el concepto");
    }

}

function cargarEmisor(id) {
    if ($("#" + id).val() != "") {
        $.post("facturacion/Tabla_Receptor.php", {id: $("#" + id).val()}, function(data) {
            var res = data.split("||#||");
            $("#RFCEmisorA").val(res[1]);
            $("#RFCEmisor").val(res[0]);
            $("#NombreEmisor").val(res[2]);
            $("#RegimenFiscal").val(res[3]);
            $("#LugarExpedicion").val(res[4]);
            $("#NumCtaPago").val(res[5])
        });
    }
}

function agregarPeriodo() {
    var numero_periodos = $("#numero_periodos").val();
    numero_periodos++;

    $("#t_datos_grales").append("<tr id='table_row_" + numero_periodos + "'><td></td><td></td><td></td>" +
            "<td style='text-align:left; vertical-align:top;' class='Etiquetas' >" +
            "<input type='text' id='periodo_facturacion_" + numero_periodos + "' name='periodo_facturacion_" + numero_periodos + "'/>" +
            "<input type='image' src='resources/images/add.png' title='Agregar otro periodo' onclick='agregarPeriodo(); return false;'/>" +
            "<input type='image' src='resources/images/Erase.png' title='Eliminar este periodo' onclick='borrarPeriodo(" + numero_periodos + "); return false;'/>" +
            "</td></tr>");
    $('#periodo_facturacion_' + numero_periodos).datepicker({
        changeMonth: true,
        changeYear: true,
        changeDay: false,
        showButtonPanel: true,
        dateFormat: 'yy-mm-01',
        onClose: function(dateText, inst) {
            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).datepicker('setDate', new Date(year, month, 1));
        }
    });

    $("#periodo_facturacion_" + numero_periodos).rules('add', {
        required: true,
        messages: {
            required: " * Ingrese el periodo"
        }
    });

    $("#numero_periodos").val(numero_periodos);
}

function borrarPeriodo(fila) {
    var row = 'table_row_' + fila;
    var trs = $("#t_datos_grales tr").length;
    var contador = $("#numero_periodos").val();
    if (trs > 1) {//Si hay filas en la tabla        
        $("#" + row).remove();
        $("#" + row).rules("remove");
        for (var i = (fila + 1); i <= contador; i++) {
            if ($("#periodo_facturacion_" + i).length) {
                $('#periodo_facturacion_' + i).attr('id', function() {
                    return 'periodo_facturacion_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'periodo_facturacion_' + (i - 1);  // change name
                });
            }

            if ($("#table_row_" + i).length) {
                $('#table_row_' + i).attr('id', function() {
                    return 'table_row_' + (i - 1);  // change id
                });
            }
        }
        $("#numero_periodos").val(contador - 1);
    }
}
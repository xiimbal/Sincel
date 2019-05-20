$(document).ready(function() {
    if($("#slOrdenCompra").length){//Si esta en la pantalla de alta entrada
        $('#txtFechaInicioL').datepicker({dateFormat: 'yy-mm-dd'});
        $('#txtFechaInicioL').mask("9999-99-99");
        $('#txtFechaFinL').datepicker({dateFormat: 'yy-mm-dd'});
        $('#txtFechaFinL').mask("9999-99-99");
        var id_compra = $("#slOrdenCompra").val();

        if ($("#fileupload").length) {
            $('#fileupload').fileupload({
                dataType: 'json',
                submit: function(e, data) {
                    if ($("#txtFolioFactura").val() == "") {
                        alert("Ingresa el folio de la factura");
                        $("#errorFolio").html("* Ingresa el folio de la factura").css("color", "red");
                        return false;
                    }
                },
                done: function(e, data) {
                    $.each(data.result.files, function(index, file) {
                        $("#file_name").val(file.name);
                        $('#progress .bar').text("Archivo " + file.name + " cargado exitosamente");
                        $('#fileupload').hide();
                        $('#contenidos_invisibles').load('WEB-INF/Controllers/compras/Controler_ImportarOC.php',
                                {'nombre_archivo': file.name, 'idCompra': id_compra, 'folio_factura': $("#txtFolioFactura").val(), 'tipo': 2,
                                    'almacen': $("#slAlmacen").val(), 'estatus': 0, 'estadoOC': $("#slOrdenCompra").val()}, function(data) {
                            var n = data.indexOf("Error:");
                            data = data.substring(n);

                            $('#mensajes').html(data);
                            if (data.toString().indexOf("Error:") === -1) {
                                setTimeout(function() {
                                    window.location = "principal.php?mnu=compras&action=alta_entrada_orden_compra&id=" + id_compra;
                                }, 4000);
                            } else {
                                finished();
                            }
                        });
                    });
                },
                progressall: function(e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress .bar').text('Procesado: ' + progress + '%');
                }
            });
        }
    }
});

function recargarReporteEntrada(idOrden, factura){
    window.location.href = "reporte_entrada_orden_compra.php?id="+idOrden+"&fct="+factura;
}

function mostrarDatosOC(oc) {
    loading("Cargando ...");
    $('#contenidos').load("compras/alta_entrada_orden_compra.php", {"id": oc}, function() {
        finished();
    });
}

function BuscarEntradaOC() {
    loading("Cargando ...");
    var oc = $("#txtOcL").val();
    var fi = $("#txtFechaInicioL").val();
    var ff = $("#txtFechaFinL").val();
    var estatus = $("#slEstatusL").val();
    var no_pedido = $("#txt_no_ped").val();
    var surtidos = 0;
    if ($("#ckSurtido").is(":checked")) {
        surtidos = "1";
    }

    $('#contenidos').load("compras/lista_entrada_orden_compra.php", {"oc": oc, "fi": fi, "ff": ff, "estatus": estatus, "surtido": surtidos, "no_pedido": no_pedido}, function() {
        finished();
    });
}

function recibirAlmacenDetalle(id_compra, id_detalle, tipo, serie, parte, cantidad, almacen, ubicacion) {
    var controlador = "WEB-INF/Controllers/compras/Controler_Entrada_Orden_Individual.php";
    $('#contenidos_invisibles').load(controlador, {'id_compra': id_compra, 'id_detalle': id_detalle, 'tipo': tipo, 'NoParte': parte, 'Serie': serie, 'Cantidad': cantidad, 'Almacen': almacen, 'Ubicacion': ubicacion}, function(data) {
        var mensaje = $("#mensajes").val();
        mensaje = mensaje + "<br/>" + data;
        $('#mensajes').html(mensaje);
    });
}

function recibirAlmacen(id_compra) {
    if (confirm("Está seguro de recibir lo seleccionado en el almacen")) {
        loading("Procesando ... ");
        var i = 1;
        var separador = "X&&__&&X";
        var tipos = "";
        var partes = "";
        var almacenes = "";
        var cantidades = "";
        var series = "";
        var ubicaciones = "";
        var id_detalles = "";
        var no_pedido = $("#txt_pedido").val();
        var ubicaciones_nueva = "";
        var cantidad_nueva = "";
        for (i = 1; ; i++) {
            if ($("#recibido_" + i).length) {
                if ($("#recibido_" + i).is(":checked")) {
                    tipos += ($("#tipo_" + i).val() + separador);
                    partes += ($("#parte_" + i).val() + separador);
                    almacenes += ($("#almacen_" + i).val() + separador);
                    cantidades += ($("#cantidad_" + i).val() + separador);
                    series += ($("#serie_" + i).val() + separador);
                    ubicaciones += ($("#ubicacion_" + i).val() + separador);
                    id_detalles += ($("#id_detalle_" + i).val() + separador);

                    ubicaciones_nueva += ($("#txt_ub_entrada_" + i).val() + separador);
                    cantidad_nueva += ($("#txt_cant_entrada_" + i).val() + separador);
                    /*recibirAlmacenDetalle(id_compra,$("#id_detalle_"+i).val(), $("#tipo_"+i).val(), $("#serie_"+i).val(), $("#parte_"+i).val(), 
                     $("#cantidad_"+i).val(), $("#almacen_"+i).val(), $("#ubicacion_"+i).val());*/
                }
            } else {
                break;
            }
        }
        tipos = tipos.substring(0, tipos.length - separador.length);
        partes = partes.substring(0, partes.length - separador.length);
        almacenes = almacenes.substring(0, almacenes.length - separador.length);
        cantidades = cantidades.substring(0, cantidades.length - separador.length);
        series = series.substring(0, series.length - separador.length);
        ubicaciones = ubicaciones.substring(0, ubicaciones.length - separador.length);
        id_detalles = id_detalles.substring(0, id_detalles.length - separador.length);

        ubicaciones_nueva = ubicaciones_nueva.substring(0, ubicaciones_nueva.length - separador.length);
        cantidad_nueva = cantidad_nueva.substring(0, cantidad_nueva.length - separador.length);


        var controlador = "WEB-INF/Controllers/compras/Controler_Entrada_Orden_Individual.php";
        $('#contenidos_invisibles').load(controlador, {'separador': separador, 'id_compra': id_compra, 'id_detalle': id_detalles, 'tipo': tipos, 'NoParte': partes,
            'Serie': series, 'Cantidad': cantidades, 'Almacen': almacenes, 'Ubicacion': ubicaciones, "no_pedido": no_pedido, "ubicaciones_nueva": ubicaciones_nueva, "cantidad_nueva": cantidad_nueva}, function(data) {
            $('#mensajes').html(data);
            if (data.toString().indexOf("Error:") === -1) {
                setTimeout(function() {
                    window.location = "../principal.php?mnu=compras&action=entrega_evidencias&id="+ id_compra;
                }, 4000);
            } else {
                finished();
            }
        });
    }
}

function RecibirOC(estado) {
    var mensaje = "";
    $("#recibir_oc").hide();
    $("#cancelar_oc").hide();
    $("#recibir_oc1").hide();
    $("#cancelar_oc1").hide();
        
    if (estado == 0) {
        mensaje = "recibir";
    } else {
        mensaje = "rechazar";
    }
    if (confirm("Esta seguro que desea " + mensaje + " lo seleccionado")) {
        loading("Cargando ...");
        
        var avanzar = true;
        var seleccionados = 0;
        var oc = $("#slOrdenCompra").val();
        var almacen = $("#slAlmacen").val();
        var folio = $("#txtFolioFactura").val();
        var no_pedido = $("#txt_pedido").val();
        var arrayIdDetalle = new Array();
        var arrayCantidad = new Array();
        var arrayUbicacion = new Array();
        var arrayNoSerie = new Array();
        var estatus = estado;
        if (estatus == "0" && folio != "" || estatus == "1") {
            $("input:checkbox:checked").each(function() {
                seleccionados++;
                var fila = $(this).val();
                var idDetalle = $("#txtIdDetalle" + fila).val();
                var recibidos = $("#txtCantidadEntrada" + fila).val();
                var ubicacion = $("#txtUbicacion" + fila).val();
                var cantidad = $("#txtCantidad" + fila).val();
                var noSerie = "++";
                if ($("#txtNoSerie" + fila).length) {//no serie
                    noSerie = $("#txtNoSerie" + fila).val();
                }
                if (!isNaN(recibidos) && recibidos !== "" && recibidos > 0 && parseInt(cantidad) >= parseInt(recibidos)) {
                    $("#errorSerie" + fila).html("");
                    $("#errorCantidad" + fila).html("");
                    arrayIdDetalle.push(idDetalle);
                    arrayCantidad.push(recibidos);
                    arrayUbicacion.push(ubicacion);
                    arrayNoSerie.push(noSerie);
                    if (estatus == "0") {
                        if ($("#txtNoSerie" + fila).length) {
                            if ($("#txtNoSerie" + fila).val() === "") {
                                avanzar = false;
                                $("#errorSerie" + fila).html("Ingresa el número de serie").css("color", "red");
                            } else {
                                $("#errorSerie" + fila).html("").css("color", "red");
                            }
                        }
                    }
                } else {
                    avanzar = false;
                    if (recibidos === "") {
                        $("#errorCantidad" + fila).html("Ingresa la cantidad").css("color", "red");
                    } else if (isNaN(recibidos)) {
                        $("#errorCantidad" + fila).html("ingrese solo números").css("color", "red");
                    } else if (recibidos <= 0) {
                        $("#errorCantidad" + fila).html("ingrese un número mayor a 0").css("color", "red");
                    } else if (parseInt(recibidos) > parseInt(cantidad)) {
                        $("#errorCantidad" + fila).html("La cantidad recibida debe ser menor a la solicitada").css("color", "red");
                    } else {
                        $("#errorCantidad" + fila).html("");
                    }
                }
            });
            if (seleccionados > 0) {
                if (avanzar) {
                    var estadoOC = $("#slOrdenCompra").val();
                    $.post("WEB-INF/Controllers/compras/Controler_Entrada_Orden_Compra.php",
                            {"arrayIdDetalle": arrayIdDetalle, "arrayCantidad": arrayCantidad, "arrayUbicacion": arrayUbicacion, "almacen": almacen,
                                "estatus": estatus, "arrayNoSerie": arrayNoSerie, "estadoOC": estadoOC, "folio": folio, "no_pedido": no_pedido}).done(function(data) {
                        $('#mensajes').html(data);
                        if (data.toString().indexOf("Error:") === -1) {
                            $('#contenidos').load("compras/alta_entrada_orden_compra.php", {"id": oc}, function() {                                
                                finished();
                                $("#recibir_oc").show();
                                $("#cancelar_oc").show();
                                $("#recibir_oc1").show();
                                $("#cancelar_oc1").show();
                            });
                        } else {                            
                            finished();
                            $("#recibir_oc").show();
                            $("#cancelar_oc").show();
                            $("#recibir_oc1").show();
                            $("#cancelar_oc1").show();
                        }
                    });
                } else {
                    finished();
                    $("#recibir_oc").show();
                    $("#cancelar_oc").show();
                    $("#recibir_oc1").show();
                    $("#cancelar_oc1").show();
                }
            } else {
                alert("Selecciona por lo menos un registro");
                finished();
                $("#recibir_oc").show();
                $("#cancelar_oc").show();
                $("#recibir_oc1").show();
                $("#cancelar_oc1").show();
            }
        } else {
            $("#errorFolio").html("Ingresa el folio de la factura").css("color", "red");
            finished();
            $("#recibir_oc").show();
            $("#cancelar_oc").show();
            $("#recibir_oc1").show();
            $("#cancelar_oc1").show();
        }
    }
}

function imprimirReporteEntradaOC(pagina, id) {
    limpiarMensaje();
    window.open(pagina + "?id=" + id, '_blank');
}

function seleccionaTodo() {
    var i = 1;
    for (i = 1; ; i++) {
        if ($("#recibido_" + i).length) {
            $("#recibido_" + i).prop('checked', true);
        } else {
            return;
        }
    }
}

function validafila(fila) {
    var disponible = $("#txt_cant_disponible_" + fila).val();
    var cantidad = $("#txt_cant_entrada_" + fila).val();
    if (parseInt(cantidad) <= parseInt(disponible)) {
        $("#div_error_cantidad_" + fila).html("");
    } else {
        $("#div_error_cantidad_" + fila).html("La cantidad debe ser menor o igual a la cantidad disponible");
        $("#recibido_" + fila).prop("checked", false);
    }
}
function ejecutar_cron() {
    loading("Ejecutando ... ");
    $('#contenidos').load("compras/cron_pendientes_recibir.php", function() {
        finished();
    });
}
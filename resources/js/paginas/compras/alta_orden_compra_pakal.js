var inicializado = false;
$(document).ready(function() {
    var form = "#frmOrdenCompra";
    var paginaExito = "mesa/lista_ticket_pakal.php";
    var controlador = "WEB-INF/Controllers/compras/Controler_Orden_Compra_pakal.php";
    if ($("#fileupload").length) {
        inicializado = true;
        $('#fileupload').fileupload({
            dataType: 'json',
            done: function(e, data) {
                $.each(data.result.files, function(index, file) {
                    $("#file_name").val(file.name);
                    $('#progress .bar').text("Archivo " + file.name + " cargado exitosamente");
                    $('#fileupload').hide();
                    inicializado = false;
                });
            },
            progressall: function(e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .bar').text('Procesado: ' + progress + '%');
            }
        });
    }

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    jQuery.validator.addMethod('validarCero', function(value) {
        if (value == 0) {
            return false;
        } else {
            return true;
        }
    }, " * Ingrese un número mayor a 0");
    jQuery.validator.addMethod('validarNoC', function(value) {
        var noParte = value.split('/');
        if (noParte.length >= 3) {
            return true;
        } else {
            return false;
        }

    }, " * Ingrese un componente valido");
    jQuery.validator.addMethod('validarNoE', function(value) {
        var noParte = value.split('/');
        if (noParte.length >= 3) {
            return true;
        } else {
            return false;
        }

    }, " * Ingrese un equipo valido");
//    jQuery.validator.addMethod('valDolar', function(value) {
//        if ($("#ck_dolar").is(':checked')) {
//            if (value == "") {
//                return true;
//            } else {
//                return false;
//            }
//        } else {
//            return true;
//        }
//    }, " * Ingrese el tipo de cambio");
    $(form).validate({
        rules: {
            txt_pedido: {required: true},
            slProveedor: {selectcheck: true},
            slRazonSocial: {selectcheck: true},
            slEstatus: {selectcheck: true},
            txtfechaOrden: {required: true},
            slFormaPago: {selectcheck: true},
            txtPeso: {number: true},
            txtMetros: {number: true},
            txtTipoCambio: {required: true, number: true, validarCero: true},            
            slAlmacen: {selectcheck: true}

        },
        messages: {
            txt_pedido: {required: " * Ingrese el número de pedido"},
            txtfechaOrden: {required: " * Ingrese la fecha"},
            txtNoOrden: {required: " * Ingrese el número de orden"},
            txtCondicionesPago: {required: " * Ingrese las condiciones de pago"},
            txtPeso: {number: " * Ingrese solo números"},
            txtMetros: {number: " * Ingrese solo números"},            
            txtTipoCambio: {required: " * Ingrese el tipo de cambio", number: " * Ingrese solo números"}
        }
    });
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            event.preventDefault();
            var tbComponente = $("#tbComponente tr").length;
            var tbEquipo = $("#tbEquipo tr").length;
            $.post(controlador, {form: $(form).serialize(), "tbComponente": tbComponente, "tbEquipo": tbEquipo, "arrayCompiDetalle": arrayCompiDetalle}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {
                    var id_compra = data;
                    $("#idOrden_compra").val(id_compra);
                    $('#mensajes').html("La compra <b>" + id_compra + "</b> se actualizo correctamente");
                    if ($("#file_name").length && $("#file_name").val() != "") {
                        $('#contenidos_invisibles').load('WEB-INF/Controllers/compras/Controler_ImportarOC.php', {'nombre_archivo': $("#file_name").val(),
                            'idCompra': id_compra, 'tipo': 1}, function(data) {
                            if (data.toString().indexOf("Error:") === -1) {
                                setTimeout(function() {
                                    window.location = "principal.php?mnu=compras&action=alta_orden_compra&id=" + id_compra;
                                }, 3000);
                            } else {
                                $('#mensajes').html(data);
                                finished();
                            }
                        });
                    } else {
                        $('#contenidos').load(paginaExito, function() {
                         finished();
                         });
                        
                    }
                } else {
                    $('#mensajes').html(data);
                    finished();
                }
            });
        }
    });
    $('#txtfechaOrden').datepicker({dateFormat: 'yy-mm-dd'});
    $('#txtfechaOrden').mask("9999-99-99");
    $('#txtFechaInicioL').datepicker({dateFormat: 'yy-mm-dd'});
    $('#txtFechaInicioL').mask("9999-99-99");
    $('#txtFechaFinL').datepicker({dateFormat: 'yy-mm-dd'});
    $('#txtFechaFinL').mask("9999-99-99");
    $(".onckeyC").keyup(function() {
        var fila = $(this).attr('fila');
        rulesCompPrecio(fila);
    });
    $(".onckeyCantidad").keyup(function() {
        var fila = $(this).attr('fila');
        rules_cantidad(fila);
    });

    $(".onckeyE").keyup(function() {
        var fila = $(this).attr('fila');
        rulesCompPrecioEquipo(fila);
    });
    $(".onckeyCantidad_eq").keyup(function() {
        var fila = $(this).attr('fila');
        rules_cantidad_eq(fila);
    });
//    $("input:checkbox:checked").each(function() {
//        $("#divTipoCambio").show();
//    });
});
var filaComponentes = $("#txttamanoComponentes").val();
function agregarComponenteOC(tipo, cantidad, noParte, idNota, precioC) {
    var newRow = "<tr id='filaComponenteOC" + filaComponentes + "'>" +
            "<td align='center' scope='row'>" +
            "<input type='hidden' id='txtidApartado" + filaComponentes + "' name='txtidApartado" + filaComponentes + "' style='width:100%'/>" +
            "<input type='hidden' id='txtidDetalleC" + filaComponentes + "' name='txtidDetalleC" + filaComponentes + "' value=''/>" +
            "<select id='slTipoComponente" + filaComponentes + "'name='slTipoComponente" + filaComponentes + "' style='width:100%' onchange='cargarSelectComponente(" + filaComponentes + ",this.value)'><option value='0'>Selecciona una opción</option></select></td>" +
            "<td align='center' scope='row'><input type='text' id='txtComponentesOC" + filaComponentes + "' name='txtComponentesOC" + filaComponentes + "' style='width:99%' onBlur='costoComponente(" + filaComponentes + ")'/></td>" +
            "<td align='center' scope='row'><input type='text' id='txtCantidad_entregada_comp" + filaComponentes + "' name='txtCantidad_entregada_comp" + filaComponentes + "' value='0' readonly style='width:99%'/></td>" +
            "<td align='center' scope='row'><input type='text' id='txtCantidadComponente" + filaComponentes + "' name='txtCantidadComponente" + filaComponentes + "' style='width:99%'/></td>" +
            "<td align='center' scope='row'><input type='text' id='txtPrecioVentaC" + filaComponentes + "' name='txtPrecioVentaC" + filaComponentes + "' style='width:100%' readonly/></td>" +
            "<td align='center' scope='row'><input type='text' id='txtPrecioUnitarioC" + filaComponentes + "' name='txtPrecioUnitarioC" + filaComponentes + "' fila='" + filaComponentes + "' onBlur='elimnarRulsPrecio(" + filaComponentes + ");' style='width:99%'/></td>" +
            "<td align='center' scope='row'><img class='imagenMouse' src='resources/images/Erase.png' title='Eliminar componente' onclick='deleteRowComponenteOC(" + filaComponentes + ")' style='cursor: pointer;'/></td>";
    $('#tbComponente tr:last').after(newRow); //add the new row  
    $("#txtPrecioUnitarioC" + filaComponentes).keyup(function() {
        var fila = $(this).attr('fila');
        rulesCompPrecio(fila);
    });
    //var attrFila = $("#txtPrecioUnitarioC" + filaComponentes).attr('fila');
    rulesComponentesOC(filaComponentes);
    cargarSelectTipoComponente(filaComponentes, tipo, cantidad, noParte, precioC);
    $("#txtidApartado" + filaComponentes).val(idNota);
    filaComponentes++;
}
var filaEquipo = $("#txttamanoEquipos").val();
function agregarEquipoOC() {
    var newRow = "<tr id='filaEquipoOC" + filaEquipo + "'>" +
            "<td align='center' scope='row'>" +
            "<input type='hidden' id='txtidDetalleE" + filaEquipo + "' name='txtidDetalleE" + filaEquipo + "'/>" +
            "<input type='text' id='txtEquipoOC" + filaEquipo + "' name='txtEquipoOC" + filaEquipo + "' style='width:99%' onBlur='costoEquipo(" + filaEquipo + ")'/></td>" +
            "<td align='center' scope='row'><input type='text' id='txtCantidad_entregada_eq" + filaEquipo + "' name='txtCantidad_entregada_eq" + filaEquipo + "' value='0' readonly style='width:99%'/></td>" +
            "<td align='center' scope='row'><input type='text' id='txtCantidadEquipo" + filaEquipo + "' name='txtCantidadEquipo" + filaEquipo + "' style='width:99%'/></td>" +
            "<td align='center' scope='row'><input type='text' id='txtPrecioVentaE" + filaEquipo + "' name='txtPrecioVentaE" + filaEquipo + "' style='width:100%' readonly/></td>" +
            "<td align='center' scope='row'><input type='text' id='txtPrecioUnitarioE" + filaEquipo + "' name='txtPrecioUnitarioE" + filaEquipo + "' fila='" + filaEquipo + "'style='width:99%' onBlur='elimnarRulsPrecioE(" + filaEquipo + ")'/></td>" +
            "<td align='center' scope='row'><img class='imagenMouse' src='resources/images/Erase.png' title='Eliminar equipo' onclick='deleteRowEquipoOC(" + filaEquipo + ")' style='cursor: pointer;'/></td>";
    $('#tbEquipo tr:last').after(newRow); //add the new row
    $("#txtPrecioUnitarioE" + filaEquipo).keyup(function() {
        var fila = $(this).attr('fila');
        rulesCompPrecioEquipo(fila);
    });
    cargarSelectEquipo(filaEquipo);
    rulesEquipoOC(filaEquipo);
    filaEquipo++;
}
function importarComponentes() {
    loading("Cargando componentes ...");
    $("#btnImport").hide();
    $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"ImportarComponentes": "0"}, function(data) {
        finished();
        var dataJson = eval(data);
        var tipoComp = "";
        var cantidadComp = 0;
        var noParte = "";
        var idNota = "";
        var precioC = 0;
        for (var i in dataJson) {
            tipoComp = dataJson[i].tipoComponente;
            cantidadComp = dataJson[i].cantidad;
            noParte = dataJson[i].noParte;
            idNota = dataJson[i].idNotaTicket;
            precioC = dataJson[i].precio;
            agregarComponenteOC(tipoComp, cantidadComp, noParte, idNota, precioC);
        }
        ApartarImportacion();
    });
}
var arrayCompiDetalle = new Array();
var conArray = 0;
function deleteRowComponenteOC(numRow) {
    if ($("#txtidDetalleC" + numRow).val() != "") {
        arrayCompiDetalle[conArray] = $("#txtidDetalleC" + numRow).val();
    }
    if ($("#txtidApartado" + numRow).val() != "") {//elimiar importados
        $.post("WEB-INF/Controllers/compras/Controler_Apartar_Componente_Import.php", {"idApartados": $("#txtidApartado" + numRow).val()}, function(data) {
        });
    }
    var fila = "filaComponenteOC" + numRow;
    $("#" + fila).remove();
    conArray++;
}
function deleteRowEquipoOC(numRow) {
    if ($("#txtidDetalleE" + numRow).val() != "") {
        arrayCompiDetalle[conArray] = $("#txtidDetalleE" + numRow).val();
    }
    var fila = "filaEquipoOC" + numRow;
    $("#" + fila).remove();
    conArray++;
}
function cargarSelectTipoComponente(numFila, tipo, cantidad, noParte, precioC) {
    $('#slTipoComponente' + numFila).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {"OrdenCompraTipoComponente": "0"}, function(data) {
        if (tipo != "" || tipo != 0) {
            $("#slTipoComponente" + numFila + " option[value=" + tipo + "]").attr("selected", true);
            cargarSelectComponente(numFila, tipo, noParte);
            $("#txtCantidadComponente" + numFila).val(cantidad);
            var precioTotal = parseFloat(precioC) * parseFloat($("#txtTipoCambio").val());
            $("#txtPrecioVentaC" + numFila).val(parseFloat(precioTotal));
        }
    });
}
function cargarSelectComponente(numFila, tipoComponente, noParte) {
    var availableTags = new Array();
    var proveedor = "";
    if (tipoComponente == "7" || tipoComponente == "8") {
        proveedor = $("#slProveedor").val();
    }
    $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"componentesOC": "0", "tipoComponente": tipoComponente, "proveedor": proveedor}, function(data) {
        var dataJson = eval(data);
        for (var i in dataJson) {
            availableTags.push(dataJson[i].Componente);
        }
    });
    $("#txtComponentesOC" + numFila).val(noParte);
    cargarCompnentes("txtComponentesOC" + numFila, availableTags);
}
function cargarSelectEquipo(numFila) {
    var availableTags = new Array();
    $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"SelectEquipo": "0"}, function(data) {
        var dataJson = eval(data);
        for (var i in dataJson) {
            availableTags.push(dataJson[i].Equipo);
        }
    });
    cargarCompnentes("txtEquipoOC" + numFila, availableTags);
}

function TipoDePrecio(fila) {
    var costoTotal = 0;
    var tipoCambio = $("#txtTipoCambio").val();
    var costo = $("#txtPrecioVentaC" + fila).val();
    if ($("#ckDolarC" + fila).is(":checked")) {//poner en dolares precio   
        costoTotal = parseFloat(costo) / parseFloat(tipoCambio);
    } else {
        costoTotal = parseFloat(costo) * parseFloat(tipoCambio);
    }
    $("#txtPrecioVentaC" + fila).val(parseFloat(costoTotal));
}
function TipoDePrecioEquipo(fila) {
    var costoTotal = 0;
    var tipoCambio = $("#txtTipoCambio").val();
    var costo = $("#txtPrecioVentaE" + fila).val();
    if ($("#ckDolarE" + fila).is(":checked")) {//poner en dolares precio   
        costoTotal = parseFloat(costo) / parseFloat(tipoCambio);
    } else {
        costoTotal = parseFloat(costo) * parseFloat(tipoCambio);
    }
    $("#txtPrecioVentaE" + fila).val(parseFloat(costoTotal));
}
function rulesCompPrecio(attrFila) {
    var aux = $("#txtPrecioVentaC" + attrFila);
    jQuery.validator.addMethod('precios', function(value) {
        if (parseFloat(value) > parseFloat(aux.val())) {
            return false;
        } else {
            return true;
        }
    }, " * El precio de compra es mayor al precio de venta");
    $("#txtPrecioUnitarioC" + attrFila).rules("add", {precios: true});
}
/*function rules_cantidad(attrFila) {
    var aux = $("#txtCantidad_entregada_comp" + attrFila);
    jQuery.validator.addMethod('validar_cantidad_com', function(value) {
        if (value >= aux.val()) {
            return true;
        } else {
            return false;
        }
    }, " * El la cantidad es menor a la cantidad entregada");
    $("#txtCantidadComponente" + attrFila).rules("add", {validar_cantidad_com: true});
}
function rules_cantidad_eq(attrFila) {
    var aux = $("#txtCantidad_entregada_eq" + attrFila);
    jQuery.validator.addMethod('validar_cantidad_eq', function(value) {
        if (value >= aux.val()) {
            return true;
        } else {
            return false;
        }
    }, " * El la cantidad es menor a la cantidad entregada");
    $("#txtCantidadEquipo" + attrFila).rules("add", {validar_cantidad_eq: true});
}*/
function rulesCompPrecioEquipo(attrFila) {
    var aux = $("#txtPrecioVentaE" + attrFila);
    jQuery.validator.addMethod('preciosE', function(value) {
        if (parseFloat(value) > parseFloat(aux.val())) {
            return false;
        } else {
            return true;
        }
    }, " * El precio de compra es mayor a la precio de venta");
    $("#txtPrecioUnitarioE" + attrFila).rules("add", {preciosE: true});
}
function rulesComponentesOC(fila) {
    $("#slTipoComponente" + fila).rules("add", {selectcheck: true});
    $("#txtComponentesOC" + fila).rules("add", {required: true, validarNoC: true, messages: {required: " * Ingrese el componente"}});
    $("#txtCantidadComponente" + fila).rules("add", {required: true, number: true, validarCero: true, messages: {required: " * Campo obligatorio", number: "* Ingresa solo números"}});
    $("#txtPrecioUnitarioC" + fila).rules("add", {required: true, number: true, validarCero: true, messages: {required: " * Campo obligatorio", number: "* Ingresa solo números"}});
}
function rulesEquipoOC(fila) {
    $("#txtEquipoOC" + fila).rules("add", {required: true, validarNoE: true, messages: {required: " * Ingrese el Equipo"}});
    $("#txtCantidadEquipo" + fila).rules("add", {required: true, number: true, validarCero: true, messages: {required: " * Campo obligatorio", number: "* Ingresa solo números"}});
    $("#txtPrecioUnitarioE" + fila).rules("add", {required: true, number: true, validarCero: true, messages: {required: " * Campo obligatorio", number: "* Ingresa solo números"}});
}
function buscarOrdenCompra() {
    loading("Cargando ...");
    var proveedor = $("#slProveedorL").val();
    var modelo = $("#txtModeloL").val();
    var fechaInicio = $("#txtFechaInicioL").val();
    var fechaFin = $("#txtFechaFinL").val();
    var oc = $("#txtOrdenCompraL").val();
    var estatus = $("#slEstatusL").val();
    var cancelados = "0", tickets = "0";
    var surtido = "0";
    var no_pedido = $("#txt_no_ped").val();
    if ($("#ckTickets").is(":checked")) {
        tickets = "1";
    }
    if ($("#ckSurtido").is(":checked")) {
        surtido = "1";
    }
    if ($("#ckCancelados").is(":checked")) {
        cancelados = "1";
    }
    $('#contenidos').load("compras/lista_orden_compra.php", {"proveedor": proveedor, "modelo": modelo, "fechaInicio": fechaInicio, "fechaFin": fechaFin, "oc": oc, "estatus": estatus, "cancelados": cancelados, "surtido": surtido, "no_pedido": no_pedido, "tickets": tickets}, function() {
        finished();
    });
}
function mostrarDireccionProveedor(proveedor) {
    $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"direccionProv": "0", "prov": proveedor}).done(function(data) {
        $("#txtNoCliente").val(data);
    });
}
function mostrarDireccionFacturacion(facturcion) {
    loading("Cargando ...");
    $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"direccionFacturacion": "0", "fact": facturcion}).done(function(data) {
        $("#txtdireccionFactra").val(data);
        finished();
    });
}
function mostrarDireccionAlmacen(almacen) {
    loading("Cargando ...");
    $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"direccionAlmacen": "0", "idAlmacen": almacen}).done(function(data) {
        $("#txtdireccionEmbarca").val(data);
        finished();
    });
}
function imprimirReporteOC(pagina, id) {
    limpiarMensaje();
    window.open(pagina + "?id=" + id, '_blank');
}
function ExportarOCExcel() {
    $("#FormularioExportacion").submit();
}
function cargarCompnentes(campo, availableTags) {
    $("#" + campo).autocomplete({
        source: availableTags,
        minLength: 2
    });
}
function ApartarImportacion() {
    $.post("WEB-INF/Controllers/compras/Controler_Apartar_Componente_Import.php", {"ApartarMoroso": ""}, function(data) {
        //$("#btnImport").prop("disabled", false);
    });
}
function CopiarOrdenCompra() {
    var idOC = $("#slOrdenCompra").val();
    loading("Cargando ...");
    limpiarMensaje();
    $('#loading_text').load("verificaSession.php", function(data) {
        if (data.toString().indexOf("false") === -1) {/*En caso de que la sesion siga existiendo*/
            $("#contenidos").load('compras/alta_orden_compra.php', {"id": idOC, "copiado": "1"}, function() {
                $(".button").button();
                finished();
            });
        } else {
            window.location = "index.php?session=finished";
        }
    });
}
function costoComponente(fila) {
    var componente = $("#txtComponentesOC" + fila).val();
    var auxComponente = componente.split(" // ");
    $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"precioComponente": "0", "noParte": auxComponente[1]}).done(function(data) {        
        var costo = "";
        $("#div_err_tipo").html("").css("color", "red");
        if ($("#ck_dolar").is(':checked')) {
            if ($("#txtTipoCambio").val() !== "") {
                costo = parseFloat(data) * parseFloat($("#txtTipoCambio").val());                
                $("#txtPrecioVentaC" + fila).val(parseFloat(costo));
            } else {
                $("#div_err_tipo").html("* Ingresa el tipo de cambio").css("color", "red");
                $("#txtTipoCambio").focus();
            }
        } else {            
            costo = parseFloat(data);            
            $("#txtPrecioVentaC" + fila).val(parseFloat(costo));
        }

    });
}
function costoEquipo(fila) {
    var equipo = $("#txtEquipoOC" + fila).val();
    var auxequipo = equipo.split(" / ");
    $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"precioEquipo": "0", "auxequipo": auxequipo[1]}).done(function(data) {
        var costo = "";
        $("#div_err_tipo").html("").css("color", "red");
        if ($("#ck_dolar").is(':checked')) {
            if ($("#txtTipoCambio").val() !== "") {
                costo = parseFloat(data) * parseFloat($("#txtTipoCambio").val());
                $("#txtPrecioVentaE" + fila).val(parseFloat(costo));
            } else {
                $("#div_err_tipo").html("* Ingresa el tipo de cambio").css("color", "red");
                $("#txtTipoCambio").focus();
            }

        } else {
            costo = parseFloat(data);
            $("#txtPrecioVentaE" + fila).val(parseFloat(costo));
        }

    });
}
function elimnarRulsPrecio(fila) {
    $("#txtPrecioUnitarioC" + fila).rules("remove", "precios");
}
function elimnarRulsPrecioE(fila) {
    $("#txtPrecioUnitarioE" + fila).rules("remove", "preciosE");
}
function cancelarOrdenDeCompra(pagina) {
    var form = "#frmOrdenCompra";
    var controlador = "WEB-INF/Controllers/compras/Controler_Apartar_Componente_Import.php";
    loading("Cargando ...");
    limpiarMensaje();

    if ($("#idOrden_compra").val() == "") {
        var tbComponente = $("#tbComponente tr").length;
        $.post(controlador, {form: $(form).serialize(), "tbComponente": tbComponente, "eliminarApartados": "0"}).done(function(data) {
            $('#mensajes').html(data);
            if (data.toString().indexOf("Error:") === -1) {
                $('#contenidos').load(pagina, function() {
                    finished();
                });
            } else {
                finished();
            }
        });
    } else {
        $("#contenidos").load(pagina, function() {
            finished();
        });
    }

}
function desactivarTipoCambio() {
    if ($("#ck_dolar").is(':checked')) {
        $("#txtTipoCambio").val("");
        $('#txtTipoCambio').prop('readonly', true);
    } else {

        $('#txtTipoCambio').prop('readonly', false);
    }
}
function validaNumericos(event) {
    if(event.charCode >= 48 && event.charCode <= 57){
      return true;
     }
     return false;        
}
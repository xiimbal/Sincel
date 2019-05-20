$(document).ready(function() {
    $("#dialog").hide();
    var form = "#formConceptos";
    var controlador = "WEB-INF/Controllers/facturacion/Controller_nuevo_Concepto.php";
    $(".boton").button();/*Estilo de botones*/
    jQuery.validator.addMethod("decimal", function(value) {
        return $.isNumeric(value);
    }, "* debe de ser un número");    
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            Cantidad: {required: true, number: true},
            //Unidad: {required: true},
            Descripcion: {required: true},
            PrecioUnitario: {required: true, decimal: true}
        },
        messages: {
            Cantidad: {required: " * Ingrese la cantidad", number: " * Ingrese un número"},
            //Unidad: {required: " * Ingrese las unidades", number: " * Ingrese un número"},
            Descripcion: {required: " * Ingrese la descripción", number: " * Ingrese un número"},
            PrecioUnitario: {required: " * Ingrese el precio unitario", number: " * Ingrese un número"}
        }
    });

    $('.ui-multiselect').css('width', '100px');
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize(), idFactura: $("#idFactura").val()}).done(function(data) {
                var direccion = "facturacion/ConceptosFacturacion.php";
                var ndc = "0";
                if($("#ndc").length && $("#ndc").val() == "1"){
                    ndc = "1";
                }
                $("#botones").hide();
                $("#divConceptos").load(direccion, {id: $("#idFactura").val(), 'ndc':ndc}, function() {
                    finished();
                });
                $('#divinfo').html(data);
                loading("Enviando...");
                $("#divinfo").empty();
            });
        }
    });
    $("#conceptos_form").validate();
});

function limpiarConcepto() {
    $("#Cantidad").val("");
    $("#Descripcion").val("");
    $("#PrecioUnitario").val("");
}

function modificarConcepto(id) {
    $("#cantidad_" + id).removeAttr("disabled");
    $("#descripcion_" + id).removeAttr("disabled");
    $("#preciounitario_" + id).removeAttr("disabled");
    $("#Unidad_" + id).removeAttr("disabled");
    $("#cantidad_" + id).rules("add", {
        required: true,
        decimal: true,
        messages: {
            required: "* Ingresa la Cantidad",
            decimal: "* Debes Ingresar un número"
        }
    });
    $("#descripcion_" + id).rules("add", {
        required: true,
        messages: {
            required: "* Ingresa la Descripción"
        }
    });
    $("#preciounitario_" + id).rules("add", {
        required: true,
        decimal: true,
        messages: {
            required: "* Ingresa la Cantidad"
        }
    });
    $("#Unidad_" + id).rules("add", {
        required: true,
        messages: {
            required: "* Seleccione la unidad"
        }
    });
    $("#td_concepto_" + id).html("<a onclick='GuardarConcepto(" + id + ")'><img src='resources/images/Apply.png' /></a>");
}

function GuardarConcepto(id) {
    var form = "#conceptos_form";
    var controlador = "WEB-INF/Controllers/facturacion/Controller_nuevo_Concepto.php";
    if ($(form).valid()) {
        $.post(controlador, {form: $(form).serialize(), idFactura: $("#idFactura").val(), concepto: id}).done(function(data) {
            if (data.toString().indexOf("Error:") === -1) {
                $("#cantidad_" + id).attr("disabled", "disabled");
                $("#descripcion_" + id).attr("disabled", "disabled");
                $("#preciounitario_" + id).attr("disabled", "disabled");
                $("#Unidad_" + id).attr("disabled", "disabled");
                $("#cantidad_" + id).rules("remove");
                $("#descripcion_" + id).rules("remove");
                $("#preciounitario_" + id).rules("remove");
                $("#Unidad_" + id).rules("remove");
                $("#td_concepto_" + id).html("<a onclick='modificarConcepto(" + id + ")'><img src='resources/images/Modify.png' /></a>");
                $.post("WEB-INF/Controllers/facturacion/Controller_Total_Factura.php", {id: $("#idFactura").val()}).done(function(data) {
                    $("#total_letras_conceptos").html(data);
                });
            }
        });
    }
}

function calcularImporte(id) {
    $("#importe_" + id).val($("#preciounitario_" + id).val() * $("#cantidad_" + id).val());
}

function EliminarConcepto(id) {
    var controlador = "WEB-INF/Controllers/facturacion/Controller_nuevo_Concepto.php?id=" + id;
    $.post(controlador, function(data) {
        var direccion = "facturacion/ConceptosFacturacion.php";
        var ndc = "0";
        if($("#ndc").length && $("#ndc").val() == "1"){
            ndc = "1";
        }
        $("#divConceptos").load(direccion, {id: $("#idFactura").val(),'ndc':ndc}, function() {
            finished();
        });
        $('#divinfo').html(data);
        loading("Enviando...");
        $("#divinfo").empty();
    });
}

function cargarmodelo(origen, destino) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_modelo_precioabc_factura.php";
    $("#" + destino).load(dir, {id: $("#" + origen).val()}, function() {
        /*Refrescamos las opciones*/
        var x = $('#' + destino).find('option');
        $('#' + destino).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $('#' + destino).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $('.ui-multiselect').css('width', '100px');

    });
}

function cargarPrecio(origen, destino) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_precioabc.php";
    $("#" + destino).load(dir, {modelo: $("#" + origen).val(), tipo: $("#tipo").val()}, function() {
        /*Refrescamos las opciones*/
        var x = $('#' + destino).find('option');
        $('#' + destino).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $('#' + destino).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $('.ui-multiselect').css('width', '100px');
    });

    dir = "WEB-INF/Controllers/Ventas/Controller_text_Descripcion.php";
    $.post(dir, {modelo: $("#" + origen).val(), id: $("#tipo").val()}, function(data) {
        $('#Descripcion').val(data);
    });
}

function otro(origen, destino) {
    if ($("#" + origen).val() === "none") {
        $("#" + destino).prop("disabled", false);
    } else {
        $("#" + destino).prop("disabled", true);
    }
}


function GenerarFactura(id) {    
    /*Validamos el form antes de intentar timbrar*/    
    /*if(!$( "#altaFacturaform" ).valid()){        
        return false;
    }*/
    for(var i=0;;i++){
        if($("#kaddenda_"+i).length){
            var id_addenda=$("#kaddenda_"+i).val();
            if($("#addenda_" + id_addenda).val() === ""){
                alert("Llena todos los valores de las addendas");
                return false;
            }
            /*$("#addenda_" + id).rules('add', {
                required: true,
                messages: {
                    required: " * Ingrese el valor del concepto"
                }
            });*/
        }else{
            break;
        }
    }
    
    //Validamos si hay caracteres especiales    
    $("#contenidos_invisibles").load("WEB-INF/Controllers/facturacion/Controller_Verificar_Factura.php",{'idFactura':id}, function(data){
        if (data.toString().indexOf("Error:") === -1) {//Si no hay caracteres especiales en los conceptos
            var ndc = 0;
            var tipo = "factura";
            var idFacturaNet = 0;
            if($("#ndc").length && $("#ndc").val() == "1"){
                ndc = 1;
                tipo = "nota de crédito";
                if($("#IdFacturaNET").length){
                    idFacturaNet = $("#IdFacturaNET").val();
                }
            }                        
            
            $("#text_factura").css('width', 565);
            $("#text_factura").css('height', 365);
            $("#dialog").dialog({width: 600,
                height: 500,
                buttons: {
                    "Aceptar": function() {
                        if (confirm("¿Esta seguro que desea generar la "+tipo+"?")) {
                            var leyenda = $("#text_factura").val();
                            //alert(leyenda);
                            loading("Procesando ...");                            
                            $.post("WEB-INF/Controllers/facturacion/Controller_nueva_Factura.php", {form: $("#altaFacturaform").serialize(), 'ndc':ndc}).done(function(data) {
                                if (data.toString().indexOf("Error:") === -1) {
                                    var id2 = 0;                                    
                                    if(ndc === 1){
                                        var ids = data.split(",");
                                        if(ids[1]!=null && ids[1]!=""){
                                            id2 = ids[1];
                                        }                                        
                                    }
                                    //alert(id2);
                                    $.post("WEB-INF/Controllers/facturacion/Controller_Generar_Factura.php?id=" + id + "&id2="+id2, 
                                    {'leyenda': leyenda,'ndc':ndc,'IdFacturaNET':idFacturaNet,form: $("#altaFacturaform").serialize()}, function(data) {
                                        if (data.toString().indexOf("Error:") === -1) {
                                            cambiarContenidos('facturacion/ReporteFacturacion.php','Facturas Lecturas');
                                            $('#mensajes').html(data);
                                        } else {
                                            $('#mensajes').html(data);
                                            finished();
                                        }
                                    });
                                } else {
                                    $('#mensajes').html(data);
                                    finished();
                                }
                            });
                            $(this).dialog("close");
                            $(this).dialog('destroy').remove();
                        }
                    },
                    Cancelar: function() {
                        $(this).dialog("close");
                        $(this).dialog('destroy').remove()
                    }
                }});
        }else{            
            $("#mensajes").text(data);
        }
    });    
}
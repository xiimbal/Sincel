var productos = 0;
var contadorOc = 0;
var formProductos = "#frmOc";
var formCamion = "#frmCamion";

$(document).ready(function() {
    
    var controlador = "WEB-INF/Controllers/compras/Controller_CrearCamion.php";
    
    jQuery.validator.addMethod('minStrict', function (value, el, param) {
        if(value !== "" && value !== undefined){
            return value > param;
        }
        return true;
    });
    
    /*validate form*/
    $(formProductos).validate({
        rules: {
            proveedor: {required: true},
            pesoBruto: {number: true, minStrict: 0},
            tara: {number: true, minStrict: 0},
            neto: {number: true, minStrict: 0},
            costoTotal: {number: true, minStrict: 0}
        },
        messages: {
            proveedor: {required: " * Selecciona un proveedor"},
            pesoBruto: {number: " * Ingrese valores numericos", minStrict: " * Debe ser mayor a 0"},
            tara: {number: " * Ingrese valores numericos", minStrict: " * Debe ser mayor a 0"},
            neto: {number: " * Ingrese valores numericos", minStrict: " * Debe ser mayor a 0"},
            costoTotal: {number: " * Ingrese valores numericos", minStrict: " * Debe ser mayor a 0"}
        }
    });
    
    /*validate form*/
    $(formCamion).validate({
        rules: {
            chofer: {required: true},
            empaque: {required: true},
            cantidad: {required: true, digits: true, min: 1},
            producto: {required: true},
            kg: {number: true, minStrict: 0},
            precio: {number: true, minStrict: 0},
            total: {number: true, minStrict: 0},
            latitud: {number: true},
            longitud: {number: true}
        },
        messages: {
            chofer: {required: " * Selecciona un chofer"},
            empaque: {required: " * Ingrese un empaque"},
            cantidad: {required: " * Ingrese la cantidad", digits: "Ingrese un número entero", min: "Ingrese una cantidad mayor a 0"},
            producto: {required: " * Selecciona un producto"},
            kg: {number: " * Ingrese valores numericos", minStrict: " * Debe ser mayor a 0"},
            precio: {number: " * Ingrese valores numericos", minStrict: " * Debe ser mayor a 0"},
            total: {number: " * Ingrese valores numericos", minStrict: " * Debe ser mayor a 0"},
            latitud: {number: " * Ingrese valores numericos"},
            longitud: {number: " * Ingrese valores numericos"}
        }
    });
    
    $(".select").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    $('.boton').button();
    $("#tabs").tabs();
    
});

function cargarProductosProveedor(){
    var claveProveedor = $("#proveedor").val();
    if(claveProveedor == ""){
        $("#producto").html("<option value=''>Seleccione un producto</option>");
        $("#producto").multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
    }else{
        $("#producto").load("WEB-INF/Controllers/Ajax/CargaSelect.php", {ClaveProveedor: claveProveedor, productos: true}, function(data){
            $("#producto").multiselect({
                multiple: false,
                noneSelectedText: "No ha seleccionado",
                selectedList: 1
            }).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
        });
    }
}

function agregarProducto(){
    if ($(formProductos).valid()) {
        $("#tProductos").append(
            "<tr id='tr_" + productos + "'>"  +
            "<td><input type='hidden' id='cantidad_"+productos+"' name='cantidad_"+productos+"' value='"+$("#cantidad").val()+"'/>"+$("#cantidad").val()+"</td>" +
            "<td><input type='hidden' id='empaque_"+productos+"' name='empaque_"+productos+"' value='"+$("#empaque").val()+"'/>"+$("#empaque").val()+"</td>" +
            "<td><input type='hidden' id='producto_"+productos+"' name='producto_"+productos+"' value='"+$("#producto").val()+"'/>"+$("#producto").val()+"</td>" +
            "<td><input type='hidden' id='kg_"+productos+"' name='kg_"+productos+"' value='"+$("#kg").val()+"'/>"+$("#kg").val()+"</td>" +
            "<td><input type='hidden' id='precio_"+productos+"' name='precio_"+productos+"' value='"+$("#precio").val()+"'/>"+$("#precio").val()+"</td>" +
            "<td><input type='hidden' id='total_"+productos+"' name='total_"+productos+"' value='"+$("#total").val()+"'/>"+$("#total").val()+"</td>" +
            "<td><input type='image' src='resources/images/Erase.png' title='Eliminar' onclick='eliminarProducto("+productos+"); return false;'/></td>" +
            "</tr>"
        );
        productos++;
    }
}

function eliminarProducto(contador){
    var fila = "tr_" + contador;
    var trs = $("#tProductos tr").length;
    $("#" + fila).remove();
    productos--;
}

function agregarOc(){
    if(productos > 0){
        limpiarMensaje();
        loading("Cargando ...");
        var controlador = "WEB-INF/Controllers/compras/Controller_CrearCamion.php";
        $.post(controlador, {formProductos: $(formProductos).serialize(), contadorProductos: productos}).done(function(data) {
            if (data.toString().indexOf("Error:") === -1) {
                data = $.trim(data);
                $("#ocs").append(
                    "<div id='div_oc_"+contadorOc+"'>" +
                    "<input type='hidden' name='oc_"+contadorOc+"' id='oc_"+contadorOc+"' value='"+data+"' />"+
                    "<table style='width:90%;'><tr>" +
                    "<td style='width:30%;'><h3>Proveedor: "+$( "#proveedor option:selected" ).text()+" </h3></td>" +
                    "<td style='width:30%;'>Orden: <input type='text' id='posicion_"+contadorOc+"' name='posicion_"+contadorOc+"'/></td>" +
                    "<td style='width:20%;'>" +
                    "<input type=\"image\" height=\"24px\" width=\"24px\" src=\"resources/images/ver.png\" title=\"Modificar\" onclick=\"lanzarPopUp('Orden compra','compras/verOCCamion.php?id="+data+"'); return false;\"/>" +
                    "</td>" +
                    "<td style='width:20%;'>" +
                    "<input type='image' src='resources/images/Erase.png' title='Eliminar' onclick='eliminarOc("+contadorOc+"); return false;'/>" +
                    "</td>" +
                    "</tr></table>" +
                    "</div>"
                );

                $( "#posicion_" + contadorOc ).rules( "add", {
                    required: true,
                    digits: true,
                    messages: {
                      required: "Ingrese un valor",
                      digits: "Solo puede contener cantidades enteras (sin decimales)"
                    }
                });

                contadorOc++;
                limpiarProveedor();
                finished();
            } else {
                $('#mensajes').html(data);
                finished();
            }
        });
    }else{
        $('#mensajes').html("Para crear la OC debe haber al menos un producto");
    }
}

function limpiarProveedor(){
    $("#proveedor").val("");
    $("#cantidad").val("");
    $("#empaque").val("");
    $("#producto").val("");
    $("#kg").val("");
    $("#precio").val("");
    $("#total").val("");
    
    productosT = productos;
    
    for(i = 0; i < productosT; i++){
        eliminarProducto(i);
    }
    
}

function creandoCamion(){
    if(contadorOc > 0){
        var controlador = "WEB-INF/Controllers/compras/Controller_CrearCamion.php";
        if ($(formCamion).valid()) {
            loading("Cargando ...");
            $.post(controlador, {formCamion: $(formCamion).serialize(), contadorOc: contadorOc}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {
                    $("#mensajes").html(data);
                    $('#contenidos').load("compras/crearCamion.php", function() {
                        finished();
                    });
                } else {
                    $('#mensajes').html(data);
                    finished();
                }
            });
        }
    }else{
        $('#mensajes').html("Debe crear al menos una OC para el camión");
    }
}

function eliminarOc(oc){
    loading("Cargando ...");
    var controlador = "WEB-INF/Controllers/compras/Controller_CrearCamion.php";
    $.post(controlador, {id: $("#oc_"+oc).val()}).done(function(data) {
        if (data.toString().indexOf("Error:") === -1) {
            $("#div_oc_"+oc).remove();
            finished();
        }else{
            $('#mensajes').html(data);
            finished();
        }
    });
}

function calcularTotal(){
    if($("#precio").val() != "" && $("#kg").val() != ""){
        $("#total").val($("#precio").val() * $("#kg").val());
    }
}

function cargarEmpaque(){
    var componente = $("#producto").val();
    $.post("WEB-INF/Controllers/Ajax/cargaDivs.php", {componente: componente, empaque: true}).done(function(data){
        $("#empaque").val(data);
    });
}
var direccion = "Multi/list_empresas.php";
var contadorProductos = 0;

$(document).ready(function() {
    var form = "#formcliente";
    var controlador = "WEB-INF/Controllers/Multi/Controller_Empresa.php";
    $(".boton").button();/*Estilo de botones*/
    jQuery.validator.addMethod("imagen_seleccion", function(value, element) {
        if ($("#imagen_existe").length && $("#imagen_existe").val() == 1) {
            return true;
        } else {
            if ($("#logo").val() != "") {
                return true;
            } else {
                return false;
            }
        }
    }, '* Selecciona el logo');

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            RazonSocial: {required: true},
            TipoDomicilioF: {required: true},
            CalleF: {required: true},
            NoExteriorF: {required: true, number: true},
            NoInteriorF: {number: true},
            ColoniaF: {required: true},
            CiudadF: {required: true},
            EstadoF: {required: true},
            DelegacionF: {required: true},
            CPF: {required: true, number: true},
            RFCD: {required: true},
            cfdi: {required: true},
            pac: {required: true},
            regimenfiscal: {required: true},
            serie: {required: true},
            logo: {imagen_seleccion: true, accept: "jpg,png,jpeg"}
        },
        messages: {
            RazonSocial: {required: " * Ingrese la razón social"},
            TipoDomicilioF: {required: " * Seleccione el tipo de domicilio"},
            CalleF: {required: " *  Ingrese la calle"},
            NoExteriorF: {required: " *  Ingrese el No Exterior", number: " * Ingrese un número"},
            NoInteriorF: {number: " * Ingrese un número"},
            ColoniaF: {required: " *  Ingrese la colonia"},
            CiudadF: {required: " * Ingrese la ciudad"},
            EstadoF: {required: " *  Ingrese el Estado"},
            DelegacionF: {required: " * Ingrese la delecgación o municipio"},
            CPF: {required: " * Ingrese el Código Postal", number: " * Ingrese un número"},
            RFCD: {required: " * Ingrese el RFC"},
            cfdi: {required: " * Seleccione el CFDI"},
            pac: {required: " * Seleccione el PAC"},
            regimenfiscal: {required: " * Ingrese el régimen fiscal"},
            serie: {required: " * Seleccione una serie"},
            logo: {imagen_seleccion: " * Selecciona el logo", accept: " * Selecciona una imagen"}
        }
    });
    
    contadorProductos = $("#contadorProductos").val();
    for(var i = 0; i < contadorProductos; i++){
        addRulesProducto(i);
    }

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            var inputs = $("input[type=file]"),
                    files = [];
            for (var i = 0; i < inputs.length; i++) {
                files.push(inputs.eq(i).prop("files")[0]);
            }
            var formData = new FormData();
            $.each(files, function(key, value)
            {
                formData.append(key, value);
            });
            formData.append("contadorProductos",contadorProductos);
            formData.append('form', $(form).serialize());
            $.ajax({
                url: controlador,
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data, textStatus, jqXHR)
                {
                    if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                        cambiarContenidos(direccion, "Empresas");
                        $('#mensajes').html(data);
                    } else {
                        var mensaje = data.split("&&&&");
                        if(mensaje.length == 1){
                            cambiarContenidos(direccion, "Empresas");
                            $('#mensajes').html(data);
                        }else{
                            cambiarContenidos("Multi/EditarEmpresa.php?id="+mensaje[0], "Empresas");
                            $('#mensajes').html(mensaje[1]);
                        }
                    }
                    finished();
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                },
                complete: function()
                {
                }
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });        

    $('#logo').fileValidator({
        onValidation: function(files) {            
            $(".error_file").text("");
            $(this).attr('class', '');
        },
        onInvalid: function(type, file) {                    
            $(".error_file").text("Debes de elegir una imagen menor de 25kb");
            var control = $("#logo");
            control.replaceWith( control = control.clone( true ) );            
            $(this).addClass('invalid ' + type);
            return false;
        },
        maxSize: '25kb',
        type: 'image'
    });
});

function agregarProducto(){
    $("#tProductos").append(
        "<tr id='ar_" + contadorProductos + "'>" +
        "<td style='width:30%;' style='text-align: center;'>" +
            "<input style='width:80%;' type='text' id='producto_" + contadorProductos + "' name='producto_" + contadorProductos + "' />" +
        "</td>" +
        "<td style='width:30%;' style='text-align: center;'>" +
            "<input style='width:80%;' type='text' id='unidadmedida_" + contadorProductos + "' name='unidadmedida_" + contadorProductos + "' />" +
        "</td>" +
        "<td style='text-align: center;'>" +
        "<a onclick='eliminarProducto(" + contadorProductos + ")' style='cursor: pointer;'>" +
        "<img class='imagenMouse' src='resources/images/Erase.png' title='Nuevo' style='float: right; cursor: pointer;' /></a></td>" +
        "<td></td>" +
        "</tr>" +
        "<tr id='ar_" + contadorProductos + "'>" +
        "<td colspan=4>&nbsp</td>" +
        "</tr>" 
    );
    
    addRulesProducto(contadorProductos);
    contadorProductos++;
}

function eliminarProducto(elem){
    var fila = "ar_" + elem;
    var trs = $("#tProductos tr").length;
    deleteRulesProducto(elem);
    if (trs > 1) {
        $("#" + fila).remove();
        $("#" + fila).remove();
    }    
    contadorProductos--;
}

function addRulesProducto(contador){
    
    $("#unidadmedida_" + contador).autocomplete({
        source: function (request, response) {
            $.post("WEB-INF/Controllers/Ajax/CargaAutocomplete.php", {tipo_controlador: "UnidadMedida", Palabra: request['term']}).done(function (data) {
                //$('#unidad_medida').removeClass('ui-autocomplete-loading');  // hide loading image
                response($.parseJSON(data));
            });
        },
        minLength: 3
    });

    $("#producto_" + contador).autocomplete({
        source: function (request, response) {
            $.post("WEB-INF/Controllers/Ajax/CargaAutocomplete.php", {tipo_controlador: "ProductosSat", Palabra: request['term']}).done(function (data) {
                //$('#unidad_medida').removeClass('ui-autocomplete-loading');  // hide loading image
                response($.parseJSON(data));
            });
        },
        minLength: 3
    });
    
    $( "#producto_" + contador ).rules( "add", {
        required: true,
        messages: {
          required: "Ingresa un producto"
        }
    });
    $( "#unidadmedida_" + contador ).rules( "add", {
        required: true,
        messages: {
          required: "Ingresa una unidad de medida"
        }
    });
}

function deleteRulesProducto(contador){
    $( "#producto_" + contador ).rules( "remove" );
    $( "#unidadmedida_" + contador ).rules( "remove" );
}



$(document).ready(function() {
    var form = "#formcliente";
    var controlador = "WEB-INF/Controllers/Ventas/Controller_Nuevo_Cliente_pakal.php";
    var direccion = $("#regresar").val();
    
    $.validator.addMethod(
            "regex",
            function(value, element, regexp) {
                var re = new RegExp(regexp);
                return this.optional(element) || re.test(value);
            },
            "Por favor, valida que sea un RFC válido (no se permiten espacios ni guiones)"
    );
    
    $(".boton").button();/*Estilo de botones*/
    jQuery.validator.addMethod("imagen_seleccion", function(value, element) {
        if ($("#imagen_existe").length && $("#imagen_existe").val() == 1) {
            return true;
        } else {
            if ($("#imagen").val() != "") {
                return true;
            } else {
                return false;
            }
        }
    }, '* Selecciona una imagen');

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            EstatusCobranza: {required: true},
            RazonSocial: {required: true},
            ejecutivocuenta: {required: true},
            zona: {required:true},
            TipoCliente: {required: true},
            Giro: {required: true},
            razon_cliente2: {required: true},
            TipoDomicilioF: {required: true},
            CalleF: {required: true},
            NoExteriorF: {required: true},
            ColoniaF: {required: true},
            CiudadF: {required: true},
            EstadoF: {required: true},
            DelegacionF: {required: true},
            CPF: {required: true, number:true},
            RFCD: {required: true},
            latitud: {number: true, maxlength: 12},
            longitud: {number: true, maxlength: 12},
            imagen: {imagen_seleccion: true, accept: "jpg,png,jpeg"}
        },
        messages: {
            EstatusCobranza: {required: " * Seleccione el estatus"},
            ejecutivocuenta: {required: " * Seleccione el vendedor"},
            zona: {required:" * Seleccione la zona"},
            RazonSocial: {required: " * Ingrese el nombre del cliente"},
            TipoCliente: {required: " * Seleccione el tipo de cliente"},
            Giro: {required: " * Seleccione el giro de la empresa"},
            razon_cliente2: {required: " * Seleccione la razón social"},
            TipoDomicilioF: {required: " * Seleccione el tipo de domicilio"},
            CalleF: {required: " *  Ingrese la calle"},
            NoExteriorF: {required: " *  Ingrese el No Exterior"},
            ColoniaF: {required: " *  Ingrese la colonia"},
            CiudadF: {required: " * Ingrese la ciudad"},
            EstadoF: {required: " *  Ingrese el Estado"},
            DelegacionF: {required: " * Ingrese la delecgación o municipio"},
            CPF: {required: " * Ingrese el Código Postal",number: " * Ingrese un número"},
            RFCD: {required: " * Ingrese el RFC"},
            latitud: {number: " * Ingrese un número", maxlength: " * Ingrese un máximo de {0} números"},
            longitud: {number: " * Ingrese un número", maxlength: " * Ingrese un máximo de {0} números"},
            imagen: {imagen_seleccion: " * Selecciona una imagen", accept: " * Selecciona una imagen"}
        }
    });
    
    $("#RFCD").rules("add", { regex: /^([A-Za-zÑ\x26]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1]))([A-Za-z\d]{3})$/ });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                 if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/   
                    cambiarContenidos(direccion, "Mis Clientes");
                    $('#mensajes').html(data);
                } else {
                    $('#mensajes').html(data);
                }
                finished();
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });
});

$('#imagen').fileValidator({
    onValidation: function(files) {            
        $(".error_file").text("");
        $(this).attr('class', '');
    },
    onInvalid: function(type, file) {                    
        $(".error_file").text("Debes de elegir una imagen menor de 25kb");
        var control = $("#imagen");
        control.replaceWith( control = control.clone( true ) );            
        $(this).addClass('invalid ' + type);
        return false;
    },
    maxSize: '25kb',
    type: 'image'
});

function agregarCategoria() {
    var numero = $("#numero_categoria").val();
    numero++;

    $("#t_datos_categorias").append("<tr id='row_" + numero + "'>" +
            "<td>Categoria</td>" +
            "<td><select id='categoria"+numero+"' name='categoria"+numero+"'></select></td>" +
            "<td><input type=\"image\" src=\"resources/images/add.png\" title=\"Agregar otra categoría\" onclick=\"agregarCategoria(); return false;\" /></td>" +
            "<td><input type='image' id='erase"+numero+"' src='resources/images/Erase.png' title='Eliminar este periodo' onclick='borrarCategoria(" + numero + "); return false;'/></td>" +
            "</td></tr>");   
    /*Copiamos los tipos de componentes*/
    var $options = $("#categoria1 > option").clone();
    $('#categoria' + numero).append($options);
    
    $("#numero_categoria").val(numero);
}

function borrarCategoria(fila) {            
    var trs = $("#t_datos_categorias tr").length;
    var contador = $("#numero_categoria").val();            
    if(fila > contador){
        fila = contador;        
    }
    var row = 'row_' + fila;
    if (trs > 1) {//Si hay filas en la tabla        
        $("#" + row).remove();
        //$("#" + row).rules("remove");
        for (var i = (fila + 1); i <= contador; i++) {
            if ($("#categoria" + i).length) {
                $('#categoria' + i).attr('id', function() {
                    return 'categoria' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'categoria' + (i - 1);  // change name
                });
            }                                    

            if ($("#row_" + i).length) {
                $('#row_' + i).attr('id', function() {
                    return 'row_' + (i - 1);  // change id
                });
            }
        }
        $("#numero_categoria").val(contador - 1);
    }
}

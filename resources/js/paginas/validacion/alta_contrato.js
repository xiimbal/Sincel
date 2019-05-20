$(document).ready(function() {
    var form = "#formContrato";
    var controlador = "WEB-INF/Controllers/Validacion/Controler_Contrato.php";
    if ($("#independiente").length) {
        controlador = "../WEB-INF/Controllers/Validacion/Controler_Contrato.php";
    }

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            clave_contrato2: {required: true, maxlength: 50, minlength: 3},
            //fecha_ini2: {required: true},
            //fecha_fin2: {required: true},
            Calle: {required: true},
            NoExterior: {required: true},
            Colonia: {required: true},
            Estado: {required: true},
            Delegacion: {required: true},
            CP: {required: true},
            /*forma_pago: {required: true},
            metodo_pago: {required: true},
            usoCFDI: {required: true},*/
            razon_social: {required: true},
            estadoContrato:{required: true},
            dias_credito:{required:true}
        },
        messages: {
            clave_contrato2: {required: " * Ingrese la clave del contrato", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            fecha_ini2: {required: " * Ingrese la fecha de inicio"},
            fecha_fin2: {required: " * Ingrese la fecha final"},
            Calle: {required: " * Ingrese la calle"},
            NoExterior: {required: " * Ingrese el número"},
            Colonia: {required: " * Ingrese la colonia"},
            Estado: {required: " * Seleccione el estado"},
            Delegacion: {required: " * Ingrese la delegación"},
            CP: {required: " * Ingrese el código postal"},
            /*forma_pago: {required: " * Selecciona la forma de pago"},
            metodo_pago: {required: " * Selecciona el método de pago"},
            usoCFDI: {required: " * Selecciona el uso de cfdi"},*/
            razon_social: {required: " * Selecciona la razón social"},
            estadoContrato:{required: " * Selecciona un estado"},
            dias_credito:{required: " *Ingresa d&iacute;as de cr&eacute;dito"}
        }
    });

    $(".fecha").mask("9999-99-99");
    /*Prevent form*/
    $(form).submit(function(event) {
        /* stop form from submitting normally */
            event.preventDefault();
            
        if($("#estadoContrato").val() == 7 || $("#estadoContrato").val() == 3){
            //agregar fechas obligatorias
            $("#fecha_ini2").rules("add", "required");
            $("#fecha_fin2").rules("add", "required");
        }else{
            //quitar fechas obligatorias
            $("#fecha_ini2").rules("remove", "required");
            $("#fecha_fin2").rules("remove", "required");
        }
        if ($(form).valid()) {
            if($("#estadoContrato").val() == 2){
                alert("Se pondrá el tipo de cliente como moroso.");
            }
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                    $('#mensaje_contrato2').html("El contrato se guard\u00f3  correctamente");
                    $("#cancelar_contrato").trigger("click");/*llamamos el evento onclick del boton cancelar para que regrese a la pantalla de lista*/
                } else {
                    $('#mensaje_contrato2').html(data);
                }
            });
        }
    });
});

function agregarConcepto() {
    var numero = $("#numero_conceptos").val();
    numero++;

    $("#tcontrato").append("<tr id='row_" + numero + "'>" +            
            "<td style='text-align:center'><input type='text' id='campo_"+numero+"' name='campo_"+ numero+"'/></td>" +
            "<td style='text-align:center'><input type='text' id='valor_"+numero+"' name='valor_"+ numero+"'/></td>" +
            "<td style='text-align:center'><input type='checkbox' id='mostrar_"+numero+"' name='mostrar_"+ numero+"'/></td>" +
            "<td style='text-align:center'><input type=\"image\" src=\"../resources/images/add.png\" title=\"Agregar otro concepto\" onclick=\"agregarConcepto(); return false;\" /></td>" +
            "<td><input type='image' id='erase"+numero+"' src='../resources/images/Erase.png' title='Eliminar este concepto' onclick='borrarConcepto(" + numero + "); return false;'/></td>" +
            "</tr>");     
    
    $("#campo_" + numero).rules('add', {
        required: true, maxlength: 50, minlength: 1,
        messages: {
            required: " * Ingrese el nombre del concepto", maxlength: " * Ingrese un máximo de {0} números", minlength: " * Ingrese un minímo de {0} números"
        }
    });
    
    $("#valor_" + numero).rules('add', {
        required: true, maxlength: 50, minlength: 1,
        messages: {
            required: " * Ingrese el valor del concepto", maxlength: " * Ingrese un máximo de {0} números", minlength: " * Ingrese un minímo de {0} números"
        }
    });
    
    $("#numero_conceptos").val(numero);
}

function borrarConcepto(fila) {            
    var trs = $("#tcontrato tr").length;
    var contador = $("#numero_conceptos").val();            
    if(fila > contador){
        fila = contador;        
    }
    var row = 'row_' + fila;
    if (trs > 1) {//Si hay filas en la tabla        
        $("#" + row).remove();
        //$("#" + row).rules("remove");
        for (var i = (fila + 1); i <= contador; i++) {
            if ($("#campo_" + i).length) {
                $('#campo_' + i).attr('id', function() {
                    return 'campo_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'campo_' + (i - 1);  // change name
                });
            }   
            
            if ($("#valor_" + i).length) {
                $('#valor_' + i).attr('id', function() {
                    return 'valor_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'valor_' + (i - 1);  // change name
                });
            }

            if ($("#row_" + i).length) {
                $('#row_' + i).attr('id', function() {
                    return 'row_' + (i - 1);  // change id
                });
            }
        }
        $("#numero_conceptos").val(contador - 1);
    }
}

$(document).ready(function() {
    var form = "#formServiciosEspeciales";
    var controlador = "WEB-INF/Controllers/Validacion/Controler_ServiciosEspeciales.php";
    if ($("#independiente").length) {
        controlador = "../WEB-INF/Controllers/Validacion/Controler_ServiciosEspeciales.php";
    }

    $('.boton').button().css('margin-top', '20px').css('font-size', '13px');
    
    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            nombre_1: {maxlength: 50, minlength: 1},
            precio_1: {number:true}
        },
        messages: {
            nombre_1: {maxlength: " * Ingrese un máximo de {0} caracteres", minlength: " * Ingrese un minímo de {0} caracteres" },
            precio_1: {number: "* Ingrese solo numeros"}
        }
    });

    $(".fecha").mask("9999-99-99");
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /*Obtenemos los datos para después de que se haga el proceso de los datos*/
            var div = "";
            if ($("#div_pagina").length) {
                div = $("#div_pagina").val();
            }

            var pagina = "";
            if ($("#pagina").length) {
                pagina = $("#pagina").val();
            }

            var anexo = "";
            if ($("#claveAnexo").length) {
                anexo = $("#claveAnexo").val();
            }

            var cc = "";
            if ($("#cc").length) {
                cc = $("#cc").val();
            }

            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function (data) {

                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                    $('#mensajes').html("El servicio se registro correctamente");
                    if (div != "") {
                        if ($("#independiente").length) {
                            cargarDependencia(div, "../cliente/validacion/" + pagina, anexo, null, cc);
                        } else {
                            cargarDependencia(div, "ventas/validacion/" + pagina, anexo, null, cc);
                        }
                    } else {
                        $("#cancelar_servicio").trigger("click");/*llamamos el evento onclick del boton cancelar para que regrese a la pantalla de lista*/
                    }
                } else {
                    $('#mensajes').html(data);
                }
            });
        }
    });
});

function agregarConcepto() {
    var numero = $("#numero_conceptos").val();
    numero++;

    $("#tserviciosEspeciales").append("<tr id='row_" + numero + "'>" +            
            "<td style='text-align:center'><input type='text' id='nombre_"+numero+"' name='nombre_"+ numero+"' style='width: 80%;'/></td>" +
            "<td style='text-align:center'><input type='text' id='precio_"+numero+"' name='precio_"+ numero+"' style='width: 60%;'/></td>" +
            "<td><select id='tarifa_" + numero +"' name='tarifa_" + numero +"' class='select' style='width: 80%;'></select></td>" +
            "<td style='text-align:center'><input type='checkbox' id='variable_"+numero+"' name='variable_"+ numero+"'/></td>" +
            "<td><select id='estado_" + numero +"' name='estado_" + numero +"' class='select' style='width: 90%;'></select></td>" +
            "<td style='text-align:center'><input type=\"image\" src=\"../resources/images/add.png\" title=\"Agregar otro servicio\" onclick=\"agregarConcepto(); return false;\" /></td>" +
            "<td><input type='image' id='erase"+numero+"' src='../resources/images/Erase.png' title='Eliminar este servicio' onclick='borrarConcepto(" + numero + "); return false;'/></td>" +
            "</tr>");     
    
     /*Copiamos los tipos de componentes*/
    var $options = $("#estado_1 > option").clone();
    $('#estado_' + numero).append($options);
    
    var $options = $("#tarifa_1 > option").clone();
    $('#tarifa_' + numero).append($options);
    
    $("#nombre_" + numero).rules('add', {
        required: true, maxlength: 50, minlength: 1,
        messages: {
            required: " * Ingrese el nombre del servicio", maxlength: " * Ingrese un máximo de {0} caracteres", minlength: " * Ingrese un minímo de {0} caracteres"
        }
    });
    
    $("#precio_" + numero).rules('add', {
        maxlength: 50, minlength: 1, number: true,
        messages: {
            required: " * Ingrese el precio del servicio", maxlength: " * Ingrese un máximo de {0} números", minlength: " * Ingrese un minímo de {0} números", number: " * Solo ingresa números ",
        }
    });
    
    $("#estado_" + numero).rules('add', {
        required: true,
        messages: {
            required: " * Selecciona un elemento de la lista"
        }
    });
    
    $("#numero_conceptos").val(numero);
}

function borrarConcepto(fila) {            
    var trs = $("#tserviciosEspeciales tr").length;
    var contador = $("#numero_conceptos").val();            
    if(fila > contador){
        fila = contador;        
    }
    var row = 'row_' + fila;
    if (trs > 1) {//Si hay filas en la tabla  
        if ($("#nombre_" + fila).length) {
            $("#nombre_" + fila).rules("remove");
        }
        
        if ($("#precio_" + fila).length) {
            $("#precio_" + fila).rules("remove");
        }
        
        if ($("#estado_" + fila).length) {
            $("#estado_" + fila).rules("remove");
        }
        $("#" + row).remove();                        
        for (var i = (fila + 1); i <= contador; i++) {
            if ($("#nombre_" + i).length) {
                $('#nombre_' + i).attr('id', function() {
                    return 'nombre_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'nombre_' + (i - 1);  // change name
                });
            }   
            
            if ($("#precio_" + i).length) {
                $('#precio_' + i).attr('id', function() {
                    return 'precio_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'precio_' + (i - 1);  // change name
                });
            }
            
            if ($("#tarifa_" + i).length) {
                $('#tarifa_' + i).attr('id', function() {
                    return 'tarifa_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'tarifa_' + (i - 1);  // change name
                });
            }
            
            if ($("#variable_" + i).length) {
                $('#variable_' + i).attr('id', function() {
                    return 'variable_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'variable_' + (i - 1);  // change name
                });
            }
            
            if ($("#estado_" + i).length) {
                $('#estado_' + i).attr('id', function() {
                    return 'estado_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'estado_' + (i - 1);  // change name
                });
            }
            
            if ($("#erase" + (i)).length) {//Campo de costo
                $('#erase' + i).attr('id', function() {
                    return 'erase' + (i - 1);  // change id
                });                                
                $("#erase"+(i-1)).attr("onclick","borrarConcepto("+(i-1)+"); return false;");
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



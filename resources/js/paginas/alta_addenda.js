var direccion = "admin/lista_addenda.php";
$(document).ready(function() {
    var form = "#form_addenda";
    var controlador = "WEB-INF/Controllers/Controler_Addenda.php";
    $(".boton").button();/*Estilo de botones*/
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            nombre_addenda: {required: true, maxlength: 50, minlength: 2},
            nombre_1: {required: true, maxlength: 50, minlength: 1},
            valor_1: {required:true, maxlength: 50, minlength: 1}
        },
        messages: {
            nombre_addenda: {required: " * Ingrese el nombre de la addenda", maxlength: " * Ingrese un máximo de {0} números", minlength: " * Ingrese un minímo de {0} números"},
            nombre_1: {required: " * Ingrese el nombre del concepto", maxlength: " * Ingrese un máximo de {0} caracteres", minlength: " * Ingrese un minímo de {0} caracteres"},
            valor_1: {required: " * Ingrese el valor del valor", maxlength: " * Ingrese un máximo de {0} caracteres", minlength: " * Ingrese un minímo de {0} caracteres"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Guardando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                 if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/   
                    cambiarContenidos(direccion);
                    $('#mensajes').html(data);
                } else {
                    $('#mensajes').html(data);
                }
                finished();
            });            
            $("#divinfo").empty();
        }
    });
});

function agregarConcepto() {
    var numero = $("#numero_conceptos").val();
    numero++;

    $("#t_datos_addenda").append("<tr id='row_" + numero + "'>" +            
            "<td><input type='text' id='nombre_"+numero+"' name='nombre_"+ numero+"'/></td>" +
            "<td><input type='text' id='valor_"+numero+"' name='valor_"+ numero+"'/></td>" +
            "<td><input type='checkbox' id='dinamico_"+numero+"' name='dinamico_"+numero+"' value='on'/>Variable por factura</td>"+
            "<td><input type=\"image\" src=\"resources/images/add.png\" title=\"Agregar otro concepto\" onclick=\"agregarConcepto(); return false;\" /></td>" +
            "<td><input type='image' id='erase"+numero+"' src='resources/images/Erase.png' title='Eliminar este concepto' onclick='borrarConcepto(" + numero + "); return false;'/></td>" +
            "</td></tr>");     
    
    $("#nombre_" + numero).rules('add', {
        required: true, maxlength: 50, minlength: 1,
        messages: {
            required: " * Ingrese el nombre del concepto", maxlength: " * Ingrese un máximo de {0} números", minlength: " * Ingrese un minímo de {0} números"
        }
    });
    
    $("#valor_" + numero).rules('add', {
        required: true, maxlength: 50, minlength: 1,
        messages: {
            required: " * Ingrese el nombre del concepto", maxlength: " * Ingrese un máximo de {0} números", minlength: " * Ingrese un minímo de {0} números"
        }
    });
    
    $("#numero_conceptos").val(numero);
}

function borrarConcepto(fila) {            
    var trs = $("#t_datos_addenda tr").length;
    var contador = $("#numero_conceptos").val();            
    if(fila > contador){
        fila = contador;        
    }
    var row = 'row_' + fila;
    if (trs > 1) {//Si hay filas en la tabla        
        $("#" + row).remove();
        //$("#" + row).rules("remove");
        for (var i = (fila + 1); i <= contador; i++) {
            if ($("#nombre_" + i).length) {
                $('#nombre_' + i).attr('id', function() {
                    return 'nombre_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'nombre_' + (i - 1);  // change name
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

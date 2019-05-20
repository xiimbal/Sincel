var rango=0;
$(document).ready(function() {
    var form = "#form_pp";
    var controlador = "../WEB-INF/Controllers/facturacion/Controller_PagoParcial.php";
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
    $("#fecha").datepicker({
        dateFormat: 'yy-mm-dd',
        changeYear: true,
        changeMonth: true,
        maxDate: '+0D'
    });
    
    jQuery.validator.addMethod("mynumber", function(value, element) {
        return this.optional(element) || /^\d+(\.\d{0,3})?$/.test(value);
    }, "Ingresa correctamente el número");
    
    jQuery.validator.addMethod("rango", function(value, element) {
        if ($("#importe").val() > rango) {
            return false;
        } else {
            return true;
        }
    }, "El importe no debe superar el total");
    
    /*jQuery.validator.addMethod("maximo_pago", function(value, element) {
        if (parseFloat($("#importe").val()) < parseFloat($("#por_pagar").val())) {
            return false;
        } else {
            return true;
        }
    }, "El importe no debe ser mayor al importe por pagar "+$("#por_pagar").val());*/
    
    $(form).validate({
        errorClass: "my-error-class",
        rules: {            
            importe: {mynumber: true, rango: true, min:0},
            fecha: {required: true}
        },
        messages: {            
            fecha: {required: " * Selecciona la fecha", min:" * El valor mínimo es {0}"}
        }
    });
    
    if($("#localidades").length){
        $("#localidades").multiselect({
            multiple: true,
            noneSelectedText: "No ha seleccionado",
            checkAllText: "Seleccionar todo",
            uncheckAllText: "Deseleccionar todo",
            selectedList: 3,
            selectedText: "# seleccionados",
            minWidth: 125
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
    }
    
    if($("#saldoFavor").length){
        
        jQuery.validator.addMethod("importeSaldo", function(value, element) {
            if (parseFloat($("#cantidadSaldo").val()) > parseFloat($("#saldoFavor").val())) {
                return false;
            } else {
                return true;
            }
        }, "El importe de saldo no debe superar el saldo a Favor");
        
        jQuery.validator.addMethod("importeMayorSaldo", function(value, element) {
            if (parseFloat($("#cantidadSaldo").val()) > parseFloat($("#por_pagar").val())) {
                return false;
            } else {
                return true;
            }
        }, "Si paga con saldo a favor, no puede superar la cantidad a pagar");
        
        $("#importe").rules("remove");   
        $("#cantidadSaldo").rules("add", {
            number: true,
            importeSaldo: true,
            importeMayorSaldo: true,
            messages: { number: "Ingrese un número válido",
                importeSaldo: "El importe de saldo no debe superar el saldo a Favor", 
                importeMayorSaldo: "Si paga con saldo a favor, no puede superar la cantidad a pagar"}
        });
    }
    
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            
            $("#submit_equipo").hide();
            $("#cargando").show();
            /* stop form from submitting normally */
            event.preventDefault();
            //Verificamos si el pago actual, hace que el pago total sea mayor al monto total de la factura
            var pago = 0;
            var importeT = 0;
            
            if($("#importe").length && parseInt($("#importe").val()) > 0){
                importeT += parseInt($("#importe").val());
            }
            if($("#cantidadSaldo").length && parseInt($("#cantidadSaldo").val()) > 0){
                importeT += parseInt($("#cantidadSaldo").val());
            }
            
            if($("#pago").length){//Si se esta editando un pago
                pago = $("#pago").val();
            }
            
            $("#contenidos_invisibles").load(controlador, {'comprueba_pago':true, 'idFactura':$("#factura").val(), 
                'pago':importeT,'idPago':pago}, function(data){
                
                if (data.toString() == "") {//el pago no es mayor al total
                    /*Serialize and post the form*/
                    $.post(controlador, {form: $(form).serialize()}).done(function(data) {                            
                        if (data.toString().indexOf("Error:") === -1) {
                            if(!$("#cxc_activo").length){
                                $('#mensajes').html(data);
                                setTimeout(function(){
                                    cambiarContenidos('list_pago_parcial.php?factura=' + $("#idpp").val(), "Pago Parcial");
                                    $("#submit_equipo").show();
                                    $("#cargando").hide();
                                },4500);                         
                            }else{
                                $('#mensajes').html(data);
                                setTimeout(function(){
                                    cambiarContenidos('list_pago_parcial.php?factura=' + $("#idpp").val()+"&cxc=true", "Pago Parcial");
                                    $("#submit_equipo").show();
                                    $("#cargando").hide();
                                },4500);                        
                            }                        
                        } else {
                            $('#mensajes').html(data);
                            $("#submit_equipo").show();
                            $("#cargando").hide();
                        }
                    });
                }else{
                    if(confirm(data)){
                        /*Serialize and post the form*/
                        $.post(controlador, {form: $(form).serialize()}).done(function(data) {                
                            if (data.toString().indexOf("Error:") === -1) {
                                if(!$("#cxc_activo").length){
                                    $('#mensajes').html(data);
                                    setTimeout(function(){
                                        cambiarContenidos('list_pago_parcial.php?factura=' + $("#idpp").val(), "Pago Parcial");
                                        $("#submit_equipo").show();
                                        $("#cargando").hide();
                                    },10000);                         
                                }else{
                                    $('#mensajes').html(data);
                                    setTimeout(function(){
                                        cambiarContenidos('list_pago_parcial.php?factura=' + $("#idpp").val()+"&cxc=true", "Pago Parcial");
                                        $("#submit_equipo").show();
                                        $("#cargando").hide();
                                    },10000);                        
                                }                        
                            } else {
                                $('#mensajes').html(data);
                                $("#submit_equipo").show();
                                $("#cargando").hide();
                            }
                        });
                    }else{
                        
                    }
                }
            });
            
        }
    });
    cambiarTipoCadena();
    verificarCtaOrdenante();
});

function addrange(id) {
    rango=id;
}

function cambiarTipoCadena(){
    if($("#TipoCadPago").val() != ""){
        $(".tipo_cadena").show();
        $("#CertPago").rules('add', {
            required: true,
            messages: {
                required: " * Ingresa el certificado del pago"
            }
        });
        $("#CadPago").rules('add', {
            required: true,
            messages: {
                required: " * Ingresa la cadena del pago"
            }
        });
        $("#SelloPago").rules('add', {
            required: true,
            messages: {
                required: " * Ingresa el sello del pago"
            }
        });
    }else{
        $(".tipo_cadena").hide();
        $("#CertPago").rules('remove');
        $("#CadPago").rules('remove');
        $("#SelloPago").rules('remove');        
    }
}

function verificarRFCEmisor(){
    if($("#RFCBancoEmisor").val() == "XEXX010101000"){
        $("#NombreBancoEmisor").rules('add', {
            required: true,
            messages: {
                required: " * El nombre del banco es obligatorio cuando se usa el RFC genérico"
            }
        });
    }
}

function verificarCtaOrdenante(){
    if($("#forma_pago").val() != 2 && $("#forma_pago").val() != 1 && $("#forma_pago").val() != 4 
        && $("#forma_pago").val() != 7 && $("#forma_pago").val() != 10 && $("#forma_pago").val() != 5
        && $("#forma_pago").val() != 6){
        $("#ClaveInterbancariaEmisor").prop('disabled', true);
    }else{
        $("#ClaveInterbancariaEmisor").prop('disabled', false);
    }
}
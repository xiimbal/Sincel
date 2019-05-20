var rango=0;
//var gggg = "AMMMMM";
$(document).ready(function(){
    var form = "#form_ppagos";
    var controlador = "../WEB-INF/Controllers/facturacion/Controller_PagosParciales.php";
    $(".boton").button();
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
    
    /*jQuery.validator.addMethod("rango", function(value, element) {
        if ($("#importe").val() > rango) {
            return false;
        } else {
            return true;
        }
    }, "El importe no debe superar el total");*/
    
    $.validator.addMethod("ctaBancaria", function(value, element, paramtrs) {  
        if(value.length != 0){
            return $.inArray(value.length,paramtrs) >= 0;
        }
        return true;
    }, " * Verifique el patrón para la cuenta en el catálogo de pagos del SAT");
    $(form).validate({
        errorClass: "my-error-class",
        rules: {            
            //importe: {mynumber: true, rango: true, min:0},
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
    $("#submit_equipo").click(function(){
        $("#submit_equipo").hide();
        $("#cargando").show();
        /* stop form from submitting normally */
        event.preventDefault();
        
        var abonoFacturas = "";
        var contador = parseInt((document.getElementById("num_folios").value));
        var importeTFac = "";
        var facturas= $("#factura").val();
        var arr_facturas = facturas.split('_');
        for ( var i = 0; i < contador; i++) {
            if ($('#abonado_'+i).length) {      
                abonoFacturas = abonoFacturas + $("#abonado_"+i).val()+"_";
                //Verificamos si el pago actual, hace que el pago total sea mayor al monto total de la factura
                var pago = 0;
                var importeT = 0;
                if($("#abonado_"+i).length && parseInt($("#abonado_"+i).val()) > 0){
                    importeT += parseInt($("#abonado_"+i).val());
                }
                if($("#pago").length){//Si se esta editando un pago
                    pago = $("#pago").val();
                }
                if($("#cantidadSaldo").length && parseInt($("#cantidadSaldo").val()) > 0){
                    importeT += parseInt($("#cantidadSaldo").val());
                }
                importeTFac = importeTFac + importeT.toString()+"_";
            }else{
                console.log('esta vacio ves');
            }
        }

            $("#contenidos_invisibles").load(controlador, {'comprueba_pago':true,'idFacturas':$("#factura").val(),  
                    'pago':importeTFac,'idpago':pago,'abonoFacturas':abonoFacturas}, function(data){
                
                    if (data.toString() == "") {//el pago no es mayor al total
                        /*Serialize and post the form*/
                        console.log("Si el pago es menor al total");
                        //$.post(controlador, {form: $(form).serialize()}).done(function(data) {
                        $.post(controlador, {form: $(form).serialize()}).done(function(data) {                             
                            if (data.toString().indexOf("Error:") === -1) {
                                if(!$("#cxc_activo").length){
                                    $('#mensajes').html(data);

                                    setTimeout(function(){
                                        cambiarContenidos('list_pagos_parciales.php?factura=' + $("#factura").val()+"&abonoFacturas="+abonoFacturas, "Lista de pagos multiples");
                                        $("#submit_equipo").show();
                                        $("#cargando").hide();
                                    },4500);
                                    //console.log("entramos al primer if del 1er if del primer if ");
                                    
                                }else{
                                    $('#mensajes').html(data);
                                     setTimeout(function(){
                                       cambiarContenidos('list_pagos_parciales.php?factura=' + $("#factura").val()+"&cxc=true"+"&abonoFacturas="+abonoFacturas, "Lista de pagos multiples");
                                        $("#submit_equipo").show();
                                        $("#cargando").hide();
                                    },4500);
                                    //console.log("entramos al else del primer if del 1er if del primer if");
                                    
                                    //console.log( $(form).serialize() );                        
                                }                        
                            } else {
                                $('#mensajes').html(data);
                                $("#submit_equipo").show();
                                $("#cargando").hide();
                                console.log("Entramos al else del 1er if del primer if");
                            }
                        });
                        //}
                    }else{
                        console.log("Si el pago es mayor al total");
                        if(confirm(data)){
                            //console.log("entramos al if de confirmacion");
                            /*Serialize and post the form*/
                            
                            $.post(controlador, {form: $(form).serialize()}).done(function(data) {                
                                if (data.toString().indexOf("Error:") === -1) {
                                    if(!$("#cxc_activo").length){
                                        $('#mensajes').html(data);
                                        setTimeout(function(){
                                            cambiarContenidos('list_pagos_parciales.php?factura=' +  $("#factura").val()+"&abonoFacturas="+$abonoFacturas, "Lista de pagos multiples");
                                            $("#submit_equipo").show();
                                            $("#cargando").hide();
                                            //},10000);                         
                                        },4500);
                                        //console.log("entramos al IF del if del if");
                                    }else{
                                        //console.log("entramos al ELSE del if del if");
                                        $('#mensajes').html(data);
                                        setTimeout(function(){
                                            cambiarContenidos('list_pagos_parciales.php?factura=' + $("#factura").val()+"&cxc=true"+"&abonoFacturas="+abonoFacturas, "Lista de pagos multiples");
                                            $("#submit_equipo").show();
                                            $("#cargando").hide();
                                            //},10000);
                                        },4500);
                                    }                        
                                } else {
                                    console.log("entramos al ELSE del if");
                                    $('#mensajes').html(data);
                                    $("#submit_equipo").show();
                                    $("#cargando").hide();
                                }
                            });
                            //}
                        }else{
                            
                        }
                    }
                      
            });
        
    });
    cambiarTipoCadena();
    verificarCtaOrdenante();
});

function Abonos(){
    var abonoFacturas = "";
            var contador = parseInt((document.getElementById("num_folios").value));
            //var contador = $("#num_folios").value; 
            for ( var i = 0; i < contador; i++) {
                if ($('#abonado_'+i).length) {
                    abonoFacturas = abonoFacturas + $("#abonado_"+i).val()+"_";
                    //i = i+1;
                }else{
                    console.log('esta vacio ves');
                }
            }
            console.log('abonos'+abonoFacturas);
}

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

function reglasCtaOrdenate(){
    $("#ClaveInterbancariaEmisor").rules('remove');
    if($("#forma_pago").val() == 2){    //Cheque nominativo
        $("#ClaveInterbancariaEmisor").rules('add', {
            ctaBancaria: [11,18]
        });
    }else if($("#forma_pago").val() == 1){  //Transferencia electrónica de fondos
        $("#ClaveInterbancariaEmisor").rules('add', {
            ctaBancaria: [10,16,18]
        });
    }else if($("#forma_pago").val() == 4){  //Tarjeta de crédito
        $("#ClaveInterbancariaEmisor").rules('add', {
            ctaBancaria: [16]
        });
    }else if($("#forma_pago").val() == 5){  //Monedero electrónico
        $("#ClaveInterbancariaEmisor").rules('add', {
            minlength: 3,
            maxlength: 50,
            messages: {
                minlength: " * Verifique el patrón para la cuenta en el catálogo de pagos del SAT",
                maxlength: " * Verifique el patrón para la cuenta en el catálogo de pagos del SAT"
            }
        });
    }else if($("#forma_pago").val() == 7){  //Dinero electrónico
        $("#ClaveInterbancariaEmisor").rules('add', {
            ctaBancaria: [10]
        });
    }else if($("#forma_pago").val() == 5){  //Tarjeta de débito
        $("#ClaveInterbancariaEmisor").rules('add', {
            ctaBancaria: [16]
        });
    }else if($("#forma_pago").val() == 6){  //Tarjeta de servicios
        $("#ClaveInterbancariaEmisor").rules('add', {
            ctaBancaria: [15,16]
        });
    }
}

function verificarCtaOrdenante(){
    if($("#forma_pago").val() != 2 && $("#forma_pago").val() != 1 && $("#forma_pago").val() != 4 
        && $("#forma_pago").val() != 7 && $("#forma_pago").val() != 10 && $("#forma_pago").val() != 5
        && $("#forma_pago").val() != 6 && $("#forma_pago").val() != 8){
        $("#ClaveInterbancariaEmisor").prop('disabled', true);
    }else{
        $("#ClaveInterbancariaEmisor").prop('disabled', false);
        reglasCtaOrdenate();
    }
}

function hola() {
    var num_folios = parseInt((document.getElementById("num_folios").value));

    if ((document.getElementById("importe").value)==""){
        alert("Agregue un importe");
    }else{
        var pagado = parseFloat((document.getElementById("importe").value));
        for (var var_1 = 0; var_1<num_folios; var_1++) {
            //var var_1 = 0;
            var fo = "facturado_"+var_1;
            var ab = "abonado_"+var_1;
            console.log("FO: "+fo+"\nAB: "+ab);
            console.log("NumFol: "+num_folios);

            var st_facturado =(document.getElementById(fo).value).replace(",","");
            var st_facturado =st_facturado.replace("$","");
            var fl_fac =  parseFloat(st_facturado);
            console.log("Facturado: "+pagado);

            if (pagado <= 0) {
                document.getElementById(ab).value = "0";
                console.log("IF 0");
            }else if (pagado >= fl_fac) {
                document.getElementById(ab).value = fl_fac;
                pagado = pagado - fl_fac;
                console.log("IF");
            }else if (pagado < fl_fac){
                document.getElementById(ab).value = (pagado);
                pagado = pagado - fl_fac;
                console.log("ELSE");
            }
        }
    }
}
function suma() {
      var add = 0;
      $('.cl').each(function() {
          if (!isNaN($(this).val())) {
              add += Number($(this).val());
          }
      });
      $('#importe').val(add);
  };


var oTable;
$(document).ready(function() {
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
    oTable = $('#tcc').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25,
        "width":100
        /*"sDom": '<"H"lTfr>t<"F"ip>',
        "oTableTools": {
            "sSwfPath": "resources/media/swf/copy_cvs_xls_pdf.swf",
            "aButtons": [
                {'sExtends': 'copy', 'sMessage': 'Copiar', 'sButtonText': 'Copiar', 'sButtonClass': "boton_tabla"},
                {
                    "sExtends": "pdf",
                    "sFileName": "SICOP.pdf",
                    "bSelectedOnly": true
                }
            ]
        }*/
    });

    // PCM 12/02/2019
    oTable = $('#tcc0').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25,
        "width":100        
    });
    oTable = $('#tcc1').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25,
        "width":100        
    });
    oTable = $('#tcc2').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25,
        "width":100        
    });
    oTable = $('#tcc3').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25,
        "width":100        
    });
    oTable = $('#tcc4').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25,
        "width":100        
    });
    oTable = $('#tcc5').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25,
        "width":100        
    });
    oTable = $('#tcc6').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25,
        "width":100        
    });
    oTable = $('#tcc7').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25,
        "width":100        
    });
    // PCM 12/02/2019

});

function eliminarPP(id,cxc){
    if (confirm("¿Esta seguro que eliminar el pago parcial?")) {
        loading("Actualizando y cargando ...");
        if(cxc == ""){
            $.post("../WEB-INF/Controllers/facturacion/Controller_PagosParciales.php", {idpago: id, factura : $("#idpp").val()}, function(data) {
                $('#mensajes').html(data);
                setTimeout(function(){cambiarContenidos('list_pagos_parciales.php?factura=' + $("#idpp").val(),"Lista de pagos multiples");},3000);
            });
        }else{
            console.log("pago "+id);
            console.log("factura "+ $("#idpp").val());
            $.post("../WEB-INF/Controllers/facturacion/Controller_PagosParciales.php", {idpago: id, factura:$("#idpp").val(), cxc:true}, function(data) {
                $('#mensajes').html(data);
                setTimeout(function(){cambiarContenidos('list_pagos_parciales.php?factura=' + $("#idpp").val()+"&cxc=true","Lista de pagos multiples");},3000);
            });
        }
    }
}
//Timbrar pagos multiples
function timbrarPago(idPago,cxc){
    if (confirm("¿Esta seguro que desea timbrar el pago parcial?")) {
        loading("Timbrando...");
        if(cxc == ""){
            $.post("../WEB-INF/Controllers/facturacion/Controler_Comprobante_Multi_Pago.php", {pago: idPago, factura: $("#idpp").val()}, function(data) {
                setTimeout(function(){
                    cambiarContenidos('list_pagos_parciales.php?factura=' + $("#idpp").val(),"Lista de pagos multiples");
                    $('#mensajes').html(data);
                },1000);
            });
        }else{
            $.post("../WEB-INF/Controllers/facturacion/Controler_Comprobante_Multi_Pago.php", {pago: idPago, cxc:true, factura: $("#idpp").val()}, function(data) {
                setTimeout(function(){
                    cambiarContenidos('list_pagos_parciales.php?factura=' + $("#idpp").val()+"&cxc=true","Lista de pagos multiples");
                    $('#mensajes').html(data);
                },1000);
            });
        }
    }
}

function timbrarPrePago(idPago,cxc){
        loading("Generando PDF PrePago...");
        if(cxc == ""){
            $.post("../WEB-INF/Controllers/facturacion/Controler_Comprobante_PrePago.php", {pago: idPago}, function(data) {
                setTimeout(function(){
                    cambiarContenidos('list_pago_parcial.php?factura=' + $("#idpp").val(),"Pago Parcial");
                    $('#mensajes').html(data);
                },1000);
            });
        }else{
            $.post("../WEB-INF/Controllers/facturacion/Controler_Comprobante_PrePago.php", {pago: idPago, cxc:true}, function(data) {
                setTimeout(function(){
                    cambiarContenidos('list_pago_parcial.php?factura=' + $("#idpp").val()+"&cxc=true","Pago Parcial");
                    $('#mensajes').html(data);
                },1000);
            });
        }
}
//timbrarPrepago multimple
function timbrarPrePagoMulti(idPago,cxc){
        loading("Generando PDF PrePago...");
        if(cxc == ""){
            $.post("../WEB-INF/Controllers/facturacion/Controler_Comprobante_PrePago_Multi.php", {pago: idPago, factura: $("#idpp").val()}, function(data) {
                setTimeout(function(){
                    cambiarContenidos('list_pagos_parciales.php?factura=' + $("#idpp").val(),"Lista de pagos multiples");
                    $('#mensajes').html(data);
                },1000);
            });
        }else{
            $.post("../WEB-INF/Controllers/facturacion/Controler_Comprobante_PrePago_Multi.php", {pago: idPago, cxc:true, factura: $("#idpp").val()}, function(data) {
                setTimeout(function(){
                    cambiarContenidos('list_pagos_parciales.php?factura=' + $("#idpp").val()+"&cxc=true","Lista de pagos multiples");
                    $('#mensajes').html(data);
                },1000);
            });
        }
}

function cancelarPago(idPago,factura){
    if (confirm("¿Esta seguro que desea cancelar el pago parcial?")) {
        loading("Cancelando...");
        $.post("../WEB-INF/Controllers/facturacion/Controler_Cancelar_Multi_Pago.php",{pago:idPago, factura:factura},function(data){
            setTimeout(function(){
                    cambiarContenidos('list_pagos_parciales.php?factura=' + factura+"&cxc=true","Lista de pagos multiples");
                    $('#mensajes').html(data);
                },1000);
        });
    }
}







$(document).ready(function() {
    
    $("#datos_a_enviar").val( $("<div>").append($(".reporte").eq(0).clone()).html());                    
    
    $(".botonExcel").click(function(event) {        
        $("#FormularioExportacion").submit();
    }).css('cursor','pointer');            
});
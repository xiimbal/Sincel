$(document).ready(function(){
    $("#reporte_facturas").click(function(){
        var FechaInicio = $("#Fecha_Inicio").val();
        var FechaFin = $("#Fecha_Fin").val();
        var get = "?FechaInicio="+FechaInicio+"&FechFin="+FechaFin;
        var url = 'reportes/reporte_factura_servicio.php'+get;
        var win = window.open(url, '_blank');
        if (win) {
            //Browser has allowed it to be opened
            win.focus();
        } else {
            //Browser has blocked it            
            alert('Please allow popups for this website');
        }
    });
    
    $("#reporte_pagos").click(function(){
        var FechaInicio = $("#Fecha_Inicio").val();
        var FechaFin = $("#Fecha_Fin").val();
        var get = "?FechaInicio="+FechaInicio+"&FechFin="+FechaFin;
        var url = 'reportes/reporte_pagos_proveedores.php'+get;
        var win = window.open(url, '_blank');
        if (win) {
            //Browser has allowed it to be opened
            win.focus();
        } else {
            //Browser has blocked it            
            alert('Please allow popups for this website');
        }
    });
    
    $("#layout_facturas").click(function(){
        var FechaInicio = $("#Fecha_Inicio").val();
        var FechaFin = $("#Fecha_Fin").val();
        var get = "?FechaInicio="+FechaInicio+"&FechFin="+FechaFin;
        var url = 'reportes/reporte_relacion_servicios.php'+get;
        var win = window.open(url, '_blank');
        if (win) {
            //Browser has allowed it to be opened
            win.focus();
        } else {
            //Browser has blocked it            
            alert('Please allow popups for this website');
        }
    });
});
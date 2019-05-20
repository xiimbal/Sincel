function buscarFinancial(pagina, IdConductor, IdConcepto, IdTipoRetencion, IdEstatus, FechaInicioPrestamo, FechaFinPrestamo, 
    FechaInicioConcepto, FechaFinConcepto, Folio){
    loading("Buscando ...");
    $("#contenidos").load(pagina, {'IdConductor':$("#"+IdConductor).val(),'IdConcepto':$("#"+IdConcepto).val(),'IdTipoRetencion':$("#"+IdTipoRetencion).val(),
        'IdEstatus':$("#"+IdEstatus).val(),'FechaInicioPrestamo':$("#"+FechaInicioPrestamo).val(),'FechaFinPrestamo':$("#"+FechaFinPrestamo).val(),
        'FechaInicioConcepto':$("#"+FechaInicioConcepto).val(),'FechaFinConcepto':$("#"+FechaFinConcepto).val(),'Folio':$("#"+Folio).val(),'MostrarTabla':true}, 
        function(data){
            finished();
    });
}
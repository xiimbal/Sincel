function cambiarselectmodelo(origen, destino) {
    dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $('#'+destino).load(dir, { 'tipo': $("#" + origen).val(), 'multiple':true}, function(){            
        /*Refrescamos las opciones*/
        var x = $('#'+destino).find('option');
        $('#'+destino).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $('#'+destino).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#"+destino).css('width', '230px');         
    });    
}
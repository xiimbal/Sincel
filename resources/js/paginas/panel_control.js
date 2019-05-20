$(document).ready(function () {    
    if($("#docking").length){
        $('#docking').jqxDocking({ theme: 'shinyblack', orientation: 'horizontal', width: '100%', mode: 'docked' });
        $('#docking').jqxDocking('disableWindowResize', 'window1');
        $('#docking').jqxDocking('disableWindowResize', 'window2');
        $('#docking').jqxDocking('disableWindowResize', 'window3');
        $('#docking').jqxDocking('disableWindowResize', 'window4');                                
        $('#docking').jqxDocking('disableWindowResize', 'window5'); 
        /*$('#docking').jqxDocking('disableWindowResize', 'window6'); 
        $('#docking').jqxDocking('disableWindowResize', 'window7'); */
        //$("#uno").load("indicadores/equipos_background.php");
        cargarDiv('uno', 'indicadores/equipos.php', 'fecha_inicio','fecha_fin','cliente','ejecutivo','razon_social');
        cargarDiv('dos', 'indicadores/facturacion.php', 'fecha_inicio','fecha_fin','cliente','ejecutivo','razon_social');
        cargarDiv('tres', 'indicadores/tickets_background.php', 'fecha_inicio','fecha_fin','cliente','ejecutivo','razon_social');
        cargarDiv('cuatro', 'indicadores/solicitudes_background.php', 'fecha_inicio','fecha_fin','cliente','ejecutivo','razon_social');
        cargarDiv('cinco', 'indicadores/toner_background.php', 'fecha_inicio','fecha_fin','cliente','ejecutivo','razon_social');
        /*$("#dos").load("indicadores/equipos_background.php");
        $("#tres").load("indicadores/equipos_background.php");
        $("#cuatro").load("indicadores/equipos_background.php");
        $("#cinco").load("indicadores/equipos_background.php");*/
    }    
    
    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "Sin algún elemento",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    }).css('max-width','200px');
    
    $('.fecha').each(function() {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            maxDate: "+0D",
            changeMonth: true,
            changeYear: true
        });
    });
    $('.fecha').mask("9999-99-99");
    $(".button").button();
});


function cargarDiv(div, pagina, fechaInicio, fechaFinal, cliente, ejecutivo, razonSocial){
    loading("Cargando ...");
    $('#'+div).load(pagina, {'fechaInicio': $("#"+fechaInicio).val(), 'fechaFinal':$("#"+fechaFinal).val(), 'cliente':$("#"+cliente).val(), 
        'ejecutivo':$("#"+ejecutivo).val(), 'razonSocial':$("#"+razonSocial).val()}, function(){
            finished();
        });
}

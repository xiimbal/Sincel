var form = "#formCampanas";
var controlador = "viajes/GenerarReportesExcelCampanas.php";
$(document).ready(function() {
    $(".boton").button();/*Estilo de botones*/
    $("#fecha_inicio").datepicker({
        dateFormat: 'yy-mm-dd',
        maxDate: '+0D'
    });
    $("#fecha_fin").datepicker({
        dateFormat: 'yy-mm-dd',
        maxDate: '+0D'
    });
    
    
    $(".filtroselect").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    $(".filtroselectmultiple").multiselect({
        multiple: true,
        noneSelectedText: "Todos",
        selectedList: 3,        
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    $(".filtroselect").select({
        noneSelectedText: "Todos",
        selectedList: 3,        
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $('.fecha').mask("9999-99-99");

    $("#reporteServicios").click(function(){
        if($(form).valid()){
            var direccion = "viajes/reporteServiciosPDF.php";
            $.post(direccion, {form: $(form).serialize()}).done(function(data) {
                var w = window.open('about:blank', 'ReporteServicio');
                w.document.write(data);
                w.document.close();
            });
            return true;
        }else{
            return false;
        }
    });
    
    $("#reporteAdministracion").click(function(){
        if($(form).valid()){
            var direccion = "viajes/reporteAdministracionPDF.php";
            $.post(direccion, {form: $(form).serialize()}).done(function(data) {
                var w = window.open('about:blank', 'ReporteAdministración');
                w.document.write(data);
                w.document.close();
            });
            return true;
        }else{
            return false;
        }
    });
    
    $("#miReporte").click(function(){
        if($(form).valid()){
            var direccion = "viajes/reporteAdministracionPDF.php";
            $.post(direccion, {form: $(form).serialize(), miReporte: 1}).done(function(data) {
                var w = window.open('about:blank', 'MiReporteAdministración');
                w.document.write(data);
                w.document.close();
            });
            return true;
        }else{
            return false;
        }
    });
    
    $("#reporteMovimientos").click(function(){
        if($(form).valid()){
            var direccion = "viajes/reporteMovimientosPDF.php";
            $.post(direccion, {form: $(form).serialize()}).done(function(data) {
                var w = window.open('about:blank', 'ReporteMovimientos');
                w.document.write(data);
                w.document.close();
            });
            return true;
        }else{
            return false;
        }
    });
    
    $("#reporteViajes").click(function(){
        if($(form).valid()){
            var direccion = "viajes/reporteViajesPDF.php";
            $.post(direccion, {form: $(form).serialize()}).done(function(data) {
                var w = window.open('about:blank', 'ReporteViajes');
                w.document.write(data);
                w.document.close();
            });
            return true;
        }else{
            return false;
        }
    });
    
    $(form).validate();
});

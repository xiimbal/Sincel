$(document).ready(function() {
    $(".boton").button();/*Estilo de botones*/
    $("#fecha1").datepicker({
        dateFormat: 'yy-mm-dd'
    });
    $("#fecha2").datepicker({
        dateFormat: 'yy-mm-dd'
    });
    
    $(".fecha").mask("9999-99-99");
});

function cargarlocalidades(origen, componente) {
    $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_localidad.php", {id: $("#" + origen).val()});
}
function cargarNoSerie(origen, componente) {
    $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_NoSerie.php", {id: $("#" + origen).val()});
}

function cargarclientes(origen, componente) {
    var client = $("#client").val();
    $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_cli_mtto.php", {id: $("#" + origen).val(), client: client});
}

function enviardatos() {
    loading('Cargando ...');
    $("#tablainfo").load("ventas/tabla_mtto.php", {"vendedor": $("#vendedor").val(),"noserie": $("#NoSerie").val(), "cliente": $("#cliente").val(), "localidad": $("#localidad").val(),
        "fecha1": $("#fecha1").val(), "fecha2": $("#fecha2").val()}, function() {
        finished();
    });
}
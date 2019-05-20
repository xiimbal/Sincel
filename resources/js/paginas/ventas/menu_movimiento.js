$(document).ready(function() {
    $(".boton").button();/*Estilo de botones*/
    $("#fecha1").datepicker({
        dateFormat: 'yy-mm-dd'
    });
    $("#fecha2").datepicker({
        dateFormat: 'yy-mm-dd'
    });

    $(".fecha").mask("9999-99-99");
    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $('.ui-multiselect').css('width', '150px');
});

function cargarlocalidades(origen, componente) {
    $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_localidad.php", {id: $("#" + origen).val()}, function(data) {
        $(".filtro").multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $('.ui-multiselect').css('width', '150px');
    });
}


function cargarclientes(origen, componente) {
    $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_cli_mtto.php", {id: $("#" + origen).val()});
}

function enviardatos() {
    var val = 0;
    if ($("#retirado").is(':checked')) {
        val = 1;
    }
    loading('Cargando ...');
    $("#tablainfo").load("ventas/tabla_movimientos.php", {"noserie": $("#NoSerie").val(), "cliente": $("#cliente").val(), "localidad": $("#localidad").val(),
        "fecha1": $("#fecha1").val(), "fecha2": $("#fecha2").val(), "tipo": $("#tipo").val(), "NoRep": $("#NoRep").val(), "retirado": val}, function() {
        finished();
    });
}
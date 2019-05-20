var form = "#rtoners";
var controlador = "reportes/reporte_toner.php";
$(document).ready(function() {
    $(".boton").button();/*Estilo de botones*/
    $("#fecha1").datepicker({
        dateFormat: 'yy-mm-dd'
    });
    $("#fecha2").datepicker({
        dateFormat: 'yy-mm-dd'
    });
    $("#almacen").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter();
    $("#cliente").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter();
    $("#localidad").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter();
    $("#equipo").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    });
});

function cargarlocalidades(origen, destino) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_localidad.php";
    $("#" + destino).load(dir, {id: $("#" + origen).val()}, function() {
        /*Refrescamos las opciones*/
        var x = $('#' + destino).find('option');
        $('#' + destino).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $('#' + destino).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter();
    });
}

function cargarequipos(origen, destino) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_equipos.php";
    $("#" + destino).load(dir, {id: $("#" + origen).val()}, function() {
        /*Refrescamos las opciones*/
        var x = $('#' + destino).find('option');
        $('#' + destino).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $('#' + destino).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter();
    });
}

function consultarreporttonner() {
    var bool = false;
    if($("#almacen").val() === "" && $("#cliente").val() === ""){
        if(confirm("Si no selecciona un almacén o un cliente la consulta puede tardar varios minutos.\n\
                    Desea continuar?")){
            bool = true;
        }
    }else{
        bool = true;
    }
    if(bool){
        var nombre = ["almacen", "fecha1", "fecha2", "cliente", "localidad", "equipo"];
        var urlextra = "";
        for (x = 0; x < nombre.length; x++) {
            if ($("#" + nombre[x]).val() !== "") {
                if (urlextra === "") {
                    urlextra += "?" + nombre[x] + "=" + $("#" + nombre[x]).val();
                } else {
                    urlextra += "&" + nombre[x] + "=" + $("#" + nombre[x]).val();
                }
            }
        }
        window.open(controlador + urlextra, '_blank');
    }
}
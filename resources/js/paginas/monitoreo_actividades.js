var tid = null;
var tid2 = null;
var pagina_actual = 0;
var tiempo_intervalo = 60000;//El tiempo de intervalo es cada 60 segundos
var flag = true;

function intervalo(){
    if ($("#monitoreoActividades").length) {
        cargarInformacion();
    } else {
        parar();
    }
}

$(document).ready(function() {
    cargarInformacion();
    parar();
    if(tid == null){
        tid = setInterval(intervalo, tiempo_intervalo);//Se refresca cada 5 minutos (300,000 mili-segundo)
    }
});

function cargarInformacion(){
    var fecha = $("#fecha").val(), fechaHora = $("#fechaHora").val();
        
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

    $.post("actividades_detalle.php",{pagina: pagina_actual, fecha: fecha, fechaHora: fechaHora}).done(function(data){
        if(data.toString() !== ""){//Hay información que mostrar
            //parar2();
            $("#monitor").html(data);
            oTable = $('.tabla').dataTable({
                "bJQueryUI": true,
                "bRetrieve": true,
                "bDestroy": true,
                "oLanguage": espanol,
                "sPaginationType": "full_numbers",
                "bDeferRender": true,
                "iDisplayLength": 10,
                "width":10
            });
            //$( document ).tooltip();
            pagina_actual++;
        }else if(pagina_actual === 0){
            $("#monitor").html("No hay registros para mostrar. La página se actualizará en un minuto.");
            Intervalo2();
        }else{
            parar();
            //parar2();
            pagina_actual = 0;
            //$("#contenidos").load("mesa/monitoreo_actividades.php");
            location.reload();
        }
    });
}

function parar(){
    //$( document ).tooltip("disable");
    clearInterval(tid);
}

function Intervalo2(){
    flag = !flag;
    if(flag){//Recargamos
        location.reload();
    }
}

function mostrarInfo2(){
    location.reload();
}

function parar2(){
    clearInterval(tid2);
}
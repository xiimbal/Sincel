var xTable;
$(document).ready(function() {
    $(".boton").button();/*Estilo de botones*/
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
    
    if($('#mostrarLista').length){
        xTable = $('#mostrarLista').dataTable({                                    
            "bJQueryUI": true,
            "bRetrieve": true,
            "bDestroy": true,
            "oLanguage": espanol,
            "sPaginationType": "full_numbers",
            "bDeferRender": true,
            "iDisplayLength": 100,
            "aaSorting": [[0, "desc"]]
        });
    }
    
    $('.ui-multiselect').css('width', '150px');
    jQuery(function($) {
        $.datepicker.regional['es'] = {
            closeText: 'Cerrar',
            prevText: '<Ant',
            nextText: 'Sig>',
            currentText: 'Hoy',
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Juv', 'Vie', 'Sáb'],
            dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
            weekHeader: 'Sm',
            dateFormat: 'dd/mm/yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''};
        $.datepicker.setDefaults($.datepicker.regional['es']);
    });
    
    if($("#cargarBase").length){
        $("#cargarBase2").show();
    }
    
    if($("#mensaje_faltantes").length){
        var html = $("#mensaje_faltantes").html();
        if(html != ""){
            $("#mensaje_faltantes2").html(html);
        }
    }
});

function cargarLista(){
    loading("Preparando el envío de los datos, podría tomar algunos minutos ...");
    $("#cargarBase").hide();    
    $("#cargarBase2").hide();
    var bandera = $('#ban').val();
    if(bandera==1)
    var claveCliente = $('#cliente').val();
    else
        if(bandera==0){             //PrintFleet
            var clientes = $('#clientes').val();
    
            var liSeries = $("#listaSeries").val();         // listaSeries
            var liClientes = $("#listaClientes").val();     // listaClientes
            var liUbicacion = $("#listaUbicacion").val();   // listaUbicacion
            var liStatus = $("#listaStatus").val();         // listaStatus
        }    
    
    var lista = [];    
    var rows = $("#mostrarLista").dataTable().fnGetNodes();
    
    for(var i=0;i<rows.length;i++)
    {
        var aux = [];        
        if(bandera==1){
            
        aux.push(rows[i].cells[1].innerHTML);//Serie        
        aux.push(rows[i].cells[3].innerHTML);//ContadorBN
        if(rows[i].cells[4].innerHTML != ""){//Contador CL
            aux.push(rows[i].cells[4].innerHTML);
        }
        lista.push(aux);
        }else if(bandera==0){
            aux.push(rows[i].cells[1].innerHTML);//Cliente        
            aux.push(rows[i].cells[4].innerHTML);//Serie        
            aux.push(rows[i].cells[6].innerHTML);//ContadorBN
            if(rows[i].cells[7].innerHTML != ""){//Contador CL
                aux.push(rows[i].cells[7].innerHTML);
    }
            aux.push(rows[i].cells[8].innerHTML);//IP
            aux.push(rows[i].cells[9].innerHTML);//Ubicaci�n
            lista.push(aux);
        }
    }
        
    loading("Procesando lecturas, podría tardar algunos minutos ...");
    
    var per = $("#periodo").val();
    

    if(bandera==1){
    $("#contenidos_invisibles").load("../WEB-INF/Controllers/Controler_LecturaFileSave.php",
                {"lista": lista, "cliente":claveCliente, "periodo":per, "banderaTR":bandera},
        function (data) {
            $('#mensajeLista').html(data);
            finished();
    });
    }else
        if(bandera==0){

            $("#contenidos_invisibles").load("../WEB-INF/Controllers/Controler_LecturaFileSave.php",
                {"lista": lista, "cliente":clientes, "periodo":per, "banderaTR":bandera, "listaSeries":liSeries, "listaClientes":liClientes,
                "listaUbicacion":liUbicacion, "listaStatus":liStatus},
            function (data) {
                $('#mensajeLista').html(data);
                finished();
            });
}
}

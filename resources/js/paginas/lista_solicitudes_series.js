var tipo_accion;
$(document).ready(function() {
    tipo_accion = 1;
    var form = "#formSerie";
    var paginaExito = "ventas/list_sol_equipo.php";
    var controlador = "WEB-INF/Controllers/Controler_Configuracion_Rapida.php";
    $(".boton").button();/*Estilo de botones*/
    /*validate form*/
    $(form).validate({
        rules: {           
        },
        messages: {            
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize() , tipo: tipo_accion}).done(function(data) {
                $('#mensajes').html(data);
                //alert(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(paginaExito, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });       
        
    var total_componentes = Number($("#total_componentes").val());
    var todo = true; //Si ya estan capturadas todas las serie y componentes
    
    for(var i=0;i<total_componentes;i++){//Recorremos todas las filas de componentes
        if($("#cantidad2_sur_"+i).length){//Ponemos los valores maximos y minimos para las cantidades a surtir
            var max = $("#cantidad2_"+i).val();
            $("#cantidad2_sur_" + i).rules('add', {            
                number:true,  
                min: 1,
                max: max,
                messages: {
                    number: " * Ingresa solo números", min: "El valor mínimo permitido es {0}", max: "El valor máximo permitido es {0}"
                }
            });
        }
        
        if($("#almacen_"+i+"_seleccionado").val()!=""){
            $("#almacen_sol2_"+i).val($("#almacen_"+i+"_seleccionado").val());            
            $("#check_solicitud2_"+i).prop('disabled', false);
            $("#check_solicitud2_"+i).prop("checked", false);
            //Ponemos las existencias del almacen ya seleccionado
            getExistenciasAlmacen('existencia2_'+i,'almacen_sol2_'+i,'modelo2_'+i);
        }else{
            $("#check_solicitud2_"+i).hide();
            todo = false;
        }
    }
    
     /*Habilitamos los checkbox en caso de que la solicitud esté en estatus de enviar por mensajeria*/
    if($("#mover_series").length){
        $("#div_envios").show();        
        var total = Number($("#total").val());
        for(var i=0;i<total;i++){            
            if($("#check_solicitud_"+i).length){
                $("#check_solicitud_"+i).prop('disabled', false);
                $("#check_solicitud_"+i).prop("checked", false);
            }/*else{
                todo = false;
            } */                                   
        }
        
        $("#tipo_envio_mensajeria").prop("checked", true);
        mostrarMensajeria(1);
        
        /*if(todo){
            $("#submit_series").hide();
        }*/
    }        
    
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

    oTable = $('.tabla_datos').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "bSort": false,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25        
    });

    $('.fecha').each(function() {
        $(this).datepicker({
            dateFormat: 'dd-mm-yy',
            changeYear: true,
            changeMonth: true,
            defaultDate: '01-01-1980',
            maxDate: "+0D"
        });
    });       
});

function getExistenciasAlmacen(textname, almacen, noparte){
    var pagina = "WEB-INF/Controllers/Ajax/cargaDivs.php";
    $("#contenidos_invisibles").load(pagina, {'no_parte': $("#"+noparte).val(), 'idAlmacen':$("#"+almacen).val(), 'existencias':true}, function(data){                
        $("#"+textname).val(data);
    });
}

function marcarNoSurtido(check, id_solicitud, id_partida){
    var pagina = "WEB-INF/Controllers/Ajax/cargaDivs.php";
    var marcar = 0;
    if($('#'+check).prop('checked')){
        marcar = 1;
    }
    $("#mensajes").load(pagina, {'id_solicitud':id_solicitud, 'id_partida':id_partida, 'marcar':marcar}, function(data){                
        cambiarContenidos("ventas/lista_solicitud_series.php?id="+id_solicitud);
    });
}

function addRow(NoParte, idSerie, idRow, estado){
    if($("#pedir_contador").length && $("#pedir_contador").val()!= "0"){//Si se piden los contadores
        var NoSerie = $("#"+idSerie).val();
        if($("#fila_contador_"+idRow).length){//Si la fila de contadores de la partida ya existe, la eliminamos.
            eliminarFila(idRow);
        }
        if(NoSerie!=""){        
            var pagina = "WEB-INF/Controllers/Ajax/cargaDivs.php";
            $("#contenidos_invisibles").load(pagina, {'NoParte':NoParte, 'crea_fila_contadores':true, 'NoSerie':NoSerie, 'idRow':idRow, 'estado':estado}, function(data){            
                var newrow = $(''+data);
                $('#'+idRow).after(newrow);
                agregarObligatorio(NoSerie);
            });        
        }
    }
}

function eliminarFila(idRow){    
    $('table#tAlmacen tr#fila_contador_'+idRow).remove();    
}
/**
 * Hace obligatorios los campos de contadores de la serie especificada y pone los rangos minimos y maximos permitidos
 * @param {type} serie NoSerie
 * @returns {undefined} void
 */
function agregarObligatorio(serie){
        var minimo = 0;
        if($("#max_contador_bn_"+serie).length){
            minimo = $("#max_contador_bn_"+serie).val();
        }
        if($("#contador_bn_"+serie).length){//Si existe contador b/n
            $("#contador_bn_" + serie).rules('add', {
                required: true,
                number:true,  
                min: minimo,
                messages: {
                    required: " * Ingrese el contador", number: " * Ingresa solo números", min: "El valor mínimo permitido es {0} por la lectura máxima del equipo"
                }
            });
        }
        minimo = 0;
        if($("#max_contador_color_"+serie).length){
            minimo = $("#max_contador_color_"+serie).val();
        }
        if($("#contador_color_"+serie).length){//Si existe contador color
            $("#contador_color_" + serie).rules('add', {
                required: true,
                number:true,  
                min: minimo,
                messages: {
                    required: " * Ingrese el contador", number: " * Ingresa solo números", min: "El valor mínimo permitido es {0} por la lectura máxima del equipo"
                }
            });
        }
        if($("#toner_bn_" + serie).length){//Si existe toner b/n
            $("#toner_bn_" + serie).rules('add', {
                required: false,
                number:true,
                max: 100,
                min: 0,
                messages: {
                    required: " * Ingrese el contador", number: " * Ingresa solo números", max: "Ingresa un valor menor a {0}", min: "El valor mínimo es {0}"
                }
            });
        }
        if($("#toner_cian_"+serie).length){//Si existe toner cian
            $("#toner_cian_" + serie).rules('add', {
                required: false,
                number:true,
                max: 100,
                min: 0,
                messages: {
                    required: " * Ingrese el contador", number: " * Ingresa solo números", max: "Ingresa un valor menor a {0}", min: "El valor mínimo es {0}"
                }
            });
        }
        if($("#toner_magenta_"+serie).length){//Si existe toner magenta
            $("#toner_magenta_" + serie).rules('add', {
                required: false,
                number:true,
                max: 100,
                min: 0,
                messages: {
                    required: " * Ingrese el contador", number: " * Ingresa solo números", max: "Ingresa un valor menor a {0}", min: "El valor mínimo es {0}"
                }
            });
        }
        if($("#toner_amarillo_"+serie).length){//Si existe toner amarillo
            $("#toner_amarillo_" + serie).rules('add', {
                required: false,
                number:true,
                max: 100,
                min: 0,
                messages: {
                    required: " * Ingrese el contador", number: " * Ingresa solo números", max: "Ingresa un valor menor a {0}", min: "El valor mínimo es {0}"
                }
            });
        }    
}

function marcarDesmarcar(check, serie){
    if($('#'+check).is(':checked') ){
        agregarObligatorio(serie);
    }else{
        quitarObligatorio(serie);
    }
}

function quitarObligatorio(serie){
    if($("#contador_bn_"+serie).length){
        $("#contador_bn_" + serie).rules( "remove");
    }
    if($("#contador_color_"+serie).length){
        $("#contador_color_" + serie).rules( "remove");
    }
    if($("#toner_bn_"+serie).length){
        $("#toner_bn_" + serie).rules( "remove");
    }
    if($("#toner_cian_"+serie).length){
        $("#toner_cian_" + serie).rules( "remove");
    }
    if($("#toner_magenta_"+serie).length){
        $("#toner_magenta_" + serie).rules( "remove");
    }
    if($("#toner_amarillo_"+serie).length){
        $("#toner_amarillo_" + serie).rules( "remove");          
    }
}

function cargarEquiposByAlmacen(selectAlmacen, selectDestino, NoParte){
    $("#" + selectDestino).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'almacen': $("#" + selectAlmacen).val(), 'NoParte': $("#"+NoParte).val()});
}

function cambiarTipoAccion(tipo){
    tipo_accion = tipo;
}

function mostrarMensajeria(activo){
    if(activo === 1){
        $(".mensajeria").show();
        $(".propio").hide();                
        $(".otro_envio").hide();
    }else if(activo === 2){        
        $(".mensajeria").hide();
        $(".propio").show();
        $(".otro_envio").hide();
    }else{
        $(".mensajeria").hide();
        $(".propio").hide();
        $(".otro_envio").show();
    }
}

function ponerCantidad(origen, destino, select){
    if($("#"+select).val() != ""){
        $("#"+destino).rules( "remove");
        var max = $("#"+origen).val();
        $("#"+destino).rules('add', {  
            required: true,
            number:true,  
            min: 1,
            max: max,
            messages: {
                required: " * Este valor es obligatorio", number: " * Ingresa solo números", min: "El valor mínimo permitido es {0}", max: "El valor máximo permitido es {0}"
            }
        });
        $("#"+destino).val($("#"+origen).val());
    }else{
        $("#"+destino).rules( "remove");
    }
}

/**
 * Elimina un equipo de la partida especificada
 * @param {type} idSolicitud id de la solicitud
 * @param {type} idPartida id de la partida
 * @returns {undefined}
 */
function eliminarEquipoDeSolicitud(idSolicitud, idPartida){
    var pagina = "WEB-INF/Controllers/Ventas/Controller_SeriesSolicitud.php";
    if(confirm("Está seguro de eliminar esta fila?")){
        $("#mensajes").load(pagina, {'id_solicitud':idSolicitud, 'id_partida':idPartida, 'eliminar':true}, function(data){                            
            cambiarContenidos("ventas/lista_solicitud_series.php?id="+idSolicitud);
            $("#mensajes").html(data);
        });
    }
}
var contador = 2;
$(document).ready(function() {
    var form = "#solform";
    var controlador = "WEB-INF/Controllers/Ventas/Controller_Editar_Solicitud.php";

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            cliente: {required: true},
            numero1: {required: true, number: true},
            tipo1: {required: true},
            modelo1: {required: true},
            localidad1: {required: true}
        },
        messages: {
            cliente: {required: " * Selecciona el cliente"},
            numero1: {required: " * Ingrese la cantidad", number: " * Ingresa un número"},
            tipo1: {required: " * Selecciona el tipo"},
            modelo1: {required: " * Selecciona el modelo"},
            localidad1: {required: " * Selecciona la localidad"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            loading("Guardando y enviando correos ...");            
            $.post(controlador, {form: $(form).serialize(), num: contador}).done(function(data) {
                 if (data.toString().indexOf("Error:") === -1) {                    
                    finished();
                    cambiarContenidos("ventas/list_sol_equipo.php", "Solicitudes");
                } else {
                    $("#mensajes").html(data);
                    finished();
                }                                
            });
            $("#divinfo").empty();
        }
    });
    
    $("#almacen").val($("#almacen_p").val());//Cargamos el almacen pre-seleccionado
    
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

    $('.fecha').each(function() {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',            
            changeMonth: true,
            changeYear: true,
            minDate: '-0D'
        });
    });
    
    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    $(".tipo").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1,
        minWidth: "130"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    }).css('max-width', '130px');
    
    $('.boton').button().css('margin-top', '20px');
    
    establecercontador($("#contador").val());
    var tipo = parseInt($("#tipo_solicitud").val());
    
    if(tipo == 4 || tipo == 5){
        activarRetorno(true);
    }
    
    /*Ponemos visible el select de tipo de inventario para las filas que sean de equipos y no de componentes*/
    for(var i=1;i<=parseInt($("#contador").val());i++){
        if($("#tipo"+i).length){/*Si el select tipo existe*/
            if($("#tipo"+i).val()=="0"){
                $("#tipo_inventario"+i).show();
            }else{
                mostrarTipoInventario('tipo'+i,'tipo_inventario'+i,'div_serie_cliente'+i);
                mostrarEquiposLocalidad('localidad'+i,'serie_con_cliente'+i,i);
            }
        }
    }
        
    cargarClientePropio("cliente");
    /*if(tipo == 1){/*En arrendamiento las formas de pago y dias de revision son necesarias*/
    /*    addRequiredFormaPago(true);
        addRequiredDiasRevision(true);
    }else if(tipo==6){
        addRequiredFormaPago(true);
    }else{
        addRequiredFormaPago(false);
        addRequiredDiasRevision(false);
    }*/
});

function agregarcamposol() {        
    $("#tsolformtabla").append("<tr><td><label for=\"numero" + contador + "\">Cantidad</label></td><td><input type=\"text\" id=\"numero" + contador + "\" name=\"numero" + contador + "\" maxlength=\"5\" style=\"width: 50px;\"/>" +
            "</td><td><label for=\"tipo" + contador + "\">Tipo</label></td><td><select id=\"tipo" + contador + "\" name=\"tipo" + contador + "\" "+
            "class=\"tipo\" onchange=\"cambiarselectmodelo('tipo" + contador + "', 'modelo" + contador + "');"+
            "mostrarTipoInventario('tipo"+contador+"','tipo_inventario"+contador+"','div_serie_cliente"+contador+"');\" style=\"width: 115px;\">" +
            "</select></td>" +
            "<td><select id=\"modelo" + contador + "\" name=\"modelo" + contador + "\" class=\"size filtro\" style=\"width: 250px;\"><option value=\"\">Selecciona el modelo</option></select></td>" +
            "<td><select id=\"localidad" + contador + "\" name=\"localidad" + contador + "\" class=\"size filtro localidad\" style=\"width: 250px;\" "+            
            "onchange=\"actualizarDatosContrato(); mostrarEquiposLocalidad('localidad"+contador+"','serie_con_cliente"+contador+"');\"></select></td>"+
            "<td><select id=\"tipo_inventario"+contador+"\" name=\"tipo_inventario"+contador+"\" style='display:none;'></select>"+
            "<div id=\"div_serie_cliente"+contador+"\" style=\"display: none;\">"+
                "<label for=\"serie_con_cliente"+contador+"\">Equipo en localidad</label>"+
                "<select id=\"serie_con_cliente"+contador+"\" name=\"serie_con_cliente"+contador+"\">"+
                    "<option value=\"\">Selecciona un equipo</option>"+
                "</select>"+
            "</div>"+
            "</td></tr>");
    dir = "WEB-INF/Controllers/Ventas/Controller_select_localidades.php";
    
    /*Copiamos los tipos de componentes*/
    var $options = $("#tipo1 > option").clone();
    $('#tipo'+contador).append($options);    
    
    var $options = $("#tipo_inventario1 > option").clone();
    $('#tipo_inventario'+contador).append($options);
    
    if($("#tipo"+contador).val() == "0"){
        $("#tipo_inventario"+contador).show();
    }
    
    $('#tipo'+contador).val("");
    
    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    $(".tipo").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1,
        minWidth: "130"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    }).css('max-width', '130px');
    
    //$("#localidad" + contador).load(dir, {id: $("#cliente").val()});
    $("#numero" + contador).rules('add', {
        required: true,
        number: true,
        messages: {
            required: " * Ingrese la cantidad", number: " * Ingresa un número"
        }
    });
    $("#tipo" + contador).rules('add', {
        required: true,
        messages: {
            required: " * Selecciona el tipo"
        }
    });
    $("#modelo" + contador).rules('add', {
        required: true,
        messages: {
            required: " * Selecciona el modelo"
        }
    });
    //if(parseInt($("#tipo_solicitud").val()) <= 2){
    addRequiredLocalidad(contador);
    //}
    //cargartipocompo("tipo" + contador);
    cambiarccostoEspecifico('cliente',contador);    
    contador++;
}

function cargartipocompo(destino) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_tipocompo.php";
    $("#" + destino).load(dir);
}

function establecercontador(count) {
    contador = count;
    for (var i = 2; i < contador; i++) {
        $("#numero" + i).rules('add', {
            required: true,
            number: true,
            messages: {
                required: " * Ingrese la cantidad", number: " * Ingresa un número"
            }
        });
        $("#tipo" + i).rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el tipo"
            }
        });
        $("#modelo" + i).rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el modelo"
            }
        });
        //if(parseInt($("#tipo_solicitud").val()) <= 2){
        addRequiredLocalidad(i);
        //}
    }
    
}

function cambiarselectmodelo(origen, destino) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_compoequip.php";
    $('#'+destino).load(dir, { 'id': $("#" + origen).val()}, function(){            
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
        $("#"+destino).css('width', '250px');         
    });
    //$("#" + destino).load(dir, {id: $("#" + origen).val()});
}

function cambiarccosto(origen) {
    var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $("#contenidos_invisibles").load(dir,{"cliente":$("#" + origen).val(), "is_suspendido":true}, function(data){        
        if(data == "false"){
            dir = "WEB-INF/Controllers/Ventas/Controller_select_localidades.php";    
            for (var i = 1; i < contador; i++) {
                $('#localidad'+i).load(dir, { 'id': $("#" + origen).val()}, function(){/*Refrescamos el select y volvemos a poner filtros*/                    
                    /*Refrescamos las opciones*/
                    var x = $(this).find('option');
                    $(this).multiselect("refresh", x).multiselectfilter({
                        label: 'Filtro',
                        placeholder: 'Escribe el filtro'
                    });
                    /*Volvemos a aplicar filtros*/
                    $(this).multiselect({
                        multiple: false,
                        noneSelectedText: "No ha seleccionado",
                        selectedList: 1
                    }).multiselectfilter({
                        label: 'Filtro',
                        placeholder: 'Escribe el filtro'
                    });
                    $(this).css('width', '250px');//Width del select
                });
                /*$("#localidad" + i).load(dir, {id: $("#" + origen).val()});*/
            }  
        }else{
            alert("Este cliente está marcado como suspendido o moroso, no se puede continuar el proceso");
            $('.localidad').empty();
            var x = $("#localidad1").find('option');
            $(".localidad").multiselect("refresh", x).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
            return false;
        }
    });
      
}

function cambiarccostoEspecifico(origen, numeroCC) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_localidades.php";        
    $('#localidad'+numeroCC).load(dir, { 'id': $("#" + origen).val()}, function(data){/*Refrescamos el select y volvemos a poner filtros*/        
        /*Refrescamos las opciones*/
        var x = $("#localidad"+numeroCC).find('option');
        $("#localidad"+numeroCC).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Volvemos a aplicar filtros*/
        $("#localidad"+numeroCC).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#localidad"+numeroCC).css('width', '250px');//Width del select
    });
    /*$("#localidad" + i).load(dir, {id: $("#" + origen).val()});*/      
}

function eliminarfilaulti() {
    if (contador == 2) {
        alert("No se pueden borrar mas filas.");
    } else {
        $("#tsolformtabla tr:last").remove();
        contador--;
    }
    actualizarDatosContrato();
}

function actualizarDatosContratoPrecargados(localidades_concatenadas){  
    var res = localidades_concatenadas.split("&_&");
    var localidades = []; //Array con localidades no repetidas
    for(var i=0;i<res.length;i++){
        var searchTerm = res[i];        
        if(searchTerm!="" && searchTerm!="null" && searchTerm!=null && localidades.indexOf(searchTerm)==-1){//Si la localidad no se encuentra en el array
            localidades.push(searchTerm);
        }
    }
    localidades_concatenadas = "";
    for(i=0;i<localidades.length;i++){
        localidades_concatenadas += (localidades[i]+"&_&");
    }
    localidades_concatenadas = localidades_concatenadas.substring(0,localidades_concatenadas.length-3);        
    if($("#localidades_anteriores").val()!= localidades_concatenadas){//Si ya variaron las localidades  
        deleteRequiredContratos();
        $("#datos_contratos").load("WEB-INF/Controllers/Ajax/cargaDivs.php",{'clavesCC':localidades_concatenadas,'servicios':true}, function(){
            if(parseInt($("#tipo_solicitud").val()) <= 2 ){
                addRequiredContratos();
            }
        });
    }    
    $("#localidades_anteriores").val(localidades_concatenadas);
}

function actualizarDatosContrato(){
    var localidades = []; //Array con localidades no repetidas
    for(var i=1;i<contador;i++){//Recorremos 
        var searchTerm = $("#localidad"+i).val();
        if(searchTerm!="" && searchTerm!="null" && searchTerm!=null && localidades.indexOf(searchTerm)==-1){//Si la localidad no se encuentra en el array
            localidades.push(searchTerm);
        }
    }
    var localidades_concatenadas = "";
    for(i=0;i<localidades.length;i++){
        localidades_concatenadas += (localidades[i]+"&_&");
    }
    localidades_concatenadas = localidades_concatenadas.substring(0,localidades_concatenadas.length-3);
    if($("#localidades_anteriores").val()!= localidades_concatenadas){//Si ya variaron las localidades
        deleteRequiredContratos();
        $("#datos_contratos").load("WEB-INF/Controllers/Ajax/cargaDivs.php",{'clavesCC':localidades_concatenadas,'servicios':true}, function(){
            if(parseInt($("#tipo_solicitud").val()) <= 2 ){
                addRequiredContratos();
            }
        });        
    }    
    $("#localidades_anteriores").val(localidades_concatenadas);    
}

function cargarContratos(origen, destino){
    var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $("#" + destino).load(dir, {'cliente': $("#" + origen).val(), 'contrato': 'true'});
}

function cargarAnexos(origen, destino, cc, servicio){    
     /*En caso de que no exista, creamos anexos y/o asociamos a las localidad elegida. Tambien se crean los servicios*/
    var dir = "WEB-INF/Controllers/Ajax/updates.php";
    //$("#contenidos_invisibles").load(dir, {'contrato': $("#" + origen).val(), 'cc':cc , 'client':$("#cliente").val(), 'anexo': 'true'}, function(data){        
	$("#contenidos_invisibles").load(dir, {'cc':cc , 'crear':'true'}, function(data){/*Asociamos o creamos anexos y contratos cuando sea necesario*/    
        dir = "WEB-INF/Controllers/Ventas/Controller_select_clianexo.php";/*Cargamos los anexos*/
        $("#" + destino).load(dir, {'ccosto':cc ,  'group':true, 'omite_selecciona':true}, function(){
            cargarServicios(destino, servicio);
        });
    });    
}

function cargarIdAnexoClienteCC(origen, destino){
    var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $("#" + destino).load(dir, {'anexo': $("#" + origen).val(), 'idAnexoClienteCC': 'true'});
}

var precargado = false;
function cargarServicios(origen, destino){
    /*var dir = "WEB-INF/Controllers/Ajax/updates.php";
    var tipo = 1; /*0: servicio global, 1: servicio particular.*/
    /*if($("#tipo_servicio").length){
        tipo = $("#tipo_servicio").val();
    }
    $("#contenidos_invisibles").load(dir,{'idAnexoClienteCC': $("#" + origen).val(), 'catalogo_servicios': 'true', 'tipo':tipo}, function(){*/
	var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";    
	$("#" + destino).load(dir, {'idAnexoClienteCC': $("#" + origen).val(), 'catalogo_servicios': 'true', 'anexo_completo':true}, function(data){
		if(!precargado){
			//Seleccionamos en los combos los valores que se habían seleccionado
			var num_filas = $("#num_filas").val();
			for(var fila = 0; fila<=num_filas;fila++){
				if($("#contrato_precargado"+fila).length){//Si existe el hidden correspondiente                    
					$("#contrato"+fila).val($("#contrato_precargado"+fila).val());                                        
					$("#anexo"+fila).val($("#anexo_precargado"+fila).val());
					$("#servicio"+fila).val($("#servicio_precargado"+fila).val());
				}
			}
			precargado = true;
		}
	});
    //});     
}

function addRequiredLocalidad(indice){
    $("#localidad" + indice).rules('add', {
        required: true,
        messages: {
            required: " * Selecciona la localidad"
        }
    }); 
}

function deleteRequiredLocalidad(indice){
    $("#localidad" + indice).rules( "remove");
}

function cambioTipoSolicitud(){
    var tipo = parseInt($("#tipo_solicitud").val());
    if(tipo <= 2 ){/*Todo es obligatorio*/        
        addRequiredContratos();
    }else{               
       deleteRequiredContratos();
    }
    
    if(tipo == 4 || tipo == 5){
        activarRetorno(true);
    }else{
        activarRetorno(false);
    }
    
    /*if(tipo == 1){/*En arrendamiento las formas de pago y dias de revision son necesarias*/
    /*    addRequiredFormaPago(true);
        addRequiredDiasRevision(true);
    }else{
        addRequiredFormaPago(false);
        addRequiredDiasRevision(false);
    }*/
}


function activarRetorno(activar){
    if(!activar){
        $("#fecha_regreso").rules( "remove");
        $("#retorno").hide();                
    }else{        
        $("#retorno").show();        
        $("#fecha_regreso").rules('add', {
            required: true,
            messages: {
                required: " * Selecciona la fecha de devoluci\u00f3n"
            }
        });
    }
}

function deleteRequiredContratos(){
    /*Borramos rules de required a los campos en caso de que existan*/
    for(var j=0;j<=20;j++){
        if($("#contrato" + j).length){
            $("#contrato" + j).rules( "remove");                                            
        }
        if( $("#anexo" + j).length){
            $("#anexo" + j).rules( "remove");            
        }
        if($("#servicio" + j).length){
            $("#servicio" + j).rules( "remove");
        }
    }
}

function addRequiredContratos(){
    var num_contratos = parseInt($("#numero_contratos").val());
    for(var i=0;i<num_contratos;i++){                
        $("#contrato" + i).rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el contrato"
            }
        }); 
        $("#anexo" + i).rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el anexo"
            }
        }); 
        $("#servicio" + i).rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el servicio"
            }
        }); 
    }
}

function addRequiredFormaPago(required){
    if(required){
        $("#formas_pago").rules('add', {
            required: true,
            messages: {
                required: " * Selecciona la forma de pago"
            }
        });
        $("#dias_credito").rules('add', {
            required: true,
            messages: {
                required: " * Escribe los días de crédito"
            }
        });        
    }else{
        $("#formas_pago").rules( "remove");
        $("#dias_credito").rules( "remove");
    }
}

function addRequiredDiasRevision(required){
    if(required){
        $("#dias_revision").rules('add', {
            required: true,
            messages: {
                required: " * Escribe los días de revisión"
            }
        });        
    }else{
        $("#dias_revision").rules( "remove");        
    }
}

function mostrarTipoInventario(origen, destino, div_serie){
    if($("#"+origen).val() == "0"){
        $("#"+div_serie).hide();
        $("#"+destino).show();        
    }else{
        $("#"+destino).hide();
        $("#"+div_serie).show();
    }
}

function mostrarEquiposLocalidad(origen, destino, indice){
    //alert(origen); alert(destino);
    var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $("#" + destino).load(dir, {'localidad': $("#" + origen).val(), 'equipos': 'true'}, function(){
        if($("#serie_asociada"+indice).length){
            $("#"+destino).val($("#serie_asociada"+indice).val());
        }
    });
}

function cargarClientePropio(origen){
    var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $("#contenidos_invisibles").load(dir, {'cliente': $("#" + origen).val(), 'tipo_cliente': true}, function(data){
        if(data == "7"){
            $('.oculto').show();
            propio = 1;
            $("#cliente_propio").val(propio);
        }else{
            $('.oculto').hide();
            propio = 0;
            $("#cliente_propio").val(propio);
        }
    });
}
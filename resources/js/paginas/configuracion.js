var precargado = false;
$(document).ready(function(){    
    var form = "#formConfiguracion";
    var paginaExito = $("#pagina_anterior").val();
    var controlador = "WEB-INF/Controllers/Controler_Configuracion.php";    

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");   
   
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            serie: {required: true,  maxlength: 400, minlength: 3},
            no_parte: {selectcheck: true}
        },
        messages: {
            serie: {required: " * Ingrese el numero de serie", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(paginaExito, function() {
                        $('.fecha').each(function() {
                            $(this).datepicker({  
                            dateFormat: 'yy-mm-dd',
                            changeMonth: true,      
                            changeYear: true,
                            maxDate: "+0D"
                            });
                         });
                         $(".button").button();
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
    
    var espanol = {
        "sProcessing":     "Procesando...",
        "sLengthMenu":     "Mostrar _MENU_ registros",
        "sZeroRecords":    "No se encontraron resultados",
        "sEmptyTable":     "Ning\u00fan dato disponible en esta tabla",
        "sInfo":           "Mostrando de _START_ a _END_ de  _TOTAL_ registros",
        "sInfoEmpty":      "Mostrando 0 registros",
        "sInfoFiltered":   "(filtrado de _MAX_ registros)",
        "sInfoPostFix":    "",
        "sSearch":         "Buscar:",
        "sUrl":            "",
        "sInfoThousands":  ",",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst":    "Primero",
            "sLast":     "\u00daltimo",
            "sNext":     "Siguiente",
            "sPrevious": "Anterior"
        },
        "oAria": {
            "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }
    };

    oTable = $('.dataTable').dataTable({                        
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength" : 25
    }); 
    
    $('.fecha').each(function() {
        $(this).datepicker({  
        dateFormat: 'yy-mm-dd',
        changeMonth: true,      
        changeYear: true,
        maxDate: "+0D"
        });
     });
     
     /*En caso de que se este editando el registro*/
     if($("#id_bitacora").val()!=""){
         precargado = true;
         cargarModeloByParte('modelo','no_parte');
         $("#cliente").val($("#clave_cliente").val());
         cargarLocalidadByCliente('localidad','cliente'); limpiarAnexo('anexo');                  
     }
          
     /*Agregamos validaciones*/     
     /*for(var i=1;i<=Number($("#cantidad_componentes").val());i++){         
        var nombre = "#c_no_parte_" + i;        
        $(nombre).rules("add", {
           selectcheck: true,
           messages: {required: " * Seleccione el n\u00famero de parte"}
        });
     }
          
     for(i=1;i<=Number($("#cantidad_suministros").val());i++){
        var nombre = "#s_no_parte_" + i;        
        $(nombre).rules("add", {
           selectcheck: true,
           messages: {required: " * Seleccione el n\u00famero de parte"}
        });
     }*/
    if($("#radio_cliente").length){//si radio cliente existe
        mostrarOcultarDiv('radio_cliente','cliente_div','almacen_div');
    }
    
    $("#equipo_demo").change(function() {
        if(this.checked) {
            $("#contadorBN").rules("remove");
        }else{
            $("#contadorBN").rules('add', {
               required: true, maxlength: 10, number: true,
               messages: {
                   required: " * Ingrese el contador", 
                   maxlength: " * Ingrese un máximo de {0} números", 
                   number: " * Ingresa sólo números"
               }
           });
           if($("#contadorColor").length){
                $("#contadorColor").rules('add', {
                    required: true, maxlength: 10, number: true,
                    messages: {
                        required: " * Ingrese el contador", 
                        maxlength: " * Ingrese un máximo de {0} números", 
                        number: " * Ingresa sólo números"
                    }
                });
           }
        }
    });
    
    
    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
});

function cargarModeloByParte(destino, origen){
    $("#info_config").load("WEB-INF/Controllers/Ajax/CargaSelect.php", {parte_modelo: $("#" + origen).val()}, function(data){        
        $("#"+destino).val(data);
    });    
}

function cargarLocalidadByCliente(selectDestino, selectCliente){
    $("#" + selectDestino).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {cliente: $("#" + selectCliente).val()}, function(){
        if($("#id_bitacora").val()!=""){
            $("#localidad").val($("#clave_cc").val());
            cargaranexo('localidad','anexo');
        }
    });
}

function cargarClienteBySolicitud(selectDestino, selectSolicitud){
    $("#info_config").load("WEB-INF/Controllers/Ajax/CargaSelect.php", {id_solicitud: $("#" + selectSolicitud).val()}, function(data){                
        $("#"+selectDestino).val(data);
        cargarLocalidadByCliente('localidad','cliente'); limpiarAnexo('anexo');                  
    });
}

function limpiarAnexo(selectAnexo){
    $('#'+selectAnexo).empty().append('Selecciona un anexo');
}

var cantidadComponente = $("#cantidad_componentes").val();
function nuevoComponente() {
    cantidadComponente++;
    var newRow = "<tr>"+
    "<td><select id=\"tipo"+cantidadComponente+"\" name=\"tipo"+cantidadComponente+"\" class=\"size filtro\" "+
    "onchange=\"cambiarSelectModeloCompatible('tipo"+cantidadComponente+"', 'c_no_parte_"+cantidadComponente+"','"+$("#no_parte_confi").val()+"');\">"+    
    "</select></td>"+
    "<td><select class='entrada filtro' id='c_no_parte_"+cantidadComponente+"' name='c_no_parte_"+cantidadComponente+"'></select></td>"+    
    "<td><input type='text' class='fecha entrada' id='c_fecha_"+cantidadComponente+"' name='c_fecha_"+cantidadComponente+"'/></td>"+
    "</tr>";
    $('#table_componentes tr:last').after(newRow);//add the new row
    $('#tipo1 option').clone().appendTo('#tipo' + cantidadComponente);//Clonamos las opciones del select
    $('.fecha').each(function() {
        $(this).datepicker({  
        dateFormat: 'yy-mm-dd',
        changeMonth: true,      
        changeYear: true,
        maxDate: "+0D"
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
     /*var nombre = "#c_no_parte_" + cantidadComponente;
     $(nombre).rules("add", {
        selectcheck: true,
        messages: {required: " * Seleccione el n\u00famero de parte"}
     });*/
}

var cantidadSuministro = $("#cantidad_suministros").val();
function nuevoSuministro() {
    cantidadSuministro++;
    var newRow = "<tr>"+
    "<td><select id=\"stipo"+cantidadSuministro+"\" name=\"stipo"+cantidadSuministro+"\" class=\"size filtro\" "+
    "onchange=\"cambiarSelectModeloCompatible('stipo"+cantidadSuministro+"', 's_no_parte_"+cantidadSuministro+"','"+$("#no_parte_confi").val()+"');\">"+    
    "</select></td>"+
    "<td><select class='entrada filtro' id='s_no_parte_"+cantidadSuministro+"' name='s_no_parte_"+cantidadSuministro+"'></select></td>"+    
    "<td><input type='text' class='fecha entrada' id='s_fecha_"+cantidadSuministro+"' name='s_fecha_"+cantidadSuministro+"'/></td>"+
    "</tr>";
    $('#table_suministro tr:last').after(newRow);//add the new row
    $('#stipo1 option').clone().appendTo('#stipo' + cantidadSuministro);//Clonamos las opciones del select
    $('.fecha').each(function() {
        $(this).datepicker({  
        dateFormat: 'yy-mm-dd',
        changeMonth: true,      
        changeYear: true,
        maxDate: "+0D"
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
}

function deleteRowComponente() {
    var trs = $("#table_componentes tr").length;
    if (trs > 1) {
        $("#table_componentes tr:last").remove();
    }
    cantidadComponente--;
}

function deleteRowSuministro() {
    var trs = $("#table_suministro tr").length;
    if (trs > 1) {
        $("#table_suministro tr:last").remove();
    }
    cantidadSuministro--;
}

function cargarlocalidades(origen, componente) {
    $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_cliloc.php", {id: $("#" + origen).val(), vendedor: $('#vendedor').val()});
}

function cargaranexo(origen, componente) {
    var dir = "WEB-INF/Controllers/Ajax/updates.php";
    $("#contenidos_invisibles").load(dir, {'cc':$("#" + origen).val() , 'crear':'true'}, function(data){/*Asociamos o creamos anexos y contratos cuando sea necesario*/
        $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_clianexo.php", {ccosto: $("#" + origen).val(), 'group':true}, function() {
            if ($("#id_bitacora").val() != "") {
                $("#anexo").val($("#id_anexo").val());
            }
            cargarServicios('anexo','servicio');
        });
    });    
}

function cargarServicios(origen, destino){
    /*var dir = "WEB-INF/Controllers/Ajax/updates.php";
    var tipo = 1; /*0: servicio global, 1: servicio particular.*/
    /*if($("#tipo_servicio").length){
        tipo = $("#tipo_servicio").val();
    }
    $("#contenidos_invisibles").load(dir,{'idAnexoClienteCC': $("#" + origen).val(), 'catalogo_servicios': 'true', 'tipo':tipo}, function(){*/
	var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php"; 
	$("#" + destino).load(dir, {'idAnexoClienteCC': $("#" + origen).val(), 'catalogo_servicios': 'true', 'anexo_completo':true}, function(data){            
		if(precargado && $("#IdKServicio").length){               
			$("#"+destino).val($("#IdServicioInv").val()+"-"+$("#IdKServicio").val());
			precargado = false;
		}
	});
    //});    
}

function cargarclientes(origen, componente) {
    $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_clientes.php", {cliente: $("#" + origen).val()});
}

function mostrarOcultarDiv(radio, div, div_ocultar){
    if($('#'+radio).is(':checked')){
        $("."+div).show();
        $("."+div_ocultar).hide();
    }else{
        $("."+div).hide();
        $("."+div_ocultar).show();
    }
    
    if(radio == "radio_cliente" && $('#'+radio).is(':checked')){
        $("#cliente").rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el cliente"
            }
        }); 
        $("#localidad").rules('add', {
            required: true,
            messages: {
                required: " * Selecciona la localidad"
            }
        });
        $("#anexo").rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el anexo"
            }
        });
        $("#servicio").rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el servicio"
            }
        });
        $("#almacen_equipo").rules( "remove");
    }else{
        $("#cliente").rules( "remove");
        $("#localidad").rules( "remove");
        $("#anexo").rules( "remove");
        $("#servicio").rules( "remove");
        $("#almacen_equipo").rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el almacén"
            }
        });
    }
}

function cambiarSelectModeloCompatible(origen, destino, NoParte) {
    var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    //alert(destino+" "+NoParte);
    $("#" + destino).load(dir, {"idTipoComponente": $("#" + origen).val(), "NoParteEquipo": NoParte}, function(){
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
}

function quitarblancos(id){
    var val = $("#"+id).val();
    val = val.replace(" ","");
    $("#"+id).val(val);
}
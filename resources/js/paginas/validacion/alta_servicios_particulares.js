function cargarServicios(tipo, id, select, table){
    $("#"+select).hide();
    ocultarTablas();    
    $("#"+select).load("../WEB-INF/Controllers/Ajax/CargaSelect.php", {'tipoServicio': tipo, 'campoID':id}, function(){
        $("#"+select).show();
        $("#"+table).show();
    });
}

function ocultarTablas(){
    $("#table_impresoras_parti").hide();
    $("#table_fa_parti").hide();
}

var servicio = "";
function setServicio(valor){    
    servicio = valor;    
}

$(document).ready(function() {
    var form = "#table_impresoras_parti";
    var paginaExito = "validacion/lista_servicios_parti.php";
    var controlador = "../WEB-INF/Controllers/Validacion/Controler_ServicioIM.php";
    
    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");
    
    $(".button").button().css('height','30px').css('width','90px').css('font-size','12px');
    
    if($("#tipo_servi").val()!=""){
        if($("#tipo_servi").val() == "fa"){
            $('input:radio[name=tipo_equipo_parti]')[1].checked = true;
            cargarServicios('c_serviciofa','IdServicioFA','c_servicio_parti_fa','table_fa_parti');
        }else{
            $('input:radio[name=tipo_equipo_parti]')[0].checked = true;
            cargarServicios('c_servicioim','IdServicioIM','c_servicio_parti','table_impresoras_parti');
        }
    }
    
    /*validate form*/
    $(form).validate({
        rules: {            
            c_servicio_parti: {selectcheck: true}
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
            $.post(controlador, {'form': $(form).serialize(), 'servicio':servicio, 'IdAnexo':$("#idClaveAnexoCC").val()}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/                      
                    $('#mensaje_serviciosp2').html("El servicio se guard\u00f3  correctamente");
                    cargarDependencia("servicios_p2",paginaExito+"?tipo=impresoras&idkanexo="+$("#idClaveAnexoCC").val()+"&ClaveCliente="+$("#claveClienteS").val(),$("#claveAnexo").val(),null,null);
                    //$( "#cancelar" ).trigger( "click" );/*llamamos el evento onclick del boton cancelar para que regrese a la pantalla de lista*/
                } else {
                    $('#mensaje_serviciosp2').html(data);                    
                }
            });
        }
    });        
});
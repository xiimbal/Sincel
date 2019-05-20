$(document).ready(function() {
    var form = "#form_equipo";
    var controlador = "WEB-INF/Controllers/Controller_AgregarEquipo.php";
    if($("#back_folder").length){
        controlador = "../"+controlador;
    }

    $(".boton").button();
    
    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            serie: {required: true},
            modelo: {required: true},
            contrato: {selectcheck: true},
            anexo: {selectcheck: true},
            servicio: {selectcheck: true}
        },
        messages: {
            serie: {required: " * Ingresa el número de serie"},
            modelo: {required: " * Selecciona el modelo"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Guardando el equipo ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {
                    finished();
                    $("#mensajes").html(data);
                    if(!$("#independiente").length){//Si la venta no es independiente, se recarga la pagina
                        setTimeout(function() {
                            location.reload();
                            $("#mensajes").html(data);
                        }, 4000);
                    }else{
                        $("#boton_regresar").click();
                    }
                    //cambiarContenidos("contrato/alta_equipo.php", "Alta equipo");
                } else {
                    $("#mensajes").html(data);
                    finished();
                }
            });
            $("#divinfo").empty();
        }
    });
    
    $(".filtroselect").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });    
});

function quitarblancos(id) {
    var val = $("#" + id).val();
    val = val.replace(" ", "");
    $("#" + id).val(val);
}


function cargarContratos(origen, destino){
    alert(origen+" "+destino);
    var dir = "../WEB-INF/Controllers/Ajax/CargaSelect.php";
    $("#" + destino).load(dir, {'cliente': $("#" + origen).val(), 'contrato': 'true'});
}

function cargarAnexos(origen, destino, cc, servicio){    
    /*En caso de que no exista, creamos anexos y/o asociamos a las localidad elegida. Tambien se crean los servicios*/
    var dir = "../WEB-INF/Controllers/Ajax/updates.php";
	//$("#contenidos_invisibles").load(dir, {'contrato': $("#" + origen).val(), 'cc':cc , 'client':$("#cliente").val(), 'anexo': 'true'}, function(data){        
	$("#contenidos_invisibles").load(dir, {'cc':cc , 'crear':'true'}, function(data){/*Asociamos o creamos anexos y contratos cuando sea necesario*/    
        dir = "../WEB-INF/Controllers/Ventas/Controller_select_clianexo.php";/*Cargamos los anexos*/
        $("#" + destino).load(dir, {'ccosto':cc , 'group':true, 'omite_selecciona':true}, function(data){            
            cargarServicios(destino, servicio);
        });
    });        
}

function actualizarAnexo(contrato, anexo, servicio, cc, destino){
    /*En caso de que no exista, creamos anexos y/o asociamos a las localidad elegida. Tambien se crean los servicios*/
    var dir = "../WEB-INF/Controllers/Ajax/updates.php";
    $("#contenidos_invisibles").load(dir, {'cc':cc , 'crear':'true'}, function(data){/*Asociamos o creamos anexos y contratos cuando sea necesario*/    
        dir = "../WEB-INF/Controllers/Ajax/updates.php";
        $("#contenidos_invisibles").load(dir, {'localidad':$("#"+cc).val(), 
            'anexo':$("#"+anexo).val(), 'contrato':$("#"+contrato).val(), 'IdAnexo':true}, function(data){   
            //alert(data);
            $("#"+destino).val(data);
        });
    });
}

function cargarServicios(origen, destino){
    /*var dir = "../WEB-INF/Controllers/Ajax/updates.php";
    var tipo = 1; /*0: servicio global, 1: servicio particular.*/
    /*if($("#tipo_servicio").length){
        tipo = $("#tipo_servicio").val();
    }
    $("#contenidos_invisibles").load(dir,{'idAnexoClienteCC': $("#" + origen).val(), 'catalogo_servicios': 'true', 'tipo':tipo}, function(){*/     
	var dir = "../WEB-INF/Controllers/Ajax/CargaSelect.php";    
	$("#" + destino).load(dir, {'idAnexoClienteCC': $("#" + origen).val(), 'catalogo_servicios': 'true', 'anexo_completo':true}, function(data){
		
	});
    //});    
}
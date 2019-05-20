$(document).ready(function(){
    $("#no_serie").keyup(function(event){
        if(event.keyCode == 13){
            $("#aceptar_bitacora").click();
        }
    });
    
    $("#modelo").keyup(function(event){
        if(event.keyCode == 13){
            $("#aceptar_bitacora").click();
        }
    });
    
    $("#id_solicitud").keyup(function(event){
        if(event.keyCode == 13){
            $("#aceptar_bitacora").click();
        }
    });
    
    $("#id_bitacora").keyup(function(event){
        if(event.keyCode == 13){
            $("#aceptar_bitacora").click();
        }
    });
    
    $("#cliente").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
});

function cargarBitacora(pagina, serie, modelo, solicitud, bitacora, claveCliente,contador_ckb){    
    var contador = "";
    if ($("#"+contador_ckb).is(":checked")) {
        contador = "true";
        if($("#"+serie).val() == "" && $("#"+solicitud).val() == "" && $("#"+bitacora).val() == ""){
            if(!confirm("Esta seguro que desea realizar esta consulta?, podría tardar varios minutos. Le recomendamos usar otros filtros.")){
                return false;
            }
        }
    }
    
    if($("#"+solicitud).val()!="" && ! $.isNumeric($("#"+solicitud).val())){
        $("#error_solicitud").show();
        return false;
    }
    
    if($("#"+bitacora).val()!="" && ! $.isNumeric($("#"+bitacora).val())){
        $("#error_bitacora").show();
        return false;
    }        
    
    loading("Cargando ...");
    $("#contenidos").load(pagina,
    {'serie':$("#"+serie).val(), 'modelo':$("#"+modelo).val(), 'solicitud':$("#"+solicitud).val(), 'bitacora':$("#"+bitacora).val(),
        'ClaveCliente':$("#"+claveCliente).val(),'contador':contador}, function(){
        $(".button").button();
        finished();
        return true;
    });
    
    return false;
}

function submitform() {
    $("#FormularioExportacion").submit();
}
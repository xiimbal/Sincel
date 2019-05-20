cargaSelect = "WEB-INF/Controllers/Ajax/CargaSelect.php";
detalleHistorico = "contrato/detalle_historico_servicios.php";
$(document).ready(function(){
   
   $(".boton").button();
   
   $(".filtroselect").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
   
   $("#cliente").change(function(){
       cargaContrato();
   });
   
   $("#contrato").change(function(){
       cargaAnexo();
   });
   
   $("#enviar").click(function(){
       $("#servicios").load(detalleHistorico,{anexo:$("#anexo").val()},function(data){
           
       });
   });
   
});

function cargaContrato(){
    $("#contrato").load(cargaSelect,{idCliente:$("#cliente").val(),pantalla:"HS"},function(data){
            $("#contrato").multiselect({
             multiple: false,
             noneSelectedText: "No ha seleccionado",
             selectedList: 1
         }).multiselectfilter({
             label: 'Filtro',
             placeholder: 'Escribe el filtro'
         });   
    });
}

function cargaAnexo(){
    $("#anexo").load(cargaSelect,{idContrato:$("#contrato").val(),pantalla:"HS"},function(data){
                $("#anexo").multiselect({
                multiple: false,
                noneSelectedText: "No ha seleccionado",
                selectedList: 1
            }).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
    });
}
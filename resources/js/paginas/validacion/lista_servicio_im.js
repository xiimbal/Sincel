function cambiarContenidoServicio(div, pagina, nuevo, anexo, idKServicio, CC){
    $("#cargando_servicioim").show();
    
    if(idKServicio === "ve"){
        var paginaNueva = "cliente/validacion/serviciosEspeciales.php?CC=" + CC;
        
        if($("#independiente").length){
            paginaNueva = "../"+paginaNueva;
        }
        
        $("#"+div).load(paginaNueva,{'anexo':anexo}, function(){
            $("#cargando_servicioim").hide();
        });
    }else{     
        var s_nuevo = "";
        if(nuevo){
            s_nuevo = "&Nuevo=si";
        }
        if($("#independiente").length){
            pagina = "../"+pagina;
        }
        $("#"+div).load(pagina+s_nuevo,{'anexo':anexo, 'idKServicio':idKServicio}, function(){
            $("#cargando_servicioim").hide();
        });
    }
}

function eliminarServicioIM(IdKServicio, div, pagina, anexo, cc){
    if(confirm("Esta seguro que desea eliminar este servicio?")){
        var controlador = "WEB-INF/Controllers/Validacion/Controler_ServicioIM.php";            
        if($("#independiente").length){
            controlador = "../"+controlador;
        }
        $("#mensaje_im").load(controlador,{'IdKServicio':IdKServicio,'eliminar':true}, function(data){
            if($("#independiente").length){
                cargarDependencia(div,"../cliente/validacion/"+pagina,anexo,null,cc);
            }else{
                cargarDependencia(div,"ventas/validacion/"+pagina,anexo,null,cc);
            }            
            $("#mensaje_im").text(data);        
        });
    }
}

function eliminarServicioGIM(IdKServicio, div, pagina, anexo, cc){    
    if(confirm("Esta seguro que desea eliminar este servicio?")){
        var controlador = "WEB-INF/Controllers/Validacion/Controler_ServicioGIM.php";    
        if($("#independiente").length){
            controlador = "../"+controlador;
        }
        $("#mensaje_im").load(controlador,{'IdKServicio':IdKServicio,'eliminar':true}, function(data){
            if($("#independiente").length){
                cargarDependencia(div,"../cliente/validacion/"+pagina,anexo,null,cc);
            }else{
                cargarDependencia(div,"ventas/validacion/"+pagina,anexo,null,cc);
            }
            $("#mensaje_im").text(data);
        });
    }
}

function eliminarServicioFA(IdKServicio, div, pagina, anexo, cc){
    if(confirm("Esta seguro que desea eliminar este servicio?")){
        var controlador = "WEB-INF/Controllers/Validacion/Controler_ServicioFA.php";    
        if($("#independiente").length){
            controlador = "../"+controlador;
        }
        $("#mensaje_im").load(controlador,{'IdKServicio':IdKServicio,'eliminar':true}, function(data){
            if($("#independiente").length){
                cargarDependencia(div,"../cliente/validacion/"+pagina,anexo,null,cc);
            }else{
                cargarDependencia(div,"ventas/validacion/"+pagina,anexo,null,cc);
            }
            $("#mensaje_im").text(data);
        });
    }
}

function eliminarServicioGFA(IdKServicio, div, pagina, anexo, cc){
    if(confirm("Esta seguro que desea eliminar este servicio?")){
        var controlador = "WEB-INF/Controllers/Validacion/Controler_ServicioGFA.php";    
        if($("#independiente").length){
            controlador = "../"+controlador;
        }
        $("#mensaje_im").load(controlador,{'IdKServicio':IdKServicio,'eliminar':true}, function(data){
            if($("#independiente").length){
                cargarDependencia(div,"../cliente/validacion/"+pagina,anexo,null,cc);
            }else{
                cargarDependencia(div,"ventas/validacion/"+pagina,anexo,null,cc);
            }
            $("#mensaje_im").text(data);
        });
    }
}
$(document).ready(function() {
    var form = "#fupload";
    var controlador = "reportes/upload_file.php";

    $.validator.addMethod('filesize', function(value, element, param) {
        return this.optional(element) || (element.files[0].size <= param)
    });

    /*validate form*/
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            file: {filesize: 1048576, accept: "LOG|csv|log"}
        },
        messages: {
            file: "El archivo orden de origen debe ser LOG o CSV y pesar menos de un mega 1MB"
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Procesando archivo, puede tomar algunos minutos ...");            
            /* stop form from submitting normally */
            event.preventDefault();
            $("#loader").hide();
            var inputFileImage = document.getElementById("file");
            var file = inputFileImage.files[0];
            var data = new FormData();
            data.append("file", file);
            var url = controlador;
            $.ajax({
                url: url,
                type: "POST",
                contentType: false,
                data: data,
                processData: false,
                cache: false
            }).done(function(data) {
                //$("#mensajes").text(data);
                var pos = data.indexOf("IdReporte:");
                var id;
                if(pos>=0){
                    var pos_final = data.lastIndexOf(".Fin.");
                    if(pos_final >= 0){
                        id = data.substring(pos+10,pos_final);                        
                    }else{
                       id = 0; 
                    }
                }else{
                    id = 0;
                }
                //alert(id);
                editarRegistro("reportes/selector_reporteUso.php",id);
                /*$("#loader").show();                
                finished();*/
            });
        }
    });
});
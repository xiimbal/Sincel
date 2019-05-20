var paginaExito = "compras/lista_archivos.php";
$(document).ready(function() {
    var form = "#frmArchivos";
    var controlador = "WEB-INF/Controllers/compras/Controler_Archivos_Proveedor.php";
    
    $.validator.addMethod('filesize', function(value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    });
    /*validate form*/
    $(form).validate({
        rules: {
            factura_file: {factura: 1048576, accept: "png|jpe?g|pdf|doc|docx"},
            xml: {filesize: 1048576, accept: "xml"}
        },
        messages: {
            factura: "El archivo factura debe ser JPG,PNG,PDF,DOC,DOCX,XLS,XLSX y pesar menos de un mega 1MB",
            xml: "El archivo XML debe ser XML y pesar menos de un mega 1MB"
        }
    });
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            // uploadFileFactura();
            uploadFileXMl();
        }
    });
    $('#txtFechaInicioL').datepicker({dateFormat: 'yy-mm-dd'});
    $('#txtFechaInicioL').mask("9999-99-99");
    $('#txtFechaFinL').datepicker({dateFormat: 'yy-mm-dd'});
    $('#txtFechaFinL').mask("9999-99-99");
    
    $("#slIdOrdenCompra").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter();
});
function uploadFileXMl() {
    var intp = $("#xml").val();
    var id = $("#slIdOrdenCompra").val();
    if (intp !== "") {
        loading("Subiendo XML ...");
        var formData = new FormData($("#frmArchivos")[0]);
        formData.append("tipo", "xml");
        formData.append("idOC", id);
        $.ajax({
            url: 'WEB-INF/Controllers/compras/Controler_Archivos_Proveedor.php',
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $("#mensajes").text("Subiendo XML ...");
            },
            success: function(data) {
                var res = data.split("*--* ");
                $("#mensajes").text(res[0]);
                finished();
                if (res[0].toString().indexOf("Error:") === -1) {
                    uploadFileFactura(res[1]);
                    //alert("Success XML: "+data);
                }
            },
            error: function(data) {
                $("#mensajes").text(data);
                //alert("Error Factura XML: "+data);
                finished();
            }
        });
    }
}
function uploadFileFactura(idFactura) {
    var id = $("#slIdOrdenCompra").val();
    var intp = $("#factura").val();
    if (intp !== "") {
        loading("Subiendo factura ...");
        var formData = new FormData($("#frmArchivos")[0]);
        formData.append("tipo", "factura");
        formData.append("idOC", id);
        formData.append("idFactura", idFactura);
        $.ajax({
            url: 'WEB-INF/Controllers/compras/Controler_Archivos_Proveedor.php',
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                //$("#mensajes").text("Subiendo lista de partes...");
            },
            //una vez finalizado correctamente
            success: function(data) {
                $("#mensajes").text(data);
                cambiarContenidos(paginaExito, "Compras > Facturas");
                //alert("Success Factura PDF: "+data);
                finished();
            },
            error: function(data) {
                $("#mensajes").text(data);
                //alert("Error Factura PDF: "+data);
                finished("");
            }

        });
    } else {
        uploadFileXMl();
    }
}
function buscarFactura() {
    loading("Cargando ...");
    var folio = $("#txtFolioL").val();
    var proveedor = $("#slProveedorL").val();
    var fechaInicio = $("#txtFechaInicioL").val();
    var fechaFin = $("#txtFechaFinL").val();
    var empresa = $("#slEmpresaL").val();
    var mostrar = 0;
    if ($("#ckMostrar").is(":checked")) {
        mostrar = "1";
    }
    $('#contenidos').load("compras/lista_archivos.php", {"folio": folio, "proveedor": proveedor, "fechaInicio": fechaInicio, "fechaFin": fechaFin, "empresa": empresa, "mostrar": mostrar}, function() {
        finished();
    });
}


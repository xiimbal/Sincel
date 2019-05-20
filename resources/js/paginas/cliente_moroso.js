/**
 * Marca/Desmarca el cliente a moroso
 * @param {type} clave clave del cliente
 * @param {type} estatus estatus actual del cliente (1: normal, 2:moroso)
 * @returns {undefined} void
 */
function marcarClienteMoroso(clave, estatus, tipomoroso, ddl) {
    if (tipomoroso !== "1" && tipomoroso !== "") {
        lanzarPopUp('Moroso', 'facturacion/Alta_Moroso.php?id=' + clave);
    } else {
        loading("Cargando ...");
        $("#mensajes").load("WEB-INF/Controllers/Ajax/updates.php", {"cliente": clave, "estatus": estatus}, function(data) {
            cambiarContenidos("facturacion/clientes_morosos.php", "Clientes morosos");
            $("#mensajes").text(data);
            finished();
        });
    }
}

function marcarClienteMorosoSuspendido(clave, ddl_estatus, tipomoroso) {
    if (tipomoroso !== "1" && $("#" + ddl_estatus).val() !== "0" && tipomoroso !== "" ) {
        lanzarPopUp('Moroso', 'facturacion/Alta_Moroso.php?id=' + clave);
    } else {
        var estatus = $("#" + ddl_estatus).val();
        if (estatus == "2") {
            loading("Cargando ...");
            $("#mensajes").load("WEB-INF/Controllers/Ajax/updates.php", {"cliente": clave, "estatus": estatus, "suspendido": "false"}, function(data) {
                //cambiarContenidos("facturacion/clientes_morosos.php", "Clientes morosos");
                $("#mensajes").text(data);
                finished();
                marcarClienteMoroso(clave, "0",tipomoroso,ddl_estatus);//Se manda un estatus diferente a 1 para que en el controller se le ponga 1
            });
        } else if (estatus == "1") {
            loading("Cargando ...");
            $("#mensajes").load("WEB-INF/Controllers/Ajax/updates.php", {"cliente": clave, "estatus": estatus, "suspendido": "false"}, function(data) {
                //cambiarContenidos("facturacion/clientes_morosos.php", "Clientes morosos");
                $("#mensajes").text(data);
                finished();
                marcarClienteMoroso(clave, "1",tipomoroso,ddl_estatus);//Se manda un estatus igual a 1 para que en el controller se le ponga 2
            });
        } else if (estatus == "0") {//Marcar como suspendido
            loading("Cargando ...");
            $("#mensajes").load("WEB-INF/Controllers/Ajax/updates.php", {"cliente": clave, "estatus": estatus, "suspendido": "true"}, function(data) {
                cambiarContenidos("facturacion/clientes_morosos.php", "Clientes morosos");
                $("#mensajes").text(data);
                finished();
            });
        }
    }
}

function VerClientesFacturacio()
{
    var pagina = "facturacion/clientes_morosos.php";
    var controler = "WEB-INF/Controllers/Controler_ClienteExportacion.php";
    loading("Cargando ...");
    $("#mensajes").text("Calculando nuevos clientes morosos ...");
    $('#mensajes').load(controler, {"CCliente": "CCliente"}, function() {
        $('#contenidos').load(pagina, function() {
            $(".button").button();
            finished();
            CilentesNoMorosos();
        });
    });
}

function CilentesNoMorosos() {
    var pagina = "facturacion/clientes_morosos.php";
    var controler = "WEB-INF/Controllers/Controler_MarcarNoMorosos.php";
    loading("Cargando ...");
    $("#mensajes").text("Calculando clientes no morosos ...");
    $('#mensajes').load(controler, {"sesion": true}, function() {
        $('#contenidos').load(pagina, function() {
            $(".button").button();
            finished();
            /*setTimeout(function() {
             cambiarContenidos("facturacion/clientes_morosos.php");
             }, 3000);*/

        });
    });
}

function NoPonerMoroso(nombre)
{
    loading("Cargando ...");
    var activadoCliente = 0;
    if ($("#" + nombre).is(':checked'))
        activadoCliente = 1;
    else
        activadoCliente = 0;

    var controler = "WEB-INF/Controllers/Controler_ClienteExportacion.php?CCliente=nada";
    var pagina = "facturacion/clientes_morosos.php";
    $('#mensajes').load(controler, {"claveCliente": nombre, "tipo": activadoCliente}, function() {
        $('#contenidos').load(pagina, function() {
            $(".button").button();
            finished();
        });
    });
//    var claves = new Array();
//    var contador = 0;
//    loading("Cargando ...");
//    $("input:checkbox:checked").each(function() {
//        //cada elemento seleccionado
//        claves[contador] = $(this).val();
//        contador++;
//
//    });
//    $('#mensajes').load(controler, {"claveCliente": claves}, function() {
//        $('#contenidos').load(pagina, function() {
//            $(".button").button();
//            finished();
//        });
//    });
}
//


<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();
?>
<!DOCTYPE html>
<html lang="es">
    <head>  
        <title>Reporte de Técnico Ticket</title>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_ticket.js"></script>
         <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <!-- FontAwesome para iconos -->
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">
    </head>

    <body>
     <div class="principal">
        <form method="POST" action="../Cron_ReporteSemanalTicket.php" target="_blank">
            <div class="container-fluid">
                <div class="form-row">
                <div class="form-group  col-md-4">
                    <label>Fecha inicio</label>
                    <input class="form-control"  id="fecha_inicio" name="fecha_inicio" class="fecha" value="<?php echo $FechaInicio; ?>" />
                </div>
                <div class="form-group col-md-4">
                    <label>Fecha final</label>
                    <input class="form-control" id="fecha_fin" name="fecha_fin" class="fecha" value="<?php echo $FechaFin; ?>" /> 
                </div>
                <div class="form-group col-md-4">                   
                    <label>Tipo Reporte</label>
                        <select class="form-control" id="reporte_ticket" multiple name="reporte_ticket" class="select">
                            <?php
                            /* Inicializamos la clase */
                            $query = $catalogo->obtenerLista("SELECT e.IdEstado, e.Nombre FROM c_estado AS e
                                                            INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND kfe.IdFlujo = 1 ORDER BY Nombre;");
                            echo "<option value=''>Todos los tipos</option>";
                            while ($rs = mysql_fetch_array($query)) {                               
                                echo "<option value='" . $rs['IdEstado'] . "'>" . $rs['Nombre'] . "</option>";
                            }
                            ?> 
                        </select>
                    </div>
                    <div class="form-group  col-md-4"> 
                    <label>Área de atención</label>
                        <select multiple class="form-control" id="area_ticket" name="area_ticket"  class="select">
                            <?php
                            /* Inicializamos la clase */
                            $query = $catalogo->obtenerLista("SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
                                INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) ORDER BY Nombre;");
                            echo "<option value=''>Todos las áreas</option>";
                            while ($rs = mysql_fetch_array($query)) {                                
                                echo "<option value='" . $rs['IdEstado'] . "'>" . $rs['Nombre'] . "</option>";
                            }
                            ?> 
                        </select>
                    </div>
                    <div class="form-group col-md-4"> 
                    <label>Cliente: </label>
                        <select multiple class="form-control" id="cliente" name="cliente"  class="select">
                            <option value="">Todos los clientes</option>
                            <?php
                            /* Inicializamos la clase */
                            $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                            while ($rs = mysql_fetch_array($query)) {                                
                                echo "<option value='" . $rs['ClaveCliente'] . "'>" . $rs['NombreRazonSocial'] . "</option>";
                            }
                            ?> 
                        </select>
                    </div>
                    <div class="form-group col-md-4"> 
                    <label>Usuario: </label>
                        <select multiple class="form-control"  id="usuario" name="usuario"  class="select">
                            <option value="">Todos los usuarios</option>
                            <?php
                            /* Inicializamos la clase */
                            $query = $catalogo->getListaAlta("c_usuario", "Nombre");
                            while ($rs = mysql_fetch_array($query)) {                                
                                echo "<option value='" . $rs['IdUsuario'] . "'>" . $rs['Nombre'] . " ".$rs['ApellidoPaterno']." ".$rs['ApellidoMaterno']."</option>";
                            }
                            ?> 
                        </select>
                    </div>   
                    <input type="hidden" id="IdEmpresa" name="IdEmpresa" value="<?php echo $_SESSION['idEmpresa']; ?>"/>
                    <input  class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" type="submit" value="Generar Reporte" class="button"/>  
                  </div>
              </div>
        </form>     
    </body>
</html>

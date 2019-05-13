<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Empresa.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/lib/PHPImagen.lib.php");

$catalogo = new Catalogo();
$contadorProductos = 0;
$empresa = new Empresa();
$id = 0;
if (isset($_GET['id']) && $_GET['id'] != "") {
    $empresa->setId($_GET['id']);
    $empresa->getRegistrobyID();
    $id = $empresa->getId();
}
?>
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/Multi/Editar_Empresa.js"></script>
    </head>
    <body>
        <form id="formcliente">
            <table style=" width:100%">
                <tr>
                    <td>
                        <table style=" width:100%">
                            <tr>
                                <td style=" width:140px"> Nombre/razón social:</td>
                                <td><input name="RazonSocial" type="text" maxlength="150" id="RazonSocial" value="<?php
                                    if ($empresa->getRazonSocial() != "") {
                                        echo $empresa->getRazonSocial();
                                    }
                                    ?>" style="width:300px;" /> <span id="MainContent_reqValRazonSocial" style="display:none;"></span></td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        &nbsp;</td>
                    <td style=" vertical-align:top;">
                    </td>
                </tr>
            </table>
            <fieldset>
                <legend>Domicilio/Domicilio Fiscal<br /></legend>
                <table style=" width:100%">
                    <tr>
                        <td  style=" width:100px">
                            Tipo de domicilio:<br />
                        </td>
                        <td>
                            <input name="TipoDomicilioF" type="text" value="Domicilio cliente (fiscal)" maxlength="50" id="TipoDomicilioF" disabled="disabled" class="aspNetDisabled" style="width:200px;" />
                            <span id="MainContent_reqValTipoDomicilio" style="display:none;"></span>
                        </td>
                    </tr>
                    <tr>
                        <td >
                            Calle:<br />
                        </td>
                        <td>
                            <input name="CalleF" type="text" maxlength="100" id="CalleF" value="<?php
                            if ($empresa->getCalle() != "") {
                                echo $empresa->getCalle();
                            }
                            ?>" style="width:350px;" />
                            <span id="Calle" style="display:none;"></span>
                        </td>
                        <td >
                            No.exterior:<br /> 
                        </td>
                        <td>
                            <input name="NoExteriorF" type="text" maxlength="30" id="NoExteriorF" value="<?php
                            if ($empresa->getNoExterior() != "") {
                                echo $empresa->getNoExterior();
                            }
                            ?>" style="width:100px;" />
                            <span id="MainContent_reqValNoExterior" style="display:none;"></span>
                        </td>
                        <td >
                            No. interior:<br />
                        </td>
                        <td>
                            <input name="NoInteriorF" type="text" maxlength="30" id="NoInteriorF" value="<?php
                            if ($empresa->getNoInterior() != "") {
                                echo $empresa->getNoInterior();
                            }
                            ?>" style="width:100px;" />

                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td >
                            Colonia:<br />
                        </td>
                        <td>
                            <input name="ColoniaF" type="text" maxlength="50" id="ColoniaF" value="<?php
                            if ($empresa->getColonia() != "") {
                                echo $empresa->getColonia();
                            }
                            ?>"style="width:250px;" />
                            <span id="MainContent_reqValColonia" style="display:none;"></span>
                        </td>
                        <td style=" width:40px"></td>
                        <td >
                            Estado:<br />
                        </td>
                        <td>
                            <select name="EstadoF" id="EstadoF">
                                <?php
                                $nombres = Array("Selecciona un estado", "Aguascalientes", "Baja California", "Baja California Sur", "Campeche", "Ciudad de México" ,"Coahuila", "Colima", "Chiapas", "Chihuahua",  "Durango", "Estado de México", "Guanajuato", "Guerrero", "Hidalgo", "Jalisco", "Michoacán", "Morelos", "Nayarit", "Nuevo León", "Oaxaca", "Puebla", "Querétaro", "Quintana Roo", "San Luis Potosí", "Sinaloa", "Sonora", "Tabasco", "Tamaulipas", "Tlaxcala", "Veracruz", "Yucatán", "Zacatecas");
                                $values = Array("",                      "Aguascalientes", "Baja California", "Baja California Sur", "Campeche", "Ciudad de México" ,"Coahuila", "Colima", "Chiapas", "Chihuahua",  "Durango", "Estado de México", "Guanajuato", "Guerrero", "Hidalgo", "Jalisco", "Michoacán", "Morelos", "Nayarit", "Nuevo León", "Oaxaca", "Puebla", "Querétaro", "Quintana Roo", "San Luis Potosí", "Sinaloa", "Sonora", "Tabasco", "Tamaulipas", "Tlaxcala", "Veracruz", "Yucatán", "Zacatecas");
                                if ($empresa->getEstado() != "") {
                                    for ($var = 0; $var < count($values); $var++) {
                                        if (strtolower($values[$var]) == strtolower($empresa->getEstado())) {
                                            echo "<option value=\"" . $values[$var] . "\" selected>" . $nombres[$var] . "</option>";
                                        } else {
                                            echo "<option value=\"" . $values[$var] . "\">" . $nombres[$var] . "</option>";
                                        }
                                    }
                                } else {
                                    for ($var = 0; $var < count($values); $var++) {
                                        echo "<option value=\"" . $values[$var] . "\">" . $nombres[$var] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                            <span id="MainContent_reqVEstado" style="display:none;"></span>
                        </td>
                    </tr>
                    <tr>
                        <td >
                            Delegación:<br />
                        </td>
                        <td>
                            <input name="DelegacionF" type="text" maxlength="50" id="DelegacionF" value="<?php
                            if ($empresa->getDelegacion() != "") {
                                echo $empresa->getDelegacion();
                            }
                            ?>" style="width:200px;" />
                            <span id="MainContent_reqValDelegacion" style="display:none;"></span>
                        </td>
                        <td >
                            C.P:
                        </td>
                        <td>
                            <input name="CPF" type="text" maxlength="30" id="CPF" value="<?php
                            if ($empresa->getCP() != "") {
                                echo $empresa->getCP();
                            }
                            ?>" />
                            <span id="MainContent_reqValCP" style="display:none;"></span>
                            <span id="MainContent_regValCP" style="display:none;"></span>

                        </td>
                        <td>
                            Régimen Fiscal
                        </td>
                        <td>
                            <select id="regimenfiscal" name="regimenfiscal">
                                <option value="">Selecciona una opción</option>
                            <?php
                                $result = $catalogo->getListaAlta("c_regimenfiscal", "IdRegimenFiscal");
                                while($rs = mysql_fetch_array($result)){
                                    $s = "";
                                    if ((int)$empresa->getRegimenFiscal() == (int)$rs['IdRegimenFiscal']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='".$rs['IdRegimenFiscal']."' $s>".$rs['IdRegimenFiscal']." ".$rs['Descripcion']."</option>";
                                }
                            ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Logo</td>
                        <td>
                            <input type="file" name="logo" id="logo" class="invalid maxSize" data-max-size='25kb' data-type='image'/>
                            <span class="error_file" style="color: red; background: #FDD9DB;"></span>
                        </td>
                        <td>
                            <?php if ($empresa->getArchivoLogo() != "") {                                                                
                            ?>
                                <image src="LOGOS/<?php echo $empresa->getArchivoLogo() ?>" height="100" width="100"/>
                                <input type="hidden" name="imagen_existe" id="imagen_existe" value="1"/>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </fieldset>
            <fieldset>
                <legend>Datos de la Facturación</legend>
                <table style="width:100%">
                    <tr>
                        <td style="width:8%;">
                            RFC:<br />
                        </td>
                        <td style="width:22%;">
                            <input name="RFCD" type="text" maxlength="50" id="RFCD" value="<?php
                            if ($empresa->getRFC() != "") {
                                echo $empresa->getRFC();
                            }
                            ?>" style="width:250px;" />
                            <span id="MainContent_reqValRFC" style="display:none;"></span>
                            <span id="MainContent_regValRFC" style="display:none;"></span>
                        </td>
                        <td style="width: 12%;">
                            Factura los tickets
                        </td>
                        <td style="width: 10%; text-align: left;"><input type="checkbox" id="tickets" name="tickets" <?php
                            if($empresa->getFacturaTickets() == "1"){
                                echo "checked";
                            }
                        ?>></td>
                        <td style="width: 10%;">
                            Usar CFDI 3.3
                        </td>
                        <td style="width: 4%;"><input type="checkbox" id="cfdi33" name="cfdi33" <?php
                            if($empresa->getCfdi33() == "1"){
                                echo "checked";
                            }
                        ?>></td>
                        <td style="width: 36%">
                            
                        </td>
                    </tr>
                    <tr>
                        <td>CFDI</td>
                        <td>
                            <select id="cfdi" name="cfdi">
                                <option value="">Selecciona el CFDI</option>
                                <?php
                                $query = $catalogo->obtenerLista("SELECT * FROM c_cfdi ORDER BY nombre");
                                if ($empresa->getId_Cfdi() != "") {
                                    while ($rs = mysql_fetch_array($query)) {
                                        if ($empresa->getId_Cfdi() == $rs['id_Cfdi']) {
                                            echo "<option value='" . $rs['id_Cfdi'] . "' selected>" . $rs['nombre'] . "</option>";
                                        } else {
                                            echo "<option value='" . $rs['id_Cfdi'] . "'>" . $rs['nombre'] . "</option>";
                                        }
                                    }
                                } else {
                                    while ($rs = mysql_fetch_array($query)) {
                                        echo "<option value='" . $rs['id_Cfdi'] . "'>" . $rs['nombre'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td>PAC</td>
                        <td>
                            <select id="pac" name="pac">
                                <option value="">Selecciona el PAC</option>
                                <?php
                                $query = $catalogo->obtenerLista("SELECT * FROM c_pac ORDER BY nombre");
                                if ($empresa->getId_pac() != "") {
                                    while ($rs = mysql_fetch_array($query)) {
                                        if ($empresa->getId_pac() == $rs['id_pac']) {
                                            echo "<option value='" . $rs['id_pac'] . "' selected>" . $rs['nombre'] . "</option>";
                                        } else {
                                            echo "<option value='" . $rs['id_pac'] . "'>" . $rs['nombre'] . "</option>";
                                        }
                                    }
                                } else {
                                    while ($rs = mysql_fetch_array($query)) {
                                        echo "<option value='" . $rs['id_pac'] . "'>" . $rs['nombre'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Serie</td>
                        <td>
                            <select name="serie" id="serie">
                                <option value="">Selecciona una serie</option>
                                <?php
                                    $result = $catalogo->obtenerLista("SELECT * FROM c_serie WHERE IdSerie <> 1;");
                                    while($rs = mysql_fetch_array($result)){
                                        $s = ($empresa->getIdSerie() == $rs['IdSerie'])? "selected" : "";
                                        echo '<option value="'.$rs['IdSerie'].'" '.$s.'>'.$rs['Prefijo'].'</option>';
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </fieldset>
            <fieldset>
                <legend>Productos SAT</legend>
                <table id="tProductos" style="width: 100%">
                    <thead>
                        <tr>
                            <td style="width: 30%;">Producto</td>
                            <td style="width: 30%;">Unidad Medida</td>
                            <td style="width: 20%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td style="width: 20%;">
                                <a onclick="agregarProducto(); return false;">
                                    <p style="float: right; cursor: pointer;" >Agregar Producto</p>
                                    <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" style="float: right; cursor: pointer;" />
                                </a>
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $consulta = "SELECT eps.IdEmpresaProductoSAT, 
                                CONCAT_WS(' ',um.ClaveUnidad,um.UnidadMedida) AS Unidad,
                                CONCAT_WS(' ',cps.ClaveProdServ,cps.Descripcion) AS Producto
                                FROM k_empresaproductosat eps
                                LEFT JOIN c_claveprodserv AS cps ON cps.IdProdServ = eps.IdClaveProdServ
                                LEFT JOIN c_datosfacturacionempresa AS dfe ON dfe.IdDatosFacturacionEmpresa = eps.IdDatosFacturacionEmpresa
                                LEFT JOIN c_unidadmedidaSAT AS um ON um.IdUnidadMedida = eps.IdUnidadMedida
                                WHERE eps.IdDatosFacturacionEmpresa = $id";
                            $result = $catalogo->obtenerLista($consulta);
                            while($rs = mysql_fetch_array($result)){
                                echo "<tr id='ar_$contadorProductos'>" .
                                    "<td style='width:30%;' style='text-align: center;'>" .
                                        "<input style='width:80%;' type='text' id='producto_$contadorProductos' name='producto_$contadorProductos' value='".$rs['Producto']."'/>" .
                                    "</td>" .
                                    "<td style='width:30%;' style='text-align: center;'>" .
                                        "<input style='width:80%;' type='text' id='unidadmedida_$contadorProductos' name='unidadmedida_$contadorProductos' value='".$rs['Unidad']."' />" .
                                    "</td>" .
                                    "<td style='text-align: center;'>" .
                                    "<a onclick='eliminarProducto($contadorProductos)' style='cursor: pointer;'>" .
                                    "<img class='imagenMouse' src='resources/images/Erase.png' title='Nuevo' style='float: right; cursor: pointer;' /></a></td>" .
                                    "<td><input type='hidden' id='id_$contadorProductos' name='id_$contadorProductos' value='".$rs['IdEmpresaProductoSAT']."'/></td>" .
                                    "</tr>" .
                                    "<tr id='ar_$contadorProductos'>" .
                                    "<td colspan=4>&nbsp</td>" .
                                    "</tr>";
                                $contadorProductos++;
                            }
                        ?>
                    </tbody>
                </table>
            </fieldset>
            <br />
            <div >
                <input id="chkActivo" type="checkbox" name="chkActivo" <?php
                if ($empresa->getActivo() == "1") {
                    echo "checked='checked'";
                } elseif ($empresa->getActivo() == "0") {
                    echo "";
                } else {
                    echo "checked='checked'";
                }
                ?> value="1"/><label for="Activo">Activo</label>
            </div>
            <br />
            <br />
            <?php
            if (isset($_GET['id']) && $_GET['id'] != "") {
                ?>
                <input type="hidden" value="<?php echo $_GET['id']; ?>" id="id" name="id"/>
                <?php
            }
            ?>
            <input type='hidden' id='contadorProductos' name='contadorProductos' value='<?php echo $contadorProductos; ?>'/>
            <table style=" width:100%; text-align:center">
                <tr>
                    <td>
                        <input type="submit" class="boton" name="Guardar" value="Guardar"  id="Guardar" />
                    </td>
                    <td>
                        <input type="button" onclick="cambiarContenidos('Multi/list_empresas.php', 'Empresas');
                        return false;" class="boton" name="Cancelar" value="Cancelar" id="Cancelar" />
                    </td>
                </tr>
            </table>
        </form> 
    </body>
</html>
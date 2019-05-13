<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/bibliotecaSoluciones.js"></script>
<script type="text/javascript">
    function filterTable(Stxt, table) {
       dehighlight(document.getElementById(table));
       if (Stxt.length > 0)
         highlight(Stxt.toLowerCase(), document.getElementById(table));
    }
</script>
 <script type="text/javascript">
/*
* Transform back each
* <span>preText <span class="highlighted">Stxt</span> postText</span>
* into its original
* preText Stxt postText
*/
function dehighlight(container) {
    for (var i = 0; i < container.childNodes.length; i++) {
        var node = container.childNodes[i];
        if (node.attributes && node.attributes['class'] && node.attributes['class'].value == 'highlighted') {
            node.parentNode.parentNode.replaceChild(document.createTextNode(
                    node.parentNode.innerHTML.replace(/<[^>]+>/g, "")),node.parentNode);
            // Stop here and process next parent
            return;
        } else if (node.nodeType != 3) {
            // Keep going onto other elements
            dehighlight(node);
        }
    }
}
/*
* Create a
* <span>preText <span class="highlighted">Stxt</span> postText</span>
* around each search Stxt
*/
function highlight(Stxt, container) {
    for (var i = 0; i < container.childNodes.length; i++) {
        var node = container.childNodes[i];
        if (node.nodeType == 3) {
            // Text node
            var data = node.data;
            var data_low = data.toLowerCase();
            if (data_low.indexOf(Stxt) >= 0) {
                //Stxt found!
                var new_node = document.createElement('span');
                node.parentNode.replaceChild(new_node, node);
                var result;
                while ((result = data_low.indexOf(Stxt)) != -1) {
                    new_node.appendChild(document.createTextNode(data.substr(0, result)));
                    new_node.appendChild(create_node(document.createTextNode(data.substr(result, Stxt.length))));
                    data = data.substr(result + Stxt.length);
                    data_low = data_low.substr(result + Stxt.length);
                }
                new_node.appendChild(document.createTextNode(data));
            }
        } else {
            // Keep going onto other elements
            highlight(Stxt, node);
        }
    }
}

function create_node(child) {
    var node = document.createElement('span');
    node.setAttribute('class', 'highlighted');
    node.attributes['class'].value = 'highlighted';
    node.appendChild(child);
    return node;
}
</script>
        <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        
        <!-- FontAwesome para iconos -->
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">

<style type="text/css">
    .highlighted
    {
       background-color:Yellow;
    }
</style>

<form id="formbiblioteca">
    <div class="principal">
        <div class="container-fluid"> 
           <div class="form-row">  

        <div class="form-group  col-md-4">
             <label>Palabra</label>
             <input class="form-control" type="text" name="palabra" id="palabra"/>  
        </div> 

        <div class="form-group  col-md-4">
             <label>Fecha Inicio</label><br>
             <input class="fecha form-control"   id="fecha_inicio" name="fecha_inicio" class="fecha"  />
        </div>

        <div class="form-group col-md-4">
             <label>Fecha Fin</label><br>
             <input class="fecha form-control"  id="fecha_fin" name="fecha_fin" class="fecha" />
        </div>

       
        <input class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" type="button" id="buscar" value="Buscar"/>
                

        <div id="busq" style="display: none;">Palabra a resaltar</div>
        <div id="busq2" style="display: none;">
        <input type="text" id="Stxt" onkeyup="filterTable(this.value,'tbusqueda')"/></div>
        
   </div>  
   </div>  
</form>
<div id="divinfo">         
</div>

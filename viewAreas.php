<?php
session_start();
if ($_SESSION["userid"] == null) {
    header("Location: index.php?error=Sin_sesion_iniciada");
}
if ($_SESSION["access_control"] != 1) {
    echo '<script>alert("No tienes permisos para ver esta pagina");window.location="bienvenida.php"</script>';
}
?>
<!DOCTYPE html>
<html >
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Areas</title>
    <link rel="stylesheet" type="text/css" href="css/dataTables.css">
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/sweetalert2.min.css" rel="stylesheet">
    <script src="js/jquery.min.js"></script>
    <script src="js/funcionesAreas.js"></script>
    <script src="js/dataTables.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="js/sweetalert2.min.js"></script>		    
</head>
<body>
        <?php include "php/navbar.php"; ?>      
        <center><h1><span class="label label-danger">Listado de areas</span></h1></center>

<div style="margin: 10px; text-align: right;">		        
    <button id="agregar" class="btn btn-success">Agregar area</button>                         
</div>            
<!---Tabla en donde se mostraran los registro--->
<table class="table table-hover" id="agrega-registros"></table>       
 <!-- Modal -->
 <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">          
      <div class="modal-content">
      
        <!-- modal header -->
        <div class="modal-header">
          <button type="button" class="close btn btn-danger" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="p"></h4>
        </div>
        <!-- modal header -->

        <!-- modal body-->
        <div class="modal-body">
        <form  class="formulario" id="formulario">
            <input type="hidden" id="id" name="id"/>    
                <div class="form-group">
                    <label for="nombre">Nombre del area: </label>
                    <input type="text" class="form-control" name="nombre" id="nombre">
                </div>
             </form>                              
        </div>
        <!-- modal body-->

        <!-- modal footer-->
        <div class="modal-footer">
            <button type="button" id="reg" class="btn btn-info" onclick="return agregaRegistro();">Registrar</button>          
            <button type="button" id="edi" class="btn btn-info" onclick="return Actualizar();">Actualizar</button>          
        </div>
        <!-- modal footer-->
      </div>
    </div>
  </div>                  
</body>
</html>

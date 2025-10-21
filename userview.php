<?php
session_start();
	require ('php/conexion.php');
if ($_SESSION["userid"] == null) {
    header("Location: index.php?error=Sin_sesion_iniciada");
}
if ($_SESSION["access_control"] != 1) {
    echo '<script>alert("No tienes permisos para ver esta pagina");window.location="bienvenida.php"</script>';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Usuarios</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">    
	<link href="bootstrap/css/jquery-ui.min.css"  rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="css/dataTables.css">
    <link href="css/estilo.css" rel="stylesheet">	
	<link href="css/select2.min.css" rel="stylesheet">
    <link href="css/select2-bootstrap.min.css" rel="stylesheet">
    <link href="css/sweetalert2.min.css" rel="stylesheet">
	<script src="js/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>	
    <script src="bootstrap/js/jquery-ui-1.12.1.min.js" ></script>    
    <script src="js/dataTables.js"></script>
	<script src="js/select2.min.js"></script>
    <script src="js/sweetalert2.min.js"></script>
    <script src="js/funcionesuser.js"></script>    				
</head>
<body>

  <?php include "php/navbar.php"; ?>
  
  <center><h1><span class="label label-danger">Listado de usuarios</span></h1></center>

<div style="margin: 10px; text-align: right;">		        
    <button id="agregar" class="btn btn-success">Agregar nuevo</button>                         
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
            <form  class="formulario" id="formusuarios">
                <input type="hidden" id="idUsu" name="idUsu"/>                
                <div class="form-group">
                    <label for="nomcompleto">Nombre y apellido(s)</label>
                    <input type="text" class="form-control" name="nomcompleto" id="nomcompleto"   required>
                </div>
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" class="form-control" name="username" id="username"   required>
                </div>        
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="text" class="form-control" name="password" id="password"  required>
                </div>
                <div class="form-group">
                    <label for="rol">Rol</label>
                    <select class="form-control" id="rol" name="rol" >
                    </select>
                </div>
                <div class="form-group">
                    <label for="fk_idarea">Area a la que pertenece</label>
                    <select class="form-control" id="fk_idarea" name="fk_idarea" style="width:100%">
                    </select>
                </div>

		        <div class="form-group">
                    <label for="fk_idsubarea">Sub-Area a la que pertenece</label>
                    <select class="form-control" id="fk_idsubarea" name="fk_idsubarea"style="width:100%">
                    </select>
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
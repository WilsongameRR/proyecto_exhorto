<?php
$access_control = $_SESSION["access_control"];
include "conexion.php";

// Obtener info del usuario logueado
$id_usuario = $_SESSION["userid"];
$sqlUser = "SELECT u.nomcompleto, a.nombre AS area, s.nombreS AS subarea,
                   t.id AS id_tua, t.tua, t.ciudad_sede
            FROM users u
            LEFT JOIN areas a ON u.fk_idarea = a.id_areas
            LEFT JOIN subareas s ON u.fk_idsubarea = s.id_subarea
            LEFT JOIN cat_tuas t ON u.fk_id_tua = t.id
            WHERE u.user_id = $id_usuario";

$resUser = $con->query($sqlUser);
$infoUser = $resUser->fetch_assoc();
?>
<nav class="navbar navbar-inverse" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse navbar-ex1-collapse">
            <!-- Usuario con imagen a la izquierda -->
            <ul class="nav navbar-nav navbar-left">
                <li class="navbar-text" style="display:flex; align-items:center; gap:10px;">
                    <img src="images/tas.png" alt="Usuario" 
                         style="height:60px; width:60px; border-radius:50%; border:1px solid #ccc;">
                    <div style="line-height:1.3;">
                        <!-- Nombre -->
                        <b><?php echo $infoUser["nomcompleto"]; ?></b><br>
                        
                        <!-- TUA -->
                        <b>
                            TUA: 
                            <?php 
                                echo $infoUser["tua"] 
                                     ? "<strong>".$infoUser["tua"]."</strong> ".$infoUser["ciudad_sede"] 
                                     : "Sin TUA"; 
                            ?>
                        </b><br>
                        
                        <!-- Área y Subárea en gris -->
                        <small style="color:#bbb;">
                            Área: <?php echo $infoUser["area"] ?: "Sin área"; ?> | 
                            Subárea: <?php echo $infoUser["subarea"] ?: "Sin subárea"; ?>
                        </small>
                    </div>
                </li>
            </ul>

            <!-- Menús a la derecha -->
            <ul class="nav navbar-nav navbar-right">
                <?php if ($access_control == 1): ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">Administración <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="userview.php">Usuarios</a></li>
                        <li><a href="viewRoles.php">Roles</a></li>
                        <li><a href="viewAreas.php">Áreas</a></li>
                        <li><a href="viewSubArea.php">Subáreas</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">Expedientes <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="nuevoExpediente.php">Nuevo Expediente</a></li>
                        <li><a href="consultarExpediente.php">Consultar Expediente</a></li>
                        <li><a href="expedientesRecibidos.php">Expedientes Recibidos</a></li>
                        <li><a href="expedientesEnviados.php">Expedientes Enviados</a></li>
                    </ul>
                </li>

                <li><a class="navbar-text" href="php/cierraSesion.php"><b>Cerrar Sesión</b></a></li>
            </ul>
        </div>
    </div>
</nav>

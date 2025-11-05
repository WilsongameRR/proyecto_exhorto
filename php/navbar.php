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
                        <b><?php echo htmlspecialchars($infoUser["nomcompleto"]); ?></b><br>
                        
                        <b>
                            TUA: 
                            <?php 
                                echo $infoUser["tua"] 
                                     ? "<strong>".htmlspecialchars($infoUser["tua"])."</strong> ".htmlspecialchars($infoUser["ciudad_sede"]) 
                                     : "Sin TUA"; 
                            ?>
                        </b><br>
                        
                        <small style="color:#bbb;">
                            Área: <?php echo $infoUser["area"] ?: "Sin área"; ?> | 
                            Subárea: <?php echo $infoUser["subarea"] ?: "Sin subárea"; ?>
                        </small>
                    </div>
                </li>
            </ul>

            <!-- Menús a la derecha -->
            <ul class="nav navbar-nav navbar-right">

                <!-- 🔹 SOLO ADMIN VE ESTE MENÚ -->
                <?php if ($access_control === 'admin' || $access_control == 1): ?>
                <li>
                    <a href="viewUsuarios.php">
                        <i class="fa fa-user-gear"></i> Usuarios
                    </a>
                </li>
                <?php endif; ?>

                <!-- 🔹 BOTÓN CERRAR SESIÓN -->
                <li>
                    <a class="navbar-text" href="php/cierraSesion.php">
                        <b>Cerrar Sesión</b>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

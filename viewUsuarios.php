<?php
session_start();
include "php/conexion.php";

if (!isset($_SESSION["access_control"]) || ($_SESSION["access_control"] != 1 && $_SESSION["access_control"] !== 'admin')) {
    header("Location: bienvenida.php?msg=no_autorizado");
    exit();
}

// ✅ Insertar nuevo usuario
if (isset($_POST["guardar_usuario"])) {
    $nomcompleto = trim($_POST["nomcompleto"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $access_control = intval($_POST["access_control"]);
    $fk_id_tua = !empty($_POST["fk_id_tua"]) ? intval($_POST["fk_id_tua"]) : null;
    $fk_idarea = !empty($_POST["fk_idarea"]) ? intval($_POST["fk_idarea"]) : null;
    $fk_idsubarea = !empty($_POST["fk_idsubarea"]) ? intval($_POST["fk_idsubarea"]) : null;

    if ($nomcompleto && $username && $password) {
        $stmt = $con->prepare("INSERT INTO users (nomcompleto, username, password, access_control, fk_id_tua, fk_idarea, fk_idsubarea)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiii", $nomcompleto, $username, $password, $access_control, $fk_id_tua, $fk_idarea, $fk_idsubarea);
        $stmt->execute();
        $stmt->close();
        header("Location: viewUsuarios.php?msg=usuario_creado");
        exit();
    }
}

// ✅ Eliminar usuario
if (isset($_GET["eliminar"]) && is_numeric($_GET["eliminar"])) {
    $id = intval($_GET["eliminar"]);
    $con->query("DELETE FROM users WHERE user_id = $id");
    header("Location: viewUsuarios.php?msg=usuario_eliminado");
    exit();
}

// ✅ Consultar usuarios existentes
$usuarios = $con->query("SELECT * FROM users ORDER BY user_id ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Gestión de Usuarios</title>
<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="js/jquery.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<style>
body {
    background-color: #f5f7fa;
    font-family: "Segoe UI", Roboto, sans-serif;
}
.container {
    margin-top: 40px;
    max-width: 1000px;
}
.table th, .table td {
    text-align: center;
    vertical-align: middle;
}
.btn-institucional {
    background-color: #004B8D;
    border: none;
    color: #fff;
    border-radius: 6px;
    padding: 8px 18px;
}
.btn-institucional:hover { background-color: #003C73; }
h2 { color: #2c3e50; border-bottom: 2px solid #dfe6e9; padding-bottom: 8px; margin-bottom: 25px; }
</style>
</head>
<body>

<?php include "php/navbar.php"; ?>

<div class="container">
    <h2>Gestión de Usuarios</h2>

    <!-- Formulario de Alta -->
    <form method="POST" class="mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label><b>Nombre completo</b></label>
                <input type="text" name="nomcompleto" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label><b>Usuario</b></label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label><b>Contraseña</b></label>
                <input type="text" name="password" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label><b>Nivel de acceso</b></label>
                <select name="access_control" class="form-select" required>
                    <option value="0">Usuario normal</option>
                    <option value="1">Administrador</option>
                </select>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-3">
                <label><b>ID TUA</b></label>
                <input type="number" name="fk_id_tua" class="form-control">
            </div>
            <div class="col-md-3">
                <label><b>ID Área</b></label>
                <input type="number" name="fk_idarea" class="form-control">
            </div>
            <div class="col-md-3">
                <label><b>ID Subárea</b></label>
                <input type="number" name="fk_idsubarea" class="form-control">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" name="guardar_usuario" class="btn btn-institucional w-100">
                    <i class="fa fa-user-plus"></i> Agregar Usuario
                </button>
            </div>
        </div>
    </form>

    <!-- Tabla de Usuarios -->
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>ID</th><th>Nombre</th><th>Usuario</th><th>Acceso</th>
                <th>ID TUA</th><th>ID Área</th><th>ID Subárea</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($u = $usuarios->fetch_assoc()): ?>
            <tr>
                <td><?= $u["user_id"] ?></td>
                <td><?= htmlspecialchars($u["nomcompleto"]) ?></td>
                <td><?= htmlspecialchars($u["username"]) ?></td>
                <td><?= $u["access_control"] == 1 ? "Administrador" : "Usuario" ?></td>
                <td><?= $u["fk_id_tua"] ?: "-" ?></td>
                <td><?= $u["fk_idarea"] ?: "-" ?></td>
                <td><?= $u["fk_idsubarea"] ?: "-" ?></td>
                <td>
                    <?php if ($u["user_id"] != 1): ?>
                    <a href="viewUsuarios.php?eliminar=<?= $u['user_id'] ?>" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">
                        <i class="fa fa-trash"></i> Eliminar
                    </a>
                    <?php else: ?>
                    <span class="text-muted">Protegido</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>

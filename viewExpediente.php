<?php
session_start();
if (empty($_SESSION["userid"])) {
    header("Location: index.php?error=Sin_sesion_iniciada");
    exit();
}

include "php/conexion.php";
include "php/funciones_expediente.php";
include "php/funciones_diligencia.php";
include "php/notificaciones.php";

$id_expediente = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
$id_usuario    = intval($_SESSION["userid"]);
$id_tua        = intval($_SESSION["fk_id_tua"]);

if ($id_expediente <= 0) die("ID de expediente inválido.");

$expediente = obtenerExpedientePorID($con, $id_expediente);
if (!$expediente) die("No se encontró el expediente.");

$id_tua_origen  = (int)$expediente["id_tua_origen"];
$id_tua_destino = (int)$expediente["id_tua_destino"];
$estatus_actual = $expediente["estatus"];

$es_destinatario = ($id_tua == $id_tua_destino);
$es_remitente    = ($id_tua == $id_tua_origen);

/* ==========================================================
   🔹 GUARDAR DATOS (folio, estatus y fecha) SIN AJAX
========================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["guardar_cambios"])) {
    $nuevo_estatus = trim($_POST["estatus"] ?? "");
    $nuevo_folio   = trim($_POST["folio_destino"] ?? "");
    $nueva_fecha   = trim($_POST["fecha_recepcion"] ?? "");

    if ($es_destinatario) {
        if ($nuevo_folio !== "") {
            $stmt = $con->prepare("UPDATE expedientes SET folio_destino=? WHERE id_expediente=?");
            $stmt->bind_param("si", $nuevo_folio, $id_expediente);
            $stmt->execute();
            $stmt->close();
        }

        if ($nuevo_estatus !== "") {
            $stmt = $con->prepare("UPDATE expedientes SET estatus=? WHERE id_expediente=?");
            $stmt->bind_param("si", $nuevo_estatus, $id_expediente);
            $stmt->execute();
            $stmt->close();
        }

        if ($nueva_fecha !== "") {
            $stmt = $con->prepare("UPDATE expedientes SET fecha_recepcion=? WHERE id_expediente=?");
            $stmt->bind_param("si", $nueva_fecha, $id_expediente);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: bienvenida.php?msg=guardado");
        exit();
    } else {
        header("Location: bienvenida.php?msg=no_autorizado");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Detalle del Exhorto</title>
<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="js/jquery.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>

<style>
body {
    background-color: #f5f7fa;
    font-family: "Segoe UI", Roboto, sans-serif;
    color: #2c3e50;
}
h2 {
    font-weight: 600;
    color: #34495e;
    border-bottom: 2px solid #dfe6e9;
    padding-bottom: 10px;
    margin-bottom: 25px;
}
h3 {
    color: #2d3436;
    margin-top: 40px;
    font-size: 20px;
    font-weight: 600;
}

/* ✅ Tabla */
.table th, .table td {
    text-align: center !important;
    vertical-align: middle !important;
}

/* ✅ SOLO campos editables (más bajos, bonitos y modernos) */
.select-bonito, 
.input-bonito {
    width: 200px;
    padding: 5px 8px;
    border: 1px solid #c9d1d9;
    border-radius: 6px;
    background-color: #f9fbfd;
    text-align: center;
    color: #2c3e50;
    transition: all 0.2s ease-in-out;
    font-size: 14px;
    height: 34px; /* más bajos */
}

.select-bonito:hover,
.input-bonito:hover {
    background-color: #f1f7ff;
}

.select-bonito:focus,
.input-bonito:focus {
    outline: none;
    border-color: #004B8D;
    box-shadow: 0 0 3px rgba(0,75,141,0.3);
    background-color: #fff;
}

/* ✅ Flecha moderna */
.select-bonito {
    appearance: none;
    background-image: url("data:image/svg+xml;utf8,<svg fill='gray' height='14' viewBox='0 0 24 24' width='14' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 14px;
    padding-right: 25px;
}

/* ✅ Botones */
.text-center-btn {
    text-align: center;
    margin-top: 35px;
    display: flex;
    justify-content: center;
    gap: 15px;
}

.btn-success {
    background: #28a745;
    border: none;
    border-radius: 6px;
    padding: 10px 26px;
    font-size: 16px;
    color: #fff;
}
.btn-success:hover {
    background: #218838;
}
.btn-institucional {
    background-color: #004B8D;
    border: none;
    border-radius: 6px;
    padding: 10px 26px;
    font-size: 16px;
    color: #fff;
    font-weight: 500;
}
.btn-institucional:hover {
    background-color: #003C73;
    color: #fff !important;
}
</style>
</head>
<body>

<?php include "php/navbar.php"; ?>

<div class="container" style="margin-top:40px; max-width:950px;">
    <h2>Detalle del Exhorto</h2>

    <form method="POST">
        <input type="hidden" name="id_expediente" value="<?= htmlspecialchars($id_expediente) ?>">

        <table class="table table-bordered">
            <tr><th>Número de Expediente</th><td><?= htmlspecialchars($expediente["num_expediente"]) ?></td></tr>
            <tr><th>Estado</th><td><?= htmlspecialchars($expediente["nombre_estado"]) ?></td></tr>
            <tr><th>Municipio</th><td><?= htmlspecialchars($expediente["nombre_municipio"]) ?></td></tr>
            <tr><th>Núcleo Agrario</th><td><?= htmlspecialchars($expediente["nombre_nucleo"]) ?></td></tr>
            <tr><th>Exhorto</th><td><?= htmlspecialchars($expediente["exhorto"]) ?></td></tr>
            <tr><th>TUA Origen</th><td><?= htmlspecialchars($expediente["tua_origen"] . " — " . $expediente["ciudad_origen"]) ?></td></tr>
            <tr><th>TUA Destino</th><td><?= htmlspecialchars($expediente["tua_destino"] . " — " . $expediente["ciudad_destino"]) ?></td></tr>
            <tr><th>Fecha Registro</th><td><?= $expediente["f_registro"] ? date("d/m/Y h:i A", strtotime($expediente["f_registro"])) : "Sin registro" ?></td></tr>

            <tr>
                <th>Estatus Actual</th>
                <td>
                    <?php if ($es_destinatario): ?>
                        <select name="estatus" class="select-bonito">
                            <?php
                            $estatuses = ["En Proceso", "Atendida", "Vencida", "Incompetencia"];
                            foreach ($estatuses as $op) {
                                $sel = ($estatus_actual == $op) ? "selected" : "";
                                echo "<option value='$op' $sel>$op</option>";
                            }
                            ?>
                        </select>
                    <?php else: ?>
                        <?= htmlspecialchars($estatus_actual) ?>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <th>Folio de Recepción</th>
                <td>
                    <?php if ($es_destinatario): ?>
                        <input type="text" name="folio_destino" class="input-bonito" value="<?= htmlspecialchars($expediente["folio_destino"] ?? '') ?>">
                    <?php else: ?>
                        <?= !empty($expediente["folio_destino"]) ? htmlspecialchars($expediente["folio_destino"]) : "<span class='text-muted'>Sin folio</span>" ?>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <th>Fecha de Recepción</th>
                <td>
                    <?php if ($es_destinatario): ?>
                        <input type="date" name="fecha_recepcion" class="input-bonito" value="<?= htmlspecialchars($expediente["fecha_recepcion"] ?? '') ?>">
                    <?php else: ?>
                        <?= !empty($expediente["fecha_recepcion"]) ? htmlspecialchars($expediente["fecha_recepcion"]) : "<span class='text-muted'>Sin fecha</span>" ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <h3>Diligencias del Exhorto</h3>
        <?php mostrarTablaDiligencias($con, $id_expediente, $id_tua); ?>

        <div class="text-center-btn">
            <button type="submit" name="guardar_cambios" class="btn btn-success">
                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar
            </button>
            <button type="button" id="btnRegresar" class="btn btn-institucional">
                <i class="fa-solid fa-arrow-left me-1"></i> Regresar
            </button>
        </div>
    </form>
</div>

<script>
$("#btnRegresar").on("click", function(){
    window.location.href = "bienvenida.php";
});
</script>

</body>
</html>

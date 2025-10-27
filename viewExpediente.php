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
   🔹 GUARDAR DATOS (folio y estatus) SIN AJAX
========================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["guardar_cambios"])) {
    $nuevo_estatus = trim($_POST["estatus"] ?? "");
    $nuevo_folio   = trim($_POST["folio_destino"] ?? "");

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

/* ✅ Tabla centrada */
.table th, .table td {
    text-align: center !important;
    vertical-align: middle !important;
}

/* ✅ Campo folio */
.folio-input {
    width: 220px;
    border-radius: 6px;
    border: 1px solid #ccc;
    padding: 6px 10px;
    text-align: center;
}

/* ✅ Contenedor botones */
.text-center-btn {
    text-align: center;
    margin-top: 35px;
    display: flex;
    justify-content: center;
    gap: 15px;
}

/* ✅ Botón Guardar */
.btn-success {
    background: #28a745;
    border: none;
    border-radius: 6px;
    padding: 10px 26px;
    font-size: 16px;
    color: #fff;
}
.btn-success:hover,
.btn-success:focus,
.btn-success:active {
    background: #218838;
    color: #fff !important;
}

/* ✅ Botón Regresar */
.btn-institucional {
    background-color: #004B8D;
    border: none;
    border-radius: 6px;
    padding: 10px 26px;
    font-size: 16px;
    color: #fff;
    font-weight: 500;
}
.btn-institucional:hover,
.btn-institucional:focus,
.btn-institucional:active {
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
        <?php mostrarDatosExpediente($expediente, $estatus_actual, $es_destinatario); ?>

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

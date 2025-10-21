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

/* ============================
   Guardar FOLIO vía AJAX
============================ */
if (isset($_POST["ajax_folio"]) && $es_destinatario) {
    $folio = trim($_POST["folio_destino"]);
    $stmt = $con->prepare("UPDATE expedientes SET folio_destino = ? WHERE id_expediente = ?");
    $stmt->bind_param("si", $folio, $id_expediente);
    $stmt->execute();
    $stmt->close();
    echo json_encode(["status" => "ok", "folio" => $folio]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Detalle del Exhorto</title>
<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
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

/* ✅ Folio editable (tamaño fijo y centrado) */
.folio-editable {
    border: 1px solid #ccc;
    padding: 6px 10px;
    border-radius: 6px;
    width: 200px;          /* 🔹 ancho fijo */
    min-height: 30px;
    text-align: center;
    background-color: #fff;
    display: inline-block;
}
.folio-editable[contenteditable="true"]:focus {
    outline: none;
    border-color: #28a745;
    box-shadow: 0 0 4px rgba(40,167,69,0.4);
}

/* ✅ Folio solo lectura */
.folio-view-only {
    border: 1px solid #dcdcdc;
    padding: 6px 10px;
    border-radius: 6px;
    width: 200px;
    min-height: 30px;
    text-align: center;
    background-color: #f9f9f9;
    display: inline-block;
    color: #333;
}

/* ✅ Toast verde */
#toastMsg {
    position: fixed;
    top: 25px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    background-color: #28a745;
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    opacity: 0;
    transition: opacity 0.4s ease;
    font-weight: 500;
}

/* ✅ Botón centrado */
.text-center-btn {
    text-align: center;
    margin-top: 35px;
}
.btn-success {
    background: #28a745;
    border: none;
    border-radius: 6px;
    padding: 10px 26px;
    font-size: 16px;
}
.btn-success:hover {
    background: #218838;
}
</style>
</head>
<body>

<?php include "php/navbar.php"; ?>
<div id="toastMsg"></div>

<div class="container" style="margin-top:40px; max-width:950px;">
    <h2>Detalle del Exhorto</h2>

    <!-- ✅ Mostrar expediente con estilo unificado -->
    <?php mostrarDatosExpediente($expediente, $estatus_actual, $es_destinatario); ?>

    <h3>Diligencias del Exhorto</h3>
    <?php mostrarTablaDiligencias($con, $id_expediente, $id_tua); ?>

    <div class="text-center-btn">
        <button id="btnGuardar" class="btn btn-success">Guardar</button>
    </div>
</div>

<script>
$(function(){
    // ✅ Mostrar toast animado
    function showToast(msg) {
        const toast = $("#toastMsg");
        toast.text(msg).css("opacity", "1");
        setTimeout(() => toast.css("opacity", "0"), 2500);
    }

    const esDestinatario = <?= $es_destinatario ? 'true' : 'false' ?>;

    // ✅ Guardar botón
    $("#btnGuardar").on("click", function(){
        if(!esDestinatario){
            window.location.href = "bienvenida.php";
            return;
        }
        const nuevoFolio = $("#folio_destino_label").text().trim();
        const nuevoEstatus = $("#selectEstatusExpediente").val();

        if(nuevoFolio !== ""){
            $.post("viewExpediente.php?id=<?= $id_expediente ?>", {
                ajax_folio: 1,
                folio_destino: nuevoFolio
            });
        }

        if(nuevoEstatus){
            const fd = new FormData();
            fd.append("ajax_guardar_estatus_expediente","1");
            fd.append("id_expediente","<?= (int)$id_expediente ?>");
            fd.append("estatus", nuevoEstatus);
            fetch("php/funciones_expediente.php",{method:"POST",body:fd});
        }

        showToast("💾 Cambios guardados correctamente");
        setTimeout(()=> window.location.href = "bienvenida.php", 1800);
    });
});
</script>

</body>
</html>

<?php
session_start();
if (empty($_SESSION["userid"])) {
    header("Location: /proyecto_exhorto/almacen2/index.php?error=Sin_sesion_iniciada");
    exit();
}

include "php/navbar.php";
include "php/conexion.php";
include "php/funciones_expediente.php";

// ================================
// Generar número automático ####/AAAA-TUA
// ================================
$nuevo_num = generarNumeroExpediente($con, $_SESSION["fk_id_tua"]);
$tua_usuario = str_pad($_SESSION["fk_id_tua"], 3, "0", STR_PAD_LEFT);
$anio_actual = date("Y");
$exhorto_base = substr($nuevo_num, 0, strpos($nuevo_num, '-'));

// ================================
// Generar número incremental para Exhorto (####/AAAA)
// ================================
$num_exhorto = generarNumeroExhorto($con);

// ================================
// Catálogos
// ================================
$id_tua_origen = intval($_SESSION["fk_id_tua"]);
$estados = $con->query("SELECT id, estado FROM cat_estados ORDER BY estado");
$tuas = $con->query("SELECT id, tua, ciudad_sede 
                     FROM cat_tuas 
                     WHERE id != $id_tua_origen 
                     ORDER BY tua");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nuevo Exhorto</title>
    <link rel="stylesheet" href="/proyecto_exhorto/almacen2/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="/proyecto_exhorto/almacen2/js/jquery.min.js"></script>

    <style>
        body {
            background-color: #f7f8fa;
        }
        h2 {
            color: #004085;
            font-weight: bold;
            margin-bottom: 25px;
            text-align: center;
        }
        label {
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .diligencia-card {
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            padding: 15px 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .diligencia-card:hover {
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        .btn-group-centered {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="container" style="margin-top:40px;">
    <h2><i class="fa fa-file-circle-plus"></i> Registro de Nuevo Exhorto</h2>

    <form id="formExhorto" method="POST" enctype="multipart/form-data" 
          action="/proyecto_exhorto/almacen2/php/guardar_exhorto.php">
        
        <!-- Número de Expediente -->
        <div class="form-group row justify-content-center">
            <label class="col-sm-4 col-form-label text-right">Número de Expediente:</label>
            <div class="col-sm-6">
                <input type="text" 
                       name="num_expediente" 
                       id="num_expediente" 
                       class="form-control"
                       placeholder="####/AAAA-###"
                       maxlength="15"
                       pattern="^[0-9]{4}/[0-9]{4}-[0-9]{1,3}$"
                       title="Formato requerido: ####/AAAA-### (Ejemplo: 9586/2025-9)"
                       required>
            </div>
        </div>

        <!-- Estado -->
        <div class="form-group row justify-content-center">
            <label class="col-sm-4 col-form-label text-right">Estado:</label>
            <div class="col-sm-6">
                <select name="id_estado_exh" id="estado_exh" class="form-control" required>
                    <option value="">Seleccione un Estado</option>
                    <?php while ($row = $estados->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['estado']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <!-- Municipio -->
        <div class="form-group row justify-content-center">
            <label class="col-sm-4 col-form-label text-right">Municipio:</label>
            <div class="col-sm-6">
                <select name="id_municipio_exh" id="municipio_exh" class="form-control" required>
                    <option value="">Seleccione un estado primero</option>
                </select>
            </div>
        </div>

        <!-- Núcleo Agrario -->
        <div class="form-group row justify-content-center">
            <label class="col-sm-4 col-form-label text-right">Núcleo Agrario:</label>
            <div class="col-sm-6">
                <select name="id_nucleo_exh" id="nucleo_exh" class="form-control" required>
                    <option value="">Seleccione un municipio primero</option>
                </select>
            </div>
        </div>

        <!-- Exhorto -->
        <div class="form-group row justify-content-center">
            <label class="col-sm-4 col-form-label text-right">Exhorto:</label>
            <div class="col-sm-6">
                <input type="text" 
                       id="exhorto" 
                       name="exhorto" 
                       class="form-control"
                       value="<?= htmlspecialchars($num_exhorto); ?>" 
                       readonly
                       style="background:#f0f0f0;font-weight:bold;"
                       title="Generado automáticamente por el sistema">
            </div>
        </div>

        <!-- TUA destinatario -->
        <div class="form-group row justify-content-center">
            <label class="col-sm-4 col-form-label text-right">TUA Destinatario:</label>
            <div class="col-sm-6">
                <select name="id_tua_destino_exh" class="form-control" required>
                    <option value="">Seleccione...</option>
                    <?php while ($t = $tuas->fetch_assoc()): ?>
                        <option value="<?= $t['id'] ?>">
                            <?= htmlspecialchars($t['tua']) . " — " . htmlspecialchars($t['ciudad_sede']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <!-- ===================== DILIGENCIAS ===================== -->
        <h4 class="mt-4 text-primary">
            <i class="fa fa-folder-open"></i> Diligencias
        </h4>

        <div id="contenedor_diligencias" class="row"></div>

        <div class="text-center mt-3">
            <button type="button" class="btn btn-outline-primary" id="agregar_diligencia">
                <i class="fa fa-plus"></i> Agregar diligencia
            </button>
        </div>

        <!-- BOTONES -->
        <div class="btn-group-centered">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fa fa-save"></i> Guardar
            </button>
            <a href="/proyecto_exhorto/almacen2/bienvenida.php" class="btn btn-default btn-lg">
                <i class="fa fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<!-- SCRIPT DE DILIGENCIAS -->
<script>
let contador = 0;
$("#agregar_diligencia").click(function(){
    contador++;
    const card = `
    <div class="col-md-6 diligencia-card" id="diligencia_${contador}">
        <h5><i class="fa fa-file"></i> Diligencia ${contador}</h5>

        <div class="form-group">
            <label><b>Tipo de Diligencia</b></label>
            <select name="diligencias[${contador}][diligencia]" class="form-control select-diligencia" data-id="${contador}" required>
                <option value="">Seleccione...</option>
                <option value="Emplazamiento">Emplazamiento</option>
                <option value="Notificación">Notificación</option>
                <option value="Otro">Otro (especificar)</option>
            </select>
        </div>

        <div class="form-group otro-diligencia" id="otro_diligencia_${contador}" style="display:none;">
            <label><b>Especifique el tipo de diligencia</b></label>
            <input type="text" name="diligencias[${contador}][otro_diligencia]" class="form-control otro-input" placeholder="Describa la diligencia...">
        </div>

        <div class="form-group">
            <label><b>Nombre</b> <small class="text-muted">(persona o institución)</small></label>
            <input type="text" name="diligencias[${contador}][nombre_destinatario]" class="form-control" placeholder="Ejemplo: Juan Pérez López o Tribunal Unitario Agrario">
        </div>

        <input type="hidden" name="diligencias[${contador}][estatus_diligencia]" value="Pendiente">

        <div class="text-right">
            <button type="button" class="eliminar_fila btn btn-sm btn-outline-danger" data-id="${contador}">
                <i class="fa fa-trash"></i> Eliminar
            </button>
        </div>
    </div>`;
    $("#contenedor_diligencias").append(card);
});

$(document).on("change", ".select-diligencia", function(){
    const id = $(this).data("id");
    const valor = $(this).val();
    const campoOtro = $("#otro_diligencia_" + id);
    campoOtro.hide();
    if (valor === "Otro") {
        campoOtro.slideDown();
    }
});

$(document).on("click", ".eliminar_fila", function(){
    const id = $(this).data("id");
    $("#diligencia_" + id).fadeOut(300, function(){ $(this).remove(); });
});
</script>

<!-- ===================== AUTO COMPLETAR AÑO ===================== -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("num_expediente");
    const anio = new Date().getFullYear();
    
    input.addEventListener("input", () => {
        const valor = input.value;

        // Si el usuario solo escribió los primeros 4 dígitos, añadir automáticamente /AAAA-
        if (/^[0-9]{4}$/.test(valor)) {
            input.value = valor + "/" + anio + "-";
        }
    });
});
</script>

<!-- ===================== CARGA DE MUNICIPIOS Y NÚCLEOS ===================== -->
<script>
$("#estado_exh").change(function(){
    var id_estado = $(this).val();
    if(id_estado){
        $.post("/proyecto_exhorto/almacen2/php/getMunicipios.php", {id_estado}, function(data){
            $("#municipio_exh").html('<option value="">Seleccione...</option>');
            $.each(data, function(i, municipio){
                $("#municipio_exh").append('<option value="'+municipio.id+'">'+municipio.municipio+'</option>');
            });
            $("#nucleo_exh").html('<option value="">Seleccione un municipio primero</option>');
        }, "json");
    }
});

$("#municipio_exh").change(function(){
    var id_municipio = $(this).val();
    if(id_municipio){
        $.post("/proyecto_exhorto/almacen2/php/getNucleos.php", {id_municipio}, function(data){
            $("#nucleo_exh").html('<option value="">Seleccione...</option>');
            $.each(data, function(i, nucleo){
                $("#nucleo_exh").append('<option value="'+nucleo.id+'">'+nucleo.nucleo+'</option>');
            });
        }, "json");
    }
});
</script>

</body>
</html>

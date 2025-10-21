<?php
session_start();
if (empty($_SESSION["userid"])) {
    header("Location: index.php?error=Sin_sesion_iniciada");
    exit();
}

require_once "php/conexion.php";
require_once "php/header_html.php";
require_once "php/notificaciones_bienvenida.php";

$id_tua = intval($_SESSION["fk_id_tua"] ?? 0);

/* ================================
   DOCUMENTOS ENVIADOS
================================ */
$sql_enviados = "
SELECT 
    e.id_expediente,
    e.num_expediente,
    e.exhorto,
    est.estado AS nombre_estado,
    mun.municipio AS nombre_municipio,
    nac.nucleo AS nombre_nucleo,
    CONCAT(t_destino.tua, ' — ', t_destino.ciudad_sede) AS tua_destino,
    e.estatus
FROM expedientes e
LEFT JOIN cat_tuas t_destino ON e.id_tua_destino = t_destino.id
LEFT JOIN cat_estados est ON e.id_estado = est.id
LEFT JOIN cat_municipios mun ON e.id_municipio = mun.id
LEFT JOIN cat_nucleos_agrarios nac ON e.id_nucleo = nac.id
WHERE e.id_tua_origen = $id_tua
ORDER BY e.id_expediente DESC";
$res_enviados = $con->query($sql_enviados);

/* ================================
   DOCUMENTOS RECIBIDOS
================================ */
$sql_recibidos = "
SELECT 
    e.id_expediente,
    e.num_expediente,
    e.exhorto,
    est.estado AS nombre_estado,
    mun.municipio AS nombre_municipio,
    nac.nucleo AS nombre_nucleo,
    CONCAT(t_origen.tua, ' — ', t_origen.ciudad_sede) AS tua_origen,
    e.estatus
FROM expedientes e
LEFT JOIN cat_tuas t_origen ON e.id_tua_origen = t_origen.id
LEFT JOIN cat_estados est ON e.id_estado = est.id
LEFT JOIN cat_municipios mun ON e.id_municipio = mun.id
LEFT JOIN cat_nucleos_agrarios nac ON e.id_nucleo = nac.id
WHERE e.id_tua_destino = $id_tua
ORDER BY e.id_expediente DESC";
$res_recibidos = $con->query($sql_recibidos);
?>

<?php mostrarHeader("Bienvenida"); ?>
<body>
<?php include "php/navbar.php"; ?>
<?php mostrarNotificacion($_GET["msg"] ?? ""); ?>

<style>
.table th {
    text-align: center !important;
    vertical-align: middle !important;
    font-weight: 700;
    background-color: #f8f9fa;
    letter-spacing: 0.4px;
}
.table td {
    text-align: center;
    vertical-align: middle !important;
}

/* ✅ Mensaje de “sin resultados” */
#mensajeSinResultados {
    display: none;
    font-weight: 600;
    color: #d9534f;
    background: #fbeaea;
    padding: 12px;
    border-radius: 6px;
    margin-top: 20px;
    width: 60%;
    margin-left: auto;
    margin-right: auto;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    animation: none;
}

/* ✅ Animación shake */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-8px); }
    40%, 80% { transform: translateX(8px); }
}
</style>

<div class="container text-center">
    <div class="logo-container" style="margin-top:80px; margin-bottom:25px;">
        <img src="images/ta.png" alt="Logo del Sistema" 
             style="max-width:500px;width:100%;height:auto;transition:transform .3s ease;filter:drop-shadow(0 4px 10px rgba(0,0,0,0.18));"
             onmouseover="this.style.transform='scale(1.07)'"
             onmouseout="this.style.transform='scale(1)'">
    </div>

    <h2>Bienvenido al Sistema de Exhorto</h2>

    <div style="margin:35px 0;">
        <a href="nuevoExpediente.php" class="btn btn-nuevo">
            <i class="fa fa-plus-circle"></i> Nuevo Documento
        </a>
    </div>

    <!-- 🔍 Barra de búsqueda -->
    <div class="form-group" style="max-width:400px; margin:0 auto 25px;">
        <input type="text" id="busqueda" class="form-control" placeholder="Buscar">
    </div>

    <!-- ⚠️ Mensaje cuando no hay resultados -->
    <div id="mensajeSinResultados">⚠️ No se encontraron resultados en Enviados ni en Recepcionados.</div>

    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#enviados">📤 Enviados</a></li>
        <li><a data-toggle="tab" href="#recibidos">📥 Recepcionados</a></li>
    </ul>

    <div class="tab-content" style="margin-top:25px;">
        <!-- ENVIADOS -->
        <div id="enviados" class="tab-pane fade in active">
            <h3>Enviados</h3>
            <table id="tablaEnviados" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>No. Expediente</th>
                        <th>Estado</th>
                        <th>Municipio</th>
                        <th>Núcleo Agrario</th>
                        <th>Exhorto</th>
                        <th>TUA Destino</th>
                        <th>Estatus</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res_enviados && $res_enviados->num_rows > 0): ?>
                        <?php while($row = $res_enviados->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row["num_expediente"]); ?></td>
                                <td><?= htmlspecialchars($row["nombre_estado"]); ?></td>
                                <td><?= htmlspecialchars($row["nombre_municipio"]); ?></td>
                                <td><?= htmlspecialchars($row["nombre_nucleo"]); ?></td>
                                <td><?= htmlspecialchars($row["exhorto"]); ?></td>
                                <td><?= htmlspecialchars($row["tua_destino"]); ?></td>
                                <td><?= htmlspecialchars($row["estatus"]); ?></td>
                                <td>
                                    <a href="viewExpediente.php?id=<?= intval($row["id_expediente"]); ?>" 
                                       class="btn btn-ver btn-sm" data-toggle="tooltip" title="Ver detalle">
                                       <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8">No hay documentos enviados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- RECIBIDOS -->
        <div id="recibidos" class="tab-pane fade">
            <h3>Recepcionados</h3>
            <table id="tablaRecibidos" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>No. Expediente</th>
                        <th>Estado</th>
                        <th>Municipio</th>
                        <th>Núcleo Agrario</th>
                        <th>Exhorto</th>
                        <th>TUA Origen</th>
                        <th>Estatus</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res_recibidos && $res_recibidos->num_rows > 0): ?>
                        <?php while($row = $res_recibidos->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row["num_expediente"]); ?></td>
                                <td><?= htmlspecialchars($row["nombre_estado"]); ?></td>
                                <td><?= htmlspecialchars($row["nombre_municipio"]); ?></td>
                                <td><?= htmlspecialchars($row["nombre_nucleo"]); ?></td>
                                <td><?= htmlspecialchars($row["exhorto"]); ?></td>
                                <td><?= htmlspecialchars($row["tua_origen"]); ?></td>
                                <td><?= htmlspecialchars($row["estatus"]); ?></td>
                                <td>
                                    <a href="viewExpediente.php?id=<?= intval($row["id_expediente"]); ?>" 
                                       class="btn btn-ver btn-sm" data-toggle="tooltip" title="Ver detalle">
                                       <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8">No hay documentos recibidos</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ✅ SCRIPT MEJORADO -->
<script>
$(function(){
    $('[data-toggle="tooltip"]').tooltip();

    $("#busqueda").on("keyup", function() {
        var valor = $(this).val().toLowerCase().trim();
        var encontradosEnviados = 0;
        var encontradosRecibidos = 0;

        // Filtrar ENVIADOS
        $("#tablaEnviados tbody tr").each(function() {
            var visible = $(this).text().toLowerCase().indexOf(valor) > -1;
            $(this).toggle(visible);
            if (visible) encontradosEnviados++;
        });

        // Filtrar RECIBIDOS
        $("#tablaRecibidos tbody tr").each(function() {
            var visible = $(this).text().toLowerCase().indexOf(valor) > -1;
            $(this).toggle(visible);
            if (visible) encontradosRecibidos++;
        });

        // ✅ Mostrar mensaje si no hay resultados (con shake)
        if (valor.length > 0 && encontradosEnviados === 0 && encontradosRecibidos === 0) {
            $("#mensajeSinResultados")
                .stop(true, true)
                .fadeIn(200)
                .css("animation", "shake 0.4s ease");
            setTimeout(()=> $("#mensajeSinResultados").css("animation", "none"), 600);
        } else {
            $("#mensajeSinResultados").fadeOut(200);
        }

        // ✅ Cambiar automáticamente de pestaña según resultados
        if (valor.length > 0) {
            if (encontradosRecibidos > 0 && encontradosEnviados === 0) {
                $(".nav-tabs li:eq(1) a").tab("show");
                $('html, body').animate({ scrollTop: $("#recibidos").offset().top - 80 }, 500);
            } else if (encontradosEnviados > 0 && encontradosRecibidos === 0) {
                $(".nav-tabs li:eq(0) a").tab("show");
                $('html, body').animate({ scrollTop: $("#enviados").offset().top - 80 }, 500);
            }
        } else {
            $("#mensajeSinResultados").fadeOut(200);
            $("#tablaEnviados tbody tr, #tablaRecibidos tbody tr").show();
        }
    });
});
</script>

<?php include "includes/footer.php"; ?>

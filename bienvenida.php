<?php
session_start();
if (empty($_SESSION["userid"])) {
    header("Location: /proyecto_exhorto/index.php?error=Sin_sesion_iniciada");
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
    e.folio_destino,
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
    e.folio_destino,
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
}
.table td {
    text-align: center;
    vertical-align: middle !important;
}
.folio-destino { display: none; }

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
    margin: 20px auto;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
@keyframes shake {0%,100%{transform:translateX(0);}20%,60%{transform:translateX(-8px);}40%,80%{transform:translateX(8px);}}
@keyframes flashTab {0%,100%{color:#004085;}50%{color:#ff4500;}}
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
                        <th class="folio-destino">Folio Destino</th>
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
                                <td class="folio-destino"><?= htmlspecialchars($row["folio_destino"]); ?></td>
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
                        <tr><td colspan="9">No hay documentos enviados</td></tr>
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
                        <th class="folio-destino">Folio Destino</th>
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
                                <td class="folio-destino"><?= htmlspecialchars($row["folio_destino"]); ?></td>
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
                        <tr><td colspan="9">No hay documentos recibidos</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ✅ SCRIPT DE BÚSQUEDA AVANZADA -->
<script>
$(function(){
    $('[data-toggle="tooltip"]').tooltip();

    $("#busqueda").on("keyup", function() {
        const valor = $(this).val().toLowerCase().trim();
        const palabras = valor.split(/\s+/).filter(p => p.length > 0);
        let encontradosEnviados = 0;
        let encontradosRecibidos = 0;

        // --- Enviados ---
        $("#tablaEnviados tbody tr").each(function() {
            const texto = $(this).text().toLowerCase();
            const visible = palabras.every(p => texto.includes(p));
            $(this).toggle(visible);
            if (visible) encontradosEnviados++;
        });

        // --- Recibidos ---
        $("#tablaRecibidos tbody tr").each(function() {
            const texto = $(this).text().toLowerCase();
            const visible = palabras.every(p => texto.includes(p));
            $(this).toggle(visible);
            if (visible) encontradosRecibidos++;
        });

        // --- Mostrar mensaje sin resultados ---
        if (valor && encontradosEnviados === 0 && encontradosRecibidos === 0) {
            $("#mensajeSinResultados").fadeIn(200).css("animation", "shake 0.4s ease");
            setTimeout(()=> $("#mensajeSinResultados").css("animation", "none"), 600);
        } else {
            $("#mensajeSinResultados").fadeOut(200);
        }

        // --- Cambiar pestaña automáticamente ---
        if (valor) {
            if (encontradosEnviados > 0 && encontradosRecibidos === 0) {
                $(".nav-tabs li:eq(0) a").tab("show");
                $('html, body').animate({ scrollTop: $("#enviados").offset().top - 80 }, 400);
            } 
            else if (encontradosRecibidos > 0 && encontradosEnviados === 0) {
                $(".nav-tabs li:eq(1) a").tab("show");
                $('html, body').animate({ scrollTop: $("#recibidos").offset().top - 80 }, 400);
            } 
            else if (encontradosEnviados > 0 && encontradosRecibidos > 0) {
                $(".nav-tabs li a").css("animation", "none");
                setTimeout(() => {
                    $(".nav-tabs li:eq(0) a, .nav-tabs li:eq(1) a")
                        .css("animation", "flashTab 1s ease 2");
                }, 100);
            }
        } else {
            $("#mensajeSinResultados").fadeOut(200);
            $("#tablaEnviados tbody tr, #tablaRecibidos tbody tr").show();
        }
    });
});
</script>

<?php include "includes/footer.php"; ?>

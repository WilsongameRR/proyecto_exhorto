<?php
function mostrarNotificacion($msg, $id_expediente = 0) {
    if (empty($msg)) return;

    // === Tipo y mensaje ===
    $clase = "toast-success";
    $mensaje = "";

    if ($msg === "guardado") {
        $mensaje = "✅ Exhorto guardado correctamente.";
    } elseif ($msg === "error") {
        $mensaje = "❌ Ocurrió un error al guardar el exhorto.";
        $clase = "toast-danger";
    }

    // === Estructura visual centrada ===
    echo "
    <style>
    /* Fondo translúcido */
    #toastOverlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        background: rgba(0, 0, 0, 0.25);
        animation: fadeInBg 0.4s ease;
    }

    /* Caja de notificación */
    .toast-box {
        background: #ffffff;
        padding: 25px 40px;
        border-radius: 12px;
        color: #333;
        font-size: 18px;
        font-weight: 600;
        box-shadow: 0 6px 20px rgba(0,0,0,0.25);
        animation: fadeInUp 0.4s ease;
        text-align: center;
        min-width: 350px;
        max-width: 500px;
        border-left: 8px solid #28a745;
    }

    .toast-success { border-color: #28a745; }
    .toast-danger { border-color: #dc3545; }

    /* Animaciones */
    @keyframes fadeInBg {
        from {opacity: 0;}
        to {opacity: 1;}
    }

    @keyframes fadeInUp {
        from {opacity: 0; transform: translateY(25px);}
        to {opacity: 1; transform: translateY(0);}
    }

    /* Botón de cierre */
    .toast-btn {
        margin-top: 15px;
        padding: 8px 18px;
        background-color: #004085;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .toast-btn:hover {
        background-color: #002b57;
    }
    </style>

    <!-- Overlay -->
    <div id='toastOverlay'>
        <div class='toast-box $clase'>
            $mensaje
            <br>
            <button class='toast-btn' id='cerrarToast'>Aceptar</button>
        </div>
    </div>

    <script>
    $(document).ready(function(){
        // Botón de cierre manual
        $('#cerrarToast').on('click', function(){
            $('#toastOverlay').fadeOut(400, function(){ $(this).remove(); });
        });

        // Autoocultar después de 5 segundos
        setTimeout(() => {
            $('#toastOverlay').fadeOut(500, function(){ $(this).remove(); });
        }, 2000);

        // Quitar parámetros ?msg= de la URL
        if (window.history.replaceState) {
            const url = window.location.href.split('?')[0] + (window.location.href.includes('id=') ? '?id=$id_expediente' : '');
            window.history.replaceState(null, null, url);
        }
    });
    </script>
    ";
}
?>

$(function(){
    // ==================== MUNICIPIOS ====================
    $("#estado_exh").change(function(){
        var id_estado = $(this).val();
        if(id_estado){
            $.post("php/getMunicipios.php", {id_estado: id_estado}, function(data){
                $("#municipio_exh").html('<option value="">Seleccione...</option>');
                $.each(data, function(i, municipio){
                    $("#municipio_exh").append('<option value="'+municipio.id+'">'+municipio.municipio+'</option>');
                });
                $("#nucleo_exh").html('<option value="">Seleccione un municipio primero</option>');
            }, "json");
        } else {
            $("#municipio_exh").html('<option value="">Seleccione un estado primero</option>');
            $("#nucleo_exh").html('<option value="">Seleccione un municipio primero</option>');
        }
    });

    // ==================== NÚCLEOS ====================
    $("#municipio_exh").change(function(){
        var id_municipio = $(this).val();
        if(id_municipio){
            $.post("php/getNucleos.php", {id_municipio: id_municipio}, function(data){
                $("#nucleo_exh").html('<option value="">Seleccione...</option>');
                $.each(data, function(i, nucleo){
                    $("#nucleo_exh").append('<option value="'+nucleo.id+'">'+nucleo.nucleo+'</option>');
                });
            }, "json");
        } else {
            $("#nucleo_exh").html('<option value="">Seleccione un municipio primero</option>');
        }
    });

    // ==================== DILIGENCIAS ====================
    let contador = 0;
    $("#agregar_diligencia").click(function(){
        contador++;
        $("#tabla_diligencias tbody").append(`
        <tr>
            <td><input type="text" name="diligencias[${contador}][diligencia]" class="form-control"></td>
            <td><input type="text" name="diligencias[${contador}][nombre_destinatario]" class="form-control"></td>
            <td>
                <select name="diligencias[${contador}][estatus_diligencia]" class="form-control">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Realizada">Realizada</option>
                    <option value="Cancelada">Cancelada</option>
                </select>
            </td>
            <td><input type="file" name="diligencias[${contador}][archivo_diligencia]" class="form-control"></td>
            <td><input type="date" name="diligencias[${contador}][fecha_diligencia]" class="form-control"></td>
            <td><input type="text" name="diligencias[${contador}][observaciones_diligencia]" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm eliminar_fila">Eliminar</button></td>
        </tr>`);
    });

    $(document).on("click", ".eliminar_fila", function(){
        $(this).closest("tr").remove();
    });
});

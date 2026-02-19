/*=============================================
1. FUNCIONES DE CÁLCULO
=============================================*/

function calcularCompra() {
    // Capturamos los valores directamente
    // parseFloat leerá correctamente el punto decimal del input type="number"
    let costo = parseFloat($("#cost_purchase").val()) || 0;
    let cantidad = parseFloat($("#qty_purchase").val()) || 0;
    
    // Para la utilidad, quitamos el % si existe antes de convertir
    let utilidadRaw = $("#utility_purchase").val() ? $("#utility_purchase").val().toString().replace('%', '') : "0";
    let utilidad = parseFloat(utilidadRaw) || 0;

    // --- CÁLCULO DE INVERSIÓN ---
    let inversion = costo * cantidad;
    // Seteamos el valor con 2 decimales
    $("#invest_purchase").val(inversion.toFixed(2));

    // --- CÁLCULO DE PRECIO DE VENTA ---
    let precioVenta = costo + (costo * (utilidad / 100));
    $("#price_purchase").val(precioVenta.toFixed(2));
}

/*=============================================
2. EVENTOS DE DISPARO
=============================================*/

// Usamos 'input' porque es el evento más nativo y rápido para type="number"
$(document).on("input change", "#cost_purchase, #qty_purchase, #utility_purchase", function() {
    calcularCompra();
});

/*=============================================
3. ELIMINAR REGISTRO (SIN TOCAR TU LÓGICA)
=============================================*/

$(document).on("click", ".deleteItem", function(e) {
    e.preventDefault();
    var idItem = $(this).attr("idItem");
    var table = $(this).attr("table");
    var suffix = $(this).attr("suffix");

    fncSweetAlert("confirm", "¿Está seguro de eliminar este registro?", "Esta acción no se puede deshacer").then(function(result) {
        if (result.value) {
            var data = new FormData();
            data.append("idPageDelete", idItem);
            data.append("table", table);
            data.append("column", "id_" + suffix);
            data.append("token", localStorage.getItem("tokenAdmin"));

            $.ajax({
                url: "ajax/pages.ajax.php",
                method: "POST",
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response == 200) {
                        fncFormatHtml("success", "El registro ha sido eliminado correctamente");
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                }
            });
        }
    });
});
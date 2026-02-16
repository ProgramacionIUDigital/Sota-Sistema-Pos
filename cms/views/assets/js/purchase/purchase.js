/*=============================================
1. FUNCIONES DE CÁLCULO (INTACTAS)
=============================================*/
function priceSale(utility, cost){
    if(utility && cost){
        var utilityNum = utility.toString().includes('%') ? 
                         Number(utility.toString().replace('%', '')) : 
                         Number(utility);
        var costNum = Number(cost);
        var result = costNum + (costNum * (utilityNum / 100));
        $("#price_purchase").val(Math.round(result));
    }
}

function totalInvest(qty, cost){
    if(qty && cost){
        var total = Number(qty) * Number(cost);
        $("#invest_purchase").val(Math.round(total));
    }
}

/*=============================================
2. EVENTOS AUTOMÁTICOS DE CÁLCULO
=============================================*/
$(document).on("input change", "#qty_purchase", function(){
    totalInvest($(this).val(), $("#cost_purchase").val());
});

$(document).on("input change", "#cost_purchase", function(){
    priceSale($("#utility_purchase").val(), $(this).val());
    totalInvest($("#qty_purchase").val(), $(this).val());
});

$(document).on("input change", "#utility_purchase", function(){
    priceSale($(this).val(), $("#cost_purchase").val());
});

/*=============================================
3. VALIDACIÓN Y DISPARO DE GUARDADO
=============================================*/
$(document).on("click", ".btnGuardar", function(e) {

    // Detenemos el envío automático para validar primero
    e.preventDefault();

    let errores = [];

    // Captura de datos
    let provider   = $("#supplier_purchase").val();
    let phone      = $("#contact_purchase").val();
    let id_product = $("#id_product_purchase").val();
    let id_office  = $("#id_office_purchase").val();
    let cost       = $("#cost_purchase").val();
    let qty        = $("#qty_purchase").val();
    let utility    = $("#utility_purchase").val();
    let price      = $("#price_purchase").val();
    let invest     = $("#invest_purchase").val();

    // Limpiar estilos previos
    $("input, select").css("border","1px solid #ccc");

    // Validar cada campo
    if (!provider || !provider.trim()) {
        errores.push("Proveedor obligatorio");
        $("#supplier_purchase").css("border","2px solid red");
    }
    if (!phone || !phone.trim()) {
        errores.push("Teléfono obligatorio");
        $("#contact_purchase").css("border","2px solid red");
    }
    if (!id_product) errores.push("Producto obligatorio");
    if (!id_office) errores.push("Sucursal obligatoria");
    if (!cost || Number(cost) <= 0) errores.push("Costo inválido");
    if (!qty || Number(qty) <= 0) errores.push("Cantidad inválida");
    if (!utility) errores.push("Utilidad obligatoria");
    if (!price || Number(price) <= 0) errores.push("Precio no calculado");
    if (!invest || Number(invest) <= 0) errores.push("Inversión no calculada");

    // REVISIÓN DE ERRORES
    if (errores.length > 0) {
        alert("⚠ No se puede guardar:\n\n• " + errores.join("\n• "));
        return false; 
    }

    // ✅ SI TODO ESTÁ LLENO: FORZAMOS EL ENVÍO DEL FORMULARIO
    // Buscamos el formulario por su ID y lo enviamos
    console.log("Validación exitosa. Enviando formulario...");
    $("#form-manage").submit(); 
});

/*=============================================
4. MEJORAS DE UX
=============================================*/
$(document).on("input change", "input, select", function(){
    $(this).css("border","1px solid #ccc");
});

$(document).on("input", "#qty_purchase, #cost_purchase", function(){
    this.value = this.value.replace(/[^0-9.]/g, '');
});


/*=============================================
4. ELIMINAR REGISTRO (SINCRONIZADO CON TABLES.PHP)
=============================================*/

$(document).on("click", ".deleteItem", function(e){

    e.preventDefault();

    // Capturamos los datos que ya vienen en el botón de tables.php
    var idItem = $(this).attr("idItem"); // Ya viene en base64 según tu búsqueda
    var table = $(this).attr("table");
    var suffix = $(this).attr("suffix");

    fncSweetAlert("confirm", "¿Está seguro de eliminar este registro?", "Esta acción no se puede deshacer").then(function(result) {

        if (result.value) {

            var data = new FormData();
            data.append("idPageDelete", idItem); // No usamos btoa() porque tables.php ya lo trae encodeado
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
                        
                        setTimeout(function(){
                            location.reload();
                        }, 1000);

                    } else {
                        fncNotie(3, "Error al eliminar el registro");
                    }
                }
            });
        }
    });
});
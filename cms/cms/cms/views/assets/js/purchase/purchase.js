/*=============================================
1. FUNCIONES DE CÁLCULO (VENTA E INVERSIÓN)
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
2. EVENTOS AUTOMÁTICOS (LO QUE HABÍA DESAPARECIDO)
=============================================*/

// CALCULAR INVERSIÓN AUTOMÁTICAMENTE AL ESCRIBIR LA CANTIDAD
$(document).on("input change", "#qty_purchase", function(){
    let qty = $(this).val();
    let cost = $("#cost_purchase").val();
    totalInvest(qty, cost);
});

// CALCULAR PRECIO E INVERSIÓN AL CAMBIAR EL COSTO
$(document).on("input change", "#cost_purchase", function(){
    let cost = $(this).val();
    let utility = $("#utility_purchase").val();
    let qty = $("#qty_purchase").val();
    
    priceSale(utility, cost);
    totalInvest(qty, cost);
});

// CALCULAR PRECIO AL CAMBIAR LA UTILIDAD
$(document).on("input change", "#utility_purchase", function(){
    priceSale($(this).val(), $("#cost_purchase").val());
});

// Botón manual de Calcular (Se mantiene como respaldo)
$(document).on("click", "#btnCalculateTotals", function() {
    priceSale($("#utility_purchase").val(), $("#cost_purchase").val());
    totalInvest($("#qty_purchase").val(), $("#cost_purchase").val());
    if(typeof fncToastr === 'function') fncToastr("success", "Totales actualizados");
});

/*=============================================
3. ESCUDO DE PROTECCIÓN (VALIDACIÓN TOTAL)
=============================================*/
$(document).on("click", ".btnSavePurchase", function(e) {

    // Lista de todos los campos que el sistema EXIGIRÁ
    let campos = [
        "#id_office_purchase",   // Sucursal
        "#id_product_purchase",  // Producto
        "#cost_purchase",        // Costo
        "#utility_purchase",     // Utilidad
        "#qty_purchase",         // Cantidad
        "#price_purchase",       // Precio Venta
        "#invest_purchase"       // Inversión Total
    ];

    let incompleto = false;

    campos.forEach(id => {
        let input = $(id);
        let valor = input.val();

        // Si está vacío o es cero, resaltamos en rojo
        if (!valor || valor == "" || valor == "0" || valor == null) {
            input.css("border", "2px solid red"); 
            incompleto = true;
        } else {
            input.css("border", "1px solid #ccc"); 
        }
    });

    if (incompleto) {
        // BLOQUEO DE GUARDADO
        e.preventDefault(); 
        e.stopPropagation();

        if(typeof fncSweetAlert === 'function'){
            fncSweetAlert("error", "¡Datos Incompletos!", "No puedes guardar el registro hasta que todos los campos marcados en rojo tengan información válida.");
        } else {
            alert("Atención: Llena todos los campos resaltados en rojo.");
        }
        
        return false;
    }
    
    console.log("Validación correcta. Guardando...");
});
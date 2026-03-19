/*=============================================
1. FUNCIÓN PARA PARSEAR NÚMEROS
   Entrada: "1.234,45" → 1234.45
=============================================*/
function parseMoney(val) {
    if (!val) return 0;
    let clean = val.toString().replace(/\./g, '').replace(',', '.');
    let num = parseFloat(clean);
    return isNaN(num) ? 0 : num;
}

/*=============================================
2. TRUNCAR A 2 DECIMALES (sin redondear)
   Ej: 13.695 → 13.69
=============================================*/
function truncate2(value) {
    return Math.trunc(value * 100) / 100;
}

/*=============================================
3. FORMATO DE DINERO
   Entrada: 1234.45 → "1.234,45"
=============================================*/
function formatMoney(value) {
    let v       = truncate2(value);
    let neg     = v < 0;
    let abs     = Math.abs(v);
    let integer = Math.floor(abs);
    let decimal = Math.round((abs - integer) * 100);
    let intStr  = integer.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    let decStr  = decimal.toString().padStart(2, '0');
    return (neg ? '-' : '') + intStr + ',' + decStr;
}

/*=============================================
4. FUNCIÓN DE CÁLCULO
=============================================*/
function calcularCompra() {

    let costo    = parseMoney($("#cost_purchase").val());
    let cantidad = parseMoney($("#qty_purchase").val());

    // Utilidad: quitar el % si existe
    let utilidadRaw = $("#utility_purchase").val()
        ? $("#utility_purchase").val().toString().replace('%', '').trim()
        : "0";
    let utilidad = parseFloat(utilidadRaw) || 0;

    // --- INVERSIÓN: costo × cantidad ---
    let inversion = costo * cantidad;
    $("#invest_purchase").val(formatMoney(inversion));

    // --- PRECIO DE VENTA: costo + (costo × utilidad%) ---
    let precioVenta = costo + (costo * utilidad / 100);
    $("#price_purchase").val(formatMoney(precioVenta));
}

/*=============================================
5. EVENTOS DE DISPARO
=============================================*/
$(document).on("input change", "#cost_purchase, #qty_purchase, #utility_purchase", function() {
    calcularCompra();
});
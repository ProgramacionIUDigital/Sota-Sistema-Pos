<?php
/*=============================================
Traemos columnas de la tabla
=============================================*/
$url = "columns?linkTo=id_module_column&equalTo=".$module->id_module;
$method = "GET";
$fields = array();
$columns = CurlController::request($url,$method,$fields);

if($columns->status == 200){
    $columns = $columns->results;
}else{
    $columns = array();
}

$module->columns = $columns;

/*=============================================
Traemos contenido de la tabla
=============================================*/
$limit = 10;
$urlTable = $module->title_module."?orderBy=id_".$module->suffix_module."&orderMode=DESC&startAt=0&endAt=".$limit;
$table = CurlController::request($urlTable,$method,$fields);

if($table->status == 200){
    $table = $table->results;
    $urlTotal = $module->title_module."?select=id_".$module->suffix_module;
    $totalData = CurlController::request($urlTotal,$method,$fields)->total;
    $totalPages = ceil($totalData/$limit);
}else{ 
    $table = array();
    $totalData = 0;
}

$totalColumns = 3;
?>

<?php if (!empty($routesArray[1]) && $routesArray[1] == "manage"): ?>
    <?php 
    include "manage/manage.php";
    include "views/modules/modals/files.php";
    ?>
<?php else: ?>

<div class="<?php if ($module->width_module == "100"): ?> col-lg-12 <?php endif ?><?php if ($module->width_module == "75"): ?> col-lg-9 <?php endif ?><?php if ($module->width_module == "50"): ?> col-lg-6 <?php endif ?><?php if ($module->width_module == "33"): ?> col-lg-4 <?php endif ?><?php if ($module->width_module == "25"): ?> col-lg-3 <?php endif ?> col-12 mb-3 position-relative">

    <div class="card rounded p-3 w-100 shadow-sm" id="cardTable">

        <div class="card-header bg-white border-0 px-0">
            <div class="d-flex flex-row justify-content-between align-items-center w-100">
                
                <div class="d-flex flex-row align-items-center gap-2">
                    <?php if ($_SESSION["admin"]->rol_admin == "superadmin" || $module->editable_module == 1): ?>
                        <a href="/<?php echo $module->url_page ?>/manage" class="btn btn-default btn-sm rounded backColor px-3 py-2 shadow-sm">
                            Agregar registro
                        </a>
                        <button type="button" class="btn btn-danger btn-sm rounded px-3 py-2 btnDeleteCentral shadow-sm">
                            Eliminar seleccionados
                        </button>
                    <?php endif ?>
                </div>

                <div class="flex-grow-1 d-flex justify-content-center px-3">
                    <div class="position-relative" style="width: 250px;">
                        <input type="text" id="smartSearch" class="form-control rounded-pill form-control-sm pe-5" placeholder="Buscar...">
                        <i class="bi bi-search position-absolute" style="right: 35px; top: 7px; color: #aaa;"></i>
                        <i class="bi bi-x-circle-fill position-absolute" id="clearSearch"
                           style="right: 12px; top: 7px; cursor:pointer; display:none; color:#dc3545;"></i>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <?php if ($_SESSION["admin"]->rol_admin == "superadmin"): ?>
                        <div class="border rounded bg-white shadow-sm d-flex">
                            <button type="button" class="btn btn-sm text-muted myModule" item='<?php echo json_encode($module) ?>' idPage="<?php echo $page->results[0]->id_page ?>">
                                <i class="bi bi-pencil-square me-1"></i> <span class="small fw-bold">Editar Módulo Tabla</span>
                            </button>
                            <button type="button" class="btn btn-sm text-muted deleteModule" idModule=<?php echo base64_encode($module->id_module) ?> >
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    <?php endif ?>
                </div>

            </div>
        </div>

        <div class="card-body px-0">

            <input type="hidden" id="contentModule" value='<?php echo json_encode($module) ?>'>
            <input type="hidden" id="orderByTable" value="id_<?php echo $module->suffix_module ?>">
            <input type="hidden" id="orderModeTable" value="DESC">
            <input type="hidden" id="limitTable" value="<?php echo $limit ?>">
            <input type="hidden" id="rolAdmin" value="<?php echo $_SESSION["admin"]->rol_admin ?>">
            <input type="hidden" id="searchTable" value=""> 
            <input type="hidden" id="between1" value="<?php echo date("Y-m-d", 0) ?>">
            <input type="hidden" id="between2" value="<?php echo date("Y-m-d") ?>">
            <input type="hidden" id="checkItems" value="" table="<?php echo $module->title_module ?>" suffix="<?php echo $module->suffix_module ?>">
            
            <button type="button" class="deleteAllItems d-none"></button>

            <div class="table-responsive">
                <table class="table table-hover align-middle" width="100%" id="mainDynamicTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <?php if ($_SESSION["admin"]->rol_admin == "superadmin" || $module->editable_module == 1): ?>
                            <th>
                                <button type="button" class="btn btn-sm bg-blue rounded border-0 checkAllItems" mode="false">
                                    <i class="bi bi-check2-square"></i>
                                </button>
                            </th>  
                            <?php endif ?>
                            
                            <?php foreach ($columns as $item): ?>
                                <?php if ($item->visible_column == 1): $totalColumns++ ?>
                                    <th class="text-capitalize position-relative">
                                        <?php echo $item->alias_column ?>
                                        <i class="bi bi-arrow-down-short orderFilter" orderBy="<?php echo $item->title_column ?>" orderMode="ASC" style="cursor: pointer;"></i>
                                    </th>   
                                <?php endif ?>
                            <?php endforeach ?>

                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="small" id="loadTable">
                        <?php if (!empty($table)): ?>
                            <?php foreach (json_decode(json_encode($table),true) as $key => $value): ?>
                            <tr>
                                <td><?php echo ($key+1) ?></td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input checkItem" type="checkbox" idItem="<?php echo base64_encode($value["id_".$module->suffix_module]) ?>">
                                    </div>
                                </td>

                                <?php foreach ($columns as $item): ?>
                                    <?php if ($item->visible_column == 1): ?>
                                        <td>
                                        <?php 
                                        $val = urldecode($value[$item->title_column]);
                                        if($item->type_column == "image"){
                                            echo '<img src="'.$val.'" class="rounded shadow-sm" style="width:45px; height:45px; object-fit: cover;" onerror="this.src=\'/views/assets/img/no-image.png\'">';
                                        } else {
                                            echo TemplateController::reduceText($val, 25); 
                                        }
                                        ?> 
                                        </td>
                                    <?php endif ?>
                                <?php endforeach ?>

                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="/<?php echo $module->url_page ?>/manage/<?php echo base64_encode($value["id_".$module->suffix_module]) ?>/copy" class="btn btn-sm text-muted"><i class="bi bi-copy"></i></a>
                                        <a href="/<?php echo $module->url_page ?>/manage/<?php echo base64_encode($value["id_".$module->suffix_module]) ?>" class="btn btn-sm text-primary"><i class="bi bi-pencil-square"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach ?> 
                        <?php endif ?>
                    </tbody>
                </table>
            </div>  
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#smartSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#loadTable tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
        $("#searchTable").val(value);
        if(value.length > 0) { $("#clearSearch").show(); } else { $("#clearSearch").hide(); }
        if (typeof dataTable === "function") { dataTable(0, $("#limitTable").val(), value); }
    });

    $("#clearSearch").on("click", function(){
        $("#smartSearch").val("");
        $("#searchTable").val("");
        $(this).hide();
        $("#loadTable tr").show();
        if (typeof dataTable === "function") { dataTable(0, $("#limitTable").val(), ""); }
    });

    $(document).on("click", ".btnDeleteCentral", function() {
        if ($(".checkItem:checked").length == 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Seleccione registros para eliminar' });
            return;
        }
        Swal.fire({
            title: '¿Confirmar eliminación?',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) { 
                $(".deleteAllItems").click(); 
            } else {
                $(".checkItem").prop("checked", false);
                $(".checkAllItems").attr("mode", "false");
                $("#checkItems").val("");
            }
        });
    });
});
</script>

<?php 
  include "views/modules/modals/booleans.php"; 
  include "views/modules/modals/selects.php"; 
?>

<?php endif ?>
<?php if ($module->columns[$i]->type_column == "file" || 
$module->columns[$i]->type_column == "image" || 
$module->columns[$i]->type_column == "video"): ?>

<div class="input-group">

    <input 
    type="text" 
    class="form-control rounded-start"
    id="<?php echo $module->columns[$i]->title_column ?>"
    name="<?php echo $module->columns[$i]->title_column ?>"
    value="<?php if (!empty($data)): ?><?php echo urldecode($data[$module->columns[$i]->title_column]) ?><?php endif ?>">

    <span class="input-group-text rounded-end myFiles" style="cursor:pointer"><i class="bi bi-cloud-arrow-up-fill"></i></span>

</div>

<?php if (!empty($data) && !empty($data[$module->columns[$i]->title_column])): ?>
    
    <div class="mt-2 text-center border rounded bg-light p-2 shadow-sm">
        
        <label class="d-block mb-1 small text-muted">Vista previa:</label>
        
        <img src="<?php echo urldecode($data[$module->columns[$i]->title_column]) ?>" 
             class="img-thumbnail rounded" 
             style="max-width: 150px; height: auto;"
             onerror="this.src='views/assets/img/no-image.png';">
             
    </div>

<?php endif ?>
    
<?php endif ?>
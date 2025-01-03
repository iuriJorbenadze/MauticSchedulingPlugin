<?php $view->extend('MauticCoreBundle:Default:content.html.php'); ?>

<?php
$view['slots']->set('headerTitle', 'Custom Import');
?>

<div class="container">
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $message['type']; ?>">
                <?php echo $message['text']; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="<?php echo $view['router']->path('schedulingfeature_import'); ?>">
        <div class="form-group">
            <label for="import_file">Upload File</label>
            <input type="file" name="import_file" id="import_file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Import</button>
    </form>
</div>

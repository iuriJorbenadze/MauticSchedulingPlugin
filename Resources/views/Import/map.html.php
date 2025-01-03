<?php $view->extend('MauticCoreBundle:Default:content.html.php'); ?>

<?php $view['slots']->set('headerTitle', 'Map CSV Columns to Database Fields'); ?>

<div class="container">
    <h3>Map CSV Headers to Database Fields</h3>
    <form method="POST" action="<?php echo $view['router']->path('schedulingfeature_mapping', ['filePath' => urlencode($filePath)]); ?>">
        <input type="hidden" name="filePath" value="<?php echo $view->escape($filePath); ?>">

        <p><strong>Instructions:</strong> Please map each CSV column to the corresponding database field. You can leave columns unmapped if not needed.</p>

        <!-- Owner ID Dropdown Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h4 class="text-primary">Assign Owner</h4>
                <p>Select the owner to associate with this import.</p>
            </div>
            <div class="form-group col-md-6">
                <label for="owner_id" class="font-weight-bold text-secondary">Owner</label>
                <select name="owner_id" id="owner_id" class="form-control">
                    <option value="">-- Select Owner --</option>
                    <?php foreach ($owners as $owner): ?>
                        <option value="<?php echo $view->escape($owner['id']); ?>">
                            <?php echo $view->escape($owner['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- CSV Headers Section -->
        <div class="row">
            <div class="col-md-12">
                <h4 class="text-primary">CSV Headers</h4>
                <p>These are the headers detected from your uploaded CSV file:</p>
            </div>

            <?php foreach ($csvHeaders as $header): ?>
                <div class="form-group col-md-6">
                    <label for="map_<?php echo $view->escape($header); ?>" class="font-weight-bold text-secondary">
                        CSV Header: <?php echo $view->escape($header); ?>
                    </label>
                    <select 
                        name="map[<?php echo $view->escape($header); ?>]" 
                        id="map_<?php echo $view->escape($header); ?>" 
                        class="form-control map-select">
                        <option value="">-- Not Selected --</option>
                        <?php foreach ($modelFields as $field): ?>
                            <option value="<?php echo $view->escape($field); ?>">
                                <?php echo $view->escape($field); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Submit Mapping</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Select the Submit button
    const submitButton = document.querySelector('button[type="submit"].btn-primary');

    // Select all dropdowns
    const dropdowns = document.querySelectorAll('.map-select');

    // Check if the button exists in the DOM
    if (submitButton) {
        // Attach a click event listener to the button
        submitButton.addEventListener('click', function (event) {
            // Create an array to store selected values
            const selectedValues = [];

            // Loop through all dropdowns to collect their selected values
            dropdowns.forEach(dropdown => {
                const selectedValue = dropdown.value;
                if (selectedValue) {
                    selectedValues.push(selectedValue);
                }
            });

            // Check if no fields are selected
            if (selectedValues.length === 0) {
                alert('At least one field must be mapped.');
                event.preventDefault(); // Stop form submission
                return;
            }

            // Detect duplicate values
            const duplicates = selectedValues.filter((value, index, self) => self.indexOf(value) !== index);

            // If duplicates exist, show an alert and prevent submission
            if (duplicates.length > 0) {
                alert(`Select field only once. Duplicate values detected: ${duplicates.join(', ')}`);
                event.preventDefault(); // Stop form submission
                return;
            }

            // Check if the Owner dropdown is selected
            const ownerDropdown = document.querySelector('#owner_id');
            if (!ownerDropdown.value) {
                alert('Please select an owner for this import.');
                event.preventDefault(); // Stop form submission
                return;
            }

            // Allow form submission if no duplicates and owner is selected
            console.log('No duplicates found and owner selected, proceeding to submission.');
        });
    } else {
        console.error('Submit button not found in the DOM.');
    }
});
</script>

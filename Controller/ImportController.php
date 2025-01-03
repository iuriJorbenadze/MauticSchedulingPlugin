<?php

namespace MauticPlugin\SchedulingFeatureBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

use MauticPlugin\SchedulingFeatureBundle\Model\SchedulingFeatureModel;


class ImportController extends CommonController
{
    public function indexAction(Request $request)
    {
        $messages = [];

        if ($request->isMethod('POST')) {
            $file = $request->files->get('import_file');

            if ($file) {
                try {
                    // Save the uploaded file to a temporary location
                    $filePath = $this->saveUploadedFile($file);

                    // Redirect to the mapping page with the file path
                    return $this->redirectToRoute('schedulingfeature_mapping', ['filePath' => urlencode($filePath)]);
                } catch (FileException $e) {
                    $messages[] = ['type' => 'error', 'text' => 'File upload failed: ' . $e->getMessage()];
                }
            } else {
                $messages[] = ['type' => 'error', 'text' => 'No file uploaded.'];
            }
        }

        // Render the upload form
        return $this->delegateView([
            'viewParameters' => ['messages' => $messages],
            'contentTemplate' => 'SchedulingFeatureBundle:Import:index.html.php',
            'passthroughVars' => [
                'activeLink' => 'schedulingfeature_import',
                'route' => $this->generateUrl('schedulingfeature_import'),
            ],
        ]);
    }


private function saveUploadedFile($file)
{
    // Use plugin-specific directory for uploads
    $uploadDir = __DIR__ . '/../Resources/uploads';

    // Ensure the upload directory exists
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new \RuntimeException('Failed to create directory: ' . $uploadDir);
        }
    }

    // Generate a unique file name
    $fileName = uniqid() . '_' . $file->getClientOriginalName();

    try {
        // Move the file to the custom upload directory
        $file->move($uploadDir, $fileName);
    } catch (\Exception $e) {
        throw new \RuntimeException('Failed to save uploaded file: ' . $e->getMessage());
    }

    // Return the full path to the uploaded file
    return $uploadDir . '/' . $fileName;
}
public function mapColumnsAction(Request $request)
{
    $filePath = urldecode($request->query->get('filePath'));

    // Ensure the uploaded file exists
    if (!file_exists($filePath)) {
        throw $this->createNotFoundException('Uploaded file not found.');
    }

    // Read the CSV headers
    $csvHeaders = $this->getCsvHeaders($filePath);

    // Fetch database fields dynamically
    $modelFields = $this->getDatabaseFields();

    // Fetch owners from the database
    $connection = $this->get('database_connection');
    $owners = $connection->fetchAllAssociative("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users");

    // Handle form submission
    if ($request->isMethod('POST')) {
        $mapping = $request->request->get('map'); // Fetch mapping data from POST
        $ownerId = $request->request->get('owner_id'); // Fetch selected owner ID

        // Ensure at least one field is mapped
        $filteredMapping = array_filter($mapping); // Remove empty values

        if (empty($filteredMapping)) {
            throw new \InvalidArgumentException('At least one field must be mapped.');
        }

        // Store owner_id along with mapping for later use
        return $this->redirectToRoute('schedulingfeature_process', [
            'filePath' => urlencode($filePath),
            'mapping' => urlencode(serialize($filteredMapping)),
            'ownerId' => $ownerId, // Pass owner ID down the flow
        ]);
    }

    // Render the mapping form
    return $this->delegateView([
        'viewParameters' => [
            'csvHeaders' => $csvHeaders,
            'modelFields' => $modelFields,
            'filePath' => $filePath,
            'owners' => $owners, // Pass owners to the view
        ],
        'contentTemplate' => 'SchedulingFeatureBundle:Import:map.html.php',
    ]);
}


// public function mapColumnsAction(Request $request)
// {
//     $filePath = urldecode($request->query->get('filePath'));

//     // Ensure the uploaded file exists
//     if (!file_exists($filePath)) {
//         throw $this->createNotFoundException('Uploaded file not found.');
//     }

//     // Read the CSV headers
//     $csvHeaders = $this->getCsvHeaders($filePath);

//     // Fetch database fields dynamically
//     $modelFields = $this->getDatabaseFields();

//     // Handle form submission
//     if ($request->isMethod('POST')) {
//         $mapping = $request->request->get('map');

//         // Validate mapping: Ensure at least one field is mapped
//         $mappedFields = array_intersect_key($modelFields, array_flip($mapping));
//         if (empty($mappedFields)) {
//             throw new \InvalidArgumentException('At least one field must be mapped.');
//         }

//         // Redirect to processing phase with mapping data
//         return $this->redirectToRoute('schedulingfeature_process', [
//             'filePath' => urlencode($filePath),
//             'mapping' => urlencode(serialize($mapping)),
//         ]);
//     }

//     // Render the mapping form
//     return $this->delegateView([
//         'viewParameters' => [
//             'csvHeaders' => $csvHeaders,
//             'modelFields' => $modelFields,
//             'filePath' => $filePath,
//         ],
//         'contentTemplate' => 'SchedulingFeatureBundle:Import:map.html.php',
//     ]);
// }





    private function getCsvHeaders($filePath)
    {
        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);
        fclose($file);

        return $headers;
    }


public function processImportAction(Request $request)
{
    $filePath = urldecode($request->get('filePath'));
    $mapping = unserialize(urldecode($request->get('mapping')));
    $ownerId = $request->get('ownerId'); // Fetch owner_id from request

    if (!file_exists($filePath)) {
        throw $this->createNotFoundException('Uploaded file not found.');
    }

    try {
        // Add a new job to the queue
        $connection = $this->get('database_connection');
        $connection->insert('import_jobs', [
            'file_path' => $filePath,
            'mapping' => serialize($mapping),
            'owner_id' => $ownerId, // Include owner_id
            'status' => 'queued',
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        return $this->delegateView([
            'viewParameters' => ['messages' => [['type' => 'success', 'text' => 'Job added to queue successfully.']]],
            'contentTemplate' => 'SchedulingFeatureBundle:Import:index.html.php',
        ]);
    } catch (\Exception $e) {
        return $this->delegateView([
            'viewParameters' => ['messages' => [['type' => 'error', 'text' => 'Failed to add job to queue.']]],
            'contentTemplate' => 'SchedulingFeatureBundle:Import:index.html.php',
        ]);
    }
}



private function processImportFile($filePath, $mapping)
{
    $file = fopen($filePath, 'r');
    $headers = fgetcsv($file); // Read CSV headers

    $connection = $this->get('database_connection');

    while (($row = fgetcsv($file)) !== false) {
        $data = [];

        // Map CSV columns to application fields
        foreach ($mapping as $csvColumn => $modelField) {
            $index = array_search($csvColumn, $headers);
            if ($index !== false && $modelField) {
                $data[$modelField] = $row[$index]; // Populate data for each mapped field
            }
        }

        // Debugging: Check extracted data
        file_put_contents(__DIR__ . '/../Resources/uploads/debug.log', print_r($data, true), FILE_APPEND);

        try {
            // Use the model to validate and structure the data
            $model = new SchedulingFeatureModel($data);

            // Debugging: Check model output
            file_put_contents(__DIR__ . '/../Resources/uploads/debug.log', print_r($model->toArray(), true), FILE_APPEND);

            // Insert validated data into the database
            $connection->insert('scheduling_feature', $model->toArray());
        } catch (\Exception $e) {
            // Log errors for invalid rows
            file_put_contents(__DIR__ . '/../Resources/uploads/import_errors.log', $e->getMessage() . "\n", FILE_APPEND);
        }
    }

    fclose($file);
}

// private function getDatabaseFields(): array
// {
//     $excludedFields = ['id', 'created_at']; // List of fields to exclude
//     $connection = $this->get('database_connection');
//     $columns = $connection->fetchAllAssociative("SHOW COLUMNS FROM scheduling_feature");

//     $fields = [];
//     foreach ($columns as $column) {
//         if (!in_array($column['Field'], $excludedFields)) {
//             $fields[$column['Field']] = $column['Field'];
//         }
//     }

//     return $fields;
// }

private function getDatabaseFields(): array
{

$excludedFields = [
    'id',
    'created_by',
    'created_by_user',
    'date_added',
    'date_modified',
    'modified_by',
    'modified_by_user',
    'checked_out',
    'checked_out_by',
    'checked_out_by_user',
    'internal',
    'social_cache',
    'generated_email_domain',
    'owner_id',
    'stage_id',
    'is_published',
    'date_identified',
    'preferred_profile_image',
];


    $connection = $this->get('database_connection');

    // Fetch all columns from the table
    $columns = $connection->fetchAllAssociative("SHOW COLUMNS FROM customleads");

    // Return fields without prettifying them
    $fields = [];
    foreach ($columns as $column) {
        if (!in_array($column['Field'], $excludedFields)) {
            $fields[$column['Field']] = $column['Field']; // Use exact database field names
        }
    }

    return $fields;
}






}

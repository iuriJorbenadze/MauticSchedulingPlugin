<?php

namespace MauticPlugin\SchedulingFeatureBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class ProcessQueueCommand extends Command
{
    protected static $defaultName = 'mautic:customimport:processqueue';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Process import jobs in the queue.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting import queue processing...</info>');

        // Check if there are any jobs already in progress
        $inProgress = $this->connection->fetchOne("SELECT COUNT(*) FROM import_jobs WHERE status = 'in_progress'");
        if ($inProgress > 0) {
            $output->writeln('<comment>A job is already in progress. Exiting...</comment>');
            return 0; // Success
        }

        // Fetch the latest queued job
        $job = $this->connection->fetchAssociative("SELECT * FROM import_jobs WHERE status = 'queued' ORDER BY created_at LIMIT 1");
        if (!$job) {
            $output->writeln('<comment>No queued jobs found. Exiting...</comment>');
            return 0; // Success
        }

        // Mark the job as in progress
        $this->connection->update('import_jobs', ['status' => 'in_progress'], ['id' => $job['id']]);

        // Process the file in batches
        try {
            $filePath = $job['file_path'];
            $mapping = unserialize($job['mapping']);
        	$this->processFile($filePath, $mapping, $job['id'], $output, $job['owner_id']);

        
        } catch (\Exception $e) {
            $output->writeln("<error>Processing failed: {$e->getMessage()}</error>");
            $this->connection->update('import_jobs', [
                'status' => 'failed',
                'error_log' => $e->getMessage(),
            ], ['id' => $job['id']]);
            return 1; // Failure
        }

        $output->writeln('<info>Finished processing the import queue.</info>');
        return 0; // Success
    }


private function processFile(string $filePath, array $mapping, int $jobId, OutputInterface $output, ?int $ownerId = null)
{
    if (!file_exists($filePath)) {
        throw new \RuntimeException("File not found: $filePath");
    }

    $file = fopen($filePath, 'r');
    $headers = fgetcsv($file); // Read headers

    $rowCount = 0;
    $batchSize = 100;
    $rows = [];
    while (($row = fgetcsv($file)) !== false) {
        $rows[] = $row;
        $rowCount++;

        if (count($rows) === $batchSize) {
            $this->processBatch($rows, $headers, $mapping, $jobId, $output, $ownerId);
            $rows = []; // Clear batch
        }
    }

    if (count($rows) > 0) {
        $this->processBatch($rows, $headers, $mapping, $jobId, $output, $ownerId);
    }

    fclose($file);

    // Update job status
    $this->connection->update('import_jobs', [
        'status' => 'completed',
        'row_count' => $rowCount,
        'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
    ], ['id' => $jobId]);

    $output->writeln("<info>Job #{$jobId} completed successfully.</info>");
}


private function processBatch(array $rows, array $headers, array $mapping, int $jobId, OutputInterface $output, ?int $ownerId = null)
{
    $tableName = 'customleads';

    foreach ($rows as $row) {
        try {
            $data = [];

            foreach ($mapping as $csvColumn => $dbField) {
                if (empty($dbField)) {
                    continue;
                }

                $index = array_search($csvColumn, $headers);
                if ($index !== false) {
                    $data[$dbField] = $row[$index];
                }
            }

            if (!empty($data)) {
                // Add mandatory fields
                $data['date_added'] = (new \DateTime())->format('Y-m-d H:i:s');
                $data['is_published'] = 1;
                $data['points'] = 0;
                $data['internal'] = serialize([]);
                $data['social_cache'] = serialize([]);
                $data['date_identified'] = (new \DateTime())->format('Y-m-d H:i:s');
                $data['preferred_profile_image'] = 'gravatar';

                // Add owner_id if provided
                if ($ownerId !== null) {
                    $data['owner_id'] = $ownerId;
                }

                $this->connection->insert($tableName, $data);
            }
        } catch (\Exception $e) {
            $errorMessage = "Row error: {$e->getMessage()}";
            $this->connection->update('import_jobs', [
                'error_log' => $errorMessage,
            ], ['id' => $jobId]);
            $output->writeln("<error>$errorMessage</error>");
        }
    }

    // Update progress
    $processedCount = $this->connection->fetchOne("SELECT processed_rows FROM import_jobs WHERE id = ?", [$jobId]);
    $this->connection->update('import_jobs', [
        'processed_rows' => $processedCount + count($rows),
        'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
    ], ['id' => $jobId]);
}



// private function processBatch(array $rows, array $headers, array $mapping, int $jobId, OutputInterface $output)
// {
//     foreach ($rows as $row) {
//         try {
//             $data = [];
//             $dbFields = [];

//             // Prepare data for insertion based on the mapping
//             foreach ($mapping as $csvColumn => $dbField) {
//                 if (empty($dbField)) {
//                     continue; // Skip unmapped columns
//                 }

//                 $index = array_search($csvColumn, $headers);
//                 if ($index !== false) {
//                     $data[$dbField] = $row[$index];
//                     $dbFields[] = $dbField;
//                 }
//             }

//             if (!empty($data)) {
//                 // Construct a dynamic SQL query
//                 $this->connection->insert('scheduling_feature', $data);
//             }
//         } catch (\Exception $e) {
//             $errorMessage = "Row error: {$e->getMessage()}";
//             $this->connection->update('import_jobs', [
//                 'error_log' => $errorMessage,
//             ], ['id' => $jobId]);
//             $output->writeln("<error>$errorMessage</error>");
//         }
//     }

//     // Update progress for the job
//     $processedCount = $this->connection->fetchOne("SELECT processed_rows FROM import_jobs WHERE id = ?", [$jobId]);
//     $this->connection->update('import_jobs', [
//         'processed_rows' => $processedCount + count($rows),
//         'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
//     ], ['id' => $jobId]);
// }




}

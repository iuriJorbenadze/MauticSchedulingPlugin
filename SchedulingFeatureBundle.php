<?php

namespace MauticPlugin\SchedulingFeatureBundle;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Mautic\PluginBundle\Entity\Plugin;
use Mautic\CoreBundle\Factory\MauticFactory;
use Doctrine\DBAL\Schema\Table;

class SchedulingFeatureBundle extends PluginBundleBase
{

public static function onPluginInstall(
    Plugin $plugin,
    MauticFactory $factory,
    $metadata = null,
    $installedSchema = null
) {
    $connection = $factory->getDatabase();
    $schemaManager = $connection->getSchemaManager();

    // Create the `scheduling_feature` table if it doesn't exist
    if (!$schemaManager->tablesExist(['scheduling_feature'])) {
        $table = new Table('scheduling_feature');

        // Allow NULL values for all fields
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('first_name', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('last_name', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255, 'notnull' => false]);
    	$table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $schemaManager->createTable($table);
    }

    // Create the `import_jobs` table to track background jobs
    if (!$schemaManager->tablesExist(['import_jobs'])) {
        $table = new Table('import_jobs');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('file_path', 'string', ['length' => 255]);
        $table->addColumn('mapping', 'text');
        $table->addColumn('status', 'string', ['length' => 50, 'default' => 'queued']);
        $table->addColumn('row_count', 'integer', ['notnull' => false]);
        $table->addColumn('processed_rows', 'integer', ['default' => 0]);
        $table->addColumn('last_processed_row', 'integer', ['default' => 0]);
        $table->addColumn('error_log', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
    	$table->addColumn('owner_id', 'integer', ['notnull' => false, 'default' => null]);

        $table->setPrimaryKey(['id']);
        $schemaManager->createTable($table);
    }




    // Custom leads table creation SQL
    $sql = "
    CREATE TABLE IF NOT EXISTS `customleads` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `owner_id` int(10) unsigned DEFAULT NULL,
        `stage_id` int(10) unsigned DEFAULT NULL,
        `is_published` tinyint(1) NOT NULL,
        `date_added` datetime DEFAULT NULL,
        `created_by` int(11) DEFAULT NULL,
        `created_by_user` varchar(191) DEFAULT NULL,
        `date_modified` datetime DEFAULT NULL,
        `modified_by` int(11) DEFAULT NULL,
        `modified_by_user` varchar(191) DEFAULT NULL,
        `checked_out` datetime DEFAULT NULL,
        `checked_out_by` int(11) DEFAULT NULL,
        `checked_out_by_user` varchar(191) DEFAULT NULL,
        `points` int(11) NOT NULL,
        `last_active` datetime DEFAULT NULL,
        `internal` longtext DEFAULT NULL COMMENT '(DC2Type:array)',
        `social_cache` longtext DEFAULT NULL COMMENT '(DC2Type:array)',
        `date_identified` datetime DEFAULT NULL,
        `preferred_profile_image` varchar(191) DEFAULT NULL,
        `title` varchar(191) DEFAULT NULL,
        `firstname` varchar(191) DEFAULT NULL,
        `lastname` varchar(191) DEFAULT NULL,
        `company` varchar(191) DEFAULT NULL,
        `position` varchar(191) DEFAULT NULL,
        `email` varchar(191) DEFAULT NULL,
        `phone` varchar(191) DEFAULT NULL,
        `mobile` varchar(191) DEFAULT NULL,
        `address1` varchar(191) DEFAULT NULL,
        `address2` varchar(191) DEFAULT NULL,
        `city` varchar(191) DEFAULT NULL,
        `state` varchar(191) DEFAULT NULL,
        `zipcode` varchar(191) DEFAULT NULL,
        `timezone` varchar(191) DEFAULT NULL,
        `country` varchar(191) DEFAULT NULL,
        `fax` varchar(191) DEFAULT NULL,
        `preferred_locale` varchar(191) DEFAULT NULL,
        `attribution_date` datetime DEFAULT NULL,
        `attribution` double DEFAULT NULL,
        `website` varchar(191) DEFAULT NULL,
        `facebook` varchar(191) DEFAULT NULL,
        `foursquare` varchar(191) DEFAULT NULL,
        `instagram` varchar(191) DEFAULT NULL,
        `linkedin` varchar(191) DEFAULT NULL,
        `skype` varchar(191) DEFAULT NULL,
        `twitter` varchar(191) DEFAULT NULL,
        `generated_email_domain` varchar(255) GENERATED ALWAYS AS (substr(`email`, locate('@', `email`) + 1)) VIRTUAL COMMENT '(DC2Type:generated)',
        PRIMARY KEY (`id`),
        KEY `IDX_179045527E3C61F9` (`owner_id`),
        KEY `IDX_179045522298D193` (`stage_id`),
        KEY `lead_date_added` (`date_added`),
        KEY `date_identified` (`date_identified`),
        KEY `fax_search` (`fax`),
        KEY `preferred_locale_search` (`preferred_locale`),
        KEY `attribution_date_search` (`attribution_date`),
        KEY `attribution_search` (`attribution`),
        KEY `website_search` (`website`),
        KEY `facebook_search` (`facebook`),
        KEY `foursquare_search` (`foursquare`),
        KEY `instagram_search` (`instagram`),
        KEY `linkedin_search` (`linkedin`),
        KEY `skype_search` (`skype`),
        KEY `twitter_search` (`twitter`),
        KEY `contact_attribution` (`attribution`, `attribution_date`),
        KEY `date_added_country_index` (`date_added`, `country`),
        KEY `generated_email_domain` (`generated_email_domain`),
        CONSTRAINT `fk_customleads_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
        CONSTRAINT `fk_customleads_stage_id` FOREIGN KEY (`stage_id`) REFERENCES `stages` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
    ";

    try {
        // Execute raw SQL query
        $connection->executeStatement($sql);
    } catch (\Exception $e) {
        throw new \RuntimeException('Failed to create customleads table: ' . $e->getMessage());
    }




// Check if 'segment_name' already exists in lead_fields table
$checkFieldQuery = "
    SELECT COUNT(*) AS count 
    FROM lead_fields 
    WHERE alias = ?
";

$fieldExists = $connection->fetchOne($checkFieldQuery, ['segment_name']);
if (!$fieldExists) {
    $sqlInsert = "
    INSERT INTO lead_fields (
        is_published, label, alias, type, 
        field_group, is_required, is_fixed, is_visible, 
        is_short_visible, is_listable, is_publicly_updatable, 
        is_unique_identifer, field_order, object
    ) VALUES (
        1, 'segment_name', 'segment_name', 'text', 
        'core', 0, 0, 1, 
        1, 1, 0, 
        0, 0, 'lead'
    );
    ";

    try {
        $connection->executeStatement($sqlInsert);
    } catch (\Exception $e) {
        throw new \RuntimeException('Failed to insert into lead_fields: ' . $e->getMessage());
    }
}

// Check if 'segment_name' column exists in 'customleads' table
$checkColumnCustomLeads = "
    SELECT COUNT(*) AS count 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'customleads' 
      AND COLUMN_NAME = 'segment_name';
";

$columnExistsCustomLeads = $connection->fetchOne($checkColumnCustomLeads);
if (!$columnExistsCustomLeads) {
    $sqlAlter1 = "ALTER TABLE customleads ADD COLUMN segment_name VARCHAR(255);";

    try {
        $connection->executeStatement($sqlAlter1);
    } catch (\Exception $e) {
        throw new \RuntimeException('Failed to alter customleads table for segment_name column: ' . $e->getMessage());
    }
}

// Check if 'segment_name' column exists in 'leads' table
$checkColumnLeads = "
    SELECT COUNT(*) AS count 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'leads' 
      AND COLUMN_NAME = 'segment_name';
";

$columnExistsLeads = $connection->fetchOne($checkColumnLeads);
if (!$columnExistsLeads) {
    $sqlAlter2 = "ALTER TABLE leads ADD COLUMN segment_name VARCHAR(255);";

    try {
        $connection->executeStatement($sqlAlter2);
    } catch (\Exception $e) {
        throw new \RuntimeException('Failed to alter leads table for segment_name column: ' . $e->getMessage());
    }
}




//Transfer Data related inits

    // Define the folder path
    $schedulesDir = __DIR__ . '/Resources/schedules';

    // Ensure the folder exists
    if (!is_dir($schedulesDir)) {
        if (!mkdir($schedulesDir, 0777, true)) {
            throw new \RuntimeException("Failed to create schedules directory: $schedulesDir");
        }
    }

    // Define the files to be created
    $scheduleFile = $schedulesDir . '/schedule.txt';
    $sentSchedulesFile = $schedulesDir . '/sent_schedules.txt';
    $executionCounterFile = $schedulesDir . '/execution_counter.txt';

    // Default content for the files
    $defaultScheduleContent = json_encode([100, 200, 500, 100], JSON_PRETTY_PRINT);
    $defaultSentSchedulesContent = ""; // Initially empty
    $defaultExecutionCounterContent = "0"; // Counter starts at 0

    try {
        // Create schedule.txt
        if (!file_exists($scheduleFile)) {
            file_put_contents($scheduleFile, $defaultScheduleContent);
        }

        // Create sent_schedules.txt
        if (!file_exists($sentSchedulesFile)) {
            file_put_contents($sentSchedulesFile, $defaultSentSchedulesContent);
        }

        // Create execution_counter.txt
        if (!file_exists($executionCounterFile)) {
            file_put_contents($executionCounterFile, $defaultExecutionCounterContent);
        }
    } catch (\Exception $e) {
        throw new \RuntimeException("Failed to create schedule files: " . $e->getMessage());
    }

    // Existing table creation logic remains here...
    // Example: $connection->executeStatement($sql);

    // Informative message
    echo "Schedules folder and files created successfully.\n";




//SIMPLIFIED version which does not check before inserting

// // Additional SQL query for inserting into lead_fields table
// $sqlInsert = "
// INSERT INTO lead_fields (
//     is_published, label, alias, type, 
//     field_group, is_required, is_fixed, is_visible, 
//     is_short_visible, is_listable, is_publicly_updatable, 
//     is_unique_identifer, field_order, object
// ) VALUES (
//     1, 'segment_name', 'segment_name', 'text', 
//     'core', 0, 0, 1, 
//     1, 1, 0, 
//     0, 0, 'lead'
// );
// ";

// // Execute additional SQL query
// try {
//     $connection->executeStatement($sqlInsert);
// } catch (\Exception $e) {
//     throw new \RuntimeException('Failed to insert into lead_fields: ' . $e->getMessage());
// }

// // SQL queries for altering tables
// $sqlAlter1 = "ALTER TABLE customleads ADD COLUMN segment_name VARCHAR(255);";
// $sqlAlter2 = "ALTER TABLE leads ADD COLUMN segment_name VARCHAR(255);";

// try {
//     $connection->executeStatement($sqlAlter1);
//     $connection->executeStatement($sqlAlter2);
// } catch (\Exception $e) {
//     throw new \RuntimeException('Failed to alter tables for segment_name column: ' . $e->getMessage());
// }



}




    public static function onPluginUninstall(
        Plugin $plugin,
        MauticFactory $factory,
        $metadata = null
    ) {
        $connection = $factory->getDatabase();
        $schemaManager = $connection->getSchemaManager();

        // Drop the table if it exists
        if ($schemaManager->tablesExist(['scheduling_feature'])) {
            $schemaManager->dropTable('scheduling_feature');
        }
    }
}

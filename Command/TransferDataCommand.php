<?php

namespace MauticPlugin\SchedulingFeatureBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransferDataCommand extends Command
{
    protected static $defaultName = 'mautic:customimport:transferData';

    private Connection $connection;
    private string $schedulesDir;
    private string $scheduleFile;
    private string $sentSchedulesFile;
    private string $executionCounterFile;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        // Define file paths
        $this->schedulesDir = __DIR__ . '/../Resources/schedules';
        $this->scheduleFile = $this->schedulesDir . '/schedule.txt';
        $this->sentSchedulesFile = $this->schedulesDir . '/sent_schedules.txt';
        $this->executionCounterFile = $this->schedulesDir . '/execution_counter.txt';

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Transfer data based on schedule configuration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Load schedule
        $schedule = file_exists($this->scheduleFile) ? json_decode(file_get_contents($this->scheduleFile), true) : [0];
        // If schedule array is empty, move 500 rows as default, otherwise take the first value
        $rowsToMove = !empty($schedule) ? $schedule[0] : 0;

        // Load execution counter
        $executionCounter = file_exists($this->executionCounterFile) ? (int)file_get_contents($this->executionCounterFile) : 0;

        if ($rowsToMove === 0) {
            $output->writeln('<comment>No schedules available. Exiting...</comment>');
            return 0; // Success
        }

        try {
            // Start transaction
            $this->connection->beginTransaction();
            $output->writeln("Transferring $rowsToMove rows...");

            // Delete rows with null values in email and firstname columns
            $this->connection->executeStatement("DELETE FROM customleads WHERE email IS NULL AND firstname IS NULL");

            // Lock tables
            $this->connection->executeStatement("LOCK TABLES customleads WRITE, leads WRITE");

            // Create temporary table with IDs to move
            $this->connection->executeStatement("
                CREATE TEMPORARY TABLE customleads_to_move AS
                SELECT id
                FROM customleads
                ORDER BY id DESC
                LIMIT $rowsToMove
            ");

            // Insert into leads with ON DUPLICATE KEY UPDATE
            $insertSql = "
                INSERT INTO leads 
                (owner_id, stage_id, is_published, date_added, created_by, created_by_user, date_modified, modified_by, modified_by_user, checked_out, checked_out_by, checked_out_by_user, points, last_active, date_identified, preferred_profile_image, title, firstname, lastname, company, position, email, phone, mobile, address1, address2, city, state, zipcode, timezone, country, fax, preferred_locale, attribution_date, attribution, website, facebook, foursquare, instagram, linkedin, skype, twitter, segment_name)
                SELECT 
                owner_id, stage_id, is_published, date_added, created_by, created_by_user, date_modified, modified_by, modified_by_user, checked_out, checked_out_by, checked_out_by_user, points, last_active, date_identified, preferred_profile_image, title, firstname, lastname, company, position, email, phone, mobile, address1, address2, city, state, zipcode, timezone, country, fax, preferred_locale, attribution_date, attribution, website, facebook, foursquare, instagram, linkedin, skype, twitter, segment_name
                FROM customleads
                WHERE id IN (SELECT id FROM customleads_to_move)
                ON DUPLICATE KEY UPDATE
                    owner_id = VALUES(owner_id),
                    stage_id = VALUES(stage_id),
                    is_published = VALUES(is_published),
                    date_added = VALUES(date_added),
                    created_by = VALUES(created_by),
                    created_by_user = VALUES(created_by_user),
                    date_modified = VALUES(date_modified),
                    modified_by = VALUES(modified_by),
                    modified_by_user = VALUES(modified_by_user),
                    checked_out = VALUES(checked_out),
                    checked_out_by = VALUES(checked_out_by),
                    checked_out_by_user = VALUES(checked_out_by_user),
                    points = VALUES(points),
                    last_active = VALUES(last_active),
                    date_identified = VALUES(date_identified),
                    preferred_profile_image = VALUES(preferred_profile_image),
                    title = VALUES(title),
                    firstname = VALUES(firstname),
                    lastname = VALUES(lastname),
                    company = VALUES(company),
                    position = VALUES(position),
                    email = VALUES(email),
                    phone = VALUES(phone),
                    mobile = VALUES(mobile),
                    address1 = VALUES(address1),
                    address2 = VALUES(address2),
                    city = VALUES(city),
                    state = VALUES(state),
                    zipcode = VALUES(zipcode),
                    timezone = VALUES(timezone),
                    country = VALUES(country),
                    fax = VALUES(fax),
                    preferred_locale = VALUES(preferred_locale),
                    attribution_date = VALUES(attribution_date),
                    attribution = VALUES(attribution),
                    website = VALUES(website),
                    facebook = VALUES(facebook),
                    foursquare = VALUES(foursquare),
                    instagram = VALUES(instagram),
                    linkedin = VALUES(linkedin),
                    skype = VALUES(skype),
                    twitter = VALUES(twitter),
                    segment_name = VALUES(segment_name)
            ";

            // Execute the insert and get number of affected rows
            $actualRowsMoved = $this->connection->executeStatement($insertSql);

            // Delete the rows from customleads that were moved
            $this->connection->executeStatement("
                DELETE FROM customleads
                WHERE id IN (SELECT id FROM customleads_to_move)
            ");

            // Clean up temporary table
            $this->connection->executeStatement("DROP TEMPORARY TABLE IF EXISTS customleads_to_move");

            // Unlock the tables
            $this->connection->executeStatement("UNLOCK TABLES");

            // Commit the transaction
            $this->connection->commit();

            // Remove the executed schedule entry and update the schedule file
            array_shift($schedule);
            file_put_contents($this->scheduleFile, json_encode($schedule));

            // Log the results as per snippet logic
            // $logMessage = sprintf(
            //     "%s - Attempted to move %d rows, actually moved %d rows.\n",
            //     date('Y-m-d H:i:s'),
            //     $rowsToMove,
            //     $actualRowsMoved
            // );
            // file_put_contents($this->sentSchedulesFile, $logMessage, FILE_APPEND);

        
        	$logMessage = sprintf(
    			"%s - Attempted to move %d contacts, actually moved %d contacts.\n",
    			date('Y-m-d H:i:s'),
    			$rowsToMove,
    			$actualRowsMoved
			);
			file_put_contents($this->sentSchedulesFile, $logMessage, FILE_APPEND);
        
            // Increment and save execution counter
            $executionCounter++;
            file_put_contents($this->executionCounterFile, (string) $executionCounter);

            $output->writeln("Operation completed successfully. Attempted to move $rowsToMove rows, actually moved $actualRowsMoved rows.");

        } catch (\Exception $e) {
            // Rollback the transaction if something failed
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }
            $output->writeln("<error>Error: {$e->getMessage()}</error>");
            return 1; // Failure
        }

        return 0; // Success
    }
}

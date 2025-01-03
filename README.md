# MauticSchedulingPlugin
The Mautic Contact/Email Scheduling Plugin saves you time by allowing users to upload large amounts of data all at once and then control the outflow. It enables users to control the pace at which contacts appear in Mautic, automating the process while maintaining full control over the data flow.


## Description

The **Scheduling Feature Plugin** is a powerful extension for Mautic that enables users to import data via CSV files, process imports through a job queue, and schedule data transfers between custom tables. Designed for seamless integration and enhanced flexibility, this plugin simplifies large-scale data management.

## Features

- **Custom Import**: Upload and map CSV data to the database dynamically.
- **Job Queue Processing**: Handles file imports in queued batches to ensure data integrity.
- **Scheduled Data Transfers**: Automates the transfer of data between tables based on pre-defined schedules.
- **Error Logging**: Tracks processing errors for enhanced debugging and user feedback.

---

## Installation Instructions

### Step 1: Download the Plugin
1. Download the plugin repository as a ZIP file or clone it from the repository.

### Step 2: Place the Plugin in the Correct Directory
1. Extract the plugin files and move the folder to the `plugins/` directory of your Mautic installation.
2. Rename the folder to `SchedulingFeatureBundle`.

### Step 3: Clear the Mautic Cache
Run the following command to clear the cache and ensure Mautic recognizes the new plugin:

```bash
sudo /usr/bin/php /path-to-mautic/bin/console cache:clear
```

### Step 4: Install the Plugin

1. Navigate to the **Plugins** page in the Mautic admin panel.
2. Click the "Install/Upgrade Plugins" button to register the new plugin.

Alternatively, you can install the plugin via command line:

```bash
sudo /usr/bin/php /path-to-mautic/bin/console mautic:plugins:install
```

---

## User Flow Scenario

### Create A Segment
1. Create a segment you wish to import data to from custom import
2. Add filter to your segment which will match 'alias' of your segment. In filter dropdown choose 'segment_name' option (plugin creates that field automatically)
3. Your filter should look like this: 'segment_name' equals '{put_name_of_your_segment_into_filter}'
4. In your csv or excel file add column called 'segment_name' and as values in each row add same value from segment filter which is '{put_name_of_your_segment_into_filter}'

### Custom Import
1. Upload a CSV file through the "Custom Import" menu.
2. Map the CSV headers to the database fields dynamically.
3. Assign ownership to the imported data.
4. Queue the file for processing with the job queue.

### Job Queue Processing
1. The plugin processes queued jobs sequentially.
2. CSV data is inserted into the `customleads` table in batches.
3. Error logs track processing issues and provide detailed feedback.

### Scheduled Data Transfers
1. Define schedules in the `schedule.txt` file or through the UI.
2. The plugin moves data rows from `customleads` to `leads`.
3. Log details of each transfer in `sent_schedules.txt`.

---

## Development Details

### Directory Structure

- **`Command/`**: Contains Symfony CLI commands for processing queues and data transfers.
    - `ProcessQueueCommand.php`
    - `TransferDataCommand.php`
- **`Config/`**: Contains plugin configuration files.
    - `config.php`
- **`Controller/`**: Manages plugin-specific routes and user interactions.
    - `ImportController.php`
    - `ScheduledSendingController.php`
- **`Resources/`**: Contains uploads, schedules, and view templates.
    - `uploads/`: Directory for uploaded CSV files and logs.
    - `schedules/`: Files for managing schedules and transfer logs.
    - `views/`: Twig templates for UI rendering.
- **`SchedulingFeatureBundle.php`**: Main bundle file for setup and installation.

### Commands

1. **Process Import Queue**
    - Command: `php bin/console mautic:customimport:processqueue`
    - Processes queued import jobs in batches and inserts data into `customleads`.

2. **Transfer Data**
    - Command: `php bin/console mautic:customimport:transferData`
    - Transfers rows from `customleads` to `leads` based on the schedule.

---

## Authors

- **Iuri Jorbenadze** - [Email](mailto:jorbenadze2001@gmail.com)

---

## License

This project is licensed under the GPL-3.0-or-later License.

---


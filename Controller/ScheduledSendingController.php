<?php

namespace MauticPlugin\SchedulingFeatureBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ScheduledSendingController extends CommonController
{
    private string $schedulesDir;
    private string $scheduleFile;
    private string $sentSchedulesFile;

    public function __construct()
    {
        $this->schedulesDir = __DIR__ . '/../Resources/schedules';
        $this->scheduleFile = $this->schedulesDir . '/schedule.txt';
        $this->sentSchedulesFile = $this->schedulesDir . '/sent_schedules.txt';
    }

// public function loadSentSchedulesFromFileAction(): JsonResponse
// {
//     $filePath = $this->schedulesDir . '/sent_schedules.txt';

//     if (!file_exists($filePath)) {
//         return new JsonResponse(['success' => false, 'error' => 'Sent schedules file not found.']);
//     }

//     try {
//         $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
//         $parsedEntries = [];

//         foreach ($lines as $line) {
//             if (preg_match('/^(?<date>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) - Attempted to move (?<attempted>\d+) rows, actually moved (?<sent>\d+) rows\.$/', $line, $matches)) {
//                 $date = new \DateTime($matches['date']);
//                 $parsedEntries[] = [
//                     'date' => $date->format('F j, Y'), // e.g., "December 9, 2024"
//                     'sent' => (int) $matches['sent'],
//                     'attempted' => (int) $matches['attempted'],
//                 ];
//             }
//         }

//         return new JsonResponse(['success' => true, 'data' => $parsedEntries]);
//     } catch (\Exception $e) {
//         return new JsonResponse(['success' => false, 'error' => $e->getMessage()]);
//     }
// }

public function loadSentSchedulesFromFileAction(): JsonResponse
{
    $filePath = $this->schedulesDir . '/sent_schedules.txt';

    if (!file_exists($filePath)) {
        return new JsonResponse(['success' => false, 'error' => 'Sent schedules file not found.']);
    }

    try {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $parsedEntries = [];

        foreach ($lines as $line) {
            if (preg_match('/^(?<date>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) - Attempted to move (?<attempted>\d+) contacts, actually moved (?<sent>\d+) contacts\.$/', $line, $matches)) {
                $parsedEntries[] = [
                    'date' => $matches['date'],
                    'sent' => (int) $matches['sent'],
                    'attempted' => (int) $matches['attempted'],
                ];
            }
        }

        // Reverse the parsed entries to show the newest entries first
        $parsedEntries = array_reverse($parsedEntries);

        return new JsonResponse(['success' => true, 'data' => $parsedEntries]);
    } catch (\Exception $e) {
        return new JsonResponse(['success' => false, 'error' => $e->getMessage()]);
    }
}




public function saveSchedulesToFileAction(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    if (!isset($data['schedules']) || !is_array($data['schedules'])) {
        return new JsonResponse(['success' => false, 'error' => 'Invalid schedule data provided.']);
    }

    try {
        $filePath = $this->schedulesDir . '/schedule.txt';
        file_put_contents($filePath, json_encode($data['schedules'], JSON_PRETTY_PRINT));
        return new JsonResponse(['success' => true]);
    } catch (\Exception $e) {
        return new JsonResponse(['success' => false, 'error' => $e->getMessage()]);
    }
}


    public function loadSentSchedulesAction(): JsonResponse
    {
        if (!file_exists($this->sentSchedulesFile)) {
            return new JsonResponse(['success' => false, 'error' => 'Sent schedules file not found.']);
        }

        $sentSchedules = array_map(function ($line) {
            $parts = explode(' - ', $line);
            return [
                'date' => trim($parts[0]),
                'sent' => (int) trim($parts[1]),
                'attempted' => (int) trim($parts[2]),
            ];
        }, file($this->sentSchedulesFile));

        return new JsonResponse(['success' => true, 'data' => $sentSchedules]);
    }

public function triggerTransferDataAction(): JsonResponse
{
    try {
        $command = $this->get('schedulingfeature.command.transferdata'); // Ensure the service is defined in the plugin config
        $input = new \Symfony\Component\Console\Input\ArrayInput([]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();

        // Execute the command
        $command->run($input, $output);

        // Get the command output for debugging/logging
        $commandOutput = $output->fetch();

        return new JsonResponse(['success' => true, 'details' => $commandOutput]);
    } catch (\Exception $e) {
        return new JsonResponse(['success' => false, 'error' => $e->getMessage()]);
    }
}


public function loadSchedulesFromFileAction(): JsonResponse
{
    try {
        // Define the file path
        $filePath = $this->schedulesDir . '/schedule.txt';

        // Check if the file exists
        if (!file_exists($filePath)) {
            return new JsonResponse(['success' => false, 'error' => 'Schedule file not found.']);
        }

        // Read and decode the file content
        $content = file_get_contents($filePath);
        $schedules = json_decode($content, true);

        // Check if the content is a valid array
        if (!is_array($schedules)) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid schedule format.']);
        }

        return new JsonResponse(['success' => true, 'data' => $schedules]);
    } catch (\Exception $e) {
        return new JsonResponse(['success' => false, 'error' => $e->getMessage()]);
    }
}


public function indexAction()
{
    return $this->delegateView([
        'viewParameters' => [],
        'contentTemplate' => 'SchedulingFeatureBundle:Scheduled:index.html.php',
        'passthroughVars' => [
            'activeLink' => 'schedulingfeature_scheduledsending',
        ],
    ]);
}

}

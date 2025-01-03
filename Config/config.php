<?php

return [
    'name'        => 'Scheduling Feature',
    'description' => 'A plugin for scheduling and importing data.',
    'version'     => '1.0.0',
    'author'      => 'Your Name',


'routes' => [
    'main' => [
        'schedulingfeature_import' => [
            'path'       => '/customimport',
            'controller' => 'SchedulingFeatureBundle:Import:index',
            'method'     => 'GET|POST',
        ],
        'schedulingfeature_mapping' => [
            'path'       => '/customimport/map',
            'controller' => 'SchedulingFeatureBundle:Import:mapColumns',
            'method'     => 'GET|POST',
        ],
        'schedulingfeature_process' => [
            'path'       => '/customimport/process',
            'controller' => 'SchedulingFeatureBundle:Import:processImport',
            'method'     => 'GET|POST',
        ],
        'schedulingfeature_process_queue' => [
            'path'       => '/customimport/processqueue',
            'controller' => 'SchedulingFeatureBundle:Import:processQueue',
            'method'     => 'GET',
        ],
   
        'schedulingfeature_scheduledsending' => [
            'path'       => '/scheduledsending',
            'controller' => 'SchedulingFeatureBundle:ScheduledSending:index',
            'method'     => 'GET|POST',
        ],

        'schedulingfeature_load_schedules' => [
            'path'       => '/scheduledsending/load-schedules',
            'controller' => 'SchedulingFeatureBundle:ScheduledSending:loadSchedulesFromFile',
            'method'     => 'GET',
        ],
    
        'schedulingfeature_save_schedules' => [
            'path'       => '/scheduledsending/save-schedules',
            'controller' => 'SchedulingFeatureBundle:ScheduledSending:saveSchedulesToFile',
            'method'     => 'POST',
        ],
    
        'schedulingfeature_load_sent_schedules' => [
            'path'       => '/scheduledsending/load-sent-schedules',
            'controller' => 'SchedulingFeatureBundle:ScheduledSending:loadSentSchedulesFromFile',
            'method'     => 'GET',
        ],
        
    	'schedulingfeature_transfer_data' => [
            'path'       => '/scheduledsending/send-now',
            'controller' => 'SchedulingFeatureBundle:ScheduledSending:triggerTransferData',
            'method'     => 'POST',
        ],
    
    
    ],
],





'menu' => [
    'main' => [
        'items' => [
            'scheduling_feature' => [
                'route'      => 'schedulingfeature_import',
                'label'      => 'Custom Import',
                'iconClass'  => 'fa-calendar',
                'priority'   => 50,
                'description'=> 'Import custom data into the system',
            ],
        
            'scheduled_sending' => [
                'route'      => 'schedulingfeature_scheduledsending', // Link to the new route
                'label'      => 'Scheduled Sending', // Button text
            	'iconClass'  => 'fa-paper-plane',
                'priority'   => 60, // Adjust to place after "Custom Import"
                'description'=> 'View and manage scheduled sending tasks', // Tooltip description
            ],

        
        ],
    ],
],

'services' => [
    'other' => [
        'schedulingfeature.controller.import' => [
            'class' => \MauticPlugin\SchedulingFeatureBundle\Controller\ImportController::class,
        ],
        'schedulingfeature.controller.scheduledsending' => [
            'class' => \MauticPlugin\SchedulingFeatureBundle\Controller\ScheduledSendingController::class,
        ],
   
    ],

'commands' => [
    'schedulingfeature.command.processqueue' => [
        'class' => \MauticPlugin\SchedulingFeatureBundle\Command\ProcessQueueCommand::class,
        'arguments' => [
            '@doctrine.dbal.default_connection',
        ],
    ],
    'schedulingfeature.command.transferdata' => [
        'class' => \MauticPlugin\SchedulingFeatureBundle\Command\TransferDataCommand::class,
        'arguments' => [
            '@doctrine.dbal.default_connection', // Inject the database connection here
        ],
    ],
],


],

];

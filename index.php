<?php
    ini_set('display_errors', 1);

    session_start();

    include_once __DIR__ . '/vendor/autoload.php';

    spl_autoload_register(function ($class_name) {
        include $class_name . '.php';
    });

    $amoCRM = new AmoCRM('mazqazasd@outlook.com', '112f8e19ebf782451171efff26b1b071c65b2bbd', 'mazqazasd');

    $authResult = $amoCRM->authorization();

    if ($authResult['auth']) {
        echo 'Hello ' . $authResult['accounts'][0]['name'] . '!<br>';
        $leadsResult = $amoCRM->getLeads();
        $leadsId = array();
        if (isset($leadsResult)) {
            foreach ($leadsResult as $value)
                $leadsId[] = $value['id'];
            echo 'Leads id: ' . implode(', ', $leadsId) . '<br>';
        } else
            echo 'Does not contain elements in leads!<br>';

        $tasksResult = $amoCRM->getTasks();
        $tasksId = array();
        if (isset($tasksResult)) {
            foreach ($tasksResult as $value)
                if ($value['is_completed'] != true)
                    $tasksId[] = $value['element_id'];
            echo 'Tasks element_id: ' . implode(', ', $tasksId) . '<br>';
        } else
            echo 'Does not contain elements in tasks!<br>';

        $leadsIdWithoutTask = array_diff($leadsId, $tasksId);

        if (sizeof($leadsIdWithoutTask)) {
            echo 'Leads id without task: ' . implode(', ', $leadsIdWithoutTask) . '<br>';

            $taskList = array();
            foreach ($leadsIdWithoutTask as $id) {
                $taskList[] = array(
                    'element_id' => $id,
                    'element_type' => 2,
                    'text' => 'Сделка без задачи',
                );
            }

            if ($amoCRM->addTasks($taskList)) {
                echo 'Successful!<br>';
            } else
                echo 'Not Completed!<br>';
        } else
            echo 'No leads without tasks!<br>';
    }

    $amoCRM->closeCurl();
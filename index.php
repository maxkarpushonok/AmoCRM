<?php
    ini_set('display_errors', 1);

    session_start();

    include_once __DIR__ . '/vendor/autoload.php';
    include_once 'AmoCRM.php'; //TODO auto load

    $amoCRM = new AmoCRM('mazqazasd@outlook.com', '112f8e19ebf782451171efff26b1b071c65b2bbd', 'mazqazasd');

    $authResult = $amoCRM->authorization();

    if ($authResult['auth']) {
        $leadsResult = $amoCRM->getLeads();
        $leadsId = array();
        if (isset($leadsResult))
            foreach ($leadsResult as $value)
                $leadsId[] = $value['id'];
        echo var_dump($leadsId).'<br>'; //TODO delete

        $tasksResult = $amoCRM->getTasks(); echo $tasksResult;
        $tasksId = array();
        if (isset($tasksResult))
            foreach ($tasksResult as $value)
                if ($value['is_completed'] != true)
                    $tasksId[] = $value['element_id'];
        echo var_dump($tasksId).'<br>'; //TODO delete

        $leadsIdWithoutTask = array_diff($leadsId, $tasksId);
        echo var_dump($leadsIdWithoutTask).'<br>'; //TODO delete

        if (isset($leadsIdWithoutTask)) {
            $taskList = array();
            foreach ($leadsIdWithoutTask as $id) {
                $taskList[] = array(
                    'element_id' => $id,
                    'element_type' => 2,
                    'text' => 'Сделка без задачи',
                );
            }

            echo $amoCRM->addTasks($taskList); //TODO delete
        }
    }

    $amoCRM->closeCurl();
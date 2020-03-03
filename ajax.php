<?php
    include_once __DIR__ . '/vendor/autoload.php';

    spl_autoload_register(function ($class_name) {
        include $class_name . '.php';
    });

    if(isset($_POST['auth']) || isset($_POST['add'])) {
        $login = htmlspecialchars($_POST['login']);
        $hash = htmlspecialchars($_POST['hash']);
        $subDomain = htmlspecialchars($_POST['subDomain']);

        $amoCRM = new AmoCRM($login, $hash, $subDomain);
        $authResult = $amoCRM->authorization();

        if ($authResult['auth']) {
            $result = array('result' => true, 'message' => 'Authorization successful!');
            $result['auth'] = $authResult['accounts'][0];

            $leadsResult = $amoCRM->getLeads();
            $leadsId = array();
            if (isset($leadsResult)) {
                foreach ($leadsResult as $value)
                    $leadsId[] = $value['id'];
                $result['leads'] = 'Leads id: ' . implode(', ', $leadsId);
            } else
                $result['leads']  = 'Does not contain elements in leads!';

            $tasksResult = $amoCRM->getTasks();
            $tasksId = array();
            if (is_array($tasksResult)) {
                foreach ($tasksResult as $value)
                    if ($value['is_completed'] != true)
                        $tasksId[] = $value['element_id'];
                $result['tasks']  = 'Tasks element_id: ' . implode(', ', $tasksId);
            } else
                $result['tasks'] = 'Does not contain elements in tasks!';

            $leadsIdWithoutTask = array_diff($leadsId, $tasksId);

            if (sizeof($leadsIdWithoutTask)) {
                $result['leadsWithoutTasks'] = true;
                $result['leadsIdWithoutTasks'] = 'Leads id without task: ' . implode(', ', $leadsIdWithoutTask);

                if (isset($_POST['add'])) {
                    $tasksList = array();
                    foreach ($leadsIdWithoutTask as $id) {
                        $tasksList[] = array(
                            'element_id' => $id,
                            'element_type' => 2,
                            'text' => 'Сделка без задачи',
                        );
                    }

                    if ($amoCRM->addTasks($tasksList)) {
                        $result['addResult'] = true;
                        $result['addMessage'] ='Successful!';
                    } else {
                        $result['addResult'] = false;
                        $result['addMessage'] = 'Not completed!';
                    }
                }

            } else {
                $result['leadsWithoutTasks'] = false;
                $result['leadsIdWithoutTasks'] = 'No leads without tasks!';
            }

            echo json_encode($result);
        } else
            echo json_encode(array('result' => false, 'message' => 'Authorization failed!'));

        $amoCRM->closeCurl();
    }
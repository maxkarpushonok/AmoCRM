<?php
    class AmoCRM {
        private $user = null;
        private $domain = null;
        private $curl = null;

        const PAGES = array(
            'AUTH' => '/private/api/auth.php?type=json',
            'LEADS' => '/api/v2/leads?',
            'TASKS' => '/api/v2/tasks?'
        );

        public function __construct(String $login, String $hash, String $subDomain) {
            $this->user = array('USER_LOGIN' => $login, 'USER_HASH' => $hash);
            $this->domain = 'https://'.$subDomain.'.amocrm.ru';
            $this->curl = curl_init();
            $this->setOpt();
        }

        public function authorization() {
            curl_setopt($this->curl, CURLOPT_URL, $this->domain.self::PAGES['AUTH']);
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
            $out = curl_exec($this->curl);
            $code = (int) curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $this->checkCode($code);
            return (json_decode($out, true))['response'];
        }

        public function getLeads(array $params = null) {
            return $this->getPage(self::PAGES['LEADS'], $params);
        }

        public function getTasks(array $params = null) {
            return $this->getPage(self::PAGES['TASKS'], $params);
        }

        public function addTasks() {

        }

        private function setOpt() {
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($this->user));
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($this->curl, CURLOPT_HEADER, false);
            curl_setopt($this->curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt');
            curl_setopt($this->curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt');
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
        }

        private function getPage(String $page, array $params = null) {
            if ($params != null)
                $params = implode('&', $params);
            echo $params.'<br>'; //TODO delete
            curl_setopt($this->curl, CURLOPT_URL, $this->domain.$page.$params);
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');
            $out = curl_exec($this->curl);
            $code = (int) curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $this->checkCode($code);
            return (json_decode($out, true))['_embedded']['items'];
        }

        public function closeCurl() {
            if ($this->curl != null)
                curl_close($this->curl);
        }

        private function checkCode($code) {
            $errors = array(
                301 => 'Moved permanently',
                400 => 'Bad request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                500 => 'Internal server error',
                502 => 'Bad gateway',
                503 => 'Service unavailable',
            );

            try {
                //Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
                if ($code != 200 && $code != 204) {
                    throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
                }
            } catch (Exception $E) {
                die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
            }
        }
    }
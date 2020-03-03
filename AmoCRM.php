<?php

    /**
     * Class AmoCRM
     * Retrieving and adding data to amoCRM using the API
     */
    class AmoCRM {
        private $user = null;
        private $domain = null;
        private $curl = null;

        /**
         * API page list
         */
        const PAGES = array(
            'AUTH' => '/private/api/auth.php?type=json&',
            'LEADS' => '/api/v2/leads?',
            'TASKS' => '/api/v2/tasks?'
        );

        /**
         * AmoCRM constructor.
         * @param String $login amocrm user login (e-mail)
         * @param String $hash amocrm user hash (from profile)
         * @param String $subDomain amocrm subdomain name
         */
        public function __construct(String $login, String $hash, String $subDomain) {
            $this->user = array('USER_LOGIN' => $login, 'USER_HASH' => $hash);
            $this->domain = 'https://' . $subDomain . '.amocrm.ru';
            $this->curl = curl_init();

            $this->setOpt();
        }

        /**
         * @return mixed (associative array with authorized user data)
         */
        public function authorization() {
            curl_setopt($this->curl, CURLOPT_URL, $this->domain . self::PAGES['AUTH']);
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
            $out = curl_exec($this->curl);
            $code = (int) curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $this->checkCode($code);
            return (json_decode($out, true))['response'];
        }

        /**
         * @param array|null $params (GET params (key=>value) for amoCRM request to leads)
         * @return mixed (associative array with leads data)
         */
        public function getLeads(array $params = null) {
            return $this->getPage(self::PAGES['LEADS'], $params);
        }

        /**
         * @param array|null $params (GET params (key=>value) for amoCRM request to tasks)
         * @return mixed (associative array with tasks data)
         */
        public function getTasks(array $params = null) {
            return $this->getPage(self::PAGES['TASKS'], $params);
        }

        /**
         * @param array|null $params (POST params with associative array tasks data)
         * @return mixed
         */
        public function addTasks(array $params = null) {
            $tasks['add'] = $params;
            curl_setopt($this->curl, CURLOPT_URL, $this->domain . self::PAGES['TASKS']);
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($tasks));
            $out = curl_exec($this->curl);
            $code = (int) curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $this->checkCode($code);
            return true;
        }

        /**
         * Sets the default params for curl
         */
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

        /**
         * @param String $page (page url)
         * @param array|null $params (get params (key=>value) for amoCRM request)
         * @return mixed (associative array)
         */
        private function getPage(String $page, array $params = null) {
            if ($params != null)
                $params = implode('&', $params);
            curl_setopt($this->curl, CURLOPT_URL, $this->domain . $page . $params);
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');
            $out = curl_exec($this->curl);
            $code = (int) curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $this->checkCode($code);
            return (json_decode($out, true))['_embedded']['items'];
        }

        /**
         * Close curl
         */
        public function closeCurl() {
            if ($this->curl != null)
                curl_close($this->curl);
        }

        /**
         * Checking code and display error if exists
         * @param $code (error code number)
         */
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
                //If answer code is not 200 or 204 - return error message
                if ($code != 200 && $code != 204) {
                    throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
                }
            } catch (Exception $E) {
                die('Error: ' . $E->getMessage() . PHP_EOL . 'Error code: ' . $E->getCode());
            }
        }
    }
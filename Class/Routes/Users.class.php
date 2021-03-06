<?php 
    require_once "./Class/Routes/Request.class.php";
    require_once "./Class/Connection/connection.php";
    require_once "./Class/Interface/interface.php";

    class Users extends Connection implements RequestClass  {
        private $userID;
        private $username = "";
        private $lastname = "";
        private $email = "";
        private $embarcacionesID = "";
        private $token = "";

        public function getList($page = 1) {
            $_Request = new Request();
            $start = 0;
            $count = 100;

            if($page > 1) {
                $start = ($count * ($page - 1)) + 1;
                $count = $count * $page;
            }

            $query = "SELECT * FROM usuarios LIMIT $start, $count";
            $result = parent::getData($query);

            if($result) {
                return $this->returnResponseJSON($result);
            }

            if(!$result) {
                $_Request->httpResponseCode(500);
                return $this->returnResponseJSON($_Request->error_500());
            }
        }

        public function getUser($id = 0) {
            $query = "SELECT * FROM usuarios WHERE userID = $id";
            $result = parent::getData($query);

            if($result) {
                return $this->returnResponseJSON($result);
            }

            if(!$result) {
                $_Request->httpResponseCode(500);
                return $this->returnResponseJSON($_Request->error_500());
            }
        }

        public function post($json) {
            $data = json_decode($json, true);
            $_Request = new Request();
            
            if(!isset($data["token"])) {
                return $_Request->returnResponseJSON($_Request->error_401());
            }

            if(isset($data["token"])) {
                $this->token = $data["token"];

                $dataToken = $this->searchToken();

                if($dataToken) {
                    if(!isset($data["username"])
                        ||!isset($data["lastname"])
                        || !isset($data["email"])
                        || !isset($data["embarcacionesID"])) {
                        $_Request->httpResponseCode(400);
                        return $this->returnResponseJSON($_Request->error_400());
                    } else {
                        $result = $this->insertUser($data);

                        if($result) {
                            $_Request->httpResponseCode(200);
                            return $this->returnResponseJSON($_Request->success_200(array("id" => $result)));
                        }

                        if(!$result) {
                            $_Request->httpResponseCode(500);
                            return $this->returnResponseJSON($this->error_500());
                        }
                    }
                }

                if(!$dataToken) {
                    $_Request->httpResponseCode(401);
                    return $_Request->returnResponseJSON($_Request->error_401());
                }
            }

        }

        public function insertUser($data) {
            $this->username = $data["username"];
            $this->lastname = $data["lastname"];
            $this->email = $data["email"];
            $this->embarcacionesID = $data["embarcacionesID"];

            $query = "INSERT INTO usuarios (username, lastname, email, embarcacionesID) VALUES ('$this->username', '$this->lastname', '$this->email', '$this->embarcacionesID')";
            $result = parent::anyQueryID($query);

            if($result) {
                return $result;
            }

            if(!$result) {
                return false;
            }
        }

        public function put($json) {
            $data = json_decode($json, true);
            $_Request = new Request();

            if(!isset($data["userID"])) {
                $_Request->httpResponseCode(400);
                return $this->returnResponseJSON($_Request->error_400());
            }

            if(isset($data["userID"])) {
                $this->userID = $data["userID"];

                if(isset($data["username"])) $this->username = $data["username"];
                if(isset($data["lastname"])) $this->lastname = $data["lastname"];
                if(isset($data["email"])) $this->email = $data["email"];

                if(isset($data["embarcacionesID"])) {
                    $this->embarcacionesID = $data["embarcacionesID"];
                }

                $result = $this->updateUser();

                if($result) {
                    $_Request->httpResponseCode(200);
                    return $this->returnResponseJSON($_Request->success_200(array("userID" => $this->userID, "count" => $result)));
                }

                if(!$result) {
                    $_Request->httpResponseCode(500);
                    return $this->returnResponseJSON($this->error_500());
                }
            }
        }

        public function updateUser() {
            $query = "UPDATE usuarios SET";

            if($this->username !== "") {
                $query .= " username = '$this->username', ";
            }

            if($this->lastname !== "") {
                $query .= " lastname = '$this->lastname', ";
            }

            if($this->email !== "") {
                $query .= " email = '$this->email', ";
            }

            if($this->embarcacionesID !== "") {
                $query .= " embarcacionesID = '$this->embarcacionesID', ";
            }

            //Si solo es uno, quita la ultima coma
            if(strlen($query) > strlen("UPDATE Embarcaciones SET ")) {
                $query = substr($query, 0, strlen($query) - 2);
            }

            $query .= " WHERE userID = $this->userID";

            $result = parent::anyQuery($query);

            if($result >= 1) {
                return $result;
            }

            if($result < 1) {
                return false;
            }
        }

        public function delete($id) {
            $data = json_decode($id, true);
            $_Request = new Request();

            if(!isset($data["userID"])) {
                $_Request->httpResponseCode(400);
                return $this->returnResponseJSON($_Request->error_400());
            }

            if(isset($data["userID"])) {
                $this->userID = $data["userID"];

                $result = $this->deleteUser();

                if($result) {
                    $_Request->httpResponseCode(200);
                    return $this->returnResponseJSON($_Request->success_200(array("userID" => $this->userID, "count" => $result)));
                }

                if(!$result) {
                    $_Request->httpResponseCode(500);
                    return $this->returnResponseJSON($this->error_500());
                }

            }
        }

        public function deleteUser() {
            $query = "DELETE FROM usuarios WHERE userID = $this->userID";
            $result = parent::anyQuery($query);

            if($result >= 1) {
                return $result;
            }

            if($result < 1) {
                return false;
            }
        }

        public function returnResponseJSON($response) {
            return json_encode($response);
        }

        private function searchToken() {
            $query = "SELECT userID, token, status FROM users_token WHERE token = '$this->token' AND status = 'active'";
            $response = parent::getData($query);

            if($response) {
                return $response;
            }

            if(!$response) {
                return false;
            }
        }

        private function updateToken($tokenId) {
            $date = date("Y-m-d H:i:s");
            $query = "UPDATE users_token SET date = '$date' WHERE id = $tokenId";

            $response = parent::anyQuery($query);

            if($response >= 1) {
                return $response;
            }

            if($response < 1) {
                return false;
            }
        }
  
    }
?>
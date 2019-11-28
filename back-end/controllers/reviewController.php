<?php namespace Controllers;

require_once 'models/review.php';
require_once 'models/user.php';

use Models\User;
use Models\Review;

class ReviewController
{
    private $username;
    private function getHeaderAuth()
    {
        foreach (getallheaders() as $name => $value) {
            if ($name === 'Authorization') {
                return $value;
            }
        }
    }
    
    private function validateAccessToken(User $user, $connection, $access_token)
    {
        $token_expdate_arr = $user->validateAccessToken($connection, $access_token);
        $token_expdate = $token_expdate_arr[0];
        if ($token_expdate_arr !== '500') {
            if ($this->validateExpDate($token_expdate)) {
                // returnResponse('200', 'Access Token Valid.');
                return true;
            }
        } else {
            returnResponse('500', 'Invalid Access Token.');
            return false;
        }
    }

    private function validateExpDate($token_expdate)
    {
        $validate = true;
        if ($token_expdate < $this->generateCurrentDate()) {
            $validate = false;
            returnResponse('500', 'Expired Access Token.');
        }
        return $validate;
    }

    private function generateCurrentDate()
    {
        return date('Y-m-d H:i:s');
    }

    private function fetchUsername(User $user, $connection, $access_token)
    {
        $this->username = $user->fetchUsername($connection, $access_token);
    }

    public function submit($connection)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $access_token = $this->getHeaderAuth();
            $user = new User($connection);
            if ($this->validateAccessToken($user, $connection, $access_token)) {
                $this->fetchUsername($user, $connection, $access_token);
                $data = json_decode(file_get_contents("php://input"));
                $review = new Review($connection);
                $result = $review->submitReview($data->description, $data->rating, $this->username, $data->id_movie, $connection);
                if ($result === '200') {
                    returnResponse('200', 'Review has been inserted.');
                } else {
                    returnResponse('500', 'Internal server error.');
                }
            }
        } else {
            returnResponse('500', 'Invalid HTTP REQUEST.');
        }
    }

    public function delete($connection, $params)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $access_token = $this->getHeaderAuth();
            $user = new User($connection);
            if ($this->validateAccessToken($user, $connection, $access_token)) {
                $this->fetchUsername($user, $connection, $access_token);
                $review = new Review($connection);
                if ($params['id_review']) {
                    $result = $review->delete($connection, $params);
                    if ($result === '200') {
                        returnResponse('200', 'Review has been deleted.');
                    } else {
                        returnResponse('500', 'Internal server error.');
                    }
                } else {
                    returnResponse('500', 'Internal server error.');
                }
            }
        } else {
            returnResponse('500', 'Invalid HTTP REQUEST.');
        }
    }

    public function edit($connection, $params)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $access_token = $this->getHeaderAuth();
            $user = new User($connection);
            if ($this->validateAccessToken($user, $connection, $access_token)) {
                $this->fetchUsername($user, $connection, $access_token);
                $data = json_decode(file_get_contents("php://input"));
                $review = new Review($connection);
                $result = $review->edit($data->description, $data->rating, $connection, $params);
                if ($result === '200') {
                    returnResponse('200', 'Review has been edited.');
                } else {
                    returnResponse('500', 'Internal server error.');
                }
            }
        } else {
            returnResponse('500', 'Invalid HTTP REQUEST.');
        }
    }

    public function fetch($connection, $params) //ngambil semua data kursi, schedule
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $access_token = $this->getHeaderAuth();
            $user = new User($connection);
            $seat = new Seat($connection);
            $schedule = new Schedule($connection);
            if ($this->validateAccessToken($user, $connection, $access_token)) {
                if ($params['schedule']) { //if exist param, get all seat
                    $this->getAllAttributes($connection, $params, $seat, $schedule);
                } else {
                    returnResponse('500', 'Invalid Params.');
                }
            }
        } else {
            returnResponse('500', 'Invalid HTTP REQUEST.');
        }
    }

    
    public function getAllAttributes($connection, $params, Seat $seat, Schedule $schedule)
    {
        $seats_arr = $seat->getStatus($connection, $params);
        $movie_arr = $schedule->getMovieOnSchedule($connection, $params);
        if ($seats_arr != '500' && $movie_arr != '500') {
            $this->render($seats_arr, $movie_arr);
        } else {
            returnResponse('500', 'Internal Server Error.');
        }
    }
}

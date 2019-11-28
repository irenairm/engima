<?php namespace Controllers;

include_once 'utils/response.php';
require_once 'models/movie.php';
require_once 'models/review.php';
require_once 'models/user.php';
require_once 'models/schedule.php';

use Models\Movie;
use Models\User;
use Models\Schedule;
use Models\Review;

class DetailController
{
    private $movie;
    private $schedules;
    private $reviews;
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

    public function fetch($connection, $params)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $access_token = $this->getHeaderAuth();
            $user = new User($connection);
            $movie = new Movie($connection);
            if ($this->validateAccessToken($user, $connection, $access_token)) {
                if ($params['id']) {
                    $resAtr = $this->getAllAttributesOneMovie($connection, $params);
                    $resSch = $this->getMovieSchedules($connection, $params);
                    $resRev = $this->getMovieReviews($connection, $params);
                    if ($resAtr && $resSch && $resRev) {
                        $this->render();
                    }
                }
            }
        } else {
            returnResponse('500', 'Invalid HTTP REQUEST.');
        }
    }

    public function getAllAttributesOneMovie($connection, $params)
    {
        $movie = new Movie($connection);
        $movies_arr = $movie->getAllAttributes($connection, $params);
        if ($movies_arr != '500') {
            $this->movie = $movies_arr;
            return true;
        } else {
            returnResponse('500', 'Internal Server Error.');
            return false;
        }
    }

    public function getMovieSchedules($connection, $params)
    {
        $schedule = new Schedule($connection);
        $schedule_arr = $schedule->getMovieSchedules($connection, $params);
        if ($schedule_arr != '500') {
            $this->schedules = $schedule_arr;
            return true;
        } else {
            returnResponse('500', 'Internal Server Error.');
            return false;
        }
    }

    public function getMovieReviews($connection, $params)
    {
        $review = new Review($connection);
        $review_arr = $review->getMovieReviews($connection, $params);
        if ($review_arr != '500') {
            $this->reviews = $review_arr;
            return true;
        } else {
            returnResponse('500', 'Internal Server Error.');
            return false;
        }
    }

    public function render()
    {
        $html .= '<div class="container-ticket">';
        $html .=      '<div class="header">
                            <div class="click-previously">
                                <img class="movie-design" src="https://image.tmdb.org/t/p/w185/' . $this->movie['poster_path'] .'">
                            </div>
                            <div class="movie-header">
                                <div class="movie-detail title"> ' . $this->movie['title'] . '
                                </div>
                                <div class="detail-genre">';
        $x = count($this->movie['genres']);
        foreach ($this->movie['genres'] as $mg) {
        $html .= ''. $mg['name'] .'';
        if($x > 1) {
            $html .= ', ';
            $x -=1;
        }
        }
        $html .=' | '. $this->movie['runtime'] . ' mins
                                </div>
                                <div class ="detail-released-time">
                                    Released date: '. date("d-m-Y", strtotime($this->movie['release_date'])) . '
                                </div>
                                <div class="review">
                                        <img class="star-icon" src="../assets/star-icon.png">
                                        <p class="rating"><span class="rate">' . $this->movie['vote_average'] .'</span><span class="per-rate">/10</span></p>
                                </div>
                                <div class="detail-summary">
                                    <p class="spacing"> '. $this->movie['overview'] . ' </p>
                                </div>
                             </div>
                        </div>';
        $html .=    '<div class="flex-container-book container-details">';
        $html .=        '<div class="seat-selection of-schedule">
                            <div class="modal-auth schedule">
                                <p class="title-schedule">Schedules</p>
                                <table>
                                    <tr>
                                        <th>Date</th>
                                        <th class="time">Time</th>
                                        <th>Available Seats</th>
                                        <th class="link-book"></th>
                                        <th class="symbol-book"></th>
                                    </tr>';
        foreach ($this->schedules as $schedule) {
            $class = "";
            $class_icon = "fa fa-chevron-circle-right fa-2x";
            if ($schedule['seat'] == 0) {
                $class = "red";
                $content = "Not Available";
                $class_icon = "fa fa-times-circle fa-2x";
            }
            $html .= '<tr class="list-schedule">
                        <th>.' . $schedule['date'] . ' </th>
                        <th class="time"> '. $schedule['time'] . '</th>
                        <th class="seats-availability"> '. $schedule['seat'] . ' seats </th>
                        <th id="'. $schedule['id_schedule'] .'" class="link-book"><a href="bioskop.html?schedule='. $schedule['id_schedule'] . '&id='.$this->movie['id'].'"> Book Now </a></th>
                        <th class="symbol-book"><i class="'. $class_icon .'"></i></th>
                    </tr>'; // <a class="'. $class .'"> '. $content . ' " </a>
        }
        $html .=  '</table>
                </div>
            </div>';
        $html .= '<div class="booking-message detail">
                        <div class="modal-auth review-user">
                            <p class="title-schedule">Reviews</p>
                            <table>';
        foreach ($this->reviews as $review) {
            $html .= '<tr>
                        <th class="profile-pic">
                            <img class="profile-pic-child" src="http://localhost:8080/pictures/users/'. $review['username'] .'">
                        </th>
                        <th class="review-message">
                            <div class="user-name">'. $review['username'] .'</div>
                            <div class="review star">
                                    <img class="star-icon rev" src="../assets/star-icon.png">
                                    <p class="rating"><span class="rate smaller">'. $review['rating'] . '</span><span class="per-rate">/10</span></p>
                            </div>
                            <div class="user-message">
                                <p class="spacing-review"> '.
                                    $review['description'] .'
                                </p> 
                            </div>
                        </th>
                    </tr>';
        }
        $html .=  '</table>  
                    </div>
                </div>
            </div>
        </div>';
        returnResponse('200', $html);
    }
}

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

    public function fetch($connection, $params) 
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $access_token = $this->getHeaderAuth();
            $user = new User($connection);
            $review = new Review($connection);
            if ($this->validateAccessToken($user, $connection, $access_token)) {
                    $this->getAllAttributes($connection, $params, $user, $review);
            }
            else {
                    returnResponse('500', 'Invalid Params.');
                }
            
        } else {
            returnResponse('500', 'Invalid HTTP REQUEST.');
        }
    }

    
    public function getAllAttributes($connection, $params, User $user, Review $review)
    {
        $review_arr = $review->getMovieReviews($connection, $params);
        if ($review_arr != '500') {
            $this->render($review_arr);
        } else {
            returnResponse('500', 'Internal Server Error.');
        }
    }

    public function render($review_arr){
        $html = ' 
        <div class="header">
            <div class="click-previously">
                    <a href="#"><i class="fa fa-angle-left fa-3x"></i></a>
            </div>

                <!-- Detail movie -->
            <div class="movie-header">
                <div class="title-review">
                    Avengers: Endgame
                </div>
            </div>
        </div>  
        <div class="">
        <!-- Review-->
        <div class="rating">
            <span class="style-review">Add Rating</span>
            <span>';
            // on-in star sebanyak $review['rating']
     $html .=           '<fieldset class="stars">
                        <label class = "full" for="star1"></label><input type="radio" id="star1" name="rating-star" value="1" />
                        <label class="full" for="star2"></label><input type="radio" id="star2" name="rating-star" value="2" />
                        <label class = "full" for="star3"></label><input type="radio" id="star3" name="rating-star" value="3" />
                        <label class="full" for="star4"></label><input type="radio" id="star4" name="rating-star" value="4" />
                        <label class = "full" for="star5"></label><input type="radio" id="star5" name="rating-star" value="5" />
                        <label class="full" for="star6"></label><input type="radio" id="star6" name="rating-star" value="6" />
                        <label class = "full" for="star7"></label><input type="radio" id="star7" name="rating-star" value="7" />
                        <label class="full" for="star8"></label><input type="radio" id="star8" name="rating-star" value="8" />											<label class = "full" for="star9"></label><input type="radio" id="star9" name="rating-star" value="9" />
                        <label class="full" for="star10"></label><input type="radio" id="star10" name="rating-star" value="10" />
                        </fieldset>
            </span>
        </div> ';
        // Untuk document.getElementById('review').innerHTML = response.message
    $html.=
       ' <div class="review">
            <span class="style-review add">Add Review</span>
            <span class="review-form">
                <input id= "review" name = "review" class = "input-review" placeholder="Lorem ipsum"/>
            </span>
            <div class="button">
                <span>
                    <button class="button-rev" id ="submit-rev">Submit</button>
                    <button class="button-rev cancel" type="button">Cancel</button>
                    
                </span>
            </div>
        </div>
    </div>';
    returnResponse('200', $html);   
    }
}

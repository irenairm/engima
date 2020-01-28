<?php namespace Controllers;

require_once 'models/review.php';
require_once 'models/user.php';
require_once 'models/movie.php';

use Models\User;
use Models\Review;
use Models\Movie;

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
            $movie = new Movie($connection);
            if ($this->validateAccessToken($user, $connection, $access_token)) {
                $this->fetchUsername($user, $connection, $access_token);
                if ($params['id']){
                    $this->addReview($movie,$params);
                }
                else{
                    $this->getAllAttributes($connection, $params, $this->username, $review, $movie);
                }
            }
            else {
                    returnResponse('500', 'Invalid Params.');
                }
            
        } else {
            returnResponse('500', 'Invalid HTTP REQUEST.');
        }
    }

    
    public function getAllAttributes($connection, $params, $username, Review $review, Movie $movie)
    {
        $review_arr = $review->getAllMovieReviews($connection, $username, $movie);
        $this->render($review_arr,$movie);
    }

    public function render($review_arr, $movie){
        if ($review_arr == '500'){
            $html = '<div class="transaction-header">
            No reviews yet
        </div>';
        } else {
            $html = '
        <div class="transaction-header">
            Your Reviews
        </div>';

        foreach ($review_arr as $review){
            $movie_attr = $movie->getMovieAttributes($review['id_movie']);
            $html .=
                '<div class="movie-transaction">
                    <img src="https://image.tmdb.org/t/p/w185/' . $movie_attr['poster_path'] .'" class="movie-poster">
                    <div class="judul">
                        ' .$movie_attr['title'].'
                    </div>
                    <div class="review pages">
                        <img class="star-icon" src="../assets/star-icon.png">
                        <p class="rating"><span class="rate">' .$review['rating'] .'</span><span class="per-rate">/10</span></p>
                    </div>
                    <div class="detail-summary pages">
                        <p class="spacing">' .$review['description']. '</p>
                    </div>
                    <div class="button-transaction">
                        <button class="delete-button">Delete Review</button>
                        <button class="edit-button">Edit Review</button>
                        <!-- <button class="edit-button" id="add">Edit Review</button> -->
                    </div>
                <hr> 
                </div>
            ';
        }
            
        }
        returnResponse('200', $html);   
    }

    public function addReview($movie, $params){
        $movie_attr = $movie->getMovieAttributes($params['id']);
        $movietitle = $movie_attr['title'];
        $html = ' 
        <div class="header">
            <div class="click-previously">
                    <a href="#"><i class="fa fa-angle-left fa-3x"></i></a>
            </div>

                <!-- Detail movie -->
            <div class="movie-header">
                <div class="title-review">'
                    .$movietitle.
        $html .=       '</div>
            </div>
        </div>  
        <div class="">
        <!-- Review-->
        <div class="rating">
            <span class="style-review">Add Rating</span>
            <span>';
            // on-in star sebanyak $review['rating']
     $html .=           '<fieldset class="stars">
     <label class = "full" for="10"></label><input type="radio" id="10" name="rating-star" value="10" />
     <label class="full" for="9"></label><input type="radio" id="9" name="rating-star" value="9" />
     <label class = "full" for="8"></label><input type="radio" id="8" name="rating-star" value="8" />
     <label class="full" for="7"></label><input type="radio" id="7" name="rating-star" value="7" />
     <label class = "full" for="6"></label><input type="radio" id="6" name="rating-star" value="6" />
     <label class="full" for="5"></label><input type="radio" id="5" name="rating-star" value="5" />
     <label class = "full" for="4"></label><input type="radio" id="4" name="rating-star" value="4" />
     <label class="full" for="3"></label><input type="radio" id="3" name="rating-star" value="3" />											
     <label class = "full" for="2"></label><input type="radio" id="2" name="rating-star" value="2" />
     <label class="full" for="1"></label><input type="radio" id="1" name="rating-star" value="1" />
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

    public function editReview($params){
        $movie_attr = $movie->getMovieAttributes($review['id_movie']);
        $movietitle = $movie_attr['title'];
        $html = ' 
        <div class="header">
            <div class="click-previously">
                    <a href="#"><i class="fa fa-angle-left fa-3x"></i></a>
            </div>

                <!-- Detail movie -->
            <div class="movie-header">
                <div class="title-review">'
                    .$movietitle.
        $html .=       '</div>
            </div>
        </div>  
        <div class="">
        <!-- Review-->
        <div class="rating">
            <span class="style-review">Add Rating</span>
            <span>';
            // on-in star sebanyak $review['rating']
     $html .=         '<fieldset class="stars">
     <label class = "full" for="10"></label><input type="radio" id="10" name="rating-star" value="10" />
     <label class="full" for="9"></label><input type="radio" id="9" name="rating-star" value="9" />
     <label class = "full" for="8"></label><input type="radio" id="8" name="rating-star" value="8" />
     <label class="full" for="7"></label><input type="radio" id="7" name="rating-star" value="7" />
     <label class = "full" for="6"></label><input type="radio" id="6" name="rating-star" value="6" />
     <label class="full" for="5"></label><input type="radio" id="5" name="rating-star" value="5" />
     <label class = "full" for="4"></label><input type="radio" id="4" name="rating-star" value="4" />
     <label class="full" for="3"></label><input type="radio" id="3" name="rating-star" value="3" />											
     <label class = "full" for="2"></label><input type="radio" id="2" name="rating-star" value="2" />
     <label class="full" for="1"></label><input type="radio" id="1" name="rating-star" value="1" />
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

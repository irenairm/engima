<?php namespace Controllers;

include_once 'utils/response.php';
require_once 'models/movie.php';
// require_once 'models/review.php';
require_once 'models/user.php';

use Models\Movie;
use Models\User;

class HomeController
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

    public function fetch($connection, $params)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $access_token = $this->getHeaderAuth();
            $user = new User($connection);
            if ($this->validateAccessToken($user, $connection, $access_token)) {
                $this->fetchUsername($user, $connection, $access_token);
                if ($params['page']) {
                    $this->getAllMoviesWithKeyword($connection, $params);
                } else {
                    $this->getAllMovies($connection);
                }
            }
        } else {
            returnResponse('500', 'Invalid HTTP REQUEST.');
        }
    }

    public function getAllMovies($connection)
    {
        $movie = new Movie($connection);
        $movies_arr = $movie->getAllMovies($connection);
        // if ($movies_arr != '500') {
            $this->render($movies_arr);
        // } else {
        //     returnResponse('500', 'Internal Server Error.');
        // }
    }

    public function getAllMoviesWithKeyword($connection, $params)
    {
        $movie = new Movie($connection);
        $movies_arr = $movie->getAllMoviesWithKeyword($connection, $params);
        // Count rows in movies_arr:
        if (count($movies_arr) != 0) {
            $count_arr = $movie->countAllMoviesWithKeyWord($connection, $params);
            $count = $count_arr;
            $this->renderSearchFound($movies_arr, $count, $params['keyword']);
        } else {
            $this->renderSearchNotFound($params['keyword']);
        }
    }
    
    public function render($movies_arr)
    {
        $html .=        '<div class="section-welcome">
                            <h3> Hello, <span id="username">' . $this->username . '</span>! </h3>
                            <h4 class="tagline"> Now Playing </h4>
                        </div>
                        <div class="section-movies">';
        foreach ($movies_arr as $movie) {
        if (((strtotime(date("Y-m-d")) - strtotime(date($movie['release_date']))) <= 604800) && ((strtotime(date("Y-m-d")) - strtotime(date($movie['release_date']))) >= 0)) {
            $html .=    '<a href="movie_detail.html?id='. $movie['id'] . '">
                            <div class="home-page-movie">
                                <img class="movie-design" src="https://image.tmdb.org/t/p/w185/' . $movie['poster_path'] .'">
                                <p class="movie-title">'. $movie['title'] . '</p>
                                <div class="review">
                                    <img class="star-icon" src="../assets/star-icon.png">
                                    <p>' . $movie['vote_average'] . '</p>
                                </div>
                            </div>
                        </a>';
            }
        }
        $html .=        '</div>';
        returnResponse('200', $html);
    }

    public function renderSearchFound($movies_arr, $count, $keyword)
    {
        $html .= '<div class="section-title">';
        $html .=    '<h3> Showing search result for keyword "<span id="keyword">'. $keyword . '</span>" </h3>';
        $html .=    '<h4 class="tagline">'. $count . ' results available </h4>';
        $html .= '</div>';
        $html .= '<div class="section-search">';
        foreach ($movies_arr as $movie) {
            if (((strtotime(date("Y-m-d")) - strtotime(date($movie['release_date']))) <= 604800) && ((strtotime(date("Y-m-d")) - strtotime(date($movie['release_date']))) >= 0)) {
            $html .= '<div class="search-card">';
            $html .=    '<div class="image-section">
                            <img class="movie-design" src="https://image.tmdb.org/t/p/w185/' . $movie['poster_path'] .'";">
                        </div>';
            $html .=    '<div class="desc-section">';
            $html .=        '<h3>'. $movie['title'] .'</h3>';
            $html .=        '<div class="review">
                                <img class="star-icon" src="../assets/star-icon.png">
                                <p class="review-score"> 8.5</p>
                             </div>';
            $html .=        '<div class="synopsis">
                                <p>'. $movie['overview'] .'</p>
                            </div>';
            $html .=    '</div>';
            $html .=    '<span id="" class="view-detail fa-lg"> 
                            <p> View details <a href="movie_detail.html?id='. $movie['id'] . '"></p>
                            <i class="fa fa-arrow-circle-right icon-detail"></i></a>
                         </span>';
            $html .= '</div>';
        }
        }
        $html .= '</div>';
        returnSearch('200', $html, $count);
    }

    public function renderSearchNotFound($keyword)
    {
        $html .= '<div class="section-title">';
        $html .=    '<h3> Showing search result for keyword "<span id="keyword">'. $keyword . '</span>" </h3>';
        $html .=    '<h4 class="tagline"> 0 results available </h4>';
        $html .= '</div>';
        // $html .= '<div class="section-search">';
        returnResponse('200', $html);
    }
}

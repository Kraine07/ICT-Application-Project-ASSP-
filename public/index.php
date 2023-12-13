
<?php
session_start();
// session_destroy();

require_once('api-handler.php');
require_once('error-handler.php');
require_once('dbConn.php');

// initialize session variables
if($_SESSION == null){
    $_SESSION['auth-user'] = false;
    $_SESSION['screen'] = "main";
    $_SESSION['movie-search-results']=[];

}



if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['movie_search'])){
        $query = trim($_POST['movie_search']);
        $search_movie = "https://api.themoviedb.org/3/search/movie?&include_adult=false&query={$query}";

        $response = fetchData($search_movie);

        if(!isset($response->{'success'})){  // check if api call was successful
            $_SESSION['movie-search-results'] = $results = $response-> {'results'};

            $_SESSION['screen'] = "list";

            }
        else{
            showErrorMessage($response->{'status_message'},'index');
        }

    }
    elseif(isset($_POST['movie-id'])){
        $movie_id = $_POST['movie-id'];
        $movie_url = "https://api.themoviedb.org/3/movie/{$movie_id}?append_to_response=release_dates,videos&language=en-US";
        $response = fetchData($movie_url);
        $release_dates = $response->{'release_dates'}->{'results'};
        $videos = $response->{'videos'}->{'results'};


        $title = $response->{'original_title'};
        $plot = $response->{'overview'};
        $duration = $response->{'runtime'};
        $poster = 'https://image.tmdb.org/t/p/original'.$response->{'poster_path'};
        $rating='';
        $trailer ='';
        $genres = $response->{'genres'};


        // rating
        foreach($release_dates as $rd){
            if($rd->{'iso_3166_1'} == 'US'){
                $rating = $rd->{'release_dates'}[0]->{'certification'};
            }
        }

        // trailer link
        foreach($videos as $video){
            $key = $video->{'key'};

            if($video->{'type'} === 'Trailer'){
                $trailer = 'https://www.youtube.com/embed/'.$key.'?autoplay=1&mute=1&controls=1';
                break;
            }


        }
        // display details
        require_once('movie-details.php');
    }
    elseif(isset($_POST['movie-details'])){
        $sql = "INSERT INTO {$movie_table} VALUES (?,?,?,?,?,?,?)";

        if($conn){
            $result = mysqli_execute_query($conn,$sql,[
                $_SESSION['movie-id'],
                $_SESSION['title'],
                $_SESSION['plot'],
                $_SESSION['duration'],
                $_SESSION['poster'],
                $_SESSION['trailer'],
                $_SESSION['rating'] == "" ? "NR" : $_SESSION['rating']
            ]);

        }
        else{
            $_POST['movie-id'] = $_SESSION['movie-id'];
            showErrorMessage('Database connection error. Please try again or contact technical support.','add-movie');
        }
    }
}




// handle page loading
require_once('./partials/head.php');

if($_SESSION['auth-user'] == false){
    require_once('./partials/landing.php');
}
else{
    require_once('./partials/admin-panel.php');
}

require_once('./partials/footer.php');

?>
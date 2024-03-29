<?php

session_start();
$_SESSION['patron-view'] = false;

require_once('dbConn.php');
require_once('./partials/head.php');
require_once('message-display.php');

require_once('api-handler.php');
require_once("redirect.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // search api for movie title
    if(isset($_POST['search_movie'])){
        $query = preg_replace('/\s+/', '%20', $_POST['search_movie']); // replace all spaces with '%20' (api requirement)
        $search_movie = "https://api.themoviedb.org/3/search/movie?&include_adult=false&query={$query}";

        $response = fetchData($search_movie);

        if($response == null){
            showErrorMessage('No connection to movie source. Please try again or contact technical support.');
        }
        else{
            if(!isset($response->{'success'})){  // check if api call was successful
                $_SESSION['movie-search-results'] = $results = $response-> {'results'};

                $_SESSION['screen'] = "movie-result-list";
                redirect('index.php');

                }
            else{
                showErrorMessage($response->{'status_message'});
            }

        }
    }


    // add movie
    elseif(isset($_POST['movie-details'])){
        $sql = "INSERT IGNORE INTO `{$database}`.`{$movie_table}` VALUES (?,?,?,?,?,?,?,?)";
        $movie = [
            $_SESSION['movie-id'],
            $_SESSION['title'],
            $_SESSION['plot'],
            $_SESSION['release-date'],
            $_SESSION['duration'],
            $_SESSION['poster'],
            $_SESSION['trailer'],
            $_SESSION['rating'] == "" ? "NR" : $_SESSION['rating']
        ];
        // add movie to database
        if(!mysqli_execute_query($conn,$sql,$movie)){
            showErrorMessage("Error adding movie. Please try again or contact technical support.");
        }
        else if(mysqli_affected_rows($conn) < 1){
            showErrorMessage("Duplicate entry. Movie already in the database.");
        }
        else{
            // add genres to database
            foreach($_SESSION['genres'] as $genre){
                $genre_sql = "INSERT IGNORE INTO `{$database}`.`{$has_genre_table}` VALUES (?,?)";
                if(!mysqli_execute_query($conn,$genre_sql, [$_SESSION['movie-id'], $genre->{'id'}] )){
                    showErrorMessage("Error adding movie genres. Please try again or contact technical support.");
                }
            }

            // add cast
            foreach($_SESSION['cast'] as $cast){
                $cast_sql = "INSERT IGNORE INTO `{$database}`.`{$cast_table}` VALUES (?,?)";
                if(!mysqli_execute_query($conn,$cast_sql, [ $_SESSION['movie-id'], $cast ] )){
                    showErrorMessage("Error adding cast details. Please try again or contact technical support.");
                }
            }
            // return to movies main screen
            $_SESSION['screen'] = "movie";
            showSuccessMessage("Movie added successfully.");
        }
    }


    elseif(isset($_POST['edit-id'])){
        require_once('./partials/head.php');

        // handle delete and edit button clicks
        switch($_POST['edit-option']){

            case 'edit':
                $movie_id = $_POST['edit-id'];
                $movie_sql = "SELECT * FROM `{$database}`.`{$movie_table}` WHERE `movie_id` = {$movie_id} LIMIT 1";
                if($movie_result = mysqli_query($conn, $movie_sql)){
                    $_SESSION['movie_form'] = true;
                    $_SESSION['form_movie'] = mysqli_fetch_array($movie_result);
                }
                redirect('index.php');
                break;

            // handle delete
            case 'delete':
                handleDeleteMovie($_POST['edit-id'],$schedule_table,$conn,$database);
                break;
        }
    }

    // cancel button click
    elseif(isset($_POST['cancel-delete'])){
        redirect("index.php");
    }



    // DELETE MOVIE
    elseif(isset($_POST['delete-id'])){
        $delete_movie_sql = "
            DELETE FROM `{$database}`.`{$has_genre_table}` WHERE `movie` = {$_POST['delete-id']};
            DELETE FROM `{$database}`.`{$cast_table}` WHERE `movie` = {$_POST['delete-id']};
            DELETE FROM `{$database}`.`{$movie_table}` WHERE `movie_id` = {$_POST['delete-id']};
        ";

        if(!mysqli_multi_query($conn,$delete_movie_sql)){
            showErrorMessage("Error deleting movie. Please try again or contact technical support.");
        }
        else{
            while(mysqli_next_result($conn));
            mysqli_close($conn);
            showSuccessMessage("Movie deleted successfully.");
        }

    }



    // EDIT MOVIE
    elseif(isset($_POST['form-movie-id'])){
        if($_POST['genres'] == null){
            showErrorMessage("Please select at least 1 genre.");
        }
        else{
            $movie_id = trim($_POST['form-movie-id']);
            $released_date = date_format(date_create(trim($_POST['release'])),'U');
            $cast = [
                trim($_POST['cast1']),
                isset($_POST['cast2'])? trim($_POST['cast2']): null,
                isset($_POST['cast3'])? trim($_POST['cast3']): null,
            ];
            $movie = [
                trim($_POST['title']),
                trim($_POST['plot']),
                trim($_POST['duration']),
                trim($_POST['poster']),
                trim($_POST['trailer']),
                trim($_POST['rating']),
                $released_date,
                $movie_id
            ];
            $sql = "UPDATE `{$database}`.`{$movie_table}` SET `movie_title`= ?, `movie_plot`= ?, `movie_duration` = ?, `movie_poster` = ?, `movie_trailer` = ?, `movie_rating` = ?, `movie_release_date` = ?  WHERE `movie_id` = ? ";
            if(mysqli_execute_query($conn, $sql, $movie)){

                // delete old genres
                $delete_genres = "DELETE FROM `{$database}`.`{$has_genre_table}` WHERE `movie` = {$movie_id}";
                if(mysqli_query($conn, $delete_genres)){

                    // add genres from form
                    foreach($_POST['genres'] as $genre){
                        $genre_sql = "INSERT INTO `{$database}`.`{$has_genre_table}` VALUES (?,?)";
                        if(!mysqli_execute_query($conn,$genre_sql, [$movie_id, $genre])){
                            showErrorMessage("Error updating movie genres. Please try again or contact technical support.");
                        }
                    }
                }

                // delete old cast
                $delete_cast = "DELETE FROM `{$database}`.`{$cast_table}` WHERE `movie` = {$movie_id}";
                if(mysqli_query($conn, $delete_cast)){

                    // add genres from form
                    foreach($cast as $c){
                        $cast_sql = "INSERT INTO `{$database}`.`{$cast_table}` VALUES (?,?)";
                        if(!mysqli_execute_query($conn,$cast_sql, [$movie_id, $c])){
                            showErrorMessage("Error updating movie cast. Please try again or contact technical support.");
                        }
                    }
                }

                mysqli_close($conn);

                // return to movies main screen
                $_SESSION['screen'] = "movie";
                showSuccessMessage("Movie updated successfully.");
            }
            else{
                showErrorMessage('Error updating movie. Please try again or contact technical support.');
            }
        }
    }
}



function handleDeleteMovie($movie_id,$schedule_table,$conn,$database){

    // sql to search for selected movie in the schedule table
    $sql = "SELECT * FROM `{$database}`.`{$schedule_table}` WHERE `movie` = {$movie_id}";
    $result = mysqli_query($conn,$sql);

    // delete selected movie
    if(mysqli_num_rows($result) > 0){
        showErrorMessage("Cannot delete movie because it is linked to a schedule. Please resolve scheduling issue then try again.");
        mysqli_free_result($result);
        mysqli_close($conn);
    }
    else{
        // display confirmation popup
        echo '
        <div class="absolute top-0 left-0 h-screen w-screen bg-app-modal">
            <form action="manage-movie.php" method="post" id="cancel">
                <input type="text" name="cancel-delete" value="0" hidden>
            </form>
            <form action="manage-movie.php" method="post" id="delete-form" class="  bg-app-tertiary text-gray-200 absolute top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 pb-4  px-6 w-[340px] text-sm">
                <p class="bg-app-blue text-app-orange text-lg font-light -mx-6 px-6 py-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 inline mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                WARNING
                </p>
                <input name="delete-id" type="text" value="'.$_POST['edit-id'].'" hidden>
                <p class="my-8"> Are you sure you want to remove <span class="italic text-app-orange">'.$_POST['del-movie-title'].'</span> from the records? This action is irreversible. </p>
                <div class="flex justify-around items-center">
                    <button form="delete-form" class="text-white bg-red-600 py-1 px-8 rounded-md">YES</button>
                    <button form="cancel" class="text-white bg-green-600  py-1 px-8 rounded-md">NO</button>
                </div>
            </form>
        </div>
        ';
        mysqli_free_result($result);
    }
}


require_once('./partials/footer.php');
?>
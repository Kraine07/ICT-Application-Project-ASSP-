<?php

session_start();
$_SESSION['patron-view'] = false;

require_once('dbConn.php');
require_once('./partials/head.php');
require_once('message-display.php');

require_once('api-handler.php');
require_once("redirect.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // get all movies from api with full or partial match to title entered
    if(isset($_POST['search_movie'])){
        $query = preg_replace('/\s+/', '%20', $_POST['search_movie']); // replace all spaces with '%20' (api requirement)
        $search_movie = "https://api.themoviedb.org/3/search/movie?&include_adult=false&query={$query}";

        $response = fetchData($search_movie);

        if($response == null){
            showErrorMessage('No connection to source. Please try again or contact technical support.');
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
        $sql = "INSERT INTO `{$database}`.`{$movie_table}` VALUES (?,?,?,?,?,?,?)";
        $movie = [
            $_SESSION['movie-id'],
            $_SESSION['title'],
            $_SESSION['plot'],
            $_SESSION['duration'],
            $_SESSION['poster'],
            $_SESSION['trailer'],
            $_SESSION['rating'] == "" ? "NR" : $_SESSION['rating']
        ];
        // add movie to database
        if(!mysqli_execute_query($conn,$sql,$movie)){
            showErrorMessage("Error adding movie. Please try again or contact technical support.");
        }
        else{
            // add genres to database
            foreach($_SESSION['genres'] as $genre){
                $genre_sql = "INSERT INTO `{$database}`.`{$has_genre_table}` VALUES (?,?)";
                if(!mysqli_execute_query($conn,$genre_sql,[$_SESSION['movie-id'], $genre->{'id'}])){
                    showErrorMessage("Error adding movie genres. Please try again or contact technical support.");
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

            // TODO handle edit
            case 'edit':
                echo "<p class='text-2xl w-screen text-center'>Edit movie to be done here</p>";
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

    // delete all genres associated with the movie
    elseif(isset($_POST['delete-id'])){
        $delete_sql = "DELETE FROM `{$database}`.`{$has_genre_table}` WHERE `movie` = {$_POST['delete-id']};
        DELETE FROM `{$database}`.`{$movie_table}` WHERE `movie_id` = {$_POST['delete-id']};";
        mysqli_multi_query($conn,$delete_sql);
        while(mysqli_next_result($conn));

        showSuccessMessage("Movie deleted.");

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
    }
    else{
        // display confirmation popup
        echo '
            <form action="edit-movie.php" method="post" id="cancel">
                <input type="text" name="cancel-delete" value="0" hidden>
            </form>
            <form action="edit-movie.php" method="post" id="delete-form" class=" mt-4 pb-4 mx-auto px-6 w-[340px] shadow-custom text-sm">
                <p class="bg-red-600 text-white text-lg font-light -mx-6 px-6 py-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 inline mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                WARNING
                </p>
                <input name="delete-id" type="text" value="'.$_POST['edit-id'].'" hidden>
                <p class="my-8"> Are you sure you want to delete movie? This action is irreversible. </p>
                <div class="flex justify-around items-center">
                    <button form="delete-form" class="text-white bg-red-600 py-1 px-8 rounded-full">YES</button>
                    <button form="cancel" class="text-white bg-green-600  py-1 px-8 rounded-full">NO</button>
                </div>
            </form>
        ';
        mysqli_free_result($result);
    }
}


require_once('./partials/footer.php');
?>
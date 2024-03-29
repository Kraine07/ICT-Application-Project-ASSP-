<?php
session_start();
require_once('redirect.php');
require_once('dbConn.php');


    if($_SERVER['REQUEST_METHOD'] == "POST"){

        // handle select cinema button clicks
        if(isset($_POST['screen-id'])){
            $_SESSION['screen-id'] = $_POST['screen-id'];
            $_SESSION['page'] = "main.php#on-today";
            redirect($_SESSION['page']);
        }

        // view movie details button click
        elseif(isset($_POST['movie-id'])){

            $sql = "SELECT * FROM `{$database}`.`{$movie_table}`, `{$database}`.`{$has_genre_table}`, `{$database}`.`{$genre_table}`,`{$database}`.`{$cast_table}` WHERE `has_genre`.`movie` = `movie_id` AND `genre` = `genre_id` AND `movie_id` = {$_POST['movie-id']} AND `movie_id` = `cast`.`movie`";

            if($result = mysqli_query($conn, $sql)){
                $genres = [];
                $cast = [];

                while($row = mysqli_fetch_assoc($result)){
                    $_SESSION['patron-movie'] = $row;
                    array_push($genres, $row['genre_name']);
                    array_push($cast, $row['cast_name']);
                }
                $genres = array_unique($genres); // remove duplicates
                $cast = array_unique($cast); // remove duplicates
                $_SESSION['patron-genres'] = $genres;
                $_SESSION['patron-cast'] = $cast;
                $_SESSION['movie-info'] = true;
                redirect($_SESSION['page']);
            }
        }

        // watch trailer
        elseif(isset($_POST['trailer-link'])){
            $_SESSION['trailer-link'] = $_POST['trailer-link'];
            $_SESSION['watch-trailer'] = true;
            redirect($_SESSION['page']);
        }

        // close movie info modal
        elseif(isset($_POST['close-movie-info'])){
            $_SESSION['movie-info'] = false;
            redirect($_SESSION['page']);
        }

    }


?>
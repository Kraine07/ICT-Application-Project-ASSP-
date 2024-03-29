<?php
session_start();


// initialize session variables
if(!isset($_SESSION['screen-id'])){
    $_SESSION['screen-id'] = "1";
}
if(!isset($_SESSION['watch-trailer'])){
    $_SESSION['watch-trailer'] = false;
}
if(!isset($_SESSION['movie-info'])){
    $_SESSION['movie-info'] = false;
}

$_SESSION['patron-view'] = true;
$_SESSION['page'] = "main.php";




require_once('dbConn.php');
require_once('redirect.php');



// set default timezone
date_default_timezone_set('America/Jamaica');

// store today's date
$today = strtotime(date("F j, Y"));


// sql to get movie data to display in slideshow
$schedule_info_sql = "SELECT * FROM `{$database}`.`{$schedule_table}`, `{$database}`.`{$movie_table}`, `{$database}`.`{$screen_table}` WHERE `movie_id` = `movie` and `screen_id` = `screen` AND `start` >= $today ORDER BY RAND() LIMIT 3";

// sql to get screen data
$screen_sql = "SELECT * FROM `{$database}`.{$screen_table}";

// sql to get movies scheduled for toady
$today_sql = "SELECT `movie_poster`, `movie_id`, `movie_title`, `movie_plot`, `movie_duration`,`movie_trailer`, `start`, `end`, `movie`, `screen` FROM `{$database}`.`{$schedule_table}`, `{$database}`.`{$movie_table}`, `{$database}`.`{$screen_table}` WHERE `movie` = `movie_id` AND `screen` = {$_SESSION['screen-id']} AND `screen` = `screen_id` AND FROM_UNIXTIME(`start`,'%Y-%m-%d') = CURDATE() ORDER BY `start`";

// sql to get unscheduled movies
$coming_soon_sql = "SELECT * FROM `{$database}`.`{$movie_table}` WHERE NOT EXISTS (SELECT * FROM `{$database}`.`{$schedule_table}` WHERE `movie` = `movie_id`) ORDER BY `movie_title`";







require_once('./partials/head.php');

?>


<!-- Main page content -->
<div class=" h-full w-full bg-blue-950 overflow-y-auto overflow-x-hidden ">


    <?php
        require_once('./partials/movie-info-modal.php');
        require_once('./partials/watch-trailer.php');
        require_once('./partials/login-form-modal.php');
    ?>



    <!-- slides -->
    <div class="hidden sm:block slideshow relative h-full bg-[url('https://images.pexels.com/photos/7991486/pexels-photo-7991486.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2')] bg-cover bg-center " id="slideshow">
        <div class="">
            <?php
            if($result = mysqli_query($conn, $schedule_info_sql)){

                while($row = mysqli_fetch_assoc($result)){
                    echo '

                        <div  class="slides h-[340px] w-[560px] bg-app-tertiary left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2  absolute rounded-lg animate-fade-in ">

                            <span class="absolute p-1 w-4/5 -translate-y-1/2 left-1/2 -translate-x-1/2 text-xl text-app-secondary text-center font-bold bg-gray-200 rounded-md">Now Showing</span>

                            <div class="flex items-center justify-between h-full w-full p-6 pt-10">
                                <div class="w-1/3 h-full">
                                    <img class="object-cover h-full rounded-lg" src="'.$row['movie_poster'].' " alt="movie-poster">
                                </div>

                                <div class="flex flex-col justify-between w-3/5 h-full px-6 text-gray-200">

                                    <div class="h-2/3 flex flex-col justify-start">
                                        <h1 class="text-2xl max-h-1/3 leading-6 mb-2 ">'.$row['movie_title'].'</h1>
                                        <p class="text-clip overflow-hidden text-xs h-2/3   py-2">'.$row['movie_plot'].'</p>
                                    </div>

                                    <div class="h-1/4 flex flex-col justify-end">
                                        <div class="flex justify-between items-end w-full my-2">
                                            <div class="w-2/3">
                                                <span class=" text-2xl text-app-orange italic font-light pr-2">'.date("M d",$row['start']).'</span>
                                                <span class=" text-xl text-app-orange italic ">'.date("g:i A",$row['start']).'</span>
                                                <span class="block text-3xl ">'.$row['screen_name'].'</span>
                                            </div>
                                            <div class="flex justify-end items-end w-1/3  ">
                                                <p class="text-2xl text-end">'.$row['movie_rating'].'</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    ';
                }
            }


            ?>

        </div>


        <!-- Left scroll button -->
        <span class="absolute left-4 md:left-[10%] lg:left-[20%]  top-1/2 text-2xl p-1 text-center  cursor-pointer text-black bg-[#ffffff77] hover:bg-white font-semibold rounded-full w-6 h-6" onclick="nextSlide(-1)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-full h-full ">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>

        </span>


        <!-- Right scroll button -->
        <span class="absolute right-4  md:right-[10%] lg:right-[20%] p-1 top-1/2 text-2xl  text-center  cursor-pointer  text-black bg-[#ffffff77] hover:bg-white font-semibold rounded-full h-6 w-6" onclick="nextSlide(1)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-full h-full">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>

        </span>

        <!-- Indicators -->
        <div class="circles z-30 text-center absolute bottom-[6%] right-1/2 translate-x-1/2">
            <span class="dot bg-white h-4 aspect-square rounded-full relative inline-block cursor-pointer" onclick="currentSlide(1)"></span>
            <span class="dot bg-white h-4 aspect-square rounded-full relative inline-block cursor-pointer" onclick="currentSlide(2)"></span>
            <span class="dot bg-white h-4 aspect-square rounded-full relative inline-block cursor-pointer" onclick="currentSlide(3)"></span>
        </div>

    </div>



    <!-- On today -->

    <div class="min-h-full h-auto w-full bg-app-secondary py-8 " id="on-today" >
        <div class="lg:flex lg:items-center mx-8 w-full h-auto">

            <p class="text-2xl md:text-4xl font-light w-full md:w-1/3 text-gray-200 uppercase">On Today</p>

            <div class="  w-auto    lg:bg-app-tertiary rounded-md">
                <?php

                // select screen buttons
                if($result = mysqli_query($conn, $screen_sql)){
                    while($screen = mysqli_fetch_assoc($result)){

                        if($screen['screen_id'] == $_SESSION['screen-id']){
                            $css = "bg-app-orange text-white animate-fade-in";
                        }
                        else{
                            $css = "bg-app-tertiary text-gray-200";
                        }


                        echo '
                            <form action="process-main.php" method="post" class="w-full p-0 inline">
                                <input type="text" name="screen-id" value="'.$screen['screen_id'].'" hidden>
                                <button class="'.$css.' text-black text-xs md:text-md font-semibold py-2  md:px-10 w-[90px] md:w-[160px] truncate   focus:outline-none  uppercase rounded-md">'.$screen['screen_name'].'</button>
                            </form>
                            ';

                    }
                }

                ?>
            </div>
        </div>



        <!-- today's movie cards -->

        <div class="relative h-auto w-full  grid gap-y-4 grid-cols-2  lg:grid-cols-4 lg:gap-32 text-black p-8">
            <?php
                if($result = mysqli_query($conn, $today_sql)){

                    if(mysqli_num_rows($result)>0){
                        while($row = mysqli_fetch_assoc($result)){
                            require("./partials/movie-card.php");
                        }
                    }
                    else{
                        echo '<div><span class="text-xl text-gray-200 font-light italic col-span-2 lg:col-span-4   absolute left-1/2 -translate-x-1/2">We apologize, but currently, there are no schedules for this screen. Please try another screen.</span></div>';
                    }
                }
            ?>

        </div>

    </div>




    <!-- Coming soon -->
        <div class="min-h-full w-full bg-app-tertiary py-8" id="coming-soon">
            <div class="flex  px-8 w-full mb-10">
                <span class="text-2xl md:text-4xl  text-white font-light uppercase ">Coming Soon</span>
            </div>


            <div class="min-h-full w-full px-20 grid grid-cols-2 gap-6 md:grid-cols-6  text-black relative">
                <?php
                    if($result1 = mysqli_query($conn, $coming_soon_sql)){
                        if(mysqli_num_rows($result1)< 1){
                            echo '
                            <div><span class="absolute left-1/2 -translate-x-1/2 text-gray-200 text-xl font-light italic col-span-2 md:col-span-6 align-middle self-center">We apologize, but currently, there are no upcoming movies available. Kindly, check back later to see our exciting line up.</span></div>
                            ';
                        }
                        while($row = mysqli_fetch_assoc($result1)){
                            require('./partials/movie-card.php');
                        }
                    }

                ?>

            </div>
        </div>




<?php


require_once('./partials/footer.php');

?>
</div>




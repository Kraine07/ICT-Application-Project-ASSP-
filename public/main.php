<?php
session_start();
$_SESSION['patron-view'] = true;
$_SESSION['page'] = "main.php#on-today";


require_once('dbConn.php');

date_default_timezone_set('America/Jamaica');





// get data to display in slideshow
$schedule_info_sql = "SELECT * FROM `{$database}`.`{$schedule_table}`, `{$database}`.`{$movie_table}`, `{$database}`.`{$screen_table}` WHERE `movie_id` = `movie` and `screen_id` = `screen` ORDER BY RAND() LIMIT 3";

//get screen data
$screen_sql = "SELECT * FROM `{$database}`.{$screen_table}";

// get movies scheduled for toady
$today_sql = "SELECT `movie_poster`, `movie_id`, `movie_title`, `movie_plot`, `movie_duration`,`movie_trailer`, `start`, `end`, `movie`, `screen` FROM `{$database}`.`{$schedule_table}`, `{$database}`.`{$movie_table}`, `{$database}`.`{$screen_table}` WHERE `movie` = `movie_id` AND `screen` = {$_SESSION['screen-id']} AND `screen` = `screen_id` AND FROM_UNIXTIME(`start`,'%Y-%m-%d') = CURDATE() ORDER BY `start`";

// get unscheduled movies
$coming_soon_sql = "SELECT * FROM `{$database}`.`{$movie_table}` WHERE NOT EXISTS (SELECT * FROM `{$database}`.`{$schedule_table}` WHERE `movie` = `movie_id`) ORDER BY `movie_title`";






require_once('./partials/head.php');

?>



<div class=" h-full w-full bg-blue-950 overflow-y-auto  ">


    <!-- movie info modal -->
    <?php
        require_once('./partials/navbar.php');
        require_once('./partials/movie-info-modal.php');
        require_once('./partials/watch-trailer.php');
        require_once('./partials/login-form-modal.php');
    ?>



    <!-- slides -->
    <div class="slideshow relative h-5/6 bg-[url('https://images.pexels.com/photos/7991486/pexels-photo-7991486.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2')] bg-contain ">
        <!-- <h2 class="text-white text-2xl text-center text-light mt-4">Now showing</h2> -->
        <div class="">
            <?php
            if($result = mysqli_query($conn, $schedule_info_sql)){

                while($row = mysqli_fetch_assoc($result)){
                    echo '

                        <div  class="slides h-[360px] w-[700px] bg-white left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2  absolute rounded-lg  ">
                            <div class="flex items-center justify-between h-full w-full p-6">
                                <div class="w-1/3 h-full">
                                    <img class="object-cover h-full rounded-lg" src="'.$row['movie_poster'].' " alt="movie-poster">
                                </div>
                                <div class="flex flex-col justify-between w-3/5 h-full px-6 text-black">
                                    <div class="h-2/3 flex flex-col justify-start">
                                        <h1 class="text-2xl h-1/3 leading-6 justify-self-center ">'.$row['movie_title'].'</h1>
                                        <p class="text-xs h-2/3 overflow-clip  py-2">'.$row['movie_plot'].'</p>
                                    </div>
                                    <div class="h-1/4 flex flex-col justify-end">
                                        <div class="flex justify-between items-end w-full my-2">
                                            <div class="w-2/3">
                                                <span class="block text-2xl font-light">'.$row['screen_name'].'</span>
                                                <span class="block text-4xl text-red-700 ">'.date("g:i A",$row['start']).'</span>
                                            </div>
                                            <div class="flex justify-center items-end w-1/3  ">
                                                <p class="text-2xl">'.$row['movie_rating'].'</p>
                                            </div>
                                        </div>
                                        <form action="process-main.php" method="post">
                                            <input type="text" name="trailer-link" value="'.$row['movie_trailer'].'" hidden>
                                            <button class="bg-blue-950 text-white w-full text-2xl py-1 rounded-md flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-8 aspect-square inline">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 010 .656l-5.603 3.113a.375.375 0 01-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112z" />
                                                </svg>
                                                Watch Trailer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                    ';
                }
            }


            ?>

        </div>

        <span class="absolute left-[17%]  top-1/2 text-2xl p-1 text-center  cursor-pointer text-black bg-[#ffffff77] font-semibold rounded-full" onclick="nextSlide(-1)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8 ">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>

        </span>

        <span class="absolute right-[17%] p-1 top-1/2 text-2xl  text-center  cursor-pointer  text-black bg-[#ffffff77] font-semibold rounded-full " onclick="nextSlide(1)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>

        </span>

        <div class="circles z-30 text-center absolute bottom-[6%] right-1/2 translate-x-1/2">
            <span class="dot bg-white h-4 aspect-square rounded-full relative inline-block cursor-pointer" onclick="currentSlide(1)"></span>
            <span class="dot bg-white h-4 aspect-square rounded-full relative inline-block cursor-pointer" onclick="currentSlide(2)"></span>
            <span class="dot bg-white h-4 aspect-square rounded-full relative inline-block cursor-pointer" onclick="currentSlide(3)"></span>
        </div>

    </div>


    <!-- On today -->

    <div class="h-full w-full bg-slate-300 pt-10" id="on-today"  name="on-today">
        <div class="flex  p-8 w-full">
            <span class="text-4xl w-1/4 text-black mb-6  ">On Today</span>
            <div class="flex justify-start w-1/2  ">
                <?php

                // select screen buttons
                if($result = mysqli_query($conn, $screen_sql)){
                    while($screen = mysqli_fetch_assoc($result)){

                        if($screen['screen_id'] == $_SESSION['screen-id']){
                            $bg = "bg-blue-950";
                            $text_col = "text-white";
                        }
                        else{
                            $bg= "bg-white";
                            $text_col = "text-blue-950";
                        }


                        // screen buttons
                        echo '
                            <form action="process-main.php" method="post" class="w-full">
                                <input type="text" name="screen-id" value="'.$screen['screen_id'].'" hidden>
                                <button class="'.$bg.' '.$text_col.' text-black text-md py-1 px-10 w-auto mx-4  focus:outline-none border border-blue-950 capitalize rounded-md">'.$screen['screen_name'].'</button>
                            </form>
                            ';

                    }
                }

                ?>
            </div>
        </div>



        <!-- today's movie cards -->

        <div class=" h-auto w-full px-20  grid grid-cols-4 gap-12 text-black ">
            <?php
                if($result = mysqli_query($conn, $today_sql)){

                    while($row = mysqli_fetch_assoc($result)){
                        echo "<div class='h-auto w-auto shadow-custom'>";
                        require('./partials/movie-card.php');
                        echo "</div>";
                    }
                }
            ?>

        </div>

    </div>




    <!-- Coming soon -->
    <!-- <div class="h-auto w-full bg-[url('https://images.pexels.com/photos/7234227/pexels-photo-7234227.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2')]"> -->
        <div class="h-auto w-full bg-blue-950 pb-8">
            <div class="flex  p-8 w-full">
                <span class="text-4xl w-1/4 text-white mb-4  ">Coming Soon</span>
            </div>


            <div class=" h-auto w-full px-20 grid grid-cols-6 gap-6 text-black ">
                <?php
                    if($result1 = mysqli_query($conn, $coming_soon_sql)){
                        while($row = mysqli_fetch_assoc($result1)){
                            require('./partials/movie-card.php');
                        }
                    }

                ?>

            </div>
        </div>
    <!-- </div> -->
    

<?php

require_once('./partials/footer.php');

?>
</div>




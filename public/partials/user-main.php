<?php
require_once('dbConn.php');
$db_users_sql = "SELECT `user_id`,`first_name`,`last_name`,`email`,`role` FROM `{$database}`.`{$user_table}`"; // TODO JOIN
$result = mysqli_query($conn, $db_users_sql);

require_once('user-form.php');
?>



<div class=" flex flex-col items-center justify-center w-full px-4">
    <!-- Heading -->
    <p class="text-gray-200 text-6xl font-light py-10   text-heading">User  Management</p>

    <!-- Action buttons -->
    <div class="flex justify-between w-full my-6  action">

        <!-- New User -->
        <div class="w-full  flex justify-start ">
            <button class="  bg-app-blue text-app-orange px-4 py-2   rounded   flex items-center hover:bg-blue-950"  id="new-user-btn">
                <!-- <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 inline">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg> -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 inline">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>

                <span class="hidden lg:inline pl-2">Create User</span>
            </button>
        </div>

        <!-- Search -->
        <!-- <div class="flex    search-container">
            <select class= "bg-blue-950 text-white  p-1 px-2">
                <option selected hidden>SEARCH BY</option>
                <option class="bg-white text-blue-950" value="title">TITLE</option>
            </select>
            <input type="search" class="text-lg p-1 border border-blue-950">
            <button class= "bg-blue-950 p-1 px-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </button>
        </div> -->

    </div>

    <!-- User Table -->
    <table class="table-fixed w-full animate-fade-in">
        <thead class="bg-app-blue sticky top-0 z-0">
            <tr >
                <th class="py-2  w-1/4" >FIRST NAME</th>
                <th class=" w-1/4" >LAST NAME</th>
                <th class=" w-1/4 ">EMAIL</th>
                <th class="">ROLE</th>

                <th class=" ">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 inline">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>

                </th>
            </tr>
        </thead>
        <tbody class="">
            <?php
            while($row = mysqli_fetch_assoc($result)){

                // css to hide delete button of logged in user to prevent deletion
                $css = $row['user_id'] == $_SESSION['auth-user']['user_id'] ? "hidden":"";

                // display edit and delete buttons
                echo '
                    <tr class="even:bg-app-secondary capitalize">
                        <td class=" py-2">'.$row['first_name'].'</td>
                        <td class="">'.$row['last_name'].'</td>
                        <td class="lowercase">'.$row['email'].'</td>
                        <td class=" capitalize">'.$row['role'].'</td>
                        <td class="  ">
                            <div>
                                <form action="manage-user.php" method="post" class="inline">
                                    <input name="edit-id" type="text" value="'.$row['user_id'].'" hidden>
                                    <input name="edit-first-name" type="text" value="'.$row['first_name'].'" hidden>
                                    <input name="edit-last-name" type="text" value="'.$row['last_name'].'" hidden>
                                    <input name="edit-email" type="text" value="'.$row['email'].'" hidden>
                                    <input name="edit-role" type="text" value="'.$row['role'].'" hidden>
                                    <input name="edit-option" type="text" value="edit" hidden>
                                    <button class="text-green-600  hover:scale-150 duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" data-slot="icon" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                            </button>
                                </form>
                                <form action="manage-user.php" method="post" class="inline '.$css.'">
                                    <input name="edit-id" type="text" value="'.$row['user_id'].'" hidden>
                                    <input name="del-first-name" type="text" value="'.$row['first_name'].'" hidden>
                                    <input name="del-last-name" type="text" value="'.$row['last_name'].'" hidden>
                                    <input name="del-email" type="text" value="'.$row['email'].'" hidden>
                                    <input name="del-role" type="text" value="'.$row['role'].'" hidden>

                                    <input name="edit-option" type="text" value="delete" hidden>
                                    <button class="text-red-600  hover:scale-150 duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" data-slot="icon" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                ';
            }
            ?>
        </tbody>
    </table>
</div>
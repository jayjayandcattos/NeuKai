<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NEUKAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/navbarScroll.js" defer></script>
    <script src="js/slideAnimation.js" defer></script>
    <script src="js/loading.js" defer></script>
    <script src="js/mobilenav.js" defer></script>
    <link rel="stylesheet" href="css/index.css">
    <link rel="icon" href="images/TempIco.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: black;
            color: white;
            font-family: 'Poppins', sans-serif;
        }

        main {
            flex: 1;
        }

        footer {
            flex-shrink: 0;
        }

        .scrollbar-hidden::-webkit-scrollbar {
            display: none;
        }
    </style>

<body class="relative min-h-screen bg-black text-white font-poppins">

    <!-- Navbar -->
    <?php include 'section/desktopNavbar.php'; ?>

    <!-- Mobile Menu -->
    <?php include 'section/mobilenavbar.php'; ?>

    <div id="loading-overlay"
        class="fixed inset-0 bg-black flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
        <img src="images/Neukai Logo.svg" alt="Loading" class="loading-logo w-50 h-50" />
    </div>

    <div class="flex items-center justify-center min-h-screen">
        <div class="container mx-auto p-4 text-center">

            <h1 class="text-[#FBB901] font-bold uppercase text-3xl sm:text-4xl md:text-5xl mb-4 mt-0 font-['Rubik_Mono_One']">
                Welcome to NEUKAI
            </h1>


            <div class="mb-4">
                <img src="images/Neukai Logo.svg" alt="NEUKAI Logo" width="640" height="170" class="mx-auto mb-12">
            </div>


            <p class="font-poppins text-base md:text-lg text-white mb-12">
                Connect with donors and charities through NEUKAI! <br>
                <span class="text-[#FBB901]">SIGN UP NOW!</span>
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-32 mb-6">
                <button class="donor-btn shadow-lg shadow-[#FF5722]/70 font-poppins font-bold w-full sm:w-[291px] py-3 bg-[#FF5722] text-white rounded-bg">
                    <a href="donor/d-register.php">Become a Donor</a>
                </button>

                <span class="font-poppins font-bold text-white">OR</span>

                <button class="charity-btn shadow-lg shadow-[#0D0DAF]/70 font-poppins font-bold w-full sm:w-[291px] py-3 bg-[#0D0DAF] text-white rounded-bg">
                <a href="charity/c-signup.php">Register as Organization</a>
                </button>
            </div>


            <p class="font-poppins text-base text-gray-500 mt-12">
                Already have an account? <a href="login.php" class="text-[#FBB901] font-bold cursor-pointer">Login</a>
            </p>
        </div>
    </div>
   
  
    <!-- Parallax Background -->
    <?php include 'section/parallaxbg.php'; ?>
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
    <script src="js/indexAos.js" defer></script>
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
    </style>

</head>

<body class="relative min-h-screen bg-black text-white font-poppins">

    <div id="loading-overlay"
        class="fixed inset-0 bg-black flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
        <img src="images/Neukai Logo.svg" alt="Loading" class="loading-logo w-50 h-50" />
    </div>

    <!-- Navbar -->
    <?php include 'section/LoggedInDonorNav.php'; ?>

    <!-- Mobile Menu -->
    <?php include 'section/LoggedInDonorNavMobile.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow">
        <!-- Logo Section -->
        <section id="home" class="flex flex-col items-center justify-center text-center pt-36 md:pt-44 px-4">
            <img src="images/NEUKAI LOGO.svg" alt="NEUKAI Logo" class="w-[320px] md:w-[640.38px] h-auto aos fade-in-up" data-aos-delay="300" />
            <h1 class="text-3xl md:text-5xl font-['Rubik_Mono_One'] text-[#FBB901] mt-4 tracking-[2px] aos fade-in-up" data-aos-delay="400"
                style="-webkit-text-stroke:1px black;">NEW + UKAY</h1>
            <p class="text-sm md:text-base text-gray-300 mt-3 max-w-xl aos fade-in-up" data-aos-delay="700">
                Sustainable fashion isn’t just a trend—it’s a movement. Pass it forward and make a difference, one outfit at a time!
            </p><br>
        </section>

        <!-- ADD WELCOME USER FETCH -->

        <!-- Main Section -->
        <div class="flex justify-center px-4">
            <div class="w-full max-w-[1300px] h-auto min-h-[600px] md:h-[2100px] bg-white rounded-3xl overflow-hidden">
                <!-- Left -->
                <div class="flex flex-col md:flex-row items-center justify-between px-4 md:px-24 py-10 md:py-20">
                    <!-- Left Section -->
                    <div class="w-full md:w-1/2 aos fade-in-left" data-aos-delay="300">
                        <h1 class="text-[#FBB901] font-bold uppercase text-3xl sm:text-4xl md:text-5xl mb-4 mt-0 font-['Rubik_Mono_One']">
                            Where fashion meets compassion!
                        </h1>
                        <p class="font-poppins text-gray-700 text-sm sm:text-base md:text-lg mb-6 max-w-full md:max-w-[605px]">
                            Welcome to NEUKAI! Our platform connects donors with charities, giving pre-loved clothing a second life where it's needed most. Sign up today and be part of a community that cares!
                        </p>
                        <div class="flex gap-4 mt-6 md:justify-start justify-center">
                            <a href="donor/d-donate.php" class="w-full md:w-auto">
                                <button class="donor-btn shadow-lg shadow-[#FF5722]/70 font-poppins font-bold w-full md:w-[500px]">
                                    Feeling generous today? Donate Now!
                                </button>
                            </a>
                        </div>
                    </div>

                    <!-- Right Section (Ellipse) -->
                    <div class="w-full md:w-4/6 flex justify-center md:justify-end relative mt-5 md:mt-0 aos fade-in-right" data-aos-delay="400">
                        <div class="w-[150%] h-auto md:w-[850px] flex items-center justify-center">
                            <img src="images/ellipse.png" alt="Ellipse" class="w-full md:w-[850px] rounded-full object-cover md:transform md:translate-x-20 md:-mt-60">
                        </div>
                    </div>
                </div>

                <!-- Sliding Text -->
                <div id="sliding-container" class="overflow-hidden whitespace-nowrap mt-1 aos fade-in" data-aos-delay="300">
                    <div id="sliding-text" class="flex items-center gap-6">
                        <p class="text-xl sm:text-2xl md:text-4xl font-['Rubik_Mono_One'] uppercase outlined-text">Pass
                            the Warmth, Donate Today!</p>
                        <img src="images/TempIco.png" alt="Vector Icon" class="w-16 md:w-22 h-auto">
                        <p class="text-xl sm:text-2xl md:text-4xl font-['Rubik_Mono_One'] uppercase outlined-text">Pass
                            the Warmth, Donate Today!</p>
                        <img src="images/TempIco.png" alt="Vector Icon" class="w-16 md:w-22 h-auto">
                        <p class="text-xl sm:text-2xl md:text-4xl font-['Rubik_Mono_One'] uppercase outlined-text">Pass
                            the Warmth, Donate Today!</p>
                        <img src="images/TempIco.png" alt="Vector Icon" class="w-16 md:w-22 h-auto">
                        <p class="text-xl sm:text-2xl md:text-4xl font-['Rubik_Mono_One'] uppercase outlined-text">Pass
                            the Warmth, Donate Today!</p>
                        <img src="images/TempIco.png" alt="Vector Icon" class="w-16 md:w-24 h-auto">
                    </div>
                </div>

                <!-- Sliding Images -->
                <div class="slideshow-container overflow-hidden aos fade-in" data-aos-delay="400">
                    <div class="slideshow flex" id="slideshow-images">
                        <img src="images/slideshow1.png" alt="Image 1">
                        <img src="images/slideshow2.png" alt="Image 2">
                        <img src="images/slideshow3.png" alt="Image 3">
                        <img src="images/slideshow4.png" alt="Image 4">
                        <img src="images/slideshow5.png" alt="Image 5">
                        <img src="images/slideshow1.png" alt="Image 1 D">
                        <img src="images/slideshow2.png" alt="Image 2 D">
                        <img src="images/slideshow3.png" alt="Image 3 D">
                        <img src="images/slideshow4.png" alt="Image 4 D">
                        <img src="images/slideshow5.png" alt="Image 5 D">
                    </div>
                </div>

                <!-- Cards Section -->
                <div class="pt-1 md:pt-1">
                    <p class="text-2xl md:text-4xl font-['Rubik_Mono_One'] uppercase mx-auto text-center text-black my-2 mb-2 aos fade-in-up" data-aos-delay="300">
                        Why donate your clothes?
                    </p>
                    <p class="text-[18px] font-['Poppins'] text-center text-gray-700 mb-2 aos fade-in-up" data-aos-delay="400">
        
                    </p>
                </div>
                <div class="flex flex-col md:flex-row justify-center items-center md:space-x-4 space-y-4 md:space-y-0 mt-4 px-4">
                    <div class="bg-white p-6 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.6)] w-[321px] h-[421px] text-center rounded-2xl flex flex-col justify-center items-center aos fade-in-up" data-aos-delay="500">
                        <img src="images/1stsquare.png" alt="Placeholder" class="mb-4 w-16 h-16">
                        <h2 class="text-1xl text-black font-bold mb-2">SUSTAINABILITY</h2>
                        <p class="text-sm text-black">The clothing industry generates 92 million tons of textile waste yearly—donating clothes help reduce landfill wastes and conserves resources.</p>
                    </div>
                    <div class="bg-white p-6 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.6)] w-[321px] h-[421px] text-center rounded-2xl flex flex-col justify-center items-center aos fade-in-up" data-aos-delay="600">
                        <img src="images/2ndsquare.png" alt="Placeholder" class="mb-4 w-16 h-16">
                        <h2 class="text-1xl text-black font-bold mb-2">SUPPORT</h2>
                        <p class="text-sm text-black">Millions of people lack access to basic clothing—your donation can provide warmth, dignity, and relief to those in need.</p>
                    </div>
                    <div class="bg-white p-6 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.6)] w-[321px] h-[421px] text-center rounded-2xl flex flex-col justify-center items-center aos fade-in-up" data-aos-delay="700">
                        <img src="images/3rdsquare.png" alt="Placeholder" class="mb-4 w-16 h-16">
                        <h2 class="text-1xl text-black font-bold mb-2">RE-PURPOSE</h2>
                        <p class="text-sm text-black">Most people only wear 20% of their clothes, leaving the rest unused. Donating helps these clothes find a new home instead of going to waste.</p>
                    </div>
                </div>

                <!-- Blue Card -->
                <div class="relative w-full max-w-[1300px] h-[400px] mx-auto mt-10 flex items-center justify-between px-4 sm:px-12">
                    <div class="absolute inset-0"></div>
                    <div class="absolute inset-0 animate-blue-mix flex flex-col sm:flex-row items-center justify-between px-4 sm:px-12 py-8 sm:py-0">
                        <!-- LEFT SECTION -->
                        <div class="text-white text-center sm:text-left aos fade-in-left" data-aos-delay="300">
                            <p class="text-2xl sm:text-[40px] font-['Rubik_Mono_One'] font-bold uppercase mb-8">JOIN NEUKAI today!</p>
                            <p class="text-sm sm:text-[18px] font-['Poppins'] text-gray-200 mt-2 mb-8">
                                Struggling to find the right charity for your <br>
                                clothing donations? Donate with NEUKAI to <br>
                                easily connect with trusted organizations and <br>
                                make a meaningful impact!</p>
                            <div class="mt-4">
                                <a href="donor/d-donate.php" class="inline-block hover:scale-105 transition-transform duration-200">
                                    <img src="images/donatenow.png" alt="Donate Now" class="h-8 sm:h-10 object-contain" />
                                </a>
                            </div>
                        </div>

                        <!-- RIGHT SECTION -->
                        <div class="text-[#FBB901] text-center sm:text-right mt-0 sm:mt-0 aos fade-in-right" data-aos-delay="500">
                            <p class="text-2xl sm:text-[40px] font-['Rubik_Mono_One'] font-bold">69420+ DONORS</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'section/footer.php'; ?>

    <!-- Parallax Background -->
    <?php include 'section/parallaxbg.php'; ?>

</body>

</html>
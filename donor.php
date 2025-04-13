<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NEUKAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/optionchecks.js" defer></script>
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
            color: black;
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

<body class="relative min-h-screen flex flex-col">
    <!-- Loading Overlay -->
    <div id="loading-overlay"
        class="fixed inset-0 bg-black flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
        <img src="images/Neukai Logo.svg" alt="Loading" class="loading-logo w-50 h-50" />
    </div>

    <!-- Navbar -->
    <?php include 'section/LoggedInDonorNav.php'; ?>

    <!-- Mobile Menu -->
    <?php include 'section/LoggedInDonorNavMobile.php'; ?>

    <!-- Main Section -->
    <div id="top" class="flex-grow flex justify-center px-4 mt-24">
        <div class="w-full max-w-[1300px] h-auto min-h-[600px] md:h-[986px] bg-white rounded-3xl overflow-hidden">
            <div class="h-auto md:h-[228px] w-full bg-white flex flex-col md:flex-row items-center justify-between p-8">
                <!-- Left Side: Welcome and Logo -->
                <div class="w-full md:w-[337px] ml-0 md:ml-14">
                    <h1 class="text-[28px] md:text-[40px] font-bold text-[#FBB901] w-full md:w-[349px]"
                        style="font-family: 'Rubik Mono One', monospace;">WELCOME TO</h1>
                    <img src="images/Neukai Logo.svg" alt="Neukai Logo" class="mt-2 w-[160px] md:w-auto">
                </div>
                <!-- Right Side: Selections -->
                <div class="w-full md:w-[757px] mt-8 md:mt-0">
                    <h2 class="text-xl md:text-2xl font-bold mb-4">Sort charities to match your clothing donations</h2>
                    <!-- Selection Buttons -->
                    <div class="flex flex-wrap gap-4">
                        <button
                            class="selection-btn w-[80px] md:w-[103px] h-[35px] md:h-[43px] bg-gray-200 rounded-lg flex items-center justify-center transition-all duration-300">
                            <span class="text-sm md:text-base">Option 1</span>
                        </button>
                        <button
                            class="selection-btn w-[80px] md:w-[103px] h-[35px] md:h-[43px] bg-gray-200 rounded-lg flex items-center justify-center transition-all duration-300">
                            <span class="text-sm md:text-base">Option 2</span>
                        </button>
                        <button
                            class="selection-btn w-[80px] md:w-[103px] h-[35px] md:h-[43px] bg-gray-200 rounded-lg flex items-center justify-center transition-all duration-300">
                            <span class="text-sm md:text-base">Option 3</span>
                        </button>
                    </div>
                </div>  
            </div>
            <div class="w-full px-8 py-12">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mt-auto">
                    <div
                        class="relative h-[276px] w-full rounded-lg overflow-hidden shadow-lg transition-all duration-300 hover:shadow-xl group">
                        <img src="images/test.jpg" alt="Charity 1" class="w-full h-full object-cover">
                        <div
                            class="absolute bottom-0 left-0 w-full p-4 text-white transition-all duration-300 group-hover:-translate-y-16">
                            <p class="text-[18px] font-poppins">Location: Kyusi</p>
                            <p class="text-[24px] font-poppins font-bold">Gabriel Chavez</p>
                        </div>
                        <button
                            class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[calc(100%-20px)] h-[53px] bg-green-600 text-white px-6 py-3 rounded-lg opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:-translate-y-4 font-bold text-[16px]">
                            DONATE
                        </button>
                    </div>
                    <div
                        class="relative h-[276px] w-full rounded-lg overflow-hidden shadow-lg transition-all duration-300 hover:shadow-xl group">
                        <img src="images/shirt.jpg" alt="Charity 1" class="w-full h-full object-cover">
                        <div
                            class="absolute bottom-0 left-0 w-full p-4 text-white transition-all duration-300 group-hover:-translate-y-16">
                            <p class="text-[18px] font-poppins">Location: Kyusi</p>
                            <p class="text-[24px] font-poppins font-bold">Queen Samantha</p>
                        </div>
                        <button
                            class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[calc(100%-20px)] h-[53px] bg-green-600 text-white px-6 py-3 rounded-lg opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:-translate-y-4 font-bold text-[16px]">
                            DONATE
                        </button>
                    </div>
                    <div
                        class="relative h-[276px] w-full rounded-lg overflow-hidden shadow-lg transition-all duration-300 hover:shadow-xl group">
                        <img src="images/test.jpg" alt="Charity 1" class="w-full h-full object-cover">
                        <div
                            class="absolute bottom-0 left-0 w-full p-4 text-white transition-all duration-300 group-hover:-translate-y-16">
                            <p class="text-[18px] font-poppins">Location: Kyusi</p>
                            <p class="text-[24px] font-poppins font-bold">Rox Bruno Mars</p>
                        </div>
                        <button
                            class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[calc(100%-20px)] h-[53px] bg-green-600 text-white px-6 py-3 rounded-lg opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:-translate-y-4 font-bold text-[16px]">
                            DONATE
                        </button>
                    </div>

                    <div
                        class="relative h-[276px] w-full rounded-lg overflow-hidden shadow-lg transition-all duration-300 hover:shadow-xl group">
                        <img src="images/shirt.jpg" alt="Charity 1" class="w-full h-full object-cover">
                        <div
                            class="absolute bottom-0 left-0 w-full p-4 text-white transition-all duration-300 group-hover:-translate-y-16">
                            <p class="text-[18px] font-poppins">Location: Kyusi</p>
                            <p class="text-[24px] font-poppins font-bold">Louise Moreno</p>
                        </div>
                        <button
                            class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[calc(100%-20px)] h-[53px] bg-green-600 text-white px-6 py-3 rounded-lg opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:-translate-y-4 font-bold text-[16px]">
                            DONATE
                        </button>
                    </div>
                    <div
                        class="relative h-[276px] w-full rounded-lg overflow-hidden shadow-lg transition-all duration-300 hover:shadow-xl group">
                        <img src="images/test1.jpg" alt="Charity 1" class="w-full h-full object-cover">
                        <div
                            class="absolute bottom-0 left-0 w-full p-4 text-white transition-all duration-300 group-hover:-translate-y-16">
                            <p class="text-[18px] font-poppins">Location: Kyusi</p>
                            <p class="text-[24px] font-poppins font-bold">Heila</p>
                        </div>
                        <button
                            class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[calc(100%-20px)] h-[53px] bg-green-600 text-white px-6 py-3 rounded-lg opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:-translate-y-4 font-bold text-[16px]">
                            DONATE
                        </button>
                    </div>
                    <div
                        class="relative h-[276px] w-full rounded-lg overflow-hidden shadow-lg transition-all duration-300 hover:shadow-xl group">
                        <img src="images/shirt.jpg" alt="Charity 1" class="w-full h-full object-cover">
                        <div
                            class="absolute bottom-0 left-0 w-full p-4 text-white transition-all duration-300 group-hover:-translate-y-16">
                            <p class="text-[18px] font-poppins">Location: Kyusi</p>
                            <p class="text-[24px] font-poppins font-bold">Ayebram</p>
                        </div>
                        <button
                            class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[calc(100%-20px)] h-[53px] bg-green-600 text-white px-6 py-3 rounded-lg opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:-translate-y-4 font-bold text-[16px]">
                            DONATE
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Footer -->
    <?php include 'section/footer.php'; ?>

    <!-- Parallax Background -->
    <?php include 'section/parallaxbg.php'; ?>

</body>

</html>
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
    <script src="js/progressTracker.js" defer></script>
    <link rel="stylesheet" href="css/formStyles.css">
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

<body class="relative min-h-screen flex flex-col">
    <!-- Loading Overlay -->
    <div id="loading-overlay"
        class="fixed inset-0 bg-black flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
        <img src="images/Neukai Logo.svg" alt="Loading" class="loading-logo w-50 h-50" />
    </div>

    <!-- Navbar -->
    <?php include 'section/desktopnavbar.php'; ?>

    <!-- Mobile Menu -->
    <?php include 'section/mobilenavbar.php'; ?>

    <!-- Main Section -->
    <div id="top" class="flex justify-center px-4 mt-24">
        <div class="w-full max-w-[1200px] h-auto min-h-[1010px] bg-white rounded-3xl overflow-hidden">
            <div class="h-auto md:h-[114px] w-full bg-[#1C3AE6] flex flex-col md:flex-row items-center justify-around p-8 md:p-4">
                <div class="flex items-center justify-start space-x-4 md:space-x-5 w-full ml-[48px]">
                    <img src="images/charity.png" alt="Icon" class="w-8 h-8 md:w-8 md:h-8" />
                    <h1 class="text-white text-lg md:text-3xl font-bold">CHARITY APPLICATION</h1>
                </div>
                <!-- Progress Tracker -->
                <div class="flex items-center space-x-2 md:space-x-2 mt-4 md:mt-0 mr-24">
                    <div class="flex items-center">
                        <div id="step1" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-white flex items-center justify-center">
                            <div class="w-6 h-6 md:w-8 md:h-8 rounded-full bg-green-500"></div>
                        </div>
                        <div class="w-[50px] md:w-[100px] h-[2px] md:h-[3px] bg-white mx-1"></div>
                    </div>
                    <div class="flex items-center">
                        <div id="step2" class="w-8 h-8 md:w-10 md:h-10 rounded-3xl border-4 border-white flex items-center justify-center"></div>
                        <div class="w-[50px] md:w-[100px] h-[2px] md:h-[3px] bg-white mx-1"></div>
                    </div>
                    <div class="flex items-center">
                        <div id="step3" class="w-8 h-8 md:w-10 md:h-10 rounded-3xl border-4 border-white flex items-center justify-center"></div>
                    </div>
                </div>
            </div>

            <!-- Form Sections -->
            <div class="flex flex-col md:flex-row p-8 h-[calc(600px-114px)]">
                <!-- Left Section: Step Info -->
                <div class="w-full md:w-1/3 mb-8 md:mb-0 ml-[32px]">
                    <h2 id="stepTitle" class="text-xl mb-2 text-[#000000]">STEP 1</h2>
                    <p id="stepDescription" class="text-2xl font-bold mb-4 text-blue-700">CHARITY DETAILS</p>
                    <p id="nextStep" class="text-lg text-gray-500">Next: Contact Person</p>
                </div>

                <!-- Right Section: Form -->
                <div class="w-full md:w-2/3">
                    <div id="step1Form">
                        <form id="formStep1">
                            <div class="mb-4">
                                <label for="charityName" class="block text-lg font-medium text-gray-700">Charity Name</label>
                                <input type="text" id="charityName" class="form-input" placeholder="Enter charity name" required />
                            </div>
                            <div class="mb-4">
                                <label for="charityNumber" class="block text-lg font-medium text-gray-700">Registered Charity Number</label>
                                <input type="text" id="charityNumber" class="form-input" placeholder="Enter charity number" required />
                            </div>
                            <div class="mb-4">
                                <label for="establishmentDate" class="block text-lg font-medium text-gray-700">Date of Establishment</label>
                                <input type="date" id="establishmentDate" class="form-input" required />
                            </div>
                            <div class="mb-4">
                                <label for="charityDescription" class="block text-lg font-medium text-gray-700">Charity Description</label>
                                <textarea id="charityDescription" class="form-input" rows="3" placeholder="Enter charity description" required></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="charityWebsite" class="block text-lg font-medium text-gray-700">Charity’s Official Website (if available)</label>
                                <input type="url" id="charityWebsite" class="form-input" placeholder="Enter website URL" />
                            </div>
                        </form>
                    </div>

                    <div id="step2Form" class="hidden">
                        <form id="formStep2">
                            <div class="mb-4">
                                <label for="contactName" class="block text-lg font-medium text-gray-700">Contact Person Name</label>
                                <input type="text" id="contactName" class="form-input" placeholder="Enter contact person name" required />
                            </div>
                            <div class="mb-4">
                                <label for="contactEmail" class="block text-lg font-medium text-gray-700">Contact Email</label>
                                <input type="email" id="contactEmail" class="form-input" placeholder="Enter contact email" required />
                            </div>
                            <div class="mb-4">
                                <label for="contactPhone" class="block text-lg font-medium text-gray-700">Contact Phone</label>
                                <input type="tel" id="contactPhone" class="form-input" placeholder="Enter contact phone number" required />
                            </div>
                        </form>
                    </div>


                    <div id="step3Form" class="hidden">
                        <form id="formStep3">
                            <div class="mb-4">
                                <label for="address" class="block text-lg font-medium text-gray-700">Address</label>
                                <input type="text" id="address" class="form-input" placeholder="Enter address" required />
                            </div>
                            <div class="mb-4">
                                <label for="documents" class="block text-lg font-medium text-gray-700">Upload Documents</label>
                                <input type="file" id="documents" class="form-input" required />
                            </div>
                        </form>

                    </div>
                    <!-- Button Section -->
                    <div class="button-container flex justify-end space-x-4 pb-32">
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                        <button id="actionButton" class="btn btn-primary pulse">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    <!-- Modal -->
    <div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50">
        <div class="bg-white w-[502px] h-[325px] rounded-lg shadow-2xl flex flex-col items-center justify-center p-8 animate-slide-up">
            <h2 class="text-2xl font-bold mb-4 text-[#000000] font-poppins">Application Submitted!</h2>
            <p class="text-lg text-gray-700 mb-8 font-poppins text-center">Please check your email and wait for approval. Thank you!</p>
            <a href="index.php">
                <button id="closeModal" class="bg-[#1C3AE6] text-center text-white px-8 py-3 w-[300px] rounded-full font-poppins font-semibold hover:bg-[#1631b3] hover:scale-105 transition-all duration-300">
                    OK
                </button>
            </a>

        </div>
    </div>

    <!-- Footer -->
    <?php include 'section/footer.php'; ?>

    <!-- Parallax Background -->
    <?php include 'section/parallaxbg.php'; ?>


</body>

</html>
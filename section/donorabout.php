<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NEUKAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/navbarScroll.js" defer></script>
    <script src="../js/navlinks.js" defer></script>
    <script src="../js/slideAnimation.js" defer></script>
    <script src="../js/aboutNav.js" defer></script>
    <script src="../js/loading.js" defer></script>
    <script src="../js/indexAos.js" defer></script>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/about.css">
    <link rel="icon" href="../images/TempIco.png" type="image/x-icon">
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
</head>

<body class="relative min-h-screen bg-black text-white font-poppins">

    <!-- Navbar -->
    <?php include 'donorAbtNav.php'; ?>

    <div id="loading-overlay"
        class="fixed inset-0 bg-black flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
        <img src="../images/Neukai Logo.svg" alt="Loading" class="loading-logo w-50 h-50" />
    </div>

    <!-- Hamburger Button  -->
    <div id="neukai-menu-toggle" class="neukai-menu-toggle lg:hidden">
        <span></span>
    </div>

    <!-- Mobile Menu  -->
    <div id="neukai-mobile-nav" class="neukai-mobile-nav lg:hidden">
        <div class="flex flex-col h-full relative">
            <div id="neukai-nav-indicator" class="neukai-nav-indicator"></div>
            <nav class="px-8">
                <ul class="space-y-6">
                    <li class="neukai-menu-item">
                        <a href="../donorhome.php" class="neukai-menu-link mt-12 w-full text-lg font-medium text-white hover:text-orange-400 
                transition-all duration-300 py-2 px-4 rounded-lg
                focus:outline-none text-center block">
                            Back to Home
                        </a>
                    </li>
                    <li class="neukai-menu-item">
                        <button class="neukai-menu-link w-full -mt-2 text-lg font-medium text-white hover:text-orange-400 
                        transition-all duration-300 py-2 px-4 rounded-lg
                        focus:outline-none"
                            data-target="aboutus">
                            About Us
                        </button>
                    </li>
                    <li class="neukai-menu-item">
                        <button class="neukai-menu-link w-full text-lg font-medium text-white hover:text-orange-400 
                        transition-all duration-300 py-2 px-4 rounded-lg
                        focus:outline-none"
                            data-target="privacy">
                            Privacy Policy
                        </button>
                    </li>
                    <li class="neukai-menu-item">
                        <button class="neukai-menu-link w-full text-lg font-medium text-white hover:text-orange-400 
                        transition-all duration-300 py-2 px-4 rounded-lg
                        focus:outline-none"
                            data-target="terms">
                            Terms of Use
                        </button>
                    </li>
                    <li class="neukai-menu-item">
                        <button class="neukai-menu-link w-full text-lg font-medium text-white hover:text-orange-400 
                        transition-all duration-300 py-2 px-4 rounded-lg
                        focus:outline-none"
                            data-target="team">
                            Our Team
                        </button>
                    </li>
                    <li class="neukai-menu-item">
                        <button class="neukai-menu-link w-full text-lg font-medium text-white hover:text-orange-400 
                        transition-all duration-300 py-2 px-4 rounded-lg
                        focus:outline-none"
                            data-target="contact">
                            Contact Us
                        </button>
                    </li>
                </ul>
            </nav>
            <div class="mt-auto p-8 text-xs text-gray-400 text-center">
                © 2025 NEUKAI. All Rights Reserved.
            </div>
        </div>
    </div>

    <div id="top" class="flex-grow flex justify-center px-4 mt-24">
        <div class="w-full max-w-[1600px] h-auto min-h-[2000px] md:h-[2000px] bg-transparent rounded-3xl flex overflow-hidden">

            <!-- Left Navbar Desktop -->
            <nav class="hidden lg:flex w-[400px] h-full p-6 rounded-lg text-white flex-col items-center fixed mt-16">
                <ul class="space-y-6 w-full">
                    <li>
                        <button class="nav-link w-full text-lg font-medium text-white hover:text-orange-400 
                        transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] 
                        py-3 px-4 rounded-lg hover:bg-white/5 
                        hover:translate-x-2 hover:shadow-[2px_0_0_0_rgba(251,146,60,1)]
                        focus:outline-none focus:ring-2 focus:ring-orange-400/50
                        [text-shadow:0_2px_4px_rgba(0,0,0,0.5)]"
                            data-target="aboutus">
                            About Us
                        </button>
                    </li>
                    <li>
                        <button class="nav-link w-full text-lg font-medium text-white hover:text-orange-400 
                        transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] 
                        py-3 px-4 rounded-lg hover:bg-white/5 
                        hover:translate-x-2 hover:shadow-[2px_0_0_0_rgba(251,146,60,1)]
                        focus:outline-none focus:ring-2 focus:ring-orange-400/50
                        [text-shadow:0_2px_4px_rgba(0,0,0,0.5)]"
                            data-target="privacy">
                            Privacy Policy
                        </button>
                    </li>
                    <li>
                        <button class="nav-link w-full text-lg font-medium text-white hover:text-orange-400 
                        transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] 
                        py-3 px-4 rounded-lg hover:bg-white/5 
                        hover:translate-x-2 hover:shadow-[2px_0_0_0_rgba(251,146,60,1)]
                        focus:outline-none focus:ring-2 focus:ring-orange-400/50
                        [text-shadow:0_2px_4px_rgba(0,0,0,0.5)]"
                            data-target="terms">
                            Terms of Use
                        </button>
                    </li>
                    <li>
                        <button class="nav-link w-full text-lg font-medium text-white hover:text-orange-400 
                        transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] 
                        py-3 px-4 rounded-lg hover:bg-white/5 
                        hover:translate-x-2 hover:shadow-[2px_0_0_0_rgba(251,146,60,1)]
                        focus:outline-none focus:ring-2 focus:ring-orange-400/50
                        [text-shadow:0_2px_4px_rgba(0,0,0,0.5)]"
                            data-target="team">
                            Our Team
                        </button>
                    </li>
                    <li>
                        <button class="nav-link w-full text-lg font-medium text-white hover:text-orange-400 
                        transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] 
                        py-3 px-4 rounded-lg hover:bg-white/5 
                        hover:translate-x-2 hover:shadow-[2px_0_0_0_rgba(251,146,60,1)]
                        focus:outline-none focus:ring-2 focus:ring-orange-400/50
                        [text-shadow:0_2px_4px_rgba(0,0,0,0.5)]"
                            data-target="contact">
                            Contact Us
                        </button>
                    </li>
                </ul>
                <div class="mt-64 text-1xl text-gray-400 text-center">
                    © 2025 NEUKAI. All Rights Reserved.
                </div>
            </nav>

            <!-- Right Scrollable Section - Full width on mobile -->
            <div class="flex-1 h-auto overflow-y-scroll pr-4 scrollbar-hidden lg:ml-[400px] -mt-8">
                <section id="aboutus" class="flex flex-col text-left pt-24 md:pt-24 px-4">
                    <div class="flex flex-col items-center text-center mb-16">
                        <img src="../images/NEUKAI LOGO.svg" alt="NEUKAI Logo" class="w-[220px] sm:w-[320px] md:w-[640.38px] h-auto mb-8 aos fade-in-down" data-aos-delay="400" />
                        <h1 class="text-2xl sm:text-3xl md:text-5xl font-rubik font-extrabold text-[#FBB901] tracking-[2px] aos fade-in-down" data-aos-delay="600"
                            style="-webkit-text-stroke:1px black;">NEW + UKAY</h1>
                        <p class="text-xs sm:text-sm md:text-base text-white mt-3 mx-4 max-w-xl aos fade-in-down" data-aos-delay="700">
                            Sustainable fashion isn't just a trend—it's a movement. Pass it forward and make a difference, one outfit at a time!
                        </p>
                    </div>

                    <!-- About Us  -->
                    <h2 class="text-xl sm:text-2xl md:text-3xl mx-4 sm:ml-32 font-poppins font-bold text-white mb-4 aos fade-in-up" data-aos-delay="200">About Us</h2>
                    <p class="text-xs sm:text-sm md:text-base mx-4 sm:ml-32 font-poppins text-white mb-16 max-w-3xl aos fade-in-left" data-aos-delay="300">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                    </p>

                    <!-- 3 REKTANGOL -->
                    <div class="mx-4 sm:ml-32 flex flex-col sm:flex-row gap-4 items-center sm:items-start mb-16">
                        <div class="w-[720px] h-[140px] overflow-hidden max-w-full aos fade-in-left" data-aos-delay="200">
                            <img src="../images/slideshow1.png" alt="Image1" class="w-full h-full object-cover">
                        </div>
                        <div class="w-[720px] h-[140px] overflow-hidden max-w-full aos fade-in-left" data-aos-delay="300">
                            <img src="../images/slideshow2.png" alt="Image1" class="w-full h-full object-cover">
                        </div>
                        <div class="w-[720px] h-[140px] overflow-hidden max-w-full aos fade-in-left" data-aos-delay="400">
                            <img src="../images/slideshow3.png" alt="Image3" class="w-full h-full object-cover">
                        </div>
                    </div>


                    <!-- Privacy Policy  -->
                    <h2 id="privacy" class="text-xl sm:text-2xl md:text-3xl mx-4 sm:ml-32 font-poppins font-bold text-white mb-4 aos fade-in-up" data-aos-delay="200">Privacy Policy</h2>
                    <p class="text-xs sm:text-sm md:text-base mx-4 sm:ml-32 font-poppins text-white mb-16 max-w-3xl aos fade-in-left" data-aos-delay="300">
                        Neukai values your privacy. This policy outlines how we handle your information:<br><br>
                        <span class="font-semibold">Information Collection</span><br>
                        We collect personal details (e.g., name, email) for account creation, donations, and communication purposes, along with non-personal data (e.g., IP address) for analytics.<br><br>
                        <span class="font-semibold">Usage</span><br>
                        Your data is used to provide services, send updates, and improve our platform. You can opt out of promotional emails.<br><br>
                        <span class="font-semibold">Sharing</span><br>
                        We only share data with trusted third-party service providers or if legally required.<br><br>
                        <span class="font-semibold">Cookies</span><br>
                        Cookies are used for a better experience. You can adjust settings through your browser.<br><br>
                        <span class="font-semibold">Security</span><br>
                        We implement measures to protect your data but cannot guarantee absolute security.<br><br>
                        <span class="font-semibold">Rights</span><br>
                        You may request access, correction, or deletion of your data, subject to legal obligations.<br><br>
                        <span class="font-semibold">Updates</span><br>
                        Changes to this policy will be posted on the website.<br><br>
                        For questions or concerns, contact us at [Contact Email/Phone Number].
                    </p>

                    <!-- Terms of Use  -->
                    <h2 id="terms" class="text-xl sm:text-2xl md:text-3xl mx-4 sm:ml-32 font-poppins font-bold text-white mb-4 aos fade-in-up" data-aos-delay="200">Terms of Use</h2>
                    <p class="text-xs sm:text-sm md:text-base mx-4 sm:ml-32 font-poppins text-white mb-16 max-w-3xl aos fade-in-left" data-aos-delay="300">
                        By using our website, you agree to the following terms:<br><br>
                        <span class="font-semibold">Purpose</span><br>
                        Our platform facilitates clothing donations to those in need. Use it responsibly and lawfully.<br><br>
                        <span class="font-semibold">User Accounts</span><br>
                        Keep your account information accurate and secure. You are responsible for activities under your account.<br><br>
                        <span class="font-semibold">Donations</span><br>
                        Donations must meet our guidelines (e.g., clean, usable items). We are not liable for misuse of donated items.<br><br>
                        <span class="font-semibold">Content</span><br>
                        All website content remains our property. Do not share harmful or unauthorized material.<br><br>
                        <span class="font-semibold">Privacy</span><br>
                        Your personal information is handled as detailed in our Privacy Policy.<br><br>
                        <span class="font-semibold">Liability</span><br>
                        We provide services "as is" and are not liable for any damages.<br><br>
                        <span class="font-semibold">Updates</span><br>
                        Terms may change; continued use indicates acceptance.<br><br>
                        For questions, contact us at [Contact Email/Phone Number].
                    </p>


                    <h2 id="team" class="text-xl sm:text-2xl md:text-3xl mx-4 sm:ml-32 font-poppins font-bold text-white mb-4 aos fade-in-up" data-aos-delay="200">Our Team</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mx-4 sm:ml-32 mb-16 max-w-5xl">
                        <!-- Row 1 -->
                        <div class="flex items-center aos zoom-in" data-aos-delay="200">
                            <img src="../images/egoist1.jpeg" alt="Team Member" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full mr-4">
                            <div>
                                <span class="font-bold text-white block text-sm sm:text-base">Justin Rivera</span>
                                <span class="text-white text-xs sm:text-sm">THE LIVING NIGHTMARE</span>
                            </div>
                        </div>
                        <div class="flex items-center aos zoom-in" data-aos-delay="300">
                            <img src="../images/test1.jpg" alt="Team Member" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full mr-4">
                            <div>
                                <span class="font-bold text-white block text-sm sm:text-base">Name 2</span>
                                <span class="text-white text-xs sm:text-sm">Role 2</span>
                            </div>
                        </div>
                        <div class="flex items-center aos zoom-in" data-aos-delay="400">
                            <img src="../images/shirt.jpg" alt="Team Member" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full mr-4">
                            <div>
                                <span class="font-bold text-white block text-sm sm:text-base">Name 3</span>
                                <span class="text-white text-xs sm:text-sm">Role 3</span>
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="flex items-center aos zoom-in" data-aos-delay="200">
                            <img src="../images/test1.jpg" alt="Team Member" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full mr-4">
                            <div>
                                <span class="font-bold text-white block text-sm sm:text-base">Name 4</span>
                                <span class="text-white text-xs sm:text-sm">Role 4</span>
                            </div>
                        </div>
                        <div class="flex items-center aos zoom-in" data-aos-delay="300">
                            <img src="../images/test1.jpg" alt="Team Member" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full mr-4">
                            <div>
                                <span class="font-bold text-white block text-sm sm:text-base">Name 5</span>
                                <span class="text-white text-xs sm:text-sm">Role 5</span>
                            </div>
                        </div>
                        <div class="flex items-center aos zoom-in" data-aos-delay="400">
                            <img src="../images/test1.jpg" alt="Team Member" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full mr-4">
                            <div>
                                <span class="font-bold text-white block text-sm sm:text-base">Name 6</span>
                                <span class="text-white text-xs sm:text-sm">Role 6</span>
                            </div>
                        </div>

                        <!-- Row 3 -->
                        <div class="flex items-center aos zoom-in" data-aos-delay="200">
                            <img src="../images/test1.jpg" alt="Team Member" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full mr-4">
                            <div>
                                <span class="font-bold text-white block text-sm sm:text-base">Name 7</span>
                                <span class="text-white text-xs sm:text-sm">Role 7</span>
                            </div>
                        </div>
                        <div class="flex items-center aos zoom-in" data-aos-delay="300">
                            <img src="../images/test1.jpg" alt="Team Member" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full mr-4">
                            <div>
                                <span class="font-bold text-white block text-sm sm:text-base">Name 8</span>
                                <span class="text-white text-xs sm:text-sm">Role 8</span>
                            </div>
                        </div>
                        <div class="flex items-center aos zoom-in" data-aos-delay="400">
                            <img src="../images/test1.jpg" alt="Team Member" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full mr-4">
                            <div>
                                <span class="font-bold text-white block text-sm sm:text-base">Name 9</span>
                                <span class="text-white text-xs sm:text-sm">Role 9</span>
                            </div>
                        </div>
                    </div>

                    <h2 id="contact" class="text-xl sm:text-2xl md:text-3xl mx-4 sm:ml-32 font-poppins font-bold text-white mb-4 aos fade-in-up" data-aos-delay="200">Contact Us</h2>
                    <p class="text-xs sm:text-sm md:text-base mx-4 sm:ml-32 font-poppins text-white mb-8 max-w-3xl aos fade-in-left" data-aos-delay="300">
                        If you have any questions, concerns, or need assistance, feel free to reach out to us:
                    </p>

                    <div class="mx-4 sm:ml-32 mb-16 space-y-4">
                        <div class="flex items-center aos fade-in-right" data-aos-delay="200">
                            <img src="../images/email.svg" alt="Email" class="w-5 h-5 sm:w-6 sm:h-6 mr-4">
                            <span class="text-white font-poppins text-xs sm:text-sm md:text-base">test.gmail.com</span>
                        </div>

                        <div class="flex items-center aos fade-in-right" data-aos-delay="300">
                            <img src="../images/Call.svg" alt="Phone" class="w-5 h-5 sm:w-6 sm:h-6 mr-4">
                            <span class="text-white font-poppins text-xs sm:text-sm md:text-base">69-6969</span>
                        </div>
                    </div>

                </section>


                <!-- Parallax Background -->
                <div class="absolute top-0 left-0 w-full h-full bg-cover bg-center bg-fixed z-[-1] parallax"
                    style="background-image: url('../images/background.png');">
                    <div class="w-full h-full bg-gradient-to-b from-transparent via-black/50 to-black"></div>
                </div>
            </div>
        </div>
    </div>


</body>

</html>
let currentStep = 1;

            document.addEventListener("DOMContentLoaded", function() {
                showStep(currentStep);
                updateProgressBar(currentStep);
            });

            function showStep(step) {
                document.querySelectorAll(".step").forEach((el) => el.classList.remove("active"));
                document.getElementById(`step${step}`).classList.add("active");
                updateProgressBar(step);
            }

            function updateProgressBar(step) {

                document.querySelectorAll(".progress-step").forEach((el, index) => {
                    el.classList.remove("active", "completed");
                    if (index + 1 < step) {
                        el.classList.add("completed");
                    } else if (index + 1 === step) {
                        el.classList.add("active");
                    }
                });
            }

            function validateEmail(email) {
                const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                return emailPattern.test(email.trim());
            }

            function validatePassword(password) {
                const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
                return passwordPattern.test(password);
            }

            function nextStep(step) {
                const currentForm = document.getElementById(`step${step}`);
                const inputs = currentForm.querySelectorAll("input[required]");

                let isValid = true;


                const oldErrors = currentForm.querySelectorAll(".error-message");
                oldErrors.forEach(error => error.remove());

                inputs.forEach((input) => {

                    input.classList.remove("error");

                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add("error");


                        const errorMsg = document.createElement("div");
                        errorMsg.classList.add("error-message");
                        errorMsg.textContent = "This field is required";
                        input.parentNode.insertBefore(errorMsg, input.nextSibling);
                    }

                    if (input.type === "email" && input.value.trim()) {
                        if (!validateEmail(input.value)) {
                            isValid = false;
                            input.classList.add("error");

                            const errorMsg = document.createElement("div");
                            errorMsg.classList.add("error-message");
                            errorMsg.textContent = "Please enter a valid email address";
                            input.parentNode.insertBefore(errorMsg, input.nextSibling);
                        }
                    }

                    if (input.id === "charity_image" || input.id === "reg_image") {
                        if (input.files.length === 0) {
                            isValid = false;
                            input.classList.add("error");

                            const errorMsg = document.createElement("div");
                            errorMsg.classList.add("error-message");
                            errorMsg.textContent = "Please select a file";
                            input.parentNode.insertBefore(errorMsg, input.nextSibling);
                        }
                    }
                });


                if (step === 4) {
                    const password = document.getElementById("password").value;
                    const confirmPassword = document.getElementById("password_confirmation").value;

                    if (password && !validatePassword(password)) {
                        isValid = false;
                        document.getElementById("password").classList.add("error");

                        const errorMsg = document.createElement("div");
                        errorMsg.classList.add("error-message");
                        errorMsg.textContent = "Password must be at least 8 characters with uppercase, lowercase, number, and special character";
                        document.getElementById("password").parentNode.insertBefore(errorMsg, document.getElementById("password").nextSibling.nextSibling);
                    }

                    if (password && confirmPassword && password !== confirmPassword) {
                        isValid = false;
                        document.getElementById("password_confirmation").classList.add("error");

                        const errorMsg = document.createElement("div");
                        errorMsg.classList.add("error-message");
                        errorMsg.textContent = "Passwords do not match";
                        document.getElementById("password_confirmation").parentNode.insertBefore(errorMsg, document.getElementById("password_confirmation").nextSibling);
                    }
                }

                if (isValid) {
                    currentStep++;
                    showStep(currentStep);
                }
            }

            function prevStep(step) {
                if (step > 1) {
                    currentStep--;
                    showStep(currentStep);
                }
            }

            document.querySelectorAll('input[type="file"]').forEach(fileInput => {
                fileInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        const fileType = this.files[0].type;
                        const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];

                        if (!validTypes.includes(fileType)) {
                            alert('Invalid file type. Please upload only JPG, JPEG or PNG files.');
                            this.value = '';
                        }

                        const fileSize = this.files[0].size / 1024 / 1024; // in MB
                        if (fileSize > 5) {
                            alert('File size exceeds 5MB. Please upload a smaller file.');
                            this.value = '';
                        }
                    }
                });
            });
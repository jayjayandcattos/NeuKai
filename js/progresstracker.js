let currentStep = 1;

const step1 = document.getElementById('step1');
const step2 = document.getElementById('step2');
const step3 = document.getElementById('step3');
const actionButton = document.getElementById('actionButton');
const modal = document.getElementById('modal');
const closeModal = document.getElementById('closeModal');

const step1Form = document.getElementById('step1Form');
const step2Form = document.getElementById('step2Form');
const step3Form = document.getElementById('step3Form');

const stepTitle = document.getElementById('stepTitle');
const stepDescription = document.getElementById('stepDescription');
const nextStep = document.getElementById('nextStep');

function updateProgress() {
    if (currentStep === 1) {
        step2.innerHTML = '<div class="w-6 h-6 md:w-8 md:h-8 rounded-full bg-green-500"></div>';
        step2.classList.replace('border-4', 'bg-white');

        step1Form.classList.add('hidden');
        step2Form.classList.remove('hidden');

        stepTitle.textContent = "Step 2";
        stepDescription.textContent = "Contact Person";
        nextStep.textContent = "Next: Address & Documents";

        currentStep = 2;
        actionButton.textContent = "Next";
    } else if (currentStep === 2) {
        step3.innerHTML = '<div class="w-6 h-6 md:w-8 md:h-8 rounded-full bg-green-500"></div>';
        step3.classList.replace('border-4', 'bg-white');

        step2Form.classList.add('hidden');
        step3Form.classList.remove('hidden');

        stepTitle.textContent = "Step 3";
        stepDescription.textContent = "Address & Documents";
        nextStep.textContent = "Next: Complete";

        currentStep = 3;
        actionButton.textContent = "Complete";
    } else if (currentStep === 3) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('active'); // Apply animation
        }, 10);
    }
}

function closeModalHandler() {
    modal.classList.remove('active');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

actionButton.addEventListener('click', updateProgress);
closeModal.addEventListener('click', closeModalHandler);

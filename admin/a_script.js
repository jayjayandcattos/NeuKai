
// Live date & time
function updateDateTime() {
  const now = new Date();
  const datetimeStr = now.toLocaleString();
  const datetimeElement = document.getElementById('datetime');
  if (datetimeElement) {
      datetimeElement.textContent = datetimeStr;
  }
}

// Initialize date time if element exists
if (document.getElementById('datetime')) {
  updateDateTime();
  setInterval(updateDateTime, 1000);
}

// Tab switching logic
function setupTabSwitching() {
  const links = document.querySelectorAll('.tab-link');
  const contentContainer = document.getElementById('tab-content');

  async function loadTab(tab) {
      try {
          const response = await fetch(`${tab}.php`);
          if (!response.ok) throw new Error('Page not found.');
          const html = await response.text();
          contentContainer.innerHTML = html;

          // Execute any scripts in the loaded content
          contentContainer.querySelectorAll('script').forEach(script => {
              const newScript = document.createElement('script');
              newScript.textContent = script.textContent;
              document.body.appendChild(newScript).remove();
          });
      } catch (error) {
          contentContainer.innerHTML = `<h2>Error</h2><p>${error.message}</p>`;
      }
  }

  function activateTab(tab) {
      if (links && links.length > 0) {
          links.forEach(link => link.classList.remove('active'));
          const activeLink = [...links].find(link => link.getAttribute('data-tab') === tab);
          if (activeLink) activeLink.classList.add('active');
      }

      if (contentContainer) {
          // Trigger slide animation
          contentContainer.classList.remove('animate-slide');
          void contentContainer.offsetWidth; // Force reflow
          contentContainer.classList.add('animate-slide');

          loadTab(tab);
      }
  }

  if (links && links.length > 0) {
      links.forEach(link => {
          link.addEventListener('click', (e) => {
              e.preventDefault();
              const tab = link.getAttribute('data-tab');
              activateTab(tab);
              history.pushState(null, '', '#' + tab);
          });
      });

      window.addEventListener('load', () => {
          let hash = window.location.hash.substring(1);
          if (!hash) hash = 'home';
          activateTab(hash);
      });

      window.addEventListener('popstate', () => {
          let hash = window.location.hash.substring(1);
          if (!hash) hash = 'home';
          activateTab(hash);
      });
  }
}

// Initialize tab switching if elements exist
if (document.querySelector('.tab-link') && document.getElementById('tab-content')) {
  setupTabSwitching();
}

// Collapsible sections
document.addEventListener('click', function (e) {
  const header = e.target.closest('.collapsible-header, .collapsible-header1');
  if (header) {
      e.preventDefault();
      const body = header.nextElementSibling;
      const icon = header.querySelector('.arrow-icon');

      if (body) body.classList.toggle('open');
      if (icon) icon.classList.toggle('down');
  }
});
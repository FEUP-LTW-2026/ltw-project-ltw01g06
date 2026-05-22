// Equilibrio Gym - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
  // Auto-hide messages after 5 seconds
  const messages = document.querySelectorAll('.message');
  messages.forEach(function(message) {
    // Manual close button
    const closeBtn = message.querySelector('[data-close-message]');
    if (closeBtn) {
      closeBtn.addEventListener('click', function() {
        message.remove();
      });
    }

    setTimeout(function() {
      message.style.animation = 'slideOut 0.3s ease forwards';
      setTimeout(function() {
        message.remove();
      }, 300);
    }, 5000);
  });

  // Form validation
  const forms = document.querySelectorAll('form');
  forms.forEach(function(form) {
    form.addEventListener('submit', function(e) {
      const passwordField = form.querySelector('input[name="password"]');
      const confirmField = form.querySelector('input[name="confirm_password"]');
      
      if (passwordField && confirmField) {
        if (passwordField.value !== confirmField.value) {
          e.preventDefault();
          alert('As passwords nao coincidem!');
          return false;
        }
      }

      // Check new password fields
      const newPasswordField = form.querySelector('input[name="new_password"]');
      const confirmNewField = form.querySelector('input[name="confirm_password"]');
      
      if (newPasswordField && confirmNewField && newPasswordField.value) {
        if (newPasswordField.value !== confirmNewField.value) {
          e.preventDefault();
          alert('As novas passwords nao coincidem!');
          return false;
        }
      }
    });
  });

  // Mobile menu toggle (if needed)
  const menuToggle = document.querySelector('.menu-toggle');
  const mainNav = document.querySelector('.main-nav');
  
  if (menuToggle && mainNav) {
    menuToggle.addEventListener('click', function() {
      mainNav.classList.toggle('active');
    });
  }

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
    anchor.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      if (href !== '#') {
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      }
    });
  });

  // Enrollment confirmation
  const enrollForms = document.querySelectorAll('.enroll-form');
  enrollForms.forEach(function(form) {
    form.addEventListener('submit', function(e) {
      const action = form.querySelector('input[name="action"]');
      if (action && action.value === 'cancel') {
        if (!confirm('Tem a certeza que quer cancelar esta inscricao?')) {
          e.preventDefault();
        }
      }
    });
  });

  // Add loading state to buttons on form submit
  document.querySelectorAll('form').forEach(function(form) {
    form.addEventListener('submit', function() {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn && !submitBtn.disabled) {
        submitBtn.disabled = true;
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A processar...';
        
        // Re-enable after 3 seconds in case of error
        setTimeout(function() {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        }, 3000);
      }
    });
  });
});

// Os @keyframes slideOut estão definidos em css/components.css

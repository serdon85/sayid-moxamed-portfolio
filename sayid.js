 // Form submission handler
      document.getElementById('contactForm').addEventListener('submit', function(e) {
         e.preventDefault();
         alert('Thank you for your message! I will get back to you soon.');
         this.reset();
      });

      // Smooth scrolling for navigation links
      document.querySelectorAll('nav a').forEach(anchor => {
         anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            window.scrollTo({
               top: targetSection.offsetTop - 80,
               behavior: 'smooth'
            });
         });
      });

      // Add parallax effect to circles
      window.addEventListener('scroll', function() {
         const scrolled = window.pageYOffset;
         const circles = document.querySelectorAll('.circle');
         
         circles.forEach((circle, index) => {
            const speed = 0.5 + (index * 0.1);
            circle.style.transform = `translateY(${scrolled * speed}px) rotate(${scrolled * 0.1}deg)`;
         });
      });
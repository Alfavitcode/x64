document.addEventListener('DOMContentLoaded', function() {
    // Animate elements on page load
    const animateElements = document.querySelectorAll('.animate-on-load');
    animateElements.forEach((element, index) => {
        setTimeout(() => {
            element.classList.add('fade-in');
        }, index * 100);
    });

    // Handle accordion functionality
    const accordionItems = document.querySelectorAll('.faq-accordion-item');
    accordionItems.forEach(item => {
        const header = item.querySelector('.faq-accordion-header');
        
        header.addEventListener('click', () => {
            // Close all other accordions
            const currentlyActive = document.querySelector('.faq-accordion-item.active');
            if (currentlyActive && currentlyActive !== item) {
                currentlyActive.classList.remove('active');
            }
            
            // Toggle current accordion
            item.classList.toggle('active');
        });
    });

    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Add animation on scroll
    const animateOnScrollElements = document.querySelectorAll('.animate-on-scroll');
    
    const animateOnScroll = () => {
        animateOnScrollElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementTop < windowHeight - 100) {
                if (element.classList.contains('slide-in-left-scroll')) {
                    element.classList.add('slide-in-left');
                } else if (element.classList.contains('slide-in-right-scroll')) {
                    element.classList.add('slide-in-right');
                } else if (element.classList.contains('bounce-in-scroll')) {
                    element.classList.add('bounce-in');
                } else {
                    element.classList.add('fade-in');
                }
            }
        });
    };
    
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Initial check on page load
    
    // Add image gallery functionality
    const galleryItems = document.querySelectorAll('.faq-image-item');
    galleryItems.forEach(item => {
        item.addEventListener('click', function() {
            const imgSrc = this.querySelector('img').src;
            const modal = document.createElement('div');
            modal.className = 'faq-image-modal';
            modal.innerHTML = `
                <div class="faq-image-modal-content">
                    <span class="faq-image-modal-close">&times;</span>
                    <img src="${imgSrc}" alt="Gallery image">
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Add styles dynamically
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.backgroundColor = 'rgba(0, 0, 0, 0.9)';
            modal.style.zIndex = '1000';
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
            
            const modalContent = modal.querySelector('.faq-image-modal-content');
            modalContent.style.position = 'relative';
            modalContent.style.maxWidth = '90%';
            modalContent.style.maxHeight = '90%';
            
            const modalImg = modal.querySelector('img');
            modalImg.style.maxWidth = '100%';
            modalImg.style.maxHeight = '90vh';
            modalImg.style.objectFit = 'contain';
            modalImg.style.borderRadius = '5px';
            
            const closeBtn = modal.querySelector('.faq-image-modal-close');
            closeBtn.style.position = 'absolute';
            closeBtn.style.top = '-40px';
            closeBtn.style.right = '0';
            closeBtn.style.color = 'white';
            closeBtn.style.fontSize = '30px';
            closeBtn.style.fontWeight = 'bold';
            closeBtn.style.cursor = 'pointer';
            
            // Add closing functionality
            closeBtn.addEventListener('click', () => {
                document.body.removeChild(modal);
            });
            
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    document.body.removeChild(modal);
                }
            });
        });
    });
}); 
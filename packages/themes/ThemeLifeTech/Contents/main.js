/**
 * LifeTech Website - Main JavaScript
 * Handles carousel, smooth scrolling, animations, and interactive features
 * Updated for Tailwind CSS
 */

(function() {
    'use strict';

    // ============================================
    // Initialize when DOM is ready
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        initSmoothScrolling();
        initNavbarScroll();
        initScrollAnimations();
        initCarousel();
        initMobileMenu();
        initBackToTop();
    });

    // ============================================
    // Smooth Scrolling for Navigation Links
    // ============================================
    function initSmoothScrolling() {
        const navLinks = document.querySelectorAll('a[href^="#"]');
        
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Skip if it's just "#"
                if (href === '#' || href === '') {
                    return;
                }
                
                const target = document.querySelector(href);
                
                if (target) {
                    e.preventDefault();
                    
                    // Close mobile menu if open
                    const mobileMenu = document.getElementById('mobileMenuDropdown');
                    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                    }
                    
                    // Calculate offset for fixed navbar
                    const navbarHeight = document.querySelector('nav').offsetHeight;
                    const targetPosition = target.offsetTop - navbarHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // ============================================
    // Navbar Scroll Effect
    // ============================================
    function initNavbarScroll() {
        const navbar = document.querySelector('nav');
        
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            // Add shadow when scrolled
            if (currentScroll > 50) {
                navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.2)';
            } else {
                navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
            }
            
            // Highlight active nav link based on scroll position
            updateActiveNavLink();
        });
    }

    // ============================================
    // Update Active Navigation Link
    // ============================================
    function updateActiveNavLink() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');
        const navbarHeight = document.querySelector('nav').offsetHeight;
        const scrollPos = window.pageYOffset + navbarHeight + 100;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${sectionId}`) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }

    // ============================================
    // Scroll Animations (Fade In on Scroll)
    // ============================================
    function initScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    // Ensure opacity stays at 1 after animation
                    entry.target.style.opacity = '1';
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // Observe elements that should animate
        const animateElements = document.querySelectorAll(
            '.feature-card, .service-card, .team-card, .forum-post, .about-image'
        );
        
        animateElements.forEach(el => {
            // Only set opacity to 0 if not already animated
            if (!el.classList.contains('fade-in')) {
                el.style.opacity = '0';
                observer.observe(el);
            }
        });
    }

    // ============================================
    // Custom Carousel Functionality
    // ============================================
    function initCarousel() {
        const slides = document.querySelectorAll('.carousel-slide');
        const indicators = document.querySelectorAll('.carousel-indicator');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        let currentSlide = 0;
        let carouselInterval;

        // Function to show a specific slide
        function showSlide(index) {
            // Remove active class from all slides and indicators
            slides.forEach(slide => {
                slide.classList.remove('active');
            });
            indicators.forEach(indicator => {
                indicator.classList.remove('active');
            });

            // Add active class to current slide and indicator
            slides[index].classList.add('active');
            indicators[index].classList.add('active');
            currentSlide = index;
        }

        // Function to go to next slide
        function nextSlide() {
            const next = (currentSlide + 1) % slides.length;
            showSlide(next);
        }

        // Function to go to previous slide
        function prevSlide() {
            const prev = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(prev);
        }

        // Event listeners for buttons
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                nextSlide();
                resetCarouselInterval();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                prevSlide();
                resetCarouselInterval();
            });
        }

        // Event listeners for indicators
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                showSlide(index);
                resetCarouselInterval();
            });
        });

        // Auto-play carousel
        function startCarousel() {
            carouselInterval = setInterval(nextSlide, 5000);
        }

        function resetCarouselInterval() {
            clearInterval(carouselInterval);
            startCarousel();
        }

        // Pause on hover
        const carousel = document.getElementById('heroCarousel');
        if (carousel) {
            carousel.addEventListener('mouseenter', () => {
                clearInterval(carouselInterval);
            });

            carousel.addEventListener('mouseleave', () => {
                startCarousel();
            });
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                prevSlide();
                resetCarouselInterval();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
                resetCarouselInterval();
            }
        });

        // Initialize first slide on page load
        showSlide(0);

        // Start the carousel
        startCarousel();
    }

    // ============================================
    // Mobile Menu Enhancement
    // ============================================
    function initMobileMenu() {
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuDropdown = document.getElementById('mobileMenuDropdown');

        if (mobileMenuBtn && mobileMenuDropdown) {
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenuDropdown.classList.toggle('hidden');
                
                // Toggle icon
                const icon = mobileMenuBtn.querySelector('i');
                if (icon) {
                    if (mobileMenuDropdown.classList.contains('hidden')) {
                        icon.classList.remove('bi-x');
                        icon.classList.add('bi-list');
                    } else {
                        icon.classList.remove('bi-list');
                        icon.classList.add('bi-x');
                    }
                }
            });

            // Close menu when clicking on a link
            const mobileLinks = mobileMenuDropdown.querySelectorAll('a');
            mobileLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenuDropdown.classList.add('hidden');
                    const icon = mobileMenuBtn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('bi-x');
                        icon.classList.add('bi-list');
                    }
                });
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                const isClickInsideNav = mobileMenuBtn.contains(e.target) || 
                                        mobileMenuDropdown.contains(e.target);
                
                if (!isClickInsideNav && !mobileMenuDropdown.classList.contains('hidden')) {
                    mobileMenuDropdown.classList.add('hidden');
                    const icon = mobileMenuBtn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('bi-x');
                        icon.classList.add('bi-list');
                    }
                }
            });
        }
    }

    // ============================================
    // Back to Top Button
    // ============================================
    function initBackToTop() {
        // Create back to top button
        const button = document.createElement('button');
        button.innerHTML = '<i class="bi bi-arrow-up"></i>';
        button.className = 'back-to-top';
        button.setAttribute('aria-label', 'Back to top');
        
        button.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        document.body.appendChild(button);
        
        // Show/hide button based on scroll position
        window.addEventListener('scroll', debounce(function() {
            if (window.pageYOffset > 300) {
                button.classList.add('visible');
            } else {
                button.classList.remove('visible');
            }
        }, 100));
    }

    // ============================================
    // Utility: Debounce Function
    // ============================================
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }


})();

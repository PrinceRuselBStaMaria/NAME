(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();
    
    
    // Initiate the wowjs
    new WOW().init();


    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 40) {
            $('.navbar').addClass('sticky-top');
        } else {
            $('.navbar').removeClass('sticky-top');
        }
    });
    
    // Dropdown on mouse hover
    const $dropdown = $(".dropdown");
    const $dropdownToggle = $(".dropdown-toggle");
    const $dropdownMenu = $(".dropdown-menu");
    const showClass = "show";
    
    $(window).on("load resize", function() {
        if (this.matchMedia("(min-width: 992px)").matches) {
            $dropdown.hover(
            function() {
                const $this = $(this);
                $this.addClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "true");
                $this.find($dropdownMenu).addClass(showClass);
            },
            function() {
                const $this = $(this);
                $this.removeClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "false");
                $this.find($dropdownMenu).removeClass(showClass);
            }
            );
        } else {
            $dropdown.off("mouseenter mouseleave");
        }
    });
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


    // Date and time picker
    $('.date').datetimepicker({
        format: 'L'
    });
    $('.time').datetimepicker({
        format: 'LT'
    });


    // Image comparison
    $(".twentytwenty-container").twentytwenty({});


    // Price carousel
    $(".price-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1500,
        margin: 45,
        dots: false,
        loop: true,
        nav : true,
        navText : [
            '<i class="bi bi-arrow-left"></i>',
            '<i class="bi bi-arrow-right"></i>'
        ],
        responsive: {
            0:{
                items:1
            },
            768:{
                items:2
            }
        }
    });


    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        items: 1,
        dots: false,
        loop: true,
        nav : true,
        navText : [
            '<i class="bi bi-arrow-left"></i>',
            '<i class="bi bi-arrow-right"></i>'
        ],
    });
    
})(jQuery);

// Lerp and clamp utility functions
const lerp = (f0, f1, t) => (1 - t) * f0 + t * f1;
const clamp = (val, min, max) => Math.max(min, Math.min(val, max));

// Add these functions inside IIFE to avoid global scope pollution
$(function() {
    const lerp = (f0, f1, t) => (1 - t) * f0 + t * f1
    const clamp = (val, min, max) => Math.max(min, Math.min(val, max))
    
    class DragScroll {
      constructor(obj) {
        this.$el = document.querySelector(obj.el)
        this.$wrap = this.$el.querySelector(obj.wrap)
        this.$items = this.$el.querySelectorAll(obj.item)
        this.$bar = this.$el.querySelector(obj.bar)
        this.init()
      }
      
      init() {
        this.progress = 0
        this.speed = 0
        this.oldX = 0
        this.x = 0
        this.playrate = 0
        //
        this.bindings()
        this.events()
        this.calculate()
        this.raf()
      }
      
      bindings() {
        [
         'events', 
         'calculate',
         'raf', 
         'handleWheel', 
         'move', 
         'raf', 
         'handleTouchStart',
         'handleTouchMove', 
         'handleTouchEnd'
        ].forEach(i => { this[i] = this[i].bind(this) })
      }
      
      calculate() {
        this.progress = 0
        this.wrapWidth = this.$items[0].clientWidth * this.$items.length
        this.$wrap.style.width = `${this.wrapWidth}px`
        this.maxScroll = this.wrapWidth - this.$el.clientWidth
      }
      
      handleWheel(e) {
        this.progress += e.deltaY
        this.move()
      }
      
      handleTouchStart(e) {
        e.preventDefault()
        this.dragging = true
        this.startX = e.clientX || e.touches[0].clientX
        this.$el.classList.add('dragging')
      }
    
      handleTouchMove(e) {
        if (!this.dragging) return false
        const x = e.clientX || e.touches[0].clientX
        this.progress += (this.startX - x) * 2.5
        this.startX = x
        this.move()
      }
    
      handleTouchEnd() {
        this.dragging = false
        this.$el.classList.remove('dragging')
      }
      
      move() {
        // Add circular navigation
        if (this.progress >= this.maxScroll) {
            this.progress = 0; // Reset to start
        } else if (this.progress < 0) {
            this.progress = this.maxScroll; // Go to end
        }
        this.progress = clamp(this.progress, 0, this.maxScroll);
      }
      
      events() {
        window.addEventListener('resize', this.calculate)
        window.addEventListener('wheel', this.handleWheel)
        //
        this.$el.addEventListener('touchstart', this.handleTouchStart)
        window.addEventListener('touchmove', this.handleTouchMove)
        window.addEventListener('touchend', this.handleTouchEnd)
        //
        window.addEventListener('mousedown', this.handleTouchStart)
        window.addEventListener('mousemove', this.handleTouchMove)
        window.addEventListener('mouseup', this.handleTouchEnd)
        document.body.addEventListener('mouseleave', this.handleTouchEnd)
        
        // Add button navigation
        const prevBtn = document.querySelector('.carousel-control-prev');
        const nextBtn = document.querySelector('.carousel-control-next');
        
        prevBtn.addEventListener('click', () => this.handleNavigation('prev'));
        nextBtn.addEventListener('click', () => this.handleNavigation('next'));
      }
      
      raf() {
        requestAnimationFrame(this.raf);
        
        // Smooth transition
        this.x = lerp(this.x, this.progress, 0.1);
        
        // Calculate playrate with boundary check
        this.playrate = clamp(this.x / this.maxScroll, 0, 1);
        
        // Transform with boundary check
        const translateX = -this.x % this.maxScroll;
        this.$wrap.style.transform = `translateX(${translateX}px)`;
        this.$bar.style.transform = `scaleX(${.18 + this.playrate * .82})`;
        
        // Calculate speed with smoothing
        this.speed = Math.min(100, this.oldX - this.x);
        this.oldX = this.x;
        
        // Apply scale animation with limits
        this.scale = lerp(this.scale, clamp(this.speed, -100, 100), 0.1);
        this.$items.forEach(i => {
            const scaleValue = clamp(1 - Math.abs(this.speed) * 0.002, 0.95, 1);
            const scaleXValue = clamp(1 + Math.abs(this.speed) * 0.004, 1, 1.1);
            i.style.transform = `scale(${scaleValue})`;
            i.querySelector('img').style.transform = `scaleX(${scaleXValue})`;
        });
      }

      // Add button navigation
      handleNavigation(direction) {
        const step = this.$items[0].clientWidth;
        this.progress += direction === 'next' ? step : -step;
        this.move();
      }
    }
    
    
    /*--------------------
    Instances
    --------------------*/
    const scroll = new DragScroll({
      el: '.carousel',
      wrap: '.carousel--wrap',
      item: '.carousel--item',
      bar: '.carousel--progress-bar',
    })
    
    
    /*--------------------
    One raf to rule em all
    --------------------*/
    const raf = () => {
      requestAnimationFrame(raf)
      scroll.raf()
    }
    raf()
    
});
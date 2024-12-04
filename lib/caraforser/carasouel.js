// carousel.js
$(function() {
    // Helper functions
    const lerp = (f0, f1, t) => (1 - t) * f0 + t * f1;
    const clamp = (val, min, max) => Math.max(min, Math.min(val, max));

    class DragScroll {
        constructor(obj) {
            this.$el = document.querySelector(obj.el);
            this.$wrap = this.$el.querySelector(obj.wrap);
            this.$items = this.$el.querySelectorAll(obj.item);
            this.$bar = this.$el.querySelector(obj.bar);
            this.init();
        }

        init() {
            this.progress = 0;
            this.speed = 0;
            this.oldX = 0;
            this.x = 0;
            this.playrate = 0;
            this.scale = 1;
            this.bindings();
            this.events();
            this.calculate();
            this.raf();
        }

        // ... [Previous methods remain the same]

        raf() {
            this.x = lerp(this.x, this.progress, 0.1);
            this.playrate = this.x / this.maxScroll;

            this.$wrap.style.transform = `translateX(${-this.x}px)`;
            this.$bar.style.transform = `scaleX(${.18 + this.playrate * .82})`;

            this.speed = Math.min(100, this.oldX - this.x);
            this.oldX = this.x;

            this.scale = lerp(this.scale, this.speed, 0.1);
            this.$items.forEach(i => {
                i.style.transform = `scale(${1 - Math.abs(this.speed) * 0.002})`;
                i.querySelector('img').style.transform = `scaleX(${1 + Math.abs(this.speed) * 0.004})`;
            });
        }
    }

    // Initialize carousel
    const scroll = new DragScroll({
        el: '.carousel',
        wrap: '.carousel--wrap',
        item: '.carousel--item',
        bar: '.carousel--progress-bar',
    });

    // Animation loop
    const raf = () => {
        requestAnimationFrame(raf);
        scroll.raf();
    }
    raf();
});
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('imageCarousel');
    const progressBar = document.querySelector('.carousel--progress-bar');
    
    carousel.addEventListener('slide.bs.carousel', function (e) {
      const itemsCount = document.querySelectorAll('.carousel-item').length;
      const progress = ((e.to + 1) / itemsCount) * 100;
      progressBar.style.width = progress + '%';
    });
  });
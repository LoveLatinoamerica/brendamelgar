/* ========================================
   Brenda Melgar — Main JavaScript
   ======================================== */

document.addEventListener('DOMContentLoaded', () => {

  /* ---------- Navigation ---------- */
  const nav = document.getElementById('nav');
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');
  const navLinks = document.querySelectorAll('.nav__link');

  const handleNavScroll = () => {
    nav.classList.toggle('nav--scrolled', window.scrollY > 50);
  };
  window.addEventListener('scroll', handleNavScroll, { passive: true });
  handleNavScroll();

  navToggle.addEventListener('click', () => {
    const isOpen = navMenu.classList.toggle('nav__menu--open');
    navToggle.classList.toggle('nav__toggle--active', isOpen);
    document.body.classList.toggle('menu-open', isOpen);
  });

  navLinks.forEach(link => {
    link.addEventListener('click', () => {
      navMenu.classList.remove('nav__menu--open');
      navToggle.classList.remove('nav__toggle--active');
      document.body.classList.remove('menu-open');
    });
  });

  /* ---------- Smooth Scroll ---------- */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', (e) => {
      e.preventDefault();
      const target = document.querySelector(anchor.getAttribute('href'));
      if (target) {
        const offset = nav.offsetHeight;
        const top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });
      }
    });
  });

  /* ---------- Fade-in on Scroll ---------- */
  const animated = document.querySelectorAll('.animate-fade-up');

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animated');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });

    animated.forEach(el => observer.observe(el));
  } else {
    animated.forEach(el => el.classList.add('animated'));
  }

  /* ---------- Counter Animation ---------- */
  const counters = document.querySelectorAll('.cifra__number');

  const animateCounter = (el) => {
    const target = parseInt(el.dataset.target, 10);
    const duration = 2200;
    const start = performance.now();

    const update = (now) => {
      const progress = Math.min((now - start) / duration, 1);
      const ease = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(ease * target);
      if (progress < 1) {
        requestAnimationFrame(update);
      } else {
        el.textContent = target;
      }
    };
    requestAnimationFrame(update);
  };

  if ('IntersectionObserver' in window) {
    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    counters.forEach(el => counterObserver.observe(el));
  }

  /* ---------- Testimonial Carousel ---------- */
  const testimonios = document.querySelectorAll('.testimonio');
  const dots = document.querySelectorAll('.testimonios__dot');
  const prevBtn = document.getElementById('prevTestimonio');
  const nextBtn = document.getElementById('nextTestimonio');
  let current = 0;

  const show = (index) => {
    testimonios.forEach(t => t.classList.remove('active'));
    dots.forEach(d => d.classList.remove('active'));
    current = (index + testimonios.length) % testimonios.length;
    testimonios[current].classList.add('active');
    dots[current].classList.add('active');
  };

  if (prevBtn && nextBtn) {
    prevBtn.addEventListener('click', () => show(current - 1));
    nextBtn.addEventListener('click', () => show(current + 1));
  }

  dots.forEach((dot, i) => dot.addEventListener('click', () => show(i)));

  setInterval(() => show(current + 1), 7000);

  /* ---------- Portfolio Filter ---------- */
  const filterBtns = document.querySelectorAll('.portfolio__filter-btn');
  const items = document.querySelectorAll('.portfolio__item');

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;

      items.forEach(item => {
        if (filter === 'all' || item.dataset.category === filter) {
          item.style.display = '';
          requestAnimationFrame(() => {
            item.style.opacity = '1';
            item.style.transform = 'scale(1)';
          });
        } else {
          item.style.opacity = '0';
          item.style.transform = 'scale(0.97)';
          setTimeout(() => { item.style.display = 'none'; }, 400);
        }
      });
    });
  });

  /* ---------- Contact Form ---------- */
  const form = document.getElementById('contactForm');
  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const btn = form.querySelector('button[type="submit"]');
      const original = btn.textContent;
      btn.textContent = 'Enviando...';
      btn.disabled = true;
      btn.style.opacity = '0.6';

      const data = new URLSearchParams({
        nombre: form.nombre.value,
        email: form.email.value,
        servicio: form.servicio.value,
        mensaje: form.mensaje.value
      });

      fetch('api/contacto.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: data.toString()
      }).then(() => {
        btn.textContent = 'Mensaje enviado';
        form.reset();
      }).catch(() => {
        btn.textContent = 'Error al enviar';
      }).finally(() => {
        setTimeout(() => {
          btn.textContent = original;
          btn.disabled = false;
          btn.style.opacity = '';
        }, 3000);
      });
    });
  }

  /* ---------- Book Gallery ---------- */
  const bookImages = {
    1: ['Portada', 'Interior 1', 'Interior 2', 'Contraportada'],
    2: ['Portada', 'Interior 1', 'Interior 2', 'Contraportada']
  };

  const bookTitles = {
    'weddpreneur': 'Weddpreneur',
    '101-historias': '101 Historias de Bodas'
  };

  document.querySelectorAll('.book__thumb').forEach(thumb => {
    thumb.addEventListener('click', () => {
      const bookId = thumb.dataset.book;
      const index = parseInt(thumb.dataset.index, 10);
      const mainImage = document.getElementById('bookMain' + bookId);
      const src = thumb.dataset.src;
      const alt = thumb.dataset.alt;

      if (src) {
        // Real image
        mainImage.innerHTML = `<img src="${src}" alt="${alt}" class="book__img">`;
      } else {
        // Placeholder fallback
        const label = bookImages[bookId][index];
        const bookName = bookId === '1' ? 'Weddpreneur' : '101 Historias';
        mainImage.innerHTML = `<div class="book__placeholder"><span>${bookName}</span><small>${label}</small></div>`;
      }

      // Update active thumb
      const siblings = thumb.closest('.book__thumbs').querySelectorAll('.book__thumb');
      siblings.forEach(s => s.classList.remove('active'));
      thumb.classList.add('active');
    });
  });

  /* ---------- Download Modal ---------- */
  const modal = document.getElementById('downloadModal');
  const modalBackdrop = document.getElementById('modalBackdrop');
  const modalClose = document.getElementById('modalClose');
  const downloadForm = document.getElementById('downloadForm');
  const modalBookTitle = document.getElementById('modalBookTitle');
  const downloadBookId = document.getElementById('downloadBookId');
  const modalSuccess = document.getElementById('modalSuccess');
  const emailError = document.getElementById('emailError');
  const privacyError = document.getElementById('privacyError');

  const openModal = (bookSlug) => {
    downloadBookId.value = bookSlug;
    modalBookTitle.textContent = bookTitles[bookSlug] || bookSlug;

    // Reset state
    downloadForm.reset();
    downloadForm.classList.remove('hidden');
    modal.querySelector('.modal__header').classList.remove('hidden');
    modalSuccess.classList.remove('visible');
    emailError.classList.remove('visible');
    privacyError.classList.remove('visible');

    modal.classList.add('active');
    document.body.classList.add('menu-open');
  };

  const closeModal = () => {
    modal.classList.remove('active');
    document.body.classList.remove('menu-open');
  };

  // Open modal from download buttons
  document.querySelectorAll('[data-download]').forEach(btn => {
    btn.addEventListener('click', () => openModal(btn.dataset.download));
  });

  if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);
  if (modalClose) modalClose.addEventListener('click', closeModal);

  // Close on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
  });

  // Form validation & submit
  if (downloadForm) {
    downloadForm.addEventListener('submit', (e) => {
      e.preventDefault();
      let valid = true;

      const email = document.getElementById('dlEmail');
      const privacy = document.getElementById('dlPrivacy');

      // Reset errors
      emailError.classList.remove('visible');
      privacyError.classList.remove('visible');

      // Validate email
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!email.value || !emailPattern.test(email.value)) {
        emailError.classList.add('visible');
        valid = false;
      }

      // Validate privacy
      if (!privacy.checked) {
        privacyError.classList.add('visible');
        valid = false;
      }

      if (!valid) return;

      const bookSlug = downloadBookId.value;
      const nombre = document.getElementById('dlNombre').value || '';

      // Save download to backend
      fetch('api/descarga.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'libro=' + encodeURIComponent(bookSlug) +
              '&email=' + encodeURIComponent(email.value) +
              '&nombre=' + encodeURIComponent(nombre)
      }).catch(function(){});

      // Trigger file download
      const archivos = {
        'weddpreneur': 'descargas/weddpreneur-2026.pdf'
      };
      if (archivos[bookSlug]) {
        const a = document.createElement('a');
        a.href = archivos[bookSlug];
        a.download = '';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
      }

      // Show success
      downloadForm.classList.add('hidden');
      modal.querySelector('.modal__header').classList.add('hidden');
      modalSuccess.classList.add('visible');

      // Auto-close after 3s
      setTimeout(closeModal, 3000);
    });
  }

  /* ---------- Speaking Modal ---------- */
  const speakingModal = document.getElementById('speakingModal');
  const speakingForm = document.getElementById('speakingForm');
  const speakingSuccess = document.getElementById('speakingSuccess');
  const openSpeakingBtn = document.getElementById('openSpeakingForm');

  const openSpeakingModal = () => {
    speakingForm.reset();
    speakingForm.classList.remove('hidden');
    speakingModal.querySelector('#speakingHeader').classList.remove('hidden');
    speakingSuccess.classList.remove('visible');
    speakingModal.classList.add('active');
    document.body.classList.add('menu-open');
  };

  const closeSpeakingModal = () => {
    speakingModal.classList.remove('active');
    document.body.classList.remove('menu-open');
  };

  if (openSpeakingBtn) openSpeakingBtn.addEventListener('click', openSpeakingModal);
  document.getElementById('speakingBackdrop').addEventListener('click', closeSpeakingModal);
  document.getElementById('speakingClose').addEventListener('click', closeSpeakingModal);

  if (speakingForm) {
    speakingForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const data = new URLSearchParams({
        nombre: document.getElementById('spkNombre').value,
        email: document.getElementById('spkEmail').value,
        telefono: document.getElementById('spkTelefono').value,
        pais: document.getElementById('spkPais').value,
        fecha: document.getElementById('spkFecha').value,
        asistentes: document.getElementById('spkAsistentes').value
      });

      fetch('api/speaking.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: data.toString()
      }).catch(function(){});

      speakingForm.classList.add('hidden');
      speakingModal.querySelector('#speakingHeader').classList.add('hidden');
      speakingSuccess.classList.add('visible');

      setTimeout(closeSpeakingModal, 3000);
    });
  }

  // Close any modal on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (speakingModal.classList.contains('active')) closeSpeakingModal();
    }
  });

  /* ---------- Active Nav on Scroll ---------- */
  const sections = document.querySelectorAll('section[id]');

  const highlightNav = () => {
    const scrollY = window.scrollY + nav.offsetHeight + 100;
    sections.forEach(section => {
      const top = section.offsetTop;
      const height = section.offsetHeight;
      const id = section.getAttribute('id');
      const link = document.querySelector(`.nav__link[href="#${id}"]`);
      if (link) {
        link.classList.toggle('nav__link--active', scrollY >= top && scrollY < top + height);
      }
    });
  };

  window.addEventListener('scroll', highlightNav, { passive: true });

});

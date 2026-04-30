/* =============================================
   KRIC リニューアルデザイン - main.js
   ============================================= */
(function () {
  'use strict';

  var menuBtn   = document.getElementById('menuBtn');
  var gnav      = document.getElementById('gnav');
  function openMenu() {
    var sw = window.innerWidth - document.documentElement.clientWidth;
    if (sw > 0) document.body.style.paddingRight = sw + 'px';
    document.body.style.overflow = 'hidden';
    gnav.classList.add('is-open');
    gnav.setAttribute('aria-hidden', 'false');
    menuBtn.classList.add('is-open');
    menuBtn.setAttribute('aria-label', 'メニューを閉じる');
  }

  function closeMenu() {
    gnav.classList.remove('is-open');
    gnav.setAttribute('aria-hidden', 'true');
    menuBtn.classList.remove('is-open');
    menuBtn.setAttribute('aria-label', 'メニューを開く');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  }

  menuBtn.addEventListener('click', function () {
    gnav.classList.contains('is-open') ? closeMenu() : openMenu();
  });

  /* gnav背景クリックで閉じる */
  gnav.addEventListener('click', function (e) {
    if (e.target === gnav) closeMenu();
  });

  gnav.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', closeMenu);
  });

  gnav.querySelectorAll('.gnav-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var isOpen = btn.getAttribute('aria-expanded') === 'true';
      btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
      var child = btn.nextElementSibling;
      if (child) child.classList.toggle('is-open', !isOpen);

      var arrow = btn.querySelector('.gnav-arrow');
      if (arrow) {
        arrow.classList.add('is-changing');
        setTimeout(function () {
          arrow.textContent = isOpen ? '＋' : 'ー';
          arrow.classList.remove('is-changing');
        }, 150);
      }
    });
  });

  /* メディアスライダー（SP） */
  var mediaGrid = document.getElementById('mediaGrid');
  var dotsWrap  = document.getElementById('mediaDotsSp');
  var prevBtn   = document.getElementById('mediaSliderPrev');
  var nextBtn   = document.getElementById('mediaSliderNext');
  if (mediaGrid && dotsWrap) {
    var mediaCards = mediaGrid.querySelectorAll('.media-card');
    mediaCards.forEach(function (_, i) {
      var dot = document.createElement('span');
      dot.className = 'media-dot' + (i === 0 ? ' is-active' : '');
      dot.setAttribute('tabindex', '-1');
      dot.addEventListener('click', function () { scrollToCard(i); });
      dotsWrap.appendChild(dot);
    });
    function getCardWidth() {
      return mediaCards[0] ? mediaCards[0].offsetWidth + 12 : 0;
    }
    function scrollToCard(i) {
      mediaGrid.scrollTo({ left: getCardWidth() * i, behavior: 'smooth' });
    }
    function updateDots() {
      var atEnd = mediaGrid.scrollLeft + mediaGrid.clientWidth >= mediaGrid.scrollWidth - 2;
      var idx = atEnd
        ? mediaCards.length - 1
        : Math.round(mediaGrid.scrollLeft / (getCardWidth() || 1));
      dotsWrap.querySelectorAll('.media-dot').forEach(function (d, i) {
        d.classList.toggle('is-active', i === idx);
      });
    }
    mediaGrid.addEventListener('scroll', updateDots);
    if (prevBtn) prevBtn.addEventListener('click', function () {
      var idx = Math.round(mediaGrid.scrollLeft / (getCardWidth() || 1));
      scrollToCard(Math.max(0, idx - 1));
    });
    if (nextBtn) nextBtn.addEventListener('click', function () {
      var idx = Math.round(mediaGrid.scrollLeft / (getCardWidth() || 1));
      scrollToCard(Math.min(mediaCards.length - 1, idx + 1));
    });
  }

  /* フッターナビ折りたたみ（SP） */
  document.querySelectorAll('.footer-nav-parent').forEach(function (link) {
    var col = link.parentElement;
    if (!col.querySelector('ul')) return;
    if (link.getAttribute('href') === '#join') return;
    col.classList.add('has-children');
    link.addEventListener('click', function (e) {
      if (window.innerWidth > 768) return;
      e.preventDefault();
      col.classList.toggle('is-open');
    });
  });

  var pageTopBtn = document.querySelector('.page-top');
  if (pageTopBtn) {
    pageTopBtn.addEventListener('click', function (e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  var header = document.getElementById('header');
  window.addEventListener('scroll', function () {
    header.classList.toggle('is-scrolled', window.scrollY > 60);
  });

  var fadeEls = document.querySelectorAll(
    '.media-card, .contents-item, .act-item, .news-item, .pride-photo, .pride-text-col,' +
    '.kric-media-heading, .news-heading, .news-right, .member-intro-heading, .member-intro-inner,' +
    '.member-search-card, .activities-heading, .act-photo,' +
    '.contact-cta-photo, .contact-cta-inner'
  );
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });
    fadeEls.forEach(function (el) { el.classList.add('fade-el'); io.observe(el); });
  } else {
    fadeEls.forEach(function (el) { el.classList.add('is-visible'); });
  }

})();

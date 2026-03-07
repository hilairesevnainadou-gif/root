// ============================================
// Mobile Navigation & Gestures
// ============================================

document.addEventListener('DOMContentLoaded', () => {
  initPullToRefresh();
  initSwipeActions();
  initBottomSheet();
  initTouchFeedback();
  initInfiniteScroll();
});

// Pull to refresh
function initPullToRefresh() {
  let startY = 0;
  let currentY = 0;
  let isPulling = false;
  const threshold = 80;

  const refreshIndicator = document.createElement('div');
  refreshIndicator.className = 'pull-refresh';
  refreshIndicator.innerHTML = '<div class="pull-refresh-spinner"></div>';
  document.body.prepend(refreshIndicator);

  document.addEventListener('touchstart', (e) => {
    if (window.scrollY === 0) {
      startY = e.touches[0].clientY;
      isPulling = true;
    }
  }, { passive: true });

  document.addEventListener('touchmove', (e) => {
    if (!isPulling) return;

    currentY = e.touches[0].clientY;
    const diff = currentY - startY;

    if (diff > 0 && diff < threshold * 2) {
      refreshIndicator.style.transform = `translateY(${Math.min(diff - 60, 0)}px)`;
      refreshIndicator.classList.add('visible');
    }
  }, { passive: true });

  document.addEventListener('touchend', () => {
    if (!isPulling) return;

    const diff = currentY - startY;

    if (diff > threshold) {
      // Trigger refresh
      location.reload();
    } else {
      refreshIndicator.classList.remove('visible');
      refreshIndicator.style.transform = '';
    }

    isPulling = false;
  });
}

// Swipe actions on list items
function initSwipeActions() {
  const swipeItems = document.querySelectorAll('.swipe-item');

  swipeItems.forEach(item => {
    let startX = 0;
    let currentX = 0;
    let isSwiping = false;

    item.addEventListener('touchstart', (e) => {
      startX = e.touches[0].clientX;
      isSwiping = true;
    }, { passive: true });

    item.addEventListener('touchmove', (e) => {
      if (!isSwiping) return;

      currentX = e.touches[0].clientX;
      const diff = startX - currentX;

      if (diff > 50 && diff < 200) {
        item.classList.add('swiped');
      } else if (diff < -50) {
        item.classList.remove('swiped');
      }
    }, { passive: true });

    item.addEventListener('touchend', () => {
      isSwiping = false;
    });

    // Close on click outside
    item.addEventListener('click', (e) => {
      if (item.classList.contains('swiped')) {
        const isAction = e.target.closest('.swipe-action');
        if (!isAction) {
          item.classList.remove('swiped');
        }
      }
    });
  });
}

// Bottom sheet modal
function initBottomSheet() {
  document.querySelectorAll('[data-bottom-sheet]').forEach(trigger => {
    trigger.addEventListener('click', () => {
      const target = document.querySelector(trigger.dataset.bottomSheet);
      const overlay = document.querySelector('.bottom-sheet-overlay');

      if (target) {
        target.classList.add('open');
        overlay?.classList.add('open');
      }
    });
  });

  // Close on overlay click
  document.querySelectorAll('.bottom-sheet-overlay').forEach(overlay => {
    overlay.addEventListener('click', () => {
      document.querySelectorAll('.bottom-sheet.open').forEach(sheet => {
        sheet.classList.remove('open');
      });
      overlay.classList.remove('open');
    });
  });
}

// Touch feedback
function initTouchFeedback() {
  document.querySelectorAll('.btn, .list-item, .card').forEach(el => {
    el.classList.add('touch-feedback');
  });
}

// Infinite scroll
function initInfiniteScroll() {
  const infiniteContainers = document.querySelectorAll('[data-infinite]');

  infiniteContainers.forEach(container => {
    let loading = false;

    const observer = new IntersectionObserver((entries) => {
      const entry = entries[0];

      if (entry.isIntersecting && !loading) {
        loading = true;
        const nextPage = container.dataset.nextPage;

        if (nextPage) {
          loadMore(container, nextPage).then(() => {
            loading = false;
          });
        }
      }
    }, { rootMargin: '100px' });

    const sentinel = document.createElement('div');
    sentinel.className = 'scroll-sentinel';
    sentinel.style.height = '10px';
    container.appendChild(sentinel);

    observer.observe(sentinel);
  });
}

async function loadMore(container, url) {
  try {
    const response = await fetch(url, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    const html = await response.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const newItems = doc.querySelectorAll('[data-infinite-item]');

    newItems.forEach(item => {
      container.insertBefore(item, container.querySelector('.scroll-sentinel'));
    });

    // Update next page URL
    const nextLink = doc.querySelector('[data-next-page]');
    if (nextLink) {
      container.dataset.nextPage = nextLink.dataset.nextPage;
    } else {
      delete container.dataset.nextPage;
      container.querySelector('.scroll-sentinel')?.remove();
    }

  } catch (error) {
    console.error('Failed to load more:', error);
  }
}

// Hamburger menu for admin mobile
function toggleAdminSidebar() {
  document.querySelector('.admin-sidebar')?.classList.toggle('open');
}

// Close sidebar on route change (mobile)
window.addEventListener('popstate', () => {
  document.querySelector('.admin-sidebar')?.classList.remove('open');
});

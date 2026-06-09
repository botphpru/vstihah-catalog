document.addEventListener('DOMContentLoaded', function () {
    const navToggle = document.querySelector('.nav-toggle');
    const navWrap = document.querySelector('#siteNavWrap');
    const mobileQuery = window.matchMedia('(max-width: 991.98px)');

    if (!navToggle || !navWrap) {
        return;
    }

    function closeMobileMenu() {
        navToggle.classList.remove('is-open');
        navToggle.setAttribute('aria-expanded', 'false');
        navWrap.classList.remove('is-open');

        navWrap.querySelectorAll('.site-nav__item.is-open').forEach(function (item) {
            item.classList.remove('is-open');
        });
    }

    navToggle.addEventListener('click', function () {
        const isOpen = navWrap.classList.toggle('is-open');

        navToggle.classList.toggle('is-open', isOpen);
        navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    navWrap.querySelectorAll('.has-dropdown > .site-nav__link').forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (!mobileQuery.matches) {
                return;
            }

            event.preventDefault();

            const item = link.closest('.site-nav__item');
            const parentList = item.parentElement;
            const isOpen = item.classList.contains('is-open');

            parentList.querySelectorAll(':scope > .site-nav__item.is-open').forEach(function (openedItem) {
                if (openedItem !== item) {
                    openedItem.classList.remove('is-open');
                }
            });

            item.classList.toggle('is-open', !isOpen);
        });
    });

    document.addEventListener('click', function (event) {
        if (!mobileQuery.matches) {
            return;
        }

        const clickedInsideHeader = event.target.closest('.site-header');

        if (!clickedInsideHeader) {
            closeMobileMenu();
        }
    });

    window.addEventListener('resize', function () {
        if (!mobileQuery.matches) {
            closeMobileMenu();
        }
    });
});

document.addEventListener("DOMContentLoaded", function() {
    const banner = document.getElementById('cookieConsentBanner');
    const acceptBtn = document.getElementById('acceptCookiesBtn');
    const cookieName = 'cookie_consent_accepted';
    function getCookie(name) {
        let matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }
    if (!getCookie(cookieName)) {
        banner.classList.remove('d-none');
    }
    acceptBtn.addEventListener('click', function() {
        const maxAge = 2592000;
        document.cookie = `${cookieName}=true; max-age=${maxAge}; path=/; secure; samesite=lax`;
        banner.classList.add('d-none');
    });
});

/**
 * Sort dropdown handler + smooth scroll to poems list
 */
document.addEventListener('DOMContentLoaded', function() {

    // ========== 1. Обработчик смены сортировки ==========
    const sortSelect = document.getElementById('poems_sort');
    if (sortSelect) {
        let sortTimeout;

        sortSelect.addEventListener('change', function(e) {
            // Визуальный фидбек
            this.classList.add('sort-changing');
            setTimeout(() => this.classList.remove('sort-changing'), 300);

            clearTimeout(sortTimeout);
            sortTimeout = setTimeout(() => {
                const sortValue = e.target.value;
                const basePath = window.location.pathname;
                const newUrl = `${basePath}?sort=${encodeURIComponent(sortValue)}`;

                // Сохраняем позицию скролла в sessionStorage для плавного возврата
                sessionStorage.setItem('scrollToPoems', 'true');

                if (window.history.replaceState) {
                    window.history.replaceState(null, '', newUrl);
                }
                window.location.href = newUrl;
            }, 150);
        });
    }

    // ========== 2. Плавный скролл к #poems_wrap при загрузке ==========
    // Проверяем: есть ли ?sort в URL И есть ли целевой блок
    const urlParams = new URLSearchParams(window.location.search);
    const poemsWrap = document.getElementById('poems_wrap');

    if (urlParams.has('sort') && poemsWrap) {
        // Небольшая задержка, чтобы контент точно отрисовался
        setTimeout(() => {
            // Проверяем, не скроллили ли уже (защита от двойного срабатывания)
            const hasScrolled = sessionStorage.getItem('poemsScrolled');
            if (!hasScrolled) {
                poemsWrap.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                poemsWrap.classList.add('highlight-scroll');
                setTimeout(() => poemsWrap.classList.remove('highlight-scroll'), 800);
                sessionStorage.setItem('poemsScrolled', 'true');

                // Сбрасываем флаг через 2 секунды (на случай быстрого перехода назад)
                setTimeout(() => sessionStorage.removeItem('poemsScrolled'), 2000);
            }
        }, 100);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) return;

    // Делегирование событий для всех кнопок рейтинга
    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('[data-rating-id]');
        if (!btn || btn.classList.contains('loading') || btn.classList.contains('disabled')) return;

        const match = btn.dataset.ratingId.match(/(minus|plus)_(\d+)/);
        if (!match) return;

        const poemId = match[2];
        const value = match[1] === 'plus' ? 1 : -1;

        // UI: состояние загрузки
        btn.classList.add('loading');
        btn.style.pointerEvents = 'none';

        try {
            const response = await fetch('/api/poems/update_rating', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ poem_id: poemId, value: value })
            });

            const data = await response.json();
            btn.classList.remove('loading');
            btn.style.pointerEvents = '';

            if (data.success) {
                // Обновляем рейтинг в DOM
                const container = btn.closest('.poem-footer');
                const ratingEl = container?.querySelector('.rating-value');
                if (ratingEl) {
                    ratingEl.textContent = data.new_rating;
                    // Микро-анимация изменения
                    ratingEl.style.transform = 'scale(1.25)';
                    ratingEl.style.color = 'var(--accent-2)';
                    setTimeout(() => {
                        ratingEl.style.transform = '';
                        ratingEl.style.color = '';
                    }, 300);
                }
            } else {
                // Обработка ошибки
                btn.classList.add('disabled');
                showError(btn, data.error || 'Ошибка оценки');
            }
        } catch (err) {
            btn.classList.remove('loading');
            btn.style.pointerEvents = '';
            btn.classList.add('disabled');
            showError(btn, 'Не удалось отправить оценку');
        }
    });

    // Вспомогательная функция для вывода ошибки
    function showError(btn, message) {
        const errorSpan = document.createElement('span');
        errorSpan.className = 'rating-error';
        errorSpan.textContent = message;
        btn.parentNode.insertBefore(errorSpan, btn.nextSibling);
        setTimeout(() => errorSpan.remove(), 3000);
    }
});
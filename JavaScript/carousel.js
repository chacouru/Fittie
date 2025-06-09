// カルーセル機能のJavaScript
class ProductCarousel {
    constructor(carouselId) {
        this.carousel = document.getElementById(carouselId);
        this.track = this.carousel;
        this.container = this.carousel.closest('.carousel-container');
        this.prevBtn = this.container.querySelector('.carousel-nav.prev');
        this.nextBtn = this.container.querySelector('.carousel-nav.next');
        this.cardWidth = 300; // カード幅 + gap
        this.scrollAmount = this.cardWidth * 2; // 一度に2枚分スクロール
        
        this.init();
    }
    
    init() {
        // ボタンイベントの設定
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.scrollPrev());
        }
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.scrollNext());
        }
        
        // スクロールイベントでボタンの状態を更新
        this.track.addEventListener('scroll', () => this.updateButtonStates());
        
        // 初期状態のボタン更新
        this.updateButtonStates();
        
        // リサイズ時の対応
        window.addEventListener('resize', () => this.handleResize());
        
        // タッチスワイプ対応
        this.addTouchSupport();
    }
    
    scrollPrev() {
        this.track.scrollBy({
            left: -this.scrollAmount,
            behavior: 'smooth'
        });
    }
    
    scrollNext() {
        this.track.scrollBy({
            left: this.scrollAmount,
            behavior: 'smooth'
        });
    }
    
    updateButtonStates() {
        if (!this.prevBtn || !this.nextBtn) return;
        
        const scrollLeft = this.track.scrollLeft;
        const maxScroll = this.track.scrollWidth - this.track.clientWidth;
        
        // 左端にいる場合は前ボタンを無効化
        this.prevBtn.disabled = scrollLeft <= 0;
        
        // 右端にいる場合は次ボタンを無効化
        this.nextBtn.disabled = scrollLeft >= maxScroll - 1; // 1px の誤差を許容
    }
    
    handleResize() {
        // ウィンドウサイズ変更時にスクロール量を調整
        const containerWidth = this.container.clientWidth;
        if (containerWidth <= 480) {
            this.cardWidth = 220; // モバイル用
            this.scrollAmount = this.cardWidth * 1.5;
        } else if (containerWidth <= 768) {
            this.cardWidth = 260; // タブレット用
            this.scrollAmount = this.cardWidth * 2;
        } else {
            this.cardWidth = 300; // デスクトップ用
            this.scrollAmount = this.cardWidth * 2;
        }
        
        this.updateButtonStates();
    }
    
    addTouchSupport() {
        let startX = 0;
        let scrollLeft = 0;
        let isDragging = false;
        
        this.track.addEventListener('touchstart', (e) => {
            startX = e.touches[0].pageX - this.track.offsetLeft;
            scrollLeft = this.track.scrollLeft;
            isDragging = true;
        });
        
        this.track.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            e.preventDefault();
            const x = e.touches[0].pageX - this.track.offsetLeft;
            const walk = (x - startX) * 2; // スクロール速度
            this.track.scrollLeft = scrollLeft - walk;
        });
        
        this.track.addEventListener('touchend', () => {
            isDragging = false;
        });
        
        // マウスドラッグサポート（デスクトップ用）
        this.track.addEventListener('mousedown', (e) => {
            startX = e.pageX - this.track.offsetLeft;
            scrollLeft = this.track.scrollLeft;
            isDragging = true;
            this.track.style.cursor = 'grabbing';
        });
        
        this.track.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            e.preventDefault();
            const x = e.pageX - this.track.offsetLeft;
            const walk = (x - startX) * 2;
            this.track.scrollLeft = scrollLeft - walk;
        });
        
        this.track.addEventListener('mouseup', () => {
            isDragging = false;
            this.track.style.cursor = 'grab';
        });
        
        this.track.addEventListener('mouseleave', () => {
            isDragging = false;
            this.track.style.cursor = 'grab';
        });
    }
}

// 従来のslideCarousel関数（互換性のため）
function slideCarousel(carouselId, direction) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    const cardWidth = 300;
    const scrollAmount = cardWidth * 2 * direction;
    
    carousel.scrollBy({
        left: scrollAmount,
        behavior: 'smooth'
    });
}

// ページ読み込み完了時にカルーセルを初期化
document.addEventListener('DOMContentLoaded', function() {
    // 各カルーセルを初期化
    const carouselIds = ['history', 'recommend', 'new-arrivals', 'sale'];
    
    carouselIds.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            new ProductCarousel(id);
        }
    });
    
    // キーボードナビゲーション
    document.addEventListener('keydown', function(e) {
        const focusedCarousel = document.querySelector('.carousel-track:focus-within');
        if (!focusedCarousel) return;
        
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            focusedCarousel.scrollBy({ left: -300, behavior: 'smooth' });
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            focusedCarousel.scrollBy({ left: 300, behavior: 'smooth' });
        }
    });
});

// スクロール位置を保存・復元する機能
class ScrollPositionManager {
    static save(carouselId) {
        const carousel = document.getElementById(carouselId);
        if (carousel) {
            sessionStorage.setItem(`carousel-${carouselId}`, carousel.scrollLeft);
        }
    }
    
    static restore(carouselId) {
        const carousel = document.getElementById(carouselId);
        const savedPosition = sessionStorage.getItem(`carousel-${carouselId}`);
        
        if (carousel && savedPosition) {
            carousel.scrollLeft = parseInt(savedPosition, 10);
        }
    }
    
    static clearAll() {
        const keys = Object.keys(sessionStorage).filter(key => key.startsWith('carousel-'));
        keys.forEach(key => sessionStorage.removeItem(key));
    }
}

// ページ離脱時にスクロール位置を保存
window.addEventListener('beforeunload', function() {
    const carouselIds = ['history', 'recommend', 'new-arrivals', 'sale'];
    carouselIds.forEach(id => {
        ScrollPositionManager.save(id);
    });
});

// ページ読み込み時にスクロール位置を復元
window.addEventListener('load', function() {
    setTimeout(() => {
        const carouselIds = ['history', 'recommend', 'new-arrivals', 'sale'];
        carouselIds.forEach(id => {
            ScrollPositionManager.restore(id);
        });
    }, 100);
});

// パフォーマンス最適化：Intersection Observer を使用した遅延読み込み
class LazyImageLoader {
    constructor() {
        this.imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        this.init();
    }
    
    init() {
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => this.imageObserver.observe(img));
    }
}

// 遅延読み込みの初期化
document.addEventListener('DOMContentLoaded', function() {
    new LazyImageLoader();
});

// エクスポート（モジュール使用時）
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        ProductCarousel,
        slideCarousel,
        ScrollPositionManager,
        LazyImageLoader
    };
}
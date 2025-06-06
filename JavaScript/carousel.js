// carousel.js
class ProductCarousel {
    constructor(containerId) {
        this.containerId = containerId;
        this.track = document.getElementById(containerId);
        this.cards = this.track.querySelectorAll('.product-card');
        this.currentIndex = 0;
        this.cardWidth = 280; // カード幅 + gap
        this.cardsToShow = this.getCardsToShow();
        this.maxIndex = Math.max(0, this.cards.length - this.cardsToShow);
        
        this.init();
        this.updateNavButtons();
        
        // リサイズイベントリスナー
        window.addEventListener('resize', () => {
            this.handleResize();
        });
    }
    
    init() {
        // 初期位置設定
        this.updateTransform();
    }
    
    getCardsToShow() {
        const containerWidth = this.track.parentElement.clientWidth;
        if (window.innerWidth <= 480) {
            return Math.floor(containerWidth / 220); // モバイル
        } else if (window.innerWidth <= 768) {
            return Math.floor(containerWidth / 260); // タブレット
        } else {
            return Math.floor(containerWidth / 300); // デスクトップ
        }
    }
    
    handleResize() {
        this.cardsToShow = this.getCardsToShow();
        this.maxIndex = Math.max(0, this.cards.length - this.cardsToShow);
        
        // 現在のインデックスが範囲外の場合調整
        if (this.currentIndex > this.maxIndex) {
            this.currentIndex = this.maxIndex;
        }
        
        this.updateTransform();
        this.updateNavButtons();
    }
    
    slide(direction) {
        const newIndex = this.currentIndex + direction;
        
        if (newIndex >= 0 && newIndex <= this.maxIndex) {
            this.currentIndex = newIndex;
            this.updateTransform();
            this.updateNavButtons();
        }
    }
    
    updateTransform() {
        if (this.track) {
            const translateX = -this.currentIndex * (this.getCardWidth() + 20); // 20pxはgap
            this.track.style.transform = `translateX(${translateX}px)`;
        }
    }
    
    getCardWidth() {
        if (window.innerWidth <= 480) {
            return 200;
        } else if (window.innerWidth <= 768) {
            return 240;
        } else {
            return 280;
        }
    }
    
    updateNavButtons() {
        const container = this.track.closest('.carousel-container');
        const prevBtn = container.querySelector('.carousel-nav.prev');
        const nextBtn = container.querySelector('.carousel-nav.next');
        
        if (prevBtn && nextBtn) {
            prevBtn.disabled = this.currentIndex <= 0;
            nextBtn.disabled = this.currentIndex >= this.maxIndex;
            
            // スタイル更新
            prevBtn.style.opacity = this.currentIndex <= 0 ? '0.3' : '1';
            nextBtn.style.opacity = this.currentIndex >= this.maxIndex ? '0.3' : '1';
        }
    }
}

// カルーセルインスタンスを管理
const carousels = {};

// カルーセル初期化関数
function initCarousels() {
    const carouselIds = ['history', 'recommend', 'new-arrivals', 'sale'];
    
    carouselIds.forEach(id => {
        const element = document.getElementById(id);
        if (element && element.children.length > 0) {
            carousels[id] = new ProductCarousel(id);
        }
    });
}

// スライド関数（HTMLから呼び出される）
function slideCarousel(carouselId, direction) {
    if (carousels[carouselId]) {
        carousels[carouselId].slide(direction);
    }
}

// タッチイベント処理（スマートフォン対応）
function addTouchSupport() {
    Object.keys(carousels).forEach(id => {
        const track = document.getElementById(id);
        if (!track) return;
        
        let startX = 0;
        let startY = 0;
        let isScrolling = false;
        
        track.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            isScrolling = false;
        }, { passive: true });
        
        track.addEventListener('touchmove', (e) => {
            if (!startX || !startY) return;
            
            const diffX = Math.abs(e.touches[0].clientX - startX);
            const diffY = Math.abs(e.touches[0].clientY - startY);
            
            if (diffY > diffX) {
                isScrolling = true;
            }
        }, { passive: true });
        
        track.addEventListener('touchend', (e) => {
            if (!startX || isScrolling) return;
            
            const endX = e.changedTouches[0].clientX;
            const diff = startX - endX;
            
            if (Math.abs(diff) > 50) { // 50px以上のスワイプで反応
                if (diff > 0) {
                    slideCarousel(id, 1); // 右スワイプで次へ
                } else {
                    slideCarousel(id, -1); // 左スワイプで前へ
                }
            }
            
            startX = 0;
            startY = 0;
            isScrolling = false;
        }, { passive: true });
    });
}

// キーボードナビゲーション
function addKeyboardSupport() {
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
            const focusedCarousel = document.activeElement.closest('.carousel-container');
            if (focusedCarousel) {
                const carouselTrack = focusedCarousel.querySelector('.carousel-track');
                const carouselId = carouselTrack.id;
                
                if (carousels[carouselId]) {
                    e.preventDefault();
                    const direction = e.key === 'ArrowLeft' ? -1 : 1;
                    slideCarousel(carouselId, direction);
                }
            }
        }
    });
}

// ページ読み込み完了後に初期化
document.addEventListener('DOMContentLoaded', () => {
    // 少し遅延させて確実にDOM要素が準備されてから実行
    setTimeout(() => {
        initCarousels();
        addTouchSupport();
        addKeyboardSupport();
    }, 100);
});

// ウィンドウサイズ変更時の再計算
window.addEventListener('resize', () => {
    // デバウンス処理
    clearTimeout(window.resizeTimeout);
    window.resizeTimeout = setTimeout(() => {
        Object.keys(carousels).forEach(id => {
            if (carousels[id]) {
                carousels[id].handleResize();
            }
        });
    }, 250);
});
/**
 * 商品読み込み関連のJavaScript
 */

// DOM読み込み完了後に実行
document.addEventListener('DOMContentLoaded', function() {
    initializeProductLoader();
    initializeInfiniteScroll();
    initializeProductTracking();
});

/**
 * 商品ローダーの初期化
 */
function initializeProductLoader() {
    // ローディング表示の制御
    const loadingOverlay = document.getElementById('loading');
    
    // ページ読み込み時にローディングを非表示
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
    }
    
    // 再読み込みボタンのイベントリスナー
    const retryButtons = document.querySelectorAll('.btn-retry');
    retryButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            showLoading();
            setTimeout(() => {
                location.reload();
            }, 500);
        });
    });
    
    // 商品画像の遅延読み込み
    initializeLazyLoading();
}

/**
 * 遅延読み込みの初期化
 */
function initializeLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        lazyImages.forEach(img => imageObserver.observe(img));
    }
}

/**
 * 無限スクロールの初期化（将来の拡張用）
 */
function initializeInfiniteScroll() {
    // 現在は基本実装のみ
    // 将来的に商品一覧ページで無限スクロール機能を追加予定
}

/**
 * 商品トラッキングの初期化
 */
function initializeProductTracking() {
    // 商品カードのクリック追跡
    const productCards = document.querySelectorAll('.product_genre');
    
    productCards.forEach(card => {
        card.addEventListener('click', function(e) {
            const productId = this.dataset.productId;
            const context = this.dataset.context;
            
            if (productId) {
                trackProductClick(productId, context);
            }
        });
    });
}

/**
 * 商品クリックの追跡
 */
function trackProductClick(productId, context) {
    try {
        // 閲覧履歴をセッションストレージに記録
        let viewHistory = JSON.parse(sessionStorage.getItem('viewHistory') || '[]');
        
        // 重複を削除
        viewHistory = viewHistory.filter(id => id !== parseInt(productId));
        
        // 新しい商品IDを先頭に追加
        viewHistory.unshift(parseInt(productId));
        
        // 最大50件まで保持
        viewHistory = viewHistory.slice(0, 50);
        
        sessionStorage.setItem('viewHistory', JSON.stringify(viewHistory));
        
        // サーバーサイドにも送信（AJAX）
        sendViewHistoryToServer(productId, context);
        
    } catch (error) {
        console.warn('商品クリック追跡でエラーが発生しました:', error);
    }
}

/**
 * 閲覧履歴をサーバーに送信
 */
function sendViewHistoryToServer(productId, context) {
    if (!productId) return;
    
    fetch('./api/track_view.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            product_id: parseInt(productId),
            context: context || 'unknown',
            timestamp: Date.now()
        })
    }).catch(error => {
        console.warn('閲覧履歴の送信に失敗しました:', error);
    });
}

/**
 * ローディング表示
 */
function showLoading() {
    const loadingOverlay = document.getElementById('loading');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'flex';
        loadingOverlay.setAttribute('aria-hidden', 'false');
    }
}

/**
 * ローディング非表示
 */
function hideLoading() {
    const loadingOverlay = document.getElementById('loading');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
        loadingOverlay.setAttribute('aria-hidden', 'true');
    }
}

/**
 * 商品データの動的読み込み（AJAX）
 */
function loadMoreProducts(section, offset = 0, limit = 10) {
    return new Promise((resolve, reject) => {
        showLoading();
        
        fetch(`./api/get_products.php?section=${section}&offset=${offset}&limit=${limit}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            hideLoading();
            if (data.success) {
                resolve(data.products);
            } else {
                reject(new Error(data.error || '商品の読み込みに失敗しました'));
            }
        })
        .catch(error => {
            hideLoading();
            console.error('商品読み込みエラー:', error);
            reject(error);
        });
    });
}

/**
 * エラー表示
 */
function showError(message, container) {
    const errorHtml = `
        <div class="error-message" role="alert">
            <p>${escapeHtml(message)}</p>
            <button onclick="location.reload()" class="btn-retry">再読み込み</button>
        </div>
    `;
    
    if (container) {
        container.innerHTML = errorHtml;
    }
}

/**
 * HTMLエスケープ
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * 商品カードのHTML生成
 */
function createProductCardHtml(product, context = '') {
    const productId = parseInt(product.id);
    const productName = escapeHtml(product.name || '商品名不明');
    const productPrice = formatPrice(product.price || 0);
    const brandName = escapeHtml(product.brand_name || product.category_name || 'カテゴリなし');
    const stock = parseInt(product.stock || 0);
    const imagePath = escapeHtml(product.image_path || '../PHP/img/no-image.jpg');
    const imageAlt = escapeHtml(`${brandName} ${productName}`);
    
    let stockStatus = '';
    if (stock <= 0) {
        stockStatus = '<span class="stock-out" aria-label="売り切れ">売り切れ</span>';
    } else if (stock <= 5) {
        stockStatus = `<span class="stock-low" aria-label="残りわずか">残り${stock}個</span>`;
    }
    
    return `
        <article class="product_genre" data-product-id="${productId}" data-context="${context}">
            <a href="./product_detail.php?id=${productId}" aria-label="${productName}の詳細を見る">
                <div class="product-image-container">
                    <img src="${imagePath}" 
                         alt="${imageAlt}" 
                         loading="lazy"
                         onerror="this.src='../PHP/img/no-image.jpg'; this.alt='画像が見つかりません';">
                </div>
                <div class="product_info">
                    <p class="product_brand">${brandName}</p>
                    <h3 class="product_name">${productName}</h3>
                    <p class="product_price">${productPrice}</p>
                    ${stockStatus}
                </div>
            </a>
        </article>
    `;
}

/**
 * 価格フォーマット
 */
function formatPrice(price) {
    if (!price || price < 0) {
        return '価格未定';
    }
    return new Intl.NumberFormat('ja-JP').format(price) + '円';
}

// グローバルに公開する関数
window.ProductLoader = {
    loadMoreProducts,
    showLoading,
    hideLoading,
    showError,
    createProductCardHtml,
    trackProductClick
};
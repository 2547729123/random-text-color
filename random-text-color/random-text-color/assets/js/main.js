document.addEventListener('DOMContentLoaded', function () {
    const content = document.querySelector('.entry-content, .post-content, .article-content, article');
    if (!content) return;

    const isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

    const bolds = content.querySelectorAll('strong, b');
    const headings = content.querySelectorAll('h2, h3, h4, h5, h6');
    const paras = content.querySelectorAll('p');

    const enable_bold = window.rbtc_vars.enable_bold;
    const enable_headings = window.rbtc_vars.enable_headings;
    const enable_para = window.rbtc_vars.enable_para;
    const max_para = window.rbtc_vars.max_para;
    const gradientColors = window.rbtc_vars.gradientColors;

    function getRandomColor() {
        function rand(min = 0, max = 255) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
        let r, g, b, bright;
        do {
            r = rand(); g = rand(); b = rand();
            bright = r + g + b;
            if (!isDarkMode && bright > 600) continue;
            if (isDarkMode && bright < 200) continue;
            break;
        } while (true);
        return `rgb(${r},${g},${b})`;
    }

    if (enable_bold) {
        bolds.forEach(el => {
            el.style.color = getRandomColor();
        });
    }

    if (enable_headings) {
        headings.forEach((el, i) => {
            const c1 = gradientColors[i % gradientColors.length];
            const c2 = gradientColors[(i + 1) % gradientColors.length];
            el.style.backgroundImage = `linear-gradient(to right, ${c1}, ${c2})`;
            el.style.backgroundSize = '100% 100%';
            el.classList.add('rainbow-gradient-text');
        });
    }

    if (enable_para) {
        let count = 0;
        let paraArray = Array.from(paras);
        // 洗牌
        for (let i = paraArray.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [paraArray[i], paraArray[j]] = [paraArray[j], paraArray[i]];
        }
        paraArray.forEach(p => {
            if (count >= max_para) return;
            if (p.querySelector('strong, b')) return;
            p.style.color = getRandomColor();
            count++;
        });
    }
});

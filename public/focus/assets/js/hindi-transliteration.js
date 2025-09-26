
/**
 * Simple Hindi transliteration for demonstration.
 * Type 'namaste' → it shows 'नमस्ते' in real-time.
 */
document.addEventListener('DOMContentLoaded', function () {
    const map = {
        'a': 'अ', 'aa': 'आ', 'i': 'इ', 'ee': 'ई',
        'u': 'उ', 'oo': 'ऊ', 'e': 'ए', 'ai': 'ऐ',
        'o': 'ओ', 'au': 'औ', 'k': 'क', 'kh': 'ख',
        'g': 'ग', 'gh': 'घ', 'ch': 'च', 'chh': 'छ',
        'j': 'ज', 'jh': 'झ', 't': 'त', 'th': 'थ',
        'd': 'द', 'dh': 'ध', 'n': 'न', 'p': 'प',
        'ph': 'फ', 'b': 'ब', 'bh': 'भ', 'm': 'म',
        'y': 'य', 'r': 'र', 'l': 'ल', 'v': 'व',
        'sh': 'श', 's': 'स', 'h': 'ह', 'ṃ': 'ं',
        ' ': ' '
    };

    function transliterate(input) {
        let output = '';
        let i = 0;
        while (i < input.length) {
            let two = input.substr(i, 2).toLowerCase();
            let one = input[i].toLowerCase();

            if (map[two]) {
                output += map[two];
                i += 2;
            } else if (map[one]) {
                output += map[one];
                i += 1;
            } else {
                output += input[i];
                i += 1;
            }
        }
        return output;
    }

    const inputField = document.getElementById('NameText');
    const mirrorField = document.createElement('div');
    mirrorField.style.padding = '10px';
    mirrorField.style.marginTop = '10px';
    mirrorField.style.border = '1px solid #ccc';
    mirrorField.innerHTML = 'Translated text will appear here.';
    inputField.parentNode.appendChild(mirrorField);

    inputField.addEventListener('input', function () {
        mirrorField.textContent = transliterate(inputField.value);
    });
});

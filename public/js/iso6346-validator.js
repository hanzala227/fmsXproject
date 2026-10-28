/**
 * ISO 6346 Container Number Validator (Frontend Helper)
 * Implements standard check-digit formula without affecting backend submission.
 */
(function(root) {
    const letterValues = {
        A: 10, B: 12, C: 13, D: 14, E: 15, F: 16, G: 17, H: 18, I: 19, J: 20,
        K: 21, L: 23, M: 24, N: 25, O: 26, P: 27, Q: 28, R: 29, S: 30, T: 31,
        U: 32, V: 34, W: 35, X: 36, Y: 37, Z: 38
    };

    function calculateCheckDigit(container) {
        let total = 0;
        for (let i = 0; i < 10; i++) {
            const char = container.charAt(i);
            let value;
            if (i < 4) {
                value = letterValues[char];
                if (value === undefined) return null;
            } else {
                if (!/[0-9]/.test(char)) return null;
                value = parseInt(char, 10);
            }
            total += value * Math.pow(2, i);
        }
        let remainder = total % 11;
        if (remainder === 10) remainder = 0;
        return remainder;
    }

    const validator = {
        calculateCheckDigit: calculateCheckDigit,

        validate: function(containerStr) {
            if (!containerStr) return { status: 'empty' };
            const raw = String(containerStr).toUpperCase().replace(/\s+/g, '');
            if (raw.length === 0) return { status: 'empty' };

            // Check if exactly 11 characters
            if (raw.length !== 11) {
                return {
                    status: 'invalid',
                    reason: 'Must be exactly 11 characters (currently ' + raw.length + '). Standard ISO format: 4 letters + 6 digits + 1 check digit.',
                    enteredDigit: '-',
                    expectedDigit: '-',
                    action: 'Example valid format: CSQU3054383, MSCU6634122'
                };
            }

            // Check first 4 letters
            if (!/^[A-Z]{4}/.test(raw)) {
                return {
                    status: 'invalid',
                    reason: 'First 4 characters must be capital letters (3-letter owner code + category identifier e.g., U, J, Z).',
                    enteredDigit: '-',
                    expectedDigit: '-',
                    action: 'Ensure the first 4 characters are letters (e.g. MSCU, CSQU, TEMU)'
                };
            }

            // Check 6 digits
            if (!/^[A-Z]{4}[0-9]{6}/.test(raw)) {
                return {
                    status: 'invalid',
                    reason: 'Positions 5 to 10 must be 6 numeric digits representing serial number.',
                    enteredDigit: '-',
                    expectedDigit: '-',
                    action: 'Ensure 6 numbers follow the 4-letter owner code.'
                };
            }

            // Check last digit
            if (!/[0-9]$/.test(raw)) {
                return {
                    status: 'invalid',
                    reason: 'The 11th character must be a numeric check digit.',
                    enteredDigit: '-',
                    expectedDigit: '-',
                    action: 'Change last character to a number.'
                };
            }

            const expected = calculateCheckDigit(raw);
            const entered = parseInt(raw.charAt(10), 10);

            if (expected === null) {
                return {
                    status: 'invalid',
                    reason: 'Unable to calculate check digit due to invalid characters.',
                    enteredDigit: '-',
                    expectedDigit: '-',
                    action: 'Check format against ISO 6346.'
                };
            }

            if (entered === expected) {
                return {
                    status: 'valid',
                    enteredDigit: entered,
                    expectedDigit: expected,
                    reason: 'Valid ISO 6346 container number. Check digit matches perfectly.'
                };
            } else {
                const suggested = raw.substring(0, 10) + expected;
                return {
                    status: 'mismatch',
                    enteredDigit: entered,
                    expectedDigit: expected,
                    reason: 'Check digit mismatch. The entered check digit is ' + entered + ', but ISO 6346 expects ' + expected + '.',
                    action: 'Change last digit: ' + entered + ' → ' + expected,
                    suggestedValue: suggested
                };
            }
        }
    };

    if (typeof window !== 'undefined') {
        window.iso6346Validator = validator;
    }
    if (typeof globalThis !== 'undefined') {
        globalThis.iso6346Validator = validator;
    }
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = validator;
    }
})(typeof window !== 'undefined' ? window : (typeof globalThis !== 'undefined' ? globalThis : this));

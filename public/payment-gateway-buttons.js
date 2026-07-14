(() => {
    const catalogElement = document.getElementById('payment-gateway-catalog');
    if (!catalogElement) return;

    let gateways = [];
    try {
        gateways = JSON.parse(catalogElement.textContent || '[]').filter((gateway) => gateway.available);
    } catch (error) {
        gateways = [];
    }

    const buttonClass = (provider, secondary = false) => {
        const base = 'inline-flex min-h-10 items-center justify-center rounded-xl border px-4 py-2.5 text-xs font-bold transition disabled:cursor-not-allowed disabled:opacity-50';
        if (secondary || provider === 'palmpay' || provider === 'monnify') {
            return `${base} border-slate-300 bg-white text-blue-700 hover:bg-slate-50`;
        }
        return `${base} border-slate-950 bg-slate-950 text-white hover:bg-slate-800`;
    };

    const paymentLabel = (gateway, prefix = '') => `${prefix}${gateway.label}`;

    const removeHardcodedButtons = () => {
        document.querySelectorAll('button[formaction*="/payments/checkout/"]').forEach((button) => button.remove());
        document.querySelectorAll('form[action*="/checkout/paystack"], form[action*="/checkout/palmpay"]').forEach((form) => form.remove());
    };

    const bundleForms = [...document.querySelectorAll('form')].filter((form) =>
        form.querySelector('button[formaction*="/payments/checkout/"]'),
    );

    const individualGroups = new Map();
    document.querySelectorAll('form[action*="/checkout/"]').forEach((form) => {
        const action = form.getAttribute('action') || '';
        if (/\/payments\/checkout\/(paystack|palmpay|flutterwave|monnify)(?:\?|$)/.test(action)) return;
        if (!/\/payments\/[^/]+\/checkout\/(paystack|palmpay|flutterwave|monnify)(?:\?|$)/.test(action)) return;
        const container = form.parentElement;
        if (!container) return;
        if (!individualGroups.has(container)) individualGroups.set(container, []);
        individualGroups.get(container).push(form);
    });

    if (gateways.length === 0) {
        removeHardcodedButtons();
        bundleForms.forEach((form) => {
            const notice = document.createElement('p');
            notice.className = 'w-full rounded-xl border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-900';
            notice.textContent = 'Online payment is temporarily unavailable. Use the school bank-transfer instructions or contact the accounts office.';
            form.append(notice);
        });
        individualGroups.forEach((forms, container) => {
            forms.forEach((form) => form.remove());
            const notice = document.createElement('span');
            notice.className = 'text-xs font-semibold text-amber-700';
            notice.textContent = 'Online payment unavailable';
            container.append(notice);
        });
        return;
    }

    bundleForms.forEach((form) => {
        const templateButton = form.querySelector('button[formaction*="/payments/checkout/"]');
        if (!templateButton) return;
        const sampleAction = templateButton.getAttribute('formaction') || '';
        form.querySelectorAll('button[formaction*="/payments/checkout/"]').forEach((button) => button.remove());

        const updateDisabledState = () => {
            const hasSelection = form.querySelectorAll('input[name="invoice_ids[]"]').length > 0;
            form.querySelectorAll('[data-dynamic-payment-button]').forEach((button) => {
                button.disabled = !hasSelection;
            });
        };

        gateways.forEach((gateway, index) => {
            const button = document.createElement('button');
            button.type = 'submit';
            button.formAction = sampleAction.replace(/\/(paystack|palmpay|flutterwave|monnify)(?:\?.*)?$/, `/${gateway.value}`);
            button.className = `${buttonClass(gateway.value, index > 0)} w-full sm:w-auto`;
            button.textContent = paymentLabel(gateway, 'Pay Selected with ');
            button.dataset.dynamicPaymentButton = 'true';
            form.append(button);
        });

        new MutationObserver(updateDisabledState).observe(form, { childList: true, subtree: true });
        updateDisabledState();
    });

    individualGroups.forEach((forms, container) => {
        const sampleForm = forms[0];
        const sampleAction = sampleForm.getAttribute('action') || '';
        const csrf = sampleForm.querySelector('input[name="_token"]')?.cloneNode(true);
        forms.forEach((form) => form.remove());

        gateways.forEach((gateway, index) => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = sampleAction.replace(/\/(paystack|palmpay|flutterwave|monnify)(?:\?.*)?$/, `/${gateway.value}`);
            if (csrf) form.append(csrf.cloneNode(true));

            const button = document.createElement('button');
            button.type = 'submit';
            button.className = buttonClass(gateway.value, index > 0);
            button.textContent = gateway.label;
            form.append(button);
            container.append(form);
        });
    });
})();

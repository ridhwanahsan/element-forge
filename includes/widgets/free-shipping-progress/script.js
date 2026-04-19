(function () {
	'use strict';

	function toArray(collection) {
		return Array.prototype.slice.call(collection || []);
	}

	function parseNumber(value, fallback) {
		var parsed = parseFloat(value);

		return isNaN(parsed) ? fallback : parsed;
	}

	function formatPrice(amount) {
		var config = window.elementForgeWooWidgets || {};
		var decimals = parseInt(config.decimals, 10);
		var decimalSep = config.decimalSep || '.';
		var thousandSep = config.thousandSep || ',';
		var currencySymbol = config.currencySymbol || '';
		var currencyPos = config.currencyPos || 'left';
		var fixed = Math.abs(amount).toFixed(isNaN(decimals) ? 2 : decimals);
		var parts = fixed.split('.');
		var integerPart = parts[0];
		var decimalPart = parts[1] ? decimalSep + parts[1] : '';
		var formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSep);
		var value = formattedInteger + decimalPart;

		if (amount < 0) {
			value = '-' + value;
		}

		switch (currencyPos) {
			case 'left_space':
				return currencySymbol + ' ' + value;
			case 'right':
				return value + currencySymbol;
			case 'right_space':
				return value + ' ' + currencySymbol;
			case 'left':
			default:
				return currencySymbol + value;
		}
	}

	function getState(subtotal, threshold) {
		var remaining = Math.max(0, threshold - subtotal);
		var percent = threshold > 0 ? Math.min(100, Math.max(0, (subtotal / threshold) * 100)) : 0;
		var state = 'progress';

		if (subtotal <= 0) {
			state = 'empty';
		} else if (remaining <= 0) {
			state = 'success';
		}

		return {
			remaining: remaining,
			percent: percent,
			state: state
		};
	}

	function replaceToken(message, amount) {
		return String(message || '').replace(/\{remaining\}/g, amount);
	}

	function formatGoal(amount) {
		var config = window.elementForgeWooWidgets || {};
		var template = config.goalLabel || 'Goal: %s';

		return String(template).replace('%s', amount);
	}

	function updateWidget(wrapper, cartSummary) {
		var basis = wrapper.getAttribute('data-basis') || 'subtotal';
		var threshold = parseNumber(wrapper.getAttribute('data-threshold'), 100);
		var subtotal = 'discounted_subtotal' === basis ? cartSummary.discounted_subtotal : cartSummary.subtotal;
		var stateData = getState(subtotal, threshold);
		var messageEl = wrapper.querySelector('.ef-free-shipping-progress__message');
		var remainingEl = wrapper.querySelector('.ef-free-shipping-progress__remaining');
		var goalEl = wrapper.querySelector('.ef-free-shipping-progress__goal');
		var fillEl = wrapper.querySelector('.ef-free-shipping-progress__fill');
		var beforeText = wrapper.getAttribute('data-before-text') || '';
		var successText = wrapper.getAttribute('data-success-text') || '';
		var emptyText = wrapper.getAttribute('data-empty-text') || '';
		var remainingFormatted = formatPrice(stateData.remaining);
		var goalFormatted = formatPrice(threshold);
		var message = replaceToken(beforeText, remainingFormatted);

		wrapper.classList.remove('is-success', 'is-empty', 'is-progress');

		if ('success' === stateData.state) {
			message = successText;
			wrapper.classList.add('is-success');
		} else if ('empty' === stateData.state) {
			message = emptyText;
			wrapper.classList.add('is-empty');
		} else {
			wrapper.classList.add('is-progress');
		}

		if (messageEl) {
			messageEl.textContent = message;
		}

		if (remainingEl) {
			remainingEl.textContent = remainingFormatted;
		}

		if (goalEl) {
			goalEl.textContent = formatGoal(goalFormatted);
		}

		if (fillEl) {
			fillEl.style.width = stateData.percent + '%';
		}

		wrapper.setAttribute('data-subtotal', cartSummary.subtotal);
		wrapper.setAttribute('data-discounted-subtotal', cartSummary.discounted_subtotal);
	}

	function refreshAll(cartSummary) {
		toArray(document.querySelectorAll('.ef-free-shipping-progress')).forEach(function (wrapper) {
			updateWidget(wrapper, cartSummary);
		});
	}

	function fetchCartSummary() {
		var config = window.elementForgeWooWidgets || {};

		if (!config.cartSummaryUrl || !window.fetch) {
			return;
		}

		window.fetch(config.cartSummaryUrl, {
			credentials: 'same-origin'
		})
			.then(function (response) {
				return response.ok ? response.json() : null;
			})
			.then(function (payload) {
				if (!payload || !payload.data) {
					return;
				}

				refreshAll(payload.data);
			})
			.catch(function () {
				return null;
			});
	}

	function scheduleRefresh() {
		window.clearTimeout(scheduleRefresh.timer);
		scheduleRefresh.timer = window.setTimeout(fetchCartSummary, 120);
	}

	function initAll(root) {
		var wrappers = [];

		if (root.matches && root.matches('.ef-free-shipping-progress')) {
			wrappers.push(root);
		}

		if (root.querySelectorAll) {
			wrappers = wrappers.concat(toArray(root.querySelectorAll('.ef-free-shipping-progress')));
		}

		wrappers.forEach(function (wrapper) {
			if (wrapper.dataset.efFreeShippingReady === 'yes') {
				return;
			}

			updateWidget(wrapper, {
				subtotal: parseNumber(wrapper.getAttribute('data-subtotal'), 0),
				discounted_subtotal: parseNumber(
					wrapper.getAttribute('data-discounted-subtotal'),
					parseNumber(wrapper.getAttribute('data-subtotal'), 0)
				)
			});

			wrapper.dataset.efFreeShippingReady = 'yes';
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initAll(document);
		});
	} else {
		initAll(document);
	}

	if (document.querySelector('.ef-free-shipping-progress')) {
		scheduleRefresh();
	}

	if (window.jQuery) {
		window.jQuery(document.body).on(
			'updated_wc_div updated_cart_totals updated_checkout wc_fragments_loaded wc_fragments_refreshed added_to_cart removed_from_cart',
			scheduleRefresh
		);
	}

	new MutationObserver(function (mutations) {
		mutations.forEach(function (mutation) {
			toArray(mutation.addedNodes).forEach(function (node) {
				if (node.nodeType === 1) {
					initAll(node);
				}
			});
		});
	}).observe(document.documentElement, { childList: true, subtree: true });
}());

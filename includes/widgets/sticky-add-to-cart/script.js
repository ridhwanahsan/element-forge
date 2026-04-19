(function () {
	'use strict';

	function findMainForm() {
		return document.querySelector('.single-product form.cart') || document.querySelector('form.cart');
	}

	function toArray(collection) {
		return Array.prototype.slice.call(collection || []);
	}

	function updateFallbackQuantity(wrapper) {
		var quantityInput = wrapper.querySelector('.ef-sticky-add-to-cart__qty');
		var link = wrapper.querySelector('a.ef-sticky-add-to-cart__button');
		var url;

		if (!quantityInput || !link) {
			return;
		}

		link.setAttribute('data-quantity', quantityInput.value || '1');

		try {
			url = new URL(link.getAttribute('data-base-url') || link.href, window.location.href);
			url.searchParams.set('quantity', quantityInput.value || '1');
			link.href = url.toString();
		} catch (error) {
			link.href = link.getAttribute('data-base-url') || link.href;
		}
	}

	function init(wrapper) {
		var context;
		var position;
		var quantityInput;
		var button;
		var mainForm;
		var mainButton;
		var mainQuantity;
		var variationOutput;
		var variationIdInput;
		var variationSelects;
		var observer;

		if (wrapper.dataset.efStickyReady === 'yes') {
			return;
		}

		context = wrapper.getAttribute('data-context') || 'fallback';
		position = wrapper.getAttribute('data-position') || 'bottom';
		quantityInput = wrapper.querySelector('.ef-sticky-add-to-cart__qty');
		button = wrapper.querySelector('.ef-sticky-add-to-cart__button');

		if (button && 'A' === button.tagName) {
			button.setAttribute('data-base-url', button.href);
		}

		if ('single' !== context) {
			if (quantityInput) {
				quantityInput.addEventListener('input', function () {
					updateFallbackQuantity(wrapper);
				});
				updateFallbackQuantity(wrapper);
			}

			wrapper.classList.add('is-visible');
			wrapper.dataset.efStickyReady = 'yes';
			return;
		}

		mainForm = findMainForm();

		if (!mainForm || !button) {
			wrapper.classList.add('is-visible');
			wrapper.dataset.efStickyReady = 'yes';
			return;
		}

		mainButton = mainForm.querySelector('.single_add_to_cart_button');
		mainQuantity = mainForm.querySelector('input.qty');
		variationOutput = wrapper.querySelector('.ef-sticky-add-to-cart__variation');
		variationIdInput = mainForm.querySelector('input[name="variation_id"]');
		variationSelects = toArray(mainForm.querySelectorAll('.variations select'));

		function syncQuantityFromMain() {
			if (!quantityInput || !mainQuantity) {
				return;
			}

			quantityInput.value = mainQuantity.value || '1';
		}

		function syncQuantityToMain() {
			if (!quantityInput || !mainQuantity) {
				return;
			}

			mainQuantity.value = quantityInput.value || '1';
			mainQuantity.dispatchEvent(new Event('change', { bubbles: true }));
		}

		function updateVisibility(isVisible) {
			if (isVisible) {
				wrapper.classList.remove('is-visible');
				return;
			}

			wrapper.classList.add('is-visible');
		}

		function getVariationSummary() {
			return variationSelects
				.map(function (select) {
					var selectedOption = select.options[select.selectedIndex];

					if (!select.value || !selectedOption) {
						return '';
					}

					return selectedOption.text;
				})
				.filter(Boolean)
				.join(' / ');
		}

		function refreshVariationState() {
			var summary;
			var isVariable;
			var hasVariation;
			var defaultLabel;
			var selectOptionsLabel;
			var isUnavailable;

			if (!button) {
				return;
			}

			isVariable = 'variable' === (wrapper.getAttribute('data-product-type') || '');
			hasVariation = !variationIdInput || !!variationIdInput.value;
			defaultLabel = button.getAttribute('data-default-label') || button.textContent;
			selectOptionsLabel = button.getAttribute('data-select-options-label') || defaultLabel;
			isUnavailable = !!mainButton && !!mainButton.disabled && (!isVariable || hasVariation);

			if (variationOutput) {
				summary = getVariationSummary();
				variationOutput.textContent = summary;
				variationOutput.hidden = !summary;
			}

			button.textContent = defaultLabel;
			button.classList.remove('is-disabled');
			button.removeAttribute('aria-disabled');
			button.removeAttribute('data-selection-required');

			if (isVariable && !hasVariation) {
				button.classList.add('is-disabled');
				button.removeAttribute('disabled');
				button.setAttribute('aria-disabled', 'true');
				button.setAttribute('data-selection-required', 'yes');
				button.textContent = selectOptionsLabel;
				return;
			}

			if (isUnavailable) {
				button.classList.add('is-disabled');
				button.disabled = true;
				return;
			}

			button.disabled = false;
		}

		if (window.IntersectionObserver) {
			observer = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					updateVisibility(entry.isIntersecting);
				});
			}, {
				threshold: 0.15
			});
			observer.observe(mainForm);
		} else {
			window.addEventListener('scroll', function () {
				var rect = mainForm.getBoundingClientRect();
				var threshold = 'top' === position ? rect.bottom >= 40 : rect.top <= (window.innerHeight - 40);

				updateVisibility(threshold);
			});
		}

		if (quantityInput) {
			quantityInput.addEventListener('input', syncQuantityToMain);
		}

		if (mainQuantity) {
			mainQuantity.addEventListener('input', syncQuantityFromMain);
			mainQuantity.addEventListener('change', syncQuantityFromMain);
			syncQuantityFromMain();
		}

		if (variationSelects.length) {
			variationSelects.forEach(function (select) {
				select.addEventListener('change', function () {
					window.setTimeout(refreshVariationState, 20);
				});
			});
		}

		if (window.jQuery) {
			window.jQuery(mainForm).on('found_variation reset_data hide_variation', function () {
				window.setTimeout(refreshVariationState, 20);
			});
		}

		button.addEventListener('click', function (event) {
			var isVariable = 'variable' === (wrapper.getAttribute('data-product-type') || '');
			var selectionRequired = button.getAttribute('data-selection-required') === 'yes';
			var isDisabled = button.disabled || button.getAttribute('aria-disabled') === 'true';

			if (selectionRequired) {
				event.preventDefault();

				if (isVariable) {
					mainForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
					if (variationSelects.length) {
						variationSelects[0].focus();
					}
				}

				return;
			}

			if (isDisabled) {
				event.preventDefault();
				return;
			}

			syncQuantityToMain();
			button.classList.add('is-loading');

			if (mainButton) {
				mainButton.click();
			} else if (mainForm.requestSubmit) {
				mainForm.requestSubmit();
			} else {
				mainForm.submit();
			}

			window.setTimeout(function () {
				button.classList.remove('is-loading');
				refreshVariationState();
			}, 1200);
		});

		if (window.jQuery) {
			window.jQuery(document.body).on('found_variation reset_data added_to_cart', function () {
				window.setTimeout(function () {
					button.classList.remove('is-loading');
					syncQuantityFromMain();
					refreshVariationState();
				}, 40);
			});
		}

		refreshVariationState();
		wrapper.dataset.efStickyReady = 'yes';
	}

	function initAll(root) {
		var wrappers = [];

		if (root.matches && root.matches('.ef-sticky-add-to-cart')) {
			wrappers.push(root);
		}

		if (root.querySelectorAll) {
			wrappers = wrappers.concat(toArray(root.querySelectorAll('.ef-sticky-add-to-cart')));
		}

		wrappers.forEach(init);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initAll(document);
		});
	} else {
		initAll(document);
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

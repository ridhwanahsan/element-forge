(function () {
	'use strict';

	function getInnerContainer(wrapper) {
		return wrapper.querySelector(':scope > .elementor-container') ||
			wrapper.querySelector(':scope > .e-con-inner') ||
			wrapper.querySelector(':scope > .e-con') ||
			wrapper;
	}

	function getItems(container) {
		return Array.prototype.slice.call(container.children).filter(function (child) {
			return child.nodeType === 1 && child.classList.contains('elementor-element');
		});
	}

	function revealItems(items, start, count) {
		var end = Math.min(items.length, start + count);
		var index = 0;

		for (index = start; index < end; index++) {
			items[index].classList.remove('efx-load-more-item-hidden');
			items[index].hidden = false;
		}

		return end;
	}

	function init(wrapper) {
		var container;
		var items;
		var visibleCount;
		var step;
		var buttonText;
		var buttonWrap;
		var button;

		if (wrapper.dataset.efxLoadMoreReady === 'yes') {
			return;
		}

		container = getInnerContainer(wrapper);
		items = getItems(container);
		visibleCount = parseInt(wrapper.getAttribute('data-initial-items'), 10) || 3;
		step = parseInt(wrapper.getAttribute('data-step'), 10) || 3;
		buttonText = wrapper.getAttribute('data-button-text') || 'Load More';

		if (items.length <= visibleCount) {
			return;
		}

		items.forEach(function (item, index) {
			if (index >= visibleCount) {
				item.classList.add('efx-load-more-item-hidden');
				item.hidden = true;
			}
		});

		buttonWrap = document.createElement('div');
		buttonWrap.className = 'efx-load-more-button-wrap';

		button = document.createElement('button');
		button.type = 'button';
		button.className = 'efx-load-more-button';
		button.textContent = buttonText;
		buttonWrap.appendChild(button);
		wrapper.appendChild(buttonWrap);

		button.addEventListener('click', function () {
			var currentLabel = button.textContent;

			button.disabled = true;
			button.textContent = 'Loading...';

			window.setTimeout(function () {
				visibleCount = revealItems(items, visibleCount, step);
				button.disabled = false;
				button.textContent = currentLabel;

				if (visibleCount >= items.length) {
					buttonWrap.remove();
				}
			}, 180);
		});

		wrapper.dataset.efxLoadMoreReady = 'yes';
	}

	function initAll(root) {
		var wrappers = [];
		var index = 0;

		if (root.matches && root.matches('.efx-load-more')) {
			wrappers.push(root);
		}

		if (root.querySelectorAll) {
			wrappers = wrappers.concat(Array.prototype.slice.call(root.querySelectorAll('.efx-load-more')));
		}

		for (index = 0; index < wrappers.length; index++) {
			init(wrappers[index]);
		}
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
			var index = 0;

			for (index = 0; index < mutation.addedNodes.length; index++) {
				if (mutation.addedNodes[index].nodeType === 1) {
					initAll(mutation.addedNodes[index]);
				}
			}
		});
	}).observe(document.documentElement, { childList: true, subtree: true });
}());

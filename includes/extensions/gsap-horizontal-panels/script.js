(function () {
	'use strict';

	function getInnerContainer(wrapper) {
		return wrapper.querySelector(':scope > .elementor-container') ||
			wrapper.querySelector(':scope > .e-con-inner') ||
			wrapper.querySelector(':scope > .e-con') ||
			wrapper;
	}

	function getItems(host) {
		return Array.prototype.slice.call(host.children).filter(function (child) {
			return child.nodeType === 1 && child.classList.contains('elementor-element');
		});
	}

	function init(wrapper) {
		var host;
		var items;

		if (wrapper.dataset.efxHorizontalPanelsReady === 'yes') {
			return;
		}

		host = getInnerContainer(wrapper);
		items = getItems(host);

		if (!items.length) {
			return;
		}

		host.classList.add('efx-horizontal-panels__scroller');

		if (wrapper.getAttribute('data-wheel-scroll') === 'yes') {
			host.addEventListener('wheel', function (event) {
				if (Math.abs(event.deltaY) <= Math.abs(event.deltaX)) {
					return;
				}

				host.scrollLeft += event.deltaY;
				event.preventDefault();
			}, { passive: false });
		}

		wrapper.dataset.efxHorizontalPanelsReady = 'yes';
	}

	function initAll(root) {
		var wrappers = [];
		var index = 0;

		if (root.matches && root.matches('.efx-horizontal-panels')) {
			wrappers.push(root);
		}

		if (root.querySelectorAll) {
			wrappers = wrappers.concat(Array.prototype.slice.call(root.querySelectorAll('.efx-horizontal-panels')));
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
			Array.prototype.forEach.call(mutation.addedNodes, function (node) {
				if (node.nodeType === 1) {
					initAll(node);
				}
			});
		});
	}).observe(document.documentElement, { childList: true, subtree: true });
}());

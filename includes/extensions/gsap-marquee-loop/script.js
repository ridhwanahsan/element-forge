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
		var viewport;
		var track;

		if (wrapper.dataset.efxMarqueeReady === 'yes') {
			return;
		}

		host = getInnerContainer(wrapper);
		items = getItems(host);

		if (items.length < 2) {
			return;
		}

		viewport = document.createElement('div');
		viewport.className = 'efx-marquee-loop__viewport';

		track = document.createElement('div');
		track.className = 'efx-marquee-loop__track';

		if (wrapper.getAttribute('data-reverse') === 'yes') {
			track.classList.add('is-reverse');
		}

		items.forEach(function (item) {
			track.appendChild(item);
		});

		items.forEach(function (item) {
			track.appendChild(item.cloneNode(true));
		});

		viewport.appendChild(track);
		host.appendChild(viewport);
		host.classList.add('efx-marquee-loop__host');

		wrapper.dataset.efxMarqueeReady = 'yes';
	}

	function initAll(root) {
		var wrappers = [];
		var index = 0;

		if (root.matches && root.matches('.efx-marquee-loop')) {
			wrappers.push(root);
		}

		if (root.querySelectorAll) {
			wrappers = wrappers.concat(Array.prototype.slice.call(root.querySelectorAll('.efx-marquee-loop')));
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

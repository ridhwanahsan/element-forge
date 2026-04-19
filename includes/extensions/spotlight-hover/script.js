(function () {
	'use strict';

	function isWidget(wrapper) {
		return wrapper.classList && wrapper.classList.contains('elementor-widget');
	}

	function getDefaultSurface(wrapper) {
		if (isWidget(wrapper)) {
			return wrapper.querySelector(':scope > .elementor-widget-container') || wrapper;
		}

		return wrapper;
	}

	function getSurface(wrapper) {
		var selector = wrapper.getAttribute('data-target-selector');
		var target = null;

		if (selector) {
			try {
				target = wrapper.querySelector(selector);
			} catch (error) {
				target = null;
			}

			if (target) {
				return target;
			}
		}

		return getDefaultSurface(wrapper);
	}

	function init(wrapper) {
		var surface;

		if (wrapper.dataset.efxSpotlightReady === 'yes') {
			return;
		}

		surface = getSurface(wrapper);
		surface.classList.add('efx-spotlight-hover__surface');
		surface.setAttribute('data-layer', wrapper.getAttribute('data-layer') || 'background');
		wrapper.dataset.efxSpotlightReady = 'yes';

		function updatePosition(event) {
			var rect = surface.getBoundingClientRect();

			surface.style.setProperty('--efx-spotlight-x', (event.clientX - rect.left) + 'px');
			surface.style.setProperty('--efx-spotlight-y', (event.clientY - rect.top) + 'px');
			surface.classList.add('is-spotlight-active');
		}

		surface.addEventListener('pointerenter', updatePosition);
		surface.addEventListener('pointermove', updatePosition);
		surface.addEventListener('pointerleave', function () {
			surface.classList.remove('is-spotlight-active');
		});
	}

	function initAll(root) {
		var wrappers = [];
		var index = 0;

		if (root.matches && root.matches('.efx-spotlight-hover')) {
			wrappers.push(root);
		}

		if (root.querySelectorAll) {
			wrappers = wrappers.concat(Array.prototype.slice.call(root.querySelectorAll('.efx-spotlight-hover')));
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

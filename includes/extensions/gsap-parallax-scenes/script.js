(function () {
	'use strict';

	var scenes = [];
	var ticking = false;

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

	function updateScenes() {
		scenes.forEach(function (scene) {
			var rect = scene.wrapper.getBoundingClientRect();
			var progress = ((window.innerHeight / 2) - rect.top) / (window.innerHeight + rect.height);
			var direction = scene.reverse ? -1 : 1;
			var offsetBase = (progress - 0.5) * scene.strength * 2 * direction;

			scene.items.forEach(function (item, index) {
				var factor = (index + 1) / scene.items.length;
				item.style.transform = 'translate3d(0,' + (offsetBase * factor).toFixed(2) + 'px,0)';
			});
		});

		ticking = false;
	}

	function requestTick() {
		if (ticking) {
			return;
		}

		ticking = true;
		window.requestAnimationFrame(updateScenes);
	}

	function init(wrapper) {
		var host;
		var items;

		if (wrapper.dataset.efxParallaxReady === 'yes') {
			return;
		}

		host = getInnerContainer(wrapper);
		items = getItems(host);

		if (!items.length) {
			return;
		}

		items.forEach(function (item) {
			item.setAttribute('data-efx-parallax-layer', '');
		});

		scenes.push({
			wrapper: wrapper,
			items: items,
			strength: parseInt(wrapper.getAttribute('data-strength'), 10) || 18,
			reverse: wrapper.getAttribute('data-reverse') === 'yes'
		});

		wrapper.dataset.efxParallaxReady = 'yes';
		requestTick();
	}

	function initAll(root) {
		var wrappers = [];
		var index = 0;

		if (root.matches && root.matches('.efx-parallax-scene')) {
			wrappers.push(root);
		}

		if (root.querySelectorAll) {
			wrappers = wrappers.concat(Array.prototype.slice.call(root.querySelectorAll('.efx-parallax-scene')));
		}

		for (index = 0; index < wrappers.length; index++) {
			init(wrappers[index]);
		}
	}

	window.addEventListener('scroll', requestTick, { passive: true });
	window.addEventListener('resize', requestTick);

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

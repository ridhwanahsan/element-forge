(function () {
	'use strict';

	function markTargets(wrapper) {
		var selector = wrapper.getAttribute('data-selector') || '.elementor-heading-title,h1,h2,h3,h4,p';
		var targets = [];

		try {
			targets = Array.prototype.slice.call(wrapper.querySelectorAll(selector));
		} catch (error) {
			targets = [];
		}

		targets.forEach(function (target, index) {
			target.classList.add('efx-text-reveal-item');
			target.style.transitionDelay = (index * 0.08) + 's';
		});

		return targets;
	}

	function init(wrapper) {
		var observer;
		var targets;

		if (wrapper.dataset.efxTextRevealReady === 'yes') {
			return;
		}

		targets = markTargets(wrapper);

		if (!targets.length) {
			return;
		}

		observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					targets.forEach(function (target) {
						target.classList.add('is-text-reveal-visible');
					});
					observer.disconnect();
				}
			});
		}, {
			threshold: 0.2
		});

		observer.observe(wrapper);
		wrapper.dataset.efxTextRevealReady = 'yes';
	}

	function initAll(root) {
		var wrappers = [];
		var index = 0;

		if (root.matches && root.matches('.efx-text-reveal')) {
			wrappers.push(root);
		}

		if (root.querySelectorAll) {
			wrappers = wrappers.concat(Array.prototype.slice.call(root.querySelectorAll('.efx-text-reveal')));
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

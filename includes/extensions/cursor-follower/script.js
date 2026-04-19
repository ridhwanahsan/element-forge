(function () {
	'use strict';

	function init(wrapper) {
		var bubble;

		if (wrapper.dataset.efxCursorReady === 'yes') {
			return;
		}

		bubble = document.createElement('span');
		bubble.className = 'efx-cursor-follower__bubble';
		bubble.textContent = wrapper.getAttribute('data-label') || 'Move';
		wrapper.appendChild(bubble);

		function updatePosition(event) {
			var rect = wrapper.getBoundingClientRect();

			bubble.style.left = (event.clientX - rect.left) + 'px';
			bubble.style.top = (event.clientY - rect.top) + 'px';
			wrapper.classList.add('is-cursor-active');
		}

		wrapper.addEventListener('pointerenter', updatePosition);
		wrapper.addEventListener('pointermove', updatePosition);
		wrapper.addEventListener('pointerleave', function () {
			wrapper.classList.remove('is-cursor-active');
		});

		wrapper.dataset.efxCursorReady = 'yes';
	}

	function initAll(root) {
		var wrappers = [];
		var index = 0;

		if (root.matches && root.matches('.efx-cursor-follower')) {
			wrappers.push(root);
		}

		if (root.querySelectorAll) {
			wrappers = wrappers.concat(Array.prototype.slice.call(root.querySelectorAll('.efx-cursor-follower')));
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

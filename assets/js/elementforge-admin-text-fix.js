(function () {
	'use strict';

	var replacements = [
		[
			"Welcome back Ã¢â‚¬â€ here's what's happening with your sites.",
			"Welcome back - here's what's happening with your sites."
		],
		['Loading settingsÃ¢â‚¬Â¦', 'Loading settings...'],
		['Search widgetsÃ¢â‚¬Â¦', 'Search widgets...'],
		['WordPress.org Forums Ã¢â€ â€™', 'WordPress.org Forums ->'],
		['Ã¢Å“â€œ Saved!', 'Saved!'],
		['Ã¢Â­Â Free & Pro Plans', 'Free & Pro Plans'],
		['Ã¢Â¬â€  Coming Soon', 'Coming Soon'],
		['Ã¢â€”Â Enabled', 'Enabled'],
		['Ã¢â€”â€¹ Disabled', 'Disabled'],
		[' Ã‚Â· ', ' - '],
		['Ã¢â‚¬Â¢', '-'],
		['Ã¢â€ â€™', '->'],
		['Ã¢â‚¬Â¦', '...'],
		['Ã¢â‚¬â€', '-']
	];

	var observer = null;
	var scheduled = 0;

	function applyReplacements(text) {
		var updated = String(text || '');
		var index;

		for (index = 0; index < replacements.length; index++) {
			updated = updated.split(replacements[index][0]).join(replacements[index][1]);
		}

		return updated;
	}

	function patchTextNodes(root) {
		var walker;
		var node;
		var updated;

		if (!root || !document.createTreeWalker) {
			return;
		}

		walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
		node = walker.nextNode();

		while (node) {
			updated = applyReplacements(node.nodeValue);

			if (updated !== node.nodeValue) {
				node.nodeValue = updated;
			}

			node = walker.nextNode();
		}
	}

	function runPatch() {
		var root = document.getElementById('root');

		scheduled = 0;

		if (!root) {
			return;
		}

		patchTextNodes(root);
	}

	function schedulePatch() {
		if (scheduled) {
			return;
		}

		scheduled = window.setTimeout(runPatch, 30);
	}

	function init() {
		var root = document.getElementById('root');

		if (!root || observer) {
			schedulePatch();
			return;
		}

		runPatch();

		observer = new MutationObserver(schedulePatch);
		observer.observe(root, {
			childList: true,
			subtree: true,
			characterData: true
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}());

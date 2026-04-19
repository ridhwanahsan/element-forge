/* jshint esversion: 6 */
jQuery(document).ready(function ($) {
	'use strict';

	var AJAXURL = efTbData.ajaxUrl;
	var NONCE   = efTbData.nonce;
	var i18n    = efTbData.i18n;

	// -----------------------------------------------------------------------
	// INIT: bind events on existing rows
	// -----------------------------------------------------------------------
	bindRowEvents($('#ef-display-rules-list'));
	bindRowEvents($('#ef-exclusion-rules-list'));

	// -----------------------------------------------------------------------
	// ADD RULE
	// -----------------------------------------------------------------------
	$(document).on('click', '.ef-add-rule-btn', function () {
		var listId   = $(this).data('target');
		var prefix   = $(this).data('prefix');
		var isExcl   = $(this).data('type') === 'exclusion';
		var $list    = $('#' + listId);
		var index    = $list.find('.ef-rule-row').length;
		var defCond  = isExcl ? 'specific_pages' : 'entire_site';
		var $row     = $(buildRuleRowHtml(prefix, index, defCond, [], isExcl));

		// Show exclusion container if it was hidden
		if ( isExcl ) {
			$list.show();
		}

		$list.append($row);
		bindRowEvents($row);
		reindex($list, prefix);
	});

	// -----------------------------------------------------------------------
	// REMOVE RULE
	// -----------------------------------------------------------------------
	$(document).on('click', '.ef-remove-rule', function () {
		var $row    = $(this).closest('.ef-rule-row');
		var $list   = $row.closest('[id$="-rules-list"]');
		var prefix  = $list.attr('id') === 'ef-display-rules-list' ? 'ef_display_rules' : 'ef_exclusion_rules';
		$row.remove();
		reindex($list, prefix);
	});

	// -----------------------------------------------------------------------
	// CONDITION SELECT CHANGE
	// -----------------------------------------------------------------------
	$(document).on('change', '.ef-condition-select', function () {
		var $select     = $(this);
		var val         = $select.val();
		var $row        = $select.closest('.ef-rule-row');
		var $searchArea = $row.find('.ef-search-area');

		if (val === 'specific_pages') {
			$searchArea.show();
		} else {
			$searchArea.hide();
		}
	});

	// -----------------------------------------------------------------------
	// LIVE SEARCH TYPING
	// -----------------------------------------------------------------------
	$(document).on('input', '.ef-search-input', function () {
		var $input    = $(this);
		var q         = $input.val().trim();
		var $row      = $input.closest('.ef-rule-row');
		var $dropdown = $row.find('.ef-search-dropdown');

		clearTimeout($input.data('ef_timer'));

		if (q.length < 2) {
			$dropdown.html('<p class="ef-search-hint">' + i18n.minChars + '</p>').show();
			return;
		}

		$dropdown.html('<p class="ef-search-hint ef-searching">' + i18n.searching + '</p>').show();

		var timer = setTimeout(function () {
			$.ajax({
				url:      AJAXURL,
				method:   'GET',
				data:     { action: 'ef_search_items', nonce: NONCE, q: q },
				success:  function (res) {
					if (!res.success || !res.data || res.data.length === 0) {
						$dropdown.html('<p class="ef-search-hint">' + i18n.noResults + '</p>');
						return;
					}
					renderDropdown($row, $dropdown, res.data);
				},
			});
		}, 300);

		$input.data('ef_timer', timer);
	});

	// -----------------------------------------------------------------------
	// CLICK OUTSIDE → close dropdown
	// -----------------------------------------------------------------------
	$(document).on('click', function (e) {
		if (!$(e.target).closest('.ef-search-box-wrap').length) {
			$('.ef-search-dropdown').hide();
		}
	});

	// -----------------------------------------------------------------------
	// REMOVE CHIP
	// -----------------------------------------------------------------------
	$(document).on('click', '.ef-chip-remove', function (e) {
		e.stopPropagation();
		var $chip  = $(this).closest('.ef-chip');
		var $row   = $chip.closest('.ef-rule-row');
		$chip.remove();
		syncJson($row);
	});

	// =====================================================================
	// HELPERS
	// =====================================================================

	/**
	 * Bind custom search-box events on a list (or single row).
	 */
	function bindRowEvents($scope) {
		// Show dropdown on input focus if needed
		$scope.find('.ef-search-input').on('focus', function () {
			var q = $(this).val().trim();
			if (q.length < 2) {
				$(this).closest('.ef-rule-row').find('.ef-search-dropdown')
					.html('<p class="ef-search-hint">' + i18n.minChars + '</p>').show();
			}
		});
	}

	/**
	 * Render search result dropdown items.
	 */
	function renderDropdown($row, $dropdown, items) {
		var html    = '';
		var $chips  = $row.find('.ef-chips-wrap');
		// Collect already-selected IDs to prevent duplicates
		var selected = [];
		$chips.find('.ef-chip').each(function () {
			selected.push($(this).data('id') + '|' + $(this).data('type'));
		});

		items.forEach(function (item) {
			var key       = item.id + '|' + item.type;
			var isActive  = selected.indexOf(key) !== -1;
			html += '<div class="ef-result-item' + (isActive ? ' is-selected' : '') + '" ' +
				'data-id="' + item.id + '" ' +
				'data-type="' + item.type + '" ' +
				'data-kind="' + item.kind + '" ' +
				'data-title="' + escHtml(item.title) + '" ' +
				'data-label="' + escHtml(item.label) + '">' +
				'<span class="ef-result-badge">' + escHtml(item.label) + '</span>' +
				'<span class="ef-result-title">' + escHtml(item.title) + '</span>' +
				(isActive ? '<span class="ef-result-check">&#10003;</span>' : '') +
			'</div>';
		});

		$dropdown.html(html).show();

		// Click a result
		$dropdown.find('.ef-result-item').off('click').on('click', function () {
			var $item = $(this);
			if ($item.hasClass('is-selected')) {
				// deselect
				var removeId   = $item.data('id');
				var removeType = $item.data('type');
				$chips.find('.ef-chip').filter(function () {
					return $(this).data('id') == removeId && $(this).data('type') == removeType;
				}).remove();
				$item.removeClass('is-selected').find('.ef-result-check').remove();
			} else {
				// add chip
				var chip = buildChip($item.data('id'), $item.data('type'), $item.data('kind'), $item.data('title'), $item.data('label'));
				$chips.append(chip);
				$item.addClass('is-selected').append('<span class="ef-result-check">&#10003;</span>');
			}
			syncJson($row.closest('.ef-rule-row'));
		});
	}

	function buildChip(id, type, kind, title, label) {
		return $('<span class="ef-chip"></span>')
			.attr({'data-id': id, 'data-type': type, 'data-kind': kind})
			.append('<span class="ef-chip-type">' + escHtml(label) + '</span>')
			.append('<span class="ef-chip-title">' + escHtml(title) + '</span>')
			.append('<button type="button" class="ef-chip-remove" title="Remove">&#x2715;</button>');
	}

	/**
	 * Sync chips → hidden JSON field.
	 */
	function syncJson($row) {
		var items = [];
		$row.find('.ef-chips-wrap .ef-chip').each(function () {
			items.push({
				id:    $(this).data('id'),
				type:  $(this).data('type'),
				kind:  $(this).data('kind'),
				title: $(this).find('.ef-chip-title').text(),
				label: $(this).find('.ef-chip-type').text(),
			});
		});
		$row.find('.ef-items-json').val(JSON.stringify(items));
	}

	/**
	 * Re-index form field names after adding/removing rows.
	 */
	function reindex($list, prefix) {
		$list.find('.ef-rule-row').each(function (i) {
			$(this).attr('data-index', i);
			$(this).find('.ef-condition-select').attr('name', prefix + '[' + i + '][condition]');
			$(this).find('.ef-items-json').attr('name', prefix + '[' + i + '][items]');
		});
	}

	/**
	 * Build rule row HTML (client-side equivalent of PHP render_rule_row).
	 */
	function buildRuleRowHtml(prefix, index, conditionVal, items, isExcl) {
		var options  = efTbData.conditionOptions;
		var selHtml  = '<select name="' + prefix + '[' + index + '][condition]" class="ef-condition-select">';
		$.each(options, function (val, label) {
			if (isExcl && val === 'entire_site') return;
			selHtml += '<option value="' + val + '"' + (val === conditionVal ? ' selected' : '') + '>' + label + '</option>';
		});
		selHtml += '</select>';

		var showSearch = conditionVal === 'specific_pages' ? '' : 'display:none;';

		var html = '<div class="ef-rule-row" data-index="' + index + '">' +
			'<div class="ef-rule-inner">' +
			selHtml +
			'<div class="ef-search-area" style="' + showSearch + '">' +
			'<div class="ef-chips-wrap"></div>' +
			'<div class="ef-search-box-wrap">' +
			'<input type="text" class="ef-search-input" placeholder="' + i18n.searchPlaceholder + '" autocomplete="off" />' +
			'<div class="ef-search-dropdown" style="display:none;"><p class="ef-search-hint">' + i18n.minChars + '</p></div>' +
			'</div>' +
			'<input type="hidden" class="ef-items-json" name="' + prefix + '[' + index + '][items]" value="[]" />' +
			'</div>' +
			'</div>' +
			'<button type="button" class="ef-remove-rule" title="Remove Rule">&#x2715;</button>' +
			'</div>';

		return html;
	}

	function escHtml(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}
});

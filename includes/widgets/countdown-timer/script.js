(function () {
	'use strict';

	function pad(value) {
		return (value < 10 ? '0' : '') + value;
	}

	function updateExpiredState(elements) {
		elements.days.textContent = '00';
		elements.hours.textContent = '00';
		elements.minutes.textContent = '00';
		elements.seconds.textContent = '00';
	}

	function getCountdownElements(wrapper) {
		var days = wrapper.querySelector('.ef-days');
		var hours = wrapper.querySelector('.ef-hours');
		var minutes = wrapper.querySelector('.ef-minutes');
		var seconds = wrapper.querySelector('.ef-seconds');

		if (!days || !hours || !minutes || !seconds) {
			return null;
		}

		return {
			days: days,
			hours: hours,
			minutes: minutes,
			seconds: seconds
		};
	}

	function startCountdown(wrapper) {
		var targetDate = new Date(wrapper.getAttribute('data-date')).getTime();
		var elements = getCountdownElements(wrapper);
		var timerId = 0;

		if (isNaN(targetDate) || !elements) {
			return;
		}

		if (wrapper.elementForgeCountdownTimer) {
			window.clearInterval(wrapper.elementForgeCountdownTimer);
		}

		function updateTimer() {
			var now = Date.now();
			var distance = targetDate - now;
			var days = 0;
			var hours = 0;
			var minutes = 0;
			var seconds = 0;

			if (distance <= 0) {
				updateExpiredState(elements);

				if (timerId) {
					window.clearInterval(timerId);
				}

				wrapper.elementForgeCountdownTimer = null;
				return;
			}

			days = Math.floor(distance / (1000 * 60 * 60 * 24));
			hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			seconds = Math.floor((distance % (1000 * 60)) / 1000);

			elements.days.textContent = pad(days);
			elements.hours.textContent = pad(hours);
			elements.minutes.textContent = pad(minutes);
			elements.seconds.textContent = pad(seconds);
		}

		updateTimer();
		timerId = window.setInterval(updateTimer, 1000);
		wrapper.elementForgeCountdownTimer = timerId;
	}

	function initCountdowns(scope) {
		var context = scope && scope.querySelectorAll ? scope : document;
		var wrappers = [];
		var index = 0;

		if (context.classList && context.classList.contains('ef-countdown-wrapper')) {
			wrappers.push(context);
		}

		if (context.querySelectorAll) {
			wrappers = wrappers.concat(
				Array.prototype.slice.call(context.querySelectorAll('.ef-countdown-wrapper[data-date]'))
			);
		}

		for (index = 0; index < wrappers.length; index++) {
			startCountdown(wrappers[index]);
		}
	}

	function bindElementorHook() {
		if (!window.elementorFrontend || !window.elementorFrontend.hooks) {
			return;
		}

		window.elementorFrontend.hooks.addAction(
			'frontend/element_ready/elementforge_countdown_timer.default',
			function ($scope) {
				if ($scope && $scope.length) {
					initCountdowns($scope[0]);
				}
			}
		);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initCountdowns(document);
			bindElementorHook();
		});
	} else {
		initCountdowns(document);
		bindElementorHook();
	}
}());

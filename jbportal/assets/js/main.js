/* jbportal — front-end JS */
(function ($) {
	'use strict';

	$(function () {
		// Mobile nav toggle.
		$(document).on('click', '.jb-nav-toggle', function () {
			var $nav = $(this).closest('.jb-nav');
			var open = $nav.toggleClass('is-open').hasClass('is-open');
			$(this).attr('aria-expanded', open ? 'true' : 'false');
		});

		// Highlight already-bookmarked jobs.
		try {
			var saved = JSON.parse(localStorage.getItem('jbportal_bookmarks') || '[]');
			$('.jb-bookmark').each(function () {
				if (saved.indexOf(String($(this).data('job-id'))) !== -1) {
					$(this).addClass('is-saved').find('.jb-heart').text('♥');
				}
			});
		} catch (e) {}

		// Toggle bookmark.
		$(document).on('click', '.jb-bookmark', function (e) {
			e.preventDefault();
			var $btn   = $(this);
			var jobId  = $btn.data('job-id');

			$.post(jbportal.ajaxUrl, {
				action:  'jbportal_toggle_bookmark',
				nonce:   jbportal.nonce,
				job_id:  jobId
			}).done(function (resp) {
				if (resp && resp.success) {
					var saved = JSON.parse(localStorage.getItem('jbportal_bookmarks') || '[]');
					if (resp.data.state === 'added') {
						saved.push(String(jobId));
						$btn.addClass('is-saved').find('.jb-heart').text('♥');
					} else {
						saved = saved.filter(function (id) { return id !== String(jobId); });
						$btn.removeClass('is-saved').find('.jb-heart').text('♡');
					}
					localStorage.setItem('jbportal_bookmarks', JSON.stringify(saved));
				} else if (resp && resp.data && resp.data.message) {
					alert(resp.data.message);
				}
			}).fail(function (xhr) {
				if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
					alert(xhr.responseJSON.data.message);
				} else {
					alert(jbportal.i18n.error);
				}
			});
		});

		// Newsletter form (footer).
		$(document).on('submit', '.jb-newsletter', function (e) {
			e.preventDefault();
			var $form = $(this);
			var $msg  = $form.find('.jb-newsletter-message');
			var email = $form.find('input[name="email"]').val();
			$msg.text('');

			$.post(jbportal.ajaxUrl, {
				action: 'jbportal_newsletter',
				nonce:  jbportal.nonce,
				email:  email
			}).done(function (resp) {
				if (resp && resp.success) {
					$form[0].reset();
					$msg.text(resp.data.message);
				} else if (resp && resp.data && resp.data.message) {
					$msg.text(resp.data.message);
				}
			}).fail(function (xhr) {
				$msg.text((xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) || jbportal.i18n.error);
			});
		});

		// Apply form ux: scroll to anchor + button state.
		$('.jb-apply-form').on('submit', function () {
			$(this).find('button[type=submit]').prop('disabled', true).text(jbportal.i18n.applying);
		});
	});
})(jQuery);

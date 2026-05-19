/* jbportal — front-end JS */
(function ($) {
	'use strict';

	$(function () {
		// Theme toggle (light / dark / auto).
		var saved = localStorage.getItem('jbportal_theme') || 'auto';
		document.documentElement.setAttribute('data-jb-theme', saved);

		$(document).on('click', '.jb-theme-toggle', function () {
			var cur = document.documentElement.getAttribute('data-jb-theme') || 'auto';
			var next = cur === 'auto' ? 'light' : (cur === 'light' ? 'dark' : 'auto');
			document.documentElement.setAttribute('data-jb-theme', next);
			localStorage.setItem('jbportal_theme', next);
			$(this).attr('data-state', next).find('.jb-theme-toggle-label').text(next);
		});

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

		// Cookie banner.
		try {
			var cookieState = localStorage.getItem('jbportal_cookie');
			var $notice = $('#jb-cookie-notice');
			if ($notice.length && !cookieState) {
				$notice.removeAttr('hidden');
			}
			$notice.on('click', '[data-jb-cookie]', function () {
				localStorage.setItem('jbportal_cookie', $(this).data('jb-cookie'));
				$notice.attr('hidden', true);
			});
		} catch (e) {}

		// Follow toggle.
		$(document).on('click', '.jb-follow', function () {
			var $btn = $(this);
			$.post(jbportal.ajaxUrl, {
				action:  'jbportal_toggle_follow',
				nonce:   jbportal.nonce,
				post_id: $btn.data('post-id')
			}).done(function (resp) {
				if (resp && resp.success) {
					var followed = resp.data.state === 'followed';
					$btn.toggleClass('is-active', followed);
					$btn.find('.jb-follow-label').text(followed ? 'Following' : 'Follow');
				}
			});
		});

		// Copy link.
		$(document).on('click', '.jb-share-copy', function () {
			var url = $(this).data('url');
			if (navigator.clipboard) {
				navigator.clipboard.writeText(url).then(function () {
					alert('Link copied!');
				});
			} else {
				prompt('Copy link:', url);
			}
		});

		// Apply form ux: scroll to anchor + button state.
		$('.jb-apply-form').on('submit', function () {
			$(this).find('button[type=submit]').prop('disabled', true).text(jbportal.i18n.applying);
		});

		// Load more jobs (archive page).
		$(document).on('click', '.jb-load-more', function () {
			var $btn  = $(this);
			var $msg  = $btn.siblings('.jb-load-more-msg');
			var page  = parseInt( $btn.data('page'), 10 ) + 1;
			var max   = parseInt( $btn.data('max'), 10 );
			var query = $btn.data('query');

			$btn.prop('disabled', true).text(jbportal.i18n.loading || 'Loading…');
			$msg.hide();

			$.post(jbportal.ajaxUrl, {
				action:  'jbportal_load_more_jobs',
				nonce:   jbportal.nonce,
				page:    page,
				query:   JSON.stringify(query)
			}).done(function (resp) {
				if (resp && resp.success && resp.data.html) {
					$('#jb-jobs-container').append(resp.data.html);
					$btn.data('page', page);
					if (page >= max) {
						$btn.hide();
						$msg.text(jbportal.i18n.no_more || 'All jobs loaded.').show();
					} else {
						$btn.prop('disabled', false).text(jbportal.i18n.load_more || 'Load more jobs');
					}
				} else {
					$btn.hide();
					$msg.text(jbportal.i18n.no_more || 'No more jobs.').show();
				}
			}).fail(function () {
				$btn.prop('disabled', false).text(jbportal.i18n.load_more || 'Load more jobs');
				$msg.text(jbportal.i18n.error).show();
			});
		});
	});
})(jQuery);

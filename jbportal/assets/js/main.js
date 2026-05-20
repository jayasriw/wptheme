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

		// AI job description generation.
		$(document).on('click', '#jb-ai-generate', function () {
			if (!jbportal.aiEnabled) { return; }
			var $btn    = $(this);
			var $status = $('#jb-ai-status');
			var $area   = $('#jb-job-description');
			var title   = $('[name="job_title"]').val();
			var company = $('[name="job_company"]').val();
			var loc     = $('[name="job_location"]').val();
			var type    = $('[name="job_type"] option:selected').text();

			if (!title) {
				alert(jbportal.i18n.ai_need_title || 'Please enter a job title first.');
				return;
			}

			$btn.prop('disabled', true);
			$status.text(jbportal.i18n.ai_generating || 'Generating…').show();

			$.post(jbportal.ajaxUrl, {
				action:       'jbportal_generate_description',
				nonce:        jbportal.nonce,
				job_title:    title,
				job_company:  company,
				job_location: loc,
				job_type:     type
			}).done(function (resp) {
				if (resp && resp.success && resp.data.description) {
					$area.val(resp.data.description);
					$status.text(jbportal.i18n.ai_done || 'Done! Review and edit as needed.').show();
				} else {
					var msg = (resp && resp.data && resp.data.message) ? resp.data.message : (jbportal.i18n.error || 'Error.');
					$status.text(msg).show();
				}
			}).fail(function () {
				$status.text(jbportal.i18n.error || 'Request failed.').show();
			}).always(function () {
				$btn.prop('disabled', false);
			});
		});

		// Save search button.
		$(document).on('click', '#jb-save-search', function () {
			var $btn  = $(this);
			var $msg  = $('#jb-save-search-msg');
			var nonce = $btn.data('nonce');
			var params = new URLSearchParams(window.location.search);

			$btn.prop('disabled', true);
			$msg.hide();

			$.post(jbportal.ajaxUrl, {
				action:   'jbportal_save_search',
				nonce:    nonce,
				keyword:  params.get('keyword') || '',
				location: params.get('location') || '',
				category: params.get('category') || '',
				type:     params.get('type') || '',
				remote:   params.get('remote') || ''
			}).done(function (resp) {
				if (resp && resp.success) {
					$msg.text(resp.data.message || 'Saved!').css('color', 'var(--jb-primary)').show();
					$btn.prop('disabled', true).text('Saved');
				} else {
					var errMsg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Error saving search.';
					$msg.text(errMsg).css('color', 'var(--jb-secondary)').show();
					$btn.prop('disabled', false);
				}
			}).fail(function () {
				$msg.text('Request failed.').css('color', 'var(--jb-secondary)').show();
				$btn.prop('disabled', false);
			});
		});

		// Dashboard: delete saved search.
		$(document).on('click', '.jb-delete-saved-search', function (e) {
			e.preventDefault();
			var $btn      = $(this);
			var searchId  = $btn.data('search-id');
			var nonce     = $btn.data('nonce');

			if (!confirm(jbportal.i18n.confirm_delete || 'Delete this saved search?')) { return; }
			$btn.prop('disabled', true);

			$.post(jbportal.ajaxUrl, {
				action:    'jbportal_delete_saved_search',
				nonce:     nonce,
				search_id: searchId
			}).done(function (resp) {
				if (resp && resp.success) {
					$btn.closest('tr').fadeOut(300, function () { $(this).remove(); });
				} else {
					$btn.prop('disabled', false);
					alert((resp && resp.data && resp.data.message) ? resp.data.message : 'Error.');
				}
			}).fail(function () {
				$btn.prop('disabled', false);
			});
		});

		// Dashboard: mark notifications read when notifications tab is viewed.
		if (window.location.search.indexOf('tab=notifications') !== -1) {
			$.post(jbportal.ajaxUrl, {
				action: 'jbportal_mark_notifications_read',
				nonce:  jbportal.nonce
			});
		}
	});
})(jQuery);

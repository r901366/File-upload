jQuery(document).ready(function () {
	/**
	 * Translates a string using the i18n object.
	 * Add ___() function to prototype so the ___() method is available everywhere in the app.
	 *
	 * @param string text The text to translate.
	 * @returns string The translated text.
	 */
	function ___(text) {
		return wpscore_admin_edit_posts_ajax_var.i18n[text] || text;
	}

	var refreshInterval;

	function startRefreshInterval() {
		refreshInterval = setInterval(refreshStatusColumn, 5000);
	}

	function stopRefreshInterval() {
		clearInterval(refreshInterval);
	}

	function refreshStatusColumn() {
		function getEditingPostIds() {
			var editingRows = document.querySelectorAll(
				'tr[class^="edit-"]:not([style*="display:none"])',
			);
			return Array.prototype.map
				.call(editingRows, function (row) {
					var match = row.className.match(/edit-(\d+)/);
					return match ? match[1] : null;
				})
				.filter(Boolean);
		}

		function updateElementIfChanged(oldElement, newElement) {
			if (!oldElement || !newElement) return false;

			var oldStatusElement = oldElement.querySelector('[data-wps-ai-status]');
			var newStatusElement = newElement.querySelector('[data-wps-ai-status]');

			if (oldStatusElement && newStatusElement) {
				var oldStatus = oldStatusElement.getAttribute('data-wps-ai-status');
				var newStatus = newStatusElement.getAttribute('data-wps-ai-status');
				if (oldStatus !== newStatus) {
					oldElement.innerHTML = newElement.innerHTML;
					return true;
				}
			}
			return false;
		}

		function processAiJobs() {
			jQuery.ajax({
				type: 'post',
				dataType: 'json',
				url: wpscore_admin_edit_posts_ajax_var.url,
				data: {
					action: 'wpscore_process_ai_jobs',
					nonce: wpscore_admin_edit_posts_ajax_var.nonce,
				},
				success: function (response) {
					if (response.success === false) {
						return;
					}
					// update dom with new credits left value in response.data.data
					var creditsLeftColor =
						parseInt(response.data.data.wps_credits_left) > 10
							? 'green'
							: 'red';
					jQuery('#ai_status small strong').html(
						response.data.data.wps_credits_left,
					);
					jQuery('#ai_status small strong').css('color', creditsLeftColor);
				},
				error: function (error) {
					console.error(error);
				},
			});
		}

		var editingPostIds = getEditingPostIds();

		fetch(window.location.href, {
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
		})
			.then(function (response) {
				return response.text();
			})
			.then(function (html) {
				var parser = new DOMParser();
				var doc = parser.parseFromString(html, 'text/html');
				var newRows = doc.querySelectorAll('.wp-list-table tbody tr');

				Array.prototype.forEach.call(newRows, function (newRow) {
					var postIdMatch = newRow.className.match(/post-(\d+)/);
					if (!postIdMatch) return;

					var postId = postIdMatch[1];
					if (editingPostIds.indexOf(postId) !== -1) return;

					var oldRow = document.querySelector('tr.post-' + postId);
					if (!oldRow) return;

					var oldTitle = oldRow.querySelector('.wps-ai-title');
					var newTitle = newRow.querySelector('.wps-ai-title');
					var oldDescription = oldRow.querySelector('.wps-ai-description');
					var newDescription = newRow.querySelector('.wps-ai-description');

					var titleChanged = updateElementIfChanged(oldTitle, newTitle);
					var descriptionChanged = updateElementIfChanged(
						oldDescription,
						newDescription,
					);

					if (titleChanged) {
						oldTitle.style.transition = 'background-color 0.5s ease-in-out';
						oldTitle.style.backgroundColor = '#ffeb3b';
						setTimeout(function () {
							oldRow.innerHTML = newRow.innerHTML;
						}, 1000);
					}
					if (descriptionChanged) {
						oldDescription.style.transition =
							'background-color 0.5s ease-in-out';
						oldDescription.style.backgroundColor = '#ffeb3b';
						setTimeout(function () {
							oldRow.innerHTML = newRow.innerHTML;
						}, 1000);
					}
				});

				processAiJobs();
			})
			.catch(function (error) {
				console.error('Erreur de rafraîchissement des lignes:', error);
			});
	}

	startRefreshInterval();

	jQuery.ajax({
		type: 'post',
		dataType: 'json',
		url: wpscore_admin_edit_posts_ajax_var.url,
		data: {
			action: 'wpscore_get_wps_credits_left',
			nonce: wpscore_admin_edit_posts_ajax_var.nonce,
		},
		success: function (response) {
			if (response.success === false) {
				return;
			}

			// Add html to div#ai_status
			var creditsLeftColor =
				parseInt(response.data.wps_credits_left) > 10 ? 'green' : 'red';
			jQuery('#ai_status').append(
				[
					'<small>',
					'<a style="font-weight:bold; transform: translate(2px, -5px); position:relative; display: inline-block;" href="' +
						wpscore_admin_edit_posts_ajax_var.links['ai'] +
						'" target="_blank">?</a> ',
					'</small>',
					'<br>',
					'<small>',
					___('Credits left') +
						': <strong style="color:' +
						creditsLeftColor +
						'">' +
						response.data.wps_credits_left +
						'</strong>',
					' <a target="_blank" href="' +
						wpscore_admin_edit_posts_ajax_var.links['wps-credits'] +
						'" target="_blank">(' +
						___('Get more Credits') +
						')</a></small>',
					'</small>',
				].join(''),
			);
		},
		error: function (e) {
			console.error(___('Error while getting credits balance'));
			console.error(e);
		},
	});

	jQuery(document).on('click', '.wps-ai-generate-content-button', function (e) {
		e.preventDefault();
		stopRefreshInterval();

		var postId = jQuery(this).data('post-id');
		var aiJobType = jQuery(this).data('type');
		var aiJobId = jQuery(this).data('ai-job-id');

		var div_id = '#wps-ai-generation-status-' + postId + '-' + aiJobType;
		var statusSpan$ = jQuery(div_id + ' span.wps-ai-status');
		var detailsSpan$ = jQuery(div_id + ' span.wps-ai-details');
		var slugInfoSpan$ = jQuery(
			div_id + ' span.wps-ai-title-generation-slug-info',
		);

		var pendingLabel = {
			title: ___('Rewriting the title'),
			description: ___('Generating the description'),
		};
		var pendingHtml =
			'<strong style="color:#2271b1"><span class="dashicons dashicons-clock"></span>' +
			pendingLabel[aiJobType] +
			' <span class="loader"></span></strong>';

		detailsSpan$.html('');
		jQuery(this).removeClass('show').addClass('hide');
		slugInfoSpan$.hide();
		statusSpan$.html(pendingHtml);
		statusSpan$.attr('data-wps-ai-status', 'pending');

		jQuery.ajax({
			type: 'post',
			dataType: 'json',
			url: wpscore_admin_edit_posts_ajax_var.url,
			data: {
				action: 'wpscore_generate_content_in_posts_listing',
				nonce: wpscore_admin_edit_posts_ajax_var.nonce,
				post_id: postId,
				ai_job_type: aiJobType,
				ai_job_id: aiJobId,
			},
			success: function (response) {
				jQuery(this).attr('data-ai-job-id', response.data.ai_job_id);
			},
			error: function () {},
			complete: function () {
				setTimeout(startRefreshInterval, 5000);
			},
		});
	});
});

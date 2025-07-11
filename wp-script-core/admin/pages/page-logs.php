<?php
/**
 * Logs page
 *
 * @package PLAYER\Admin\Pages
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * WPS CORE Logs page callback function
 *
 * @return void
 */
function wpscore_logs_page() {
	?>
	<div id="wp-script">
		<div class="content-tabs">
			<?php WPSCORE()->display_logo(); ?>
			<?php WPSCORE()->display_tabs(); ?>
			<div class="tab-content">
				<div class="tab-pane active" id="logs">
					<div><!-- empty div for auto padding--></div>
					<div v-cloak class="padding-top-15">
						<div class="row text-center v-cloak--block">
							<div class="col-xs-12"><p><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> <?php esc_html_e( 'Loading Core...', 'wpscore_lang' ); ?></span></p></div>
						</div>
						<div class="v-cloak--hidden">
							<!--**************-->
							<!-- LOADING DATA -->
							<!--**************-->
							<template v-if="loading.loadingData">
								<div class="row text-center">
									<div class="col-xs-12"><p><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> <?php esc_html_e( 'Loading Data...', 'wpscore_lang' ); ?></span></p></div>
								</div>
							</template>
							<transition name="fade">
								<template v-if="dataLoaded">
									<div v-if="logs.length == 0" class="row">
										<div class="col-xs-12"><p class="text-center"><?php esc_html_e( 'No log has been written yet', 'wpscore_lang' ); ?></p></div>
									</div>
									<div v-else class="row">
										<div class="col-xs-12">
											<div class="pull-right">
												<button class="btn btn-default btn-sm" @click.prevent="copyLogs"><i class="fa" :class="loading.copyLogs ? 'fa-spinner fa-pulse fa-fw' : 'fa-clipboard'" aria-hidden="true"></i> <?php esc_html_e( 'Copy logs to clipboard', 'wpscore_lang' ); ?></button>
												<button class="btn btn-danger btn-sm" @click.prevent="deleteLogs"><i class="fa" :class="loading.deleteLogs ? 'fa-spinner fa-pulse fa-fw' : 'fa-trash-o'" aria-hidden="true"></i> <?php esc_html_e( 'Delete logs', 'wpscore_lang' ); ?></button>
											</div>
										</div>
										<div class="col-xs-12 margin-top-10">
											<div class="table-responsive">
												<table class="table table-striped table-bordered table-hover">
													<tr>
														<th width="140"><?php esc_html_e( 'Date', 'wpscore_lang' ); ?></th>
														<th>
															<form class="form-inline">
															<?php esc_html_e( 'Type', 'wpscore_lang' ); ?>
																<select name="logsType" class="form-control" v-model="filters.type">
																	<option value="">       <?php esc_html_e( 'All', 'wpscore_lang' ); ?></option>
																	<option value="success"><?php esc_html_e( 'Success', 'wpscore_lang' ); ?></option>
																	<option value="notice"> <?php esc_html_e( 'Notice', 'wpscore_lang' ); ?></option>
																	<option value="warning"><?php esc_html_e( 'Warning', 'wpscore_lang' ); ?></option>
																	<option value="error">  <?php esc_html_e( 'Error', 'wpscore_lang' ); ?></option>
																</select>
															</form>
														</th>
														<th>
															<form class="form-inline">
															<?php esc_html_e( 'Product', 'wpscore_lang' ); ?>
																<select name="logsProduct" class="form-control" v-model="filters.product">
																	<option value=""><?php esc_html_e( 'All', 'wpscore_lang' ); ?></option>
																	<option v-for="productName in products" v-bind:value="productName">{{productName}}</option>
																</select>
															</form>
														</th>
														<th>
															<form class="form-inline"><?php esc_html_e( 'Message', 'wpscore_lang' ); ?>
																<input type="text" class="form-control input-sm" placeholder="<?php esc_html_e( 'Filter messages', 'wpscore_lang' ); ?>" v-model="filters.message">
															</form>
														</th>
														<th>
															<form class="form-inline"><?php esc_html_e( 'Location', 'wpscore_lang' ); ?>
																<input type="text" class="form-control input-sm" placeholder="<?php esc_html_e( 'Filter locations', 'wpscore_lang' ); ?>" v-model="filters.location">
															</form>
														</th>
													</tr>
													<tr v-for="log in filteredlogs">
														<td><small>{{log.date}}</small></td>
														<td><span class="label" :class="log.class">{{log.type}}</span></td>
														<td><small v-html="log.product"></small></td>
														<td><small v-html="log.message"></small></td>
														<td><small>{{log.file_uri}}:{{log.file_line}}</small></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</template>
							</transition>
						</div>
					</div>
				</div>
			</div>
			<?php WPSCORE()->display_footer(); ?>
		</div>
	</div>
	<?php
}

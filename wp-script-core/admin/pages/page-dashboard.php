<?php
/**
 * Default page
 *
 * @package CORE\Admin\Pages
 */

use WPS\Ai\Application\Services\AiUtils;
use WPS\Utils\Application\Services\LinkBuilder;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * WPS CORE Dashboard page callback function
 *
 * @return void
 */
function wpscore_dashboard_page() {
	$current_user      = wp_get_current_user();
	$current_blog_user = new WP_User( $current_user->ID, '', get_current_blog_id() );
	?>
	<div id="wp-script">
		<div class="content-tabs" id="dashboard">
			<?php WPSCORE()->display_logo(); ?>
			<?php WPSCORE()->display_tabs(); ?>
			<div class="tab-content">
				<div class="tab-pane active">
					<div v-cloak class="padding-top-15">
						<div class="row text-center v-cloak--block">
							<div class="col-xs-12 loading"><p><i class="fa fa-cog fa-spin fa-2x fa-fw" aria-hidden="true"></i><br><?php esc_html_e( 'Loading Core', 'wpscore_lang' ); ?>...</span></p></div>
						</div>
						<div class="v-cloak--hidden">
							<?php if ( WPSCORE_Requirements::is_wordfence_activated() ) : ?>
								<div class="row mb-5">
									<div class="col-xs-12 text-center">
										<div class="alert alert-info">
											<p class="mb-2"><strong><?php esc_html_e( 'Wordfence firewall is enabled', 'wpscore_lang' ); ?></strong></p>
												<p><?php esc_html_e( 'Wordfence Firewall mode is enabled and can prevent WP-Script plugins to work properly because of false positive.', 'wpscore_lang' ); ?> — <a target="_blank" rel="nofollow noreferrer" href="https://www.wordfence.com/help/firewall/#whitelisted-urls-and-false-positives"><?php esc_html_e( 'Learn more', 'wpscore_lang' ); ?></a></p>
											</div>
									</div>
								</div>
							<?php endif; ?>

							<?php if ( ! in_array( 'administrator', (array) $current_blog_user->roles, true ) ) : ?>
								<div class="row">
									<div class="col-xs-12 col-md-6 col-md-push-3">
										<h3 class="text-center"><?php esc_html_e( 'Administrator Area', 'wpscore_lang' ); ?></h3>
										<div class="alert alert-info">
											<p class="text-center"><?php esc_html_e( 'You must be logged as an Administrator to access this page.', 'wpscore_lang' ); ?></p>
										</div>
									</div>
								</div>
							<?php elseif ( ! WPSCORE()->php_version_ok() ) : ?>
								<div class="row">
									<div class="col-xs-12 col-md-6 col-md-push-3">
										<h3 class="text-center"><?php esc_html_e( 'A more recent PHP version is required', 'wpscore_lang' ); ?></h3>
										<div class="alert alert-danger">
											<?php /* translators: %s is the current too old installed PHP version */ ?>
											<p><strong>PHP >= <?php echo esc_html( WPSCORE_PHP_REQUIRED ); ?></strong> <?php printf( esc_html_x( 'is required to use WP-Script products. Your PHP version (%s) is too old. Please contact your hoster to update it', '[PHP version] is required...', 'wpscore_lang' ), PHP_VERSION ); ?></p>
										</div>
									</div>
								</div>
							<?php else : ?>
								<!--**************-->
								<!-- LOADING DATA -->
								<!--**************-->
								<template v-if="loading.loadingData">
									<div class="row text-center">
										<div class="col-xs-12 loading"><p><i class="fa fa-cog fa-spin-reverse fa-2x fa-fw" aria-hidden="true"></i><br><?php esc_html_e( 'Loading Data', 'wpscore_lang' ); ?>...</span></p></div>
									</div>
								</template>
								<transition name="fade">
									<div v-if="dataLoaded">
										<div class="row" v-if="fullLifetime.can_upgrade">
											<div class="col-xs-12">
												<a v-bind:href="buildLink(fullLifetime.url + '?utm_source=core&utm_medium=dashboard&utm_campaign=fullLifetime&utm_content=learnMore')" target="_blank" class="alert text-center p-2 mb-5" style="border: 2px solid #A950A9; display: flex; align-items: center; justify-content: center; color: #555;">
													<strong style="color: #333">FULL ACCESS <span style="color: #A950A9">LIFETIME</span></strong>
													<span class="px-2"><?php esc_html_e( 'Get All Products / Unlimited Sites for only', 'wpscore_lang' ); ?>
														<span v-if="fullLifetime.price_to_pay < fullLifetime.regular_price" style="display: inline-flex"><strike>${{ fullLifetime.regular_price }}</strike> <strong class="ml-1">${{ fullLifetime.price_to_pay }}</strong></span>
														<strong v-else>${{ fullLifetime.price_to_pay }}</strong>
													</span>
													<span class="btn btn-sm btn-default" style="background: #932493; color: #fff; border: none !important; font-weight: bold;"><?php esc_html_e( 'Learn more', 'wpscore_lang' ); ?></span>
												</a>
											</div>
										</div>
										<div v-if="core !== false && core.installed_version" class="row">
											<div class="col-xs-12">
												<p class="core__version text-right">
													WP-Script Core v{{core.installed_version}}
													<template v-if="!core.is_latest_version">
														<button v-if="loading.updatingCore == false" @click="updateCore" class="btn btn-sm btn-success"><i class="fa fa-refresh" aria-hidden="true"></i> <?php esc_html_e( 'Update to', 'wpscore_lang' ); ?> v{{core.latest_version}}</button>
														<button v-if="loading.updatingCore == true" class="btn btn-sm btn-success disabled" disabled><i class="fa fa-cog fa-spin fa-fw" aria-hidden="true"></i> <?php esc_html_e( 'Updating to', 'wpscore_lang' ); ?> v{{core.latest_version}}</button>
														<button v-if="loading.updatingCore == 'activating'" class="btn btn-sm btn-success disabled" disabled><i class="fa fa-cog fa-spin fa-fw" aria-hidden="true"></i> <?php esc_html_e( 'Activating', 'wpscore_lang' ); ?>...</button>
														<button v-if="loading.updatingCore == 'reloading'" class="btn btn-sm btn-success disabled" disabled><i class="fa fa-cog fa-spin-reverse fa-fw" aria-hidden="true"></i> <?php esc_html_e( 'Reloading', 'wpscore_lang' ); ?>...</button>
													</template>

													<?php if ( WPSCORE()->get_option( 'is_site_verified' ) ) : ?>
														<br>
														<span class="label label-success"><?php esc_html_e( 'Site verified', 'wpscore_lang' ); ?> <i class="fa fa-check" aria-hidden="true"></i>
														</span>
													<?php endif; ?>
												</p>
											</div>
										</div>

										<?php if ( WPSCORE()->must_verify_site() ) : ?>
											<div class="alert p-0" style="border: 2px solid #337ab7;  align-items: center; justify-content: center; color: rgb(85, 85, 85);">
												<h4 class="text-center p-4 m-0" style="background-color: #337ab7; color: white;"><?php esc_html_e( 'Site verification required', 'wpscore_lang' ); ?></h4>
												<verify-site></verify-site>
											</div>
										<?php endif; ?>

										<!--***********************-->
										<!-- LICENSE NOT ACTIVATED -->
										<!--***********************-->
										<div v-if="!userHasLicense || loading.checkingLicense == 'reloading'">
											<div class="row">
												<div class="col-xs-12">
													<div class="alert text-center" v-bind:class="licenceBoxClass">
														<h3>
															<template v-if="loading.checkingLicense != 'reloading' && loading.checkingAccount != 'reloading' && !error"><?php esc_html_e( 'Activation required', 'wpscore_lang' ); ?></template>
															<template v-if="error">{{error}}</template>
															<template v-if="loading.checkingLicense == 'reloading' || loading.checkingAccount == 'reloading'"><?php esc_html_e( 'Activation Successful', 'wpscore_lang' ); ?> - <?php esc_html_e( 'Reloading', 'wpscore_lang' ); ?>...</template>
														</h3>
															<div class="row">
																<div class="col-xs-12 col-md-8 col-md-push-2 col-lg-6 col-lg-push-3">

																	<div class="input-group">
																		<span class="input-group-addon"><span class="fa fa-unlock-alt"></span></span>
																		<input v-model="userLicenseInput" spellcheck="false" type="text" class="form-control check-license" placeholder="<?php esc_html_e( 'Paste your WP-Script License Key here', 'wpscore_lang' ); ?>" ref="refLicenseInput" />
																		<span class="input-group-btn">
																			<button @click.prevent="checkLicense" class="btn btn-default" v-bind:disabled="toggleLicenseBtn" type="button">
																				<template v-if="loading.checkingLicense === true">
																					<i class="fa fa-cog fa-spin fa-fw" aria-hidden="true"></i> <?php esc_html_e( 'Activating', 'wpscore_lang' ); ?>...
																				</template>
																				<template v-if="loading.checkingLicense == 'reloading'"><i class="fa fa-check text-success" aria-hidden="true"></i></template>
																				<template v-if="loading.checkingLicense === false"><?php esc_html_e( 'Activate', 'wpscore_lang' ); ?></template>
																			</button>
																		</span>
																	</div>

																</div>
															</div>
														<p class="margin-top-10 margin-bottom-20"><small><a href="https://www.wp-script.com/my-account/?utm_source=core&utm_medium=dashboard&utm_campaign=account&utm_content=getLicenseKey" title="<?php esc_html_e( 'Go to your WP-Script account to get your license key', 'wpscore_lang' ); ?>" target="_blank"><?php esc_html_e( 'Go to your WP-Script account to get your license key', 'wpscore_lang' ); ?></a></small></p>

														<button class="btn btn-transparent" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
														<?php esc_html_e( "You don't have a WP-Script license yet?", 'wpscore_lang' ); ?>
														</button>
														<div class="collapse" id="collapseExample">
															<form id="form-check-account" action="" method="post">
																<div class="row padding-top-20">
																	<div class="col-xs-12 col-md-8 col-md-push-2 col-lg-6 col-lg-push-3">
																		<div class="input-group">
																			<span class="input-group-addon"><span class="fa fa-envelope"></span></span>
																			<input v-model="userEmailInput" type="text" class="form-control" value="" ref="refEmailInput"/>
																			<div class="input-group-btn">
																				<button @click.prevent="checkAccount" class="btn btn-default" type="submit">
																					<template v-if="loading.checkingAccount === true">
																						<i class="fa fa-cog fa-spin fa-fw" aria-hidden="true"></i> <?php esc_html_e( 'Creating account', 'wpscore_lang' ); ?>...
																					</template>
																					<template v-if="loading.checkingAccount == 'reloading'"><i class="fa fa-check text-success" aria-hidden="true"></i></template>
																					<template v-if="loading.checkingAccount === false"><?php esc_html_e( 'Create my WP-Script Account', 'wpscore_lang' ); ?></template>
																				</button>
																			</div>
																		</div>
																	</div>
																</div>
															</form>
															<p class="margin-top-10"><small><?php esc_html_e( 'You will receive your login details to this email address', 'wpscore_lang' ); ?></small></p>
														</div>
													</div>
												</div>
											</div>
										</div>
										<!--*******************-->
										<!-- LICENSE ACTIVATED -->
										<!--*******************-->
										<div v-else>
											<div class="row">
												<div class="col-xs-12 col-md-8">
													<div class="alert text-center p-4 alert-license">
														<h4><?php esc_html_e( 'Your WP-Script License Key', 'wpscore_lang' ); ?></h4>
														<div class="row padding-top-10 padding-bottom-10">
															<div class="col-xs-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
																<div class="input-group">
																	<input spellcheck="false" type="text" class="form-control text-center" id="input-license" v-model="userLicenseInput" ref="refLicenseInput">
																	<span class="input-group-btn">
																		<button @click.prevent="checkLicense" class="btn btn-default" v-bind:class="{'disabled' : !userLicenseChanged}" v-bind:disabled="!userLicenseChanged" type="button"><i class="fa" v-bind:class="licenseButtonIconClass" aria-hidden="true"></i></button>
																	</span>
																</div>
															</div>
														</div>
														<?php $error_msg = WPSCORE()->get_option( 'error_msg' ); ?>
														<?php if ( '' !== $error_msg ) : ?>
															<p>
															<?php
																// PHPCS:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
																echo $error_msg;
															?>
																</p>
														<?php else : ?>
															<p><?php esc_html_e( 'Your license key is valid but hidden for security reasons.', 'wpscore_lang' ); ?></p>
														<?php endif; ?>
													</div>
												</div>
												<div class="col-xs-12 col-md-4">
													<div class="alert alert-ai text-center p-4">
														<div class="ai-label"><?php esc_html_e( 'WPS AI Content Generator', 'wpscore_lang' ); ?></div>
														<h4>
															<img style="width:16px;" src="<?php echo esc_url( WPSCORE_URL . 'admin/assets/images/sparkles-white.svg' ); ?>" alt="<?php esc_attr_e( 'AI content generation', 'wpscore_lang' ); ?>">
															<?php esc_html_e( 'Boost your SEO with AI', 'wpscore_lang' ); ?>
														</h4>
														<div class="ai-infos">
															<div class="ai-infos-text">
																<?php esc_html_e( 'Rewrite videos titles and generate unique descriptions in no time with Uncensored AI.', 'wpscore_lang' ); ?>
															</div>
															<a class="ai-infos-learn-more-link" href="<?php echo esc_url( LinkBuilder::get( 'ai' ) ); ?>" target="_blank"><?php esc_html_e( 'Learn more', 'wpscore_lang' ); ?> 🢒</a>
														</div>

													</div>
													<p class="ai-credit-left">
														<?php
														$credits_left       = AiUtils::getCreditsLeft();
														$credits_left_color = $credits_left <= 10 ? 'color: red;' : 'color: green;';
														?>
														<span><?php esc_html_e( 'Credits left', 'wpscore_lang' ); ?>: <strong style="<?php echo esc_html( $credits_left_color ); ?>"><?php echo esc_html( $credits_left ); ?></strong></span>
														<span>(<a target="_blank" href="<?php echo esc_url( LinkBuilder::get( 'wps-credits' ) ); ?>"><?php esc_html_e( 'Get more Credits', 'wpscore_lang' ); ?></a>)</span>
													</p>
												</div>
											</div>

											<products v-if="productsFromApi.themes && Object.keys(productsFromApi.themes).length > 0" key="themes" v-bind:products="productsFromApi.themes" type="theme" v-bind:installed-products="installedProducts['themes']" v-bind:user-license="userLicense"></products>
											<products v-if="productsFromApi.plugins && Object.keys(productsFromApi.plugins).length > 0" key="plugins" v-bind:products="productsFromApi.plugins" type="plugin" v-bind:installed-products="installedProducts['plugins']" v-bind:user-license="userLicense"></products>
											<div v-if="productsFromApi.themes && Object.keys(productsFromApi.themes).length === 0">
												<div class="row">
													<div class="col-xs-12">
														<div class="alert alert-info text-center">
															<p><?php esc_html_e( 'We could not retrieve products list from WP-Script.', 'wpscore_lang' ); ?></p>
															<button @click.prevent="location.reload()" class="btn btn-primary mt-2">Retry</button>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</transition>
							<?php endif; ?>
						</div>
						<p class="text-right"><small><?php esc_html_e( 'cUrl version', 'wpscore_lang' ); ?>: <?php echo esc_html( WPSCORE()->get_curl_version() ); ?></small></p>
					</div>
				</div>
			</div>
			<?php WPSCORE()->display_footer(); ?>

			<!-- Create Connection Infos Modal -->
			<div class="modal fade" id="connection-infos-modal">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php esc_html_e( 'Close', 'wpscore_lang' ); ?></span></button>
							<h4 class="modal-title text-center"><?php esc_html_e( 'How WP-Script products connection works?', 'wpscore_lang' ); ?></h4>
						</div>
						<div class="modal-body text-center">
							<div class="row">
								<p><?php esc_html_e( 'The button under each product will automatically change depending on the step you are in.', 'wpscore_lang' ); ?></p>
								<div class="col-xs-12 col-md-6">
									<div class="thumbnail">
										<img src="<?php echo esc_url( (string) WPSCORE_URL ); ?>admin/assets/images/product-connection-step-1.jpg">
										<div class="caption"><h5><strong>1.</strong> <?php esc_html_e( 'Purchase any product you need', 'wpscore_lang' ); ?></h5></div>
									</div>
								</div>
								<div class="col-xs-12 col-md-6">
									<div class="thumbnail">
										<img src="<?php echo esc_url( (string) WPSCORE_URL ); ?>admin/assets/images/product-connection-step-2.jpg">
										<div class="caption"><h5><strong>2.</strong> <?php esc_html_e( 'Connect the purchased product', 'wpscore_lang' ); ?></h5></div>
									</div>
								</div>
								<div class="col-xs-12 col-md-6">
									<div class="thumbnail">
										<img src="<?php echo esc_url( (string) WPSCORE_URL ); ?>admin/assets/images/product-connection-step-3.jpg">
										<div class="caption"><h5><strong>3.</strong> <?php esc_html_e( 'Install the purchased product', 'wpscore_lang' ); ?></h5></div>
									</div>
								</div>
								<div class="col-xs-12 col-md-6">
									<div class="thumbnail">
										<img src="<?php echo esc_url( (string) WPSCORE_URL ); ?>admin/assets/images/product-connection-step-4.jpg">
										<div class="caption"><h5><strong>4.</strong> <?php esc_html_e( 'Activate the purchased product', 'wpscore_lang' ); ?></h5></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /Create Connection Infos Modal -->

			<!-- Create Requirements Infos Modal -->
			<div class="modal fade" id="requirements-modal">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php esc_html_e( 'Close', 'wpscore_lang' ); ?></span></button>
							<h4/ class="modal-title text-center"><?php esc_html_e( 'Requirements for', 'wpscore_lang' ); ?> {{currentProduct.infos.title}}</h4>
						</div>
						<div class="modal-body">
							<div v-if="currentProduct.infos.requirements == ''" class="alert alert-success text-center">
								<p><?php esc_html_e( 'There is no requirement to use this product. This product will work properly.', 'wpscore_lang' ); ?></p>
							</div>
							<template v-else>
								<p class="text-center"><?php esc_html_e( 'The following PHP elements must be installed on your server to use this product', 'wpscore_lang' ); ?></p>
								<table class="table table-bordered table-striped">
									<tr>
										<th><?php esc_html_e( 'Name', 'wpscore_lang' ); ?></th>
										<th><?php esc_html_e( 'Type', 'wpscore_lang' ); ?></th>
										<th><?php esc_html_e( 'Installed', 'wpscore_lang' ); ?></th>
									</tr>
									<tr v-for="requirement in currentProduct.infos.requirements">
										<td>
											<strong>{{requirement.name}}</strong>
										</td>
										<td>
											PHP {{requirement.type}}
										</td>
										<td>
											<span v-if="requirement.status" class="text-success">
												<i class="fa fa-check" aria-hidden="true"></i> <?php esc_html_e( 'yes', 'wpscore_lang' ); ?>
											</span>
											<span v-else  class="text-danger">
												<i class="fa fa-times" aria-hidden="true"></i> <?php esc_html_e( 'no', 'wpscore_lang' ); ?>
											</span>
										</td>
									</tr>
								</table>
								<div v-if="currentProduct.isAllRequirementsOk" class="alert alert-success text-center">
									<p><?php esc_html_e( 'All required PHP elements are installed on your server. This product will work properly.', 'wpscore_lang' ); ?></p>
								</div>
								<div v-else class="alert alert-danger text-center">
									<p><?php esc_html_e( 'Some PHP elements are not installed on your server. This product may not work properly. Please contact your web hoster.', 'wpscore_lang' ); ?></p>
								</div>
							</template>
						</div>
					</div>
				</div>
			</div>
			<!-- /Create Requirements Infos Modal -->

			<!-- Create Connection Infos Modal -->
			<div class="modal fade" id="install-modal">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php esc_html_e( 'Close', 'wpscore_lang' ); ?></span></button>
							<h4 class="modal-title text-center"><?php esc_html_e( 'An error occured while installing or updating', 'wpscore_lang' ); ?></h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-xs-12">
									<div class="alert alert-danger text-center">
										<?php esc_html_e( 'Your server configuration prevents the product to be installed automatically.', 'wpscore_lang' ); ?>
										<br>
										<br>
										<?php esc_html_e( 'Please try to install the product manually.', 'wpscore_lang' ); ?>
										<br>
										<br>
										&#10149; <a href="https://www.wp-script.com/docs/general-informations/wp-script-themes/themes-installation-guide/#2-from-the-wordpress-native-theme-installer" target="_blank"><?php esc_html_e( 'Guide to install a WP-Script Theme manually', 'wpscore_lang' ); ?></a>
										<br>
										&#10149; <a href="https://www.wp-script.com/docs/general-informations/wp-script-plugins/plugins-installation-guide/#2-from-the-wordpress-native-plugin-installer" target="_blank"><?php esc_html_e( 'Guide to install a WP-Script Plugin manually', 'wpscore_lang' ); ?></a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /Create Connection Infos Modal -->
			<vue-snotify></vue-snotify>
		</div>
	</div>
	<?php
}

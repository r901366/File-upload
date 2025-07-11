//jetpack fix
_.contains = _.includes;
window.lodash = _.noConflict();

/**
 * Helper function to return the translated text if exists, or the text itself as fallback.
 * @param string text The text to translate.
 * @returns string The translated text, or the text itself as fallback.
 */
function ___(text) {
	return WPSCORE_dashboard.i18n[text] || text;
}

function buildLink(url, autologin = true) {
	if (false === autologin) {
		return url;
	}
	if (WPSCORE_dashboard.link_autologin_params) {
		var params = [];
		lodash.each(WPSCORE_dashboard.link_autologin_params, function (value, key) {
			params.push(key + '=' + value);
		});
		url += url.indexOf('?') === -1 ? '?' : '&';
		url += params.join('&');
	}
	return url;
}

jQuery(document).ready(function () {
	if (document.getElementById('dashboard')) {
		/**
		 * Add Event Bus
		 */
		var EventBus = new Vue();

		/**
		 * Add components
		 */

		/** Verify Site component */
		var wpsVueVerifySite = {
			// Verify Site component name
			name: 'verify-site',
			data: function () {
				return {
					loading: {
						verifying: false,
					},
					verificationCode: '',
					siteKey: '',
					deadlineToVerifySiteInDays: -1,
				};
			},

			mounted: function () {
				this.$http
					.post(
						WPSCORE_dashboard.ajax.url,
						{
							action: 'wpscore_get_site_verification_data',
							nonce: WPSCORE_dashboard.ajax.nonce,
						},
						{
							emulateJSON: true,
						},
					)
					.then(
						function (response) {
							// success callback
							if (response.body === null) {
								return;
							}
							this.siteKey = response.body.site_key;
							this.deadlineToVerifySiteInDays =
								response.body.deadline_to_verify_site_in_days;
						},
						function (error) {},
					)
					.then(function () {});
			},
			methods: {
				verifySite: function () {
					this.loading.verifying = true;
					this.$http
						.post(
							WPSCORE_dashboard.ajax.url,
							{
								action: 'wpscore_verify_site',
								nonce: WPSCORE_dashboard.ajax.nonce,
								verification_code: this.verificationCode,
							},
							{
								emulateJSON: true,
							},
						)
						.then(
							function (response) {
								// success callback
								if (response.body === null) {
									this.loading.verifying = false;
									this.$snotify.error(___('Error, please try again later'), {
										timeout: 5000,
										showProgressBar: true,
										closeOnClick: true,
										pauseOnHover: true,
									});
									return;
								}
								if (response.body.code == 'error') {
									this.$snotify.error(response.body.message, {
										timeout: 5000,
										showProgressBar: true,
										closeOnClick: true,
										pauseOnHover: true,
									});
									this.loading.verifying = false;
								} else {
									this.loading.verifying = ___('Reloading');
									this.$snotify.success(response.body.message, {
										timeout: 3000,
										showProgressBar: true,
										closeOnClick: true,
										pauseOnHover: true,
									});
									setTimeout(() => {
										document.location.href = 'admin.php?page=wpscore-dashboard';
									}, 3000);
								}
							},
							function (error) {
								// error callback
								this.$snotify.error(JSON.stringify(error), {
									timeout: 5000,
									showProgressBar: true,
									closeOnClick: true,
									pauseOnHover: true,
								});
								this.loading.verifying = false;
							},
						)
						.then(function () {});
				},
			},
			template: `
        <div class="site_verification_code">
          <div class="alert alert-license mb-0 text-center p-4" style="border-radius: 0; border-bottom: 2px solid #eee;">
            <p><strong>{{ ___('Why you need to verify this website?') }}</strong></p>
            <p>{{ ___('This verification ensures that you own this site.') }}</p>
            <p>{{ ___('It helps to secure your data in case your individual WP-Script license key is stolen or lost.') }}</p>
          </div>

          <div style="max-width:350px; margin: 0 auto;" class="p-4">
            <p class="text-center"><strong>{{ ___('How to verify this website?') }}</strong></p>
            <p><strong>1. <a target="_blank" v-bind:href="WPSCORE_dashboard.wpscript_url + '/my-account/verify-site/?site_key=' + this.siteKey">{{ ___('Get the site verification code') }}</a></strong></p>
            <p class="site_verification_code-zone">
              <strong>2.</strong>
              <input class="form-control input-sm site_verification_code-code" type="text" v-model="verificationCode" v-bind:placeholder="___('Paste the code here')" spellcheck="false" />
            </p>
            <p><strong>3.</strong> <button class="btn btn-primary px-3" v-on:click.prevent="verifySite"><strong>{{ ___('Verify this website') }}</strong></button></p>
            <p class="pt-4">{{ ___('Need help?')}}<br><a href="https://docs.wp-script.com/getting-started/site-verification" target="_blank">{{ ___('Read the documentation') }}</a> • <a href="http://www.wp-script.com/open-a-ticket/" target="_blank">{{ ___('Open a support ticket') }}</a></p>
          </div>

          <div v-if="this.deadlineToVerifySiteInDays > 0" class="alert alert-warning m-0 text-center">
            <p><strong>{{this.deadlineToVerifySiteInDays}}</strong> {{ ___('days left to verify your website.') }}</p>
            <p>{{ ___('Your products on this site will be blocked after this date.') }}</p>
            <p>{{ ___('Make sure to verify all your sites that use WP-Script products.') }}</p>
          </div>

          <div v-if="this.deadlineToVerifySiteInDays == 0" class="alert alert-danger m-0 text-center">
            <p>{{ ___('Your products on this site are blocked until you verify this website.') }}</p>
            <p>{{ ___('Make sure to verify all your websites that use WP-Script products.') }}</p>
          </div>
        </div>
      `,
		};

		/** Product component */
		var wpsVueProduct = {
			// Product component name
			name: 'product',

			// Product component props
			props: [
				'productType',
				'productFromApi',
				'installedProduct',
				'userLicense',
			],

			// Product component data
			data: function () {
				return {
					loading: {
						connect: false,
						install: false,
						toggle: false,
					},
					showPopOver: false,
					currentUrl: window.location.hostname,
				};
			},

			// Product component computed data
			computed: {
				bgGradient: function () {
					if (
						!this.productFromApi.bg_color_start ||
						!this.productFromApi.bg_color_start
					)
						return false;
					var opacity = 1;

					var rgb = {
						start: hexToRgb(this.productFromApi.bg_color_start),
						end: hexToRgb(this.productFromApi.bg_color_end),
					};
					var rgba = {
						start: [rgb.start.r, rgb.start.g, rgb.start.b, opacity],
						end: [rgb.end.r, rgb.end.g, rgb.end.b, opacity],
					};
					return (
						'background: linear-gradient( 135deg, rgba(' +
						rgba.end.join(',') +
						') 50%, rgba(' +
						rgba.start.join(',') +
						') 100% );'
					);
				},
				bgImage: function () {
					if (this.productType === 'theme' && this.productFromApi.preview_url) {
						var bgUrl = this.productFromApi.preview_url.replace(
							'.png',
							'-530x150.jpg',
						);
						return 'background-image: url(' + bgUrl + ');';
					}
					return false;
				},
				productIs: function () {
					return {
						activated:
							lodash.has(this.installedProduct, 'state') &&
							this.installedProduct.state == 'activated',
						connected: this.productFromApi.status == 'connected',
						debug: this.productFromApi.debug,
						adult: this.productFromApi.adult_product,
						freemium: this.productFromApi.model == 'freemium',
						installed: this.installedProduct !== undefined,
						updatable:
							lodash.has(this.installedProduct, 'installed_version') &&
							versionCompare(
								this.productFromApi.latest_version,
								this.installedProduct.installed_version,
							) > 0,
					};
				},
				isAllRequirementsOk: function () {
					var output = true;
					lodash.each(this.productFromApi.requirements, function (r) {
						if (r.status === false) output = false;
					});
					return output;
				},
				plus18Icon: function () {
					return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 328.863 328.863" style="enable-background:new 0 0 328.863 328.863;" xml:space="preserve" width="25" height="25" src="https://www.easyvideosite.com/wp-content/themes/wps/img/plus-18.svg"><g id="_x34_4-18Plus_movie"><g><path class="wps-plus-18" d="M104.032,220.434V131.15H83.392V108.27h49.121v112.164H104.032z" fill="#cf48cf"></path></g><g><path class="wps-plus-18" d="M239.552,137.23c0,9.76-5.28,18.4-14.08,23.201c12.319,5.119,20,15.84,20,28.32c0,20.16-17.921,32.961-45.921,32.961    c-28.001,0-45.921-12.641-45.921-32.48c0-12.801,8.32-23.682,21.28-28.801c-9.44-5.281-15.52-14.24-15.52-24 c0-17.922,15.681-29.281,40.001-29.281C224.031,107.15,239.552,118.83,239.552,137.23z M180.51,186.352 c0,9.441,6.721,14.721,19.041,14.721c12.32,0,19.2-5.119,19.2-14.721c0-9.279-6.88-14.561-19.2-14.561 C187.23,171.791,180.51,177.072,180.51,186.352z M183.391,138.83c0,8.002,5.76,12.48,16.16,12.48c10.4,0,16.16-4.479,16.16-12.48 c0-8.318-5.76-12.959-16.16-12.959C189.15,125.871,183.391,130.512,183.391,138.83z" fill="#cf48cf"></path></g><g><path class="wps-plus-18" d="M292.864,120.932c4.735,13.975,7.137,28.592,7.137,43.5c0,74.752-60.816,135.568-135.569,135.568 S28.862,239.184,28.862,164.432c0-74.754,60.816-135.568,135.569-135.568c14.91,0,29.527,2.4,43.5,7.137V5.832 C193.817,1.963,179.24,0,164.432,0C73.765,0,0.001,73.764,0.001,164.432s73.764,164.432,164.431,164.432 S328.862,255.1,328.862,164.432c0-14.807-1.962-29.385-5.831-43.5H292.864z" fill="#cf48cf"></path></g><g><polygon class="wps-plus-18" points="284.659,44.111 284.659,12.582 261.987,12.582 261.987,44.111 230.647,44.111 230.647,66.781 261.987,66.781  261.987,98.309 284.659,98.309 284.659,66.781 316.186,66.781 316.186,44.111" fill="#cf48cf"></polygon></g></g></svg>';
				},
			},

			// Product component methods
			methods: {
				choosePlan: function (plan) {
					this.plan = plan;
				},
				togglePopOver: function () {
					this.showPopOver = !this.showPopOver;
				},
				connectProduct: function () {
					this.loading.connect = true;
					this.$http
						.post(
							WPSCORE_dashboard.ajax.url,
							{
								action: 'wpscore_connect_product',
								nonce: WPSCORE_dashboard.ajax.nonce,
								product_type: this.productType + 's',
								product_sku: this.productFromApi.sku,
								product_title: this.productFromApi.title,
							},
							{
								emulateJSON: true,
							},
						)
						.then(
							function (response) {
								// success callback
								if (response.body === null) {
									this.loading.connect = false;
									this.$snotify.error('Error, please try again later', {
										timeout: 5000,
										showProgressBar: true,
										closeOnClick: true,
										pauseOnHover: true,
									});
									return;
								}
								if (response.body.code == 'error') {
									this.$snotify.error(response.body.message, {
										timeout: 5000,
										showProgressBar: true,
										closeOnClick: true,
										pauseOnHover: true,
									});
									this.loading.connect = false;
								} else {
									this.loading.connect = ___('Reloading');
									this.$snotify.success(response.body.message, {
										timeout: 3000,
										showProgressBar: true,
										closeOnClick: true,
										pauseOnHover: true,
									});
									setTimeout(() => {
										document.location.href = 'admin.php?page=wpscore-dashboard';
									}, 3000);
								}
							},
							function (error) {
								// error callback
								this.$snotify.error(JSON.stringify(error), {
									timeout: 5000,
									showProgressBar: true,
									closeOnClick: true,
									pauseOnHover: true,
								});
								this.loading.connect = false;
							},
						)
						.then(function () {});
				},
				installProduct: function (method) {
					this.loading.install = true;
					this.$http
						.post(
							WPSCORE_dashboard.ajax.url,
							{
								action: 'wpscore_install_product',
								nonce: WPSCORE_dashboard.ajax.nonce,
								product_sku: this.productFromApi.sku,
								product_type: this.productType,
								product_zip: this.productFromApi.zip_file,
								product_slug: this.productFromApi.slug,
								product_folder_slug: this.productFromApi.folder_slug,
								method: method,
								new_version: this.productFromApi.latest_version,
							},
							{
								emulateJSON: true,
							},
						)
						.then(
							function (response) {
								// installProduct success callback
								if (
									response.body === true ||
									response.body == '<div class="wrap"><h1></h1></div>'
								) {
									this.loading.toggle = ___('Reloading');
									document.location.href = 'admin.php?page=wpscore-dashboard';
								} else {
									this.showInstallModal(response.body);
								}
							},
							function (error) {
								// installProduct error callback
								this.$snotify.error(JSON.stringify(error), {
									timeout: 5000,
									showProgressBar: true,
									closeOnClick: true,
									pauseOnHover: true,
								});
							},
						)
						.then(function () {
							this.loading.install = false;
						});
				},
				toggleProduct: function () {
					this.loading.toggle = true;
					this.$http
						.post(
							WPSCORE_dashboard.ajax.url,
							{
								action: 'wpscore_toggle_' + this.productType,
								nonce: WPSCORE_dashboard.ajax.nonce,
								product_folder_slug: this.productFromApi.folder_slug,
							},
							{
								emulateJSON: true,
							},
						)
						.then(
							function (response) {
								// toggleProduct success callback
								if (lodash.has(this.installedProduct, 'state')) {
									// Following line is commented to prevent "activating / deactivating" text flickering
									// this.installedProduct.state = response.body.product_state;
								}
							},
							function (error) {
								// toggleProduct error callback
								this.$snotify.error(JSON.stringify(error), {
									timeout: 5000,
									showProgressBar: true,
									closeOnClick: true,
									pauseOnHover: true,
								});
							},
						)
						.then(
							function () {
								this.loading.toggle = ___('Reloading');
								if (this.installedProduct.state == 'activated') {
									document.location.href =
										'admin.php?page=wpscore-dashboard&activated=true';
								} else {
									document.location.href = 'admin.php?page=wpscore-dashboard';
								}
							}.bind(this),
						);
				},
				showRequirementsModal: function (productInfos, isAllRequirementsOk) {
					EventBus.$emit(
						'show-requirements-modal',
						productInfos,
						isAllRequirementsOk,
					);
				},
				showInstallModal: function (productInfos) {
					EventBus.$emit('show-install-modal', productInfos);
				},
				showConnectionInfosModal: function () {
					EventBus.$emit('show-connection-infos-modal');
				},
			},

			// Product component template
			template: `
        <div class="product" v-bind:id="'product_' + productFromApi.sku.toLowerCase()" v-bind:class="{ 'product__installed': productIs.installed, 'product__connected' : productIs.connected, 'product__activated': productIs.activated,'product__plugin' : productType == 'plugin', 'product__theme' : productType == 'theme'}">
          <div class="product__gradient" v-bind:style="bgGradient"></div>
          <div class="product__image" v-bind:style="bgImage"></div>
          <div class="product__logo"><img v-bind:src="productFromApi.icon_url"></div>
          <div class="product__description">
            <div class="product__requirements" v-on:click="showRequirementsModal(productFromApi, isAllRequirementsOk)">
              <small>{{___('Requirements')}} <i class="fa" v-bind:class="[isAllRequirementsOk ? 'fa-check text-success' : 'fa-exclamation-triangle text-danger']" aria-hidden="true"></i></small>
            </div>

            <h4 class="product__title">{{productFromApi.title}} <span v-if="productIs.adult" v-html="plus18Icon" class="product__adult-icon"></span></h4>
            <div class="product__installed">
              <span v-if="productIs.installed" class="product__version-installed">
                v{{installedProduct.installed_version}}
              </span>
              <span v-else class="product__not-installed">
                {{___('Not installed')}}
              </span>
            </div>

            <p class="product__exerpt">{{productFromApi.exerpt}} <span class="product__learn-more">&mdash; <a v-bind:href="buildLink(productFromApi.url + '?utm_source=core&utm_medium=dashboard&utm_campaign=' + productFromApi.slug + '&utm_content=learnMore')" target="_blank" v-bind:title="___('View details about') + ' ' + productFromApi.title">{{ ___('Learn more') }}</a></span></p>
          </div>

          <div class="product__footer">
            <template v-if="!productIs.installed && (productIs.connected || productIs.freemium)">
              <button v-if="!loading.toggle && !loading.install" v-on:click.prevent="installProduct('install')" class="btn btn-sm btn-default" v-bind:title="'Install ' + productFromApi.title"><i class="fa fa-download" aria-hidden="true"></i> {{___('Install')}}</button>
              <button v-if="!loading.toggle && loading.install" class="btn btn-sm btn-default disabled" disabled v-bind:title="'Installing ' + productFromApi.title"><i class="fa fa-cog fa-spin fa-fw" aria-hidden="true"></i> {{___('Installing')}}...</button>
              <button v-if="loading.toggle == true" class="btn btn-sm btn-default disabled" disabled v-bind:title="'Activating ' + productFromApi.title"><i class="fa fa-cog fa-spin fa-fw" aria-hidden="true"></i> {{___('Activating')}}...</button>
              <button v-if="loading.toggle == 'reloading'" class="btn btn-sm btn-default disabled" disabled v-bind:title="'Reloading'" target="_blank"><i class="fa fa-cog fa-spin-reverse fa-fw" aria-hidden="true"></i> {{___('Reloading')}}...</button>
            </template>

            <template v-if="!productIs.connected">
              <template v-if="productFromApi.connectable_sites >= 1 || productFromApi.connectable_sites == 'unlimited'">
                <button v-on:click.prevent="togglePopOver" class="btn btn-sm btn-success" v-bind:title="___('Connect') + productFromApi.title" target="_blank">{{ ___('Connect') }} &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i></button>
              </template>
              <template v-else>
                <a v-bind:href="buildLink(productFromApi.url + '?utm_source=core&utm_medium=dashboard&utm_campaign=' + productFromApi.slug + '&utm_content=buyNow')" target="_blank" class="btn btn-sm btn-pink" v-bind:title="___('Buy') + ' ' + productFromApi.title">
                  {{___('Buy now')}}
                </a>
              </template>
            </template>
            <template v-if="productIs.connected && productIs.installed">
                <transition name="wps-anim__y-up" mode="out-in" key="reloading">
                  <span v-if="loading.toggle == 'reloading'"><i class="fa fa-cog fa-spin-reverse fa-fw"></i> {{___('Reloading')}}...</span>
                  <span v-else key="not-reloading">
                    <template v-if="productIs.updatable">
                      <button v-if="!loading.install" class="btn btn-sm btn-success" href="#" v-on:click.prevent="installProduct('upgrade')"><i aria-hidden="true" class="fa fa-refresh"></i> {{___('Update to')}} v{{productFromApi.latest_version}}</button>
                      <button v-else class="btn btn-sm btn-success disabled" disabled href="#"><i aria-hidden="true" class="fa fa-cog fa-spin fa-fw"></i> {{___('Updating to')}} v{{productFromApi.latest_version}}...</button>
                    </template>
                    <template v-if="installedProduct.state == 'deactivated'">
                        <span v-if="loading.toggle == false"><a class="btn btn-sm btn-default product__btn--activate" href="#" v-on:click.prevent="toggleProduct">{{___('Activate')}}</a></span>
                        <span v-else><i class="fa fa-cog fa-spin fa-fw"></i> {{___('Activating')}}...</span>
                    </template>
                    <template v-if="installedProduct.state == 'activated'">
                      <template v-if="productType == 'plugin'">
                          <span v-if="loading.toggle == false"><a class="btn btn-sm btn-default product__btn--deactivate" href="#" v-on:click.prevent="toggleProduct">{{___('Deactivate')}}</a></span>
                          <span v-else><i class="fa fa-cog fa-spin fa-fw"></i> {{___('Deactivating')}}...</span>
                      </template>
                      <template v-else>{{___('Active theme')}}</template>
                    </template>
                  </span>
                </transition>
            </template>
          </div>

          <div class="product__over" v-bind:class="{'show product__over--show':showPopOver}">
            <template v-if="productIs.connected && !productIs.installed">
              <p class="product__over-p">{{productFromApi.title}} {{ ___('must be installed to use it') }}</p>
            </template>

            <template v-if="!productIs.connected">
              <template v-if="productFromApi.connectable_sites >= 1 || productFromApi.connectable_sites == 'unlimited'">
                <p class="product__over-p">{{ ___('Connect') }} <strong>{{productFromApi.title}}</strong> {{ ___('on') }} <span class="product__over-domain">{{currentUrl}}</span>
                <template v-if="productFromApi.connectable_sites !== 'unlimited'"><br><small>{{___('Connecting will decrease your remaining websites by 1')}}</small></template>
                <br><span class="text-success">{{___('Remaining websites')}}: <strong>{{productFromApi.connectable_sites}}</strong></span></p>
                <div class="product__footer product__footer--connect">
                <p class="m-0"><button  v-if="loading.connect == false" v-on:click.prevent="connectProduct" class="btn btn-sm btn-success" v-bind:title="___('Connect') + ' ' + productFromApi.title" target="_blank"><strong>{{___('Connect')}}</strong></button>
                  <button v-else class="btn btn-sm btn-success disabled" disabled v-bind:title="___('Connection of') + ' ' + productFromApi.title" target="_blank"><i class="fa fa-cog fa-spin fa-fw" aria-hidden="true"></i></button>
                </p>
                  <button v-on:click.prevent="togglePopOver" class="btn btn-sm ml-2">{{___('Cancel')}}</button>
                </div>
              </template>
            </template>
          </div>
        </div>
      `,
		};

		/** Products component */
		var wpsVueProducts = {
			// Products component name
			name: 'products',

			// Products component use those components
			components: {
				product: wpsVueProduct,
			},

			// Products component filters
			filters: {
				titled(value) {
					return value.charAt(0).toUpperCase() + value.slice(1) + 's';
				},
			},

			// Products component props
			props: ['products', 'type', 'installedProducts', 'userLicense'],

			// Products component data
			data: function () {
				return {
					filter: 'All',
				};
			},

			mounted: function () {
				this.filter = ___('All');
			},

			// Products component methods
			methods: {
				toggleFilter: function (newValue) {
					lodash.each(
						this.products,
						function (productFromApi) {
							var productSku = productFromApi.sku;
							switch (newValue) {
								case 'all':
									productFromApi.show = true;
									this.filter = ___('All');
									break;
								case 'connected':
									productFromApi.show = productFromApi.status == 'connected';
									this.filter = ___('Connected');
									break;
								case 'notConnected':
									productFromApi.show = productFromApi.status != 'connected';
									this.filter = ___('Not connected');
									break;
								case 'installed':
									productFromApi.show = lodash.has(
										this.installedProducts,
										productSku,
									);
									this.filter = ___('Installed');
									break;
								case 'notInstalled':
									productFromApi.show = !lodash.has(
										this.installedProducts,
										productSku,
									);
									this.filter = ___('Not installed');
									break;
								default:
									productFromApi.show = false;
									break;
							}
						}.bind(this),
					);
				},
			},

			// Products component template
			template: `
        <div class="row">
          <h3>{{type | titled}}
            <div class="btn-group">
              <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{filter}} <span class="caret"></span>
              </button>
              <ul class="dropdown-menu">
                <li><a href="#" v-on:click.prevent="toggleFilter('all')">{{___('All')}}</a></li>
                <li role="separator" class="divider"></li>
                <li><a href="#" v-on:click.prevent="toggleFilter('connected')">{{___('Connected')}}</a></li>
                <li><a href="#" v-on:click.prevent="toggleFilter('notConnected')">{{___('Not connected')}}</a></li>
                <li role="separator" class="divider"></li>
                <li><a href="#" v-on:click.prevent="toggleFilter('installed')">{{___('Installed')}}</a></li>
                <li><a href="#" v-on:click.prevent="toggleFilter('notInstalled')">{{___('Not installed')}}</a></li>
              </ul>
            </div>
          </h3>
          <div class="products">
            <div v-for="product in products" class="col-xs-12 col-md-6 col-lg-4" v-bind:class="{'product__hidden' : ! product.show}">
              <product
                v-bind:key="product.sku"
                v-bind:product-type="type"
                v-bind:product-from-api="product"
                v-bind:installed-product="installedProducts[product.sku]"
                v-bind:user-license="userLicense"
              >
              </product>
            </div>
            <div class="clear"></div>
          </div>
        </div>
      `,
		};

		/**
		 * Create main Vue instance
		 */
		var wpsVueDashboard = new Vue({
			// main instance el
			el: '#dashboard',

			// main instance uses those components
			components: {
				'verify-site': wpsVueVerifySite,
				products: wpsVueProducts,
			},

			// main instance data
			data: {
				error: '',
				loading: {
					checkingAccount: false,
					checkingLicense: false,
					loadingData: false,
					updatingCore: false,
					connectingSite: false,
				},
				dataLoaded: false,
				userID: 0,
				userLicense: '',
				userLicenseInput: '',
				userHasLicense: false,
				userEmail: '',
				userEmailInput: '',
				linkAutoLoginParams: {},
				productsFromApi: {},
				installedProducts: {},
				core: {},
				fullLifetime: false,
				currentProduct: {
					infos: [],
					isAllRequirementsOk: false,
				},
				installModal: {
					message: '',
					showMoreInfos: false,
				},
			},
			computed: {
				toggleLicenseBtn: function () {
					return this.userLicenseInput.length == 0;
				},
				userLicenseChanged: function () {
					return this.userLicense !== this.userLicenseInput;
				},
				licenseButtonIconClass: function () {
					if (!this.userLicenseChanged) {
						return 'fa-check text-success';
					} else if (this.loading.checkingLicense) {
						return 'fa-cog fa-spin';
					} else if (this.error != '') {
						return 'fa-times text-danger';
					} else {
						return 'fa-refresh text-primary';
					}
				},
				licenceBoxClass: function () {
					if (this.loading.checkingLicense == ___('Reloading'))
						return 'alert-success';
					if (this.error) return 'alert-danger';
					return 'alert-info';
				},
			},

			// main instance created hook
			created: function () {
				EventBus.$on(
					'show-requirements-modal',
					function (productInfos, isAllRequirementsOk) {
						this.currentProduct.infos = productInfos;
						this.currentProduct.isAllRequirementsOk = isAllRequirementsOk;
						jQuery('#requirements-modal').modal('show');
					}.bind(this),
				);

				EventBus.$on(
					'show-install-modal',
					function (productInfos) {
						this.installModal.message = productInfos;
						jQuery('#install-modal').modal('show');
					}.bind(this),
				);

				EventBus.$on(
					'show-connection-infos-modal',
					function () {
						jQuery('#connection-infos-modal').modal('show');
					}.bind(this),
				);
			},

			// main instance mounted hook
			mounted: function () {
				this.loadData();
			},

			// main instance methods
			methods: {
				loadData: function () {
					this.loading.loadingData = true;
					this.$http
						.post(
							WPSCORE_dashboard.ajax.url,
							{
								action: 'wpscore_load_dashboard_data',
								nonce: WPSCORE_dashboard.ajax.nonce,
							},
							{
								emulateJSON: true,
							},
						)
						.then(
							function (response) {
								// success callback
								var hiddenLicense = '•••••••••••••••••••••••••••••••••';
								this.userHasLicense = response.body.user_has_license;
								this.userLicense = this.userHasLicense ? hiddenLicense : '';
								this.userLicenseInput = this.userHasLicense
									? hiddenLicense
									: '';
								this.userEmail = this.userEmailInput = response.body.user_email;
								this.productsFromApi = response.body.products;
								this.core = response.body.core;
								this.fullLifetime = response.body.full_lifetime;
								this.linkAutoLoginParams = response.body.link_autologin_params;

								lodash.each(this.productsFromApi, function (productsByType) {
									lodash.each(productsByType, function (product) {
										product.show = true;
									});
								});
								this.installedProducts = response.body.installed_products;
							},
							function (error) {
								// error callback
								this.$snotify.error(JSON.stringify(error), {
									timeout: 5000,
									showProgressBar: true,
									closeOnClick: true,
									pauseOnHover: true,
								});
							},
						)
						.then(function () {
							this.loading.loadingData = false;
							this.dataLoaded = true;
						});
				},

				checkLicense: function () {
					this.loading.checkingLicense = true;
					var savedLicenseInput = this.userLicense;
					this.$http
						.post(
							WPSCORE_dashboard.ajax.url,
							{
								action: 'wpscore_check_license_key',
								nonce: WPSCORE_dashboard.ajax.nonce,
								license_key: this.userLicenseInput,
							},
							{
								emulateJSON: true,
							},
						)
						.then(
							function (response) {
								// success callback
								if (response.body.code === 'success') {
									this.userLicense = this.userLicenseInput;
									this.loading.checkingLicense = ___('Reloading');
									document.location.href = 'admin.php?page=wpscore-dashboard';
								} else if (response.body.code === 'error') {
									this.error = this.userLicenseInput = response.body.message;
									setTimeout(
										function () {
											this.userLicenseInput = savedLicenseInput;
											this.error = '';
											this.$refs.refLicenseInput.focus();
										}.bind(this),
										3000,
									);
								} else {
									this.error = this.userLicenseInput = ___(
										'Invalid License Key',
									);
									setTimeout(
										function () {
											this.userLicenseInput = savedLicenseInput;
											this.error = '';
											this.$refs.refLicenseInput.focus();
										}.bind(this),
										3000,
									);
								}
							},
							function (error) {
								// error callback
								this.$snotify.error(JSON.stringify(error), {
									timeout: 5000,
									showProgressBar: true,
									closeOnClick: true,
									pauseOnHover: true,
								});
							},
						)
						.then(function () {
							this.loading.checkingLicense = false;
						});
				},

				checkAccount: function () {
					this.loading.checkingAccount = true;
					var savedEmailInput = this.userEmail;
					this.$http
						.post(
							WPSCORE_dashboard.ajax.url,
							{
								action: 'wpscore_check_account',
								nonce: WPSCORE_dashboard.ajax.nonce,
								email: this.userEmailInput,
							},
							{
								emulateJSON: true,
							},
						)
						.then(
							function (response) {
								// success callback
								if (response.body.code === 'success') {
									this.loading.checkingAccount = ___('Reloading');
									this.loading.checkingLicense = ___('Reloading');
									this.userLicense = this.userLicenseInput =
										response.body.data.license;
									setTimeout(function () {
										document.location.href = 'admin.php?page=wpscore-dashboard';
									}, 3000);
								} else if (response.body.code === 'error') {
									this.error = this.userEmailInput = response.body.message;
									setTimeout(
										function () {
											this.userEmailInput = savedEmailInput;
											this.error = '';
											this.$refs.refEmailInput.focus();
											this.loading.checkingAccount = false;
										}.bind(this),
										3000,
									);
								} else {
									this.error = this.userEmailInput = ___('Invalid License Key');
									setTimeout(
										function () {
											this.userEmailInput = savedEmailInput;
											this.$refs.refEmailInput.focus();
											this.loading.checkingAccount = false;
										}.bind(this),
										3000,
									);
								}
							},
							function (error) {
								// error callback
								this.$snotify.error(JSON.stringify(error), {
									timeout: 5000,
									showProgressBar: true,
									closeOnClick: true,
									pauseOnHover: true,
								});
								this.loading.checkingAccount = false;
							},
						)
						.then(function () {});
				},

				updateCore: function () {
					this.loading.updatingCore = true;
					this.$http
						.post(
							WPSCORE_dashboard.ajax.url,
							{
								action: 'wpscore_install_product',
								nonce: WPSCORE_dashboard.ajax.nonce,
								product_sku: this.core.sku,
								product_type: 'plugin',
								product_zip: this.core.zip_file,
								product_slug: this.core.slug,
								product_folder_slug: this.core.folder_slug,
								method: 'upgrade',
								new_version: this.core.latest_version,
							},
							{
								emulateJSON: true,
							},
						)
						.then(
							function (response) {
								// success callback
								if (
									response.body === true ||
									response.body == '<div class="wrap"><h1></h1></div>'
								) {
									this.loading.updatingCore = ___('Reloading');
									document.location.href = 'admin.php?page=wpscore-dashboard';
								} else {
									this.showInstallModal(response.body);
								}
							},
							function (error) {
								// error callback
								this.$snotify.error(JSON.stringify(error), {
									timeout: 5000,
									showProgressBar: true,
									closeOnClick: true,
									pauseOnHover: true,
								});
								this.loading.updatingCore = false;
							},
						)
						.then(function () {});
				},

				toggleFilter: function (productType, newValue) {
					this.filters[productType] = newValue;
					lodash.each(
						this.productsFromApi[productType],
						function (productFromApi) {
							var productSku = productFromApi.sku;
							switch (newValue) {
								case 'all':
									productFromApi.show = true;
									break;
								case 'connected':
									productFromApi.show = productFromApi.status == 'connected';
									break;
								case 'notConnected':
									productFromApi.show = productFromApi.status != 'connected';
									break;
								case 'installed':
									productFromApi.show = this.installedProduct !== undefined;
									break;
								case 'notInstalled':
									productFromApi.show = this.installedProduct === undefined;
									break;
								case 'activated':
									productFromApi.show =
										lodash.has(
											this.installedProducts[productType][productSku],
											'state',
										) &&
										this.installedProducts[productType][productSku].state ==
											'activated';
									break;
								case 'notActivated':
									productFromApi.show =
										!lodash.has(
											this.installedProducts[productType][productSku],
											'state',
										) ||
										(lodash.has(
											this.installedProducts[productType][productSku],
											'state',
										) &&
											this.installedProducts[productType][productSku].state !=
												'activated');
									break;
								default:
									productFromApi.show = false;
									break;
							}
						},
					);
				},
			},
		});
	}
});

// helper functions
function hexToRgb(hex) {
	var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
	return result
		? {
				r: parseInt(result[1], 16),
				g: parseInt(result[2], 16),
				b: parseInt(result[3], 16),
			}
		: null;
}

function versionCompare(a, b) {
	if (!a || !b) {
		return 0;
	}
	var i, diff;
	var regExStrip0 = /(\.0+)+$/;
	var segmentsA = a.replace(regExStrip0, '').split('.');
	var segmentsB = b.replace(regExStrip0, '').split('.');
	var l = Math.min(segmentsA.length, segmentsB.length);
	for (i = 0; i < l; i++) {
		diff = parseInt(segmentsA[i], 10) - parseInt(segmentsB[i], 10);
		if (diff) {
			return diff;
		}
	}
	return segmentsA.length - segmentsB.length;
}

var Base64 = {
	_keyStr: 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=',
	encode: function (r) {
		var t,
			e,
			o,
			a,
			h,
			n,
			c,
			d = '',
			C = 0;
		for (r = Base64._utf8_encode(r); C < r.length; )
			(a = (t = r.charCodeAt(C++)) >> 2),
				(h = ((3 & t) << 4) | ((e = r.charCodeAt(C++)) >> 4)),
				(n = ((15 & e) << 2) | ((o = r.charCodeAt(C++)) >> 6)),
				(c = 63 & o),
				isNaN(e) ? (n = c = 64) : isNaN(o) && (c = 64),
				(d =
					d +
					this._keyStr.charAt(a) +
					this._keyStr.charAt(h) +
					this._keyStr.charAt(n) +
					this._keyStr.charAt(c));
		return d;
	},
	decode: function (r) {
		var t,
			e,
			o,
			a,
			h,
			n,
			c = '',
			d = 0;
		for (r = r.replace(/[^A-Za-z0-9+\/=]/g, ''); d < r.length; )
			(t =
				(this._keyStr.indexOf(r.charAt(d++)) << 2) |
				((a = this._keyStr.indexOf(r.charAt(d++))) >> 4)),
				(e =
					((15 & a) << 4) | ((h = this._keyStr.indexOf(r.charAt(d++))) >> 2)),
				(o = ((3 & h) << 6) | (n = this._keyStr.indexOf(r.charAt(d++)))),
				(c += String.fromCharCode(t)),
				64 != h && (c += String.fromCharCode(e)),
				64 != n && (c += String.fromCharCode(o));
		return (c = Base64._utf8_decode(c));
	},
	_utf8_encode: function (r) {
		r = r.replace(/rn/g, 'n');
		for (var t = '', e = 0; e < r.length; e++) {
			var o = r.charCodeAt(e);
			o < 128
				? (t += String.fromCharCode(o))
				: o > 127 && o < 2048
					? ((t += String.fromCharCode((o >> 6) | 192)),
						(t += String.fromCharCode((63 & o) | 128)))
					: ((t += String.fromCharCode((o >> 12) | 224)),
						(t += String.fromCharCode(((o >> 6) & 63) | 128)),
						(t += String.fromCharCode((63 & o) | 128)));
		}
		return t;
	},
	_utf8_decode: function (r) {
		for (var t = '', e = 0, o = (c1 = c2 = 0); e < r.length; )
			(o = r.charCodeAt(e)) < 128
				? ((t += String.fromCharCode(o)), e++)
				: o > 191 && o < 224
					? ((c2 = r.charCodeAt(e + 1)),
						(t += String.fromCharCode(((31 & o) << 6) | (63 & c2))),
						(e += 2))
					: ((c2 = r.charCodeAt(e + 1)),
						(c3 = r.charCodeAt(e + 2)),
						(t += String.fromCharCode(
							((15 & o) << 12) | ((63 & c2) << 6) | (63 & c3),
						)),
						(e += 3));
		return t;
	},
};

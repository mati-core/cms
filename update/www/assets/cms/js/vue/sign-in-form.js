Vue.component('login-form', {
	props: [
		'redirect',
		'locale',
	],
	data: function () {
		return {
			inputLogin: '',
			inputPassword: '',
			btnText: 'Sign In',
			loginSuccess: false,
			showAlert: false,
			alertMsg: ''
		}
	},
	methods: {
		redirectProcess: function () {
			window.location.href = this.redirect;
		},
		loginProcess: function (e) {
			e.preventDefault();

			this.showAlert = false;

			this.btnText = this.translate('process');
			var postData = {
				login: this.inputLogin,
				pass: this.inputPassword
			}

			fetch('/api/v1/sign/sign-in', {
				method: 'POST',
				body: JSON.stringify(postData)
			})
				.then(response => response.json())
				.then(responseData => {
					if (responseData.data !== undefined && responseData.state === 'ok') {
						if (responseData.data.loginStatus === true) {
							this.btnText = this.translate('success');
							this.loginSuccess = true;
							setTimeout(this.redirectProcess, 500);
						} else {
							this.btnText = this.translate('sign_in');
							this.loginSuccess = false;
							this.alertMsg = responseData.data.errorMsg;
							this.showAlert = true;
						}
					} else {
						this.btnText = this.translate('try_again');
						this.loginSuccess = false;
						this.alertMsg = 'Connection fail.'
						this.showAlert = true;
					}
				})
				.catch(error => {
					console.log(error)
				})
		},
		translate: function (type) {
			if (type === 'process') {
				if (this.locale === "cs") {
					return 'Přihlašuji...';
				}

				return 'Processing...';
			} else if (type === 'success') {
				if (this.locale === "cs") {
					return 'Úspěšne přihlášen';
				}

				return 'Success';
			} else if (type === 'sign_in') {
				if (this.locale === "cs") {
					return 'Vstoupit';
				}

				return 'Sign in';
			} else if (type === 'try_again') {
				if (this.locale === "cs") {
					return 'Zkusit znovu';
				}

				return 'Try again';
			} else if (type === 'login') {
				if (this.locale === 'cs') {
					return 'Už. jméno';
				}

				return 'Login';
			} else if (type === 'password') {
				if (this.locale === 'cs') {
					return 'Heslo';
				}

				return 'Password';
			}
		},
	},
	computed: {
		txtLogin: function () {
			return this.translate('login');
		},
		txtPassword: function () {
			return this.translate('password');
		},
	},
	created: function(){
		this.btnText = this.translate('sign_in');
	},
	template: `
				<form method="post">
					<div class="input-group mb-3">
						<input type="email" v-model="inputLogin" class="form-control" v-bind:placeholder="txtLogin">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-envelope"></span>
							</div>
						</div>
					</div>
					<div class="input-group mb-3">
						<input type="password" v-model="inputPassword" class="form-control" v-bind:placeholder="txtPassword">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-lock"></span>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<button
							v-on:click="loginProcess"
							v-bind:class="{ 'btn-success': loginSuccess, 'btn-primary': loginSuccess === false }"
							v-html="btnText"
							class="btn btn-block"></button>
							
						</div>
						<!-- /.col -->
					</div>
				
					<div v-if="showAlert" class="row">
						<div class="col-12 pb-1 text-center text-danger">
							{{ alertMsg }}
						</div>
					</div>
				</form>
			`
});
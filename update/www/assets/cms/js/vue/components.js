Vue.component('btn-delete', {
	props: [
		'redirect',
		'label'
	],
	data: function () {
		return {
			confirm: false
		}
	},
	computed: {
		'confirmLabel': function () {
			if(this.label){
				return this.label;
			}

			return 'confirm';
		}
	},
	methods: {
		'showConfirm': function () {
			this.confirm = true;
		},
		'hideConfirm': function () {
			this.confirm = false;
		},
		'actionDelete': function () {
			location.href = this.redirect;
		}
	},
	template: `
		<span id="delete-btn">
			<button
				class="btn btn-danger btn-xs"
				v-if="confirm === false"
				v-on:click="showConfirm">
					<i class="fas fa-trash"></i>
			</button>
			<button
				class="btn btn-warning btn-xs"
				v-if="confirm === true"
				v-on:click="actionDelete">
					<i class="fas fa-check"></i> {{ confirmLabel }}
			</button>
			<button
				class="btn btn-danger btn-xs"
				v-if="confirm === true"
				v-on:click="hideConfirm">
					<i class="fas fa-times"></i>
			</button>
		</span>
	`
});
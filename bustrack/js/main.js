import App from './src/main.vue';

Vue.component('app', {
	components: {App},
	template: '<App/>'
});

var app = new Vue({
	el: '#app',
});

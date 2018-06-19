export default {
	dicts() {
		return fetch("http://localhost:8080?token=bed04814f428bf40ef0e").then(r =>
			r.json()
		);
	},

	test(dictID) {
		return fetch(
			`http://localhost:8080/${dictID}/test?token=bed04814f428bf40ef0e`
		).then(r => r.json());
	}
};

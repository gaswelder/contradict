<template>
	<span>{{fmt}}</span>
</template>

<script>
function formatTime(d) {
	let h = d.getHours();
	let m = d.getMinutes();
	if (h < 10) h = '0' + h.toString();
	if (m < 10) m = '0' + m.toString();
	return h + ':' + m;
}

function cmp(a, b) {
	return a.replace(':', '') - b.replace(':', '');
}

export default {
	props: ['times'],
	computed: {
		fmt() {
			return this.times.map(formatTime).sort(cmp).join(', ');
		}
	}
};
</script>

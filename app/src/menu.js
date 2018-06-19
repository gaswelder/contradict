import React from "react";

function Menu(props) {
	const dicts = props.data.dicts;
	return dicts.map(function(dict) {
		return (
			<section className="dict-preview" key={dict.id}>
				<b>{dict.name}</b>
				<a href="/{ dict.id }/add">Add words</a>
				<Stats stats={dict.stats} />
				<a className="btn test-button" href={`/${dict.id}/test`}>
					Test
				</a>
			</section>
		);
	});
}

function Stats(props) {
	const stats = props.stats;
	return (
		<ul>
			<li>
				Total: {stats.pairs}; progress: {Math.round(stats.progress * 100, 1)} %
			</li>
			<li>
				(finished {stats.finished}, started {stats.started})
			</li>
			<li>Success rate {stats.successRate}</li>
		</ul>
	);
}

export default Menu;

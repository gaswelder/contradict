import React from "react";

function Test(props) {
	const { tuples1, tuples2 } = props.data;
	return (
		<form method="post" className="test-form">
			<TestSection tuples={tuples1} />
			<TestSection tuples={tuples2} />
			<div>
				<button>Submit</button>
			</div>
		</form>
	);
}

function TestSection(props) {
	return (
		<section>
			{props.tuples.map(question => (
				<div>
					<input type="hidden" name="q[]" value={question.id} />
					<input type="hidden" name="dir[]" value="0" />
					<label>
						{question.q} <small>({question.times})</small>
					</label>
					<input
						name="a[]"
						value=""
						autoComplete="off"
						placeholder={question.hint}
					/>
				</div>
			))}
		</section>
	);
}

export default Test;

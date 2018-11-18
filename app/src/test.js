import React from "react";

function Test(props) {
  const { tuples1, tuples2 } = props.data;
  const { onSubmit } = props;

  function handleSubmit(e) {
    e.preventDefault();
    const r = [...e.target.querySelectorAll("input")].map(input => [
      input.name,
      input.value
    ]);
    onSubmit(r);
  }

  return (
    <form method="post" className="test-form" onSubmit={handleSubmit}>
      <TestSection tuples={tuples1} dir="0" />
      <TestSection tuples={tuples2} dir="1" />
      <div>
        <button type="submit">Submit</button>
      </div>
    </form>
  );
}

function TestSection(props) {
  const { dir, tuples } = props;
  return (
    <section>
      {tuples.map(question => (
        <div key={`${dir}-${question.id}`}>
          <input type="hidden" name="q[]" value={question.id} />
          <input type="hidden" name="dir[]" value={dir} />
          <label>
            {question.q} <small>({question.times})</small>
          </label>
          <input name="a[]" autoComplete="off" placeholder={question.hint} />
        </div>
      ))}
    </section>
  );
}

export default Test;

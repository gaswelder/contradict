import React from "react";

export function TestSection(props) {
  const { reverse, tuples } = props;
  const dir = reverse ? 1 : 0;
  return (
    <section>
      {tuples.map((question) => (
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

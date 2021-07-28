import React from "react";

export function TestSection(props) {
  const { dir, tuples } = props;
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

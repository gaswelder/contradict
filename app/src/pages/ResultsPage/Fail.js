import React from "react";

export function Fail(props) {
  const { question, answer } = props;
  return (
    <article className="fail-card">
      <h3>{question.q}</h3>
      <p>
        {question.a}
        <br />
        <span className="strike">{answer}</span>
      </p>
      {question.urls.map((url) => (
        <a key={url} href={url} target="_blank" rel="noopener noreferrer">
          {url}
        </a>
      ))}
    </article>
  );
}

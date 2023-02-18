import React from "react";
import { Link } from "react-router-dom";

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
      <Link to={`/entries/${question.id}`}>Edit</Link>{" "}
      {question.urls.map((url) => (
        <a key={url} href={url} target="_blank" rel="noopener noreferrer">
          {url}
        </a>
      ))}
    </article>
  );
}

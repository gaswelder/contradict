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
      {question.wikiURL && (
        <a href={question.wikiURL} target="_blank" rel="noopener noreferrer">
          Open on wiki
        </a>
      )}
    </article>
  );
}

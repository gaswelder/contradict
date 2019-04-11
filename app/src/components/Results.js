import React from "react";
import { Link } from "react-router-dom";
import CorrectTable from "./CorrectTable";

function Fail(props) {
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

function Results(props) {
  const { stats, results, dict_id: dictID } = props.data;

  const ok = results.filter(e => e.correct);
  const fail = results.filter(e => !e.correct);

  return (
    <React.Fragment>
      <section className="test-stats">
        <p>
          {Math.round(
            (stats["right"] / (stats["right"] + stats["wrong"])) * 100
          )}{" "}
          %
        </p>
      </section>

      {fail.map((r, i) => (
        <Fail question={r.question} answer={r.answer} key={i} />
      ))}

      <CorrectTable results={ok} />

      <nav>
        <a className="btn" href={`/${dictID}/test`}>
          New test
        </a>
        <a className="btn" href="/">
          Home
        </a>
      </nav>
    </React.Fragment>
  );
}

export default Results;

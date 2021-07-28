import React from "react";
import { Link } from "react-router-dom";
import { ButtonLink } from "../components/ButtonLink";
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
  const { results, dict_id: dictID } = props.data;

  const ok = results.filter((e) => e.correct);
  const fail = results.filter((e) => !e.correct);
  const stats = {
    right: ok.length,
    wrong: fail.length,
  };

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
        <ButtonLink to={`/${dictID}/test`}>New test</ButtonLink>
        <ButtonLink to="/">Home</ButtonLink>
      </nav>
    </React.Fragment>
  );
}

export default Results;

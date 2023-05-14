import React from "react";
import { ROOT_PATH } from "./api";
import { ButtonLink } from "./lib/ButtonLink";

export const ResultsPage = ({ id }) => {
  let data = null;
  try {
    data = JSON.parse(localStorage.getItem(`results-${id}`));
  } catch (err) {
    //
  }

  if (!data) {
    return (
      <>
        <p>No results</p>
        <nav>
          <ButtonLink to={`${ROOT_PATH}test`}>New test</ButtonLink>
          <ButtonLink to={ROOT_PATH}>Home</ButtonLink>
        </nav>
      </>
    );
  }

  const { results, dict_id: dictID } = data;
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
        <article className="fail-card" key={i}>
          <h3>{r.question.q}</h3>
          <p>
            {r.question.a}
            <br />
            <span className="strike">{r.answer}</span>
          </p>
          {r.question.urls.map((url) => (
            <a key={url} href={url} target="_blank" rel="noopener noreferrer">
              {url}
            </a>
          ))}
        </article>
      ))}

      <table>
        <thead>
          <tr>
            <th>Q</th>
            <th>A</th>
            <th />
          </tr>
        </thead>
        <tbody>
          {ok.map((r) => (
            <tr key={r.question.id}>
              <td>{r["question"]["q"]}</td>
              <td>{r["question"]["a"]}</td>
              <td>ok</td>
            </tr>
          ))}
        </tbody>
      </table>

      <nav>
        <ButtonLink to={`${ROOT_PATH}${dictID}/test`}>New test</ButtonLink>
        <ButtonLink to={ROOT_PATH}>Home</ButtonLink>
      </nav>
    </React.Fragment>
  );
};

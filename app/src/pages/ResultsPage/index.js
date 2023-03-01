import React from "react";
import { ROOT_PATH } from "../../api";
import { ButtonLink } from "../../components/ButtonLink";
import { CorrectTable } from "./CorrectTable";
import { Fail } from "./Fail";

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
        <Fail question={r.question} answer={r.answer} key={i} />
      ))}

      <CorrectTable results={ok} />

      <nav>
        <ButtonLink to={`${ROOT_PATH}${dictID}/test`}>New test</ButtonLink>
        <ButtonLink to={ROOT_PATH}>Home</ButtonLink>
      </nav>
    </React.Fragment>
  );
};

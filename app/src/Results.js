import React from "react";

function Results(props) {
  const { stats, ok, fail, dict_id: dictID } = props.data;
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
      <table>
        <tr>
          <th>Q</th>
          <th>Expected</th>
          <th>A</th>
        </tr>
        {fail.map(r => (
          <tr className="nope" key={r.question.id}>
            <td>{r["question"]["q"]}</td>
            <td>
              <a href="/entries/{{r['question']['id']}}">
                {r["question"]["a"]}
              </a>
              {r["question"]["wikiURL"] && (
                <small>
                  (<a href={r["question"]["wikiURL"]}>wiki</a>)
                </small>
              )}
            </td>
            <td>{r["answer"]}</td>
          </tr>
        ))}
      </table>

      <table>
        <tr>
          <th>Q</th>
          <th>A</th>
          <th />
        </tr>
        {ok.map(r => (
          <tr key={r.question.id}>
            <td>{r["question"]["q"]}</td>
            <td>{r["question"]["a"]}</td>
            <td>ok</td>
          </tr>
        ))}
      </table>

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

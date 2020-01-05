import React from "react";
import { Link } from "react-router-dom";
import ColumnMeter from "./ColumnMeter";

function Dictionary(props) {
  const { dict } = props;
  return (
    <section className="dict-preview">
      <h3>{dict.name}</h3>
      <Link to={`dicts/${dict.id}`}>Edit</Link>
      <Stats stats={dict.stats} />
      <Link to={`/${dict.id}/test`}>Do a test</Link>{" "}
      <Link to={`/${dict.id}/add`}>Add words</Link>
    </section>
  );
}

function Stats(props) {
  const { stats } = props;
  const { pairs, touched, finished } = stats;

  return (
    <div>
      <dl>
        <dt>Number of entries</dt>
        <dd>{pairs}</dd>

        <dt>Finished</dt>
        <dd>{finished}</dd>

        <dt>Touched</dt>
        <dd>{touched}</dd>

        <dt>Success rate</dt>
        <dd>{(stats.successRate * 100).toFixed(1)} %</dd>
      </dl>
      <ColumnMeter
        inQueue={pairs - touched}
        inProgress={touched}
        finished={finished}
      />
    </div>
  );
}

export default Dictionary;

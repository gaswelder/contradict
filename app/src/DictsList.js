import React from "react";

function DictsList(props) {
  const dicts = props.data.dicts;
  return dicts.map(d => <DictEntry key={d.id} dict={d} />);
}

function DictEntry(props) {
  const { dict } = props;
  return (
    <section className="dict-preview">
      <h3>{dict.name}</h3>
      <Stats stats={dict.stats} />
      <a href={`/${dict.id}/test`}>Do a test</a>{" "}
      <a href={`/${dict.id}/add`}>Add words</a>
    </section>
  );
}

function Stats(props) {
  const { stats } = props;
  return (
    <dl>
      <dt>Number of entries</dt>
      <dd>{stats.pairs}</dd>

      <dt>Progress</dt>
      <dd>{(stats.progress * 100).toFixed(1)} %</dd>

      <dt>Success rate</dt>
      <dd>{(stats.successRate * 100).toFixed(1)} %</dd>

      <dt>Finished</dt>
      <dd>{stats.finished}</dd>

      <dt>Started</dt>
      <dd>{stats.started}</dd>
    </dl>
  );
}

export default DictsList;

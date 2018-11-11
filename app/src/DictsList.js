import React from "react";

function DictsList(props) {
  const dicts = props.data.dicts;
  return dicts.map(d => <DictEntry key={d.id} dict={d} />);
}

function DictEntry(props) {
  const { dict } = props;
  return (
    <section className="dict-preview">
      <b>{dict.name}</b>
      <a href="/{ dict.id }/add">Add words</a>
      <Stats stats={dict.stats} />
      <a className="btn test-button" href={`/${dict.id}/test`}>
        Test
      </a>
    </section>
  );
}

function Stats(props) {
  const { stats } = props;
  return (
    <ul>
      <li>
        Total: {stats.pairs}; progress: {Math.round(stats.progress * 100, 1)} %
      </li>
      <li>
        (finished {stats.finished}, started {stats.started})
      </li>
      <li>Success rate {stats.successRate}</li>
    </ul>
  );
}

export default DictsList;
